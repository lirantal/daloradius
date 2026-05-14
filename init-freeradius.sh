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
INIT_ERROR_LOG=${INIT_ERROR_LOG:-/data/freeradius-init-errors.log}
DEFAULT_CLIENT_SECRET=${DEFAULT_CLIENT_SECRET:-}
FREERADIUS_SQL_TLS=${FREERADIUS_SQL_TLS:-require}

function log_event {
	local level="$1"
	local event="$2"
	local outcome="$3"
	local detail="$4"
	printf 'level=%s component=freeradius-init event=%s outcome=%s detail=%s\n' "$level" "$event" "$outcome" "$detail"
}

function fail_step {
	local event="$1"
	local detail="$2"
	log_event "error" "$event" "failure" "$detail"
	exit 1
}

function setup_error_log {
	if ! { : > "$INIT_ERROR_LOG"; } 2>/dev/null; then
		fail_step "error_log_setup" "error_log_create_failed"
	fi
	if ! chmod 600 "$INIT_ERROR_LOG" 2>/dev/null; then
		fail_step "error_log_setup" "error_log_chmod_failed"
	fi
}

function setup_mysql_defaults_file {
	local defaults_file
	if ! [ -w "$INIT_ERROR_LOG" ]; then
		fail_step "mysql_defaults_setup" "error_log_unwritable"
	fi
	if ! defaults_file="$(mktemp)" 2>>"$INIT_ERROR_LOG"; then
		fail_step "mysql_defaults_setup" "defaults_file_create_failed"
	fi
	MYSQL_DEFAULTS_FILE="$defaults_file"
	trap 'rm -f "$MYSQL_DEFAULTS_FILE"' EXIT
	if ! chmod 600 "$MYSQL_DEFAULTS_FILE" 2>>"$INIT_ERROR_LOG"; then
		fail_step "mysql_defaults_setup" "defaults_file_chmod_failed"
	fi
	if ! cat 2>>"$INIT_ERROR_LOG" > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
host=$MYSQL_HOST
port=$MYSQL_PORT
user=$MYSQL_USER
password=$MYSQL_PASSWORD
EOF
	then
		fail_step "mysql_defaults_setup" "defaults_file_write_failed"
	fi
}

function write_lock {
	local lock_path="$1"
	local event="$2"
	if ! [ -w "$INIT_ERROR_LOG" ]; then
		fail_step "$event" "error_log_unwritable"
	fi
	date 2>>"$INIT_ERROR_LOG" > "$lock_path" || fail_step "$event" "lock_write_failed"
}

function require_non_empty {
	local name="$1"
	local value="${!name:-}"
	if [ -z "$value" ]; then
		fail_step "validation" "missing_required_${name}"
	fi
}

function reject_crlf {
	local name="$1"
	local value="${!name:-}"
	case "$value" in
		*$'\r'*|*$'\n'*)
			fail_step "validation" "invalid_crlf_${name}"
			;;
	esac
}

function require_positive_integer {
	local name="$1"
	local value="${!name:-}"
	if ! [[ "$value" =~ ^[1-9][0-9]*$ ]]; then
		fail_step "validation" "invalid_positive_integer_${name}"
	fi
}

function require_mysql_port {
	local name="$1"
	local value="${!name:-}"
	require_positive_integer "$name"
	if [ "${#value}" -gt 5 ] || { [ "${#value}" -eq 5 ] && [[ "$value" > "65535" ]]; }; then
		fail_step "validation" "invalid_mysql_port_${name}"
	fi
}

