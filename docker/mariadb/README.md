# MariaDB — TLS Configuration for daloRADIUS

This directory contains the TLS configuration for the MariaDB (`radius-mysql`) service.

## Files

| File | Purpose |
|------|---------|
| `tls.cnf` | MariaDB server SSL/TLS configuration (mounted to `/etc/mysql/conf.d/tls.cnf`) |
| `docker-compose.yml` | Standalone compose file to run MariaDB independently |

## How TLS Works

1. `tls.cnf` is mounted into the MariaDB container at `/etc/mysql/conf.d/tls.cnf`
2. It references certificates located in `/etc/mysql/certs/` (a named volume `mariadb_certs`)
3. The `mariadb_certs` volume is populated automatically by `setup/install-docker-compose.sh` when `FREERADIUS_SQL_TLS=enabled|require`. For manual setup without the installer, see "Enabling TLS" below.

The `../../docker/mariadb/tls.cnf` paths in the compose file resolve from the compose file directory (`docker/mariadb/`). Do NOT use `--project-directory .` with standalone compose files.

### Certificate paths expected by tls.cnf

```
/etc/mysql/certs/mysql_ca.pem         # Certificate Authority
/etc/mysql/certs/mysql_server.pem     # Server certificate
/etc/mysql/certs/mysql_server.key.pem # Server private key
```

## Enabling TLS

### 1. Set the environment variable

In your `.env` file:

```env
FREERADIUS_SQL_TLS=require
```

### 2. Generate or provide certificates

Place your certificates in `./secrets/db/`:

- `mysql_ca.pem`
- `mysql_server.pem`
- `mysql_server.key.pem`

Then populate the Docker volume. The `mariadb:11.8` image runs the server
as the `mysql` user (UID 999), so the key file must be owned by that UID:

```bash
docker volume create mariadb_certs
docker run --rm -v mariadb_certs:/certs -v "$PWD/secrets/db":/hostsecrets alpine:latest sh -c 'cp /hostsecrets/*.pem /certs/ && chown 999:999 /certs/*key.pem && chmod 644 /certs/*.pem && chmod 600 /certs/*key.pem'
# alpine:latest is intentional — it is a stable, well-maintained base image.
# Pinning to a specific version would require active maintenance to avoid
# using an outdated image with potential security issues.
```

### 3. Uncomment TLS volumes in docker-compose.yml

In `docker/mariadb/docker-compose.yml`, uncomment the TLS volume mounts.
The compose file uses `../../docker/mariadb/tls.cnf` (relative to the compose
file's directory). When running with `--project-directory .`, use:

```yaml
volumes:
  - "../../docker/mariadb/tls.cnf:/etc/mysql/conf.d/tls.cnf:ro"
  - "mariadb_certs:/etc/mysql/certs:ro"
```

> **Note**: The path `../../docker/mariadb/tls.cnf` resolves from the compose
> file location (`docker/mariadb/`). With `--project-directory .`, paths resolve
> from the project root, so use `./docker/mariadb/tls.cnf` instead.

### 4. Start the service

```bash
docker compose -f docker/mariadb/docker-compose.yml up -d
```

## Providing custom certificates

If you have your own CA/signed certificates, place them in `./secrets/db/`:

- `mysql_ca.pem` — your CA certificate
- `mysql_server.pem` — your server certificate
- `mysql_server.key.pem` — your server private key

## Security notes

- **DO NOT commit real certificates** into the repository Git history
- Use `.gitignore` to exclude `secrets/` directory (already configured)
- Self-signed certificates are acceptable for development/testing
- For production, use certificates from a trusted CA or internal PKI
- Certificate rotation: update files in `./secrets/` and re-run the volume population step

## Running standalone

```bash
# Start MariaDB only
docker compose -f docker/mariadb/docker-compose.yml up -d

# Stop MariaDB only
docker compose -f docker/mariadb/docker-compose.yml down

# View logs
docker compose -f docker/mariadb/docker-compose.yml logs -f

# Verify TLS status
docker exec radius-mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "SHOW GLOBAL VARIABLES LIKE 'have_ssl';"
```
