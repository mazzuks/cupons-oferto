$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..")
$php = Join-Path $root ".tools\php\php.exe"
$composer = Join-Path $root ".tools\composer.phar"

if (-not (Test-Path -LiteralPath $php)) {
  $php = "php"
}

if (-not (Test-Path -LiteralPath $composer)) {
  Write-Host "Composer local não encontrado em .tools\composer.phar" -ForegroundColor Red
  exit 1
}

& $php $composer @args
