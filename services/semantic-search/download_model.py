"""Download only the official INT8 graph and fast tokenizer, never PyTorch weights."""
import hashlib
import json
from pathlib import Path

from huggingface_hub import hf_hub_download

ROOT = Path(__file__).resolve().parents[2]
MODEL_DIR = ROOT / "work" / "semantic-model"
REPO = "intfloat/multilingual-e5-small"
GRAPH = "onnx/model_qint8_avx512_vnni.onnx"
REVISION = "614241f622f53c4eeff9890bdc4f31cfecc418b3"
EXPECTED_GRAPH_HASH = "dd476dd0c2514e9b9be83aeb3853fac0763e0bdf4a71645407587d77c48a2d88"


def main():
    MODEL_DIR.mkdir(parents=True, exist_ok=True)
    manifest_path = MODEL_DIR / "download.json"
    revision = REVISION
    paths = []
    for filename in (GRAPH, "tokenizer.json"):
        path = Path(hf_hub_download(REPO, filename=filename, revision=revision, local_dir=MODEL_DIR))
        paths.append(path)
        print(f"Downloaded {filename}: {path.stat().st_size / 1_000_000:.2f} MB", flush=True)
    if hashlib.sha256(paths[0].read_bytes()).hexdigest() != EXPECTED_GRAPH_HASH:
        raise RuntimeError("Official INT8 model checksum did not match.")
    manifest_path.write_text(json.dumps({'model': REPO, 'revision': revision, 'graph_sha256': EXPECTED_GRAPH_HASH}, indent=2))
    print(f"Model revision: {revision}", flush=True)


if __name__ == '__main__':
    main()
