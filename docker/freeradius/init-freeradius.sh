#!/bin/bash
# Executable process script for daloRADIUS FreeRADIUS docker image
# GitHub: https://github.com/lirantal/daloradius
#
# Fusion: upstream v2.3 + Docker Secrets + Graceful Shutdown + tail -F
set -euo pipefail

RADIUS_PATH=/etc/freeradius
MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_PORT=${MYSQL_PORT:-3306}
MYSQL_DATABASE=${MYSQL_DATABASE:-raddb}
MYSQL_USER=${MYSQL_USER:-raduser}
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-5}
FREERADIUS_SQL_TLS=${FREERADIUS_SQL_TLS:-disabled}

MYSQL_DEFAULTS_FILE=""

# ---------------------------------------------------------------------------
# Docker Secrets helper
# ---------------------------------------------------------------------------
read_secret_or_env() {
	local secret_name="$1"
	local env_name="$2"
	local default="${3:-}"
	local secret_file="/run/secrets/${secret_name}"

	if [ -f "$secret_file" ]; then
		cat "$secret_file"
	elif [ -n "${!env_name:-}" ]; then
		echo "${!env_name}"
	else
		echo "$default"
	fi
}

# Resolve sensitive values: secret file > env var (NO hardcoded fallback — must come from .env or Docker secrets)
MYSQL_PASSWORD="$(read_secret_or_env "MYSQL_PASSWORD" "MYSQL_PASSWORD")"
[ -z "$MYSQL_PASSWORD" ] && { echo "FATAL: MYSQL_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }

DEFAULT_CLIENT_SECRET="$(read_secret_or_env "DEFAULT_CLIENT_SECRET" "DEFAULT_CLIENT_SECRET")"
[ -z "$DEFAULT_CLIENT_SECRET" ] && { echo "FATAL: DEFAULT_CLIENT_SECRET not set. Define it in .env or as a Docker secret." >&2; exit 1; }

# ---------------------------------------------------------------------------
# MySQL defaults file (secure temp file with trap cleanup)
# ---------------------------------------------------------------------------
function cleanup_mysql_defaults {
	if [ -n "$MYSQL_DEFAULTS_FILE" ]; then
		rm -f "$MYSQL_DEFAULTS_FILE"
	fi
}
trap cleanup_mysql_defaults EXIT

function create_mysql_defaults_file {
	MYSQL_DEFAULTS_FILE=$(mktemp)
	chmod 600 "$MYSQL_DEFAULTS_FILE"
	{
		printf '[client]\n'
		printf 'host=%s\n' "$MYSQL_HOST"
		printf 'port=%s\n' "$MYSQL_PORT"
		printf 'user=%s\n' "$MYSQL_USER"
		printf 'password=%s\n' "$MYSQL_PASSWORD"
	} > "$MYSQL_DEFAULTS_FILE"
}

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
function escape_sed_replacement {
	printf '%s' "$1" | sed -e 's/[\/&|\\]/\\&/g'
}

function sql_escape {
	printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"
}

function mysql_radius {
	mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" "$@"
}

function table_exists {
	local table_name="$1"
	local escaped_table_name
	local count

	escaped_table_name=$(printf '%s' "$table_name" | sed -e "s/'/''/g")
	count=$(mysql_radius --batch --skip-column-names <<EOSQL
SELECT COUNT(*)
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name = '$escaped_table_name';
EOSQL
)
	test "$count" -gt 0
}

function tables_exist {
	local table_name
	for table_name in "$@"; do
		table_exists "$table_name" || return 1
	done
}

function sql_config_set {
	local key="$1"
	local value
	value=$(escape_sed_replacement "$2")
	sed -i "s|^#\s*$key = .*|$key = \"$value\"|" "$RADIUS_PATH/mods-available/sql"
}

# ---------------------------------------------------------------------------
# TLS config
# ---------------------------------------------------------------------------
function configure_sql_tls {
	case "$FREERADIUS_SQL_TLS" in
		disabled)
			sed -i 's|ca_file = "/etc/ssl/certs/my_ca.crt"|#ca_file = "/etc/ssl/certs/my_ca.crt"|' "$RADIUS_PATH/mods-available/sql"
			sed -i 's|ca_path = "/etc/ssl/certs/"|#ca_path = "/etc/ssl/certs/"|' "$RADIUS_PATH/mods-available/sql"
			sed -i 's|certificate_file = "/etc/ssl/certs/private/client.crt"|#certificate_file = "/etc/ssl/certs/private/client.crt"|' "$RADIUS_PATH/mods-available/sql"
			sed -i 's|private_key_file = "/etc/ssl/certs/private/client.key"|#private_key_file = "/etc/ssl/certs/private/client.key"|' "$RADIUS_PATH/mods-available/sql"
			sed -i 's|tls_required = yes|tls_required = no|' "$RADIUS_PATH/mods-available/sql"
			;;
		require)
			;;
		enabled)
			# Opportunistic TLS: try TLS but fall back to plain if server doesn't support it
			sed -i 's|tls_required = yes|tls_required = no|' "$RADIUS_PATH/mods-available/sql"
			;;
		*)
			echo "Invalid FREERADIUS_SQL_TLS value '$FREERADIUS_SQL_TLS'. Use 'disabled', 'enabled', or 'require'." >&2
			exit 1
			;;
	esac
}

