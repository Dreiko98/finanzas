# Finanzas

Aplicación personal para comprobar el plan financiero de un vistazo y anotar lo mínimo una vez por semana. Está hecha con PHP, PDO, HTML, CSS y JavaScript vanilla, sin framework ni proceso de compilación.

## Qué incluye

- Acceso de usuario único con contraseña hasheada, CSRF y bloqueo de 15 minutos tras cinco intentos fallidos.
- Revisión semanal de cinco saldos, progreso del colchón de 3.000 €, confirmación de aportaciones e histórico gráfico.
- Saldo restante de gastos corrientes, ingresos puntuales y resumen mensual sin categorías.
- Objetivos automáticos: 1.700 €/500 €/270 €/30 € hasta diciembre de 2026 y 1.500 €/450 €/230 €/20 € desde enero de 2027.
- Diseño responsive basado en la paleta, tipografía y marca de germanmallo.com.

## Requisitos

- PHP 8.1 o superior.
- PDO con `pdo_sqlite` (recomendado) o `pdo_mysql`.
- Apache con `mod_rewrite` y, preferiblemente, `mod_headers` en producción.
- Permiso de escritura para PHP en `data/` cuando se usa SQLite.

## Instalación

1. Genera `.env` sin escribir la contraseña en ningún fichero versionado:

   ```bash
   FINANZAS_SETUP_EMAIL='tu@email' FINANZAS_SETUP_PASSWORD='tu-contraseña' php bin/setup.php
   ```

   En PowerShell:

   ```powershell
   $env:FINANZAS_SETUP_EMAIL='tu@email'
   $env:FINANZAS_SETUP_PASSWORD='tu-contraseña'
   php .\bin\setup.php
   Remove-Item Env:FINANZAS_SETUP_EMAIL, Env:FINANZAS_SETUP_PASSWORD
   ```

   El comando guarda únicamente el hash bcrypt en `.env`. El archivo está ignorado por Git.

2. Con SQLite no hace falta configurar nada más. La base se crea en `data/finanzas.sqlite` en la primera petición.

3. Si el hosting no permite SQLite, cambia estas claves en `.env`:

   ```dotenv
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=nombre_base
   DB_USER=usuario
   DB_PASSWORD=contraseña
   ```

4. Siempre que sea posible, configura el document root del subdominio para que apunte a `public/`. Si el hosting obliga a usar directamente su raíz —como ocurre en este despliegue de IONOS— utiliza el modo `flat`; la aplicación bloquea el acceso web a `config/`, `src/`, `data/` y `.env`.

Para levantar el proyecto localmente:

```bash
php -S 127.0.0.1:8765 -t public
```

## Pruebas

```bash
php tests/run.php
```

La suite usa una base SQLite en memoria y comprueba los cambios de periodo, el progreso, los semáforos, las actualizaciones por fecha, el resumen mensual y el rate limiting. En la instalación local usada durante el desarrollo, SQLite se activa para el comando con `php -d extension=pdo_sqlite tests/run.php`.

## Despliegue SFTP

El script requiere `lftp` y estas variables, que no deben guardarse en el repositorio:

```bash
export SFTP_HOST='servidor-sftp'
export SFTP_PORT='22'
export SFTP_USER='usuario'
export SFTP_PASSWORD='contraseña'
export SFTP_TARGET='/ruta/remota/finanzas'
./deploy.sh
```

Por defecto se usa `SFTP_LAYOUT=flat`: el contenido público se coloca directamente en el document root y `src/`, `config/` y `data/` quedan protegidos mediante `.htaccess`. Es la disposición utilizada en IONOS para este proyecto. Si el panel permite apuntar el document root a una subcarpeta `public/`, se puede definir `SFTP_LAYOUT=parent`.

Cuando la cuenta SFTP ya está limitada a la raíz exacta del subdominio, usa `SFTP_TARGET=/` junto con `SFTP_ALLOW_ROOT=yes`. El script no elimina archivos remotos, no sube `.env` y nunca sustituye una base SQLite.

En el primer despliegue hay que crear `.env` en el servidor y confirmar que `data/` sea escribible por PHP. Antes de cambios de infraestructura, descarga una copia de la base de datos como respaldo.

No se incluyen credenciales SFTP reales; el script queda listo para activarse cuando estén disponibles.
