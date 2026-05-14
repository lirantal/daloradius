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
MAIL_SMTPADDR=${MAIL_SMTPADDR:-}
MAIL_PORT=${MAIL_PORT:-}
MAIL_FROM=${MAIL_FROM:-}
MAIL_AUTH=${MAIL_AUTH:-}


function init_daloradius {

    if ! test -f "$DALORADIUS_CONF_PATH" || ! test -s "$DALORADIUS_CONF_PATH"; then
        cp "$DALORADIUS_CONF_PATH.sample" "$DALORADIUS_CONF_PATH"
        chown www-data:www-data "$DALORADIUS_CONF_PATH"
    fi
    [ -n "$MYSQL_HOST" ] && sed -i "s/\$configValues\['CONFIG_DB_HOST'\] = .*;/\$configValues\['CONFIG_DB_HOST'\] = '$MYSQL_HOST';/" $DALORADIUS_CONF_PATH || MYSQL_HOST=localhost
    [ -n "$MYSQL_PORT" ] && sed -i "s/\$configValues\['CONFIG_DB_PORT'\] = .*;/\$configValues\['CONFIG_DB_PORT'\] = '$MYSQL_PORT';/" $DALORADIUS_CONF_PATH
    [ -n "$MYSQL_PASSWORD" ] && sed -i "s/\$configValues\['CONFIG_DB_PASS'\] = .*;/\$configValues\['CONFIG_DB_PASS'\] = '$MYSQL_PASSWORD';/" $DALORADIUS_CONF_PATH || MYSQL_PASSWORD=radpass
    [ -n "$MYSQL_USER" ] && sed -i "s/\$configValues\['CONFIG_DB_USER'\] = .*;/\$configValues\['CONFIG_DB_USER'\] = '$MYSQL_USER';/" $DALORADIUS_CONF_PATH || MYSQL_USER=raduser
    [ -n "$MYSQL_DATABASE" ] && sed -i "s/\$configValues\['CONFIG_DB_NAME'\] = .*;/\$configValues\['CONFIG_DB_NAME'\] = '$MYSQL_DATABASE';/" $DALORADIUS_CONF_PATH || MYSQL_DATABASE=raddb
    sed -i "s/\$configValues\['FREERADIUS_VERSION'\] = .*;/\$configValues\['FREERADIUS_VERSION'\] = '3';/" $DALORADIUS_CONF_PATH
    [ -n "$PASSWORD_MIN_LENGTH" ] && sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MIN_LENGTH'\] = '$PASSWORD_MIN_LENGTH';/" $DALORADIUS_CONF_PATH
    [ -n "$PASSWORD_MAX_LENGTH" ] && sed -i "s/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = .*;/\$configValues\['CONFIG_DB_PASSWORD_MAX_LENGTH'\] = '$PASSWORD_MAX_LENGTH';/" $DALORADIUS_CONF_PATH

    [ -n "$DEFAULT_FREERADIUS_SERVER" ] \
        && sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = '$DEFAULT_FREERADIUS_SERVER';/" $DALORADIUS_CONF_PATH \
        || sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = 'radius';/" $DALORADIUS_CONF_PATH
    [ -n "$DEFAULT_FREERADIUS_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSPORT'\] = '$DEFAULT_FREERADIUS_PORT';/" $DALORADIUS_CONF_PATH
    [ -n "$DEFAULT_CLIENT_SECRET" ] && sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = '$DEFAULT_CLIENT_SECRET';/" $DALORADIUS_CONF_PATH

    [ -n "$MAIL_SMTPADDR" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = '$MAIL_SMTPADDR';/" $DALORADIUS_CONF_PATH
    [ -n "$MAIL_PORT" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = '$MAIL_PORT';/" $DALORADIUS_CONF_PATH
    [ -n "$MAIL_FROM" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = '$MAIL_FROM';/" $DALORADIUS_CONF_PATH
    [ -n "$MAIL_AUTH" ] && sed -i "s/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = '$MAIL_AUTH';/" $DALORADIUS_CONF_PATH
    sed -i "s/\$configValues\['CONFIG_LOG_FILE'\] = .*;/\$configValues\['CONFIG_LOG_FILE'\] = '\/tmp\/daloradius.log';/" $DALORADIUS_CONF_PATH

    echo "daloRADIUS initialization completed."
}

function init_database {
    # The official MariaDB container creates MYSQL_DATABASE/MYSQL_USER during startup.
    # Import the daloRADIUS schema with the application user instead of trying to
    # create local users/databases from the web container.
    mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql"
    echo "Database initialization for daloRADIUS completed."
}

function daloradius_schema_ready {
    mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
        -e "SELECT 1 FROM operators LIMIT 1;" >/dev/null 2>&1
}

function wait_for_mysql {
    echo -n "Waiting for mysql ($MYSQL_HOST)..."
    local attempt=1
    while ! mysqladmin ping -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do
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

# Start Apache2 in the foreground
/usr/sbin/apachectl -DFOREGROUND -k start
