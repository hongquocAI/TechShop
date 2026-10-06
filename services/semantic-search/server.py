"""Local CPU semantic search with a persistent index and incremental background updates."""
import argparse
import hashlib
import json
import logging
import os
import re
import threading
import time
import unicodedata
from functools import lru_cache
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path

import numpy as np
import psutil

from encoder import Encoder

ROOT = Path(__file__).resolve().parents[2]
DIRECTORY = ROOT / 'storage/app/private/semantic-search'
MODEL_DIRECTORY = ROOT / 'work/semantic-model'
LOG = logging.getLogger('techshop.search')


def text_hash(record):
    return hashlib.sha256(record['text'].encode('utf-8')).hexdigest()


def eligible(record, filters):
    for field in ('category_id', 'brand_id'):
        if filters.get(field) not in (None, '') and str(record.get(field)) != str(filters[field]):
            return False
    if filters.get('min') not in (None, '') and record['price'] < int(filters['min']):
        return False
    if filters.get('max') not in (None, '') and record['price'] > int(filters['max']):
        return False
    return all(value in (None, '') or record.get('attributes', {}).get(str(key)) == value
               for key, value in filters.get('attributes', {}).items())


@lru_cache(maxsize=20000)
def normalized(text):
    return ''.join(character for character in unicodedata.normalize('NFD', text.lower().replace('đ', 'd'))
                   if unicodedata.category(character) != 'Mn')


@lru_cache(maxsize=256)
def explicit_intent(query):
    query = normalized(query)
    types = [
        (r'\btai nghe\b|\bheadphones?\b|\bearphones?\b', r'tai nghe|earphones?|headphones?'),
        (r'\bban phim\b|\bkeyboards?\b', r'ban phim|keyboard'),
        (r'\bchuot\b|\bmouse\b', r'chuot|mouse'),
        (r'\bpin du phong\b|\bsac du phong\b|\bpower ?bank\b', r'pin|powerbank|powercore'),
        (r'\bday sac\b|\bcap sac\b|\bcharging cable\b', r'cap|cable'),
    ]
    return {
        'types': tuple(re.compile(name_pattern) for query_pattern, name_pattern in types if re.search(query_pattern, query)),
        'headphones': bool(re.search(types[0][0], query)),
        'noise': bool(re.search(r'chong on|giam tieng on|khong bi tieng on|khong bi on|noise cancell', query)),
        'no_noise': bool(re.search(r'khong (can )?chong on', query)),
        'wireless': bool(re.search(r'khong day|wireless|bluetooth', query)),
    }


def matches_explicit_intent(record, intent):
    """Respect clear product types and factual requirements before semantic ranking."""
    name = normalized(record['name'])
    if intent['headphones']:
        name += ' ' + normalized(record.get('category') or '')
    for pattern in intent['types']:
        if not pattern.search(name):
            return False
    attributes = {normalized(key): normalized(str(value)) for key, value in record.get('named_attributes', {}).items()}
    if intent['noise']:
        if attributes.get('chong on') != ('khong' if intent['no_noise'] else 'co'):
            return False
    if intent['wireless']:
        connection = attributes.get('ket noi', '')
        if connection and not any(value in connection for value in ('bluetooth', 'khong day', 'wireless', '2.4g')):
            return False
    return True


