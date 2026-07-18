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
Docker Compose mounts these files into `/run/secrets/<name>` inside each
container. The init scripts (`read_secret_or_env()`) read from the secret
file first, falling back to environment variables if the file is absent.

### Placeholder files (committed)

The following placeholder files are committed to the repository so that
`docker compose` can resolve the `file:` paths in standalone compose files
without failing:

- `secrets/db/mysql_root_password`
- `secrets/db/mysql_password`
- `secrets/db/daloradius_client_secret`

**Replace these with real secrets before production use.**

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
- **Rotation**: to regenerate a secret, delete the file and re-run `setup/install-docker-compose.sh`.

## How it works in docker-compose.yml

The init scripts (`init.sh`, `init-freeradius.sh`) include a
`read_secret_or_env()` function that checks `/run/secrets/<name>` first,
then falls back to environment variables. Currently, the compose files
pass values via environment variables. To enable Docker Secrets:

1. Uncomment the `secrets:` section in each `docker/*/docker-compose.yml`
2. Ensure `secrets/db/*` files exist with real values
3. Add `secrets:` to each service

This is planned for a future PR.
