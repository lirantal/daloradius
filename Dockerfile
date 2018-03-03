# Official daloRADIUS Dockerfile
# GitHub: https://github.com/lirantal/daloradius
#
# Build image:
# 1. git pull git@github.com:lirantal/daloradius.git
# 2. docker build . -t lirantal/daloradius
#
# Run the container:
# 1. docker run -p 80:80 -d lirantal/daloradius

FROM ubuntu:16.04
MAINTAINER Liran Tal <liran.tal@gmail.com>

LABEL Description="daloRADIUS Official Docker based on Ubuntu 16.04 LTS and PHP7." \
	License="GPLv2" \
	Usage="docker build . -t lirantal/daloradius && docker run -d -p 80:80 lirantal/daloradius" \
	Version="1.0"

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
	php-mail \
	php-mail-mime \
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
RUN chown -R mysql:mysql /var/lib/mysql /var/run/mysqld; /usr/bin/mysqld_safe & \
 sleep 10s && \
 /usr/bin/mysql --host localhost --port 3306 -u root --password="" -e "CREATE DATABASE radius" && \
 /usr/bin/mysql -u root --password="" radius < /var/www/html/contrib/db/fr2-mysql-daloradius-and-freeradius.sql

# Enable the .htaccess in /var/www/html
RUN /bin/sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/apache2.conf

# Enable PHP short tags
RUN /bin/sed -i "s/short_open_tag\ \=\ Off/short_open_tag\ \=\ On/g" /etc/php/7.0/apache2/php.ini

# Make init.sh script executable
RUN chmod +x /var/www/html/init.sh

# Remove the original sample index.html file
RUN rm -rf /var/www/html/index.html

# Create daloRADIUS Log file
RUN touch /var/log/daloradius.log && chown -R www-data:www-data /var/log/daloradius.log

# Expose FreeRADIUS Ports, MySQL, and Web for daloRADIUS
EXPOSE 1812 1813 80 443 3306

# Run the script which executes Apache2 in the foreground as a running process
CMD ["/var/www/html/init.sh"]