# ---------------------------------------------------------------------------
# Auto-generate certificates if missing (first run or rebuild)
# Uses FreeRADIUS built-in Makefile
# ---------------------------------------------------------------------------
function ensure_certificates {
	local cert_dir="$RADIUS_PATH/certs"
	local regenerate=0

	# Check if any required cert file is missing
	if [ ! -f "$cert_dir/server.pem" ] || [ ! -f "$cert_dir/server.key" ] || [ ! -f "$cert_dir/ca.pem" ]; then
		echo "Certificates not found, generating new ones..."
		regenerate=1
	fi

	# Check expiration date (warn if less than 30 days, regenerate if expired)
	if [ -f "$cert_dir/server.pem" ] && [ "$regenerate" -eq 0 ]; then
		if command -v openssl &>/dev/null; then
			local expiry
			expiry=$(openssl x509 -in "$cert_dir/server.pem" -noout -enddate 2>/dev/null | cut -d= -f2)
			if [ -n "$expiry" ]; then
				local expiry_epoch now_epoch
				expiry_epoch=$(date -d "$expiry" +%s 2>/dev/null || echo 0)
				now_epoch=$(date +%s)
				if [ "$expiry_epoch" -le "$now_epoch" ]; then
					echo "Certificates expired ($expiry), regenerating..."
					regenerate=1
				elif [ "$((expiry_epoch - now_epoch))" -lt "$((30 * 86400))" ]; then
					echo "WARNING: Certificates will expire soon ($expiry). Consider renewing."
				fi
			fi
		fi
	fi

	if [ "$regenerate" -eq 1 ]; then
		# Extend certificate validity to 10 years (3650 days) before generating
		sed -i 's|default_days		= 60|default_days		= 3650|' "$cert_dir/ca.cnf" 2>/dev/null || true
		sed -i 's|default_days		= 60|default_days		= 3650|' "$cert_dir/server.cnf" 2>/dev/null || true

		cd "$cert_dir" && rm -f *.pem *.der *.csr *.crt *.key *.p12 serial index.txt serial.old index.txt.old \
			&& touch index.txt && echo '01' > serial && make
		echo "Certificates generated (valid for 10 years)."
	else
		echo "Certificates already exist and are valid, skipping generation."
	fi
}

