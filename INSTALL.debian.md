

# How to install daloRADIUS on Debian 11
This guide will walk you through the steps to install daloRADIUS on a Debian system.

## Prerequisites
Before you begin, you should have the following:

- A Debian 11 system with **root** access.
- A basic understanding of the Linux command line.

## Installation Steps

1. Update the package list:
```
apt update
```
2. Upgrade the system:
```
apt dist-upgrade
```

3. Install Apache web server:
```
apt install apache2
```

4. Disable the default virtual host:
```
a2dissite 000-default.conf
```
5. Stop the Apache service:
```
systemctl stop apache2
```

6. Install MariaDB server:
```
apt install mariadb-server
```
7. Secure the MariaDB installation:
```
mysql_secure_installation
```
8. Stop the MariaDB service:
```
systemctl stop mariadb
```

9. Install PHP and required modules:
```
apt install php libapache2-mod-php php-mysql php-zip php-mbstring php-cli php-common php-curl
```

10. Install additional PHP modules:
```
apt install php-gd php-db php-mail php-mail-mime
```

11. In this example we get the master-branch version. So we have to install `git` and clone daloRADIUS repository into `/var/www`:
```
apt install git
cd /var/www/
git clone https://github.com/lirantal/daloradius.git
```

12. Configure Apache ports:
```
cat <<EOF > /etc/apache2/ports.conf
Listen 80
Listen 8000

<IfModule ssl_module>
    Listen 443
</IfModule>

<IfModule mod_gnutls.c>
    Listen 443
</IfModule>
EOF
```

13. Configure 2 virtual hosts (operators and users):
```
cat <<EOF > /etc/apache2/sites-available/operators.conf
<VirtualHost *:8000>
    ServerAdmin operators@localhost
    DocumentRoot /var/www/daloradius/app/operators

    <Directory /var/www/daloradius/app/operators>
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

    <Directory /var/www/daloradius>
        Require all denied
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/daloradius/operators/error.log
    CustomLog \${APACHE_LOG_DIR}/daloradius/operators/access.log combined
</VirtualHost>
EOF

cat <<EOF > /etc/apache2/sites-available/users.conf
<VirtualHost *:80>
    ServerAdmin users@localhost
    DocumentRoot /var/www/daloradius/app/users

    <Directory /var/www/daloradius/app/users>
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

    <Directory /var/www/daloradius>
        Require all denied
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/daloradius/users/error.log
    CustomLog \${APACHE_LOG_DIR}/daloradius/users/access.log combined
</VirtualHost>
EOF
```

14. Create log directories:
```
mkdir -p /var/log/apache2/daloradius/{operators,users}
```

15. Enable the created virtual hosts:
```
a2ensite users.conf operators.conf
```

16. Enable and restart MariaDB service:
```
systemctl enable mariadb
systemctl restart mariadb
```

17. Create a new database and user for daloRADIUS.
In this example db has been named `raddb`, while user has username `raduser` and password `radpass`.
```
mysql -u root -e "CREATE DATABASE raddb;"
mysql -u root -e "CREATE USER 'raduser'@'localhost' IDENTIFIED BY 'radpass';"
mysql -u root -e "GRANT ALL PRIVILEGES ON raddb.* TO 'raduser'@'localhost'";
```
18. Import the required SQL files. In this example it is supposed you are using FreeRADIUS 3.
```
mysql -u root raddb < /var/www/daloradius/contrib/db/fr3-mysql-freeradius.sql
mysql -u root raddb < /var/www/daloradius/contrib/db/mysql-daloradius.sql
```
19. Clone the sample configuration file
```
cd /var/www/daloradius/app/common/includes/
cp daloradius.conf.php.sample daloradius.conf.php
chown www-data:www-data daloradius.conf.php
```
20. Create `var` directory and its subdirectories, then change their ownership:
```
cd /var/www/daloradius/
mkdir var/{log,backup}
chown -R www-data:www-data var
```

22. Edit the configuration file to reflect FreeRADIUS and db configuration. In this example:
```
$configValues['FREERADIUS_VERSION'] = '3';
$configValues['CONFIG_DB_ENGINE'] = 'mysqli';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = '3306';
$configValues['CONFIG_DB_USER'] = 'raduser';
$configValues['CONFIG_DB_PASS'] = 'radpass';
$configValues['CONFIG_DB_NAME'] = 'raddb';
```
23. Enable and start Apache:
```
systemctl enable apache2
systemctl restart apache2
```
24. Check if the system is working fine just by visiting `http://<ip>:8000/` for the RADIUS management application or `http://<ip>` for the user portal application.
