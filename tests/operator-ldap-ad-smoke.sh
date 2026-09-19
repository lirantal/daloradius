#!/usr/bin/env bash
# Run a real, isolated Samba AD DC and exercise plain LDAP and TLS transports from a disposable client.
set -Eeuo pipefail

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
FIXTURE_DIR="$SCRIPT_DIR/fixtures/operator-ldap-ad"
IMAGE="${LDAP_AD_IMAGE:-operator-ldap-ad-smoke:${BASHPID:-$$}-$(date +%s%N)}"
IMAGE_OWNED=0
REALM="EXAMPLE.TEST"
NETBIOS_DOMAIN="EXAMPLE"
BASE_DN="DC=example,DC=test"
DC_IP=""
NETWORK="ldap-ad-smoke-${BASHPID:-$$}-$(date +%s%N)"
DC_NAME="${NETWORK}-dc"
CLIENT_NAME="${NETWORK}-client"
ADMIN_PASS='Adm1n-Smoke-Password-9x'
SERVICE_PASS='Serv1ce-Smoke-Password-9x'
USER_PASS='Us3r-Smoke-Password-9x'
SERVICE_BIND="ldap-service@${REALM}"
USER_BIND="smoke-user@${REALM}"
NETWORK_CREATED=0
DC_CREATED=0
CA_DIR=""

log() {
    printf 'operator-ldap-ad-smoke: %s\n' "$*"
}

fail() {
    printf 'operator-ldap-ad-smoke: FAIL: %s\n' "$*" >&2
    return 1
}

cleanup() {
    local status=$?
    trap - EXIT INT TERM
    set +e
    docker rm -f "$CLIENT_NAME" >/dev/null 2>&1
    docker rm -f "$DC_NAME" >/dev/null 2>&1
    if (( NETWORK_CREATED )); then
        docker network rm "$NETWORK" >/dev/null 2>&1
    fi
    if (( IMAGE_OWNED )); then
        docker image rm "$IMAGE" >/dev/null 2>&1
    fi
    if [[ -n "$CA_DIR" ]]; then
        rm -rf -- "$CA_DIR"
    fi
    exit "$status"
}
trap cleanup EXIT INT TERM

[[ -f "$FIXTURE_DIR/Dockerfile" && -x "$FIXTURE_DIR/entrypoint.sh" ]] || \
    fail "fixture image files are missing or entrypoint is not executable"

log "building $IMAGE"
docker build --pull -t "$IMAGE" "$FIXTURE_DIR" >/dev/null
IMAGE_OWNED=1

for attempt in $(seq 1 25); do
    subnet_octet=$(( (RANDOM % 220) + 10 ))
    subnet="172.30.${subnet_octet}.0/24"
    candidate_ip="172.30.${subnet_octet}.2"
    if docker network create --driver bridge --internal --subnet "$subnet" "$NETWORK" >/dev/null 2>&1; then
        DC_IP="$candidate_ip"
        NETWORK_CREATED=1
        break
    fi
done
(( NETWORK_CREATED )) || fail "could not allocate an isolated Docker network"

network_config=$(docker network inspect --format '{{json .}}' "$NETWORK")
printf '%s\n' "$network_config" | grep -Fq '"Internal":true' || \
    fail "Docker network is not internal"

log "starting Samba AD DC on $NETWORK"
docker create \
    --name "$DC_NAME" \
    --hostname ldapdc \
    --network "$NETWORK" \
    --ip "$DC_IP" \
    --privileged \
    --env "REALM=$REALM" \
    --env "NETBIOS_DOMAIN=$NETBIOS_DOMAIN" \
    --env "ADMIN_PASS=$ADMIN_PASS" \
    "$IMAGE" >/dev/null
DC_CREATED=1

mounts=$(docker inspect --format '{{json .Mounts}}' "$DC_NAME")
[[ "$mounts" == "[]" ]] || fail "DC unexpectedly has host volumes: $mounts"
port_bindings=$(docker inspect --format '{{json .HostConfig.PortBindings}}' "$DC_NAME")
[[ "$port_bindings" == "null" || "$port_bindings" == "{}" ]] || \
    fail "DC unexpectedly has host ports: $port_bindings"

docker start "$DC_NAME" >/dev/null

log "waiting for Samba provisioning and LDAP"
ready=0
for _ in $(seq 1 120); do
    if docker exec "$DC_NAME" samba-tool domain level show >/dev/null 2>&1 \
        && docker run --rm --network "$NETWORK" --entrypoint ldapsearch "$IMAGE" \
            -LLL -o ldif-wrap=no -x -H "ldap://$DC_IP" -b '' -s base namingContexts \
            >/dev/null 2>&1; then
        ready=1
        break
    fi
    sleep 2
