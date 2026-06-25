This guide will walk you through the process of deploying a **basic open-source AAA infrastructure** on a dedicated instance of [AlmaLinux](https://almalinux.org/get-almalinux/). The configuration below was tested on AlmaLinux 10:

Package\OS|AlmaLinux 10|
--|--|
[MariaDB](https://mariadb.org/download/)|10.11
[FreeRADIUS](https://freeradius.org/releases/)|3.2.x
[Apache HTTP Server](https://httpd.apache.org/download.cgi)|2.4.x
[PHP](https://www.php.net/downloads.php)|8.3.x

# Prerequisites
Before proceeding with the installation, please ensure the following:

1. You have **root** access to the system.
2. You have a basic understanding of the Linux command line.
3. The system has network access to the AlmaLinux, CRB, EPEL, GitHub, and PEAR package repositories.

It is highly recommended to execute the following commands before installing any component:

```bash
dnf update
```

This command will update the package list and upgrade installed packages to their latest versions. Once it has completed, you can proceed with the regular installation of each component in the AAA infrastructure.

Please note that this guide covers only the basic deployment of the AAA infrastructure. In a production environment, it is important to implement additional security measures beyond the scope of this document to enhance the security of each component.

Lastly, all the commands and procedures mentioned in this guide assume the use of the **root** user. Please exercise caution when executing commands as the root user.

**The authors of this guide disclaim any responsibility for any direct, indirect, incidental, consequential or other damages arising from the use of this guide.**

# Enabling Required Repositories

Some packages required by FreeRADIUS and daloRADIUS are provided by CRB or EPEL. Enable them before installing the stack:

```bash
dnf install -y dnf-plugins-core epel-release
crb enable
dnf makecache
```

# Installing MariaDB

To install the MariaDB server and client, run the following command:

```bash
dnf install -y mariadb-server mariadb
```

Start MariaDB and enable it at boot:

```bash
systemctl enable --now mariadb
```

Once the installation process is complete, you can secure the MariaDB installation by running the command:

```bash
mariadb-secure-installation
```

Follow the prompts to set a root password, remove anonymous users, disable remote root login, and remove test databases.

After securing the MariaDB installation, create a new database and a new user for daloRADIUS and FreeRADIUS:

```bash
mariadb -u root -p
```

Enter the root password when prompted, and then execute the following SQL commands:

```sql
CREATE DATABASE raddb;
GRANT ALL ON raddb.* TO 'raduser'@'localhost' IDENTIFIED BY 'radpass';
FLUSH PRIVILEGES;
EXIT;
```

The commands above will create a database named `raddb` and a user named `raduser`. The user `raduser` will have full access to the `raddb` database from `localhost` using the password `radpass`. Please note that `raddb`, `raduser`, and `radpass` are examples, and you can choose different names as long as you use them consistently throughout the guide. Make sure to select a strong password and keep a record of the chosen names as they will be needed in the subsequent steps.

# Installing FreeRADIUS

To install the necessary FreeRADIUS packages, execute the following command:

```bash
dnf install -y freeradius freeradius-mysql freeradius-utils
```

The AlmaLinux FreeRADIUS configuration directory is `/etc/raddb`. The SQL module configuration file is located at `/etc/raddb/mods-available/sql`.

Edit `/etc/raddb/mods-available/sql`, set the database dialect and driver to MySQL/MariaDB, and configure the database connection details:

```ini
dialect = "mysql"
driver = "rlm_sql_${dialect}"
...
server = "localhost"
port = 3306
login = "raduser"
password = "radpass"
radius_db = "raddb"
```

In the provided deployment, only the essential operations required for FreeRADIUS to communicate with the database are executed. As a result, the TLS options in the SQL module are deactivated using the following command:

```bash
sed -Ei '/^[\t\s#]*tls\s+\{/, /[\t\s#]*\}/ s/^/#/' /etc/raddb/mods-available/sql
```

**Please note that disabling TLS communication is not recommended in production environments or scenarios where security is a concern.**

Also, ensure that the following two options are uncommented and specified as follows:

```ini
read_clients = yes
client_table = "nas"
```

Enable the SQL module by creating a symbolic link to the configuration file:

```bash
ln -s /etc/raddb/mods-available/sql /etc/raddb/mods-enabled/sql
```

To enforce total session-time limits such as `Max-All-Session`, also enable the SQL counter module:

```bash
sed -Ei 's/^[\t\s#]*dialect\s+=\s+.*$/\tdialect = "mysql"/g' /etc/raddb/mods-available/sqlcounter
ln -s /etc/raddb/mods-available/sqlcounter /etc/raddb/mods-enabled/sqlcounter
```

Then add the `noresetcounter` SQL counter to the `authorize` section in `/etc/raddb/sites-available/default`, immediately after `-sql`:

```text
authorize {
    ...
    -sql
    noresetcounter
    ...
}
```

This lets FreeRADIUS compare the user's accumulated accounting time with daloRADIUS check attributes such as `Max-All-Session`.

If the packaged FreeRADIUS TLS certificates are not already present, generate the sample certificates before validating the configuration:

```bash
cd /etc/raddb/certs
make
chown root:radiusd /etc/raddb/certs/*.{pem,key,crt,p12,der,crl} 2>/dev/null || true
chmod g+r /etc/raddb/certs/*.{pem,key,crt,p12,der,crl} 2>/dev/null || true
restorecon -Rv /etc/raddb/certs
```

After editing the FreeRADIUS configuration, validate it before restarting:

```bash
radiusd -XC
```

To complete the installation, enable and restart the FreeRADIUS service using the following commands:

```bash
systemctl enable --now radiusd
systemctl restart radiusd
```

It is important to note that FreeRADIUS is a highly customizable and versatile service that can be tailored to meet a wide range of use cases. However, providing a comprehensive configuration of the FreeRADIUS service is beyond the scope of this document as it depends on the specific needs and requirements of an ISP.

Therefore, the presented steps provide a general outline for enabling communication between FreeRADIUS and MariaDB.

# Installing daloRADIUS

To proceed with the installation of daloRADIUS, install the Apache HTTP Server and the necessary PHP packages:

```bash
dnf install -y httpd php php-cli php-mysqlnd php-mbstring php-gd php-xml php-pear php-process \
               git tar unzip wget firewalld policycoreutils-python-utils
```

Some PEAR packages used by daloRADIUS are not always available as AlmaLinux 10 RPM packages. Install them with `pear`:

```bash
pear channel-update pear.php.net
pear install DB Mail Mail_Mime
```

After the installation of the required packages, proceed to download daloRADIUS with git. These commands will create a new directory named `daloradius` in `/var/www`:

```bash
cd /var/www
git clone https://github.com/lirantal/daloradius.git
```

The Architecture overview section specifies that daloRADIUS shares certain database tables with FreeRADIUS. Therefore, it is essential to load the schemas in the correct order: the FreeRADIUS base schema first, then the daloRADIUS base schema, then every migration in alphabetical order, and finally the performance indexes. This can be accomplished by executing the following commands:

```bash
cd /var/www/daloradius/contrib/db

# Set the database password
export MYSQL_PWD='radpass'

# Load FreeRADIUS base schema
mariadb -u raduser raddb < fr3-mariadb-freeradius.sql

# Load daloRADIUS base schema
mariadb -u raduser raddb < mariadb-daloradius.sql

# Load daloRADIUS dictionaries
mariadb -u raduser raddb < mariadb-daloradius-dictionaries.sql

# Load daloRADIUS migration schemas
for f in $(ls -1 migrations/*.sql); do
    mariadb -u raduser raddb < "$f"
done

# Load daloRADIUS performance indexes
mariadb -u raduser raddb < update-performance-indexes.sql

# Clean up
unset MYSQL_PWD

Afterwards, it is necessary to create the log directories for `daloradius/operators` and `daloradius/users`:

```bash
mkdir -p /var/log/httpd/daloradius/{operators,users}
```

As daloRADIUS has two distinct interfaces, one reserved for `operators` (i.e. privileged users) named **RADIUS Management application** and the other reserved for regular `users` named **User Portal application**, it is necessary to create two separate Apache virtual hosts.

Create `/etc/httpd/conf.d/daloradius.conf` with the following content:

```apache
Listen 8000

<VirtualHost *:8000>
  ServerAdmin admin@daloradius.example.org
  DocumentRoot /var/www/daloradius/app/operators

  <Directory /var/www/daloradius/app/operators>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  <Directory /var/www/daloradius>
    Require all denied
  </Directory>

  ErrorLog /var/log/httpd/daloradius/operators/error.log
  CustomLog /var/log/httpd/daloradius/operators/access.log combined
</VirtualHost>

<VirtualHost *:80>
  ServerAdmin admin@daloradius.example.org
  DocumentRoot /var/www/daloradius/app/users

  <Directory /var/www/daloradius/app/users>
    Options -Indexes +FollowSymLinks
    AllowOverride None
    Require all granted
  </Directory>

  <Directory /var/www/daloradius>
    Require all denied
  </Directory>

  ErrorLog /var/log/httpd/daloradius/users/error.log
  CustomLog /var/log/httpd/daloradius/users/access.log combined
</VirtualHost>
```

AlmaLinux's default Apache configuration already listens on port `80`. The configuration above only adds `Listen 8000` for the operators interface. If Apache reports a duplicate `Listen` directive, remove the duplicate listener and run `httpd -t` again.

Subsequently, clone the sample configuration file and modify its permissions and ownership in `/var/www/daloradius/app/common/includes`:

```bash
cd /var/www/daloradius/app/common/includes
cp daloradius.conf.php.sample daloradius.conf.php
chown apache:apache daloradius.conf.php
chmod 664 daloradius.conf.php
```

Also, the file `dalo-crontab` needs to be owned by `apache`:

```bash
chown apache:apache /var/www/daloradius/contrib/scripts/dalo-crontab
```

After that, edit `daloradius.conf.php` to match the FreeRADIUS and MariaDB configurations:

```php
...
$configValues['FREERADIUS_VERSION'] = '3';
$configValues['CONFIG_DB_ENGINE'] = 'mysqli';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = '3306';
$configValues['CONFIG_DB_USER'] = 'raduser';
$configValues['CONFIG_DB_PASS'] = 'radpass';
$configValues['CONFIG_DB_NAME'] = 'raddb';
...
```

In addition, the directory `var` must be created along with its subdirectories `log` and `backup`. Appropriate permissions and ownership must be set for these directories:

```bash
cd /var/www/daloradius/
mkdir -p var/{log,backup}
chown -R apache:apache var /var/log/httpd/daloradius
chmod -R 775 var
```

# SELinux and firewalld

AlmaLinux enables SELinux by default. Configure the file contexts and allow Apache to connect to MariaDB:

```bash
semanage fcontext -a -t httpd_sys_content_t "/var/www/daloradius(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/daloradius/var(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/daloradius/app/common/includes/daloradius.conf.php"
restorecon -Rv /var/www/daloradius
semanage port -a -t http_port_t -p tcp 8000 || semanage port -m -t http_port_t -p tcp 8000
setsebool -P httpd_can_network_connect_db on
```

If firewalld is enabled, allow HTTP, the daloRADIUS operators port, and the RADIUS ports:

```bash
systemctl enable --now firewalld
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-port=8000/tcp
firewall-cmd --permanent --add-port=1812/udp
firewall-cmd --permanent --add-port=1813/udp
firewall-cmd --reload
```

Finally, validate Apache configuration, enable Apache, and restart it:

```bash
httpd -t
systemctl enable --now httpd
systemctl restart httpd
```

# Testing the Infrastructure

To ensure proper functionality of daloRADIUS, follow these steps to access the RADIUS Management and User Portal applications:

1. **RADIUS Management application**: Access the application using the URL [http://daloradius.example.org:8000](http://daloradius.example.org:8000). Replace `daloradius.example.org` with the domain name or IP address associated with your system.

2. **User Portal application**: Access the application using the URL [http://daloradius.example.org](http://daloradius.example.org). Replace `daloradius.example.org` with the domain name or IP address associated with your system.

The port numbers `80` and `8000` reflect the choices made in the previous sections of this guide. Please ensure that you have a web browser installed and a network connection to the daloRADIUS server.

To log in to the RADIUS Management application, use the following default credentials:

- Username: `administrator`
- Password: `radius`

Upon logging in, it is highly recommended to **update the administrator's password with a strong one**. Follow these steps to change the password:

1. Navigate to the "Config / Operators" section within the RADIUS Management application.
2. Locate the option to change the password for the administrator account.
3. Choose a new password that is secure, using a combination of uppercase and lowercase letters, numbers, and special characters.
4. Save the changes to update the administrator's password.

You can also validate the local services from the command line:

```bash
systemctl status mariadb radiusd httpd --no-pager
curl -I http://127.0.0.1/
curl -I http://127.0.0.1:8000/
```

To validate a basic FreeRADIUS SQL authentication flow, add a test user and run `radtest`:

```bash
mariadb -u raduser -p raddb
```

```sql
INSERT INTO radcheck (username, attribute, op, value)
VALUES ('testuser', 'Cleartext-Password', ':=', 'testpass');
EXIT;
```

```bash
radtest testuser testpass 127.0.0.1 0 testing123
```

A successful response includes `Access-Accept`.

# Credits
This guide is based on the Debian installation guide created by **Filippo Lauria** and **Andrea De Vita**, adapted for AlmaLinux package names, service names, paths, SELinux, and firewalld.
