import json
import tempfile
import time
import unittest
from pathlib import Path

import numpy as np

from server import Index, explicit_intent, matches_explicit_intent


class FakeEncoder:
    def __init__(self):
        self.documents = 0

    def encode(self, texts):
        self.documents += len(texts)
        vectors = np.zeros((len(texts), 384), dtype=np.float32)
        for position, text in enumerate(texts):
            vectors[position, 0 if 'headphones' in text else 1] = 1
        return vectors

    def query(self, text):
        return self.encode([text])[0]


def product(identity, text, price=100):
    return {'id': identity, 'name': text, 'sku': str(identity), 'text': text,
            'category_id': 1, 'brand_id': 1, 'price': price, 'attributes': {'2': 'Bluetooth'}}


class IndexTests(unittest.TestCase):
    def test_explicit_connection_and_noise_requirements_exclude_wrong_products(self):
        record = product(1, 'Sony WH-CH520 Bluetooth')
        record['category'] = 'Tai nghe'
        record['named_attributes'] = {'Kết nối': 'Bluetooth 5.3', 'Chống ồn': 'Không'}
        self.assertTrue(matches_explicit_intent(record, explicit_intent('tai nghe không dây')))
        self.assertFalse(matches_explicit_intent(record, explicit_intent('nghe nhạc không bị tiếng ồn')))
        self.assertTrue(matches_explicit_intent(record, explicit_intent('tai nghe không chống ồn')))
        record['named_attributes']['Kết nối'] = 'Có dây'
        self.assertFalse(matches_explicit_intent(record, explicit_intent('tai nghe không dây')))

    def test_incremental_changes_reuse_vectors_update_filters_and_remove_products(self):
        with tempfile.TemporaryDirectory() as temporary:
            directory = Path(temporary)
            (directory / 'catalog.json').write_text(json.dumps({'products': [product(1, 'headphones')]}))
            encoder = FakeEncoder()
            index = Index(encoder, directory)
            index.refresh()
            self.assertEqual(encoder.documents, 1)
            changes = directory / 'changes'
            changes.mkdir()
            (changes / '1.json').write_text(json.dumps(product(1, 'headphones', 500)))
            index.refresh()
            self.assertEqual(encoder.documents, 1, 'Price changes should not re-encode text')
            self.assertEqual(index.search('headphones', {'max': 200}, 10, .5)[0], [])
            (changes / '2.json').write_text(json.dumps(product(2, 'headphones wireless')))
            index.refresh()
            self.assertEqual(index.search('headphones', {'attributes': {'2': 'Bluetooth'}}, 10, .5)[0], [1, 2])
            (changes / '1.json').write_text(json.dumps({'id': 1, 'deleted': True}))
            index.refresh()
            self.assertEqual(index.search('headphones', {}, 10, .5)[0], [2])
            restarted = Index(encoder, directory)
            self.assertTrue(restarted.ready)
            self.assertEqual(restarted.search('headphones', {}, 10, .5)[0], [2])

    def test_full_export_overrides_old_changes_and_empty_catalogue_is_valid(self):
        with tempfile.TemporaryDirectory() as temporary:
            directory = Path(temporary)
            changes = directory / 'changes'
            changes.mkdir()
            (changes / '1.json').write_text(json.dumps(product(1, 'headphones')))
            time.sleep(.01)
            (directory / 'catalog.json').write_text(json.dumps({'products': []}))
            index = Index(FakeEncoder(), directory)
            index.refresh()
            self.assertTrue(index.ready)
            self.assertEqual(index.search('headphones', {}, 10, .5)[0], [])


if __name__ == '__main__':
    unittest.main()
