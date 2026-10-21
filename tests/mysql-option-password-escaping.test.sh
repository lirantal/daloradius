#!/usr/bin/env bash
set -euo pipefail

ROOT=$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

command -v docker >/dev/null 2>&1 || fail "docker is required"

MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=raduser
MYSQL_PASSWORD=
MYSQL_DEFAULTS_FILE=
DB_SCHEMA=raddb
DB_HOST=localhost
DB_PORT=3306
DB_USER=raduser
DB_PASS=
MARIADB_CLIENT_FILENAME=

print_green() {
    :
}

# Keep cleanup paths in global variables so EXIT traps remain safe
# when the test functions that registered them have returned.
INSTALL_TEST_CONTAINER="daloradius-mysql-option-test-db"
INSTALL_TEST_NETWORK="daloradius-mysql-option-test"

PDO_TEST_NETWORK="daloradius-pdo-password-test"
PDO_TEST_CONTAINER="daloradius-pdo-password-test-db"
PDO_TEST_PHP_CONTAINER="daloradius-pdo-password-test-php"
PDO_TEST_IMAGE="daloradius-pdo-password-test"
PDO_TEST_CONFIG_FILE=

cleanup() {
    rm -f "${MYSQL_DEFAULTS_FILE:-}" \
          "${MARIADB_CLIENT_FILENAME:-}" \
          "${PDO_TEST_CONFIG_FILE:-}"

    docker rm -f \
        "$INSTALL_TEST_CONTAINER" \
        "$PDO_TEST_PHP_CONTAINER" \
        "$PDO_TEST_CONTAINER" >/dev/null 2>&1 || true

    docker network rm \
        "$INSTALL_TEST_NETWORK" \
        "$PDO_TEST_NETWORK" >/dev/null 2>&1 || true

    docker image rm "$PDO_TEST_IMAGE" >/dev/null 2>&1 || true
}

trap cleanup EXIT

test_option_file() {
    local password="$1"
    local option_file="$2"
    local expected_database="${3:-}"

    local parsed
    parsed=$(
        docker run --rm \
            -v "$option_file:/tmp/mysql-options.cnf:ro,Z" \
            mariadb:11.8 \
            my_print_defaults \
            --defaults-extra-file=/tmp/mysql-options.cnf client
    )

    local expected=""

    if [ -n "$expected_database" ]; then
        expected="--database=$expected_database
"
    fi

    expected+="--host=localhost
--port=3306
--user=raduser
--password=$password"

    if [ "$parsed" != "$expected" ]; then
        printf 'Generated option file:\n' >&2
        cat "$option_file" >&2
        printf 'Expected parsed values:\n%s\n' "$expected" >&2
        printf 'Actual parsed values:\n%s\n' "$parsed" >&2
        fail "Password was not preserved by the MariaDB option-file parser"
    fi
}

test_init_script() {
    local init="$ROOT/init.sh"

    eval "$(awk '
        /^function escape_mysql_option_value \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$init")"

    eval "$(awk '
        /^function create_mysql_defaults_file \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$init")"

    local password

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        'abc\123' \
        'abc\n123' \
        $'abc\n123'
    do
        MYSQL_PASSWORD="$password"
        create_mysql_defaults_file
        test_option_file "$password" "$MYSQL_DEFAULTS_FILE"

        rm -f "$MYSQL_DEFAULTS_FILE"
        MYSQL_DEFAULTS_FILE=
    done
}

test_freeradius_script() {
    local init="$ROOT/init-freeradius.sh"

    eval "$(awk '
        /^function escape_mysql_option_value \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$init")"

    eval "$(awk '
        /^function create_mysql_defaults_file \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$init")"

    local password

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        'abc\123' \
        'abc\n123' \
        $'abc\n123'
    do
        MYSQL_PASSWORD="$password"
        create_mysql_defaults_file
        test_option_file "$password" "$MYSQL_DEFAULTS_FILE"

        rm -f "$MYSQL_DEFAULTS_FILE"
        MYSQL_DEFAULTS_FILE=
    done
}

