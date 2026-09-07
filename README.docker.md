# Docker usage

The Compose setup in `docker-compose.yml` starts a complete local daloRADIUS stack:

- `radius-mysql`: MariaDB database;
- `radius`: FreeRADIUS;
- `radius-web`: daloRADIUS users and operators web interfaces.

The primary `Dockerfile` also supports a web-only container connected to MariaDB and FreeRADIUS services managed outside this repository.

## Full Compose stack

Create a private environment file from the template:

```bash
install -m 600 .env.example .env
```

Edit `.env` and replace every `CHANGE_ME_...` value:

```dotenv
MYSQL_PASSWORD=CHANGE_ME_RADIUS_DB_PASSWORD
MYSQL_ROOT_PASSWORD=CHANGE_ME_ROOT_DB_PASSWORD
DEFAULT_CLIENT_SECRET=CHANGE_ME_RADIUS_SHARED_SECRET
```

The Compose stack generates a random internal status secret on first start and
persists it in the `status_secret` volume. To manage or rotate it explicitly,
set a base64/hex value of at least 16 characters, for example:

```bash
openssl rand -hex 32
```

```dotenv
DALORADIUS_STATUS_SECRET=PASTE_THE_GENERATED_VALUE_HERE
```

Placeholder values beginning with `CHANGE_ME_` are rejected.

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

The operators' **Reports → RADIUS Server Status** page checks the configured database connection for MariaDB and probes FreeRADIUS through a dedicated internal `Status-Server` listener on UDP port `18122`. The status listener uses a separate secret file shared only by the RADIUS and web containers, requires a RADIUS `Message-Authenticator`, is restricted to the Docker network, and is not published to the host. The secret is passed to `radclient` through its `-S` file option rather than through process arguments. The regular NAS/client secret and port `1812` remain reserved for the operator **Test User Connectivity** feature; restrict that NAS entry to the Docker web network and never authorize `0.0.0.0/0`. SSHd is shown as not applicable in the web container.

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

Existing Compose deployments do not need to add a status secret before
upgrading: it is generated in the new `status_secret` volume. If the old
`.env` already defines `DALORADIUS_STATUS_SECRET`, that value is imported into
the volume. Remove it after the first successful start to keep the secret out
of future container environments. To rotate it, set a new value and run
`docker compose up -d --force-recreate radius radius-web`; after both services
are healthy, remove the variable from `.env` again.

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

## Web-only container with external services

The primary `Dockerfile` can run only the daloRADIUS web interfaces while using an existing MariaDB database and FreeRADIUS server.

Build the same web image used by the Compose stack:

```bash
docker build -t daloradius-web .
```

Create an environment file for the external services and restrict its permissions:

```dotenv
MYSQL_HOST=db.example.com
MYSQL_PORT=3306
MYSQL_DATABASE=radius
MYSQL_USER=radius
MYSQL_PASSWORD=CHANGE_ME_RADIUS_DB_PASSWORD
DEFAULT_FREERADIUS_SERVER=radius.example.com
DEFAULT_FREERADIUS_PORT=1812
DEFAULT_CLIENT_SECRET=CHANGE_ME_RADIUS_SHARED_SECRET
DALORADIUS_STATUS_MODE=network
DALORADIUS_STATUS_SERVER=radius.example.com
DALORADIUS_STATUS_PORT=18122
DALORADIUS_STATUS_SECRET_FILE=/run/secrets/daloradius_status_secret
```

```bash
chmod 600 daloradius-web.env
```

Start the web container:

```bash
docker run --name daloradius-web \
  --env-file ./daloradius-web.env \
  -v ./daloradius_status_secret:/run/secrets/daloradius_status_secret:ro \
  -v daloradius-data:/data \
  -p 80:80 \
  -p 127.0.0.1:8000:8000 \
  -d daloradius-web
```

Create `daloradius_status_secret` with mode `0600`; its base64/hex value must also be configured on the external FreeRADIUS server with `Message-Authenticator` enforcement. Do not reuse the regular NAS secret. `DEFAULT_CLIENT_SECRET` remains reserved for the operators UI connectivity test on the regular authentication listener. The external database must already contain the FreeRADIUS and daloRADIUS schemas; review `contrib/db/migrations/` when connecting a deployment created by an older release.
