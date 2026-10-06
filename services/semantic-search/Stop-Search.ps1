$ErrorActionPreference = 'Stop'
$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
$pidFile = Join-Path $projectRoot 'work/semantic-search.pid'
if (-not (Test-Path -LiteralPath $pidFile)) { Write-Output 'Không có PID dịch vụ đã lưu.'; exit 0 }
$serviceProcessId = [int]([System.IO.File]::ReadAllText($pidFile).Trim())
$process = Get-CimInstance Win32_Process -Filter "ProcessId = $serviceProcessId"
$expectedScript = Join-Path $PSScriptRoot 'server.py'
$expectedPython = Join-Path $projectRoot 'work/semantic-venv/Scripts/python.exe'
if ($process -and $process.ExecutablePath -eq $expectedPython -and $process.CommandLine.Contains($expectedScript)) {
    Stop-Process -Id $serviceProcessId
    Remove-Item -LiteralPath $pidFile
    Write-Output 'Đã dừng dịch vụ tìm kiếm của TechShop.'
} elseif ($process) {
    throw 'PID không còn thuộc dịch vụ TechShop; không dừng tiến trình này.'
} else {
    Remove-Item -LiteralPath $pidFile
    Write-Output 'Dịch vụ đã dừng.'
}
