#!/bin/bash
# Executable process script for daloRADIUS docker image:
# GitHub: git@github.com:lirantal/daloradius.git
set -euo pipefail

MIGRATE_ONLY=false
case "${1:-}" in
    "") [[ $# -eq 0 ]] || { echo "Unexpected arguments." >&2; exit 2; } ;;
    --migrate-only) [[ $# -eq 1 ]] || { echo "Unexpected arguments." >&2; exit 2; }; MIGRATE_ONLY=true ;;
    *) echo "Usage: init.sh [--migrate-only]" >&2; exit 2 ;;
esac

DALORADIUS_PATH=/var/www/daloradius
DALORADIUS_CONF_PATH=/var/www/daloradius/app/common/includes/daloradius.conf.php

MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_PORT=${MYSQL_PORT:-3306}
MYSQL_DATABASE=${MYSQL_DATABASE:-raddb}
MYSQL_USER=${MYSQL_USER:-raduser}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-radpass}
MYSQL_WAIT_INTERVAL=${MYSQL_WAIT_INTERVAL:-5}

function normalize_ldap_timeout {
    local value="$1"

    case "$value" in
        ''|*[!0-9]*)
            echo "Invalid integer value for CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT." >&2
            return 1
            ;;
    esac

    # PHP interprets an unquoted leading-zero integer as octal. Canonicalize
    # the decimal string before writing it to daloradius.conf.php.
    while [ "${#value}" -gt 1 ] && [ "${value:0:1}" = 0 ]; do
        value="${value:1}"
    done

    if [ "${#value}" -gt 2 ] || {
        [ "${#value}" -eq 2 ] && [ "$value" -gt 30 ]
    }; then
        value=30
    fi
    if [ "$value" -lt 1 ]; then
        value=1
    fi
    printf '%s' "$value"
}

PASSWORD_MIN_LENGTH=${PASSWORD_MIN_LENGTH:-}
PASSWORD_MAX_LENGTH=${PASSWORD_MAX_LENGTH:-}
DEFAULT_FREERADIUS_SERVER=${DEFAULT_FREERADIUS_SERVER:-radius}
DEFAULT_FREERADIUS_PORT=${DEFAULT_FREERADIUS_PORT:-}
DEFAULT_CLIENT_SECRET=${DEFAULT_CLIENT_SECRET:-}
MAIL_SMTPADDR=${MAIL_SMTPADDR:-}
MAIL_PORT=${MAIL_PORT:-}
MAIL_FROM=${MAIL_FROM:-}
MAIL_AUTH=${MAIL_AUTH:-}
OPERATOR_AUTH_LOCAL_ENABLED=${DALORADIUS_OPERATOR_AUTH_LOCAL_ENABLED:-true}
OPERATOR_AUTH_LDAP_ENABLED=${DALORADIUS_OPERATOR_AUTH_LDAP_ENABLED:-false}
OPERATOR_AUTH_DEFAULT=${DALORADIUS_OPERATOR_AUTH_DEFAULT:-local}
LDAP_URI_JSON=${DALORADIUS_LDAP_URI:-[]}
LDAP_SECURITY=${DALORADIUS_LDAP_SECURITY:-starttls}
LDAP_TLS_VERIFY=${DALORADIUS_LDAP_TLS_VERIFY:-true}
LDAP_TLS_CA_FILE=${DALORADIUS_LDAP_TLS_CA_FILE:-}
LDAP_BASE_DN=${DALORADIUS_LDAP_BASE_DN:-}
LDAP_USER_BASE_DN=${DALORADIUS_LDAP_USER_BASE_DN:-}
LDAP_BIND_DN=${DALORADIUS_LDAP_BIND_DN:-}
LDAP_FILTER=${DALORADIUS_LDAP_FILTER:-}
LDAP_EXTERNAL_ID_ATTRIBUTE=${DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE:-}
LDAP_TIMEOUT=$(normalize_ldap_timeout "${DALORADIUS_LDAP_TIMEOUT:-5}")
LDAP_ALLOWED_GROUPS_JSON=${DALORADIUS_LDAP_ALLOWED_GROUPS:-[]}
LDAP_GROUP_ATTRIBUTE=${DALORADIUS_LDAP_GROUP_ATTRIBUTE:-memberOf}
LDAP_GROUP_MATCHING_RULE=${DALORADIUS_LDAP_GROUP_MATCHING_RULE:-}

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

