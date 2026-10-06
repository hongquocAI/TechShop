param([string]$Php)
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'Set-SearchMode.ps1') -Mode Keyword -Php $Php
if ($LASTEXITCODE -ne 0) { throw 'Không đổi được về tìm kiếm từ khóa.' }
& (Join-Path $PSScriptRoot 'Stop-Search.ps1')
Write-Output 'Đã trở về tìm kiếm từ khóa và dừng AI. Checkpoint mã nguồn: checkpoint-before-semantic-search-20261006 (cf618a4).'
