# run daloradius as a standalone container
## prerequisite

1. mysql server and freeradius (in docker or on-premise) server that has been configured properly
2. docker runtime

## how to run

1. run prebuilt image
2. build the image first

### preparing daloradius.conf.php

you can edit sample config <https://github.com/lirantal/daloradius/blob/master/app/common/includes/daloradius.conf.php.sample> and then mount it in container or you can just run container, edit mounted config and re-run container

## prebuilt image

```bash
docker run --name daloradius-standalone -v /path/to/daloradius.conf.php:/var/www/html/daloradius/common/includes/daloradius.conf.php -p 80:80 -p 8000:8000 -d dormancygrace/daloradius
```

## build image

```bash
docker build -t daloradius-standalone -f Dockerfile-standalone
```

```bash
docker run --name daloradius-standalone -v /path/to/daloradius.conf.php:/var/www/html/daloradius/common/includes/daloradius.conf.php -p 80:80 -p 8000:8000 -d daloradius-standalone
```
