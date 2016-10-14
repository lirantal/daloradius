FROM ubuntu:16.04
MAINTAINER Liran Tal <liran.tal@gmail.com>

# silence package installations to that debpkg doesn't prompt for mysql
# passwords and other input
ENV DEBIAN_FRONTEND "noninteractive"
ENV mysql_pass ""

# PHP install
RUN apt-get update && \
	apt-get -y install php7.0 \
  php7.0-cli \
	php7.0-common \
	php7.0-curl \
  php7.0-gd \
  php7.0-mcrypt \
	php7.0-mysql \
  php-pear \
  php-db

# Apache2 install
RUN apt-get -y install apache2 libapache2-mod-php7.0

# PHP Pear DB library install
RUN pear install DB && rm -rf /var/cache/apk/*

# MySQL server install
RUN apt-get install -y mysql-server

# Add current project directory which should be a clone of daloradius from:
# git@github.com:lirantal/daloradius.git

ADD . /var/www/html
RUN chown www-data.www-data -R /var/www/html

# Run MySQL server so that it initializes the database and seeds information
RUN /usr/bin/mysqld_safe & \
 sleep 10s && \
 /usr/bin/mysql --host localhost --port 3306 -u root --password="" -e "CREATE DATABASE radius" && \
 /usr/bin/mysql -u root --password="" radius < /var/www/html/contrib/db/fr2-mysql-daloradius-and-freeradius.sql

#
RUN chmod +x /var/www/html/init.sh

# Expose FreeRADIUS Ports, MySQL, and Web for daloRADIUS
EXPOSE 1812 1813 80 443 3306

# Run the script which executes Apache2 in the foreground as a running process
CMD ["/var/www/html/init.sh"]
