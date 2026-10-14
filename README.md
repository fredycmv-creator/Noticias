# Pulso Noticias

Producto Académico 02 · Pruebas y Calidad de Software · NRC 30180.
Proyecto individual identificado para Coronacion Meza Fredy, código 76083502.

Blog autoadministrable en PHP con Laravel 12, Blade, CSS local y SQLite. Incluye
lectura pública, búsqueda, categorías predefinidas, paginación, acceso editorial,
creación, edición, borradores, publicación y eliminación con confirmación.
No utiliza JavaScript, servicios externos, Docker ni una compilación de Node.

## Requisitos

PHP 8.2 o superior con PDO SQLite, mbstring, curl, DOM, OpenSSL y las extensiones
habituales de Laravel. Composer 2. En esta PC se utilizó `C:\xampp\php\php.exe`.
No hace falta iniciar Apache o MySQL para usar `php artisan serve`.

## Preparar una copia extraída del ZIP

Abre esta carpeta en VS Code y ejecuta en PowerShell:

```powershell
.\tools\preparar.ps1
php artisan serve --host=127.0.0.1 --port=8020
```

La preparación instala las versiones de `composer.lock` si faltan, crea `.env`,
una clave local y SQLite, aplica migraciones y añade la demo sin borrar registros.
Una segunda ejecución conserva la clave, el contenido y las credenciales existentes.
La instalación de dependencias requiere Internet y acceso a sus registros.

Si PowerShell impide ejecutar el archivo, realiza los pasos equivalentes:

```powershell
composer install --no-interaction --prefer-dist
Copy-Item .env.example .env
New-Item database/database.sqlite -ItemType File
php artisan key:generate
php artisan migrate
php artisan blog:demo
php artisan serve --host=127.0.0.1 --port=8020
```

Los comandos `Copy-Item` y `New-Item` son solo para una copia nueva. No reemplaces
la configuración ni la base de una instalación existente.

Visita http://127.0.0.1:8020. Acceso editorial ficticio, exclusivamente local:
`admin@blog.test` / `DemoNoticias!2026`. El seeder de demo rechaza entornos distintos
de `local` y `testing`. No publiques esas credenciales en un servicio operativo.

## Ejecutar pruebas

En otra terminal de la misma carpeta:

```powershell
php artisan test
php artisan test --filter=cp20
php artisan test --filter=cp23
php artisan test --log-junit reports/phpunit-junit.xml
```

## Evaluación automatizada Quantum

El PDF nuevo indica GitLab en https://gitlab.quantumdevops.online y SonarQube en
https://sonar.quantumdevops.online. `.gitlab-ci.yml` incluye exactamente
`root/laboratorio-quality` y `/gitlab/quality-pipeline.yml`; `project.yml` identifica
este blog, la actividad individual, el curso exacto y el peso del parcial del 80 %.
La cuenta del portal nuevo debe identificarse con el código de estudiante.

ANTES del primer push hay que completar el correo real del docente:

```powershell
.\tools\configurar_docente.ps1 -Correo "CORREO_REAL@continental.edu.pe"
```

El texto `PENDIENTE_CORREO_DOCENTE` no es una dirección y no debe enviarse al
pipeline. Ese dato no hace falta para ejecutar la web o las pruebas locales.
Cada push puede generar una evaluación y correos; no se ha realizado ninguno.

Para regenerar la cobertura con Xdebug o PCOV disponible:

```powershell
.\tools\cobertura.ps1
# Si Xdebug no está cargado: usa el DLL oficial compatible con tu versión de PHP.
.\tools\cobertura.ps1 -XdebugDll "C:\ruta\al\xdebug.dll"
```

El reporte real se guarda en `coverage/clover.xml`, junto al JUnit. Ambos se
incluyen en Git y en el ZIP. El módulo temporal usado en esta PC está ignorado
en `.tools/` y no se distribuye; no se modifica php.ini. Fuente oficial:
https://xdebug.org/download. SonarQube admite Clover para PHP mediante
`sonar.php.coverage.reportPaths`:
https://docs.sonarsource.com/sonarqube-server/analyzing-source-code/test-coverage/php-test-coverage.

No se excluyen carpetas de código ni líneas de cobertura en Sonar. Los archivos
de pruebas se clasifican como pruebas; dependencias, cachés y secretos se omiten
mediante `.gitignore`. No subas vendor, node_modules, venv ni .env. La nota de
SonarQube y su cobertura global solo se conocen al ejecutar el pipeline real.

El primer comando muestra las 36 pruebas con marcas de aprobado o fallo y su total.
Las pruebas usan SQLite **en memoria** y no borran las noticias de demostración.
CP20 comprueba títulos duplicados y CP23 contenido distinto del resumen.

Con el servidor iniciado y los datos de demo disponibles:

```powershell
php tools/verificar_http.php
```

Este último verifica 14 puntos mediante HTTP real, incluido CSRF. Crea una noticia
ficticia temporal, la publica y elimina al completar el recorrido. También mide
10 peticiones secuenciales a la portada; no simula concurrencia ni usuarios reales.
Si se interrumpe, elimina desde el panel únicamente el registro `Prueba HTTP ...`
de esa ejecución, si quedó guardado.

También están disponibles tareas de VS Code en **Terminal > Ejecutar tarea**.

## Qué contiene el proyecto

- `app/Http/Requests/PostRequest.php`: reglas y mensajes en español.
- `app/Http/Controllers`: acceso, consulta pública y administración.
- `app/Models`: relaciones, normalización del título y estado de publicación.
- `resources/views`: páginas Blade con etiquetas, errores y navegación semántica.
- `public/css/blog.css`: diseño adaptable y foco visible sin fuentes remotas.
- `database/migrations`: esquema reproducible; `database/seeders`: demo ficticia.
- `tests/Feature/BlogFeatureTest.php`: CP01–CP36, relacionados con el informe.
- `tools/verificar_http.php`: recorrido real H01–H14 y medición local.

La administración de categorías, registro público, recuperación de contraseña,
comentarios, archivos multimedia y despliegue productivo están fuera de este alcance.
Las categorías iniciales son Tecnología, Educación y Comunidad.

## Procedencia y asistencia

Se reutilizó del laboratorio Laravel L18 únicamente el esqueleto del framework,
configuraciones, `UserFactory` y dependencias fijadas en `composer.lock`.
El dominio de noticias, vistas, controles y 36 pruebas corresponden a este PA02;
no se copió el CRUD de cursos ni su base de datos. Laravel y su esqueleto son MIT;
las licencias de dependencias se detallan en `LICENCIAS_DEPENDENCIAS.json`.

Se utilizó asistencia de Codex para generar y revisar código, casos de prueba,
documentación y evidencias locales. El estudiante debe revisar, comprender y
validar esta versión antes de entregarla, y declarar esa asistencia según las
indicaciones de la asignatura. No se ha enviado al aula virtual ni a GitLab.


## Paletas de color

El selector superior ofrece Claro, Oscuro, Rojo-verde (azul/naranja) y
Azul-amarillo (violeta/verde). Elige una opción y pulsa Aplicar. Funciona sin
JavaScript, conserva filtros y permanece al navegar durante la sesión.
Las dos opciones alternativas evitan esas parejas de color como distinción
principal; son preferencias orientativas, no una corrección ni una garantía
para todas las personas con un tipo de daltonismo. Se conservan los nombres
de estados y mensajes escritos. Los pares de texto principales se comprobaron
con contraste mínimo 4,5:1; foco y bordes de campos, con 3:1.
Referencias: https://www.w3.org/WAI/WCAG22/Understanding/use-of-color.html
y https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html.