class Index:
    def __init__(self, encoder, directory):
        self.encoder, self.directory = encoder, directory
        self.lock = threading.Lock()
        self.records, self.vectors = [], np.empty((0, 384), dtype=np.float32)
        self.ready, self.updating, self.error = False, False, None
        self.signature = None
        self.last_update = None
        self.directory.mkdir(parents=True, exist_ok=True)
        cache = self.directory / 'index.npz'
        if cache.exists():
            try:
                with np.load(cache, allow_pickle=False) as data:
                    self.records = json.loads(str(data['records']))
                    self.vectors = data['vectors']
                    if self.vectors.shape != (len(self.records), 384):
                        raise ValueError('Invalid cached index shape')
                self.ready = True
            except Exception:
                LOG.exception('Ignoring invalid cached index')

    def sources(self):
        catalog = self.directory / 'catalog.json'
        if not catalog.exists():
            return None, None
        catalog_time = catalog.stat().st_mtime_ns
        changes = [(path, path.stat().st_mtime_ns) for path in sorted((self.directory / 'changes').glob('*.json'))]
        signature = (catalog_time, tuple((str(path), modified) for path, modified in changes))
        if signature == self.signature:
            return signature, None
        records = {int(record['id']): record for record in json.loads(catalog.read_text(encoding='utf-8'))['products']}
        for path, modified in changes:
            if modified >= catalog_time:
                record = json.loads(path.read_text(encoding='utf-8'))
                if record.get('deleted'):
                    records.pop(int(record['id']), None)
                else:
                    records[int(record['id'])] = record
        return signature, [records[key] for key in sorted(records)]

    def refresh(self):
        signature, records = self.sources()
        if records is None:
            return
        self.updating = True
        try:
            with self.lock:
                existing = {record['id']: (text_hash(record), vector) for record, vector in zip(self.records, self.vectors)}
            vectors = np.empty((len(records), 384), dtype=np.float32)
            changed = []
            for position, record in enumerate(records):
                previous = existing.get(record['id'])
                if previous and previous[0] == text_hash(record):
                    vectors[position] = previous[1]
                else:
                    changed.append(position)
            for offset in range(0, len(changed), 8):
                batch = changed[offset:offset + 8]
                vectors[batch] = self.encoder.encode(['passage: ' + records[position]['text'] for position in batch])
            temporary = self.directory / 'index.pending.npz'
            np.savez(temporary, vectors=vectors, records=json.dumps(records, ensure_ascii=False))
            os.replace(temporary, self.directory / 'index.npz')
            with self.lock:
                self.records, self.vectors = records, vectors
                self.signature, self.ready, self.error = signature, True, None
                self.last_update = time.time()
            LOG.info('Index updated: %d products, %d new embeddings', len(records), len(changed))
        finally:
            self.updating = False

    def watch(self):
        while True:
            try:
                self.refresh()
            except Exception as exception:
                self.error = str(exception)
                LOG.exception('Index update failed; keeping previous index')
            time.sleep(2)

    def search(self, query, filters, limit, min_score):
        if not self.ready:
            raise RuntimeError('Index not ready')
        query_vector = self.encoder.query(query)
        with self.lock:
            records, vectors = self.records, self.vectors
        intent = explicit_intent(query)
        positions = np.array([i for i, record in enumerate(records) if eligible(record, filters) and matches_explicit_intent(record, intent)], dtype=np.int64)
        if not positions.size:
            return [], []
        scores = vectors[positions] @ query_vector
        order = np.argsort(-scores, kind='stable')[:limit]
        threshold = max(min_score, float(scores.max()) - .035)
        matches = [(int(positions[position]), float(scores[position])) for position in order if scores[position] >= threshold]
        return [int(records[position]['id']) for position, _ in matches], [round(score, 5) for _, score in matches]


class Handler(BaseHTTPRequestHandler):
    def respond(self, status, data):
        payload = json.dumps(data, ensure_ascii=False).encode('utf-8')
        self.send_response(status)
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Content-Length', str(len(payload)))
        self.end_headers()
        try:
            self.wfile.write(payload)
        except (BrokenPipeError, ConnectionResetError):
            pass

    def do_GET(self):
        if self.path != '/health':
            return self.respond(404, {'error': 'Not found'})
        index = self.server.index
        self.respond(200, {
            'ready': index.ready, 'updating': index.updating, 'products': len(index.records),
            'model': 'multilingual-e5-small INT8', 'dimensions': 384,
            'last_update': index.last_update, 'error': index.error,
            'ram_mb': round(psutil.Process().memory_info().rss / 1_000_000, 1),
        })

    def do_POST(self):
        if self.path != '/search':
            return self.respond(404, {'error': 'Not found'})
        try:
            length = int(self.headers.get('Content-Length', '0'))
            if not 0 < length <= 8192:
                return self.respond(413, {'error': 'Invalid request size'})
            payload = json.loads(self.rfile.read(length))
            query = payload['query']
            filters = payload.get('filters') or {}
            if not isinstance(query, str) or not query.strip() or len(query) > 300 or not isinstance(filters, dict):
                raise ValueError('Invalid query or filters')
            if isinstance(filters.get('attributes'), list) and not filters['attributes']:
                filters['attributes'] = {}
            limit = min(100, max(1, int(payload.get('limit', 100))))
            min_score = float(payload.get('min_score', 0.84))
            if not 0 <= min_score <= 1:
                raise ValueError('Invalid score threshold')
            started = time.perf_counter()
            ids, scores = self.server.index.search(query.strip(), filters, limit, min_score)
            self.respond(200, {'ids': ids, 'scores': scores, 'elapsed_ms': round((time.perf_counter() - started) * 1000, 2)})
        except (ValueError, KeyError, TypeError, AttributeError):
            self.respond(422, {'error': 'Invalid search payload'})
        except RuntimeError:
            self.respond(503, {'error': 'Index not ready'})
        except Exception:
            LOG.exception('Search failed')
            self.respond(503, {'error': 'Search temporarily unavailable'})

    def log_message(self, format, *args):
        # Do not write users' search text to log files.
        pass


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--port', type=int, default=8010)
    args = parser.parse_args()
    logging.basicConfig(level=logging.INFO, format='%(asctime)s %(message)s')
    encoder = Encoder(MODEL_DIRECTORY)
    index = Index(encoder, DIRECTORY)
    server = ThreadingHTTPServer(('127.0.0.1', args.port), Handler)
    server.index = index
    threading.Thread(target=index.watch, daemon=True, name='catalog-indexer').start()
    LOG.info('Search service listening on http://127.0.0.1:%d', args.port)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()


if __name__ == '__main__':
    main()
