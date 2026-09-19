#!/bin/sh
set -eu

case "${1:-dc}" in
    dc)
        : "${REALM:=EXAMPLE.TEST}"
        : "${NETBIOS_DOMAIN:=EXAMPLE}"
        : "${ADMIN_PASS:=Adm1n-Smoke-Password-9x}"

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
        sed -i "/^[[:space:]]*realm = /a\    ldap server require strong auth = no" /etc/samba/smb.conf

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
