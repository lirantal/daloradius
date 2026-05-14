#!/bin/bash
# Executable process script for daloRADIUS freeradius docker image:
# GitHub: git@github.com:lirantal/daloradius.git
set -euo pipefail

RADIUS_PATH=/etc/freeradius
MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_PORT=${MYSQL_PORT:-3306}
MYSQL_DATABASE=${MYSQL_DATABASE:-radius}
MYSQL_USER=${MYSQL_USER:-radius}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-}
MYSQL_WAIT_RETRIES=${MYSQL_WAIT_RETRIES:-30}
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-2}
DEFAULT_CLIENT_SECRET=${DEFAULT_CLIENT_SECRET:-}
FREERADIUS_SQL_TLS=${FREERADIUS_SQL_TLS:-require}

function require_non_empty {
	local name="$1"
	local value="${!name:-}"
	if [ -z "$value" ]; then
		echo "$name must be set."
		exit 1
	fi
}

function reject_crlf {
	local name="$1"
	local value="${!name:-}"
	case "$value" in
		*$'\r'*|*$'\n'*)
			echo "$name must not contain CR or LF characters."
			exit 1
			;;
	esac
}

function require_positive_integer {
	local name="$1"
	local value="${!name:-}"
	if ! [[ "$value" =~ ^[1-9][0-9]*$ ]]; then
		echo "$name must be a positive integer."
		exit 1
	fi
}

function require_mysql_port {
	local name="$1"
	local value="${!name:-}"
	require_positive_integer "$name"
	if [ "${#value}" -gt 5 ] || { [ "${#value}" -eq 5 ] && [[ "$value" > "65535" ]]; }; then
		echo "$name must be between 1 and 65535."
		exit 1
	fi
}

