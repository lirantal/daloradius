#!/bin/bash
# Executable process script for daloRADIUS docker image
# GitHub: https://github.com/lirantal/daloradius
# EOL: normalized to LF
#
# Supports Docker Secrets: reads passwords from /run/secrets/<name> when available,
# with fallback to environment variables.
DALORADIUS_PATH=/var/www/daloradius
DALORADIUS_CONF_PATH=/var/www/daloradius/app/common/includes/daloradius.conf.php

# Helper: read a value from Docker secret file or env var
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

function init_daloradius {
	if ! test -f "$DALORADIUS_CONF_PATH" || ! test -s "$DALORADIUS_CONF_PATH"; then
		cp "$DALORADIUS_CONF_PATH.sample" "$DALORADIUS_CONF_PATH"
		chown www-data:www-data "$DALORADIUS_CONF_PATH"
	fi

	# Read secrets with fallback to env vars (docker service names as defaults)
	local MYSQL_HOST="${MYSQL_HOST:-radius-mysql}"
	local MYSQL_PORT="${MYSQL_PORT:-3306}"
	local MYSQL_USER="${MYSQL_USER:-radius}"
	local MYSQL_PASSWORD
	MYSQL_PASSWORD=$(read_secret_or_env "MYSQL_PASSWORD" "MYSQL_PASSWORD")
	[ -z "$MYSQL_PASSWORD" ] && { echo "FATAL: MYSQL_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }
	local MYSQL_DATABASE="${MYSQL_DATABASE:-radius}"

	# Escape function for PHP single-quoted string context in sed
	php_sed_escape() {
		local val="$1"
		# Escape sed metacharacters: / & | \
		val=$(printf '%s' "$val" | sed 's/[\/&|\\]/\\&/g')
		# Escape PHP single quotes: ' -> '\''
		printf '%s' "$val" | sed "s/'/'\\\\''/g"
	}

	# Escape values for PHP single-quoted string context and sed metacharacters
	local esc_host esc_port esc_pass esc_user esc_name
	printf -v esc_host '%s' "$MYSQL_HOST"; esc_host=$(php_sed_escape "$esc_host")
	printf -v esc_port '%s' "$MYSQL_PORT"
	printf -v esc_pass '%s' "$MYSQL_PASSWORD"; esc_pass=$(php_sed_escape "$esc_pass")
	printf -v esc_user '%s' "$MYSQL_USER"; esc_user=$(php_sed_escape "$esc_user")
	printf -v esc_name '%s' "$MYSQL_DATABASE"; esc_name=$(php_sed_escape "$esc_name")

	sed -i "s/\$configValues\['CONFIG_DB_HOST'\] = .*;/\$configValues\['CONFIG_DB_HOST'\] = '$esc_host';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PORT'\] = .*;/\$configValues\['CONFIG_DB_PORT'\] = '$esc_port';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PASS'\] = .*;/\$configValues\['CONFIG_DB_PASS'\] = '$esc_pass';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_USER'\] = .*;/\$configValues\['CONFIG_DB_USER'\] = '$esc_user';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_NAME'\] = .*;/\$configValues\['CONFIG_DB_NAME'\] = '$esc_name';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['FREERADIUS_VERSION'\] = .*;/\$configValues\['FREERADIUS_VERSION'\] = '3';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_ENCRYPTION'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_ENCRYPTION'\] = 'no';/" $DALORADIUS_CONF_PATH

	# Escape and set remaining config values
	local esc_pwd_min_len esc_pwd_max_len
	[ -n "$PASSWORD_MIN_LENGTH" ] && { esc_pwd_min_len=$(php_sed_escape "$PASSWORD_MIN_LENGTH"); sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = '$esc_pwd_min_len';/" $DALORADIUS_CONF_PATH; }
	[ -n "$PASSWORD_MAX_LENGTH" ] && { esc_pwd_max_len=$(php_sed_escape "$PASSWORD_MAX_LENGTH"); sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = '$esc_pwd_max_len';/" $DALORADIUS_CONF_PATH; }

	local FREERADIUS_SERVER="${DEFAULT_FREERADIUS_SERVER:-radius}"
	local FREERADIUS_PORT="${DEFAULT_FREERADIUS_PORT:-1812}"
	local CLIENT_SECRET
	CLIENT_SECRET=$(read_secret_or_env "DEFAULT_CLIENT_SECRET" "DEFAULT_CLIENT_SECRET")
	[ -z "$CLIENT_SECRET" ] && { echo "FATAL: DEFAULT_CLIENT_SECRET not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	local esc_freeradius_server esc_client_secret
	esc_freeradius_server=$(php_sed_escape "$FREERADIUS_SERVER")
	esc_client_secret=$(php_sed_escape "$CLIENT_SECRET")
	sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = '$esc_freeradius_server';/" $DALORADIUS_CONF_PATH
	[ -n "$DEFAULT_FREERADIUS_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = '$FREERADIUS_PORT';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = '$esc_client_secret';/" $DALORADIUS_CONF_PATH

	# Mail settings (never sensitive, env vars are fine)
	local esc_mail_smtpaddr esc_mail_from esc_mail_auth
	[ -n "$MAIL_SMTPADDR" ] && { esc_mail_smtpaddr=$(php_sed_escape "$MAIL_SMTPADDR"); sed -i "s/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = '$esc_mail_smtpaddr';/" $DALORADIUS_CONF_PATH; }
	[ -n "$MAIL_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = '$MAIL_PORT';/" $DALORADIUS_CONF_PATH
	[ -n "$MAIL_FROM" ] && { esc_mail_from=$(php_sed_escape "$MAIL_FROM"); sed -i "s/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = '$esc_mail_from';/" $DALORADIUS_CONF_PATH; }
	[ -n "$MAIL_AUTH" ] && { esc_mail_auth=$(php_sed_escape "$MAIL_AUTH"); sed -i "s/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = '$esc_mail_auth';/" $DALORADIUS_CONF_PATH; }
	sed -i "s/\$configValues\['CONFIG_LOG_FILE'\] = .*;/\$configValues\['CONFIG_LOG_FILE'\] = '\/var\/www\/daloradius\/var\/log\/daloradius.log';/" $DALORADIUS_CONF_PATH

	# Ensure log directory and file exist (persistent volume or fresh start)
	mkdir -p /var/www/daloradius/var/log \
	  && touch /var/www/daloradius/var/log/daloradius.log \
	  && chown -R www-data:www-data /var/www/daloradius/var

	echo "daloRADIUS initialization completed."
}

# SQL escape helper for single-quoted string context
sql_escape() {
	printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"
}

function init_database {
	# Read root password from secret or env
	local MYSQL_ROOT_PASSWORD
	MYSQL_ROOT_PASSWORD=$(read_secret_or_env "MYSQL_ROOT_PASSWORD" "MYSQL_ROOT_PASSWORD")
	[ -z "$MYSQL_ROOT_PASSWORD" ] && { echo "FATAL: MYSQL_ROOT_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	# Use --defaults-extra-file for root connections (avoids password in process list)
	local root_defaults_file
	root_defaults_file=$(mktemp)
	chmod 600 "$root_defaults_file"
	{
		printf '[client]\n'
		printf 'host=%s\n' "$MYSQL_HOST"
		printf 'user=root\n'
		printf 'password=%s\n' "$MYSQL_ROOT_PASSWORD"
	} > "$root_defaults_file"

	local MYSQL_PASSWORD
	MYSQL_PASSWORD=$(read_secret_or_env "MYSQL_PASSWORD" "MYSQL_PASSWORD")
	[ -z "$MYSQL_PASSWORD" ] && { echo "FATAL: MYSQL_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	# Use --defaults-extra-file for app user connections
	local app_defaults_file
	app_defaults_file=$(mktemp)
	chmod 600 "$app_defaults_file"
	{
		printf '[client]\n'
		printf 'host=%s\n' "$MYSQL_HOST"
		printf 'user=%s\n' "$MYSQL_USER"
		printf 'password=%s\n' "$MYSQL_PASSWORD"
	} > "$app_defaults_file"

	# Escape values for SQL string context
	local esc_db esc_user esc_pass
	esc_db=$(sql_escape "$MYSQL_DATABASE")
	esc_user=$(sql_escape "$MYSQL_USER")
	esc_pass=$(sql_escape "$MYSQL_PASSWORD")

	# Create database if not exists
	mysql --defaults-extra-file="$root_defaults_file" --skip-ssl \
		-e "CREATE DATABASE IF NOT EXISTS \`$esc_db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;"

	# Create user for any host '%' and grant privileges
	mysql --defaults-extra-file="$root_defaults_file" --skip-ssl \
		-e "CREATE USER IF NOT EXISTS '$esc_user'@'%' IDENTIFIED BY '$esc_pass';"
	mysql --defaults-extra-file="$root_defaults_file" --skip-ssl \
		-e "GRANT ALL PRIVILEGES ON \`$esc_db\`.* TO '$esc_user'@'%'; FLUSH PRIVILEGES;"

	# Import schema
	mysql --defaults-extra-file="$app_defaults_file" --skip-ssl "$esc_db" \
		< "$DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql"

	# Fix collations: ensure all tables use utf8mb4_uca1400_ai_ci
	local tables
	tables=$(mysql --defaults-extra-file="$app_defaults_file" --skip-ssl -N -e \
		"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$esc_db' AND TABLE_TYPE='BASE TABLE';" "$esc_db")
	for table in $tables; do
		mysql --defaults-extra-file="$root_defaults_file" --skip-ssl "$esc_db" \
			-e "ALTER TABLE \`$table\` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;" 2>/dev/null || true
	done

	# Import VLAN vendor dictionary
	if [ -f "$DALORADIUS_PATH/docker/daloradius/config/dictionary-vlan-config.sql" ]; then
		echo "Importing VLAN vendor dictionary..."
		mysql --defaults-extra-file="$root_defaults_file" --skip-ssl "$esc_db" \
			< "$DALORADIUS_PATH/docker/daloradius/config/dictionary-vlan-config.sql" 2>/dev/null || true
		echo "VLAN vendor dictionary import completed."
	fi

	# Clean up defaults files
	rm -f "$root_defaults_file" "$app_defaults_file"

	echo "Database initialization for daloRADIUS completed."
}

echo "Starting daloRADIUS..."

INIT_LOCK=/data/.init_done
if test -f "$INIT_LOCK"; then
	#
	if ! test -f "$DALORADIUS_CONF_PATH" || ! test -s "$DALORADIUS_CONF_PATH"; then
		echo "Init lock file exists but config file does not exist or is 0 bytes, performing initial setup of daloRADIUS."
		init_daloradius
	fi
	echo "Init lock file exists and config file exists, skipping initial setup of daloRADIUS."
else
	init_daloradius
	date > $INIT_LOCK
fi

# wait for MySQL-Server to be ready

MYSQL_ROOT_PASSWORD=$(read_secret_or_env "MYSQL_ROOT_PASSWORD" "MYSQL_ROOT_PASSWORD")
[ -z "$MYSQL_ROOT_PASSWORD" ] && { echo "FATAL: MYSQL_ROOT_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }
echo -n "Waiting for mysql ($MYSQL_HOST)..."
# Use defaults file for mysqladmin ping to avoid exposing password in process list
local ping_defaults_file
ping_defaults_file=$(mktemp)
chmod 600 "$ping_defaults_file"
{
	printf '[client]\n'
	printf 'host=%s\n' "$MYSQL_HOST"
	printf 'user=root\n'
	printf 'password=%s\n' "$MYSQL_ROOT_PASSWORD"
} > "$ping_defaults_file"
while ! mysqladmin --defaults-extra-file="$ping_defaults_file" ping --skip-ssl --silent; do
	sleep 5
done
rm -f "$ping_defaults_file"
echo "ok"

DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
	echo "Database lock file exists, skipping initial setup of mysql database."
else
	init_database && date > $DB_LOCK
fi

# Suppress Apache FQDN warning (guard against duplicate on restart)
grep -qxF 'ServerName radius-web' /etc/apache2/apache2.conf || echo "ServerName radius-web" >> /etc/apache2/apache2.conf

# Start Apache2 in the foreground
/usr/sbin/apachectl -DFOREGROUND -k start
