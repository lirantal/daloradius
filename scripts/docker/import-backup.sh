#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
project_dir="$(cd -- "$script_dir/../.." && pwd)"
skip_build=0
dump_path=""

usage() {
    printf 'Usage: %s [--skip-build] <dump.sql.gz>\n' "$(basename "$0")" >&2
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --skip-build)
            skip_build=1
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            if [ -n "$dump_path" ]; then
                usage
                exit 1
            fi
            dump_path="$1"
            shift
            ;;
    esac
done

if [ -z "$dump_path" ]; then
    usage
    exit 1
fi

if [ "${dump_path#/}" != "$dump_path" ]; then
    resolved_dump="$dump_path"
else
    dump_dir="$(dirname -- "$dump_path")"
    dump_name="$(basename -- "$dump_path")"
    resolved_dump="$(cd -- "$dump_dir" && pwd)/$dump_name"
fi

if [ ! -f "$resolved_dump" ]; then
    printf 'Dump not found: %s\n' "$resolved_dump" >&2
    exit 1
fi

mariadb_command='set -eu
defaults_file="$(mktemp)"
cleanup() {
    rm -f "$defaults_file"
}
trap cleanup EXIT
{
    printf "[client]\n"
    printf "user=radius\n"
    printf "password=%s\n" "$MYSQL_PASSWORD"
} > "$defaults_file"
chmod 600 "$defaults_file"
mariadb --defaults-extra-file="$defaults_file" --batch --skip-column-names radius'

compose() {
    docker compose "$@"
}

wait_compose_service_healthy() {
    service_name="$1"
    timeout_seconds="${2:-180}"
    container_id="$(compose ps -q "$service_name")"

    if [ -z "$container_id" ]; then
        printf "No container found for Compose service '%s'.\n" "$service_name" >&2
        exit 1
    fi

    deadline=$((SECONDS + timeout_seconds))
    status=""
    while [ "$SECONDS" -lt "$deadline" ]; do
        status="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$container_id")"
        if [ "$status" = "healthy" ] || [ "$status" = "running" ]; then
            return 0
        fi
        sleep 3
    done

    printf "Service '%s' did not become healthy within %s seconds. Last status: %s\n" "$service_name" "$timeout_seconds" "$status" >&2
    exit 1
}

mariadb_scalar() {
    sql="$1"
    printf '%s\n' "$sql" | compose exec -T radius-mysql sh -lc "$mariadb_command" | head -n 1
}

validate_imported_schema() {
    password_width="$(mariadb_scalar "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='operators' AND COLUMN_NAME='password';")"
    if [ "$password_width" -lt 95 ]; then
        printf 'Expected operators.password width >= 95, found %s.\n' "$password_width" >&2
        exit 1
    fi

    password_nullable="$(mariadb_scalar "SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='operators' AND COLUMN_NAME='password';")"
    if [ "$password_nullable" != "NO" ]; then
        printf 'Expected operators.password to be NOT NULL, found IS_NULLABLE=%s.\n' "$password_nullable" >&2
        exit 1
    fi

    for table in operators operators_acl radcheck radreply radusergroup radacct nas; do
        count="$(mariadb_scalar "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table';")"
        if [ "$count" -ne 1 ]; then
            printf "Expected imported table '%s' to exist.\n" "$table" >&2
            exit 1
        fi
    done
}

run_post_import_password_conversion() {
    docker compose build radius-web
    docker compose run --rm --no-deps --entrypoint php radius-web /usr/local/bin/daloradius-hash-imported-passwords.php
}

cd "$project_dir"

if [ ! -f .env ]; then
    printf 'Missing .env in %s. Copy .env.example to .env and set required variables first.\n' "$project_dir" >&2
    exit 1
fi

docker compose config --quiet
gzip -t "$resolved_dump"

docker compose stop radius radius-web
docker compose up -d radius-mysql
wait_compose_service_healthy "radius-mysql"

gzip -cd "$resolved_dump" | docker compose exec -T radius-mysql sh -lc "$mariadb_command"

while IFS= read -r -d '' migration; do
    printf 'Applying post-import migration %s\n' "$(basename -- "$migration")"
    docker compose exec -T radius-mysql sh -lc "$mariadb_command" < "$migration"
done < <(find "$project_dir/docker/post-import-migrations" -maxdepth 1 -name '*.sql' -print0 | sort -z)

run_post_import_password_conversion
validate_imported_schema

if [ "$skip_build" -eq 1 ]; then
    docker compose up -d
else
    docker compose up -d --build
fi

docker compose ps
