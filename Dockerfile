# Official daloRADIUS Dockerfile
# GitHub: https://github.com/lirantal/daloradius
#
# Build image:
# 1. git pull git@github.com:lirantal/daloradius.git
# 2. docker build . -t lirantal/daloradius
#
# Run the container:
# 1. docker run -p 80:80 -d lirantal/daloradius

FROM ubuntu:20.04
MAINTAINER Liran Tal <liran.tal@gmail.com>

LABEL Description="daloRADIUS Official Docker based on Ubuntu 20.04 LTS and PHP7." \
	License="GPLv2" \
	Usage="docker build . -t lirantal/daloradius && docker run -d -p 80:80 lirantal/daloradius" \
	Version="1.0"

ENV DEBIAN_FRONTEND noninteractive

# default timezone
ENV TZ Europe/Vienna

# PHP install
RUN apt-get update \
	&& apt-get install --yes --no-install-recommends \
		ca-certificates \
		apt-utils \
		freeradius-utils \
		tzdata \
		apache2 \
		libapache2-mod-php \
		cron \
		net-tools \
		php \
		php-common \
		php-gd \
		php-curl \
		php-mail \
		php-dev \
		php-mail-mime \
		php-db \
		php-mysql \
		mariadb-client \
		libmysqlclient-dev \
		unzip \
		wget \
	&& rm -rf /var/lib/apt/lists/*


# PHP Pear DB library install
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
 && update-ca-certificates -f \
 && mkdir -p /tmp/pear/cache \
 && wget http://pear.php.net/go-pear.phar \
 && php go-pear.phar \
 && rm go-pear.phar \
 && pear channel-update pear.php.net \
 && pear install -a -f DB \
 && pear install -a -f Mail \
 && pear install -a -f Mail_Mime

# Add current project directory which should be a clone of daloradius from:
# git@github.com:lirantal/daloradius.git

# Create directories
# /data should be mounted as volume to avoid recreation of database entries
RUN mkdir /data /internal_data

ADD . /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Enable the .htaccess in /var/www/html
RUN /bin/sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/apache2.conf

# Make init.sh script executable
RUN chmod +x /var/www/html/init.sh

# Remove the original sample index.html file
RUN rm -rf /var/www/html/index.html

# Create daloRADIUS Log file
RUN touch /var/log/daloradius.log && chown -R www-data:www-data /var/log/daloradius.log

# Expose Web port for daloRADIUS
EXPOSE 80

# Run the script which executes Apache2 in the foreground as a running process
CMD ["/var/www/html/init.sh"]
