# Proyecto ju-app - Configuración de Entorno Local

## Configuración de lando

El primer paso es intalar [Lando](https://docs.lando.dev/basics/installation.html).

Lando Es una herramienta DevOps y un entorno de desarrollo local multiplataforma, de código abierto y gratuito, construida sobre la tecnología de contenedores Docker y desarrollada por Tandem. Diseñado para trabajar con la mayoría de los lenguajes, marcos y servicios principales, Lando proporciona una manera fácil para que los desarrolladores de todos los niveles de habilidad especifiquen requisitos simples o complejos para sus proyectos y luego se pongan a trabajar rápidamente en ellos.

Después de clonar el repositorio se debe correr el comando:

```
lando start
```
Este comando creará el contenedor e instalará los paquetes necesarios para correr Drupal en un entorno local.

Cuando finalice el proceso de instalación se generará una URL local `http://ju-app.lndo.site/` esta será la url local.

## Permiso de Directorios

A continuación se listan los permisos para directorios necesarios para futuras actualizaciones de composer

```
chmod 0666 sites/default/settings.php
chmod 0777 sites/default/files
```
## Configuración de Base de datos

Para desarrollo local la base de datos tiene la siguiente configuración.

```
database: drupal9
username: drupal9
password: drupal9
host: database
# for mysql
port: 3306
```

Para su primer importación en local se debe correr el siguiente comando, antes debe crearse la carpeta dumps en el directorio raíz, este de facto esta ignorado en el .gitignore:
```
lando db-import dumps/xxx.*sql
```

Para mayor información se recomienda leer la documentación oficial.

* [MySql](https://docs.lando.dev/config/mysql.html#configuration).
* [SQL-Import](https://docs.lando.dev/guides/db-import.html).
* [SQL-Export](https://docs.lando.dev/guides/db-export.html).

## Comandos Drush

Drush es un shell de línea de comandos y una interfaz de scripting Unix para Drupal. Drush core incluye muchos comandos útiles para interactuar con código como módulos / temas / perfiles. De manera similar, ejecuta update.php, ejecuta consultas SQL y migraciones de bases de datos, y diversas utilidades como ejecutar cron o borrar caché. A los desarrolladores les encanta el comando generate, que inicia su proyecto de codificación escribiendo archivos PHP y YML listos para personalizar.

Para ejecutar comandos Drush en el entorno local debe anteponerse el prefijo `lando` por ejemplo para limpiar el caché se ejecutaría `lando drush cr` para activar un módulo `lando drush en modulo`..
