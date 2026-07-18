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
│   └── daloradius_client_secret ← RADIUS shared secret (NAS client)
├── freeradius/                  ← FreeRADIUS secrets (future use)
│   └── .gitkeep
└── daloradius/                  ← daloRADIUS web secrets (future use)
    └── .gitkeep
```

## Secret files

Each file under `secrets/db/` contains a single line with the secret value.
The init scripts (`read_secret_or_env()`) read from the secret file first,
falling back to environment variables if the file is absent.

> **Note:** Secret-file mounting via Docker Compose (`docker-secret:` + `file:`
> paths) is wired into the standalone compose files. The files listed below
> are **not committed** — they must be created locally before running Compose.
> Use `setup/install-docker-compose.sh` to generate them automatically.

### Required files (must be created locally)

These paths are not committed. Create each file with a real secret value before
running a standalone Compose file:

- `secrets/db/mysql_root_password`
- `secrets/db/mysql_password`
- `secrets/db/daloradius_client_secret`

Run `setup/install-docker-compose.sh` to auto-generate all three.

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
then falls back to `*_FILE` environment variables.

The standalone compose files now mount Docker Secrets via:
- `secrets:` section in each service
- `MYSQL_ROOT_PASSWORD_FILE`, `MYSQL_PASSWORD_FILE`, `DEFAULT_CLIENT_SECRET_FILE` set to `/run/secrets/<name>`

See `docker/*/docker-compose.yml` for the current wiring.
