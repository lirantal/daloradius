#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SECRETS_DIR="$REPO_ROOT/secrets"
TLS_CONF_DIR="$REPO_ROOT/docker/mariadb"
TLS_CONF_FILE="$TLS_CONF_DIR/tls.cnf"

echo "== daloRADIUS Docker Compose installer (TLS-enabled MySQL) =="
echo ""
echo "This script:"
echo "  1. Generates SSL certificates for MariaDB (if not present)"
echo "  2. Starts MariaDB only to configure TLS"
echo "  3. Verifies TLS works and adjusts permissions"
echo "  4. Brings up the full stack with SSL enabled"
echo ""

# Check dependencies
if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is not installed or not in PATH" >&2
  exit 1
fi
if ! command -v docker-compose >/dev/null 2>&1; then
  echo "Error: docker-compose is not installed or not in PATH" >&2
  exit 1
fi
if ! command -v openssl >/dev/null 2>&1; then
  echo "Error: openssl is required to generate certificates" >&2
  exit 1
fi

mkdir -p "$SECRETS_DIR"
mkdir -p "$TLS_CONF_DIR"

# Generate random strings (used for root/app passwords)
generate_random_string() {
  local len=${1:-12}
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -base64 $((len+4)) | tr -dc 'A-Za-z0-9' | cut -c1-$len
  else
    head -c 256 /dev/urandom | tr -dc 'A-Za-z0-9' | head -c $len
  fi
}

# Ensure a MariaDB root password file exists — generate one if missing
if [ ! -f "$SECRETS_DIR/mysql_root_password" ]; then
  echo "Generating random MariaDB root password and writing to $SECRETS_DIR/mysql_root_password"
  DB_PASS=$(generate_random_string 16)
  echo -n "$DB_PASS" > "$SECRETS_DIR/mysql_root_password"
  chmod 600 "$SECRETS_DIR/mysql_root_password" 2>/dev/null || true
fi

# If DB_PASS wasn't set above, generate a value (used as fallback)
if [ -z "${DB_PASS:-}" ]; then
    DB_PASS=$(generate_random_string 12)
fi

# Ensure an application DB password exists in secrets (idempotent)
if [ ! -f "$SECRETS_DIR/mysql_password" ]; then
  echo "Generating random application DB password and writing to $SECRETS_DIR/mysql_password"
  APP_DB_PASS=$(generate_random_string 16)
  echo -n "$APP_DB_PASS" > "$SECRETS_DIR/mysql_password"
  chmod 600 "$SECRETS_DIR/mysql_password" 2>/dev/null || true
fi

# Basic colored output helpers (simple versions copied from setup/install.sh)
GREEN='\e[32m'
RED='\e[31m'
YELLOW='\e[33m'
BLUE='\e[34m'
NC='\e[0m'

print_green() { echo -e "${GREEN}$1${NC}"; }
print_red() { echo -e "${RED}$1${NC}"; }
print_yellow() { echo -e "${YELLOW}$1${NC}"; }
print_blue() { echo -e "${BLUE}$1${NC}"; }

# Spinner that accepts a PID
print_spinner() {
    PID=$1
    i=1
    sp="/-\\|"
    echo -n ' '
    while [ -d /proc/$PID ]; do
        printf "\b${sp:i++%${#sp}:1}"
        sleep 0.1
    done
    printf "\b"
}

