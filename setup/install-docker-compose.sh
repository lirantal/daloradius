#!/usr/bin/env bash
# daloRADIUS Docker Compose installer
# GitHub: https://github.com/lirantal/daloradius
#
# Fusion: upstream v2.3 + conditional TLS + Docker Secrets + data dirs
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SECRETS_DIR="$REPO_ROOT/secrets/db"
TLS_CONF_DIR="$REPO_ROOT/docker/db"
TLS_CONF_FILE="$TLS_CONF_DIR/tls.cnf"

# --- Colored output ---
GREEN='\e[32m'; RED='\e[31m'; YELLOW='\e[33m'; BLUE='\e[34m'; NC='\e[0m'
print_green()  { echo -e "${GREEN}$1${NC}"; }
print_red()    { echo -e "${RED}$1${NC}"; }
print_yellow() { echo -e "${YELLOW}$1${NC}"; }
print_blue()   { echo -e "${BLUE}$1${NC}"; }

# --- Banner ---
echo "============================================"
echo "  daloRADIUS Docker Compose installer"
echo "  Directory: $REPO_ROOT"
echo "============================================"
echo ""

# --- Dependency checks ---
DOCKER_COMPOSE="docker compose"
if ! docker compose version >/dev/null 2>&1; then
  if command -v docker-compose >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
    echo "  Using docker-compose (legacy)."
  else
    echo "Error: neither 'docker compose' nor 'docker-compose' found." >&2
    exit 1
  fi
fi

for cmd in docker openssl; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "Error: $cmd is not installed or not in PATH" >&2
    exit 1
  fi
done

# --- Source .env to read FREERADIUS_SQL_TLS ---
ENV_FILE="$REPO_ROOT/.env"
if [ -f "$ENV_FILE" ]; then
  set -o allexport
  # shellcheck disable=SC1091
  . "$ENV_FILE"
  set +o allexport
fi

FREERADIUS_SQL_TLS="${FREERADIUS_SQL_TLS:-disabled}"
TLS_ENABLED=false
case "$FREERADIUS_SQL_TLS" in
  enabled|require) TLS_ENABLED=true  ;;
  disabled)        TLS_ENABLED=false ;;
  *)
    echo "Error: Invalid FREERADIUS_SQL_TLS='$FREERADIUS_SQL_TLS'. Use 'disabled', 'enabled', or 'require'." >&2
    exit 1
    ;;
esac

echo "  TLS mode: $FREERADIUS_SQL_TLS"
echo ""

# --- Helpers ---
generate_random_string() {
  local len=${1:-20}
  openssl rand -base64 $((len+4)) | tr -dc 'A-Za-z0-9' | cut -c1-"$len"
}

sanitize_file_for_linux() {
  for f in "$@"; do
    [ -f "$f" ] || continue
    grep -q $'\r' "$f" 2>/dev/null && sed -i 's/\r$//' "$f" && echo "  Sanitized CRLF: $f"
    head -c 3 "$f" | grep -q $'\xEF\xBB\xBF' 2>/dev/null && sed -i '1s/^\xEF\xBB\xBF//' "$f" && echo "  Removed BOM: $f"
  done
}

# Escape &, \, and | for use in sed replacement strings
escape_sed_replacement() {
  printf '%s' "$1" | sed -e 's/[&\\|]/\\&/g'
}

prompt_or_generate_secret() {
  local filename="$1"
  local description="$2"
  local filepath="$SECRETS_DIR/$filename"

  if [ -f "$filepath" ]; then
    echo "  $filename already exists, keeping it."
    chmod 600 "$filepath" 2>/dev/null || true
    return 0
  fi

  echo ""
  echo "  Secret '$filename' ($description) not found."
  read -r -p "  Generate automatically? [Y/n] " answer
  if [[ "$answer" =~ ^[Nn] ]]; then
    read -r -s -p "  Enter value for $filename (silent): " custom_val
    echo ""
    echo -n "$custom_val" > "$filepath"
    echo "  Written to $filepath"
  else
    generated="$(generate_random_string)"
    echo -n "$generated" > "$filepath"
    echo "  Generated secret written to $filepath"
  fi
  chmod 600 "$filepath"
}

# ============================================================
# STEP 0: Create data directories for bind mounts
# ============================================================
echo "==> STEP 0: Creating data directories..."
mkdir -p "$REPO_ROOT/data/mysql"
mkdir -p "$REPO_ROOT/data/freeradius"
mkdir -p "$REPO_ROOT/data/daloradius"
mkdir -p "$REPO_ROOT/logs/freeradius"
print_green "  Data directories ready."

