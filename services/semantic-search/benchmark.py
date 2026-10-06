"""Measure real local HTTP searches and a synthetic 10k-vector retrieval workload."""
import json
import statistics
import sys
import tempfile
import time
from pathlib import Path
from urllib.request import Request, urlopen

import numpy as np

from encoder import Encoder
from server import DIRECTORY, MODEL_DIRECTORY, ROOT, Index

QUERIES = [
    'tai nghe không dây', 'nghe nhạc không bị tiếng ồn', 'tai nghe chống ồn',
    'sạc nhanh cho điện thoại', 'dây sạc dài', 'chuột không gây tiếng động',
    'bàn phím nhỏ gọn', 'sạc mang theo khi đi du lịch', 'pin dự phòng dung lượng lớn',
    'K380', 'Sony', 'giày thể thao',
]


def summarize(values):
    return {'median_ms': round(statistics.median(values), 2),
            'p95_ms': round(float(np.percentile(values, 95)), 2),
            'max_ms': round(max(values), 2)}


def main():
    sys.stdout.reconfigure(encoding='utf-8')
    products = json.loads((DIRECTORY / 'catalog.json').read_text(encoding='utf-8'))['products']
    names = {record['id']: record['name'] for record in products}
    http_results = []
    for query in QUERIES:
        started = time.perf_counter()
        request = Request('http://127.0.0.1:8010/search',
                          data=json.dumps({'query': query, 'min_score': .84, 'limit': 5}).encode(),
                          headers={'Content-Type': 'application/json'})
        with urlopen(request, timeout=5) as response:
            result = json.load(response)
        http_results.append({'query': query, 'total_ms': round((time.perf_counter() - started) * 1000, 2),
                             'engine_ms': result['elapsed_ms'],
                             'matches': [{'name': names[identity], 'score': score}
                                         for identity, score in zip(result['ids'], result['scores'])]})
    with urlopen('http://127.0.0.1:8010/health', timeout=5) as response:
        health = json.load(response)
    encoder = Encoder(MODEL_DIRECTORY)
    with tempfile.TemporaryDirectory() as directory:
        index = Index(encoder, Path(directory))
        with np.load(DIRECTORY / 'index.npz', allow_pickle=False) as data:
            base_vectors = data['vectors']
        # Repeat existing vectors to measure scale; this does not evaluate 10k unique products.
        index.records = [dict(products[position % len(products)], id=position + 1) for position in range(10_000)]
        index.vectors = np.tile(base_vectors, (int(np.ceil(10_000 / len(base_vectors))), 1))[:10_000].copy()
        index.ready = True
        encoder.query('khởi động mô hình')
        uncached, cached = [], []
        for query in QUERIES:
            started = time.perf_counter()
            index.search(query, {}, 100, .84)
            uncached.append((time.perf_counter() - started) * 1000)
            started = time.perf_counter()
            index.search(query, {}, 100, .84)
            cached.append((time.perf_counter() - started) * 1000)
    report = {'health': health, 'real_catalogue_http': http_results,
              'synthetic_10k': {'description': '10,000 records with repeated catalogue vectors; no database writes',
                                'vector_mb': round(index.vectors.nbytes / 1_000_000, 2),
                                'uncached_query': summarize(uncached), 'cached_query': summarize(cached)}}
    output = ROOT / 'outputs/semantic-search-benchmark.json'
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    main()