test_install_script() {
    local install="$ROOT/setup/install.sh"
    local network="$INSTALL_TEST_NETWORK"
    local container="$INSTALL_TEST_CONTAINER"
    local root_password="root-test-password"
    local escaped_sql_password
    local parsed
    local password
    local ready

    eval "$(awk '
        /^escape_mysql_option_value\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$install")"

    eval "$(awk '
        /^escape_mysql_sql_string\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$install")"

    eval "$(awk '
        /^mariadb_init_conf\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$install")"

    docker rm -f "$container" >/dev/null 2>&1 || true
    docker network rm "$network" >/dev/null 2>&1 || true

    docker network create "$network" >/dev/null

    docker run -d \
        --name "$container" \
        --network "$network" \
        -e MARIADB_ROOT_PASSWORD="$root_password" \
        mariadb:11.8 >/dev/null

    ready=0

    for _ in {1..30}; do
        if docker run --rm \
            --network "$network" \
            mariadb:11.8 \
            mariadb \
            --host="$container" \
            --user=root \
            --password="$root_password" \
            --execute="SELECT 1" >/dev/null 2>&1
        then
            ready=1
            break
        fi

        sleep 1
    done

    if [ "$ready" -eq 0 ]; then
        fail "MariaDB installer-test container did not become ready in time"
    fi

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        "abc'123" \
        'abc\123' \
        'abc\n123' \
        $'abc\n123'
    do
        DB_HOST="$container"
        DB_PORT=3306
        DB_SCHEMA=raddb
        DB_USER=raduser
        DB_PASS="$password"

        escaped_sql_password=$(escape_mysql_sql_string "$DB_PASS")

        docker run --rm \
            --network "$network" \
            mariadb:11.8 \
            mariadb \
            --host="$container" \
            --user=root \
            --password="$root_password" \
            --execute="SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_BACKSLASH_ESCAPES', '');
CREATE DATABASE IF NOT EXISTS ${DB_SCHEMA};
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${escaped_sql_password}';
ALTER USER '${DB_USER}'@'%' IDENTIFIED BY '${escaped_sql_password}';
GRANT ALL ON ${DB_SCHEMA}.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;" >/dev/null

        mariadb_init_conf >/dev/null

        parsed=$(
            docker run --rm \
                --network "$network" \
                -v "$MARIADB_CLIENT_FILENAME:/tmp/mysql-options.cnf:ro,Z" \
                mariadb:11.8 \
                mariadb \
                --defaults-extra-file=/tmp/mysql-options.cnf \
                --execute="SELECT 1" 2>/dev/null
        )

        if [ "$parsed" != $'1\n1' ]; then
            printf 'Password authentication failed for password test case:\n' >&2
            printf '%q\n' "$password" >&2
            printf 'Generated option file:\n' >&2
            cat "$MARIADB_CLIENT_FILENAME" >&2
            fail "SQL account password and MySQL option-file password do not match"
        fi

        rm -f "$MARIADB_CLIENT_FILENAME"
        MARIADB_CLIENT_FILENAME=
    done

    docker rm -f "$container" >/dev/null
    docker network rm "$network" >/dev/null
}