# ============================================================
# STEP 1: Secrets
# ============================================================
echo "==> STEP 1: Ensuring Docker Secrets..."
mkdir -p "$SECRETS_DIR"

prompt_or_generate_secret "mysql_root_password"    "MariaDB root password"
prompt_or_generate_secret "mysql_password"         "Application DB password (radius user)"
print_green "  Secrets ready."

# === daloRADIUS client secret (in secrets/daloradius/) ===
DALORADIUS_SECRETS_DIR="$REPO_ROOT/secrets/daloradius"
mkdir -p "$DALORADIUS_SECRETS_DIR"
CLIENT_SECRET_FILE="$DALORADIUS_SECRETS_DIR/daloradius_client_secret"
if [ -f "$CLIENT_SECRET_FILE" ]; then
  echo "  daloradius_client_secret already exists, keeping it."
  chmod 600 "$CLIENT_SECRET_FILE" 2>/dev/null || true
else
  echo ""
  echo "  Secret 'daloradius_client_secret' (RADIUS shared secret) not found."
  read -r -p "  Generate automatically? [Y/n] " answer
  if [[ "$answer" =~ ^[Nn] ]]; then
    read -r -s -p "  Enter value (silent): " custom_val
    echo ""
    echo -n "$custom_val" > "$CLIENT_SECRET_FILE"
    echo "  Written to $CLIENT_SECRET_FILE"
  else
    generated="$(generate_random_string)"
    echo -n "$generated" > "$CLIENT_SECRET_FILE"
    echo "  Generated secret written to $CLIENT_SECRET_FILE"
  fi
  chmod 600 "$CLIENT_SECRET_FILE"
fi
print_green "  daloRADIUS client secret ready."

# ============================================================
# STEP 1b: Create root docker-compose.yml orchestrator
# ============================================================
ORCHESTRATOR_FILE="$REPO_ROOT/docker-compose.yml"
echo "==> STEP 1b: Creating root docker-compose.yml orchestrator..."
cat > "$ORCHESTRATOR_FILE" <<'EOF'
# daloRADIUS Docker Compose orchestrator
#
# Auto-generated by setup/install-docker-compose.sh.
# Re-run the installer to regenerate.
#
# Usage:
#   docker compose up -d              # start full stack
#   docker compose up -d radius-mysql # start only MariaDB
#
# Standalone service files live in docker/<service>/docker-compose.yml.
# This file includes all three via the `include:` directive.

include:
  - docker/mariadb/docker-compose.yml
  - docker/freeradius/docker-compose.yml
  - docker/daloradius/docker-compose.yml
EOF

# ============================================================
# STEP 2: TLS certificates (only if FREERADIUS_SQL_TLS=enabled/require)
# ============================================================
if [ "$TLS_ENABLED" = true ]; then
  echo "==> STEP 2: TLS enabled — checking SSL certificates..."
  mkdir -p "$TLS_CONF_DIR"

  CA_KEY="$SECRETS_DIR/ca.key.pem"
  CA_PEM="$SECRETS_DIR/mysql_ca.pem"
  SERVER_KEY="$SECRETS_DIR/mysql_server.key.pem"
  SERVER_CSR="$SECRETS_DIR/mysql_server.csr"
  SERVER_PEM="$SECRETS_DIR/mysql_server.pem"

  if [ -f "$CA_PEM" ] && [ -f "$SERVER_PEM" ] && [ -f "$SERVER_KEY" ]; then
    echo "  Certificates already exist, skipping generation."
  else
    echo "  Generating self-signed SSL certificates (development only)..."
    openssl genrsa -out "$CA_KEY" 4096
    openssl req -new -x509 -days 3650 -key "$CA_KEY" \
      -subj "/CN=daloradius-mariadb-CA/O=daloRADIUS/C=US" -out "$CA_PEM"
    openssl genrsa -out "$SERVER_KEY" 4096
    openssl req -new -key "$SERVER_KEY" \
      -subj "/CN=radius-mysql/O=daloRADIUS/C=US" -out "$SERVER_CSR"
    openssl x509 -req -in "$SERVER_CSR" -CA "$CA_PEM" -CAkey "$CA_KEY" -CAcreateserial \
      -days 3650 -out "$SERVER_PEM"
    echo "  Certificates generated."
  fi

  chmod 600 "$CA_KEY" "$SERVER_KEY" 2>/dev/null || true
  chmod 644 "$CA_PEM" "$SERVER_PEM" 2>/dev/null || true

  # Write tls.cnf
  cat > "$TLS_CONF_FILE" <<'EOF'
