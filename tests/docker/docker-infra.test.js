const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const root = path.resolve(__dirname, "..", "..");

function read(relativePath) {
  return fs.readFileSync(path.join(root, relativePath), "utf8");
}

test("FreeRADIUS image uses one executable startup directive", () => {
  const dockerfile = read("Dockerfile-freeradius");

  assert.match(dockerfile, /^ENTRYPOINT \["\/app\/init-freeradius\.sh"\]$/m);
  assert.doesNotMatch(dockerfile, /^CMD \["\/app\/init-freeradius\.sh"\]$/m);
});

test("Compose avoids insecure local defaults", () => {
  const compose = read("docker-compose.yml");

  assert.doesNotMatch(compose, /radiusdbpw|radiusrootdbpw|testing123/);
  assert.match(compose, /MYSQL_PASSWORD=\$\{MYSQL_PASSWORD:\?Set MYSQL_PASSWORD\}/);
  assert.match(compose, /MYSQL_ROOT_PASSWORD=\$\{MYSQL_ROOT_PASSWORD:\?Set MYSQL_ROOT_PASSWORD\}/);
  assert.match(compose, /DEFAULT_CLIENT_SECRET=\$\{DEFAULT_CLIENT_SECRET:\?Set DEFAULT_CLIENT_SECRET\}/);
  assert.match(compose, /DALORADIUS_ADMIN_PASSWORD=\$\{DALORADIUS_ADMIN_PASSWORD:\?Set DALORADIUS_ADMIN_PASSWORD\}/);
});

test("Compose limits exposed admin surface and waits for FreeRADIUS health", () => {
  const compose = read("docker-compose.yml");

  assert.match(compose, /'\$\{DALORADIUS_OPERATORS_BIND:-127\.0\.0\.1:8000\}:8000'/);
  assert.match(compose, /radius:[\s\S]*?healthcheck:/);
  assert.match(compose, /radius-web:[\s\S]*?radius:[\s\S]*?condition: service_healthy/);
});

test("FreeRADIUS healthcheck queries the live status server", () => {
  const compose = read("docker-compose.yml");

  assert.doesNotMatch(compose, /freeradius -C/);
  assert.match(compose, /echo 'FreeRADIUS-Statistics-Type = 1' \| radclient -q -r 1 -t 3 127\.0\.0\.1:18121 status adminsecret >\/dev\/null/);
});

