param([int]$Port = 8080)
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $projectRoot
php -d extension=openssl -d extension=mbstring -S "127.0.0.1:$Port" router.php
