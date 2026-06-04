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

function table_exists {
    local table_name="$1"
    local escaped_table_name
    local count

    escaped_table_name=$(printf '%s' "$table_name" | sed -e "s/'/''/g")
    count=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --skip-column-names "$MYSQL_DATABASE" <<EOSQL
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

function ensure_operator_password_column {
    local column_length

    if ! table_exists "operators"; then
        return
    fi

    column_length=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --skip-column-names "$MYSQL_DATABASE" <<'EOSQL'
SELECT CHARACTER_MAXIMUM_LENGTH
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'operators'
  AND column_name = 'password';
EOSQL
)

    case "$column_length" in
        ""|*[!0-9]*)
            return
            ;;
    esac

    if [ "$column_length" -lt 95 ]; then
        echo "Updating operators.password column length for password hashes."
        mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" <<'EOSQL'
ALTER TABLE operators MODIFY password VARCHAR(95) NOT NULL;
EOSQL
    fi
}

function ensure_operator_totp_columns {
    if ! table_exists "operators"; then
        return
    fi

    local missing_columns
    missing_columns=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --skip-column-names "$MYSQL_DATABASE" <<'EOSQL'
SELECT COUNT(*)
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'operators'
  AND column_name IN ('totp_enabled', 'totp_secret', 'totp_last_counter', 'totp_confirmed_at', 'totp_recovery_codes');
EOSQL
)

    if [ "$missing_columns" -lt 5 ]; then
        echo "Adding operator TOTP columns."
        mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" <<'EOSQL'
ALTER TABLE operators
  ADD COLUMN IF NOT EXISTS totp_enabled TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS totp_secret VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_last_counter BIGINT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_confirmed_at DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_recovery_codes TEXT DEFAULT NULL;
EOSQL
    fi

    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" <<'EOSQL'
INSERT IGNORE INTO operators_acl_files (file, category, section)
VALUES ('config_operator_2fa', 'Configuration', 'Operators');

INSERT IGNORE INTO operators_acl (operator_id, file, access)
SELECT id, 'config_operator_2fa', 1
FROM operators
WHERE username = 'administrator';
EOSQL
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
    if tables_exist "operators" "operators_acl" "operators_acl_files"; then
        echo "Existing daloRADIUS database schema detected, skipping initial setup of mysql database."
    else
        init_database
    fi
    date > "$DB_LOCK"
fi

ensure_operator_password_column
ensure_operator_totp_columns

# Start Apache2 in the foreground
cleanup_mysql_defaults
trap - EXIT
exec /usr/sbin/apachectl -DFOREGROUND -k start
