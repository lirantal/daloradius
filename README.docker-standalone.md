# Docker usage

The recommended Docker setup for local or self-contained deployments is the full Compose stack in `docker-compose.yml`. It starts three services:

- `radius-mysql`: MariaDB database for daloRADIUS and FreeRADIUS.
- `radius`: FreeRADIUS server.
- `radius-web`: daloRADIUS web interfaces.

Use the standalone web image only when you already have an external database and FreeRADIUS server configured.

## Full Compose stack

### Prerequisites

- Docker with the Compose plugin.
- Available host ports `80`, `1812/udp`, and `1813/udp`.
- Available host bind `127.0.0.1:8000` unless you override `DALORADIUS_OPERATORS_BIND`.

### Prepare environment

Copy the commented template, uncomment the required variables, and replace every `CHANGE_ME_...` value before starting the stack:

```bash
cp .env.example .env
```

Required variables:

- `MYSQL_PASSWORD`
- `MYSQL_ROOT_PASSWORD`
- `DEFAULT_CLIENT_SECRET`
- `DALORADIUS_ADMIN_PASSWORD`
- `FREERADIUS_SQL_TLS`

For local or non-TLS Compose setups, set:

```dotenv
FREERADIUS_SQL_TLS=disabled
```

Use `FREERADIUS_SQL_TLS=require` only when database TLS is configured.

The operators UI bind is optional and defaults to localhost:

```dotenv
DALORADIUS_OPERATORS_BIND=127.0.0.1:8000
```

Optional timezone and mail settings can also be set in `.env`:

```dotenv
TZ=Europe/Vienna
MAIL_SMTPADDR=127.0.0.1
MAIL_PORT=25
MAIL_FROM=root@daloradius.xdsl.by
MAIL_AUTH=
```

### Validate configuration

Validate the Compose configuration and required environment variables:

```bash
docker compose config
```

If a required variable is missing, Compose exits with an error that names it.

### Import an existing database backup

When importing a database from a previous non-Docker daloRADIUS or FreeRADIUS instance, import the dump before starting `radius` and `radius-web`. Legacy dumps can contain older table definitions, so run the post-import migrations before starting the full stack.

Example for `backup_radius_14-05-2026.sql.gz`:

```powershell
.\scripts\docker\import-backup.ps1 -DumpPath .\backup_radius_14-05-2026.sql.gz
```

Linux equivalent:

```bash
bash scripts/docker/import-backup.sh ./backup_radius_14-05-2026.sql.gz
```

The runners live at `scripts/docker/import-backup.ps1` and `scripts/docker/import-backup.sh`. They perform this sequence:

- validates `.env` through `docker compose config --quiet`
- validates the dump with `gzip -t`
- starts only `radius-mysql`
- imports the dump into the `radius` database
- applies SQL files from `docker/post-import-migrations`
- builds `radius-web` and hashes imported operator passwords
- validates required tables and critical schema widths
- starts the complete stack

#### Post-import migrations

Post-import migrations handle schema drift from older dumps. The current required migration widens `operators.password` to `VARCHAR(95)` because current daloRADIUS operator passwords are stored with PHP `password_hash()` values. Older dumps may define that column as `VARCHAR(32)`, which prevents `radius-web` from setting `DALORADIUS_ADMIN_PASSWORD`.

After SQL migrations, the runner converts only daloRADIUS operator passwords. Legacy non-hash operator values are hashed with PHP `password_hash()`. If an `administrator` operator exists, its password is set from `DALORADIUS_ADMIN_PASSWORD` in `.env`. This step does not rewrite daloRADIUS user portal passwords or FreeRADIUS `radcheck` authentication attributes.

Manual equivalent:

```powershell
$mariadb = @'
set -eu
defaults_file="$(mktemp)"
cleanup() {
    rm -f "$defaults_file"
}
trap cleanup EXIT
{
    printf "[client]\n"
    printf "user=radius\n"
    printf "password=%s\n" "$MYSQL_PASSWORD"
} > "$defaults_file"
chmod 600 "$defaults_file"
mariadb --defaults-extra-file="$defaults_file" --batch --skip-column-names radius
'@

docker compose stop radius radius-web
docker compose up -d radius-mysql
gzip -cd .\backup_radius_14-05-2026.sql.gz | docker compose exec -T radius-mysql sh -lc $mariadb
Get-Content -Raw .\docker\post-import-migrations\001-operators-password-width.sql | docker compose exec -T radius-mysql sh -lc $mariadb
docker compose build radius-web
docker compose run --rm --no-deps --entrypoint php radius-web /usr/local/bin/daloradius-hash-imported-passwords.php
docker compose up -d --build
```

The MariaDB command uses a temporary client defaults file inside the database container so `MYSQL_PASSWORD` is not passed as a process argument.

### Start the stack

Build images and start the services:

```bash
docker compose up -d --build
```

Check service state:

```bash
docker compose ps
```

### Access services

- Users UI: `http://localhost/`
- Operators UI: `http://127.0.0.1:8000/`, unless `DALORADIUS_OPERATORS_BIND` is overridden.
- RADIUS authentication: host UDP port `1812`
- RADIUS accounting: host UDP port `1813`

MySQL port `3306` is internal to the Compose network and is not published to the host.

### Logs

Container logs:

```bash
docker compose logs radius-web
docker compose logs radius
docker compose logs radius-mysql
```

Follow logs:

```bash
docker compose logs -f radius-web radius radius-mysql
```

Useful application log paths inside the containers:

- FreeRADIUS: `/var/log/freeradius/radius.log`
- daloRADIUS: `/var/www/daloradius/var/log/daloradius.log`

The Compose stack shares the FreeRADIUS log directory through the `radius_logs` volume. Host syslog and boot logs shown by the web container are placeholder files; use `docker compose logs radius-web` for container-level output.

### Stop and rebuild

Stop the stack without deleting volumes:

```bash
docker compose down
```

Rebuild images after Dockerfile or dependency changes:

```bash
docker compose build --pull
docker compose up -d
```

To remove containers and named volumes, including database and persisted application data:

```bash
docker compose down -v
```

### Volumes

The Compose stack uses these named volumes:

- `radius_mysql`
- `radius_freeradius_data`
- `radius_daloradius_data`
- `radius_logs`

## Standalone web image

`Dockerfile-standalone` builds only the daloRADIUS web application image. It does not start MariaDB or FreeRADIUS. Use it when those services are already deployed and the daloRADIUS database schema and RADIUS integration are configured externally.

Build the image:

```bash
docker build -t daloradius-standalone -f Dockerfile-standalone .
```

Create a `daloradius.conf.php` for your external database and RADIUS settings, then mount it into the container. Path values in the mounted config should match the standalone container layout, such as `/var/www/html/daloradius/...`.

```bash
docker run --name daloradius-standalone \
  -v /path/to/daloradius.conf.php:/var/www/html/daloradius/common/includes/daloradius.conf.php:ro \
  -p 80:80 \
  -p 127.0.0.1:8000:8000 \
  -d daloradius-standalone
```

The standalone image expects the mounted configuration at `/var/www/html/daloradius/common/includes/daloradius.conf.php`.