done
if (( ! ready )); then
    docker logs "$DC_NAME" >&2 || true
    fail "Samba AD DC did not become ready"
fi

realm_value=$(docker exec "$DC_NAME" samba-tool testparm --parameter-name realm 2>/dev/null | tr -d "\r") || \
    fail "Samba realm inspection failed"
[[ "$realm_value" == "$REALM" ]] || {
    printf "reported realm: %s\n" "$realm_value" >&2
    fail "provisioned realm was not $REALM"
}

CA_DIR=$(mktemp -d "${TMPDIR:-/tmp}/operator-ldap-ad-ca.XXXXXX")
chmod 0700 "$CA_DIR"
log "copying disposable test CAs"
docker cp "$DC_NAME:/etc/samba/tls/certs/ca.pem" "$CA_DIR/ca.pem" >/dev/null
docker cp "$DC_NAME:/etc/samba/tls/certs/untrusted-ca.pem" "$CA_DIR/untrusted-ca.pem" >/dev/null
chmod 0600 "$CA_DIR/ca.pem" "$CA_DIR/untrusted-ca.pem"
[[ -s "$CA_DIR/ca.pem" && -s "$CA_DIR/untrusted-ca.pem" ]] || \
    fail "Samba test CA files were not copied"

log "creating LDAP service account, user, and group"
docker exec "$DC_NAME" samba-tool user create ldap-service "$SERVICE_PASS" >/dev/null
docker exec "$DC_NAME" samba-tool user create smoke-user "$USER_PASS" >/dev/null
docker exec "$DC_NAME" samba-tool group add "Smoke Group" >/dev/null
docker exec "$DC_NAME" samba-tool group add "Parent Group" >/dev/null
docker exec "$DC_NAME" samba-tool group addmembers "Smoke Group" smoke-user >/dev/null
docker exec "$DC_NAME" samba-tool group addmembers "Parent Group" "Smoke Group" >/dev/null

log "running LDAP assertions from disposable client"
docker run --rm --name "$CLIENT_NAME" --network "$NETWORK" \
    --add-host "ldapdc:$DC_IP" \
    --add-host "ldapdc.example.test:$DC_IP" \
    --volume "$CA_DIR:/run/ldap-ca:ro" \
    --volume "$SCRIPT_DIR/..:/opt/daloradius:ro" \
    --env "LDAP_URI=ldap://$DC_IP" \
    --env "LDAP_HOST=ldapdc.example.test" \
    --env "LDAP_CA_FILE=/run/ldap-ca/ca.pem" \
    --env "LDAP_WRONG_CA_FILE=/run/ldap-ca/untrusted-ca.pem" \
    --env "BASE_DN=$BASE_DN" \
    --env "SERVICE_BIND=$SERVICE_BIND" \
    --env "SERVICE_PASS=$SERVICE_PASS" \
    --env "USER_BIND=$USER_BIND" \
    --env "USER_PASS=$USER_PASS" \
    --entrypoint /bin/sh "$IMAGE" -c '
set -eu

auth_args() {
    printf "%s\n" -D "$SERVICE_BIND" -w "$SERVICE_PASS"
}

ldap_search() {
    ldapsearch -LLL -o ldif-wrap=no -x -H "$LDAP_URI" "$@"
}

service_output=$(ldap_search -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s sub \
    "(&(objectClass=user)(sAMAccountName=smoke-user))" sAMAccountName)
printf "%s\n" "$service_output" | grep -Fqx "sAMAccountName: smoke-user" || {
    printf "%s\n" "$service_output" >&2
    printf "service bind/search did not find smoke-user\n" >&2
    exit 1
}
printf "plain LDAP service bind/search: PASS\n"

user_output=$(ldap_search -D "$USER_BIND" -w "$USER_PASS" -b "$BASE_DN" -s sub \
    "(&(objectClass=user)(sAMAccountName=smoke-user))" sAMAccountName)
printf "%s\n" "$user_output" | grep -Fqx "sAMAccountName: smoke-user" || {
    printf "%s\n" "$user_output" >&2
    printf "user bind/search did not find smoke-user\n" >&2
    exit 1
}
printf "plain LDAP user bind: PASS\n"

