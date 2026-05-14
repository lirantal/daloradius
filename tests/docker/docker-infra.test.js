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