# ---------------------------------------------------------------------------
# External certificates — prefer cert_ext/private_ext when present
# Falls back to default FreeRADIUS built-in certificates.
#
# Directory structure for external certificates (bind-mount volumes):
#   docker/freeradius/ssl/cert_ext/       → /etc/freeradius/certs/cert_ext/
#   docker/freeradius/ssl/private_ext/    → /etc/freeradius/certs/private_ext/
#
# This layout is designed so that users can replace self-signed certs with
# CA-signed certificates (Let's Encrypt, commercial CA, etc.) by simply
# placing the new files in these directories and restarting the container.
# No Dockerfile rebuild or structural changes are required.
# ---------------------------------------------------------------------------
function configure_external_certs {
	local eap_file="$RADIUS_PATH/mods-available/eap"
	local cert_ext_path="/etc/freeradius/certs/cert_ext"
	local private_ext_path="/etc/freeradius/certs/private_ext"
	local default_cert_dir="$RADIUS_PATH/certs"

	# First, ensure default certs exist (always needed as fallback)
	ensure_certificates

	if [ -d "$cert_ext_path" ] && [ -d "$private_ext_path" ] && \
	   [ -f "$cert_ext_path/server.pem" ] && [ -f "$cert_ext_path/ca.pem" ] && \
	   [ -f "$private_ext_path/server.key" ]; then
		echo "External certificates found in $cert_ext_path and $private_ext_path, configuring EAP..."
		chown -R freerad:freerad "$cert_ext_path" "$private_ext_path"
		chmod -R 640 "$cert_ext_path"/*.pem "$private_ext_path"/*.key
		sed -i 's|private_key_file = .*|private_key_file = '"$private_ext_path"'/server.key|' "$eap_file"
		sed -i 's|certificate_file = .*|certificate_file = '"$cert_ext_path"'/server.pem|' "$eap_file"
		sed -i 's|ca_file = .*|ca_file = '"$cert_ext_path"'/ca.pem|' "$eap_file"
		echo "EAP configured to use external certificates."
	else
		echo "No external certificates found in $cert_ext_path / $private_ext_path, using default FreeRADIUS certs."
		# Restore default certificate paths (in case they were previously set to external)
		sed -i 's|private_key_file = .*|private_key_file = '"$default_cert_dir"'/server.key|' "$eap_file"
		sed -i 's|certificate_file = .*|certificate_file = '"$default_cert_dir"'/server.pem|' "$eap_file"
		sed -i 's|ca_file = .*|ca_file = '"$default_cert_dir"'/ca.pem|' "$eap_file"
	fi
}

# ---------------------------------------------------------------------------
# Log preparation
# ---------------------------------------------------------------------------
function prepare_freeradius_logs {
	mkdir -p /var/log/freeradius
	chown -R freerad:33 /var/log/freeradius
	find /var/log/freeradius -type d -exec chmod 2750 {} +
	find /var/log/freeradius -type f -exec chmod 0640 {} +
}

# ---------------------------------------------------------------------------
# Main FreeRADIUS init
# ---------------------------------------------------------------------------
function init_freeradius {
	# Enable SQL in freeradius
	sed -i 's|driver = "rlm_sql_null"|driver = "rlm_sql_mysql"|' "$RADIUS_PATH/mods-available/sql"
	sed -i 's|dialect = "sqlite"|dialect = "mysql"|' "$RADIUS_PATH/mods-available/sql"
	sed -i 's|dialect = ${modules.sql.dialect}|dialect = "mysql"|' "$RADIUS_PATH/mods-available/sqlcounter"
	configure_sql_tls
	sed -i 's|#\s*read_clients = yes|read_clients = yes|' "$RADIUS_PATH/mods-available/sql"
	sed -i 's|#\s*read_profiles = yes|read_profiles = yes|' "$RADIUS_PATH/mods-available/sql"
	sed -i 's|#\s*read_groups = yes|read_groups = yes|' "$RADIUS_PATH/mods-available/sql"
	ln -sf "$RADIUS_PATH/mods-available/sql" "$RADIUS_PATH/mods-enabled/sql"
	ln -sf "$RADIUS_PATH/mods-available/sqlcounter" "$RADIUS_PATH/mods-enabled/sqlcounter"
	ln -sf "$RADIUS_PATH/mods-available/sqlippool" "$RADIUS_PATH/mods-enabled/sqlippool"
	enable_noresetcounter
	sed -i 's|instantiate {|instantiate {\nsql|' "$RADIUS_PATH/radiusd.conf"

	# Enable tunnel for UniFi
	sed -i 's|use_tunneled_reply = no|use_tunneled_reply = yes|' "$RADIUS_PATH/mods-available/eap"

	# Enable VLAN assignment via post-auth (Dynamic VLAN from radgroupreply)
	enable_vlan_post_auth

	# Log authentication
	sed -i 's|auth = no|auth = yes|' "$RADIUS_PATH/radiusd.conf"
	sed -i 's|#\s*msg_goodpass =.*|msg_goodpass = "authenticationtype:\\"%{control:Auth-Type}\\";nasipv4address:\\"%{request:NAS-IP-Address}\\";nasipv6address:\\"%{request:NAS-IPv6-Address}\\";nasid:\\"%{request:NAS-Identifier}\\";srcipaddress:\\"%{request:Packet-Src-IP-Address}\\";nasport:\\"%{request:NAS-Port-Id}\\";nasporttype:\\"%{request:NAS-Port-Type}\\";vlan:\\"%{reply:Tunnel-Private-Group-Id}\\";calledstationid:\\"%{request:Called-Station-Id}\\";callingstationid:\\"%{request:Calling-Station-Id}\\";session_timeout:\\"%{reply:Session-Timeout}\\""|' "$RADIUS_PATH/radiusd.conf"
	sed -i 's|#\s*msg_badpass =.*|msg_badpass = "authenticationtype:\\"%{control:Auth-Type}\\";nasipv4address:\\"%{request:NAS-IP-Address}\\";nasipv6address:\\"%{request:NAS-IPv6-Address}\\";nasid:\\"%{request:NAS-Identifier}\\";srcipaddress:\\"%{request:Packet-Src-IP-Address}\\";nasport:\\"%{request:NAS-Port-Id}\\";nasporttype:\\"%{request:NAS-Port-Type}\\";calledstationid:\\"%{request:Called-Station-Id}\\";callingstationid:\\"%{request:Calling-Station-Id}\\""|' "$RADIUS_PATH/radiusd.conf"

	# Enable status
	ln -sf "$RADIUS_PATH/sites-available/status" "$RADIUS_PATH/sites-enabled/status"

	# Database connection
	sql_config_set "server" "$MYSQL_HOST"
	sql_config_set "port" "$MYSQL_PORT"
	sed -i "1,\$s/radius_db.*/radius_db=\"$(escape_sed_replacement "$MYSQL_DATABASE")\"/g" "$RADIUS_PATH/mods-available/sql"
	sql_config_set "password" "${MYSQL_PASSWORD//\"/\\\"}"
	sql_config_set "login" "$MYSQL_USER"

	sed -i "s|testing123|$(escape_sed_replacement "$DEFAULT_CLIENT_SECRET")|" "$RADIUS_PATH/mods-available/sql"

	chown root:freerad "$RADIUS_PATH/mods-available/sql"
	chmod 0640 "$RADIUS_PATH/mods-available/sql"

	# External certificates (if mounted via docker-compose volumes)
	configure_external_certs

	echo "freeradius initialization completed."
}

