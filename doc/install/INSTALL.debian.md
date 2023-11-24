This guide will take you through the process of deploying a **basic open-source AAA infrastructure** on a dedicated instance of [Debian 11](https://www.debian.org/download) using the following components:
 - [MariaDB](https://mariadb.org/download/) (version 10.5);
 - [FreeRADIUS](https://freeradius.org/releases/) (version 3.0.x);
 - daloRADIUS, which requires:
   - [Apache 2](https://httpd.apache.org/download.cgi) (version 2.4.x);
   - [PHP](https://www.php.net/downloads.php) (version 7.4).

Please note that this guide has been fully tested on a Debian 11 environment, and the specific versions of the components used are mentioned in the above list.

# Prerequisites
Before proceeding with the installation, please ensure the following:

1. You have **root** access to the system.
2. You have a basic understanding of the Linux command line.

It is highly recommended to execute the following commands before installing any component:

```bash
apt update
apt dist-upgrade
```

These commands will update the package list and upgrade all installed packages to their latest versions. Once these commands have been executed, you can proceed with the regular installation of each component in the AAA infrastructure.

Please note that this guide covers only the basic deployment of the AAA infrastructure. In a production environment, it is important to implement additional security measures beyond the scope of this document to enhance the security of each component.

Lastly, all the commands and procedures mentioned in this guide assume the use of the **root** user. Please exercise caution when executing commands as the root user.

**The authors of this guide disclaims any responsibility for any direct, indirect, incidental, consequential or other damages arising from the use of this guide.**

# Installing MariaDB

To install the MariaDB server, run the following command:
```bash
apt --no-install-recommends install mariadb-server
```

Once the installation process is complete, you can secure the MariaDB installation by running the command:
```bash
mariadb-secure-installation
```

Follow the prompts to set a root password, remove anonymous users, disable remote root login, and remove test databases.

After securing the MariaDB installation, you need to create a new database and a new user for daloRADIUS and FreeRADIUS. Execute the following commands:
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

To complete the installation, ensure that MariaDB is enabled to start automatically on system boot by running the command:
```bash
systemctl enable mariadb
```

# Installing FreeRADIUS

To install the necessary FreeRADIUS packages, execute the following command:
```bash
apt --no-install-recommends install freeradius freeradius-mysql mariadb-client
```

To load the FreeRADIUS SQL schema into MariaDB, you can use the following commands:
```bash
cd /etc/freeradius/3.0/mods-config/sql/main/mysql
mariadb -u raduser -p raddb < schema.sql
```

Next, you need to edit the FreeRADIUS SQL module configuration file located at `/etc/freeradius/3.0/mods-available/sql`. Set the database driver to `rlm_sql_mysql` and uncomment the lines for the database connection details. Enter the correct values for your MariaDB server. For example:

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

In the provided deployment, only the essential operations required for FreeRADIUS to communicate with the database are executed. As a result, the TLS options are deactivated using the following command:
```bash
sed -Ei '/^[\t\s#]*tls\s+\{/, /[\t\s#]*\}/ s/^/#/' /etc/freeradius/3.0/mods-available/sql
```

**Please note that disabling TLS communication is not recommended in production environments or scenarios where security is a concern.**

To finalize the installation of FreeRADIUS, enable the SQL module by creating a symbolic link to the configuration file using the following command:
```bash
ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
```

To complete the installation, enable and restart the FreeRADIUS service using the following commands:
```bash
systemctl enable freeradius
systemctl restart freeradius
```

It is important to note that FreeRADIUS is a highly customizable and versatile service that can be tailored to meet a wide range of use cases. However, providing a comprehensive configuration of the FreeRADIUS service is beyond the scope of this document as it depends on the specific needs and requirements of an ISP.

Therefore, the presented steps provide a general outline for enabling communication between the FreeRADIUS and MariaDB components.

# Installing daloRADIUS

To proceed with the installation of daloRADIUS, execute the following command which is required to install the Apache 2 web server and the necessary packages:
```bash
apt --no-install-recommends install apache2 php libapache2-mod-php \
                                    php-mysql php-zip php-mbstring php-common php-curl \
                                    php-gd php-db php-mail php-mail-mime \
                                    mariadb-client freeradius-utils
```

After the installation of the required packages, proceed to download the daloRADIUS package with git by executing the following commands. These commands will create a new directory named `daloradius` in `/var/www`:
```bash
apt --no-install-recommends install git
cd /var/www
git clone https://github.com/filippolauria/daloradius.git
```

Afterwards, it is necessary to create the log directories for `daloradius/operators` and `daloradius/users`. This can be achieved by using the following command:
```bash
mkdir -p /var/log/apache2/daloradius/{operators,users}
```

To configure daloRADIUS on Apache 2, it is necessary to define some environment variables and update the Apache 2 configuration files. The environment variables must be defined in the file `/etc/apache2/envvars` using the following command:
```bash
cat <<EOF >> /etc/apache2/envvars
# daloRADIUS users interface port
export DALORADIUS_USERS_PORT=80

# daloRADIUS operators interface port
export DALORADIUS_OPERATORS_PORT=8000

# daloRADIUS package root directory
export DALORADIUS_ROOT_DIRECTORY=/var/www/daloradius  

# daloRADIUS administrator's email
export DALORADIUS_SERVER_ADMIN=admin@daloradius.local
EOF
```
These variables define the ports for the users and operators interfaces, the root directory of the daloRADIUS package, and the email address of the daloRADIUS administrator.

To ensure that the Apache 2 web server listens to incoming connections on the chosen ports, the `/etc/apache2/ports.conf` file needs to be rewritten. This can be done by executing the following commands, which create a backup of the original file and replace it with the desired configuration:
```bash
cat <<EOF > /etc/apache2/ports.conf

# daloRADIUS
Listen \${DALORADIUS_USERS_PORT}
Listen \${DALORADIUS_OPERATORS_PORT}
EOF
```
By doing so, Apache 2 will listen on the ports specified by the previously set environment variables `DALORADIUS_USERS_PORT` and `DALORADIUS_OPERATORS_PORT`.

As daloRADIUS has two distinct interfaces, one reserved for `operators` (i.e. privileged users) named **RADIUS Management application** and the other reserved for regular `users` named **User Portal application**, it is necessary to create two separate Apache 2 sites.

To configure the RADIUS Management application, a new Apache 2 site file named `operators.conf` must be created using the following command:
```bash
cat <<EOF > /etc/apache2/sites-available/operators.conf
<VirtualHost *:\${DALORADIUS_OPERATORS_PORT}>
  ServerAdmin \${DALORADIUS_SERVER_ADMIN}
  DocumentRoot \${DALORADIUS_ROOT_DIRECTORY}/app/operators
  
  <Directory \${DALORADIUS_ROOT_DIRECTORY}/app/operators>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  <Directory \${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/daloradius/operators/error.log
  CustomLog \${APACHE_LOG_DIR}/daloradius/operators/access.log combined
</VirtualHost>
EOF
```

Similarly, in order to configure the User Portal application, a new Apache 2 site file named `users.conf` must be created using the following command:
```bash
cat <<EOF > /etc/apache2/sites-available/users.conf
<VirtualHost *:\${DALORADIUS_USERS_PORT}>
  ServerAdmin \${DALORADIUS_SERVER_ADMIN}
  DocumentRoot \${DALORADIUS_ROOT_DIRECTORY}/app/users

  <Directory \${DALORADIUS_ROOT_DIRECTORY}/app/users>
    Options -Indexes +FollowSymLinks
    AllowOverride None
    Require all granted
  </Directory>

  <Directory \${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/daloradius/users/error.log
  CustomLog \${APACHE_LOG_DIR}/daloradius/users/access.log combined
</VirtualHost>
EOF
```
Subsequently, it is necessary to proceed with cloning the sample configuration file and modifying its permissions and ownership in the directory `/var/www/daloradius/app/common/includes`. This can be achieved by executing the following commands:
```bash
cd /var/www/daloradius/app/common/includes
cp daloradius.conf.php.sample daloradius.conf.php
chown www-data:www-data daloradius.conf.php  
chmod 664 daloradius.conf.php
```

After that, the `daloradius.conf.php` file must be edited to match the FreeRADIUS and MariaDB configurations:
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
In addition, the directory `var` must be created along with its subdirectories `log` and `backup`. Appropriate permissions and ownership must be set for these directories. This can be achieve by executing the following commands:

```bash
cd /var/www/daloradius/
mkdir -p var/{log,backup}
chown -R www-data:www-data var  
chmod -R 775 var
```

The Architecture overview section specifies that daloRADIUS shares certain database tables with FreeRADIUS. Therefore, it is essential to guarantee the correct loading of FreeRADIUS's SQL schema. This can be accomplished by executing the following commands:
```bash
cd /var/www/daloradius/contrib/db
mariadb -u raduser -p raddb < fr3-mariadb-freeradius.sql
mariadb -u raduser -p raddb < mariadb-daloradius.sql
```

Finally, to complete the configuration, it is necessary to disable the default site, enable the newly created sites, ensure that Apache 2 is enabled, and restart it by executing the following commands:
```bash
a2dissite 000-default.conf  
a2ensite operators.conf users.conf
systemctl enable apache2
systemctl restart apache2
```


# Testing the Infrastructure

To ensure proper functionality of daloRADIUS, follow these steps to access the RADIUS Management and User Portal applications:

1. **RADIUS Management application**: Access the application using the URL [http://daloradius.local:8000](http://daloradius.local:8000). Replace `daloradius.local` with the domain name or IP address associated with your system.

2. **User Portal application**: Access the application using the URL [http://daloradius.local](http://daloradius.local). Again, replace `daloradius.local` with the appropriate domain name or IP address.

The port numbers `80` and `8000` reflect the choices made in the previous sections of this guide. Please ensure that you have a web browser installed and a network connection to the daloRADIUS server.

To log in to the RADIUS Management application, use the following default credentials:

- Username: `administrator`
- Password: `radius`

Upon logging in, it is highly recommended to **update the administrator's password with a strong one**. Follow these steps to change the password:

1. Navigate to the "Config / Operators" section within the RADIUS Management application.
2. Locate the option to change the password for the administrator account.
3. Choose a new password that is secure, using a combination of uppercase and lowercase letters, numbers, and special characters.
4. Save the changes to update the administrator's password.

# Credits
This guide was created by **Filippo Lauria** and **Andrea De Vita**.

**Filippo Lauria** has contributed to this guide by providing valuable insights and expertise.
You can reach out to Filippo via email at [filippo.lauria@iit.cnr.it](mailto:filippo.lauria@iit.cnr.it).

**Andrea De Vita** has also played a significant role in creating this guide.
If you have any questions, you can contact Andrea via email at [andrea.devita@iit.cnr.it](mailto:andrea.devita@iit.cnr.it).
