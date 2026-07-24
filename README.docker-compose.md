# daloRADIUS Docker Compose

The Compose setup in `docker-compose.yml` starts a complete daloRADIUS stack:

- `radius-mysql`: MariaDB database;
- `radius`: FreeRADIUS;
- `radius-web`: daloRADIUS users and operators web interfaces.

For the standalone image (single container with external DB/RADIUS), see `README.docker-standalone.md`.

### EAP/TLS Certificates

When using the Docker Compose stack (`setup/install-docker-compose.sh`), FreeRADIUS auto-generates self-signed TLS/EAP certificates on first start. These are intended for **development and testing only** and are **not recommended for production**.

For production, mount your own CA-signed certificates (e.g. Let's Encrypt, internal PKI) via Docker bind-mount volumes. See `doc/setup/docker-compose.md` → section **EAP/TLS Certificates** for detailed instructions on both auto-generated and external certificate workflows.

## Full Compose stack

Create an environment file from the template:

```bash
cp .env.example .env
```

Edit `.env` with your deployment values. All passwords are stored exclusively
in `secrets/` files (mounted as Docker Secrets), NOT in `.env`. The `.env` file
only contains non-sensitive configuration:

```dotenv
# Connection settings
MYSQL_HOST=radius-mysql
MYSQL_PORT=3306
MYSQL_HOST_PORT=3306
MYSQL_USER=radius
MYSQL_DATABASE=radius

# RADIUS server
FREERADIUS_SQL_TLS=disabled

# UI binding
DALORADIUS_OPERATORS_BIND=0.0.0.0:8000

# Timezone
TZ=Europe/Vienna
```

> **`DALORADIUS_OPERATORS_BIND=0.0.0.0:8000`**: binds the admin panel to all
> interfaces so it is accessible remotely. This is the default because the
> Compose stack is designed for server deployments. Change to `127.0.0.1:8000`
> to restrict access to localhost only.
>
> **`MYSQL_HOST_PORT`**: controls the host port for MariaDB (default `3306`).
> MariaDB is exposed on all interfaces by default (`0.0.0.0`), which is
> suitable for development and for debugging with external tools. For production
> environments where direct database access from outside the host is not needed,
> restrict it to localhost by setting `MYSQL_HOST_PORT=127.0.0.1:3306` in your `.env`.

> **Passwords are stored in `secrets/db/` and `secrets/daloradius/` and mounted**
> as Docker Secrets (`/run/secrets/*`). The `MYSQL_PASSWORD` and
> `DEFAULT_CLIENT_SECRET` env vars listed in the compose files are never
> used when secrets are mounted — they exist only for documentation and
> for compatibility when running without secrets.
> See the compose file comments for details.

Optional values can be kept as-is for a local setup:

```dotenv
TZ=Europe/Vienna
DALORADIUS_OPERATORS_BIND=0.0.0.0:8000
MYSQL_HOST_PORT=3306
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
- operators UI: `http://<server-ip>:8000/`, or `http://127.0.0.1:8000/` if accessing locally

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

