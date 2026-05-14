#!/bin/bash
# Executable process script for daloRADIUS docker image:
# GitHub: git@github.com:lirantal/daloradius.git
set -euo pipefail

DALORADIUS_PATH=/var/www/daloradius
DALORADIUS_CONF_PATH=/var/www/daloradius/app/common/includes/daloradius.conf.php
MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_PORT=${MYSQL_PORT:-3306}
MYSQL_DATABASE=${MYSQL_DATABASE:-raddb}
MYSQL_USER=${MYSQL_USER:-raduser}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-radpass}
MYSQL_WAIT_RETRIES=${MYSQL_WAIT_RETRIES:-30}
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-2}
PASSWORD_MIN_LENGTH=${PASSWORD_MIN_LENGTH:-}
PASSWORD_MAX_LENGTH=${PASSWORD_MAX_LENGTH:-}
DEFAULT_FREERADIUS_SERVER=${DEFAULT_FREERADIUS_SERVER:-radius}
DEFAULT_FREERADIUS_PORT=${DEFAULT_FREERADIUS_PORT:-}
DEFAULT_CLIENT_SECRET=${DEFAULT_CLIENT_SECRET:-}
DALORADIUS_ADMIN_PASSWORD=${DALORADIUS_ADMIN_PASSWORD:-}
MAIL_SMTPADDR=${MAIL_SMTPADDR:-}
MAIL_PORT=${MAIL_PORT:-}
MAIL_FROM=${MAIL_FROM:-}
MAIL_AUTH=${MAIL_AUTH:-}
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

function php_escape {
    printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/\\\\'/g"
}

function php_config_set {
    local key="$1"
    local value
    value=$(escape_sed_replacement "$(php_escape "$2")")
    sed -i "s|\\\$configValues\['$key'\] = .*;|\\\$configValues['$key'] = '$value';|" "$DALORADIUS_CONF_PATH"
}

function sql_escape {
    printf '%s' "$1" | sed "s/'/''/g"
}

function require_admin_password {
    if [ -z "$DALORADIUS_ADMIN_PASSWORD" ]; then
        echo "DALORADIUS_ADMIN_PASSWORD must be set."
        exit 1
    fi
}

function set_admin_password {
    require_admin_password

    local admin_hash
    admin_hash=$(php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "$DALORADIUS_ADMIN_PASSWORD")
    admin_hash=$(sql_escape "$admin_hash")

    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
        -e "UPDATE operators SET password='$admin_hash' WHERE username='administrator';"
}

function init_daloradius {

    if ! test -f "$DALORADIUS_CONF_PATH" || ! test -s "$DALORADIUS_CONF_PATH"; then
        cp "$DALORADIUS_CONF_PATH.sample" "$DALORADIUS_CONF_PATH"
    fi
    php_config_set "CONFIG_DB_HOST" "$MYSQL_HOST"
    php_config_set "CONFIG_DB_PORT" "$MYSQL_PORT"
    php_config_set "CONFIG_DB_PASS" "$MYSQL_PASSWORD"
    php_config_set "CONFIG_DB_USER" "$MYSQL_USER"
    php_config_set "CONFIG_DB_NAME" "$MYSQL_DATABASE"
    php_config_set "FREERADIUS_VERSION" "3"
    [ -n "$PASSWORD_MIN_LENGTH" ] && php_config_set "CONFIG_DB_PASSWORD_MIN_LENGTH" "$PASSWORD_MIN_LENGTH"
    [ -n "$PASSWORD_MAX_LENGTH" ] && php_config_set "CONFIG_DB_PASSWORD_MAX_LENGTH" "$PASSWORD_MAX_LENGTH"

    php_config_set "CONFIG_MAINT_TEST_USER_RADIUSSERVER" "$DEFAULT_FREERADIUS_SERVER"
    [ -n "$DEFAULT_FREERADIUS_PORT" ] && php_config_set "CONFIG_MAINT_TEST_USER_RADIUSPORT" "$DEFAULT_FREERADIUS_PORT"
    [ -n "$DEFAULT_CLIENT_SECRET" ] && php_config_set "CONFIG_MAINT_TEST_USER_RADIUSSECRET" "$DEFAULT_CLIENT_SECRET"

    [ -n "$MAIL_SMTPADDR" ] && php_config_set "CONFIG_MAIL_SMTPADDR" "$MAIL_SMTPADDR"
    [ -n "$MAIL_PORT" ] && php_config_set "CONFIG_MAIL_SMTPPORT" "$MAIL_PORT"
    [ -n "$MAIL_FROM" ] && php_config_set "CONFIG_MAIL_SMTPFROM" "$MAIL_FROM"
    [ -n "$MAIL_AUTH" ] && php_config_set "CONFIG_MAIL_SMTPAUTH" "$MAIL_AUTH"
    php_config_set "CONFIG_LOG_FILE" "/tmp/daloradius.log"

    echo "daloRADIUS initialization completed."
}

function init_database {
    # The official MariaDB container creates MYSQL_DATABASE/MYSQL_USER during startup.
    # Import the daloRADIUS schema with the application user instead of trying to
    # create local users/databases from the web container.
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql"
    echo "Database initialization for daloRADIUS completed."
}

function daloradius_schema_ready {
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
        -e "SELECT 1 FROM operators LIMIT 1;" >/dev/null 2>&1
}

function wait_for_mysql {
    echo -n "Waiting for mysql ($MYSQL_HOST)..."
    local attempt=1
    while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent; do
        if [ "$attempt" -ge "$MYSQL_WAIT_RETRIES" ]; then
            echo "failed"
            echo "MySQL did not become ready after $MYSQL_WAIT_RETRIES attempts."
            exit 1
        fi
        attempt=$((attempt + 1))
        sleep "$MYSQL_WAIT_INTERVAL"
    done
    echo "ok"
}

echo "Starting daloRADIUS..."

INIT_LOCK=/data/.init_done
init_daloradius
date > "$INIT_LOCK"

# wait for MySQL-Server to be ready
wait_for_mysql

DB_LOCK=/data/.db_init_done
if daloradius_schema_ready; then
    echo "Database schema already present, skipping initial setup of mysql database."
    date > "$DB_LOCK"
else
    init_database
    if ! daloradius_schema_ready; then
        echo "daloRADIUS database schema was not found after initialization."
        exit 1
    fi
    date > "$DB_LOCK"
fi

set_admin_password

# Start Apache2 in the foreground
exec /usr/sbin/apachectl -DFOREGROUND -k start