test("Compose runtime state does not live inside the build context", () => {
  const compose = read("docker-compose.yml");

  assert.doesNotMatch(compose, /\.\.?\s*\/data|\.\/data|"\.\/data|'\.\/data/);
  assert.match(compose, /radius_mysql:/);
  assert.match(compose, /radius_freeradius_data:/);
  assert.match(compose, /radius_daloradius_data:/);
});

test("Docker build context excludes local state and copies only required trees", () => {
  const dockerignore = read(".dockerignore");
  const dockerfile = read("Dockerfile");

  for (const ignoredPath of [".git", ".planning", "data/", "internal_data/", "*.log", "*.sql", ".env"]) {
    assert.match(dockerignore, new RegExp(`^${ignoredPath.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")}$`, "m"));
  }

  assert.doesNotMatch(dockerfile, /^ADD \. \/var\/www\/daloradius$/m);
  assert.match(dockerfile, /^COPY app \/var\/www\/daloradius\/app$/m);
  assert.match(dockerfile, /^COPY contrib \/var\/www\/daloradius\/contrib$/m);
  assert.match(dockerfile, /^COPY init\.sh \/var\/www\/daloradius\/init\.sh$/m);
});

test("Docker image recreates shared static asset symlinks", () => {
  const dockerfile = read("Dockerfile");

  assert.match(dockerfile, /rm -rf \/var\/www\/daloradius\/app\/operators\/static \/var\/www\/daloradius\/app\/users\/static/);
  assert.match(dockerfile, /ln -s \.\.\/common\/static \/var\/www\/daloradius\/app\/operators\/static/);
  assert.match(dockerfile, /ln -s \.\.\/common\/static \/var\/www\/daloradius\/app\/users\/static/);
});

test("Docker web logging defaults use the writable daloRADIUS var log path", () => {
  const dockerfile = read("Dockerfile");
  const webInit = read("init.sh");

  assert.match(dockerfile, /mkdir -p \/var\/www\/daloradius\/var\/log/);
  assert.match(dockerfile, /touch \/var\/www\/daloradius\/var\/log\/daloradius\.log/);
  assert.match(dockerfile, /chown -R www-data:www-data \/var\/www\/daloradius\/var/);
  assert.match(webInit, /php_config_set "CONFIG_LOG_FILE" "\/var\/www\/daloradius\/var\/log\/daloradius\.log"/);
  assert.match(webInit, /chown www-data:www-data "\$DALORADIUS_CONF_PATH"/);
  assert.match(webInit, /chmod 0644 "\$DALORADIUS_CONF_PATH"/);
  assert.doesNotMatch(webInit, /php_config_set "CONFIG_LOG_FILE" "\/tmp\/daloradius\.log"/);
});

test("FreeRADIUS log volume keeps log files readable by the web service", () => {
  const radiusInit = read("init-freeradius.sh");
  const compose = read("docker-compose.yml");

  assert.match(compose, /radius:[\s\S]*?- radius_logs:\/var\/log\/freeradius/);
  assert.match(compose, /radius-web:[\s\S]*?- radius_logs:\/var\/log\/freeradius/);
  assert.match(radiusInit, /function prepare_freeradius_logs/);
  assert.match(radiusInit, /function wait_for_radius_status/);
  assert.match(radiusInit, /radclient -q -r 1 -t 3 127\.0\.0\.1:18121 status adminsecret >\/dev\/null 2>&1/);
  assert.match(radiusInit, /chown -R freerad:33 \/var\/log\/freeradius/);
  assert.match(radiusInit, /find \/var\/log\/freeradius -type d -exec chmod 2750/);
  assert.match(radiusInit, /find \/var\/log\/freeradius -type f -exec chmod 0640/);
  assert.match(radiusInit, /freeradius -f "\$@" &[\s\S]*RADIUS_PID=\$![\s\S]*wait_for_radius_status[\s\S]*prepare_freeradius_logs/);
});

test("Docker web image provides readable placeholders for unavailable host logs", () => {
  const dockerfile = read("Dockerfile");

  assert.match(dockerfile, /System logs are not available inside this container/);
  assert.match(dockerfile, /> \/var\/log\/syslog/);
  assert.match(dockerfile, /Boot logs are not available inside this container/);
  assert.match(dockerfile, /> \/var\/log\/boot\.log/);
  assert.match(dockerfile, /chmod 0644 \/var\/log\/syslog \/var\/log\/boot\.log/);
});

test("Docker init scripts fail fast and use bounded database waits", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);

    assert.match(script, /^set -euo pipefail$/m);
    assert.match(script, /MYSQL_WAIT_RETRIES=\$\{MYSQL_WAIT_RETRIES:-30\}/);
    assert.match(script, /function wait_for_mysql/);
    assert.match(script, /while ! mysqladmin .* ping/);
    assert.match(script, /if \[ "\$attempt" -ge "\$MYSQL_WAIT_RETRIES" \]/);
  }
});

test("Dockerfiles normalize copied shell scripts before execution", () => {
  const webDockerfile = read("Dockerfile");
  const radiusDockerfile = read("Dockerfile-freeradius");
  const standaloneDockerfile = read("Dockerfile-standalone");

  assert.match(webDockerfile, /sed -i 's\/\\r\$\/\/' \/var\/www\/daloradius\/init\.sh/);
  assert.match(radiusDockerfile, /sed -i 's\/\\r\$\/\/' \/app\/init-freeradius\.sh/);
  assert.match(standaloneDockerfile, /sed -i 's\/\\r\$\/\/' \/usr\/local\/bin\/apache-config\.sh/);
});

test("Database initialization locks are backed by schema checks", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");

  assert.match(webInit, /function daloradius_schema_ready/);
  assert.match(webInit, /if daloradius_schema_ready; then[\s\S]*Database schema already present/);
  assert.match(webInit, /init_database[\s\S]*if ! daloradius_schema_ready; then/);

  assert.match(radiusInit, /function freeradius_schema_ready/);
  assert.match(radiusInit, /if freeradius_schema_ready; then[\s\S]*Database schema already present/);
  assert.match(radiusInit, /init_database[\s\S]*if ! freeradius_schema_ready; then/);
});

test("Docker init scripts do not expose DB passwords in process arguments", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);

    assert.doesNotMatch(script, /-p"\$MYSQL_PASSWORD"/);
    assert.match(script, /MYSQL_DEFAULTS_FILE=\$\(mktemp\)/);
    assert.match(script, /--defaults-extra-file="\$MYSQL_DEFAULTS_FILE"/);
  }
});

test("Docker init scripts escape environment-derived config values", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");

  assert.match(webInit, /function escape_sed_replacement/);
  assert.match(webInit, /function php_escape/);
  assert.match(webInit, /function php_config_set/);
  assert.match(radiusInit, /function escape_sed_replacement/);
  assert.match(radiusInit, /function sql_escape/);
  assert.match(radiusInit, /function require_default_client_secret/);
  assert.doesNotMatch(radiusInit, /echo "Adding client .*secret \$SECRET"/);
});

