# Enabling HTTPS (SSL/TLS) for daloRADIUS

This guide walks you through the process of enabling HTTPS on a daloRADIUS installation running on **Apache 2**. It assumes you installed daloRADIUS using the `setup/install.sh` script or followed the manual installation procedure described in the [Debian installation guide](../install/INSTALL.debian.md).

daloRADIUS is a standard PHP web application — it does not include any built-in SSL/TLS configuration. HTTPS is configured entirely at the Apache level, the same way you would for any other web application.

## Prerequisites

Before proceeding, ensure the following:

1. daloRADIUS is installed and accessible over HTTP.
2. You have **root** (or sudo) access to the server.
3. Apache 2 is running and serving the operators and users virtual hosts.
4. You have a valid SSL certificate and private key, **or** you intend to generate a self-signed certificate for testing (see [Step 2](#2-obtain-an-ssl-certificate)).

## 1. Enable the Apache SSL Module

The `ssl` module is required for Apache to handle HTTPS connections. Enable it by running:

```bash
sudo a2enmod ssl
```

## 2. Obtain an SSL Certificate

You need an SSL certificate and its corresponding private key. Choose one of the two approaches below depending on your environment.

### Option A — Self-Signed Certificate (Testing Only)

A self-signed certificate is suitable for development or internal testing. It will trigger a browser security warning because it is not signed by a trusted Certificate Authority.

Generate a self-signed certificate valid for 365 days:

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/daloradius.key \
  -out /etc/ssl/certs/daloradius.crt \
  -subj "/CN=daloradius.local"
```

Set appropriate permissions on the private key:

```bash
sudo chmod 600 /etc/ssl/private/daloradius.key
```

### Option B — Certificate from a Certificate Authority (Production)

For a production environment, obtain a certificate from a trusted authority such as [Let's Encrypt](https://letsencrypt.org/) or a commercial provider. Place the resulting files in standard locations:

| File | Recommended Path |
|------|-----------------|
| Certificate | `/etc/ssl/certs/daloradius.crt` |
| Private Key | `/etc/ssl/private/daloradius.key` |
| CA Chain (if applicable) | `/etc/ssl/certs/daloradius-chain.crt` |

If your CA provides an intermediate chain file, you will also need the `SSLCertificateChainFile` directive in your virtual host configuration (see Step 4).

## 3. Update the Port Configuration

The install script defines port variables at the bottom of `/etc/apache2/envvars`. Update them to use HTTPS ports:

```bash
# daloRADIUS users interface port
export DALORADIUS_USERS_PORT=443

# daloRADIUS operators interface port
export DALORADIUS_OPERATORS_PORT=8443
```

Make sure your `/etc/apache2/ports.conf` reflects these ports. If you followed the standard installation, it already reads from the environment variables:

```apache
Listen ${DALORADIUS_USERS_PORT}
Listen ${DALORADIUS_OPERATORS_PORT}
```

No changes to `ports.conf` should be necessary unless you customized it.

## 4. Update the Virtual Host Files

Edit each virtual host to enable SSL and point to your certificate files.

### `/etc/apache2/sites-available/operators.conf`

```apache
<VirtualHost *:${DALORADIUS_OPERATORS_PORT}>
  ServerAdmin ${DALORADIUS_SERVER_ADMIN}
  DocumentRoot ${DALORADIUS_ROOT_DIRECTORY}/app/operators

  SSLEngine on
  SSLCertificateFile      /etc/ssl/certs/daloradius.crt
  SSLCertificateKeyFile   /etc/ssl/private/daloradius.key
  # SSLCertificateChainFile /etc/ssl/certs/daloradius-chain.crt

  <Directory ${DALORADIUS_ROOT_DIRECTORY}/app/operators>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  <Directory ${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog ${APACHE_LOG_DIR}/daloradius/operators/error.log
  CustomLog ${APACHE_LOG_DIR}/daloradius/operators/access.log combined
</VirtualHost>
```

### `/etc/apache2/sites-available/users.conf`

```apache
<VirtualHost *:${DALORADIUS_USERS_PORT}>
  ServerAdmin ${DALORADIUS_SERVER_ADMIN}
  DocumentRoot ${DALORADIUS_ROOT_DIRECTORY}/app/users

  SSLEngine on
  SSLCertificateFile      /etc/ssl/certs/daloradius.crt
  SSLCertificateKeyFile   /etc/ssl/private/daloradius.key
  # SSLCertificateChainFile /etc/ssl/certs/daloradius-chain.crt

  <Directory ${DALORADIUS_ROOT_DIRECTORY}/app/users>
    Options -Indexes +FollowSymLinks
    AllowOverride None
    Require all granted
  </Directory>

  <Directory ${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog ${APACHE_LOG_DIR}/daloradius/users/error.log
  CustomLog ${APACHE_LOG_DIR}/daloradius/users/access.log combined
</VirtualHost>
```

> **Note:** Uncomment the `SSLCertificateChainFile` line and provide the correct path if your Certificate Authority supplied a chain or intermediate certificate file.

## 5. Restart Apache

Apply all changes by restarting the Apache service:

```bash
sudo systemctl restart apache2
```

If the restart fails, check the configuration syntax first:

```bash
sudo apachectl configtest
```

## 6. Verify the Configuration

Once Apache has restarted, verify that HTTPS is working:

1. **User Portal**: Open `https://your-server-address` in a web browser (port 443).
2. **Operators Interface**: Open `https://your-server-address:8443` in a web browser.

Replace `your-server-address` with the domain name or IP address of your server.

If you used a self-signed certificate, your browser will display a security warning. This is expected — you can proceed by accepting the risk or adding an exception.

## Troubleshooting

| Symptom | Possible Cause | Resolution |
|---------|---------------|------------|
| Apache fails to start | Port conflict or syntax error | Run `sudo apachectl configtest` and check for errors. Verify no other service is using ports 443 or 8443. |
| Browser shows `ERR_SSL_PROTOCOL_ERROR` | `SSLEngine on` missing or `ssl` module not enabled | Confirm `sudo a2enmod ssl` was run and that the virtual host includes `SSLEngine on`. |
| Browser shows certificate warning | Self-signed certificate or domain mismatch | Use a CA-signed certificate for production. Ensure the certificate's CN or SAN matches the server domain. |
| `AH02572: Failed to configure at least one certificate` | Incorrect file paths for certificate or key | Verify the paths in `SSLCertificateFile` and `SSLCertificateKeyFile` point to existing files. |

## Additional Notes

- If you used a different installation method (manual setup, Docker, etc.), the file locations and port configuration may differ. Adapt the instructions accordingly.

## References

- [Apache SSL/TLS How-To](https://httpd.apache.org/docs/2.4/ssl/ssl_howto.html)
- [mod_ssl Documentation](https://httpd.apache.org/docs/2.4/mod/mod_ssl.html)
- [Let's Encrypt — Getting Started](https://letsencrypt.org/getting-started/)
