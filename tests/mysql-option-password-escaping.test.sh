#!/usr/bin/env bash
set -euo pipefail

ROOT=$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
INIT=${INIT:-$ROOT/init.sh}

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

command -v docker >/dev/null 2>&1 || fail "docker is required"

# Load only the MySQL defaults-file state and functions under test.
MYSQL_DEFAULTS_FILE=""
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=raduser

eval "$(awk '
    /^function escape_mysql_option_value \{/ { capture=1 }
    capture { print }
    capture && /^}/ { exit }
' "$INIT")"

eval "$(awk '
    /^function create_mysql_defaults_file \{/ { capture=1 }
    capture { print }
    capture && /^}/ { exit }
' "$INIT")"

for password in \
    'abc#123' \
    'abc;123' \
    'abc 123' \
    'abc"123' \
    'abc\123'
do
    MYSQL_PASSWORD="$password"
    create_mysql_defaults_file

    trap 'rm -f "$MYSQL_DEFAULTS_FILE"' EXIT

    parsed=$(
        docker run --rm \
            -v "$MYSQL_DEFAULTS_FILE:/tmp/mysql-options.cnf:ro,Z" \
            mariadb:latest \
            my_print_defaults --defaults-extra-file=/tmp/mysql-options.cnf client
    )

    expected="--host=localhost
--port=3306
--user=raduser
--password=$password"

    if [ "$parsed" != "$expected" ]; then
        printf 'Generated option file:\n' >&2
        cat "$MYSQL_DEFAULTS_FILE" >&2
        printf 'Expected parsed values:\n%s\n' "$expected" >&2
        printf 'Actual parsed values:\n%s\n' "$parsed" >&2
        fail "password [$password] was not preserved by the MariaDB option-file parser"
    fi

    rm -f "$MYSQL_DEFAULTS_FILE"
    MYSQL_DEFAULTS_FILE=""
    trap - EXIT
done

printf 'PASS: MySQL option-file password escaping and MariaDB parsing\n'
