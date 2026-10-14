param([Parameter(Mandatory=$true)][string]$Correo)
$ErrorActionPreference = 'Stop'
$taskEmail = $Correo.Trim()
if ($taskEmail -notmatch '^[A-Za-z0-9._%+-]+@continental\.edu\.pe$') { throw 'Usa el correo institucional confirmado del docente.' }
$taskProject = Join-Path (Split-Path -Parent $PSScriptRoot) 'project.yml'
$taskYaml = Get-Content -LiteralPath $taskProject -Raw
if (-not $taskYaml.Contains('PENDIENTE_CORREO_DOCENTE')) { throw 'El campo ya esta configurado; revisa project.yml antes de cambiarlo.' }
[IO.File]::WriteAllText($taskProject,$taskYaml.Replace('PENDIENTE_CORREO_DOCENTE',$taskEmail),[Text.UTF8Encoding]::new($false))
Write-Host 'Correo del docente configurado. No se envio ningun correo ni se subio el proyecto.'
