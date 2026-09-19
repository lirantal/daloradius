FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        iproute2 \
        ldap-utils \
        openssl \
        procps \
        php-cli \
        php-ldap \
        samba-ad-dc \
        samba-ad-provision \
        samba-dsdb-modules \
        samba-vfs-modules \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/operator-ldap-ad-entrypoint
RUN chmod 0755 /usr/local/bin/operator-ldap-ad-entrypoint

ENTRYPOINT ["/usr/local/bin/operator-ldap-ad-entrypoint"]
CMD ["dc"]