[mysqld]
ssl-ca=/etc/mysql/certs/mysql_ca.pem
ssl-cert=/etc/mysql/certs/mysql_server.pem
ssl-key=/etc/mysql/certs/mysql_server.key.pem
# require_secure_transport = ON
EOF
  echo "  TLS config written to $TLS_CONF_FILE"

  # Docker volume for certs
  if ! docker volume inspect mariadb_certs >/dev/null 2>&1; then
    docker volume create mariadb_certs >/dev/null
    echo "  Docker volume 'mariadb_certs' created."
  fi

  echo "  Populating mariadb_certs with certificates..."
  docker run --rm -v mariadb_certs:/certs -v "$SECRETS_DIR":/hostsecrets alpine:latest sh -c '
    cp /hostsecrets/mysql_ca.pem /certs/ 2>/dev/null || true
    cp /hostsecrets/mysql_server.pem /certs/ 2>/dev/null || true
    cp /hostsecrets/mysql_server.key.pem /certs/ 2>/dev/null || true
    chmod 644 /certs/*.pem 2>/dev/null || true
    chmod 600 /certs/*key.pem 2>/dev/null || true
  ' || echo "  Warning: failed to populate cert volume" >&2

  # Chown to mysql UID 999
  docker run --rm -v mariadb_certs:/certs mariadb:11.8 sh -c '
    chown -R 999:999 /certs 2>/dev/null || true
  ' || echo "  Warning: chown failed, MariaDB may not read keys" >&2

  # Create a Compose override to activate TLS mounts for MariaDB
  # This is applied automatically when the stack starts
  TLS_OVERRIDE="$REPO_ROOT/docker/mariadb/tls-compose.yml"
  cat > "$TLS_OVERRIDE" <<EOF
# Auto-generated by install-docker-compose.sh — TLS MariaDB mounts
services:
  radius-mysql:
    volumes:
      - "$TLS_CONF_DIR/tls.cnf:/etc/mysql/conf.d/tls.cnf:ro"
      - "mariadb_certs:/etc/mysql/certs:ro"
EOF
  echo "  TLS Compose override written to $TLS_OVERRIDE"

  print_green "  TLS certificates ready."
else
  echo "==> STEP 2: TLS disabled (FREERADIUS_SQL_TLS=disabled), skipping."
fi

# ============================================================
# STEP 3: Ensure .env with generated values (BEFORE starting MariaDB)
# ============================================================
echo "==> STEP 3: Ensuring .env file with generated secrets..."

# Read generated secrets
MYSQL_ROOT_PASSWORD_VAL="$(cat "$SECRETS_DIR/mysql_root_password" 2>/dev/null || echo "")"
MYSQL_PASSWORD_VAL="$(cat "$SECRETS_DIR/mysql_password" 2>/dev/null || echo "")"
CLIENT_SECRET_VAL="$(cat "$DALORADIUS_SECRETS_DIR/daloradius_client_secret" 2>/dev/null || echo "")"

# Escape sed replacement metacharacters
MYSQL_ROOT_PASSWORD_ESC=$(escape_sed_replacement "$MYSQL_ROOT_PASSWORD_VAL")
MYSQL_PASSWORD_ESC=$(escape_sed_replacement "$MYSQL_PASSWORD_VAL")
CLIENT_SECRET_ESC=$(escape_sed_replacement "$CLIENT_SECRET_VAL")

if [ ! -f "$ENV_FILE" ]; then
  EXAMPLE_ENV="$REPO_ROOT/.env.example"
  if [ -f "$EXAMPLE_ENV" ]; then
    cp "$EXAMPLE_ENV" "$ENV_FILE"
    # Replace placeholders with generated values
    [ -n "$MYSQL_ROOT_PASSWORD_VAL" ] && sed -i "s|CHANGE_ME_ROOT_DB_PASSWORD|$MYSQL_ROOT_PASSWORD_ESC|" "$ENV_FILE" 2>/dev/null || true
    [ -n "$MYSQL_PASSWORD_VAL" ] && sed -i "s|CHANGE_ME_RADIUS_DB_PASSWORD|$MYSQL_PASSWORD_ESC|" "$ENV_FILE" 2>/dev/null || true
    [ -n "$CLIENT_SECRET_VAL" ] && sed -i "s|CHANGE_ME_RADIUS_SHARED_SECRET|$CLIENT_SECRET_ESC|" "$ENV_FILE" 2>/dev/null || true
    echo "  Created $ENV_FILE from .env.example with generated secrets."
  else
    echo "  Warning: .env.example not found, creating minimal .env"
    cat > "$ENV_FILE" <<EOF
MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD_VAL
MYSQL_PASSWORD=$MYSQL_PASSWORD_VAL
DEFAULT_CLIENT_SECRET=$CLIENT_SECRET_VAL
FREERADIUS_SQL_TLS=$FREERADIUS_SQL_TLS
TZ=Europe/Vienna
EOF
    echo "  Minimal .env created."
  fi
else
  # Update existing .env placeholders if still present
  if grep -q "CHANGE_ME" "$ENV_FILE" 2>/dev/null; then
    [ -n "$MYSQL_ROOT_PASSWORD_VAL" ] && sed -i "s|MYSQL_ROOT_PASSWORD=CHANGE_ME_ROOT_DB_PASSWORD|MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD_ESC|" "$ENV_FILE" 2>/dev/null || true
    [ -n "$MYSQL_PASSWORD_VAL" ] && sed -i "s|MYSQL_PASSWORD=CHANGE_ME_RADIUS_DB_PASSWORD|MYSQL_PASSWORD=$MYSQL_PASSWORD_ESC|" "$ENV_FILE" 2>/dev/null || true
    [ -n "$CLIENT_SECRET_VAL" ] && sed -i "s|DEFAULT_CLIENT_SECRET=CHANGE_ME_RADIUS_SHARED_SECRET|DEFAULT_CLIENT_SECRET=$CLIENT_SECRET_ESC|" "$ENV_FILE" 2>/dev/null || true
    echo "  Updated .env placeholder values."
  else
    echo "  .env already has custom values."
  fi
fi
sanitize_file_for_linux "$ENV_FILE"

# ============================================================
# STEP 4: Start MariaDB
# ============================================================
echo "==> STEP 4: Starting MariaDB..."
cd "$REPO_ROOT"
$DOCKER_COMPOSE up -d radius-mysql

echo "  Waiting for MariaDB to be healthy (up to 60s)..."
ROOT_PW="$(cat "$SECRETS_DIR/mysql_root_password" 2>/dev/null || true)"
n=0
until docker exec radius-mysql mariadb-admin ping -uroot -p"$ROOT_PW" --silent 2>/dev/null || docker exec radius-mysql mysqladmin ping -uroot -p"$ROOT_PW" --silent 2>/dev/null || [ $n -ge 30 ]; do
  printf "."
  sleep 2
  n=$((n+1))
done
echo ""
if [ $n -ge 30 ]; then
  echo "Error: MariaDB did not start. Logs:" >&2
  docker logs radius-mysql --tail 100
  exit 1
fi
print_green "  MariaDB is responding."

# ============================================================
# STEP 5: Create database and user
# ============================================================
echo "==> STEP 5: Creating database and application user..."
MYSQL_DB="${MYSQL_DATABASE:-radius}"
MYSQL_USER_NAME="${MYSQL_USER:-radius}"
APP_PW="$(cat "$SECRETS_DIR/mysql_password" 2>/dev/null || echo "")"
[ -z "$APP_PW" ] && { echo "Error: mysql_password secret not found" >&2; exit 1; }

# Escape backslashes and single quotes in passwords for SQL safety
sql_escape() { printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"; }
APP_PW_ESCAPED=$(sql_escape "$APP_PW")

docker exec radius-mysql mariadb -uroot -p"$ROOT_PW" \
  -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DB\`;" \
  -e "CREATE USER IF NOT EXISTS '$MYSQL_USER_NAME'@'%' IDENTIFIED BY '$APP_PW_ESCAPED';" \
  -e "GRANT ALL PRIVILEGES ON \`$MYSQL_DB\`.* TO '$MYSQL_USER_NAME'@'%';" \
  -e "FLUSH PRIVILEGES;" 2>&1 || echo "  Warning: DB/user creation failed (may already exist)." >&2
print_green "  Database/user ready."

# ============================================================
# STEP 6: Load SQL schemas (idempotent)
# ============================================================
echo "==> STEP 6: Loading SQL schemas..."
DB_DIR="$REPO_ROOT/contrib/db"

if docker exec radius-mysql mariadb -uroot -p"$ROOT_PW" -e "USE \`$MYSQL_DB\`; SHOW TABLES LIKE 'nas';" 2>/dev/null | grep -q 'nas'; then
  echo "  Schema already loaded (nas table exists), skipping."
else
  # Load FreeRADIUS schema first — daloRADIUS schema depends on these tables.
  # The radius container entrypoint also loads it, but that runs in Step 7.
  if [ -f "$DB_DIR/fr3-mariadb-freeradius.sql" ]; then
    echo "  Loading FreeRADIUS schema..."
    docker exec -i radius-mysql mariadb -uroot -p"$ROOT_PW" "$MYSQL_DB" < "$DB_DIR/fr3-mariadb-freeradius.sql"
    echo "  FreeRADIUS schema loaded."
  else
    echo "  Warning: $DB_DIR/fr3-mariadb-freeradius.sql not found" >&2
  fi

  if [ -f "$DB_DIR/mariadb-daloradius.sql" ]; then
    echo "  Loading daloRADIUS schema..."
    docker exec -i radius-mysql mariadb -uroot -p"$ROOT_PW" "$MYSQL_DB" < "$DB_DIR/mariadb-daloradius.sql"
    echo "  daloRADIUS schema loaded."
  else
    echo "  Warning: $DB_DIR/mariadb-daloradius.sql not found" >&2
  fi
fi
print_green "  Schemas loaded."

# ============================================================
# STEP 7: Start remaining services
# ============================================================
echo "==> STEP 7: Starting FreeRADIUS and daloRADIUS web..."
$DOCKER_COMPOSE up -d radius radius-web
print_green "  Full stack started."

# ============================================================
# Summary
# ============================================================
MYSQL_DB="${MYSQL_DATABASE:-radius}"
MYSQL_USER_NAME="${MYSQL_USER:-radius}"
MYSQL_HOST="${MYSQL_HOST:-radius-mysql}"
MYSQL_PORT="${MYSQL_PORT:-3306}"

echo ""
echo "============================================"
echo "  ✅ daloRADIUS stack is up!"
echo ""
echo "  ┌─────────────────────────────────────┐"
echo "  │  Service          │  URL            │"
echo "  ├─────────────────────────────────────┤"
echo "  │  Users portal     │  http://localhost:80  │"
echo "  │  Admin panel      │  http://localhost:8000 │"
echo "  │  MariaDB          │  radius-mysql:$MYSQL_PORT (internal) │"
echo "  │  FreeRADIUS (AC)  │  localhost:1812/udp │"
echo "  │  FreeRADIUS (ACCT)│  localhost:1813/udp │"
echo "  └─────────────────────────────────────┘"
echo ""
echo "  ┌─────────────────────────────────────┐"
echo "  │  Web UI credentials (default)       │"
echo "  ├─────────────────────────────────────┤"
echo "  │  Username: administrator            │"
echo "  │  Password: radius                   │"
echo "  │  (change after first login)         │"
echo "  └─────────────────────────────────────┘"
echo ""
echo "  ┌─────────────────────────────────────┐"
echo "  │  Database credentials               │"
echo "  ├─────────────────────────────────────┤"
echo "  │  Host:     $MYSQL_HOST           │"
echo "  │  Port:     $MYSQL_PORT                          │"
echo "  │  Database: $MYSQL_DB                            │"
echo "  │  User:     $MYSQL_USER_NAME                           │"
echo "  │  Password: stored in secrets/db/mysql_password │"
echo "  │  Root pwd: stored in secrets/db/mysql_root_password │"
echo "  └─────────────────────────────────────┘"
echo ""
echo "  ┌─────────────────────────────────────┐"
echo "  │  RADIUS                              │"
echo "  ├─────────────────────────────────────┤"
echo "  │  NAS shared secret (default client): │"
echo "  │    stored in secrets/daloradius/daloradius_client_secret │"
echo "  │                                     │"
echo "  │  Default NAS IP (auto-registered):   │"
echo "  │    Docker network gateway            │"
echo "  │    (check with: docker network inspect radius-network) │"
echo "  └─────────────────────────────────────┘"
echo ""
echo "  TLS: $FREERADIUS_SQL_TLS"
echo ""
echo "  📂 Data directories:"
echo "    - ./data/mysql       (MariaDB storage)"
echo "    - ./data/freeradius  (FreeRADIUS persistent data)"
echo "    - ./data/daloradius  (daloRADIUS persistent data)"
echo ""
echo "  🔐 Secrets directory: ./secrets/db/"
echo "     Show a secret value with:  cat ./secrets/db/<filename>"
echo ""
echo "  📋 Quick commands:"
echo "     docker compose logs -f radius-mysql   # MariaDB logs"
echo "     docker compose logs -f radius         # FreeRADIUS logs"
echo "     docker compose logs -f radius-web     # Web UI logs"
echo "     docker compose ps                     # Container status"
echo "============================================"

print_green "  Setup complete."
