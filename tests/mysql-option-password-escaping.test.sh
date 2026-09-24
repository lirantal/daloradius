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

cleanup() {
    rm -f "${MYSQL_DEFAULTS_FILE:-}" "${MARIADB_CLIENT_FILENAME:-}"
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
            my_print_defaults --defaults-extra-file=/tmp/mysql-options.cnf client
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
        fail "password was not preserved by the MariaDB option-file parser"
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

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        'abc\123' \
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

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        'abc\123' \
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
    local network="daloradius-mysql-option-test"
    local container="daloradius-mysql-option-test-db"
    local root_password="root-test-password"
    local escaped_sql_password
    local parsed

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

    cleanup_install_test() {
        docker rm -f daloradius-mysql-option-test-db >/dev/null 2>&1 || true
        docker network rm daloradius-mysql-option-test >/dev/null 2>&1 || true
    }

    cleanup_install_test

    docker network create "$network" >/dev/null
    trap cleanup_install_test EXIT

    docker run -d \
        --name "$container" \
        --network "$network" \
        -e MARIADB_ROOT_PASSWORD="$root_password" \
        mariadb:11.8 >/dev/null

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        "abc'123" \
        'abc\123' \
        $'abc\n123'
    do
        DB_HOST="$container"
        DB_PORT=3306
        DB_SCHEMA=raddb
        DB_USER=raduser
        DB_PASS="$password"

        escaped_sql_password=$(escape_mysql_sql_string "$DB_PASS")

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
            fail "MariaDB test container did not become ready in time"
        fi

        docker run --rm \
            --network "$network" \
            mariadb:11.8 \
            mariadb \
            --host="$container" \
            --user=root \
            --password="$root_password" \
            --execute="CREATE DATABASE IF NOT EXISTS ${DB_SCHEMA};
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

    cleanup_install_test
    trap cleanup EXIT
}

test_init_script
test_freeradius_script
test_install_script

printf 'PASS: MySQL option-file password escaping and MariaDB parsing\n'
