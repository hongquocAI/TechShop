param([Parameter(Mandatory = $true)][ValidateSet('Keyword', 'Hybrid')][string]$Mode, [string]$Php)
$ErrorActionPreference = 'Stop'
$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
$environmentFile = Join-Path $projectRoot '.env'
$contents = [System.IO.File]::ReadAllText($environmentFile)
$value = if ($Mode -eq 'Hybrid') { 'true' } else { 'false' }
$setting = "SEMANTIC_SEARCH_ENABLED=$value"
if ($contents -match '(?m)^SEMANTIC_SEARCH_ENABLED=.*$') {
    $contents = [regex]::Replace($contents, '(?m)^SEMANTIC_SEARCH_ENABLED=.*$', $setting)
} else {
    $contents = $contents.TrimEnd() + "`r`n$setting`r`n"
}
[System.IO.File]::WriteAllText($environmentFile, $contents, [System.Text.UTF8Encoding]::new($false))
if (-not $Php) {
    $command = Get-Command php -ErrorAction SilentlyContinue
    if ($command) { $Php = $command.Source }
    elseif (Test-Path 'D:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe') { $Php = 'D:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe' }
    else { throw 'Truyền -Php đường dẫn php.exe của Laragon để làm mới cấu hình.' }
}
& $Php (Join-Path $projectRoot 'artisan') config:clear
if ($LASTEXITCODE -ne 0) { throw 'Không làm mới được cấu hình Laravel.' }
& $Php (Join-Path $projectRoot 'artisan') cache:forget semantic-search:unavailable
if ($Mode -eq 'Hybrid') {
    & $Php (Join-Path $projectRoot 'artisan') search:index
    if ($LASTEXITCODE -ne 0) { throw 'Không xuất được danh mục sản phẩm để tạo vector.' }
}
Write-Output "Chế độ tìm kiếm: $Mode."
