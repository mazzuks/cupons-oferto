$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..")
$php = Join-Path $root ".tools\php\php.exe"

if (-not (Test-Path -LiteralPath $php)) {
  $php = "php"
}

$files = Get-ChildItem -LiteralPath $root -Recurse -Filter "*.php" |
  Where-Object {
    $_.FullName -notlike "*\.tools\*" -and
    $_.FullName -notlike "*\uploads\*"
  }

$failed = $false

foreach ($file in $files) {
  $result = & $php -l $file.FullName 2>&1
  if ($LASTEXITCODE -ne 0) {
    $failed = $true
    Write-Host ""
    Write-Host $file.FullName -ForegroundColor Red
    $result | ForEach-Object { Write-Host $_ }
  }
}

if ($failed) {
  exit 1
}

Write-Host ("PHP lint OK: {0} arquivos verificados." -f $files.Count) -ForegroundColor Green