# ---------------------------------------------------------------------------
# noresetcounter — enforce Max-All-Session in authorize
# ---------------------------------------------------------------------------
function enable_noresetcounter {
	local freeradius_default_tmp
	freeradius_default_tmp=$(mktemp) || {
		echo "Failed to create temporary file."
		exit 1
	}

	if grep -q "^[[:space:]]*noresetcounter[[:space:]]*$" "$RADIUS_PATH/sites-available/default"; then
		rm -f "$freeradius_default_tmp"
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
	' "$RADIUS_PATH/sites-available/default" > "$freeradius_default_tmp"; then
		rm -f "$freeradius_default_tmp"
		echo "Failed to add noresetcounter to FreeRADIUS authorize section."
		exit 1
	fi
	mv "$freeradius_default_tmp" "$RADIUS_PATH/sites-available/default"
}

# ---------------------------------------------------------------------------
# SQL session tracking — Simultaneous-Use limits
# ---------------------------------------------------------------------------
function enable_sql_session_tracking {
	local freeradius_default_tmp
	freeradius_default_tmp=$(mktemp) || {
		echo "Failed to create temporary file."
		exit 1
	}

	if ! awk '
		BEGIN { in_session = 0; in_post_auth = 0; session_sql = 0; sql_session_start = 0 }
		/^session[[:space:]]*[{]/ { in_session = 1 }
		in_session && /^[[:space:]]*#[[:space:]]*sql[[:space:]]*$/ {
			print "	sql"
			session_sql = 1
			next
		}
		in_session && /^[[:space:]]*sql[[:space:]]*$/ { session_sql = 1 }
		in_session && /^}/ { in_session = 0 }
		/^post-auth[[:space:]]*[{]/ { in_post_auth = 1 }
		in_post_auth && /^[[:space:]]*#[[:space:]]*sql_session_start[[:space:]]*$/ {
			print "	sql_session_start"
			sql_session_start = 1
			next
		}
		in_post_auth && /^[[:space:]]*sql_session_start[[:space:]]*$/ { sql_session_start = 1 }
		in_post_auth && /^}/ { in_post_auth = 0 }
		{ print }
		END { exit (session_sql && sql_session_start) ? 0 : 1 }
	' "$RADIUS_PATH/sites-available/default" > "$freeradius_default_tmp"; then
		rm -f "$freeradius_default_tmp"
		echo "Failed to enable SQL session tracking in FreeRADIUS."
		exit 1
	fi
	mv "$freeradius_default_tmp" "$RADIUS_PATH/sites-available/default"
}

