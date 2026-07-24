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

	# Escape values for PHP single-quoted string context and sed metacharacters
	local esc_host esc_port esc_pass esc_user esc_name
	printf -v esc_host '%s' "$MYSQL_HOST"; esc_host=$(sed 's/[\/&|]|/\&/g; s/\x27/\x27\\\x27\x27/g' <<<"$esc_host")
	printf -v esc_port '%s' "$MYSQL_PORT"
	printf -v esc_pass '%s' "$MYSQL_PASSWORD"; esc_pass=$(sed 's/[\/&|]|/\&/g; s/\x27/\x27\\\x27\x27/g' <<<"$esc_pass")
	printf -v esc_user '%s' "$MYSQL_USER"; esc_user=$(sed 's/[\/&|]|/\&/g; s/\x27/\x27\\\x27\x27/g' <<<"$esc_user")
	printf -v esc_name '%s' "$MYSQL_DATABASE"; esc_name=$(sed 's/[\/&|]|/\&/g; s/\x27/\x27\\\x27\x27/g' <<<"$esc_name")

	sed -i "s/\$configValues\['CONFIG_DB_HOST'\] = .*;/\$configValues\['CONFIG_DB_HOST'\] = '$esc_host';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PORT'\] = .*;/\$configValues\['CONFIG_DB_PORT'\] = '$esc_port';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PASS'\] = .*;/\$configValues\['CONFIG_DB_PASS'\] = '$esc_pass';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_USER'\] = .*;/\$configValues\['CONFIG_DB_USER'\] = '$esc_user';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_NAME'\] = .*;/\$configValues\['CONFIG_DB_NAME'\] = '$esc_name';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['FREERADIUS_VERSION'\] = .*;/\$configValues\['FREERADIUS_VERSION'\] = '3';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_ENCRYPTION'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_ENCRYPTION'\] = 'no';/" $DALORADIUS_CONF_PATH
	[ -n "$PASSWORD_MIN_LENGTH" ] && sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = '$PASSWORD_MIN_LENGTH';/" $DALORADIUS_CONF_PATH
	[ -n "$PASSWORD_MAX_LENGTH" ] && sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = '$PASSWORD_MAX_LENGTH';/" $DALORADIUS_CONF_PATH

	local FREERADIUS_SERVER="${DEFAULT_FREERADIUS_SERVER:-radius}"
	local FREERADIUS_PORT="${DEFAULT_FREERADIUS_PORT:-1812}"
	local CLIENT_SECRET
	CLIENT_SECRET=$(read_secret_or_env "DEFAULT_CLIENT_SECRET" "DEFAULT_CLIENT_SECRET")
	[ -z "$CLIENT_SECRET" ] && { echo "FATAL: DEFAULT_CLIENT_SECRET not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = '$FREERADIUS_SERVER';/" $DALORADIUS_CONF_PATH
	[ -n "$DEFAULT_FREERADIUS_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = '$DEFAULT_FREERADIUS_PORT';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = '$CLIENT_SECRET';/" $DALORADIUS_CONF_PATH

	# Mail settings (never sensitive, env vars are fine)
	[ -n "$MAIL_SMTPADDR" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = '$MAIL_SMTPADDR';/" $DALORADIUS_CONF_PATH
	[ -n "$MAIL_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = '$MAIL_PORT';/" $DALORADIUS_CONF_PATH
	[ -n "$MAIL_FROM" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = '$MAIL_FROM';/" $DALORADIUS_CONF_PATH
	[ -n "$MAIL_AUTH" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = '$MAIL_AUTH';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_LOG_FILE'\] = .*;/\$configValues\['CONFIG_LOG_FILE'\] = '\/var\/www\/daloradius\/var\/log\/daloradius.log';/" $DALORADIUS_CONF_PATH

	# Ensure log directory and file exist (persistent volume or fresh start)
	mkdir -p /var/www/daloradius/var/log \
	  && touch /var/www/daloradius/var/log/daloradius.log \
	  && chown -R www-data:www-data /var/www/daloradius/var

	echo "daloRADIUS initialization completed."
}

function init_database {
	# Read root password from secret or env
	local MYSQL_ROOT_PASSWORD
	MYSQL_ROOT_PASSWORD=$(read_secret_or_env "MYSQL_ROOT_PASSWORD" "MYSQL_ROOT_PASSWORD")
	[ -z "$MYSQL_ROOT_PASSWORD" ] && { echo "FATAL: MYSQL_ROOT_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	# Create database if not exists
	mysql -h "$MYSQL_HOST" --skip-ssl -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\`;"

	# Read app password from secret or env
	local MYSQL_PASSWORD
	MYSQL_PASSWORD=$(read_secret_or_env "MYSQL_PASSWORD" "MYSQL_PASSWORD")
	[ -z "$MYSQL_PASSWORD" ] && { echo "FATAL: MYSQL_PASSWORD not set. Define it in .env or as a Docker secret." >&2; exit 1; }

	# Create user for any host '%' and grant privileges (docker uses network connections, not localhost)
	mysql -h "$MYSQL_HOST" --skip-ssl -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASSWORD';"
	mysql -h "$MYSQL_HOST" --skip-ssl -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'%'; FLUSH PRIVILEGES;"

	# Import schema using client option to disable SSL if server does not have it
	mysql --skip-ssl -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < $DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql

	# Fix collations: ensure all tables use the DB default collation
	# (MariaDB 11.8+ defaults to utf8mb4_uca1400_ai_ci, older versions used utf8mb4_general_ci.
	#  The schema SQL doesn't specify COLLATE, so mixed collations break JOIN queries.)
	local tables
	tables=$(mysql --skip-ssl -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -N -e \
		"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$MYSQL_DATABASE' AND TABLE_TYPE='BASE TABLE';")
	for table in $tables; do
		mysql --skip-ssl -h "$MYSQL_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" \
			-e "ALTER TABLE \`$table\` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;" 2>/dev/null || true
	done

	# Import VLAN vendor dictionary (custom vendor "VLAN" for easier management)
	if [ -f "$DALORADIUS_PATH/docker/daloradius/config/dictionary-vlan-config.sql" ]; then
		echo "Importing VLAN vendor dictionary..."
		mysql --skip-ssl -h "$MYSQL_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" \
			< "$DALORADIUS_PATH/docker/daloradius/config/dictionary-vlan-config.sql" 2>/dev/null || true
		echo "VLAN vendor dictionary import completed."
	fi

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
# use --skip-ssl so client doesn't require TLS if server doesn't support it
while ! mysqladmin ping -h"$MYSQL_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" --skip-ssl --silent; do
	sleep 5
done
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