function php_config_set_boolean {
    local key="$1"
    local raw="$2"
    local value
    case "${raw,,}" in
        1|true|yes|on) value=true ;;
        0|false|no|off) value=false ;;
        *)
            echo "Invalid boolean value for ${key}." >&2
            exit 1
            ;;
    esac
    sed -i "s|\$configValues\['$key'\] = .*;|\$configValues['$key'] = $value;|" "$DALORADIUS_CONF_PATH"
}

function php_config_set_integer {
    local key="$1"
    local value="$2"
    case "$value" in
        ''|*[!0-9]*)
            echo "Invalid integer value for ${key}." >&2
            exit 1
            ;;
    esac
    sed -i "s|\$configValues\['$key'\] = .*;|\$configValues['$key'] = $value;|" "$DALORADIUS_CONF_PATH"
}

function php_config_set_array {
    local key="$1"
    local json="$2"
    local value

    # JSON is used instead of delimiter splitting so commas, spaces, and DN
    # punctuation remain data. The value is validated before touching config.
    value=$(php -r '$value = json_decode($argv[1], true); if (!is_array($value) || json_last_error() !== JSON_ERROR_NONE) { exit(1); } echo var_export($value, true);' "$json" | tr -d '\n') || {
        echo "Invalid JSON array value for ${key}." >&2
        exit 1
    }
    value=$(escape_sed_replacement "$value")
    sed -i "s|\$configValues\['$key'\] = .*;|\$configValues['$key'] = $value;|" "$DALORADIUS_CONF_PATH"
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

    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LOCAL_ENABLED" "$OPERATOR_AUTH_LOCAL_ENABLED"
    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LDAP_ENABLED" "$OPERATOR_AUTH_LDAP_ENABLED"
    php_config_set "CONFIG_OPERATOR_AUTH_DEFAULT" "$OPERATOR_AUTH_DEFAULT"
    php_config_set_array "CONFIG_OPERATOR_AUTH_LDAP_URI" "$LDAP_URI_JSON"
    php_config_set "CONFIG_OPERATOR_AUTH_LDAP_SECURITY" "$LDAP_SECURITY"
    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY" "$LDAP_TLS_VERIFY"
    [ -n "$LDAP_TLS_CA_FILE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE" "$LDAP_TLS_CA_FILE"
    [ -n "$LDAP_BASE_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_BASE_DN" "$LDAP_BASE_DN"
    [ -n "$LDAP_USER_BASE_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN" "$LDAP_USER_BASE_DN"
    [ -n "$LDAP_BIND_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_BIND_DN" "$LDAP_BIND_DN"
    [ -n "$LDAP_FILTER" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_FILTER" "$LDAP_FILTER"
    [ -n "$LDAP_EXTERNAL_ID_ATTRIBUTE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE" "$LDAP_EXTERNAL_ID_ATTRIBUTE"
    php_config_set_integer "CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT" "$LDAP_TIMEOUT"
    php_config_set_array "CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS" "$LDAP_ALLOWED_GROUPS_JSON"
    php_config_set "CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE" "$LDAP_GROUP_ATTRIBUTE"
    php_config_set "CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE" "$LDAP_GROUP_MATCHING_RULE"

    php_config_set "CONFIG_LOG_FILE" "/var/www/daloradius/var/log/daloradius.log"

    chown www-data:www-data "$DALORADIUS_CONF_PATH"
    chmod 0600 "$DALORADIUS_CONF_PATH"

    echo "daloRADIUS initialization completed."
}

function refresh_operator_auth_config {
    if [ ! -s "$DALORADIUS_CONF_PATH" ]; then
        return
    fi

    # Operator-auth environment settings are runtime deployment settings. Apply
    # them on every container start, not only while creating the data volume.
    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LOCAL_ENABLED" "$OPERATOR_AUTH_LOCAL_ENABLED"
    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LDAP_ENABLED" "$OPERATOR_AUTH_LDAP_ENABLED"
    php_config_set "CONFIG_OPERATOR_AUTH_DEFAULT" "$OPERATOR_AUTH_DEFAULT"
    php_config_set_array "CONFIG_OPERATOR_AUTH_LDAP_URI" "$LDAP_URI_JSON"
    php_config_set "CONFIG_OPERATOR_AUTH_LDAP_SECURITY" "$LDAP_SECURITY"
    php_config_set_boolean "CONFIG_OPERATOR_AUTH_LDAP_TLS_VERIFY" "$LDAP_TLS_VERIFY"
    [ -n "$LDAP_TLS_CA_FILE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_TLS_CA_FILE" "$LDAP_TLS_CA_FILE"
    [ -n "$LDAP_BASE_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_BASE_DN" "$LDAP_BASE_DN"
    [ -n "$LDAP_USER_BASE_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_USER_BASE_DN" "$LDAP_USER_BASE_DN"
    [ -n "$LDAP_BIND_DN" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_BIND_DN" "$LDAP_BIND_DN"
    [ -n "$LDAP_FILTER" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_FILTER" "$LDAP_FILTER"
    [ -n "$LDAP_EXTERNAL_ID_ATTRIBUTE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE" "$LDAP_EXTERNAL_ID_ATTRIBUTE"
    php_config_set_integer "CONFIG_OPERATOR_AUTH_LDAP_TIMEOUT" "$LDAP_TIMEOUT"
    php_config_set_array "CONFIG_OPERATOR_AUTH_LDAP_ALLOWED_GROUPS" "$LDAP_ALLOWED_GROUPS_JSON"
    php_config_set "CONFIG_OPERATOR_AUTH_LDAP_GROUP_ATTRIBUTE" "$LDAP_GROUP_ATTRIBUTE"
    [ -n "$LDAP_GROUP_MATCHING_RULE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_GROUP_MATCHING_RULE" "$LDAP_GROUP_MATCHING_RULE"
    chown www-data:www-data "$DALORADIUS_CONF_PATH"
    chmod 0600 "$DALORADIUS_CONF_PATH"
}

function init_freeradius_database {
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" < "$DALORADIUS_PATH/contrib/db/fr3-mariadb-freeradius.sql"
    echo "Database initialization for FreeRADIUS base schema completed."
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

function count_existing_tables {
    local table_name
    local in_list=""
    local escaped_table_name

    for table_name in "$@"; do
        escaped_table_name=$(printf '%s' "$table_name" | sed -e "s/'/''/g")
        if [ -n "$in_list" ]; then
            in_list="${in_list},'${escaped_table_name}'"
        else
            in_list="'${escaped_table_name}'"
        fi
    done

    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --skip-column-names "$MYSQL_DATABASE" <<EOSQL
SELECT COUNT(*)
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN ($in_list);
EOSQL
}

function ensure_dictionary {
    # The dictionary is bundled separately from the main daloRADIUS schema.
    # Its SQL file drops/recreates the table, so never import it over an
    # existing dictionary (which may contain deployment-specific attributes).
    if table_exists "dictionary"; then
        return
    fi

    echo "Initializing missing daloRADIUS dictionary table."
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$MYSQL_DATABASE" \
        < "$DALORADIUS_PATH/contrib/db/mariadb-daloradius-dictionaries.sql"
    echo "daloRADIUS dictionary initialization completed."
}

function run_schema_migrations {
    # One database-scoped ledger for Docker, the upgrader and manual installs.
    # On a freshly seeded database, mariadb-daloradius.sql already includes the
    # bundled schema changes, so baseline the ledger without replaying old SQL.
    # A failure exits before Apache starts; do not mark or hide failed SQL.
    local migrate_flag="--apply"
    if [[ "${FRESH_DB_INIT:-false}" == true ]]; then
        migrate_flag="--baseline-fresh"
    fi
    runuser -u www-data -- php "$DALORADIUS_PATH/contrib/scripts/maintenance/migrate-db.php" \
        --config="$DALORADIUS_CONF_PATH" "$migrate_flag"
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

refresh_operator_auth_config
wait_for_mysql

FRESH_DB_INIT=false
DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
    echo "Database lock file exists, skipping initial setup of mysql database."
else
    # All non-migration base tables created by mariadb-daloradius.sql.
    # Migration-created tables (chilli_paypal_events), the migration ledger
    # (daloradius_schema_migrations), and the separately loaded dictionary table
    # are allowed to be absent on pre-migration existing databases.
    REQUIRED_DALO_TABLES=(
        batch_history billing_history billing_merchant billing_paypal
        billing_plans billing_plans_profiles billing_rates hotspots invoice
        invoice_items invoice_status invoice_type messages node operators
        operators_acl operators_acl_files payment payment_type proxys rates
        realms userbillinfo userinfo
    )
    ALL_DALO_TABLES=(
        "${REQUIRED_DALO_TABLES[@]}"
        chilli_paypal_events daloradius_schema_migrations dictionary
    )
    CORE_FREERADIUS_TABLES=(
        radacct radcheck radgroupcheck radgroupreply radreply radusergroup
        radpostauth nas
    )
    ALL_FREERADIUS_TABLES=(
        "${CORE_FREERADIUS_TABLES[@]}"
        nasreload radippool wimax cui radhuntgroup
    )
    EXISTING_REQUIRED_DALO=$(count_existing_tables "${REQUIRED_DALO_TABLES[@]}")
    EXISTING_ANY_DALO=$(count_existing_tables "${ALL_DALO_TABLES[@]}")

    if [ "$EXISTING_REQUIRED_DALO" -eq "${#REQUIRED_DALO_TABLES[@]}" ]; then
        echo "Existing daloRADIUS database schema detected, skipping initial setup of mysql database."
    elif [ "$EXISTING_ANY_DALO" -gt 0 ]; then
        echo "ERROR: Partial daloRADIUS database schema detected (${EXISTING_REQUIRED_DALO}/${#REQUIRED_DALO_TABLES[@]} required base tables, ${EXISTING_ANY_DALO} total daloRADIUS tables)." >&2
        echo "ERROR: Refusing to import mariadb-daloradius.sql (which drops tables). Inspect or restore the database manually." >&2
        exit 1
    else
        # mariadb-daloradius.sql inserts into radgroupcheck and indexes radacct/radpostauth/radcheck.
        # Only skip fr3-mariadb-freeradius.sql when all 13 declared FreeRADIUS tables exist.
        # Because fr3-mariadb-freeradius.sql uses CREATE TABLE IF NOT EXISTS exclusively, it is
        # safe to run on a truly empty DB or on an existing standard FreeRADIUS DB that has all
        # 8 core tables but lacks optional tables (e.g. wimax/cui/radhuntgroup). Refuse truly
        # partial FreeRADIUS schemas with missing core tables.
        EXISTING_ALL_FR=$(count_existing_tables "${ALL_FREERADIUS_TABLES[@]}")
        EXISTING_CORE_FR=$(count_existing_tables "${CORE_FREERADIUS_TABLES[@]}")
        if [ "$EXISTING_ALL_FR" -eq "${#ALL_FREERADIUS_TABLES[@]}" ]; then
            echo "Existing FreeRADIUS database schema detected, skipping FreeRADIUS base schema import."
        elif [ "$EXISTING_ALL_FR" -eq 0 ] || [ "$EXISTING_CORE_FR" -eq "${#CORE_FREERADIUS_TABLES[@]}" ]; then
            init_freeradius_database
        else
            echo "ERROR: Partial FreeRADIUS database schema detected (${EXISTING_CORE_FR}/${#CORE_FREERADIUS_TABLES[@]} core tables, ${EXISTING_ALL_FR}/${#ALL_FREERADIUS_TABLES[@]} total FreeRADIUS tables)." >&2
            echo "ERROR: Refusing to run initial database import over a partial schema. Inspect or restore the database manually." >&2
            exit 1
        fi
        init_database
        FRESH_DB_INIT=true
    fi
    date > "$DB_LOCK"
fi

# Check outside DB_LOCK so already-initialized deployments missing the
# dictionary are repaired without reimporting the application schema.
ensure_dictionary
run_schema_migrations
if [[ "$MIGRATE_ONLY" == true ]]; then
    echo "Schema migration completed; application services were not started."
    exit 0
fi

# Start Apache2 in the foreground
cleanup_mysql_defaults
trap - EXIT
exec /usr/sbin/apachectl -DFOREGROUND -k start
