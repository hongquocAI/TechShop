param([int]$Port = 8010)
$ErrorActionPreference = 'Stop'
$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
$runtimePython = Join-Path $projectRoot 'work/semantic-venv/Scripts/python.exe'
$pidFile = Join-Path $projectRoot 'work/semantic-search.pid'
if (-not (Test-Path -LiteralPath $runtimePython)) {
    throw 'Chạy Setup-Search.ps1 trước để cài môi trường và mô hình.'
}
$health = $null
try {
    $health = Invoke-RestMethod "http://127.0.0.1:$Port/health" -TimeoutSec 2
} catch {
    if ($_.Exception.Response) { throw "Cổng $Port đang được dịch vụ khác sử dụng." }
    # Connection refused or timed out: start the local service below.
}
if ($health) {
    if ($health.model -eq 'multilingual-e5-small INT8') {
        Write-Output 'Dịch vụ tìm kiếm đã chạy.'
        exit 0
    }
    throw "Cổng $Port đang được dịch vụ khác sử dụng."
}
$serviceScript = Join-Path $PSScriptRoot 'server.py'
$process = Start-Process -FilePath $runtimePython -ArgumentList @('"' + $serviceScript + '"', '--port', $Port) -WorkingDirectory $projectRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $projectRoot 'work/semantic-search.stdout.log') -RedirectStandardError (Join-Path $projectRoot 'work/semantic-search.stderr.log')
[System.IO.File]::WriteAllText($pidFile, [string]$process.Id)
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    if ($process.HasExited) { throw 'Dịch vụ không khởi động được; xem work/semantic-search.stderr.log.' }
    try {
        $health = Invoke-RestMethod "http://127.0.0.1:$Port/health" -TimeoutSec 2
        if ($health.model -eq 'multilingual-e5-small INT8') {
            Write-Output "Dịch vụ chạy tại http://127.0.0.1:$Port; vector cập nhật trong nền."
            exit 0
        }
    } catch { }
    Start-Sleep -Milliseconds 500
}
throw 'Khởi động mất quá lâu; xem work/semantic-search.stderr.log.'
