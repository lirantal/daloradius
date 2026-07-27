#!/bin/bash
# Apply database migrations for daloRADIUS
#
# Reads credentials from Docker Secrets or environment variables,
# connects to MariaDB, and applies pending SQL migrations in order.
# Tracks applied migrations via the _schema_migrations table.
#
# Usage:
#   bash docker/daloradius/apply-migrations.sh
#
# Required environment variables:
#   MYSQL_PASSWORD  - Password for the daloRADIUS database user
#   MYSQL_HOST      - MariaDB hostname
#
# Optional environment variables:
#   MYSQL_USER      - Database user (default: radius)
#   MYSQL_PORT      - Database port (default: 3306)
#   MYSQL_DATABASE  - Database name (default: radius)

set -euo pipefail

# ---- Validate required vars ----
: "${MYSQL_PASSWORD:?FATAL: MYSQL_PASSWORD not set. Define it in .env or as a Docker secret.}"
: "${MYSQL_HOST:?FATAL: MYSQL_HOST not set.}"

MYSQL_USER="${MYSQL_USER:-radius}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_DATABASE="${MYSQL_DATABASE:-radius}"

# Determine script directory (works both inside container and standalone)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MIGRATIONS_DIR="$SCRIPT_DIR/migrations"

# Build defaults file for mysql client (avoids password in process list)
defaults_file=$(mktemp)
chmod 600 "$defaults_file"
{
	printf '[client]\n'
	printf 'host=%s\n' "$MYSQL_HOST"
	printf 'port=%s\n' "$MYSQL_PORT"
	printf 'user=%s\n' "$MYSQL_USER"
	printf 'password=%s\n' "$MYSQL_PASSWORD"
} > "$defaults_file"

cleanup() {
	rm -f "$defaults_file"
}
trap cleanup EXIT

# Ensure migrations directory exists
if [ ! -d "$MIGRATIONS_DIR" ]; then
	echo "Migrations directory not found: $MIGRATIONS_DIR"
	exit 0
fi

# Create tracking table if not exists
mysql --defaults-extra-file="$defaults_file" --skip-ssl "$MYSQL_DATABASE" \
	-e "CREATE TABLE IF NOT EXISTS _schema_migrations (
		filename VARCHAR(255) NOT NULL PRIMARY KEY,
		applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" 2>/dev/null || {
		echo "Warning: could not create _schema_migrations table (non-fatal)." >&2
	}

# Iterate over SQL files in numeric order
for migration in "$MIGRATIONS_DIR"/*.sql; do
	[ -f "$migration" ] || continue

	filename=$(basename "$migration")

	# Check if already applied
	already_applied=$(mysql --defaults-extra-file="$defaults_file" --skip-ssl --batch --skip-column-names "$MYSQL_DATABASE" \
		-e "SELECT COUNT(*) FROM _schema_migrations WHERE filename = '$filename';" 2>/dev/null || echo "0")

	if [ "$already_applied" != "0" ]; then
		echo "Skipping $filename (already applied)."
		continue
	fi

	echo "Applying migration: $filename..."
	mysql --defaults-extra-file="$defaults_file" --skip-ssl "$MYSQL_DATABASE" \
		< "$migration" 2>/dev/null || {
		echo "Warning: migration $filename failed (non-fatal)." >&2
		continue
	}

	# Record as applied
	mysql --defaults-extra-file="$defaults_file" --skip-ssl "$MYSQL_DATABASE" \
		-e "INSERT IGNORE INTO _schema_migrations (filename) VALUES ('$filename');" 2>/dev/null || true

	echo "Migration $filename applied successfully."
done

echo "All migrations processed."
