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
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-5}
PASSWORD_MIN_LENGTH=${PASSWORD_MIN_LENGTH:-}
PASSWORD_MAX_LENGTH=${PASSWORD_MAX_LENGTH:-}
DEFAULT_FREERADIUS_SERVER=${DEFAULT_FREERADIUS_SERVER:-radius}
DEFAULT_FREERADIUS_PORT=${DEFAULT_FREERADIUS_PORT:-}
DEFAULT_CLIENT_SECRET=${DEFAULT_CLIENT_SECRET:-}
MAIL_SMTPADDR=${MAIL_SMTPADDR:-}
MAIL_PORT=${MAIL_PORT:-}
MAIL_FROM=${MAIL_FROM:-}
MAIL_AUTH=${MAIL_AUTH:-}

MYSQL_DEFAULTS_FILE=""

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
    sed -i "s|\$configValues\['$key'\] = .*;|\$configValues['$key'] = '$value';|" "$DALORADIUS_CONF_PATH"
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
    php_config_set "CONFIG_LOG_FILE" "/var/www/daloradius/var/log/daloradius.log"

    chown www-data:www-data "$DALORADIUS_CONF_PATH"
    chmod 0600 "$DALORADIUS_CONF_PATH"

    echo "daloRADIUS initialization completed."
}

function init_database {
    # The official MariaDB container creates MYSQL_DATABASE/MYSQL_USER during startup.
    # Import the daloRADIUS schema with the application user instead of trying to
    # create local users/databases from the web container.
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql"
    echo "Database initialization for daloRADIUS completed."
}

function wait_for_mysql {
    echo -n "Waiting for mysql ($MYSQL_HOST)..."
    while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent; do
        sleep "$MYSQL_WAIT_INTERVAL"
    done
    echo "ok"
}

echo "Starting daloRADIUS..."
create_mysql_defaults_file

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
    date > "$INIT_LOCK"
fi

wait_for_mysql

DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
    echo "Database lock file exists, skipping initial setup of mysql database."
else
    init_database
    date > "$DB_LOCK"
fi

# Start Apache2 in the foreground
cleanup_mysql_defaults
trap - EXIT
exec /usr/sbin/apachectl -DFOREGROUND -k start
