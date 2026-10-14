# Análisis de Noticias en el laboratorio universitario

Esta copia conserva el código funcional de Pulso Noticias y configura el análisis
en GitLab http://192.168.0.200 mediante el trabajo `quality` de
`root/laboratorio-devops`. SonarQube está en http://192.168.0.201:9000 y la clave
individual del proyecto es `FredyC-noticias`.

El análisis usa el runner `lab` y la conexión ya definida por el laboratorio.
Este repositorio no contiene tokens. El plan generado en `metadata/builder/`
identifica el proyecto y obliga a analizar el checkout actual, sin recurrir a
otro proyecto en caché.

`sonar-project.properties` configura los reportes reales de PHPUnit/Clover de
`coverage/`. Las dependencias, .env, SQLite y el módulo local de cobertura se
excluyen de Git. La plantilla central puede sobrescribir parámetros del scanner;
revisar el log final para confirmar cobertura y clasificación de pruebas PHP.

Esta configuración corresponde al laboratorio universitario. Quantum usa otra
instancia y otra plantilla: los resultados no se trasladan automáticamente.
Las instrucciones para ejecutar la aplicación localmente están en README.md.
