#!/bin/bash
# Executable process script for daloRADIUS docker image:
# GitHub: git@github.com:lirantal/daloradius.git
mkdir -p /var/run/mysqld && chown -R mysql:mysql /var/lib/mysql /var/run/mysqld; /usr/bin/mysqld_safe & \
 sleep 10s && \

mysql -u root --password=$MYSQLTMPROOT -e \
"CREATE DATABASE radius; GRANT ALL ON radius.* TO radius@localhost IDENTIFIED BY '$RADPASS'; \
flush privileges;"
mysql -uradius --password=$RADPASS radius < /etc/freeradius/sql/mysql/schema.sql
mysql -uradius --password=$RADPASS radius < /etc/freeradius/sql/mysql/nas.sql
mysql -uradius --password=$RADPASS radius < /var/www/html/contrib/db/mysql-daloradius.sql

sed -i 's/password = "radpass"/password = "'$RADPASS'"/' /etc/freeradius/sql.conf
sed -i 's/#port = 3306/port = 3306/' /etc/freeradius/sql.conf
sed -i -e 's/$INCLUDE sql.conf/\n$INCLUDE sql.conf/g' /etc/freeradius/radiusd.conf
sed -i -e 's|$INCLUDE sql/mysql/counter.conf|\n$INCLUDE sql/mysql/counter.conf|g' /etc/freeradius/radiusd.conf
sed -i -e 's|authorize {|authorize {\nsql|' /etc/freeradius/sites-available/inner-tunnel
sed -i -e 's|session {|session {\nsql|' /etc/freeradius/sites-available/inner-tunnel 
sed -i -e 's|authorize {|authorize {\nsql|' /etc/freeradius/sites-available/default
sed -i -e 's|session {|session {\nsql|' /etc/freeradius/sites-available/default
sed -i -e 's|accounting {|accounting {\nsql|' /etc/freeradius/sites-available/default

sed -i -e 's|auth_badpass = no|auth_badpass = yes|g' /etc/freeradius/radiusd.conf
sed -i -e 's|auth_goodpass = no|auth_goodpass = yes|g' /etc/freeradius/radiusd.conf
sed -i -e 's|auth = no|auth = yes|g' /etc/freeradius/radiusd.conf

sed -i -e 's|\t#  See "Authentication Logging Queries" in sql.conf\n\t#sql|#See "Authentication Logging Queries" in sql.conf\n\tsql|g' /etc/freeradius/sites-available/inner-tunnel 
sed -i -e 's|\t#  See "Authentication Logging Queries" in sql.conf\n\t#sql|#See "Authentication Logging Queries" in sql.conf\n\tsql|g' /etc/freeradius/sites-available/default

sed -i -e 's|sqltrace = no|sqltrace = yes|g' /etc/freeradius/sql.conf

sed -i -e "s/readclients = yes/nreadclients = yes/" /etc/freeradius/sql.conf
echo -e "\nATTRIBUTE Usage-Limit 3000 string\nATTRIBUTE Rate-Limit 3001 string" >> /etc/freeradius/dictionary

sed -i "s/$configValues\['CONFIG_DB_PASS'\] = '';/$configValues\['CONFIG_DB_PASS'\] = '"$RADPASS"';/" /var/www/html/library/daloradius.conf.php
sed -i "s/$configValues\['CONFIG_DB_USER'\] = 'root';/$configValues\['CONFIG_DB_USER'\] = 'radius';/" /var/www/html/library/daloradius.conf.php

if [ -n "$CLIENT_NET" ]; then
echo "client $CLIENT_NET { 
    	secret          = $CLIENT_SECRET 
    	shortname       = clients 
}" >> /etc/freeradius/clients.conf
fi 


#======== DELETE INIT CODE ==
echo "#!/bin/bash
#(while :
#do
#  mysqld_safe >/dev/null
#done) & 
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
/etc/init.d/mysql start
sleep 3
# Start Apache2 in the foreground and in debug mode
/usr/sbin/apachectl -DFOREGROUND -k start -e debug &

/usr/sbin/freeradius -X" > /init-freeradius.sh


# Start the MySQL service
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
/etc/init.d/mysql start
sleep 3

# Start Apache2 in the foreground and in debug mode
/usr/sbin/apachectl -DFOREGROUND -k start  &
/usr/sbin/freeradius -X
# Or with the local httpd.conf use
#&>/dev/null /usr/sbin/apachectl -DFOREGROUND -k start -e debug -d . -f httpd.conf
