# daloRADIUS Standalone Web Image

Build and run daloRADIUS as a single container with an external MariaDB and
FreeRADIUS server (managed outside this repository).

## Build

```bash
docker build -t daloradius-standalone -f Dockerfile-standalone .
```

## Run

Create a `daloradius.conf.php` with your external database and RADIUS settings,
then mount it into the container:

```bash
docker run --name daloradius-standalone \
  -v /path/to/daloradius.conf.php:/var/www/html/daloradius/common/includes/daloradius.conf.php:ro \
  -p 80:80 \
  -p 0.0.0.0:8000:8000 \
  -d daloradius-standalone
```

## Access

- Users UI: `http://<server-ip>/`
- Operators UI: `http://<server-ip>:8000/`

Default credentials: `administrator` / `radius`

## Notes

- This image does NOT include MariaDB or FreeRADIUS.
- You must provide your own `daloradius.conf.php` with the connection details.
- For the full Docker Compose stack (all-in-one), see `README.docker-compose.md`.
