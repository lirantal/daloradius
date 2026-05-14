# Managing FreeRADIUS Certificates

This guide explains how to manage certificates used by **FreeRADIUS** with daloRADIUS deployments.

These certificates are different from the Apache HTTPS certificates used to secure the daloRADIUS web interface. Apache HTTPS certificates protect browser access to daloRADIUS. FreeRADIUS certificates are used by RADIUS authentication methods such as PEAP, EAP-TTLS, and EAP-TLS.

For Apache HTTPS configuration, see [Enabling HTTPS (SSL/TLS) for daloRADIUS](ssl-config.md).

## Overview

FreeRADIUS stores its default certificate material under:

```text
/etc/freeradius/certs
```

Depending on the Linux distribution or FreeRADIUS package layout, the path may also be:

```text
/etc/raddb/certs
```

The most common files are:

| File | Purpose |
|------|---------|
| `ca.pem` | CA certificate used to sign the FreeRADIUS server certificate |
| `ca.der` | DER-formatted CA certificate, often distributed to client devices |
| `server.pem` | FreeRADIUS server certificate, often including the private key |
| `client.pem` | Example client certificate for EAP-TLS testing |
| `dh` | Diffie-Hellman parameters |
| `random` | Random seed file used by the certificate tooling |

For PEAP and EAP-TTLS, client devices normally need to trust the CA certificate that signed the FreeRADIUS server certificate. For EAP-TLS, both server and client certificates are involved.

Never distribute private key material. In particular, do not distribute `server.pem` if it contains the server private key.

## Docker Install

In the Docker stack, FreeRADIUS runs in the `radius` container. Certificate files belong to that container, not to the `radius-web` container.

The recommended Docker approach is to keep certificate files on the host and mount them into the `radius` container. This keeps certificates persistent when the container is recreated.

### 1. Create a host certificate directory

From the daloRADIUS repository directory:

```bash
mkdir -p ./data/freeradius-certs
```

Place your FreeRADIUS certificate files in that directory, for example:

```text
./data/freeradius-certs/ca.pem
./data/freeradius-certs/ca.der
./data/freeradius-certs/server.pem
```

Depending on your EAP configuration, you may also need additional files such as `dh` or `random` if `dh_file` or `random_file` are enabled.

### 2. Mount the certificate directory

Add the certificate directory to the `radius` service in `docker-compose.yml`:

```yaml
services:
  radius:
    volumes:
      - ./data/freeradius:/data
      - ./data/freeradius-certs:/etc/freeradius/certs:ro
      - radius_logs:/var/log/freeradius
```

Use `:ro` when the container only needs to read the files. If you want to generate or renew certificates from inside the container, temporarily remove `:ro` or generate them on the host first.

> **Note:** Prefer mounting only the certificate directory, or specific certificate files. Avoid mounting the whole `/etc/freeradius` directory unless you intend to fully manage the FreeRADIUS configuration yourself, because it can hide files provided by the image.

### 3. Optional: use a certificate subdirectory

Some administrators prefer to organize certificates by year or environment, for example:

```text
./data/freeradius-certs/2024
```

Mount the subdirectory into the container:

```yaml
services:
  radius:
    volumes:
      - ./data/freeradius:/data
      - ./data/freeradius-certs/2024:/etc/freeradius/certs/2024:ro
      - radius_logs:/var/log/freeradius
```

Then update the FreeRADIUS EAP module configuration to use the subdirectory. The relevant file inside the container is usually:

```text
/etc/freeradius/mods-available/eap
```

Default settings commonly look like this:

```text
private_key_file = ${certdir}/server.pem
certificate_file = ${certdir}/server.pem
ca_file = ${cadir}/ca.pem
```

For a subdirectory, update them to point to the mounted files:

```text
private_key_file = ${certdir}/2024/server.pem
certificate_file = ${certdir}/2024/server.pem
ca_file = ${certdir}/2024/ca.pem
```

If your private key has a password, also update:

```text
private_key_password = your_private_key_password
```

For Docker deployments, avoid editing this manually inside a running container because those changes are lost when the container is recreated. Instead, use one of these approaches:

