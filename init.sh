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
MYSQL_PASSWORD=${MYSQL_PASSWORD:-}
MYSQL_WAIT_RETRIES=${MYSQL_WAIT_RETRIES:-30}
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-2}
INIT_ERROR_LOG=${INIT_ERROR_LOG:-/data/daloradius-init-errors.log}
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

function log_event {
    local level="$1"
    local event="$2"
    local outcome="$3"
    local detail="$4"
    printf 'level=%s component=daloradius-init event=%s outcome=%s detail=%s\n' "$level" "$event" "$outcome" "$detail"
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

function validate_optional_no_crlf {
    local name="$1"
    local value="${!name:-}"
    if [ -n "$value" ]; then
        reject_crlf "$name"
    fi
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
    require_non_empty "DALORADIUS_ADMIN_PASSWORD"
    reject_crlf "DALORADIUS_ADMIN_PASSWORD"
    reject_crlf "DEFAULT_FREERADIUS_SERVER"
    validate_optional_no_crlf "DEFAULT_FREERADIUS_PORT"
    validate_optional_no_crlf "DEFAULT_CLIENT_SECRET"
    validate_optional_no_crlf "PASSWORD_MIN_LENGTH"
    validate_optional_no_crlf "PASSWORD_MAX_LENGTH"
    validate_optional_no_crlf "MAIL_SMTPADDR"
    validate_optional_no_crlf "MAIL_PORT"
    validate_optional_no_crlf "MAIL_FROM"
    validate_optional_no_crlf "MAIL_AUTH"
}

setup_error_log
validate_runtime_config
setup_mysql_defaults_file

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
    sed -i "s|\\\$configValues\['$key'\] = .*;|\\\$configValues['$key'] = '$value';|" "$DALORADIUS_CONF_PATH" \
        2>>"$INIT_ERROR_LOG" || fail_step "daloradius_init" "config_update_failed"
}

function sql_escape {
    printf '%s' "$1" | sed "s/'/''/g"
}

function require_admin_password {
    if [ -z "$DALORADIUS_ADMIN_PASSWORD" ]; then
        fail_step "validation" "missing_required_DALORADIUS_ADMIN_PASSWORD"
    fi
}

function set_admin_password {
    require_admin_password

    local admin_hash
    admin_hash=$(php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "$DALORADIUS_ADMIN_PASSWORD")
    admin_hash=$(sql_escape "$admin_hash")

    log_event "info" "admin_password_update" "start" "updating_admin_password"
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
        -e "UPDATE operators SET password='$admin_hash' WHERE username='administrator';" \
        2>>"$INIT_ERROR_LOG" || fail_step "admin_password_update" "admin_password_update_failed"
    log_event "info" "admin_password_update" "success" "admin_password_updated"
}

function init_daloradius {
    log_event "info" "daloradius_init" "start" "configuring_daloradius"

    if ! test -f "$DALORADIUS_CONF_PATH" || ! test -s "$DALORADIUS_CONF_PATH"; then
        cp "$DALORADIUS_CONF_PATH.sample" "$DALORADIUS_CONF_PATH" 2>>"$INIT_ERROR_LOG" \
            || fail_step "daloradius_init" "config_copy_failed"
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
    chown www-data:www-data "$DALORADIUS_CONF_PATH" 2>>"$INIT_ERROR_LOG" \
        || fail_step "daloradius_init" "config_owner_failed"
    chmod 0644 "$DALORADIUS_CONF_PATH" 2>>"$INIT_ERROR_LOG" \
        || fail_step "daloradius_init" "config_mode_failed"

    log_event "info" "daloradius_init" "success" "daloradius_configured"
    echo "daloRADIUS initialization completed."
}

function init_database {
    # The official MariaDB container creates MYSQL_DATABASE/MYSQL_USER during startup.
    # Import the daloRADIUS schema with the application user instead of trying to
    # create local users/databases from the web container.
    log_event "info" "daloradius_schema_import" "start" "importing_schema"
    if mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$DALORADIUS_PATH/contrib/db/mariadb-daloradius.sql" 2>>"$INIT_ERROR_LOG"; then
        log_event "info" "daloradius_schema_import" "success" "schema_imported"
        echo "Database initialization for daloRADIUS completed."
    else
        fail_step "daloradius_schema_import" "schema_import_failed"
    fi
}

function daloradius_schema_ready {
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
        -e "SELECT 1 FROM operators LIMIT 1;" >/dev/null 2>&1
}

function wait_for_mysql {
    log_event "info" "mysql_wait" "start" "waiting_for_mysql"
    local attempt=1
    while ! mysqladmin --defaults-extra-file="$MYSQL_DEFAULTS_FILE" ping --silent >/dev/null 2>>"$INIT_ERROR_LOG"; do
        if [ "$attempt" -ge "$MYSQL_WAIT_RETRIES" ]; then
            fail_step "mysql_wait" "mysql_wait_timeout"
        fi
        attempt=$((attempt + 1))
        sleep "$MYSQL_WAIT_INTERVAL"
    done
    log_event "info" "mysql_wait" "success" "mysql_ready"
}

echo "Starting daloRADIUS..."

INIT_LOCK=/data/.init_done
init_daloradius
write_lock "$INIT_LOCK" "daloradius_init_lock"

# wait for MySQL-Server to be ready
wait_for_mysql

DB_LOCK=/data/.db_init_done
if daloradius_schema_ready; then
    echo "Database schema already present, skipping initial setup of mysql database."
    write_lock "$DB_LOCK" "daloradius_db_lock"
else
    init_database
    log_event "info" "daloradius_schema_check" "start" "checking_schema"
    if ! daloradius_schema_ready; then
        fail_step "daloradius_schema_check" "schema_post_check_failed"
    fi
    log_event "info" "daloradius_schema_check" "success" "schema_present"
    write_lock "$DB_LOCK" "daloradius_db_lock"
fi

set_admin_password

# Start Apache2 in the foreground
exec /usr/sbin/apachectl -DFOREGROUND -k start
