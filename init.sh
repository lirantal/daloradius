#!/bin/bash
# Executable process script for daloRADIUS docker image:
# GitHub: git@github.com:lirantal/daloradius.git
DALORADIUS_PATH=/var/www/html
DALORADIUS_CONF_PATH=/var/www/html/library/daloradius.conf.php

function init_daloradius {

	if ! test -f "$/var/www/html/library/daloradius.conf.php"; then
		cp "$DALORADIUS_PATH/library/daloradius.conf.php.sample" "$DALORADIUS_CONF_PATH"
	fi

	sed -i "s/\$configValues\['CONFIG_DB_HOST'\] = .*;/\$configValues\['CONFIG_DB_HOST'\] = '$MYSQL_HOST';/" $DALORADIUS_PATH/library/daloradius.conf.php
	sed -i "s/\$configValues\['CONFIG_DB_PORT'\] = .*;/\$configValues\['CONFIG_DB_PORT'\] = '$MYSQL_PORT';/" $DALORADIUS_PATH/library/daloradius.conf.php
	sed -i "s/\$configValues\['CONFIG_DB_PASS'\] = .*;/\$configValues\['CONFIG_DB_PASS'\] = '$MYSQL_PASSWORD';/" $DALORADIUS_PATH/library/daloradius.conf.php 
	sed -i "s/\$configValues\['CONFIG_DB_USER'\] = .*;/\$configValues\['CONFIG_DB_USER'\] = '$MYSQL_USER';/" $DALORADIUS_PATH/library/daloradius.conf.php
	sed -i "s/\$configValues\['CONFIG_DB_NAME'\] = .*;/\$configValues\['CONFIG_DB_NAME'\] = '$MYSQL_DATABASE';/" $DALORADIUS_PATH/library/daloradius.conf.php
	sed -i "s/\$configValues\['FREERADIUS_VERSION'\] = .*;/\$configValues\['FREERADIUS_VERSION'\] = '3';/" $DALORADIUS_PATH/library/daloradius.conf.php
	sed -i "s|\$configValues\['CONFIG_PATH_DALO_VARIABLE_DATA'\] = .*;|\$configValues\['CONFIG_PATH_DALO_VARIABLE_DATA'\] = '/var/www/html/var';|" $DALORADIUS_PATH/library/daloradius.conf.php

	if [ -n "$DEFAULT_FREERADIUS_SERVER" ]; then
		sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = '$DEFAULT_FREERADIUS_SERVER';/" $DALORADIUS_PATH/library/daloradius.conf.php
	else
		sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSERVER'\] = 'radius';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi
	if [ -n "$DEFAULT_CLIENT_SECRET" ]; then
		sed -i "s/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = .*;/\$configValues\['CONFIG_MAINT_TEST_USER_RADIUSSECRET'\] = '$DEFAULT_CLIENT_SECRET';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi

	if [ -n "$MAIL_SMTPADDR" ]; then
		sed -i "s/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPADDR'\] = '$MAIL_SMTPADDR';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi
	if [ -n "$MAIL_PORT" ]; then
		sed -i "s/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPPORT'\] = '$MAIL_PORT';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi
	if [ -n "$MAIL_FROM" ]; then
		sed -i "s/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPFROM'\] = '$MAIL_FROM';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi
	if [ -n "$MAIL_AUTH" ]; then
		sed -i "s/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = .*;/\$configValues\['CONFIG_MAIL_SMTPAUTH'\] = '$MAIL_AUTH';/" $DALORADIUS_PATH/library/daloradius.conf.php
	fi

	echo "daloRADIUS initialization completed."
}

function init_database {
	mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < $DALORADIUS_PATH/contrib/db/mysql-daloradius.sql
	echo "Database initialization for daloRADIUS completed."
}

echo "Starting daloRADIUS..."

# wait for MySQL-Server to be ready
while ! mysqladmin ping -h"$MYSQL_HOST" --silent; do
	echo "Waiting for mysql ($MYSQL_HOST)..."
	sleep 20
done

INIT_LOCK=/internal_data/.init_done
if test -f "$INIT_LOCK"; then
	echo "Init lock file exists, skipping initial setup of daloRADIUS."
else
	init_daloradius
	date > $INIT_LOCK
fi

DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
	echo "Database lock file exists, skipping initial setup of mysql database."
else
	init_database
	date > $DB_LOCK
fi

# Start Apache2 in the foreground
/usr/sbin/apachectl -DFOREGROUND -k start
