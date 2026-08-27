$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..")
$php = Join-Path $root ".tools\php\php.exe"
$hostName = "127.0.0.1"
$port = if ($env:PORT) { $env:PORT } else { "8080" }

if (-not (Test-Path -LiteralPath $php)) {
  $php = "php"
}

Write-Host "Oferto local: http://$hostName`:$port" -ForegroundColor Cyan
& $php -S "$hostName`:$port" -t $root
