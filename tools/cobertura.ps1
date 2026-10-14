param([string]$XdebugDll)
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)
if (-not (Test-Path 'vendor/autoload.php')) { throw 'Primero ejecuta composer install.' }
$taskPhpArgs = @()
if (-not $XdebugDll -and (Test-Path '.tools/coverage/xdebug.dll')) {
    $XdebugDll = (Resolve-Path -LiteralPath '.tools/coverage/xdebug.dll').Path
}
if ($XdebugDll) {
    $taskDllPath = (Resolve-Path -LiteralPath $XdebugDll).Path
    $taskPhpArgs += @('-d', "zend_extension=$taskDllPath")
}
$taskPhpArgs += @('-d','xdebug.mode=coverage','-d','pcov.enabled=1')
New-Item -ItemType Directory -Force -Path 'coverage' | Out-Null
& php @taskPhpArgs vendor/bin/phpunit --coverage-clover coverage/clover.xml --coverage-text --log-junit coverage/phpunit-junit.xml
if ($LASTEXITCODE -ne 0) { throw 'No se pudo generar cobertura. Comprueba Xdebug o PCOV y las pruebas.' }
if (-not (Test-Path 'coverage/clover.xml')) { throw 'No se genero el reporte Clover.' }
# Convertir SOLO rutas de archivos al proyecto relativo para importarlo desde GitLab/Linux.
[xml]$taskClover = Get-Content -LiteralPath 'coverage/clover.xml' -Raw
$taskRoot = (Get-Location).Path.Replace('\','/').TrimEnd('/') + '/'
foreach ($taskFile in $taskClover.SelectNodes('//file[@name]')) {
    $taskName = $taskFile.name.Replace('\','/')
    if (-not $taskName.StartsWith($taskRoot,[StringComparison]::OrdinalIgnoreCase)) { throw 'Clover contiene una ruta fuera del proyecto.' }
    $taskFile.SetAttribute('name',$taskName.Substring($taskRoot.Length))
}
$taskClover.Save((Join-Path (Get-Location).Path 'coverage/clover.xml'))
[xml]$taskJUnit = Get-Content -LiteralPath 'coverage/phpunit-junit.xml' -Raw
foreach ($taskNode in $taskJUnit.SelectNodes('//*[@file]')) {
    $taskName = $taskNode.file.Replace('\','/')
    if ($taskName.StartsWith($taskRoot,[StringComparison]::OrdinalIgnoreCase)) { $taskNode.SetAttribute('file',$taskName.Substring($taskRoot.Length)) }
}
$taskJUnit.Save((Join-Path (Get-Location).Path 'coverage/phpunit-junit.xml'))
Write-Host 'Cobertura real disponible en coverage/clover.xml. Se incluye en Git; no cambies sus contadores.'