# ---------------------------------------------------------------------------
# Group NAS restrictions — radgroupcheck enforcement
# ---------------------------------------------------------------------------
function enable_group_nas_restrictions {
	local freeradius_default_tmp
	freeradius_default_tmp=$(mktemp) || {
		echo "Failed to create temporary file."
		exit 1
	}

	if grep -q "daloRADIUS group NAS restriction policy" "$RADIUS_PATH/sites-available/default"; then
		rm -f "$freeradius_default_tmp"
		return
	fi

	if ! awk '
		BEGIN { added = 0 }
		{
			print
			if (!added && /^[[:space:]]*noresetcounter[[:space:]]*$/) {
				print ""
				print "\t\t# daloRADIUS group NAS restriction policy"
				print "\t\t# Enforce radgroupcheck NAS-IP-Address == restrictions as an"
				print "\t\t# authentication deny rule for users assigned to SQL groups."
				print "\t\tif (&request:NAS-IP-Address) {"
				print "\t\t\tif (\"%{sql:SELECT COUNT(*) FROM radusergroup ug JOIN radgroupcheck gc ON gc.groupname = ug.groupname WHERE ug.username = '\''%{SQL-User-Name}'\'' AND gc.attribute = '\''NAS-IP-Address'\'' AND gc.op = '\''=='\''}\" != \"0\") {"
				print "\t\t\t\tif (\"%{sql:SELECT COUNT(*) FROM radusergroup ug JOIN radgroupcheck gc ON gc.groupname = ug.groupname WHERE ug.username = '\''%{SQL-User-Name}'\'' AND gc.attribute = '\''NAS-IP-Address'\'' AND gc.op = '\''=='\'' AND gc.value = '\''%{NAS-IP-Address}'\''}\" == \"0\") {"
				print "\t\t\t\t\treject"
				print "\t\t\t\t}"
				print "\t\t\t}"
				print "\t\t}"
				added = 1
			}
		}
		END { exit added ? 0 : 1 }
	' "$RADIUS_PATH/sites-available/default" > "$freeradius_default_tmp"; then
		rm -f "$freeradius_default_tmp"
		echo "Failed to add daloRADIUS group NAS restriction policy to FreeRADIUS authorize section."
		exit 1
	fi
	mv "$freeradius_default_tmp" "$RADIUS_PATH/sites-available/default"
}