if ldap_search -D "$USER_BIND" -w wrong-password -b "$BASE_DN" -s base \
    "(objectClass=*)" >/tmp/wrong-password.out 2>/tmp/wrong-password.err; then
    printf "wrong password was accepted\n" >&2
    exit 1
fi
printf "wrong password rejected: PASS\n"

details=$(ldap_search -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s sub \
    "(sAMAccountName=smoke-user)" objectGUID memberOf)
guid_line=$(printf "%s\n" "$details" | grep -Em1 "^objectGUID(:|::) " || true)
[ -n "$guid_line" ] || {
    printf "%s\n" "$details" >&2
    printf "objectGUID was not returned\n" >&2
    exit 1
}
printf "%s\n" "$details" | grep -Fqx "memberOf: CN=Smoke Group,CN=Users,$BASE_DN" || {
    printf "%s\n" "$details" >&2
    printf "smoke-user group membership was not returned\n" >&2
    exit 1
}
printf "objectGUID and memberOf: PASS\n"

escaped_output=$(ldap_search -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s sub \
    "(&(objectClass=user)(sAMAccountName=smoke\2auser))" sAMAccountName)
if printf "%s\n" "$escaped_output" | grep -q "^sAMAccountName: "; then
    printf "%s\n" "$escaped_output" >&2
    printf "escaped asterisk filter incorrectly wildcard-matched\n" >&2
    exit 1
fi
wildcard_output=$(ldap_search -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s sub \
    "(&(objectClass=user)(sAMAccountName=smoke*user))" sAMAccountName)
printf "%s\n" "$wildcard_output" | grep -Fqx "sAMAccountName: smoke-user" || {
    printf "%s\n" "$wildcard_output" >&2
    printf "wildcard control search did not find smoke-user\n" >&2
    exit 1
}
printf "escaped special-character filter: PASS\n"

tls_uri="ldap://ldapdc.example.test"
ldaps_uri="ldaps://ldapdc.example.test"
tls_output=$(ldapsearch -LLL -o ldif-wrap=no -x -H "$tls_uri" -ZZ \
    -o TLS_CACERT=/run/ldap-ca/ca.pem -o TLS_REQCERT=demand \
    -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s base \
    "(objectClass=*)" dn)
printf "%s\n" "$tls_output" | grep -Fqx "dn: $BASE_DN" || {
    printf "%s\n" "$tls_output" >&2
    printf "StartTLS CA verification did not return the base DN\n" >&2
    exit 1
}
printf "StartTLS with CA verification: PASS\n"

ldapsearch -LLL -o ldif-wrap=no -x -H "$tls_uri" -ZZ \
    -o TLS_REQCERT=never \
    -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s base \
    "(objectClass=*)" dn >/tmp/starttls-no-verify.out
printf "StartTLS TLS_REQCERT=never: PASS\n"

ldaps_output=$(ldapsearch -LLL -o ldif-wrap=no -x -H "$ldaps_uri" \
    -o TLS_CACERT=/run/ldap-ca/ca.pem -o TLS_REQCERT=demand \
    -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s base \
    "(objectClass=*)" dn)
printf "%s\n" "$ldaps_output" | grep -Fqx "dn: $BASE_DN" || {
    printf "%s\n" "$ldaps_output" >&2
    printf "LDAPS CA verification did not return the base DN\n" >&2
    exit 1
}
printf "LDAPS with CA verification: PASS\n"

if ldapsearch -LLL -o ldif-wrap=no -x -H "$ldaps_uri" \
    -o TLS_CACERT=/run/ldap-ca/untrusted-ca.pem \
    -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s base \
    "(objectClass=*)" dn >/tmp/ldaps-untrusted-ca.out 2>/tmp/ldaps-untrusted-ca.err; then
    printf "LDAPS accepted an intentionally untrusted CA\n" >&2
    cat /tmp/ldaps-untrusted-ca.out /tmp/ldaps-untrusted-ca.err >&2
    exit 1
fi
printf "LDAPS with untrusted CA/default verification rejected: PASS\n"

ldapsearch -LLL -o ldif-wrap=no -x -H "$ldaps_uri" \
    -o TLS_REQCERT=never \
    -D "$SERVICE_BIND" -w "$SERVICE_PASS" -b "$BASE_DN" -s base \
    "(objectClass=*)" dn >/tmp/ldaps-no-verify.out
printf "LDAPS TLS_REQCERT=never: PASS\n"

php /opt/daloradius/tests/operator-ldap-provider-live.test.php
'

log "PASS: Samba AD plain LDAP and TLS smoke tests"
