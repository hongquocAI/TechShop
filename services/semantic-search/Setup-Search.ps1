param([string]$Python)
$ErrorActionPreference = 'Stop'
$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
if (-not $Python) {
    $bundledPython = Join-Path $env:USERPROFILE '.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
    if (Test-Path -LiteralPath $bundledPython) { $Python = $bundledPython }
    else { $Python = (Get-Command python -ErrorAction Stop).Source }
}
$runtimePython = Join-Path $projectRoot 'work/semantic-venv/Scripts/python.exe'
if (-not (Test-Path -LiteralPath $runtimePython)) {
    & $Python -m venv (Join-Path $projectRoot 'work/semantic-venv')
    if ($LASTEXITCODE -ne 0) { throw 'Cần Python 3.12 để tạo môi trường chạy.' }
}
& $runtimePython -m pip install --disable-pip-version-check -r (Join-Path $PSScriptRoot 'requirements.txt')
if ($LASTEXITCODE -ne 0) { throw 'Cài thư viện không thành công.' }
& $runtimePython (Join-Path $PSScriptRoot 'download_model.py')
if ($LASTEXITCODE -ne 0) { throw 'Tải hoặc xác minh mô hình không thành công.' }
