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
if [ -f "$SECRETS_DIR/mysql_root_password" ]; then
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

# === STEP 4: Create README in secrets ===
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
