# Official daloRADIUS dockerfile
# GitHub: https://github.com/lirantal/daloradius
#
# Build image:
# 1. git pull git@github.com:lirantal/daloradius.git
# 2. docker build . -t lirantal/daloradius
#
# Run the container:
# 1. docker run -p 80:80 -p 8000:8000 -d lirantal/daloradius

FROM debian:13-slim

LABEL maintainer="Liran Tal <liran.tal@gmail.com>" \
	Description="daloRADIUS Official docker based on Debian 13 and PHP8." \
	License="GPLv2" \
	Usage="docker build . -t lirantal/daloradius && docker run -d -p 80:80 -p 8000:8000 lirantal/daloradius" \
	Version="2.0beta"

ENV DEBIAN_FRONTEND noninteractive

# default timezone (overridable at runtime via docker-compose/.env)
ENV TZ=Europe/Madrid

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
  php-cli \
  php-curl \
  php-mail \
  php-dev \
  php-mail-mime \
  php-mbstring \
  php-db \
  php-mysql \
  php-zip \
  mariadb-client \
  default-libmysqlclient-dev \
  unzip \
  wget \
  dos2unix \
  && rm -rf /var/lib/apt/lists/*

ADD contrib/docker/operators.conf /etc/apache2/sites-available/operators.conf
ADD contrib/docker/users.conf /etc/apache2/sites-available/users.conf
RUN a2dissite 000-default.conf && \
    a2ensite users.conf operators.conf && \
    sed -i 's/Listen 80/Listen 80\nListen 8000/' /etc/apache2/ports.conf

# Create directories
# /data should be mounted as volume to avoid recreation of database entries
RUN mkdir /data
ADD . /var/www/daloradius

#RUN touch /var/www/html/library/daloradius.conf.php
RUN chown -R www-data:www-data /var/www/daloradius

# Remove the original sample web folder
RUN rm -rf /var/www/html
#
# Create daloRADIUS Log file
RUN touch /var/log/freeradius/daloradius.log && chown -R www-data:www-data /var/log/freeradius/daloradius.log
RUN mkdir -p /var/log/apache2/daloradius && chown -R www-data:www-data /var/log/apache2/daloradius
RUN echo "Mutex posixsem" >> /etc/apache2/apache2.conf

# Add init script from docker directory and make it executable
ADD docker/daloradius/init.sh /var/www/daloradius/init.sh
RUN dos2unix /var/www/daloradius/init.sh || true && chmod +x /var/www/daloradius/init.sh

## Expose Web port for daloRADIUS
EXPOSE 80
EXPOSE 8000
#
## Run the script which executes Apache2 in the foreground as a running process
CMD ["/bin/bash", "/var/www/daloradius/init.sh"]
