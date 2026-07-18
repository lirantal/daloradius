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

	sed -i "s/\$configValues\['CONFIG_DB_HOST'\] = .*;/\$configValues\['CONFIG_DB_HOST'\] = '$MYSQL_HOST';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PORT'\] = .*;/\$configValues\['CONFIG_DB_PORT'\] = '$MYSQL_PORT';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_PASS'\] = .*;/\$configValues\['CONFIG_DB_PASS'\] = '$MYSQL_PASSWORD';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_USER'\] = .*;/\$configValues\['CONFIG_DB_USER'\] = '$MYSQL_USER';/" $DALORADIUS_CONF_PATH
	sed -i "s/\$configValues\['CONFIG_DB_NAME'\] = .*;/\$configValues\['CONFIG_DB_NAME'\] = '$MYSQL_DATABASE';/" $DALORADIUS_CONF_PATH
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
	sed -i "s/\$configValues\['CONFIG_LOG_FILE'\] = .*;/\$configValues\['CONFIG_LOG_FILE'\] = '\/tmp\/daloradius.log';/" $DALORADIUS_CONF_PATH

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
	init_database
	date > $DB_LOCK
fi

# Suppress Apache FQDN warning (guard against duplicate on restart)
grep -qxF 'ServerName radius-web' /etc/apache2/apache2.conf || echo "ServerName radius-web" >> /etc/apache2/apache2.conf

# Start Apache2 in the foreground
/usr/sbin/apachectl -DFOREGROUND -k start