# Fix files edited on Windows (remove CR, strip BOM, normalize ssl-key path)
sanitize_file_for_linux() {
  for f in "$@"; do
    if [ -f "$f" ]; then
      # Remove CR characters (DOS line endings)
      if grep -q $'\r' "$f" 2>/dev/null; then
        echo "Sanitizing CRLF in: $f"
        sed -i 's/\r$//' "$f" || true
      fi
      # Remove UTF-8 BOM if present
      if head -c 3 "$f" | grep -q $'\xEF\xBB\xBF'; then
        echo "Removing BOM from: $f"
        sed -i '1s/^\xEF\xBB\xBF//' "$f" || true
      fi
      # If this is the MariaDB TLS config, ensure ssl-key points to the expected path
      if [ "$f" = "$TLS_CONF_FILE" ]; then
        sed -i 's|^\s*ssl-key\s*=.*|ssl-key=/etc/mysql/certs/mysql_server.key.pem|' "$f" || true
        sed -i 's|^\s*ssl-cert\s*=.*|ssl-cert=/etc/mysql/certs/mysql_server.pem|' "$f" || true
        sed -i 's|^\s*ssl-ca\s*=.*|ssl-ca=/etc/mysql/certs/mysql_ca.pem|' "$f" || true
      fi
    fi
  done
}

# === STEP 1: Generate/verify SSL certificates ===
echo "==> STEP 1: Checking SSL certificates for MariaDB..."

CA_KEY="$SECRETS_DIR/ca.key.pem"
CA_PEM="$SECRETS_DIR/mysql_ca.pem"
SERVER_KEY="$SECRETS_DIR/mysql_server.key.pem"
SERVER_CSR="$SECRETS_DIR/mysql_server.csr"
SERVER_PEM="$SECRETS_DIR/mysql_server.pem"

if [ -f "$CA_PEM" ] && [ -f "$SERVER_PEM" ] && [ -f "$SERVER_KEY" ]; then
  echo "Certificates already exist in $SECRETS_DIR"
  echo "  - CA: $CA_PEM"
  echo "  - Server cert: $SERVER_PEM"
  echo "  - Server key: $SERVER_KEY"
else
  echo "Generating self-signed SSL certificates (for development/testing)..."
  
  # create CA key and cert
  openssl genrsa -out "$CA_KEY" 4096
  openssl req -new -x509 -days 3650 -key "$CA_KEY" \
    -subj "/CN=daloradius-mariadb-CA/O=daloRADIUS/C=US" \
    -out "$CA_PEM"

  # create server key and cert signing request
  openssl genrsa -out "$SERVER_KEY" 4096
  openssl req -new -key "$SERVER_KEY" \
    -subj "/CN=radius-mysql/O=daloRADIUS/C=US" \
    -out "$SERVER_CSR"

  # sign server cert with CA
  openssl x509 -req -in "$SERVER_CSR" \
    -CA "$CA_PEM" -CAkey "$CA_KEY" -CAcreateserial \
    -days 3650 -out "$SERVER_PEM"

  echo "Certificates generated in $SECRETS_DIR"
fi

# Set file permissions on host
chmod 600 "$CA_KEY" 2>/dev/null || true
chmod 600 "$SERVER_KEY" 2>/dev/null || true
chmod 644 "$CA_PEM" 2>/dev/null || true
chmod 644 "$SERVER_PEM" 2>/dev/null || true

# === Copy certs from host into Docker volumes used by MariaDB ===
echo "==> Copying certificates from $SECRETS_DIR to Docker volumes (if present) ..."

# Ensure mariadb_certs volume exists
if ! docker volume inspect mariadb_certs >/dev/null 2>&1; then
  docker volume create mariadb_certs >/dev/null
  echo "Volume 'mariadb_certs' created"
else
  echo "Volume 'mariadb_certs' already exists"
fi

