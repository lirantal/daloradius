#!/bin/bash
# Executable process script for daloRADIUS docker image:
# GitHub: git@github.com:lirantal/daloradius.git

# Start the MySQL service
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
/etc/init.d/mysql start
sleep 3

# Start Apache2 in the foreground and in debug mode
/usr/sbin/apachectl -DFOREGROUND -k start -e debug
# Or with the local httpd.conf use
#&>/dev/null /usr/sbin/apachectl -DFOREGROUND -k start -e debug -d . -f httpd.conf