# ---------------------------------------------------------------------------
# Schema & auto-register Docker client
# ---------------------------------------------------------------------------
function ensure_daloradius_schema {
	mysql_radius <<'EOSQL'
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

function ensure_docker_client {
	container_ip_address=$(ifconfig eth0 2>/dev/null | awk '/inet /{ print $2; exit }')
	container_netmask=$(ifconfig eth0 2>/dev/null | awk '/netmask/{ print $4; exit }')
	container_cidr=$(ipcalc "$container_ip_address" "$container_netmask" 2>/dev/null | awk '/Network/{ print $2; exit }')
	client_secret="$DEFAULT_CLIENT_SECRET"
	container_cidr_sql=$(sql_escape "$container_cidr")
	client_secret_sql=$(sql_escape "$client_secret")

	echo "Ensuring client for $container_cidr"
	mysql_radius <<EOSQL
INSERT INTO nas (nasname,shortname,type,ports,secret,server,community,description)
SELECT '$container_cidr_sql','DOCKER NET','other',0,'$client_secret_sql',NULL,'',''
WHERE NOT EXISTS (SELECT 1 FROM nas WHERE nasname = '$container_cidr_sql');
EOSQL
}

function init_database {
	mysql_radius < "$RADIUS_PATH/mods-config/sql/main/mysql/schema.sql"
	mysql_radius < "$RADIUS_PATH/mods-config/sql/ippool/mysql/schema.sql"
	ensure_daloradius_schema
	ensure_docker_client
	echo "Database initialization for freeradius completed."
}

# ---------------------------------------------------------------------------
# VLAN post-auth — Dynamic VLAN assignment from radgroupreply
# Uses sed to add VLAN block inside post-auth section.
# ---------------------------------------------------------------------------
function enable_vlan_post_auth {
	if grep -q "VLAN CONFIG" "$RADIUS_PATH/sites-available/default" 2>/dev/null; then
		echo "VLAN post-auth already applied, skipping."
		return
	fi

	# Insert VLAN block before the first Post-Auth-Type section inside post-auth
	sed -i '/^[[:space:]]*Post-Auth-Type REJECT/{
		i\
	# VLAN CONFIG - Dynamic VLAN assignment from radgroupreply\n\
	if (reply:Tunnel-Private-Group-Id) {\n\
		# already set by authorize/sql\n\
	} else {\n\
		update reply {\n\
			Tunnel-Private-Group-Id := "%{sql:SELECT value FROM radgroupreply WHERE attribute='\''Tunnel-Private-Group-Id'\'' AND groupname = (SELECT groupname FROM radusergroup WHERE username = '\''%{User-Name}'\'' LIMIT 1)}"\n\
		}\n\
	}\n\
\n\
	update reply {\n\
		Tunnel-Type := VLAN\n\
		Tunnel-Medium-Type := IEEE-802\n\
		Tunnel-Private-Group-Id := "%{reply:Tunnel-Private-Group-Id}"\n\
	}\n\
\n\
	# DEFAULT SESSION TIMEOUT - 1 hour (28800 seconds)\n\
	# Applied to all users who don't have one set by their group.\n\
	# Covers existing, migrated, and newly created groups automatically.\n\
	if (!&reply:Session-Timeout) {\n\
		update reply {\n\
			Session-Timeout := 28800\n\
		}\n\
	}\n
	}' "$RADIUS_PATH/sites-available/default"

	echo "VLAN post-auth and default Session-Timeout enabled."
}

# ---------------------------------------------------------------------------
# MySQL waiter
# ---------------------------------------------------------------------------
function wait_for_mysql {
	while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent; do
		echo "Waiting for mysql ($MYSQL_HOST)..."
		sleep "$MYSQL_WAIT_INTERVAL"
	done
}

# ===========================================================================
# Main
# ===========================================================================
echo "Starting freeradius..."
create_mysql_defaults_file
wait_for_mysql

INIT_LOCK=/data/.freeradius_init_done
if test -f "$INIT_LOCK"; then
	if test -L "$RADIUS_PATH/mods-enabled/sql" && grep -q "rlm_sql_mysql" "$RADIUS_PATH/mods-available/sql" 2>/dev/null; then
		enable_noresetcounter
		ln -sf "$RADIUS_PATH/sites-available/status" "$RADIUS_PATH/sites-enabled/status"
		echo "Init lock file exists and FreeRADIUS is properly configured, skipping initial setup."
	else
		echo "Init lock file exists but FreeRADIUS configuration is missing, reinitializing..."
		rm -f "$INIT_LOCK"
		prepare_freeradius_logs
		init_freeradius
		date > "$INIT_LOCK"
	fi
else
	prepare_freeradius_logs
	init_freeradius
	date > "$INIT_LOCK"
fi

# Post-init: ensure advanced features are always applied
if test -L "$RADIUS_PATH/mods-enabled/sqlcounter"; then
	enable_noresetcounter
fi
if test -L "$RADIUS_PATH/mods-enabled/sql"; then
	enable_sql_session_tracking
	enable_group_nas_restrictions
fi

DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
	echo "Database lock file exists, skipping initial setup of mysql database."
else
	init_database
	date > "$DB_LOCK"
fi

cleanup_mysql_defaults
trap - EXIT

# Graceful shutdown
_term() {
	echo "Caught SIGTERM, shutting down FreeRADIUS gracefully..."
	kill -TERM "$FRPID" 2>/dev/null
	wait "$FRPID"
	exit 0
}
_int() {
	echo "Caught SIGINT, shutting down FreeRADIUS gracefully..."
	kill -INT "$FRPID" 2>/dev/null
	wait "$FRPID"
	exit 0
}
trap _term SIGTERM
trap _int SIGINT

# Start FreeRADIUS in background, then tail logs
freeradius -f "$@" &
FRPID=$!

touch /var/log/freeradius/radius.log 2>/dev/null || true
tail -F /var/log/freeradius/radius.log 2>/dev/null &
TAILPID=$!

wait "$FRPID"