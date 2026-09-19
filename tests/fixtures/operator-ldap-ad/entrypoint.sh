#!/bin/sh
set -eu

case "${1:-dc}" in
    dc)
        : "${REALM:=EXAMPLE.TEST}"
        : "${NETBIOS_DOMAIN:=EXAMPLE}"
        : "${ADMIN_PASS:=Adm1n-Smoke-Password-9x}"

        TLS_DIR=/etc/samba/tls
        mkdir -p "$TLS_DIR/private" "$TLS_DIR/certs"
        umask 077

        openssl genrsa -out "$TLS_DIR/private/ca.key" 2048 >/dev/null 2>&1
        openssl req -x509 -new -sha256 -days 1 \
            -key "$TLS_DIR/private/ca.key" \
            -out "$TLS_DIR/certs/ca.pem" \
            -subj "/CN=operator-ldap-ad-test-ca" \
            -addext "basicConstraints=critical,CA:TRUE,pathlen:1" \
            -addext "keyUsage=critical,keyCertSign,cRLSign" \
            >/dev/null 2>&1

        openssl genrsa -out "$TLS_DIR/private/ldapdc.key" 2048 >/dev/null 2>&1
        openssl req -new -sha256 \
            -key "$TLS_DIR/private/ldapdc.key" \
            -out /tmp/ldapdc.csr \
            -subj "/CN=ldapdc.example.test" \
            >/dev/null 2>&1
        cat > /tmp/ldapdc-cert.ext <<'EOF'
 basicConstraints=critical,CA:FALSE
 keyUsage=critical,digitalSignature,keyEncipherment
 extendedKeyUsage=serverAuth
 subjectAltName=DNS:ldapdc,DNS:ldapdc.example.test
EOF
        openssl x509 -req -sha256 -days 1 \
            -in /tmp/ldapdc.csr \
            -CA "$TLS_DIR/certs/ca.pem" \
            -CAkey "$TLS_DIR/private/ca.key" \
            -CAcreateserial \
            -out "$TLS_DIR/certs/ldapdc.crt" \
            -extfile /tmp/ldapdc-cert.ext \
            >/dev/null 2>&1

        # This CA is deliberately not used to sign the server certificate.
        openssl genrsa -out "$TLS_DIR/private/untrusted-ca.key" 2048 >/dev/null 2>&1
        openssl req -x509 -new -sha256 -days 1 \
            -key "$TLS_DIR/private/untrusted-ca.key" \
            -out "$TLS_DIR/certs/untrusted-ca.pem" \
            -subj "/CN=operator-ldap-ad-untrusted-ca" \
            -addext "basicConstraints=critical,CA:TRUE,pathlen:1" \
            -addext "keyUsage=critical,keyCertSign,cRLSign" \
            >/dev/null 2>&1
        rm -f /tmp/ldapdc.csr /tmp/ldapdc-cert.ext "$TLS_DIR"/certs/ca.srl
        chmod 0600 "$TLS_DIR"/private/*.key
        chmod 0644 "$TLS_DIR"/certs/*.pem "$TLS_DIR"/certs/ldapdc.crt

        rm -f /etc/samba/smb.conf
        samba-tool domain provision \
            --realm="$REALM" \
            --domain="$NETBIOS_DOMAIN" \
            --server-role=dc \
            --dns-backend=SAMBA_INTERNAL \
            --use-rfc2307 \
            --adminpass="$ADMIN_PASS" \
            --option="dns forwarder = 127.0.0.1" \
            --option="ldap server require strong auth = no" \
            --option="interfaces = 0.0.0.0" \
            --option="bind interfaces only = no"
        sed -i "/^[[:space:]]*realm = /a\\
    ldap server require strong auth = no\\
    tls enabled = yes\\
    tls keyfile = $TLS_DIR/private/ldapdc.key\\
    tls certfile = $TLS_DIR/certs/ldapdc.crt\\
    tls cafile = $TLS_DIR/certs/ca.pem" /etc/samba/smb.conf

        exec samba -i -M single -s /etc/samba/smb.conf
        ;;
    client)
        shift
        exec "$@"
        ;;
    *)
        exec "$@"
        ;;
esac