- build a derived FreeRADIUS image with the EAP configuration adjusted;
- use an entrypoint customization to patch the EAP file at container startup;
- mount a complete, tested EAP configuration file into `/etc/freeradius/mods-available/eap`.

Example derived Dockerfile:

```dockerfile
FROM lirantal/dalofreeradius

COPY ./certs/2024 /etc/freeradius/certs/2024

RUN sed -i 's|private_key_file = ${certdir}/server.pem|private_key_file = ${certdir}/2024/server.pem|' /etc/freeradius/mods-available/eap \
 && sed -i 's|certificate_file = ${certdir}/server.pem|certificate_file = ${certdir}/2024/server.pem|' /etc/freeradius/mods-available/eap \
 && sed -i 's|ca_file = ${cadir}/ca.pem|ca_file = ${certdir}/2024/ca.pem|' /etc/freeradius/mods-available/eap
```

### 4. Generate certificates using the FreeRADIUS templates

The FreeRADIUS image includes certificate templates and a `bootstrap` script in `/etc/freeradius/certs`.

You can copy the default helper files from the running container to the host:

```bash
mkdir -p ./data/freeradius-certs
docker cp radius:/etc/freeradius/certs/. ./data/freeradius-certs/
```

Edit the copied configuration files on the host, especially:

```text
./data/freeradius-certs/ca.cnf
./data/freeradius-certs/server.cnf
```

If you copied the whole certificate directory from an existing container, it may already contain generated example certificates. Remove those generated files before creating a fresh certificate set:

```bash
docker compose run --rm \
  --entrypoint sh \
  -v "$PWD/data/freeradius-certs:/certs" \
  radius -lc 'cd /certs && make destroycerts && ./bootstrap'
```

The `make destroycerts` step removes generated certificate/key files in the mounted certificate directory before `bootstrap` creates a fresh set from the edited templates. Do not run it against a directory containing production certificates unless you have a backup.

After generating or copying certificates, make sure the FreeRADIUS user inside the container can read them:

```bash
docker compose run --rm \
  --entrypoint sh \
  -v "$PWD/data/freeradius-certs:/certs" \
  radius -lc 'chown -R freerad:freerad /certs; chmod 640 /certs/*.pem /certs/*.key 2>/dev/null || true; chmod 644 /certs/*.der /certs/*.cnf 2>/dev/null || true'
```

Alternatively, generate the certificate files on the host with OpenSSL and place the final files in `./data/freeradius-certs`, then apply equivalent ownership and permissions.

### 5. Validate and restart the Docker service

Validate the FreeRADIUS configuration before restarting the running service:

```bash
docker compose run --rm --entrypoint freeradius radius -C -l stdout
```

Then restart FreeRADIUS:

```bash
docker compose restart radius
```

If the service does not start, check the logs:

```bash
docker compose logs --tail=100 radius
```

### 6. Copy the CA certificate for clients

Client devices need the CA certificate that signed the FreeRADIUS server certificate.

If the certificate is inside the running container, copy it out:

```bash
mkdir -p ./data/freeradius-certs

docker cp radius:/etc/freeradius/certs/ca.pem ./data/freeradius-certs/ca.pem
docker cp radius:/etc/freeradius/certs/ca.der ./data/freeradius-certs/ca.der
```

Distribute `ca.pem` or `ca.der` to client devices according to your operating system or MDM requirements.

## Manual Install

For a manual Linux installation, FreeRADIUS certificate files are usually stored directly on the server under one of these paths:

```text
/etc/freeradius/3.0/certs
/etc/freeradius/certs
/etc/raddb/certs
```

Debian and Ubuntu commonly use `/etc/freeradius/3.0/certs`. RHEL, Rocky Linux, AlmaLinux, and CentOS commonly use `/etc/raddb/certs`.

### 1. Locate the active FreeRADIUS configuration directory

Check the installed service and configuration files:

```bash
sudo freeradius -XC
```

or, on RHEL-like systems:

```bash
sudo radiusd -XC
```

You can also inspect the EAP module configuration:

```bash
sudo grep -R "private_key_file\|certificate_file\|ca_file" /etc/freeradius /etc/raddb 2>/dev/null
```

### 2. Place certificate files on the server

Create or use the certificate directory for your distribution:

Debian/Ubuntu example:

