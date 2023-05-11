#!/bin/bash

set -e

sed -i '/^Listen 80.*/a Listen 8000' /etc/apache2/ports.conf

cat > /etc/apache2/sites-enabled/users.conf << USERS
<VirtualHost *:80>
    ServerAdmin users@localhost
    DocumentRoot /var/www/html/daloradius/users

    <Directory /var/www/html/daloradius/users>
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

    <Directory /var/www/html/daloradius>
        Require all denied
    </Directory>

#    ErrorLog ${APACHE_LOG_DIR}/daloradius/users/error.log
#    CustomLog ${APACHE_LOG_DIR}/daloradius/users/access.log combined
</VirtualHost>
USERS

cat > /etc/apache2/sites-enabled/operators.conf << OPERATORS
<VirtualHost *:8000>
    ServerAdmin operators@localhost
    DocumentRoot /var/www/html/daloradius/operators

    <Directory /var/www/html/daloradius/operators>
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

    <Directory /var/www/html/daloradius>
        Require all denied
    </Directory>

#    ErrorLog ${APACHE_LOG_DIR}/daloradius/operators/error.log
#    CustomLog ${APACHE_LOG_DIR}/daloradius/operators/access.log combined
</VirtualHost>
OPERATORS
