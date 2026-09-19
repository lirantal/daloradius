#!/usr/bin/env bash
set -euo pipefail

ROOT=$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
INIT=$ROOT/init.sh
MIGRATION=$ROOT/contrib/db/migrations/2026-09-operator-ldap.sql
PROVIDER=$ROOT/tests/operator-auth-provider.test.php

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

line_number() {
    local file=$1
    local needle=$2
    awk -v needle="$needle" 'index($0, needle) { print NR; exit }' "$file"
}

line_number_after() {
    local file=$1
    local start=$2
    local needle=$3
    awk -v start="$start" -v needle="$needle" 'NR > start && index($0, needle) { print NR; exit }' "$file"
}

marker_path=/data/.migration_2026-09-operator-ldap.done
marker_def=$(line_number "$INIT" "OPERATOR_LDAP_MIGRATION_MARKER=$marker_path")
marker_guard=$(line_number "$INIT" 'if test -f "$OPERATOR_LDAP_MIGRATION_MARKER"; then')
table_guard=$(line_number_after "$INIT" "$marker_guard" 'if ! table_exists "operators"; then')
migration_sql=$(line_number "$INIT" '< "$DALORADIUS_PATH/contrib/db/migrations/2026-09-operator-ldap.sql"')
marker_write=$(line_number "$INIT" 'date > "$OPERATOR_LDAP_MIGRATION_MARKER"')

[ -n "$marker_def" ] || fail 'migration marker is not version-specific and persistent'
[ -n "$marker_guard" ] || fail 'startup path does not check the migration marker'
[ -n "$table_guard" ] || fail 'startup path no longer preserves the missing-operators guard'
[ -n "$migration_sql" ] || fail 'startup path does not invoke the LDAP migration SQL'
[ -n "$marker_write" ] || fail 'successful migration does not write the marker'
[ "$marker_def" -lt "$marker_guard" ] || fail 'marker is defined after the migration function starts'
[ "$marker_guard" -lt "$table_guard" ] || fail 'marker check does not precede migration work'
[ "$migration_sql" -lt "$marker_write" ] || fail 'marker can be written before migration SQL succeeds'
grep -Fq 'set -euo pipefail' "$INIT" || fail 'startup script does not stop on migration failure'

password_check=$(line_number "$INIT" 'ensure_operator_password_column')
ldap_check=$(line_number "$INIT" 'run_operator_ldap_migration')
totp_check=$(line_number "$INIT" 'ensure_operator_totp_columns')
[ "$password_check" -lt "$ldap_check" ] || fail 'password schema check was moved after LDAP migration'
[ "$ldap_check" -lt "$totp_check" ] || fail 'MFA schema check was moved before LDAP migration'

grep -Fq 'ADD COLUMN IF NOT EXISTS auth_source' "$MIGRATION" || fail 'auth_source migration is not idempotent'
grep -Fq 'ADD COLUMN IF NOT EXISTS external_id' "$MIGRATION" || fail 'external_id migration is not idempotent'
grep -Fq 'ADD UNIQUE INDEX IF NOT EXISTS operators_external_id_uq' "$MIGRATION" || fail 'external_id index migration is not idempotent'
grep -Fq "\$caOption = defined('LDAP_OPT_X_TLS_CACERTFILE') ? LDAP_OPT_X_TLS_CACERTFILE : 24578;" "$PROVIDER" || fail 'CA assertion does not use the runtime constant and 24578 fallback'
grep -Fq '=== 24579' "$PROVIDER" && fail 'CA assertion still hard-codes 24579'

printf 'PASS: LDAP migration marker, retry ordering, schema checks, and CA assertion are guarded\n'