function require_sql_identifier {
	local name="$1"
	local value="${!name:-}"
	require_non_empty "$name"
	if ! [[ "$value" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
		fail_step "validation" "invalid_sql_identifier_${name}"
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
			fail_step "validation" "invalid_host_dots_${name}"
			;;
	esac

	IFS='.' read -r -a host_labels <<< "$value"
	for label in "${host_labels[@]}"; do
		if ! [[ "$label" =~ ^[A-Za-z0-9]([A-Za-z0-9_-]*[A-Za-z0-9])?$ ]]; then
			fail_step "validation" "invalid_host_label_${name}"
		fi
	done

	if [ "${#host_labels[@]}" -eq 0 ]; then
		fail_step "validation" "invalid_host_empty_${name}"
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
		fail_step "validation" "invalid_FREERADIUS_SQL_TLS"
	fi
}

setup_error_log
validate_runtime_config
setup_mysql_defaults_file

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
		fail_step "validation" "missing_required_DEFAULT_CLIENT_SECRET"
	fi
}

function sql_config_set {
	local key="$1"
	local value
	value=$(escape_sed_replacement "$(freeradius_quote_escape "$2")")
	sed -i "s|^[#[:space:]]*$key[[:space:]]*=.*|$key = \"$value\"|" "$RADIUS_PATH/mods-available/sql" \
		2>>"$INIT_ERROR_LOG" || fail_step "freeradius_init" "sql_config_update_failed"
}

function run_freeradius_sed {
	sed -i "$@" 2>>"$INIT_ERROR_LOG" || fail_step "freeradius_init" "config_update_failed"
}

function run_freeradius_link {
	ln -sf "$@" 2>>"$INIT_ERROR_LOG" || fail_step "freeradius_init" "config_link_failed"
}

function init_freeradius {
	require_default_client_secret
	log_event "info" "freeradius_init" "start" "configuring_freeradius"

	# Enable SQL in freeradius
	run_freeradius_sed 's|driver = "rlm_sql_null"|driver = "rlm_sql_mysql"|' "$RADIUS_PATH/mods-available/sql"
	run_freeradius_sed 's|dialect = "sqlite"|dialect = "mysql"|' "$RADIUS_PATH/mods-available/sql"
	run_freeradius_sed 's|dialect = ${modules.sql.dialect}|dialect = "mysql"|' "$RADIUS_PATH/mods-available/sqlcounter" # avoid instantiation error
	if [ "$FREERADIUS_SQL_TLS" = "disabled" ]; then
		run_freeradius_sed 's|ca_file = "/etc/ssl/certs/my_ca.crt"|#ca_file = "/etc/ssl/certs/my_ca.crt"|' "$RADIUS_PATH/mods-available/sql"
		run_freeradius_sed 's|ca_path = "/etc/ssl/certs/"|#ca_path = "/etc/ssl/certs/"|' "$RADIUS_PATH/mods-available/sql"
		run_freeradius_sed 's|certificate_file = "/etc/ssl/certs/private/client.crt"|#certificate_file = "/etc/ssl/certs/private/client.crt"|' "$RADIUS_PATH/mods-available/sql"
		run_freeradius_sed 's|private_key_file = "/etc/ssl/certs/private/client.key"|#private_key_file = "/etc/ssl/certs/private/client.key"|' "$RADIUS_PATH/mods-available/sql"
		run_freeradius_sed 's|tls_required = yes|tls_required = no|' "$RADIUS_PATH/mods-available/sql"
	fi
	run_freeradius_sed 's|#\s*read_clients = yes|read_clients = yes|' "$RADIUS_PATH/mods-available/sql"
	run_freeradius_link "$RADIUS_PATH/mods-available/sql" "$RADIUS_PATH/mods-enabled/sql"
	run_freeradius_link "$RADIUS_PATH/mods-available/sqlcounter" "$RADIUS_PATH/mods-enabled/sqlcounter"
	run_freeradius_link "$RADIUS_PATH/mods-available/sqlippool" "$RADIUS_PATH/mods-enabled/sqlippool"
	enable_noresetcounter
	run_freeradius_sed 's|instantiate {|instantiate {\nsql|' "$RADIUS_PATH/radiusd.conf" # mods-enabled does not ensure the right order

	# Enable used tunnel for unifi
	run_freeradius_sed 's|use_tunneled_reply = no|use_tunneled_reply = yes|' "$RADIUS_PATH/mods-available/eap"

        # Log authentication request in radius-log file
        run_freeradius_sed 's|auth = no|auth = yes|' "$RADIUS_PATH/radiusd.conf"
        # Sane default log setings for authentication
        run_freeradius_sed 's|#\s*msg_goodpass =.*|msg_goodpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";vlan:\\\"%{reply:Tunnel-Private-Group-Id}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\";session_timeout:\\\"%{reply:Session-Timeout}\\\""|' \
        "$RADIUS_PATH/radiusd.conf"
        run_freeradius_sed 's|#\s*msg_badpass =.*|msg_badpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\""|' \
        "$RADIUS_PATH/radiusd.conf"

	# Enable status in freeadius
	run_freeradius_link "$RADIUS_PATH/sites-available/status" "$RADIUS_PATH/sites-enabled/status"

	# Set Database connection
	sql_config_set "server" "$MYSQL_HOST"
	sql_config_set "port" "$MYSQL_PORT"
	sql_config_set "radius_db" "$MYSQL_DATABASE"
	sql_config_set "password" "$MYSQL_PASSWORD"
	sql_config_set "login" "$MYSQL_USER"
	run_freeradius_sed "s|testing123|$(escape_sed_replacement "$(freeradius_quote_escape "$DEFAULT_CLIENT_SECRET")")|" "$RADIUS_PATH/mods-available/sql"
	log_event "info" "freeradius_init" "success" "freeradius_configured"
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
	' "$RADIUS_PATH/sites-available/default" 2>>"$INIT_ERROR_LOG" > /tmp/freeradius-default; then
		rm -f /tmp/freeradius-default
		fail_step "noresetcounter_config" "noresetcounter_insert_failed"
	fi
	mv /tmp/freeradius-default "$RADIUS_PATH/sites-available/default" 2>>"$INIT_ERROR_LOG" \
		|| fail_step "noresetcounter_config" "noresetcounter_apply_failed"
}

function ensure_daloradius_schema {
	log_event "info" "radhuntgroup_schema_ensure" "start" "ensuring_schema"
	if mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" 2>>"$INIT_ERROR_LOG" <<'EOSQL'
CREATE TABLE IF NOT EXISTS `radhuntgroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nasipaddress` (`nasipaddress`)
);
EOSQL
	then
		log_event "info" "radhuntgroup_schema_ensure" "success" "schema_ensured"
	else
		fail_step "radhuntgroup_schema_ensure" "schema_ensure_failed"
	fi
}

