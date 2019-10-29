# Official daloRADIUS Dockerfile
# GitHub: https://github.com/lirantal/daloradius
#
# Build image:
# 1. git pull git@github.com:lirantal/daloradius.git
# 2. docker build -t lirantal/daloradius -f Dockerfile-freeradius
#
# Run the container:
# 1. docker run -p 80:80 -d lirantal/daloradius

FROM ubuntu:16.04
MAINTAINER Liran Tal <liran.tal@gmail.com>

LABEL Description="daloRADIUS Official Docker based on Ubuntu 16.04 LTS and PHP7." \
	License="GPLv2" \
	Usage="docker build -t lirantal/daloradius -f Dockerfile-freeradius && docker run -d -p 80:80 lirantal/daloradius" \
	Version="1.0"

# silence package installations to that debpkg doesn't prompt for mysql
# passwords and other input
ARG DEBIAN_FRONTEND=noninteractive
ENV mysql_pass ""
ENV RADPASS radpass
ENV CLIENT_NET "0.0.0.0/0"
ENV CLIENT_SECRET 891011121314

# PHP,Apache2,MySQL and FreeRADIUS install
RUN apt-get update && \
	apt-get -y install php7.0 \
        php7.0-cli \
	php7.0-common \
	php7.0-curl \
        php7.0-gd \
        php7.0-mcrypt \
	php7.0-mysql \
	php-mail \
	php-mail-mime nano \
        php-pear \
        php-db \
	net-tools \
        freeradius-utils \
        apache2 \
	libapache2-mod-php7.0 \
        mysql-server \
	mysql-client \
        freeradius \
	freeradius-mysql \
        cron \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* \
# PHP Pear DB library install
 && pear install DB \
 && pear install -a Mail \
 && pear install -a Mail_Mime

# Add current project directory which should be a clone of daloradius from:
# git@github.com:lirantal/daloradius.git

ADD . /var/www/html
RUN chown www-data.www-data -R /var/www/html && mkdir -p /var/run/mysqld

# Run MySQL server so that it initializes the database and seeds information
#RUN chown -R mysql:mysql /var/lib/mysql /var/run/mysqld; /usr/bin/mysqld_safe & \
# sleep 10s && \
# /usr/bin/mysql --host localhost --port 3306 -u root --password="" -e "CREATE DATABASE radius" && \
# /usr/bin/mysql -u root --password="" radius < /var/www/html/contrib/db/fr2-mysql-daloradius-and-freeradius.sql

# Enable the .htaccess in /var/www/html
RUN /bin/sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/apache2.conf

# Enable PHP short tags
RUN /bin/sed -i "s/short_open_tag\ \=\ Off/short_open_tag\ \=\ On/g" /etc/php/7.0/apache2/php.ini

# Make init.sh script executable
RUN chmod +x /var/www/html/init-freeradius.sh

# Remove the original sample index.html file
RUN rm -rf /var/www/html/index.html

# Create daloRADIUS Log file
RUN touch /var/log/daloradius.log && chown -R www-data:www-data /var/log/daloradius.log

# Expose FreeRADIUS Ports, MySQL, and Web for daloRADIUS
EXPOSE 1812 1813 80 443

# Run the script which executes Apache2 in the foreground as a running process
CMD ["/var/www/html/init-freeradius.sh"]
