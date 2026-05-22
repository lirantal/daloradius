# Official daloRADIUS Dockerfile
# GitHub: https://github.com/lirantal/daloradius
#
# Build image:
# 1. git pull git@github.com:lirantal/daloradius.git
# 2. docker build . -t lirantal/daloradius
#
# Run the container:
# 1. docker run -p 80:80 -p 8000:8000 -d lirantal/daloradius

FROM debian:13-slim
LABEL maintainer="Liran Tal <liran.tal@gmail.com>"
LABEL Description="daloRADIUS Official Docker based on Debian 13 and PHP 8.4." \
	License="GPLv2" \
	Usage="docker build . -t lirantal/daloradius && docker run -d -p 80:80 -p 8000:8000 lirantal/daloradius" \
	Version="2.3"

ENV DEBIAN_FRONTEND=noninteractive

# default timezone
ENV TZ=Europe/Vienna

# PHP install
RUN apt-get update \
  && apt-get install --yes --no-install-recommends \
  ca-certificates \
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
  php-mail-mime \
  php-mbstring \
  php-db \
  php-mysql \
  php-zip \
  mariadb-client \
  && rm -rf /var/lib/apt/lists/*

COPY contrib/docker/operators.conf /etc/apache2/sites-available/operators.conf
COPY contrib/docker/users.conf /etc/apache2/sites-available/users.conf
RUN a2dissite 000-default.conf && \
    a2ensite users.conf operators.conf && \
    sed -i 's/Listen 80/Listen 80\nListen 8000/' /etc/apache2/ports.conf

# Create directories
# /data should be mounted as volume to avoid recreation of database entries
RUN mkdir /data
COPY . /var/www/daloradius
RUN rm -f /var/www/daloradius/app/operators/static /var/www/daloradius/app/users/static \
  && ln -s ../common/static /var/www/daloradius/app/operators/static \
  && ln -s ../common/static /var/www/daloradius/app/users/static \
  && sed -i 's/\r$//' /var/www/daloradius/init.sh

#RUN touch /var/www/html/library/daloradius.conf.php
RUN chown -R www-data:www-data /var/www/daloradius

# Remove the original sample web folder
RUN rm -rf /var/www/html
#
# Create daloRADIUS Log file
RUN mkdir -p /var/www/daloradius/var/log \
  && touch /var/www/daloradius/var/log/daloradius.log \
  && chown -R www-data:www-data /var/www/daloradius/var
RUN mkdir -p /var/log/apache2/daloradius && chown -R www-data:www-data /var/log/apache2/daloradius
# Allow Apache/PHP to read FreeRADIUS logs mounted from the radius container.
RUN usermod -a -G freerad www-data \
  && printf '%s\n' "System logs are not available inside this container. Use docker logs radius-web." > /var/log/syslog \
  && printf '%s\n' "Boot logs are not available inside this container. Use docker logs radius-web." > /var/log/boot.log \
  && chmod 0644 /var/log/syslog /var/log/boot.log
RUN echo "Mutex posixsem" >> /etc/apache2/apache2.conf

## Expose Web port for daloRADIUS
EXPOSE 80
EXPOSE 8000
#
## Run the script which executes Apache2 in the foreground as a running process
CMD ["/bin/bash", "/var/www/daloradius/init.sh"]