```bash
sudo mkdir -p /etc/freeradius/3.0/certs
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
sudo mkdir -p /etc/raddb/certs
```

Copy your certificate files into that directory:

```text
ca.pem
ca.der
server.pem
dh
random
```

Set ownership and permissions so the FreeRADIUS service can read the files while protecting private keys.

Debian/Ubuntu example:

```bash
sudo chown freerad:freerad /etc/freeradius/3.0/certs/server.pem
sudo chmod 600 /etc/freeradius/3.0/certs/server.pem
sudo chmod 644 /etc/freeradius/3.0/certs/ca.pem /etc/freeradius/3.0/certs/ca.der
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
sudo chown radiusd:radiusd /etc/raddb/certs/server.pem
sudo chmod 600 /etc/raddb/certs/server.pem
sudo chmod 644 /etc/raddb/certs/ca.pem /etc/raddb/certs/ca.der
```

### 3. Configure the EAP module

Edit the FreeRADIUS EAP module.

Debian/Ubuntu example:

```bash
sudo editor /etc/freeradius/3.0/mods-available/eap
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
sudo editor /etc/raddb/mods-available/eap
```

Update the TLS certificate paths as needed:

```text
private_key_file = ${certdir}/server.pem
certificate_file = ${certdir}/server.pem
ca_file = ${cadir}/ca.pem
```

If your private key has a password, set:

```text
private_key_password = your_private_key_password
```

If you store certificates in a subdirectory, use explicit paths such as:

```text
private_key_file = ${certdir}/2024/server.pem
certificate_file = ${certdir}/2024/server.pem
ca_file = ${certdir}/2024/ca.pem
```

### 4. Generate certificates from the included templates

FreeRADIUS packages usually include certificate templates and a bootstrap script in the `certs` directory.

Debian/Ubuntu example:

```bash
cd /etc/freeradius/3.0/certs
sudo cp ca.cnf ca.cnf.local
sudo cp server.cnf server.cnf.local
# Edit ca.cnf.local and server.cnf.local as needed.
sudo ./bootstrap
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
cd /etc/raddb/certs
sudo cp ca.cnf ca.cnf.local
sudo cp server.cnf server.cnf.local
# Edit ca.cnf.local and server.cnf.local as needed.
sudo ./bootstrap
```

Review and adjust ownership/permissions after generating files.

### 5. Validate and restart FreeRADIUS

Validate the configuration before restarting the service.

Debian/Ubuntu example:

```bash
sudo freeradius -XC
sudo systemctl restart freeradius.service
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
sudo radiusd -XC
sudo systemctl restart radiusd.service
```

Check logs if the service does not start:

Debian/Ubuntu example:

```bash
sudo journalctl -u freeradius.service -n 100 --no-pager
```

RHEL/Rocky/AlmaLinux/CentOS example:

```bash
sudo journalctl -u radiusd.service -n 100 --no-pager
```

### 6. Distribute the CA certificate to clients

Distribute the CA certificate that signed the FreeRADIUS server certificate:

```text
ca.pem or ca.der
```

Do not distribute the server private key.

## Troubleshooting

| Symptom | Possible Cause | Resolution |
|---------|----------------|------------|
| FreeRADIUS fails to start after certificate changes | Invalid certificate path, unreadable file, or wrong private key password | Run `freeradius -XC` or `radiusd -XC` and check the exact error. |
| Clients reject the server certificate | Client does not trust the CA or the certificate name does not match the expected server identity | Install the CA certificate on clients and verify certificate CN/SAN values. |
| Docker changes disappear after recreation | Files were edited inside the running container | Store certificates on the host and mount them, or build a derived image. |
| Permission denied reading private key | Incorrect ownership or permissions | Ensure the FreeRADIUS service user can read the key and keep permissions restrictive. |
| EAP-TLS client authentication fails | Missing client certificate trust chain or incorrect CA configuration | Verify `ca_file`, client certificate issuer, and FreeRADIUS debug output. |

## Security Notes

- Protect private keys with restrictive permissions.
- Do not commit private keys or real certificates to source control.
- Use test/self-signed certificates only for development or lab environments.
- For production, use certificates generated according to your organization security policy.
- Distribute only the CA certificate required by clients, not server private key material.
