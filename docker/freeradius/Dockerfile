# Dockerfile for daloRADIUS FreeRADIUS service
# GitHub: https://github.com/lirantal/daloradius
#
# Build:
#   docker compose -f docker/freeradius/docker-compose.yml build
#
# Run:
#   docker compose -f docker/freeradius/docker-compose.yml up -d

FROM freeradius/freeradius-server:3.2.8
LABEL maintainer="Liran Tal <liran.tal@gmail.com>"
LABEL Description="FreeRADIUS Docker image optimized for daloRADIUS." \
	License="GPLv2" \
	Usage="docker compose -f docker/freeradius/docker-compose.yml up -d" \
	Version="3.2"


ENV DEBIAN_FRONTEND=noninteractive

# Default timezone — use ARG so docker-compose.yml can pass TZ from .env at build time
ARG TZ_DEFAULT=Europe/Vienna
ENV TZ=${TZ_DEFAULT}

RUN apt-get update \
	&& apt-get install --yes --no-install-recommends \
		ipcalc \
		tzdata \
		net-tools \
		mariadb-client \
	&& rm -rf /var/lib/apt/lists/*

# Create directories
# /data should be mounted as volume to avoid recreation of database entries
RUN mkdir /app /data
WORKDIR /app

# Copy init script to image
COPY docker/freeradius/init-freeradius.sh /app

# Make init script executable
RUN sed -i 's/\r$//' /app/init-freeradius.sh \
	&& chmod +x /app/init-freeradius.sh

# Expose FreeRADIUS Ports
EXPOSE 1812/udp 1813/udp

ENTRYPOINT ["/app/init-freeradius.sh"]
