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

    eval "$(awk '
        /^escape_mysql_option_value\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$install")"

    eval "$(awk '
        /^mariadb_init_conf\(\) \{/ { capture=1 }
        capture { print }
        capture && /^}/ { exit }
    ' "$install")"

    for password in \
        'abc#123' \
        'abc;123' \
        'abc 123' \
        ' pass ' \
        'abc"123' \
        'abc\123' \
        $'abc\n123'
    do
        DB_PASS="$password"
        mariadb_init_conf >/dev/null
        test_option_file "$password" "$MARIADB_CLIENT_FILENAME" "$DB_SCHEMA"
        rm -f "$MARIADB_CLIENT_FILENAME"
        MARIADB_CLIENT_FILENAME=
    done
}

test_init_script
test_freeradius_script
test_install_script

printf 'PASS: MySQL option-file password escaping and MariaDB parsing\n'
