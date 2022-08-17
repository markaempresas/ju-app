# ju-app
Herramienta Diagnóstico Jornada Única


<h2>Pasos para la instalación:</h2>

1. Clonar el repositorio en el directorio del servidor
1.1 Descargar el sql de la base de datos instrumentoju_ju.sql
2. Hacer importación de la base de datos instrumentoju_ju.sql en la base de datos creada para la aplicación
3. En la raiz del sitio Vía SSH correr el comando "composer install"
4. Modificar el arvhivo web/sites/default/settings.php con los accesos de la base de datos del servidor, esto establece la conexión de la aplicación con la base de datos
5. Abrir en un navegador web la url de la aplicación
6. Correr el comando "drush cr" para borrar y construir cache de la aplicación

<h2>Permisos de directorios</h2>

chmod 0666 sites/default/settings.php<br>
chmod 0777 sites/default/files