function init_database {
	require_default_client_secret

	log_event "info" "freeradius_schema_import" "start" "importing_schema"
	if mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$RADIUS_PATH/mods-config/sql/main/mysql/schema.sql" 2>>"$INIT_ERROR_LOG"; then
		log_event "info" "freeradius_schema_import" "success" "schema_imported"
	else
		fail_step "freeradius_schema_import" "schema_import_failed"
	fi

	log_event "info" "ippool_schema_import" "start" "importing_schema"
	if mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$RADIUS_PATH/mods-config/sql/ippool/mysql/schema.sql" 2>>"$INIT_ERROR_LOG"; then
		log_event "info" "ippool_schema_import" "success" "schema_imported"
	else
		fail_step "ippool_schema_import" "schema_import_failed"
	fi
	ensure_daloradius_schema

	# Insert a client for the current subnet (to allow daloradius to perform checks)
	discover_container_cidr
	client_secret=$DEFAULT_CLIENT_SECRET
	container_cidr_sql=$(sql_escape "$container_cidr")
	client_secret_sql=$(sql_escape "$client_secret")
	echo "Adding client for $container_cidr with configured shared secret."
	log_event "info" "nas_client_insert" "start" "inserting_docker_client"
	if mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" 2>>"$INIT_ERROR_LOG" <<EOSQL
INSERT INTO nas (nasname,shortname,type,ports,secret,server,community,description) VALUES ('$container_cidr_sql','DOCKER NET','other',0,'$client_secret_sql',NULL,'','');
EOSQL
	then
		log_event "info" "nas_client_insert" "success" "docker_client_inserted"
	else
		fail_step "nas_client_insert" "client_insert_failed"
	fi

	echo "Database initialization for freeradius completed."
}

