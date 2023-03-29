# run freeradius as a standalone container

## prerequisite

1. a standalone mysql server and freeradius server that has been configured properly
2. docker runtime

## how to run

build the image first

```bash
docker build -t daloradius-standalone -f Dockerfile-standalone
```

next, run the image

```bash
docker run --name daloradius-standalone -v /path/to/daloradius:/var/www/html -v /path/to/daloradius.conf.php:/var/www/html/library/daloradius.conf.php -p 80:80 -d daloradius-standalone
```
