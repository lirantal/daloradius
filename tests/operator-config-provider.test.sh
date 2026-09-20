#!/usr/bin/env bash
set -euo pipefail

ROOT=$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
INIT=$ROOT/init.sh
SAMPLE=$ROOT/app/common/includes/daloradius.conf.php.sample
ENV_EXAMPLE=$ROOT/.env.example

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

# Evaluate only the pure timeout normalizer from init.sh.
eval "$(awk '/^function normalize_ldap_timeout \{/{capture=1} capture {print} /^PASSWORD_MIN_LENGTH=/{exit}' "$INIT")"

[ "$(normalize_ldap_timeout 5)" = 5 ] || fail 'default timeout is not preserved'
[ "$(normalize_ldap_timeout 01)" = 1 ] || fail 'leading-zero one-second timeout is not normalized'
[ "$(normalize_ldap_timeout 08)" = 8 ] || fail 'leading-zero decimal timeout is treated as octal'
[ "$(normalize_ldap_timeout 010)" = 10 ] || fail 'two-digit leading-zero timeout is not decimal'
[ "$(normalize_ldap_timeout 00)" = 1 ] || fail 'zero timeout is not clamped to one second'
[ "$(normalize_ldap_timeout 30)" = 30 ] || fail 'maximum timeout is not preserved'
[ "$(normalize_ldap_timeout 031)" = 30 ] || fail 'oversized timeout is not clamped'
if normalize_ldap_timeout 0x10 >/dev/null 2>&1; then
    fail 'non-decimal timeout was accepted'
fi

# Empty optional environment values must not erase configured sample defaults
# during an existing-container refresh.
refresh_function=$(awk '/^function refresh_operator_auth_config \{/{capture=1} capture {print} capture && /^}/{exit}' "$INIT")
[ -n "$refresh_function" ] || fail 'refresh_operator_auth_config could not be extracted'
grep -Fq '[ -n "$LDAP_FILTER" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_FILTER" "$LDAP_FILTER"' <<<"$refresh_function" \
    || fail 'refresh does not preserve the sample LDAP filter'
grep -Fq '[ -n "$LDAP_EXTERNAL_ID_ATTRIBUTE" ] && php_config_set "CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE" "$LDAP_EXTERNAL_ID_ATTRIBUTE"' <<<"$refresh_function" \
    || fail 'refresh does not preserve the configured external ID'
grep -Eq '^[[:space:]]+php_config_set "CONFIG_OPERATOR_AUTH_LDAP_FILTER" "\$LDAP_FILTER"' <<<"$refresh_function" \
    && fail 'refresh still unconditionally writes the LDAP filter'
grep -Eq '^[[:space:]]+php_config_set "CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE" "\$LDAP_EXTERNAL_ID_ATTRIBUTE"' <<<"$refresh_function" \
    && fail 'refresh still unconditionally writes the external ID'

[ "$(grep -F "CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE" "$SAMPLE" | head -n 1)" = "\$configValues['CONFIG_OPERATOR_AUTH_LDAP_EXTERNAL_ID_ATTRIBUTE'] = '';" ] \
    || fail 'sample config still defaults the external ID to a mutable attribute'
grep -Fq 'DALORADIUS_LDAP_EXTERNAL_ID_ATTRIBUTE=' "$ENV_EXAMPLE" \
    || fail '.env.example does not require an explicit external ID'
if grep -Fq "getenv('DALORADIUS_LDAP_BIND_PASSWORD')" "$SAMPLE"; then
    fail 'sample config loads the bind password into persisted config values'
fi

echo 'PASS: LDAP identity, secret, timeout, and refresh configuration guards'
