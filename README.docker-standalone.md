# Docker usage

The Compose setup in `docker-compose.yml` starts a complete local daloRADIUS stack:

- `radius-mysql`: MariaDB database;
- `radius`: FreeRADIUS;
- `radius-web`: daloRADIUS users and operators web interfaces.

Use `Dockerfile-standalone` only when MariaDB and FreeRADIUS are already managed outside this repository.

## Full Compose stack

Create an environment file from the template:

```bash
cp .env.example .env
```

Edit `.env` and replace every `CHANGE_ME_...` value:

```dotenv
MYSQL_PASSWORD=CHANGE_ME_RADIUS_DB_PASSWORD
MYSQL_ROOT_PASSWORD=CHANGE_ME_ROOT_DB_PASSWORD
DEFAULT_CLIENT_SECRET=CHANGE_ME_RADIUS_SHARED_SECRET
```

Optional values can be kept as-is for a local setup:

```dotenv
TZ=Europe/Vienna
DALORADIUS_OPERATORS_BIND=127.0.0.1:8000
MYSQL_HEALTH_START_PERIOD=10m
FREERADIUS_SQL_TLS=disabled
MAIL_SMTPADDR=127.0.0.1
MAIL_PORT=25
MAIL_FROM=root@daloradius.example.com
MAIL_AUTH=
```

Validate the Compose file and environment:

```bash
docker compose config --quiet
```

Build and start the stack:

```bash
docker compose up -d --build
```

Check service state:

```bash
docker compose ps
```

Access the web interfaces:

- users UI: `http://localhost/`
- operators UI: `http://127.0.0.1:8000/`, unless `DALORADIUS_OPERATORS_BIND` is changed

The initial operator account seeded by the default schema is:

```text
username: administrator
password: radius
```

Use this account only for the first login, then change the operator password from the operators UI.

RADIUS authentication and accounting listen on host UDP ports `1812` and `1813`.

MariaDB data remains in `./data/mysql`, FreeRADIUS init state remains in `./data/freeradius`, and daloRADIUS init state remains in `./data/daloradius`.


## Database migrations for upgrades

Fresh Docker deployments initialize the database from the bundled schema. When upgrading an existing Docker deployment, check `contrib/db/migrations/` in the updated source tree and apply the relevant SQL migrations before using newly added features.

For example, to apply the operator MFA migration from the directory that contains `docker-compose.yml`:

```bash
docker compose exec -T radius-mysql sh -lc 'mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  < contrib/db/migrations/2026-06-operator-totp-mfa.sql
```

## Import an existing database backup

To initialize a new Docker stack from an existing MariaDB dump, copy one or more `.sql` or `.sql.gz` files into `./var/backup` before the first startup:

```bash
mkdir -p var/backup
cp /path/to/backup.sql.gz var/backup/
docker compose up -d --build
```

The `radius-mysql` container mounts `./var/backup` as `/docker-entrypoint-initdb.d`, so MariaDB imports those files automatically when `./data/mysql` is empty. After the import, the daloRADIUS and FreeRADIUS containers detect the existing schema and skip their default schema imports.

Large dumps can take several minutes to import. `MYSQL_HEALTH_START_PERIOD` controls how long Docker ignores failing MariaDB healthchecks during first startup; increase it in `.env` if your hardware or dump size requires more time.

This automatic import only runs during MariaDB first initialization. To replace an already initialized Docker database, stop the stack, back up any data you need to keep, remove `./data/mysql`, place the desired dump in `./var/backup`, and start the stack again:

```bash
docker compose down
rm -rf ./data/mysql
mkdir -p var/backup
cp /path/to/backup.sql.gz var/backup/
docker compose up -d --build
```

## Logs

Use Docker logs for container output:

```bash
docker compose logs -f radius-web radius radius-mysql
```

The FreeRADIUS log is shared with the web container through the `radius_logs` volume so the daloRADIUS operators UI can read `/var/log/freeradius/radius.log`.

## Stop and reset

Stop containers without deleting data:

```bash
docker compose down
```

Remove containers and local database/application state:

```bash
docker compose down
rm -rf ./data
```

## Standalone web image

Build the standalone web image:

```bash
docker build -t daloradius-standalone -f Dockerfile-standalone .
```

Create a `daloradius.conf.php` for your external database and RADIUS settings, then mount it into the container:

```bash
docker run --name daloradius-standalone \
  -v /path/to/daloradius.conf.php:/var/www/html/daloradius/common/includes/daloradius.conf.php:ro \
  -p 80:80 \
  -p 127.0.0.1:8000:8000 \
  -d daloradius-standalone
```