test_pdo_password_escaping() {
    local network="$PDO_TEST_NETWORK"
    local container="$PDO_TEST_CONTAINER"
    local php_container="$PDO_TEST_PHP_CONTAINER"
    local image="$PDO_TEST_IMAGE"
    local root_password="root-test-password"
    local database="radius"
    local user="radius"
    local password
    local escaped_sql_password
    local ready
    local result

    # The first four cases exercise ordinary special characters.
    # The literal backslash-n case is critical: it is two characters,
    # a backslash and the letter n, not an actual newline.
    password='abc@#%123'

    docker rm -f "$php_container" "$container" >/dev/null 2>&1 || true
    docker network rm "$network" >/dev/null 2>&1 || true
    docker image rm "$image" >/dev/null 2>&1 || true

    PDO_TEST_CONFIG_FILE=$(mktemp)

    docker network create "$network" >/dev/null

    docker build \
        --tag "$image" \
        "$ROOT" >/dev/null

    docker run -d \
        --name "$container" \
        --network "$network" \
        -e MARIADB_ROOT_PASSWORD="$root_password" \
        mariadb:11.8 >/dev/null

    ready=0

    for _ in {1..30}; do
        if docker run --rm \
            --network "$network" \
            mariadb:11.8 \
            mariadb \
            --host="$container" \
            --user=root \
            --password="$root_password" \
            --execute="SELECT 1" >/dev/null 2>&1
        then
            ready=1
            break
        fi

        sleep 1
    done

    if [ "$ready" -eq 0 ]; then
        fail "MariaDB PDO-test container did not become ready in time"
    fi

    # Create a database account with a password containing DSN-sensitive
    # characters. This test uses SQL escaping as used by the installer.
    eval "$(awk '
        /^escape_mysql_sql_string\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$ROOT/setup/install.sh")"

    for password in \
        'abc@#%123' \
        'abc\123' \
        'abc\n123' \
        $'abc\n123'
    do
        escaped_sql_password=$(escape_mysql_sql_string "$password")

        docker run --rm \
            --network "$network" \
            mariadb:11.8 \
            mariadb \
            --host="$container" \
            --user=root \
            --password="$root_password" \
            --execute="SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_BACKSLASH_ESCAPES', '');
CREATE DATABASE IF NOT EXISTS ${database};
CREATE USER IF NOT EXISTS '${user}'@'%' IDENTIFIED BY '${escaped_sql_password}';
ALTER USER '${user}'@'%' IDENTIFIED BY '${escaped_sql_password}';
GRANT ALL ON ${database}.* TO '${user}'@'%';
FLUSH PRIVILEGES;" >/dev/null

        cat > "$PDO_TEST_CONFIG_FILE" <<EOF
<?php
\$configValues = [
    'CONFIG_DB_ENGINE' => 'mysqli',
    'CONFIG_DB_HOST' => '$container',
    'CONFIG_DB_PORT' => 3306,
    'CONFIG_DB_USER' => '$user',
    'CONFIG_DB_PASS' => '$password',
    'CONFIG_DB_NAME' => '$database',
    'CONFIG_LOCATIONS' => [],
];
EOF

        # Escape shell/PHP source interpolation concerns by passing the
        # configured password through an environment variable instead of
        # embedding it directly in the PHP command.
        docker cp \
            "$PDO_TEST_CONFIG_FILE" \
            "$php_container:/tmp/pdo-test-config.php" 2>/dev/null || true

        # Re-create the PHP test container on the first iteration only.
        if ! docker container inspect "$php_container" >/dev/null 2>&1; then
            docker run -d \
                --name "$php_container" \
                --network "$network" \
                --entrypoint sleep \
                "$image" \
                infinity >/dev/null
        fi

        docker cp \
            "$PDO_TEST_CONFIG_FILE" \
            "$php_container:/tmp/pdo-test-config.php"

        result=$(
            docker exec \
                -e TEST_DB_PASSWORD="$password" \
                "$php_container" \
                php -r '
$_SERVER["PHP_SELF"] = "/test.php";
require "/var/www/daloradius/app/common/includes/pdo_connection.php";
require "/tmp/pdo-test-config.php";

$configValues["CONFIG_DB_PASS"] = getenv("TEST_DB_PASSWORD");

try {
    $pdo = dalo_pdo_connect($configValues);
    $result = $pdo->query("SELECT 1")->fetchColumn();

    if ((string) $result !== "1") {
        fwrite(STDERR, "Database query failed\n");
        exit(1);
    }

    echo "DB connection OK\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "PDO connection test failed: " . $exception->getMessage() . "\n");
    exit(1);
}
'
        )

        if [ "$result" != "DB connection OK" ]; then
            printf 'Actual PDO connection result:\n%s\n' "$result" >&2
            printf 'Password test case: ' >&2
            printf '%q\n' "$password" >&2
            fail "PDO connection failed for the configured password"
        fi
    done

    docker rm -f "$php_container" "$container" >/dev/null
    docker network rm "$network" >/dev/null
    docker image rm "$image" >/dev/null

    rm -f "$PDO_TEST_CONFIG_FILE"
    PDO_TEST_CONFIG_FILE=
}

test_init_script
test_freeradius_script
test_install_script
test_pdo_password_escaping

printf 'PASS: MySQL option-file password escaping and MariaDB authentication\n'
