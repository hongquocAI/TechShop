import os
import threading
from functools import lru_cache
from pathlib import Path

import numpy as np
import onnxruntime as ort
from tokenizers import Tokenizer


class Encoder:
    def __init__(self, model_directory: Path):
        options = ort.SessionOptions()
        options.intra_op_num_threads = min(4, os.cpu_count() or 1)
        options.inter_op_num_threads = 1
        self.session = ort.InferenceSession(
            str(model_directory / 'onnx/model_qint8_avx512_vnni.onnx'),
            sess_options=options, providers=['CPUExecutionProvider'],
        )
        self.input_names = {entry.name for entry in self.session.get_inputs()}
        self.tokenizer = Tokenizer.from_file(str(model_directory / 'tokenizer.json'))
        self.tokenizer.enable_truncation(max_length=192)
        self.tokenizer.enable_padding(pad_id=1, pad_token='<pad>')
        self.lock = threading.Lock()

    def encode(self, texts: list[str]) -> np.ndarray:
        if not texts:
            return np.empty((0, 384), dtype=np.float32)
        # One inference at a time avoids CPU oversubscription during background updates.
        with self.lock:
            encoded = self.tokenizer.encode_batch(texts)
            ids = np.array([item.ids for item in encoded], dtype=np.int64)
            mask = np.array([item.attention_mask for item in encoded], dtype=np.int64)
            feed = {'input_ids': ids, 'attention_mask': mask}
            if 'token_type_ids' in self.input_names:
                feed['token_type_ids'] = np.zeros_like(ids)
            states = self.session.run(None, feed)[0]
            weights = mask[..., None].astype(np.float32)
            pooled = (states * weights).sum(axis=1) / np.maximum(weights.sum(axis=1), 1)
            normalized = pooled / np.maximum(np.linalg.norm(pooled, axis=1, keepdims=True), 1e-12)
            return normalized.astype(np.float32)

    @lru_cache(maxsize=256)
    def query(self, text: str) -> np.ndarray:
        return self.encode(['query: ' + text])[0]