test("Operator passwords are hashed and Docker admin password is explicit", () => {
  const login = read("app/operators/dologin.php");
  const operatorNew = read("app/operators/config-operators-new.php");
  const operatorEdit = read("app/operators/config-operators-edit.php");
  const helper = read("app/operators/library/operator_passwords.php");
  const seedSql = read("contrib/db/mariadb-daloradius.sql");
  const webInit = read("init.sh");
  const installer = read("setup/install.sh");

  assert.match(helper, /function operator_password_hash/);
  assert.match(helper, /function operator_password_verify/);
  assert.doesNotMatch(login, /where username='%s' and password='%s'/i);
  assert.match(login, /operator_password_verify\(\$operator_pass, \$row\['password'\]\)/);
  assert.match(operatorNew, /operator_password_hash\(\$operator_password\)/);
  assert.match(operatorEdit, /operator_password_hash\(\$operator_password\)/);
  assert.match(operatorEdit, /"value" => ""/);
  assert.doesNotMatch(seedSql, /'administrator','radius'/);
  assert.match(webInit, /DALORADIUS_ADMIN_PASSWORD=\$\{DALORADIUS_ADMIN_PASSWORD:-\}/);
  assert.match(webInit, /function set_admin_password/);
  assert.match(webInit, /password_hash\(\$argv\[1\], PASSWORD_DEFAULT\)/);
  assert.match(installer, /INIT_PASSWORD_HASH=\$\(php -r 'echo password_hash\(\$argv\[1\], PASSWORD_DEFAULT\);'/);
});

test("Standalone image builds from local context on a supported PHP runtime", () => {
  const dockerfile = read("Dockerfile-standalone");
  const readme = read("README.docker-standalone.md");

  assert.doesNotMatch(dockerfile, /git clone/);
  assert.doesNotMatch(dockerfile, /FROM php:7-apache/);
  assert.doesNotMatch(dockerfile, /apt-get -y upgrade/);
  assert.match(dockerfile, /^FROM php:8\.4-apache(@sha256:[a-f0-9]{64})?$/m);
  assert.match(dockerfile, /^COPY app\/ \/var\/www\/html\/daloradius$/m);
  assert.match(dockerfile, /^COPY contrib\/scripts\/apache-config\.sh \/usr\/local\/bin\/apache-config\.sh$/m);
  assert.match(readme, /docker build -t daloradius-standalone -f Dockerfile-standalone \./);
  assert.doesNotMatch(readme, /dormancygrace\/daloradius/);
});

test("Containers use Docker-friendly process, log, and hardening defaults", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");
  const usersConf = read("contrib/docker/users.conf");
  const operatorsConf = read("contrib/docker/operators.conf");
  const compose = read("docker-compose.yml");

  assert.match(webInit, /exec \/usr\/sbin\/apachectl -DFOREGROUND -k start/);
  assert.match(radiusInit, /RADIUS_STATUS=\$\?/);
  assert.match(radiusInit, /exit "\$RADIUS_STATUS"/);
  assert.doesNotMatch(radiusInit, /chmod -R a\+rX/);
  assert.match(radiusInit, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:-require\}/);
  assert.match(radiusInit, /if \[ "\$FREERADIUS_SQL_TLS" = "disabled" \]/);
  assert.match(usersConf, /ErrorLog \/proc\/self\/fd\/2/);
  assert.match(usersConf, /CustomLog \/proc\/self\/fd\/1 combined/);
  assert.match(operatorsConf, /ErrorLog \/proc\/self\/fd\/2/);
  assert.match(operatorsConf, /CustomLog \/proc\/self\/fd\/1 combined/);
  assert.match(compose, /security_opt:[\s\S]*no-new-privileges:true/);
  assert.match(compose, /cap_drop:[\s\S]*- NET_RAW/);
});

test("Runtime images are pinned and avoid unnecessary build/debug packages", () => {
  const webDockerfile = read("Dockerfile");
  const radiusDockerfile = read("Dockerfile-freeradius");
  const standaloneDockerfile = read("Dockerfile-standalone");
  const compose = read("docker-compose.yml");

  assert.match(webDockerfile, /^FROM debian:13-slim@sha256:[a-f0-9]{64}$/m);
  assert.match(radiusDockerfile, /^FROM freeradius\/freeradius-server:3\.2\.8@sha256:[a-f0-9]{64}$/m);
  assert.match(standaloneDockerfile, /^FROM php:8\.4-apache@sha256:[a-f0-9]{64}$/m);
  assert.match(compose, /image: mariadb:11\.8@sha256:[a-f0-9]{64}/);
  assert.doesNotMatch(webDockerfile, /apt-utils|php-dev|default-libmysqlclient-dev|unzip|wget/);
  assert.doesNotMatch(radiusDockerfile, /apt-utils|libmysqlclient-dev|unzip|wget/);
  assert.match(radiusDockerfile, /-p 1812:1812\/udp -p 1813:1813\/udp/);
});