function freeradius_schema_ready {
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
		-e "SELECT 1 FROM radcheck LIMIT 1; SELECT 1 FROM nas LIMIT 1;" >/dev/null 2>&1
}

function wait_for_mysql {
	log_event "info" "mysql_wait" "start" "waiting_for_mysql"
	local attempt=1
	while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent >/dev/null 2>>"$INIT_ERROR_LOG"; do
		if [ "$attempt" -ge "$MYSQL_WAIT_RETRIES" ]; then
			fail_step "mysql_wait" "mysql_wait_timeout"
		fi
		echo "Waiting for mysql ($MYSQL_HOST)..."
		attempt=$((attempt + 1))
		sleep "$MYSQL_WAIT_INTERVAL"
	done
	log_event "info" "mysql_wait" "success" "mysql_ready"
}

function discover_container_cidr {
	if ! container_ip_address=$(ifconfig eth0 2>>"$INIT_ERROR_LOG" | awk '/inet/{ print $2; exit }'); then
		fail_step "cidr_discovery" "container_ip_discovery_failed"
	fi
	if [ -z "$container_ip_address" ]; then
		fail_step "cidr_discovery" "container_ip_empty"
	fi
	if ! container_netmask=$(ifconfig eth0 2>>"$INIT_ERROR_LOG" | awk '/netmask/{ print $4; exit }'); then
		fail_step "cidr_discovery" "container_netmask_discovery_failed"
	fi
	if [ -z "$container_netmask" ]; then
		fail_step "cidr_discovery" "container_netmask_empty"
	fi
	if ! container_cidr=$(ipcalc "$container_ip_address" "$container_netmask" 2>>"$INIT_ERROR_LOG" | awk '/Network/{ print $2; exit }'); then
		fail_step "cidr_discovery" "container_cidr_discovery_failed"
	fi
	if [ -z "$container_cidr" ]; then
		fail_step "cidr_discovery" "container_cidr_empty"
	fi
}

function prepare_freeradius_logs {
	chown -R freerad:33 /var/log/freeradius 2>>"$INIT_ERROR_LOG" \
		|| fail_step "freeradius_log_permissions" "log_owner_failed"
	find /var/log/freeradius -type d -exec chmod 2750 {} + 2>>"$INIT_ERROR_LOG" \
		|| fail_step "freeradius_log_permissions" "log_directory_mode_failed"
	find /var/log/freeradius -type f -exec chmod 0640 {} + 2>>"$INIT_ERROR_LOG" \
		|| fail_step "freeradius_log_permissions" "log_file_mode_failed"
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
		write_lock "$INIT_LOCK" "freeradius_init_lock"
	fi
else
	init_freeradius
	write_lock "$INIT_LOCK" "freeradius_init_lock"
fi

# Ensure Max-All-Session is enforced before FreeRADIUS starts, including after
# lock-file and reinitialization paths.
if test -L "$RADIUS_PATH/mods-enabled/sqlcounter"; then
	enable_noresetcounter
fi

DB_LOCK=/data/.db_init_done
if freeradius_schema_ready; then
	echo "Database schema already present, skipping initial setup of mysql database."
	write_lock "$DB_LOCK" "freeradius_db_lock"
	ensure_daloradius_schema
else
	init_database
	log_event "info" "freeradius_schema_check" "start" "checking_schema"
	if ! freeradius_schema_ready; then
		fail_step "freeradius_schema_check" "schema_post_check_failed"
	fi
	log_event "info" "freeradius_schema_check" "success" "schema_present"
	write_lock "$DB_LOCK" "freeradius_db_lock"
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
