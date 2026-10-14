$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'Instala PHP 8.2+ o agrega C:\xampp\php al PATH.' }
if (-not (Test-Path 'vendor/autoload.php')) {
    & composer install --no-interaction --prefer-dist
    if ($LASTEXITCODE -ne 0) { throw 'Composer no pudo instalar las dependencias.' }
}
if (-not (Test-Path '.env')) { Copy-Item -LiteralPath '.env.example' -Destination '.env' }
if (-not (Test-Path 'database/database.sqlite')) { New-Item -Path 'database/database.sqlite' -ItemType File | Out-Null }
$taskEnvText = Get-Content -LiteralPath '.env' -Raw
if ($taskEnvText -notmatch '(?m)^APP_KEY=.+$') {
    & php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear la clave local.' }
}
foreach ($taskArgs in @(@('migrate','--force'), @('blog:demo'))) {
    & php artisan @taskArgs
    if ($LASTEXITCODE -ne 0) { throw "Falló artisan: $taskArgs" }
}
Write-Host 'Preparado. Ejecuta: php artisan serve --host=127.0.0.1 --port=8020'