# Copy into mariadb_certs volume
docker run --rm -v mariadb_certs:/certs -v "$SECRETS_DIR":/hostsecrets alpine:latest sh -c '
  mkdir -p /certs
  cp /hostsecrets/mysql_ca.pem /certs/ 2>/dev/null || true
  cp /hostsecrets/mysql_server.pem /certs/ 2>/dev/null || true
  cp /hostsecrets/mysql_server.key.pem /certs/ 2>/dev/null || true
  chmod 644 /certs/*.pem 2>/dev/null || true
  chmod 600 /certs/*key.pem 2>/dev/null || true
  ls -la /certs || true
' || { echo "Warning: failed to populate mariadb_certs volume" >&2; }

# If radius-mysql is running, determine which volume/container mount is used and populate it too
VOL_RUNTIME="$(docker inspect radius-mysql --format '{{ range .Mounts }}{{ if eq .Destination "/etc/mysql/certs" }}{{ .Name }}{{ end }}{{ end }}' 2>/dev/null || true)"
if [ -n "$VOL_RUNTIME" ]; then
  echo "Detected runtime volume: $VOL_RUNTIME - copying certs into it"
  docker run --rm -v "$VOL_RUNTIME":/certs -v "$SECRETS_DIR":/hostsecrets alpine:latest sh -c '
    mkdir -p /certs
    cp /hostsecrets/mysql_ca.pem /certs/ 2>/dev/null || true
    cp /hostsecrets/mysql_server.pem /certs/ 2>/dev/null || true
    cp /hostsecrets/mysql_server.key.pem /certs/ 2>/dev/null || true
    chmod 644 /certs/*.pem 2>/dev/null || true
    chmod 600 /certs/*key.pem 2>/dev/null || true
    ls -la /certs || true
  ' || { echo "Warning: failed to populate $VOL_RUNTIME" >&2; }
fi

# Ensure ownership and permissions are compatible with MariaDB
# Use mariadb image to set owner to mysql:mysql or numeric fallback
for V in mariadb_certs ${VOL_RUNTIME:-}; do
  if docker volume inspect "$V" >/dev/null 2>&1; then
    echo "Setting owner/perm on volume: $V"
    docker run --rm -v "$V":/certs mariadb:10 sh -c '
      if id mysql >/dev/null 2>&1; then chown -R mysql:mysql /certs || true; else chown -R 999:999 /certs || true; fi
      chmod 644 /certs/*.pem 2>/dev/null || true
      chmod 600 /certs/*key.pem 2>/dev/null || true
      ls -la /certs || true
    '
  fi
done

echo "Certificate copy to volumes completed"



# === STEP 2: Configure TLS for MariaDB ===
echo ""
echo "==> STEP 2: Creating TLS configuration for MariaDB..."

cat > "$TLS_CONF_FILE" <<'EOF'
[mysqld]
# SSL/TLS configuration
ssl-ca=/etc/mysql/certs/mysql_ca.pem
ssl-cert=/etc/mysql/certs/mysql_server.pem
ssl-key=/etc/mysql/certs/mysql_server.key.pem

# Optional: force secure connections (uncomment for production)
# require_secure_transport = ON
EOF

echo "TLS configuration written to: $TLS_CONF_FILE"

# === STEP 3: Prepare and populate certificate volume, start MariaDB with TLS ===
echo ""
echo "==> STEP 3: Creating/filling Docker volume 'mariadb_certs' with certificates..."

# Create docker volume if missing
if ! docker volume inspect mariadb_certs >/dev/null 2>&1; then
  docker volume create mariadb_certs >/dev/null
  echo "Volume 'mariadb_certs' created"
else
  echo "Volume 'mariadb_certs' already exists"
fi

# Populate the volume from host ./secrets (idempotent copy)
# Use a lightweight image to copy files into the volume
docker run --rm -v mariadb_certs:/certs -v "$SECRETS_DIR":/hostsecrets alpine:latest sh -c "
  mkdir -p /certs
  cp /hostsecrets/mysql_ca.pem /certs/ 2>/dev/null || true
  cp /hostsecrets/mysql_server.pem /certs/ 2>/dev/null || true
  cp /hostsecrets/mysql_server.key.pem /certs/ 2>/dev/null || true
  chmod 644 /certs/*.pem 2>/dev/null || true
  chmod 600 /certs/*key.pem 2>/dev/null || true
" || { echo "Error: failed to populate mariadb_certs volume (docker run failed). Is Docker daemon reachable?" >&2; exit 1; }

# Try to chown files to mysql user numeric id inside the volume using mariadb image (so ownership matches container)
docker run --rm -v mariadb_certs:/certs mariadb:10 sh -c "
  if id mysql >/dev/null 2>&1; then
    chown -R mysql:mysql /certs || true
  else
    chown -R 999:999 /certs || true
  fi
  chmod 644 /certs/*.pem 2>/dev/null || true
  chmod 600 /certs/*key.pem 2>/dev/null || true
"

echo "Volume populated with certificates and permissions set"

echo "Starting MariaDB with TLS (tls.cnf mounted from ./docker/mariadb/tls.cnf)..."
cd "$REPO_ROOT"
# Start only MariaDB; ensure tls.cnf exists locally (we created it earlier)
docker-compose up -d --build radius-mysql

echo "Waiting for MariaDB to start (up to 60 seconds)..."
ROOT_PW=""
# Sanitize secrets (remove CRLF/BOM) before reading
if [ -f "$SECRETS_DIR/mysql_root_password" ]; then
  sanitize_file_for_linux "$SECRETS_DIR/mysql_root_password"
  ROOT_PW="$(cat "$SECRETS_DIR/mysql_root_password")"
fi

n=0
until docker exec radius-mysql mysqladmin ping -uroot -p"$ROOT_PW" --silent 2>/dev/null || [ $n -ge 30 ]; do
  printf "."
  sleep 2
  n=$((n+1))
done
echo ""

if [ $n -ge 30 ]; then
  echo "✗ MariaDB did not respond in time. Showing logs:"
  docker logs radius-mysql --tail 100
  echo ""
  echo "Error: Could not start MariaDB. Check the logs above."
  exit 1
fi

echo "MariaDB is responding"

# Ensure root@localhost password matches secret (or is empty)
echo "Ensuring root@'localhost' password matches secret (or is empty)..."
# If secrets file exists we've already read it into ROOT_PW above
if [ -z "${ROOT_PW:-}" ]; then
  # Ensure root@localhost has no password
  docker exec radius-mysql mysql -uroot -p"$ROOT_PW" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY ''; FLUSH PRIVILEGES;" >/dev/null 2>&1 || true
else
  # Ensure root@localhost uses the secrets password (idempotent)
  docker exec radius-mysql mysql -uroot -p"$ROOT_PW" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${ROOT_PW}'; FLUSH PRIVILEGES;" >/dev/null 2>&1 || true
fi


echo "Checking TLS status in MariaDB..."
TLS_STATUS=$(docker exec radius-mysql mysql -uroot -p"$ROOT_PW" -se "SHOW GLOBAL VARIABLES LIKE 'have_ssl';" 2>/dev/null | tail -1)

if echo "$TLS_STATUS" | grep -qi "YES"; then
  echo "TLS enabled correctly in MariaDB"
  echo ""
  docker exec radius-mysql mysql -uroot -p"$ROOT_PW" -se "
    SHOW GLOBAL VARIABLES LIKE 'have_%ssl';
    SHOW GLOBAL VARIABLES LIKE 'ssl_%';
  " 2>/dev/null || true
else
  echo "✗ TLS is NOT enabled. Showing MariaDB logs:"
  docker logs radius-mysql --tail 100
  echo ""
  echo "Error: TLS did not configure correctly."
  exit 1
fi

# Host-side schema loader helpers 
# Ensure mariadb client binary is present (or try to install via apt when running as root)
ensure_mariadb_client_installed() {
  if command -v mariadb >/dev/null 2>&1 || command -v mysql >/dev/null 2>&1; then
    return 0
  fi
  if command -v apt-get >/dev/null 2>&1 && [ "$(id -u)" = "0" ]; then
    echo "mariadb client not found. Attempting to install via apt-get..."
    apt-get update -qq >/dev/null 2>&1 && apt-get install -y --no-install-recommends mariadb-client >/dev/null 2>&1 || {
      print_red "KO"
      echo "[!] Failed to install mariadb-client automatically. Please install 'mariadb-client' (or 'mysql-client') and re-run this script." >&2
      return 1
    }
    return 0
  fi
  echo "mariadb client not found. Please install 'mariadb-client' (or 'mysql-client') and re-run this script." >&2
  return 1
}

# Create temporary mariadb client defaults file used with --defaults-extra-file
create_mariadb_client_defaults() {
  MARIADB_CLIENT_FILENAME="$(mktemp)"
  cat > "$MARIADB_CLIENT_FILENAME" <<EOF
[client]
host=127.0.0.1
port=3306
user=root
password=${ROOT_PW}
ssl-ca=${SECRETS_DIR}/mysql_ca.pem
ssl-mode=REQUIRED
EOF
  chmod 600 "$MARIADB_CLIENT_FILENAME"
  trap 'rm -f "${MARIADB_CLIENT_FILENAME:-}" >/dev/null 2>&1 || true' EXIT
}


# Create database and application user (run as root) and persist user password in secrets if newly generated
create_database_and_user() {
    # Sanitize potential Windows line endings / BOM in .env before sourcing
    if [ -f "$REPO_ROOT/.env" ]; then
        sanitize_file_for_linux "$REPO_ROOT/.env"
        set -o allexport
        # shellcheck disable=SC1091
        . "$REPO_ROOT/.env"
        set +o allexport
    fi

    MYSQL_DB="${MYSQL_DATABASE:-radius}"
    MYSQL_USER_NAME="${MYSQL_USER:-radius}"

    # If a password already exists in secrets, keep it (sanitize before reading)
    if [ -f "${SECRETS_DIR}/mysql_password" ]; then
        sanitize_file_for_linux "${SECRETS_DIR}/mysql_password"
        MYSQL_PASSWORD="$(cat "${SECRETS_DIR}/mysql_password")"
    else
        MYSQL_PASSWORD="$(generate_random_string 16)"
        echo -n "$MYSQL_PASSWORD" > "${SECRETS_DIR}/mysql_password"
        chmod 600 "${SECRETS_DIR}/mysql_password"
        echo "Wrote generated DB password to ${SECRETS_DIR}/mysql_password"
    fi

    # Ensure we have root password to connect
    if [ -z "${ROOT_PW:-}" ]; then
        echo "[!] MYSQL root password not available in ${SECRETS_DIR}/mysql_root_password. Cannot create database/user." >&2
        return 1
    fi

    # Executing the DB creation inside the mariadb container as root (more reliable in Docker setups)
    echo -n "[+] Creating database and user inside container (if missing)... "
    SQL="CREATE DATABASE IF NOT EXISTS \`$MYSQL_DB\` ; CREATE USER IF NOT EXISTS '$MYSQL_USER_NAME'@'%' IDENTIFIED BY '$MYSQL_PASSWORD'; GRANT ALL PRIVILEGES ON \`$MYSQL_DB\`.* TO '$MYSQL_USER_NAME'@'%'; FLUSH PRIVILEGES;"

    DOCKER_OUT="$(docker exec radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem --execute="${SQL}" 2>&1)" || true
    if [ $? -ne 0 ] || [ -n "${DOCKER_OUT}" ]; then
        print_red "KO"
        echo "[!] Failed to create database or user inside container. Output:" >&2
        echo "$DOCKER_OUT" >&2
        # Fallback: try host client if available (helps in some environments)
        if ensure_mariadb_client_installed >/dev/null 2>&1; then
            echo "Attempting fallback using host mariadb/mysql client..."
            if [ -z "${MARIADB_CLIENT_FILENAME:-}" ]; then
                create_mariadb_client_defaults
            fi
            if command -v mariadb >/dev/null 2>&1; then
                CLIENT_CMD="mariadb"
            else
                CLIENT_CMD="mysql"
            fi
            OUT="$(${CLIENT_CMD} --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" --execute="${SQL}" 2>&1)" || true
            if [ $? -ne 0 ] || [ -n "${OUT}" ]; then
                echo "Host fallback failed too. Output:" >&2
                echo "$OUT" >&2
                return 1
            else
                print_green "OK (host client fallback)"
            fi
        else
            echo "No host client available to try fallback. Please inspect the container logs:"
            docker logs radius-mysql --tail 200 >&2 || true
            return 1
        fi
    else
        print_green "OK"
    fi
}

# Function to load daloRADIUS SQL schema into MariaDB
# Idempotent: skips import when 'nas' table already exists
daloradius_load_sql_schema() {
    DB_DIR="${REPO_ROOT}/contrib/db"
    echo -n "[+] Loading daloRADIUS SQL schema into MariaDB... "

    if ! ensure_mariadb_client_installed; then
        print_red "KO"
        echo "[!] mariadb client is required to load schemas. Aborting." >&2
        exit 1
    fi

    if [ -z "${MARIADB_CLIENT_FILENAME:-}" ]; then
        create_mariadb_client_defaults
    fi

    # Check if 'nas' table already exists (run inside container)
    if docker exec radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem -e "USE \`${MYSQL_DATABASE:-radius}\`; SHOW TABLES LIKE 'nas';" 2>/dev/null | grep -q 'nas'; then
        print_green "SKIP (schema exists)"
        return 0
    fi

    # Import schema by streaming SQL into mysql inside the container (specify DB)
    docker exec -i radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem "${MYSQL_DATABASE:-radius}" < "${DB_DIR}/fr3-mariadb-freeradius.sql" & print_spinner $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        echo "[!] Failed to load FreeRADIUS SQL schema into MariaDB (container import). Showing error output:" >&2
        docker exec -i radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem "${MYSQL_DATABASE:-radius}" -e "SOURCE /dev/stdin" < "${DB_DIR}/fr3-mariadb-freeradius.sql" 2>&1 | sed -n '1,200p' >&2 || true
        exit 1
    fi

    docker exec -i radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem "${MYSQL_DATABASE:-radius}" < "${DB_DIR}/mariadb-daloradius.sql" & print_spinner $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        echo "[!] Failed to load daloRADIUS SQL schema into MariaDB (container import). Showing error output:" >&2
        docker exec -i radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem "${MYSQL_DATABASE:-radius}" -e "SOURCE /dev/stdin" < "${DB_DIR}/mariadb-daloradius.sql" 2>&1 | sed -n '1,200p' >&2 || true
        exit 1
    fi

    print_green "OK"
}


# === STEP 4: Ensure .env exists ===
echo "==> STEP 4: Ensuring .env exists..."
ENV_FILE="$REPO_ROOT/.env"
EXAMPLE_ENV_FILE="$REPO_ROOT/.env.example"

ensure_env_file() {
  echo "Ensuring .env exists..."

  # If .env already exists, just sanitize and exit
  if [ -f "$ENV_FILE" ]; then
    echo ".env already exists: $ENV_FILE"
    sanitize_file_for_linux "$ENV_FILE"
    return 0
  fi

  # If .env absent, copy from .env.example when available
  if [ -f "$EXAMPLE_ENV_FILE" ]; then
    sanitize_file_for_linux "$EXAMPLE_ENV_FILE"
    cp "$EXAMPLE_ENV_FILE" "$ENV_FILE"
    echo "Created $ENV_FILE from $EXAMPLE_ENV_FILE"
  else
    # Fallback: create a minimal .env so the installer can proceed
    echo "Warning: $EXAMPLE_ENV_FILE not found; creating minimal $ENV_FILE"
    cat > "$ENV_FILE" <<'EOF'
    # docker environment overrides
    # Timezone (set to your preferred TZ)
    TZ=Europe/Madrid

    # MariaDB settings (change as needed)
    MYSQL_HOST=radius-mysql
    MYSQL_PORT=3306
    MYSQL_DATABASE=radius
    MYSQL_USER=radius

    # daloRADIUS optional settings
    DEFAULT_CLIENT_SECRET=testing123
    DEFAULT_FREERADIUS_SERVER=radius
    MAIL_SMTPADDR=127.0.0.1
    MAIL_PORT=25
    MAIL_FROM=root@daloradius.xdsl.by
    MAIL_AUTH=

    # MySQL TLS mode: SKIP | DISABLED | PREFERRED | REQUIRED
    # Default is PREFERRED (try TLS, fallback if not available)
    MYSQL_SSL_MODE=PREFERRED
    # Set to SKIP to explicitly disable SSL (not recommended)
    # MYSQL_SSL_MODE=SKIP

    # If you use Docker secrets, place files under /run/secrets with names:
    # - mysql_root_password
    # - mysql_password
EOF
    echo "Created minimal $ENV_FILE"
  fi

  chmod 600 "$ENV_FILE" 2>/dev/null || true
  sanitize_file_for_linux "$ENV_FILE"
}

# Call the function
ensure_env_file


# Finalize installation tasks (set operator password etc.)
system_finalize() {
    INIT_USERNAME="administrator"
    INIT_PASSWORD=$(generate_random_string 12)
    SQL="UPDATE operators SET password='${INIT_PASSWORD}' WHERE username='${INIT_USERNAME}'"
    if ! docker exec radius-mysql mysql -uroot -p"${ROOT_PW}" --ssl-ca=/etc/mysql/certs/mysql_ca.pem --execute="${SQL}" >/dev/null 2>&1; then
        INIT_PASSWORD="radius"
        print_yellow "[!] Failed to update ${INIT_USERNAME}'s default password"
    fi

    DB_HOST="127.0.0.1"
    DB_PORT="3306"
    DB_USER="${MYSQL_USER:-radius}"
    DB_PASS="$(cat "${SECRETS_DIR}/mysql_password" 2>/dev/null || echo '')"
    DB_SCHEMA="${MYSQL_DATABASE:-radius}"

    echo -e "[+] daloRADIUS has been installed.\n"
    echo -e "    Here are some installation details:"
    echo -e "      - DB hostname: ${DB_HOST}"
    echo -e "      - DB port: ${DB_PORT}"
    echo -e "      - DB username: ${DB_USER}"
    echo -e "      - DB password: ${DB_PASS}"
    echo -e "      - DB schema: ${DB_SCHEMA}\n"

    #echo -e "    Users' dashboard can be reached via HTTP on port ${DALORADIUS_USERS_PORT}."
    #echo -e "    Operators' dashboard can be reached via HTTP on port ${DALORADIUS_OPERATORS_PORT}."
    #echo -e "    To log into the operators' dashboard, use the following credentials:"
    #echo -e "      - Username: ${INIT_USERNAME}"
    #echo -e "      - Password: ${INIT_PASSWORD}"
}

# Run DB creation, schema loader and finalization from host (idempotent)
create_database_and_user || { echo "[!] Database/user creation failed" >&2; exit 1; }
create_mariadb_client_defaults
daloradius_load_sql_schema
system_finalize

# === STEP 5: Create README in secrets ===
echo "==> STEP 5: Create README in secrets"
cat > "$SECRETS_DIR/README.txt" <<EOF
Certificates and secrets generated for daloRADIUS Docker Compose.

SSL/TLS files (shared among containers):
 - mysql_ca.pem            → CA certificate (used by clients)
 - mysql_server.pem        → MariaDB server certificate
 - mysql_server.key.pem    → MariaDB server private key

Secrets:
 - mysql_password          → Password for 'radius' user
 - mysql_root_password     → Password for root user
 - default_client_secret   → FreeRADIUS client secret

IMPORTANT: DO NOT commit these files to Git. They are for local/development use.
EOF

exit 0