function require_sql_identifier {
	local name="$1"
	local value="${!name:-}"
	require_non_empty "$name"
	if ! [[ "$value" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
		echo "$name must start with a letter or underscore and contain only letters, digits, and underscores."
		exit 1
	fi
}

function require_host_token {
	local name="$1"
	local value="${!name:-}"
	local -a host_labels
	local label
	require_non_empty "$name"
	reject_crlf "$name"

	case "$value" in
		.*|*.|*..*)
			echo "$name must not contain leading, trailing, or consecutive dots."
			exit 1
			;;
	esac

	IFS='.' read -r -a host_labels <<< "$value"
	for label in "${host_labels[@]}"; do
		if ! [[ "$label" =~ ^[A-Za-z0-9]([A-Za-z0-9_-]*[A-Za-z0-9])?$ ]]; then
			echo "$name must use dot-separated labels that start and end with a letter or digit."
			exit 1
		fi
	done

	if [ "${#host_labels[@]}" -eq 0 ]; then
		echo "$name must be a valid host token."
		exit 1
	fi
}

function validate_runtime_config {
	require_host_token "MYSQL_HOST"
	require_mysql_port "MYSQL_PORT"
	require_sql_identifier "MYSQL_DATABASE"
	require_sql_identifier "MYSQL_USER"
	require_non_empty "MYSQL_PASSWORD"
	reject_crlf "MYSQL_PASSWORD"
	require_positive_integer "MYSQL_WAIT_RETRIES"
	require_positive_integer "MYSQL_WAIT_INTERVAL"
	require_non_empty "DEFAULT_CLIENT_SECRET"
	reject_crlf "DEFAULT_CLIENT_SECRET"
	if [ "$FREERADIUS_SQL_TLS" != "require" ] && [ "$FREERADIUS_SQL_TLS" != "disabled" ]; then
		echo "FREERADIUS_SQL_TLS must be either require or disabled."
		exit 1
	fi
}

validate_runtime_config

MYSQL_DEFAULTS_FILE=$(mktemp)

chmod 600 "$MYSQL_DEFAULTS_FILE"
cat > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
host=$MYSQL_HOST
port=$MYSQL_PORT
user=$MYSQL_USER
password=$MYSQL_PASSWORD
EOF
trap 'rm -f "$MYSQL_DEFAULTS_FILE"' EXIT

function escape_sed_replacement {
	printf '%s' "$1" | sed -e 's/[\/&|\\]/\\&/g'
}

function freeradius_quote_escape {
	printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e 's/"/\\"/g' -e 's/\$/\\$/g'
}

function sql_escape {
	printf '%s' "$1" | sed "s/'/''/g"
}

function require_default_client_secret {
	if [ -z "$DEFAULT_CLIENT_SECRET" ]; then
		echo "DEFAULT_CLIENT_SECRET must be set."
		exit 1
	fi
}

function sql_config_set {
	local key="$1"
	local value
	value=$(escape_sed_replacement "$(freeradius_quote_escape "$2")")
	sed -i "s|^[#[:space:]]*$key[[:space:]]*=.*|$key = \"$value\"|" "$RADIUS_PATH/mods-available/sql"
}

function init_freeradius {
	require_default_client_secret

	# Enable SQL in freeradius
	sed -i 's|driver = "rlm_sql_null"|driver = "rlm_sql_mysql"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|dialect = "sqlite"|dialect = "mysql"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|dialect = ${modules.sql.dialect}|dialect = "mysql"|' $RADIUS_PATH/mods-available/sqlcounter # avoid instantiation error
	if [ "$FREERADIUS_SQL_TLS" = "disabled" ]; then
		sed -i 's|ca_file = "/etc/ssl/certs/my_ca.crt"|#ca_file = "/etc/ssl/certs/my_ca.crt"|' $RADIUS_PATH/mods-available/sql
		sed -i 's|ca_path = "/etc/ssl/certs/"|#ca_path = "/etc/ssl/certs/"|' $RADIUS_PATH/mods-available/sql
		sed -i 's|certificate_file = "/etc/ssl/certs/private/client.crt"|#certificate_file = "/etc/ssl/certs/private/client.crt"|' $RADIUS_PATH/mods-available/sql
		sed -i 's|private_key_file = "/etc/ssl/certs/private/client.key"|#private_key_file = "/etc/ssl/certs/private/client.key"|' $RADIUS_PATH/mods-available/sql
		sed -i 's|tls_required = yes|tls_required = no|' $RADIUS_PATH/mods-available/sql
	fi
	sed -i 's|#\s*read_clients = yes|read_clients = yes|' $RADIUS_PATH/mods-available/sql
	ln -sf $RADIUS_PATH/mods-available/sql $RADIUS_PATH/mods-enabled/sql
	ln -sf $RADIUS_PATH/mods-available/sqlcounter $RADIUS_PATH/mods-enabled/sqlcounter
	ln -sf $RADIUS_PATH/mods-available/sqlippool $RADIUS_PATH/mods-enabled/sqlippool
	enable_noresetcounter
	sed -i 's|instantiate {|instantiate {\nsql|' $RADIUS_PATH/radiusd.conf # mods-enabled does not ensure the right order

	# Enable used tunnel for unifi
	sed -i 's|use_tunneled_reply = no|use_tunneled_reply = yes|' $RADIUS_PATH/mods-available/eap

        # Log authentication request in radius-log file
        sed -i 's|auth = no|auth = yes|' $RADIUS_PATH/radiusd.conf
        # Sane default log setings for authentication
        sed -i 's|#\s*msg_goodpass =.*|msg_goodpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";vlan:\\\"%{reply:Tunnel-Private-Group-Id}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\";session_timeout:\\\"%{reply:Session-Timeout}\\\""|' \
        $RADIUS_PATH/radiusd.conf
        sed -i 's|#\s*msg_badpass =.*|msg_badpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\""|' \
        $RADIUS_PATH/radiusd.conf

	# Enable status in freeadius
	ln -sf $RADIUS_PATH/sites-available/status $RADIUS_PATH/sites-enabled/status

	# Set Database connection
	sql_config_set "server" "$MYSQL_HOST"
	sql_config_set "port" "$MYSQL_PORT"
	sql_config_set "radius_db" "$MYSQL_DATABASE"
	sql_config_set "password" "$MYSQL_PASSWORD"
	sql_config_set "login" "$MYSQL_USER"
	sed -i "s|testing123|$(escape_sed_replacement "$(freeradius_quote_escape "$DEFAULT_CLIENT_SECRET")")|" $RADIUS_PATH/mods-available/sql
	echo "freeradius initialization completed."
}

function enable_noresetcounter {
	# Enforce Max-All-Session limits through FreeRADIUS sqlcounter.
	# The sqlcounter module must run in authorize; enabling mods-enabled/sqlcounter alone is not enough.
	if grep -q "^[[:space:]]*noresetcounter[[:space:]]*$" $RADIUS_PATH/sites-available/default; then
		return
	fi

	if ! awk '
		BEGIN { in_authorize = 0; added = 0 }
		/^authorize[[:space:]]*[{]/ { in_authorize = 1 }
		in_authorize && !added && /^[[:space:]]*-sql$/ {
			print
			print "\tnoresetcounter"
			added = 1
			next
		}
		/^authenticate[[:space:]]*[{]/ { in_authorize = 0 }
		{ print }
		END { exit added ? 0 : 1 }
	' $RADIUS_PATH/sites-available/default > /tmp/freeradius-default; then
		rm -f /tmp/freeradius-default
		echo "Failed to add noresetcounter to FreeRADIUS authorize section."
		exit 1
	fi
	mv /tmp/freeradius-default $RADIUS_PATH/sites-available/default
}

function ensure_daloradius_schema {
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" <<'EOSQL'
CREATE TABLE IF NOT EXISTS `radhuntgroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nasipaddress` (`nasipaddress`)
);
EOSQL
}

function init_database {
	require_default_client_secret

	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < $RADIUS_PATH/mods-config/sql/main/mysql/schema.sql
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < $RADIUS_PATH/mods-config/sql/ippool/mysql/schema.sql
	ensure_daloradius_schema

	# Insert a client for the current subnet (to allow daloradius to perform checks)
	container_ip_address=`ifconfig eth0 | awk '/inet/{ print $2;} '` # does also work: $container_ip_address=`hostname -I | awk '{print $1}'`
	container_netmask=`ifconfig eth0 | awk '/netmask/{ print $4;} '`
	container_cidr=`ipcalc $container_ip_address $container_netmask | awk '/Network/{ print $2;} '`
	client_secret=$DEFAULT_CLIENT_SECRET
	container_cidr_sql=$(sql_escape "$container_cidr")
	client_secret_sql=$(sql_escape "$client_secret")
	echo "Adding client for $container_cidr with configured shared secret."
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" -e "INSERT INTO nas (nasname,shortname,type,ports,secret,server,community,description) VALUES ('$container_cidr_sql','DOCKER NET','other',0,'$client_secret_sql',NULL,'','')"

	echo "Database initialization for freeradius completed."
}

function freeradius_schema_ready {
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
		-e "SELECT 1 FROM radcheck LIMIT 1; SELECT 1 FROM nas LIMIT 1;" >/dev/null 2>&1
}

function wait_for_mysql {
	local attempt=1
	while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent; do
		if [ "$attempt" -ge "$MYSQL_WAIT_RETRIES" ]; then
			echo "MySQL did not become ready after $MYSQL_WAIT_RETRIES attempts."
			exit 1
		fi
		echo "Waiting for mysql ($MYSQL_HOST)..."
		attempt=$((attempt + 1))
		sleep "$MYSQL_WAIT_INTERVAL"
	done
}

function prepare_freeradius_logs {
	chown -R freerad:33 /var/log/freeradius
	find /var/log/freeradius -type d -exec chmod 2750 {} +
	find /var/log/freeradius -type f -exec chmod 0640 {} +
}

function wait_for_radius_status {
	local attempt=1
	while ! echo 'FreeRADIUS-Statistics-Type = 1' | radclient -q -r 1 -t 3 127.0.0.1:18121 status adminsecret >/dev/null 2>&1; do
		if [ "$attempt" -ge 30 ]; then
			return
		fi
		attempt=$((attempt + 1))
		sleep 1
	done
}

echo "Starting freeradius..."

# wait for MySQL-Server to be ready
wait_for_mysql

INIT_LOCK=/data/.freeradius_init_done
if test -f "$INIT_LOCK"; then
	# Lock file exists, but verify that FreeRADIUS is actually configured
	# This handles the case where containers are recreated but volumes persist
	if test -L "$RADIUS_PATH/mods-enabled/sql" && grep -q "rlm_sql_mysql" "$RADIUS_PATH/mods-available/sql" 2>/dev/null; then
		enable_noresetcounter
		echo "Init lock file exists and FreeRADIUS is properly configured, skipping initial setup."
	else
		echo "Init lock file exists but FreeRADIUS configuration is missing, reinitializing..."
		rm -f "$INIT_LOCK"
		init_freeradius
		date > "$INIT_LOCK"
	fi
else
	init_freeradius
	date > "$INIT_LOCK"
fi

# Ensure Max-All-Session is enforced before FreeRADIUS starts, including after
# lock-file and reinitialization paths.
if test -L "$RADIUS_PATH/mods-enabled/sqlcounter"; then
	enable_noresetcounter
fi

DB_LOCK=/data/.db_init_done
if freeradius_schema_ready; then
	echo "Database schema already present, skipping initial setup of mysql database."
	date > "$DB_LOCK"
	ensure_daloradius_schema
else
	init_database
	if ! freeradius_schema_ready; then
		echo "FreeRADIUS database schema was not found after initialization."
		exit 1
	fi
	date > "$DB_LOCK"
fi

# make logs readable to the shared www-data group without opening them world-wide
prepare_freeradius_logs

# start freeradius in foreground mode
freeradius -f "$@" &
RADIUS_PID=$!

wait_for_radius_status
prepare_freeradius_logs

tail -F /var/log/freeradius/radius.log &
TAIL_PID=$!

# trap SIGINT/SIGTERM and forward to both processes
_term() {
  echo "Caught signal—shutting down..."
  kill -TERM "$RADIUS_PID" 2>/dev/null
  kill -TERM "$TAIL_PID"   2>/dev/null
  wait "$RADIUS_PID"
  wait "$TAIL_PID"
  exit 0
}
trap _term INT TERM

set +e
wait "$RADIUS_PID"
RADIUS_STATUS=$?
set -e
# if freeradius dies, kill the tail and exit with its code
kill -TERM "$TAIL_PID" 2>/dev/null
wait "$TAIL_PID" 2>/dev/null || true
exit "$RADIUS_STATUS"
