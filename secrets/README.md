# Secrets — daloRADIUS Docker

This directory contains secrets used by Docker Compose via Docker Secrets.

## Structure

Secrets are organized in subdirectories per Docker service:

```
secrets/
├── README.md
├── db/                          ← MariaDB / database secrets
│   ├── .gitkeep
│   ├── mysql_root_password      ← MariaDB root password
│   ├── mysql_password           ← radius app DB password
├── freeradius/                  ← FreeRADIUS secrets (future use)
│   └── .gitkeep
└── daloradius/                  ← daloRADIUS web secrets
    ├── .gitkeep
    └── daloradius_client_secret ← RADIUS shared secret (NAS client)
```

## Secret files

Each file under `secrets/db/` contains a single line with the secret value.
The init scripts (`read_secret_or_env()`) read from the secret file first,
falling back to environment variables if the file is absent.

> **Note:** Secret-file mounting via Docker Compose (`secrets:` + `file:`
> paths) is wired into the standalone compose files.
> Use `setup/install-docker-compose.sh` to generate everything automatically.

### Required files (created by the installer)

The root `docker-compose.yml` is **generated on the fly** by
`setup/install-docker-compose.sh`. Do not create it manually.

Secret files are created in their respective directories:

- `secrets/db/mysql_root_password`
- `secrets/db/mysql_password`
- `secrets/daloradius/daloradius_client_secret`

Run `setup/install-docker-compose.sh` to auto-generate all of the above.

## Auto-generation

Run `setup/install-docker-compose.sh` from the project root:

```bash
cd /path/to/daloradius
./setup/install-docker-compose.sh
```

It will:
- Prompt to auto-generate or manually enter each secret
- Generate SSL certificates (if TLS is enabled)
- Create data directories
- Start the full Docker stack

## Security

- **Never commit real secrets**: `.gitignore` ignores everything under `secrets/` except `.gitkeep` and `README.md`.
- **Permissions**: `setup/install-docker-compose.sh` sets `chmod 600` on secret files.
- **Rotation**: to rotate a secret on an active deployment:
  1. Update the secret file in `secrets/db/<name>`
  2. Restart the service: `docker compose restart <service>`
  3. If rotating the root DB password, also update the `.env` value before restarting MariaDB
  Deleting and re-running the installer on an active deployment will break the MariaDB health check.

## How it works in docker-compose.yml

The init scripts (`init.sh`, `init-freeradius.sh`) include a
`read_secret_or_env()` function that checks `/run/secrets/<name>` first,
then falls back to environment variables with the same name.
MariaDB&#39;s official image supports `*_FILE` suffixed variables natively;
the init scripts do not — they read the matching env var directly.

The standalone compose files now mount Docker Secrets via:
- `secrets:` section in each service
- `MYSQL_ROOT_PASSWORD_FILE`, `MYSQL_PASSWORD_FILE`, `DEFAULT_CLIENT_SECRET_FILE` set to `/run/secrets/<name>`

> **⚠️ Note on duplicate secret definitions:**
> `MYSQL_PASSWORD` is defined in both `docker/mariadb/docker-compose.yml` and
> `docker/freeradius/docker-compose.yml` (same file source, same content).
> This produces a harmless Compose warning ("duplicate resource"), but it is
> intentional: each service mounts the secret independently via `secrets:`
> so the password is **never exposed as an environment variable**.
>
> **Security over clean logs.** An env var would leak the password into
> `docker inspect`, logs, and process listings. A file-backed secret
> (`/run/secrets/MYSQL_PASSWORD`) is readable only by the container user.
> The warning is cosmetic — the running services are unaffected.

See `docker/*/docker-compose.yml` for the current wiring.
