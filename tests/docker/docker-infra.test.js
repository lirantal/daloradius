const assert = require("node:assert/strict");
const childProcess = require("node:child_process");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const root = path.resolve(__dirname, "..", "..");

function read(relativePath) {
  return fs.readFileSync(path.join(root, relativePath), "utf8").replace(/\r\n/g, "\n");
}

function functionBody(script, functionName) {
  const functionStart = script.indexOf(`function ${functionName} {`);
  assert.notEqual(functionStart, -1);

  const nextFunctionStart = script.indexOf("\nfunction ", functionStart + 1);
  assert.notEqual(nextFunctionStart, -1);

  return script.slice(functionStart, nextFunctionStart);
}

function trackedFiles(...paths) {
  return childProcess
    .execFileSync("git", ["ls-files", "--", ...paths], { cwd: root, encoding: "utf8" })
    .split(/\r?\n/)
    .filter(Boolean);
}

function loggingAndEchoLines(script) {
  return script
    .split("\n")
    .filter((line) => line.includes("log_event") || line.includes("fail_step") || /^\s*echo\b/.test(line))
    .join("\n");
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function shellVariableReference(variableName) {
  const escapedName = escapeRegExp(variableName);
  return new RegExp(`\\$(?:\\{${escapedName}\\}|${escapedName})(?![A-Za-z0-9_])`);
}

function assertLineOrder(contents, beforeMarker, afterMarker) {
  const beforeIndex = contents.indexOf(beforeMarker);
  const afterIndex = contents.indexOf(afterMarker);

  assert.notEqual(beforeIndex, -1, `${beforeMarker.trim()} marker should be present`);
  assert.notEqual(afterIndex, -1, `${afterMarker.trim()} marker should be present`);
  assert.ok(beforeIndex < afterIndex, `${beforeMarker.trim()} should appear before ${afterMarker.trim()}`);
}

test("Secret variable reference matcher catches braced and unbraced shell variables", () => {
  const matcher = shellVariableReference("DEFAULT_CLIENT_SECRET");

  assert.match("echo $DEFAULT_CLIENT_SECRET", matcher);
  assert.match("echo ${DEFAULT_CLIENT_SECRET}", matcher);
  assert.doesNotMatch("fail_step \"validation\" \"missing_required_DEFAULT_CLIENT_SECRET\"", matcher);
});

test("Line order helper rejects missing markers before comparing order", () => {
  assert.throws(
    () => assertLineOrder("setup_mysql_defaults_file\n", "\nvalidate_runtime_config\n", "\nsetup_mysql_defaults_file\n"),
    /validate_runtime_config/,
  );
});

test("Docker compliance cleanup findings remain covered by explicit invariants", () => {
  const compose = read("docker-compose.yml");
  const dockerignore = read(".dockerignore");
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");
  const composeMysqlServiceMatch = compose.match(/radius-mysql:[\s\S]*?(?=\n  radius:)/);
  assert.notEqual(composeMysqlServiceMatch, null, "radius-mysql service should be present");
  const composeMysqlService = composeMysqlServiceMatch[0];
  const scriptBundle = `${webInit}\n${radiusInit}`;
  const loggingLines = `${loggingAndEchoLines(webInit)}\n${loggingAndEchoLines(radiusInit)}`;
  const discoverCidr = functionBody(radiusInit, "discover_container_cidr");
  const prepareLogs = functionBody(radiusInit, "prepare_freeradius_logs");

  const complianceFindings = [
    [
      "no tracked or build-context .env defaults",
      () => {
        assert.doesNotMatch(compose, /radiusdbpw|radiusrootdbpw|testing123/);
        assert.match(dockerignore, /^\.env$/m);
        assert.deepEqual(trackedFiles(".env"), []);
      },
    ],
    [
      "MariaDB 3306 is internal only",
      () => {
        assert.doesNotMatch(composeMysqlService, /^\s*ports:/m);
      },
    ],
    [
      "FreeRADIUS SQL TLS compose mode is explicit",
      () => {
        assert.match(compose, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:\?Set FREERADIUS_SQL_TLS to require or disabled\}/);
        assert.doesNotMatch(compose, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:-disabled\}/);
        assert.match(radiusInit, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:-require\}/);
      },
    ],
    [
      "Docker init scripts do not disable MySQL SSL checks",
      () => assert.doesNotMatch(scriptBundle, /--skip-ssl/),
    ],
    [
      "FreeRADIUS logs use grouped permissions",
      () => {
        assert.doesNotMatch(radiusInit, /chmod -R a\+rX/);
        assert.match(prepareLogs, /chown -R freerad:33 \/var\/log\/freeradius 2>>"\$INIT_ERROR_LOG"/);
        assert.match(prepareLogs, /find \/var\/log\/freeradius -type d -exec chmod 2750 \{\} \+ 2>>"\$INIT_ERROR_LOG"/);
        assert.match(prepareLogs, /find \/var\/log\/freeradius -type f -exec chmod 0640 \{\} \+ 2>>"\$INIT_ERROR_LOG"/);
      },
    ],
    [
      "structured logs and echo lines do not print secrets",
      () => {
        for (const secretVariable of [
          "DEFAULT_CLIENT_SECRET",
          "MYSQL_PASSWORD",
          "DALORADIUS_ADMIN_PASSWORD",
          "admin_hash",
          "client_secret_sql",
        ]) {
          assert.doesNotMatch(loggingLines, shellVariableReference(secretVariable));
        }
      },
    ],
    [
      "env-derived identifiers, hosts, and ports are validated before use",
      () => {
        for (const script of [webInit, radiusInit]) {
          assertLineOrder(script, "\nvalidate_runtime_config\n", "\nsetup_mysql_defaults_file\n");
          assert.match(script, /function require_sql_identifier/);
          assert.match(script, /function require_host_token/);
          assert.match(script, /function require_mysql_port/);
        }
      },
    ],
    [
      "critical Docker init writes and permissions are guarded",
      () => {
        for (const script of [webInit, radiusInit]) {
          assert.match(functionBody(script, "setup_mysql_defaults_file"), /fail_step "mysql_defaults_setup" "defaults_file_write_failed"/);
          assert.match(functionBody(script, "write_lock"), /fail_step "\$event" "lock_write_failed"/);
          assert.match(functionBody(script, "setup_error_log"), /fail_step "error_log_setup" "error_log_create_failed"/);
        }

        assert.match(discoverCidr, /fail_step "cidr_discovery" "container_cidr_discovery_failed"/);
        assert.match(prepareLogs, /fail_step "freeradius_log_permissions" "log_file_mode_failed"/);
      },
    ],
  ];

  for (const [finding, assertInvariant] of complianceFindings) {
    assert.doesNotThrow(assertInvariant, finding);
  }
});

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
  assert.doesNotMatch(compose, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:-disabled\}/);
  assert.match(compose, /FREERADIUS_SQL_TLS=\$\{FREERADIUS_SQL_TLS:\?Set FREERADIUS_SQL_TLS to require or disabled\}/);
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
  assert.match(webInit, /chmod 0600 "\$DALORADIUS_CONF_PATH"/);
  assert.doesNotMatch(webInit, /chmod 0?6[0-7][4-7] "\$DALORADIUS_CONF_PATH"/);
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
    assert.match(script, /function setup_mysql_defaults_file/);
    assert.match(script, /MYSQL_DEFAULTS_FILE="\$defaults_file"/);
    assert.match(script, /--defaults-extra-file="\$MYSQL_DEFAULTS_FILE"/);
  }
});

test("Docker init scripts escape environment-derived config values", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");
  const radiusSqlConfigSet = functionBody(radiusInit, "sql_config_set");

  assert.match(webInit, /function escape_sed_replacement/);
  assert.match(webInit, /function php_escape/);
  assert.match(webInit, /function php_config_set/);
  assert.match(radiusInit, /function escape_sed_replacement/);
  assert.match(radiusInit, /function freeradius_quote_escape/);
  assert.match(radiusInit, /function sql_escape/);
  assert.match(radiusInit, /function require_default_client_secret/);
  assert.match(radiusSqlConfigSet, /freeradius_quote_escape "\$2"/);
  assert.match(radiusSqlConfigSet, /escape_sed_replacement "\$\(freeradius_quote_escape "\$2"\)"/);
  assert.match(radiusInit, /escape_sed_replacement "\$\(freeradius_quote_escape "\$DEFAULT_CLIENT_SECRET"\)"/);
  assert.doesNotMatch(radiusInit, /echo "Adding client .*secret \$SECRET"/);
});

test("Docker init scripts validate runtime configuration before creating defaults files", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);
    const validationCallIndex = script.indexOf("\nvalidate_runtime_config\n");
    const defaultsFileIndex = script.indexOf("\nsetup_mysql_defaults_file\n");

    assert.match(script, /function validate_runtime_config/);
    assert.notEqual(validationCallIndex, -1);
    assert.notEqual(defaultsFileIndex, -1);
    assert.ok(validationCallIndex < defaultsFileIndex);

    const hostValidator = functionBody(script, "require_host_token");
    const hostCrlfCheckIndex = hostValidator.indexOf('reject_crlf "$name"');
    const hostSplitIndex = hostValidator.indexOf("IFS='.' read -r -a host_labels");
    const portValidator = functionBody(script, "require_mysql_port");

    assert.match(script, /function require_non_empty/);
    assert.match(script, /function reject_crlf/);
    assert.match(script, /function require_positive_integer/);
    assert.match(script, /function require_mysql_port/);
    assert.match(script, /function require_sql_identifier/);
    assert.match(script, /function require_host_token/);
    assert.match(script, /65535/);
    assert.doesNotMatch(portValidator, /\[ "\$value" -gt 65535 \]/);
    assert.match(portValidator, /\[ "\$\{#value\}" -gt 5 \]/);
    assert.match(portValidator, /\[ "\$\{#value\}" -eq 5 \]/);
    assert.match(portValidator, /\[\[ "\$value" > "65535" \]\]/);
    assert.match(script, /\[\[ "\$value" =~ \^\[A-Za-z_\]\[A-Za-z0-9_\]\*\$ \]\]/);
    assert.doesNotMatch(script, /\[\[ "\$value" =~ \^\[A-Za-z0-9_\]\+\$ \]\]/);
    assert.match(script, /\.\*\|\*\.\|\*\.\.\*\)/);
    assert.match(script, /IFS='\.' read -r -a host_labels <<< "\$value"/);
    assert.match(script, /for label in "\$\{host_labels\[@\]\}"; do/);
    assert.match(script, /\[\[ "\$label" =~ \^\[A-Za-z0-9\]\(\[A-Za-z0-9_-\]\*\[A-Za-z0-9\]\)\?\$ \]\]/);
    assert.doesNotMatch(script, /\[\[ "\$value" =~ \^\[A-Za-z0-9_.-\]\+\$ \]\]/);
    assert.notEqual(hostCrlfCheckIndex, -1);
    assert.notEqual(hostSplitIndex, -1);
    assert.ok(hostCrlfCheckIndex < hostSplitIndex);
  }

  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");

  assert.doesNotMatch(webInit, /MYSQL_PASSWORD=\$\{MYSQL_PASSWORD:-radpass\}/);
  assert.doesNotMatch(radiusInit, /MYSQL_PASSWORD=\$\{MYSQL_PASSWORD:-radpass\}/);
  assert.match(webInit, /MYSQL_PASSWORD=\$\{MYSQL_PASSWORD:-\}/);
  assert.match(radiusInit, /MYSQL_PASSWORD=\$\{MYSQL_PASSWORD:-\}/);
  assert.match(webInit, /require_non_empty "DALORADIUS_ADMIN_PASSWORD"/);
  assert.match(webInit, /validate_optional_no_crlf "DEFAULT_CLIENT_SECRET"/);
  assert.match(webInit, /validate_optional_no_crlf "MAIL_SMTPADDR"/);
  assert.match(webInit, /validate_optional_no_crlf "PASSWORD_MIN_LENGTH"/);

  assert.match(radiusInit, /require_non_empty "DEFAULT_CLIENT_SECRET"/);
  assert.match(radiusInit, /reject_crlf "DEFAULT_CLIENT_SECRET"/);
  assert.match(radiusInit, /FREERADIUS_SQL_TLS" != "require"/);
  assert.match(radiusInit, /FREERADIUS_SQL_TLS" != "disabled"/);
});

test("Docker init scripts use structured redacted init logging", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);
    const logEvent = functionBody(script, "log_event");
    const failStep = functionBody(script, "fail_step");
    const validationHelpers = [
      "require_non_empty",
      "reject_crlf",
      "require_positive_integer",
      "require_mysql_port",
      "require_sql_identifier",
      "require_host_token",
    ].map((helperName) => functionBody(script, helperName)).join("\n");

    assert.match(script, /function log_event/);
    assert.match(script, /function fail_step/);
    assert.match(logEvent, /level=%s component=/);
    assert.match(logEvent, /event=%s outcome=%s detail=%s/);
    assert.match(logEvent, /component=[a-z-]+-init/);
    assert.match(failStep, /log_event "error"/);
    assert.match(failStep, /exit 1/);
    assert.match(validationHelpers, /fail_step "validation"/);
    assert.doesNotMatch(validationHelpers, /echo "\$name must/);
    assert.doesNotMatch(validationHelpers, /echo "FREERADIUS_SQL_TLS must/);
  }
});

test("Critical Docker init database operations do not dump raw command errors", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");

  assert.match(webInit, /INIT_ERROR_LOG=\$\{INIT_ERROR_LOG:-\/data\/daloradius-init-errors\.log\}/);
  assert.match(webInit, /log_event "info" "mysql_wait" "start" "waiting_for_mysql"/);
  assert.match(webInit, /mysqladmin --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" ping --silent >\/dev\/null 2>>"\$INIT_ERROR_LOG"/);
  assert.match(webInit, /fail_step "mysql_wait" "mysql_wait_timeout"/);
  assert.match(webInit, /log_event "info" "daloradius_schema_import" "start" "importing_schema"/);
  assert.match(webInit, /mysql --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" "\$MYSQL_DATABASE" < "\$DALORADIUS_PATH\/contrib\/db\/mariadb-daloradius\.sql" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(webInit, /fail_step "daloradius_schema_import" "schema_import_failed"/);
  assert.match(webInit, /log_event "info" "daloradius_schema_import" "success" "schema_imported"/);
  assert.match(webInit, /log_event "info" "admin_password_update" "start" "updating_admin_password"/);
  assert.match(webInit, /mysql --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" "\$MYSQL_DATABASE" 2>>"\$INIT_ERROR_LOG" <<EOSQL\nUPDATE operators SET password='\$admin_hash'/);
  assert.match(webInit, /fail_step "admin_password_update" "admin_password_update_failed"/);
  assert.match(webInit, /log_event "info" "admin_password_update" "success" "admin_password_updated"/);
  assert.match(webInit, /log_event "info" "daloradius_schema_check" "start" "checking_schema"/);
  assert.match(webInit, /log_event "info" "daloradius_schema_check" "success" "schema_present"/);
  assert.match(webInit, /fail_step "daloradius_schema_check" "schema_post_check_failed"/);

  assert.match(radiusInit, /INIT_ERROR_LOG=\$\{INIT_ERROR_LOG:-\/data\/freeradius-init-errors\.log\}/);
  assert.match(radiusInit, /log_event "info" "mysql_wait" "start" "waiting_for_mysql"/);
  assert.match(radiusInit, /mysqladmin --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" ping --silent >\/dev\/null 2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusInit, /fail_step "mysql_wait" "mysql_wait_timeout"/);
  assert.match(radiusInit, /log_event "info" "freeradius_schema_import" "start" "importing_schema"/);
  assert.match(radiusInit, /mysql --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" "\$MYSQL_DATABASE" < "\$RADIUS_PATH\/mods-config\/sql\/main\/mysql\/schema\.sql" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusInit, /fail_step "freeradius_schema_import" "schema_import_failed"/);
  assert.match(radiusInit, /log_event "info" "freeradius_schema_import" "success" "schema_imported"/);
  assert.match(radiusInit, /log_event "info" "ippool_schema_import" "start" "importing_schema"/);
  assert.match(radiusInit, /mysql --defaults-extra-file="\$MYSQL_DEFAULTS_FILE" "\$MYSQL_DATABASE" < "\$RADIUS_PATH\/mods-config\/sql\/ippool\/mysql\/schema\.sql" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusInit, /fail_step "ippool_schema_import" "schema_import_failed"/);
  assert.match(radiusInit, /log_event "info" "ippool_schema_import" "success" "schema_imported"/);
  assert.match(radiusInit, /log_event "info" "radhuntgroup_schema_ensure" "start" "ensuring_schema"/);
  assert.match(radiusInit, /fail_step "radhuntgroup_schema_ensure" "schema_ensure_failed"/);
  assert.match(radiusInit, /log_event "info" "radhuntgroup_schema_ensure" "success" "schema_ensured"/);
  assert.match(radiusInit, /log_event "info" "nas_client_insert" "start" "inserting_docker_client"/);
  assert.match(radiusInit, /fail_step "nas_client_insert" "client_insert_failed"/);
  assert.match(radiusInit, /log_event "info" "nas_client_insert" "success" "docker_client_inserted"/);
  assert.match(radiusInit, /fail_step "noresetcounter_config" "noresetcounter_insert_failed"/);
  assert.match(radiusInit, /log_event "info" "freeradius_schema_check" "start" "checking_schema"/);
  assert.match(radiusInit, /log_event "info" "freeradius_schema_check" "success" "schema_present"/);
  assert.match(radiusInit, /fail_step "freeradius_schema_check" "schema_post_check_failed"/);
});

test("Docker init scripts initialize private error logs before redirection use", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);
    const setupErrorLog = functionBody(script, "setup_error_log");
    const setupCallIndex = script.indexOf("\nsetup_error_log\n");
    const validationCallIndex = script.indexOf("\nvalidate_runtime_config\n");
    const mysqlDefaultsCallIndex = script.indexOf("\nsetup_mysql_defaults_file\n");

    assert.match(script, /function setup_error_log/);
    assert.match(setupErrorLog, /: > "\$INIT_ERROR_LOG"; \} 2>\/dev\/null/);
    assert.match(setupErrorLog, /chmod 600 "\$INIT_ERROR_LOG" 2>\/dev\/null/);
    assert.match(setupErrorLog, /fail_step "error_log_setup" "error_log_create_failed"/);
    assert.match(setupErrorLog, /fail_step "error_log_setup" "error_log_chmod_failed"/);
    assert.doesNotMatch(setupErrorLog, /2>>"\$INIT_ERROR_LOG"/);
    assert.notEqual(setupCallIndex, -1);
    assert.notEqual(validationCallIndex, -1);
    assert.notEqual(mysqlDefaultsCallIndex, -1);
    assert.ok(setupCallIndex < validationCallIndex);
    assert.ok(setupCallIndex < mysqlDefaultsCallIndex);
    assert.doesNotMatch(script, /INIT_ERROR_LOG=\$\{INIT_ERROR_LOG:-\/tmp\//);
  }
});

test("Docker init scripts guard MySQL defaults files and lock writes", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);
    const mysqlDefaults = functionBody(script, "setup_mysql_defaults_file");
    const writeLock = functionBody(script, "write_lock");
    const setupErrorLogIndex = script.indexOf("\nsetup_error_log\n");
    const validateRuntimeIndex = script.indexOf("\nvalidate_runtime_config\n");
    const setupMysqlIndex = script.indexOf("\nsetup_mysql_defaults_file\n");

    assert.match(mysqlDefaults, /defaults_file="\$\(mktemp\)"/);
    assert.match(mysqlDefaults, /MYSQL_DEFAULTS_FILE="\$defaults_file"/);
    assert.match(mysqlDefaults, /chmod 600 "\$MYSQL_DEFAULTS_FILE" 2>>"\$INIT_ERROR_LOG"/);
    assert.match(mysqlDefaults, /cat 2>>"\$INIT_ERROR_LOG" > "\$MYSQL_DEFAULTS_FILE"/);
    assert.match(mysqlDefaults, /password=\$MYSQL_PASSWORD/);
    assert.match(mysqlDefaults, /trap 'rm -f "\$MYSQL_DEFAULTS_FILE"' EXIT/);
    assert.match(mysqlDefaults, /fail_step "mysql_defaults_setup" "defaults_file_create_failed"/);
    assert.match(mysqlDefaults, /fail_step "mysql_defaults_setup" "defaults_file_chmod_failed"/);
    assert.match(mysqlDefaults, /fail_step "mysql_defaults_setup" "defaults_file_write_failed"/);
    assert.doesNotMatch(mysqlDefaults, /log_event [^\n]*MYSQL_PASSWORD/);
    assert.match(writeLock, /date 2>>"\$INIT_ERROR_LOG" > "\$lock_path"/);
    assert.match(writeLock, /fail_step "\$event" "lock_write_failed"/);
    assert.notEqual(setupErrorLogIndex, -1);
    assert.notEqual(validateRuntimeIndex, -1);
    assert.notEqual(setupMysqlIndex, -1);
    assert.ok(setupErrorLogIndex < validateRuntimeIndex);
    assert.ok(validateRuntimeIndex < setupMysqlIndex);
    assert.doesNotMatch(script, /\nMYSQL_DEFAULTS_FILE=\$\(mktemp\)\n/);
    assert.doesNotMatch(script, /\nchmod 600 "\$MYSQL_DEFAULTS_FILE"\n/);
    assert.doesNotMatch(script, /\ncat > "\$MYSQL_DEFAULTS_FILE" <<EOF\n/);
    assert.doesNotMatch(script, /date > "\$(?:INIT|DB)_LOCK"/);
  }
});

test("FreeRADIUS init guards CIDR discovery and log permission setup", () => {
  const radiusInit = read("init-freeradius.sh");
  const initDatabase = functionBody(radiusInit, "init_database");
  const discoverCidr = functionBody(radiusInit, "discover_container_cidr");
  const prepareLogs = functionBody(radiusInit, "prepare_freeradius_logs");

  assert.match(radiusInit, /function discover_container_cidr/);
  assert.match(initDatabase, /discover_container_cidr/);
  assert.match(discoverCidr, /container_cidr=/);
  assert.doesNotMatch(initDatabase, /ifconfig eth0/);
  assert.doesNotMatch(initDatabase, /ipcalc \$container_ip_address \$container_netmask/);
  assert.match(discoverCidr, /ifconfig eth0 2>>"\$INIT_ERROR_LOG"/);
  assert.match(discoverCidr, /fail_step "cidr_discovery" "container_ip_discovery_failed"/);
  assert.match(discoverCidr, /fail_step "cidr_discovery" "container_netmask_discovery_failed"/);
  assert.match(discoverCidr, /ipcalc "\$container_ip_address" "\$container_netmask" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(discoverCidr, /fail_step "cidr_discovery" "container_cidr_discovery_failed"/);
  assert.match(discoverCidr, /fail_step "cidr_discovery" "container_cidr_empty"/);
  assert.match(prepareLogs, /chown -R freerad:33 \/var\/log\/freeradius 2>>"\$INIT_ERROR_LOG"/);
  assert.match(prepareLogs, /fail_step "freeradius_log_permissions" "log_owner_failed"/);
  assert.match(prepareLogs, /find \/var\/log\/freeradius -type d -exec chmod 2750 \{\} \+ 2>>"\$INIT_ERROR_LOG"/);
  assert.match(prepareLogs, /fail_step "freeradius_log_permissions" "log_directory_mode_failed"/);
  assert.match(prepareLogs, /find \/var\/log\/freeradius -type f -exec chmod 0640 \{\} \+ 2>>"\$INIT_ERROR_LOG"/);
  assert.match(prepareLogs, /fail_step "freeradius_log_permissions" "log_file_mode_failed"/);
});

test("Docker init scripts guard critical file configuration steps", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");
  const webConfigSet = functionBody(webInit, "php_config_set");
  const webInitBody = functionBody(webInit, "init_daloradius");
  const radiusConfigSet = functionBody(radiusInit, "sql_config_set");
  const radiusInitBody = functionBody(radiusInit, "init_freeradius");
  const noresetcounterBody = functionBody(radiusInit, "enable_noresetcounter");

  assert.match(webConfigSet, /sed -i[\s\S]*2>>"\$INIT_ERROR_LOG"/);
  assert.match(webConfigSet, /fail_step "daloradius_init" "config_update_failed"/);
  assert.match(webInitBody, /log_event "info" "daloradius_init" "start" "configuring_daloradius"/);
  assert.match(webInitBody, /cp "\$DALORADIUS_CONF_PATH\.sample" "\$DALORADIUS_CONF_PATH" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(webInitBody, /fail_step "daloradius_init" "config_copy_failed"/);
  assert.match(webInitBody, /chown www-data:www-data "\$DALORADIUS_CONF_PATH" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(webInitBody, /fail_step "daloradius_init" "config_owner_failed"/);
  assert.match(webInitBody, /chmod 0600 "\$DALORADIUS_CONF_PATH" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(webInitBody, /fail_step "daloradius_init" "config_mode_failed"/);
  assert.match(webInitBody, /log_event "info" "daloradius_init" "success" "daloradius_configured"/);

  assert.match(radiusConfigSet, /sed -i[\s\S]*2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusConfigSet, /fail_step "freeradius_init" "sql_config_update_failed"/);
  assert.match(radiusInitBody, /log_event "info" "freeradius_init" "start" "configuring_freeradius"/);
  assert.match(radiusInitBody, /run_freeradius_sed/);
  assert.match(radiusInitBody, /run_freeradius_link/);
  assert.match(radiusInitBody, /chown root:freerad "\$RADIUS_PATH\/mods-available\/sql" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusInitBody, /fail_step "freeradius_init" "sql_config_owner_failed"/);
  assert.match(radiusInitBody, /chmod 0640 "\$RADIUS_PATH\/mods-available\/sql" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(radiusInitBody, /fail_step "freeradius_init" "sql_config_mode_failed"/);
  assert.match(radiusInitBody, /log_event "info" "freeradius_init" "success" "freeradius_configured"/);
  assertLineOrder(
    radiusInitBody,
    'sql_config_set "password" "$MYSQL_PASSWORD"',
    'chown root:freerad "$RADIUS_PATH/mods-available/sql" 2>>"$INIT_ERROR_LOG"',
  );
  assertLineOrder(
    radiusInitBody,
    'run_freeradius_sed "s|testing123|$(escape_sed_replacement "$(freeradius_quote_escape "$DEFAULT_CLIENT_SECRET")")|" "$RADIUS_PATH/mods-available/sql"',
    'chmod 0640 "$RADIUS_PATH/mods-available/sql" 2>>"$INIT_ERROR_LOG"',
  );
  assertLineOrder(
    radiusInitBody,
    'chmod 0640 "$RADIUS_PATH/mods-available/sql" 2>>"$INIT_ERROR_LOG"',
    'log_event "info" "freeradius_init" "success" "freeradius_configured"',
  );
  assert.match(radiusInit, /function run_freeradius_sed/);
  assert.match(radiusInit, /function run_freeradius_link/);
  assert.match(radiusInit, /sed -i "\$@" 2>>"\$INIT_ERROR_LOG" \|\| fail_step "freeradius_init" "config_update_failed"/);
  assert.match(radiusInit, /ln -sf "\$@" 2>>"\$INIT_ERROR_LOG" \|\| fail_step "freeradius_init" "config_link_failed"/);
  assert.match(noresetcounterBody, /2>>"\$INIT_ERROR_LOG" > \/tmp\/freeradius-default/);
  assert.match(noresetcounterBody, /mv \/tmp\/freeradius-default "\$RADIUS_PATH\/sites-available\/default" 2>>"\$INIT_ERROR_LOG"/);
  assert.match(noresetcounterBody, /fail_step "noresetcounter_config" "noresetcounter_apply_failed"/);
});

test("Docker init structured logs do not interpolate secret values", () => {
  for (const scriptPath of ["init.sh", "init-freeradius.sh"]) {
    const script = read(scriptPath);
    const loggingCalls = script
      .split("\n")
      .filter((line) => line.includes("log_event") || line.includes("fail_step"))
      .join("\n");

    assert.doesNotMatch(loggingCalls, /\$MYSQL_PASSWORD/);
    assert.doesNotMatch(loggingCalls, /\$MYSQL_ROOT_PASSWORD/);
    assert.doesNotMatch(loggingCalls, /\$DEFAULT_CLIENT_SECRET/);
    assert.doesNotMatch(loggingCalls, /\$DALORADIUS_ADMIN_PASSWORD/);
    assert.doesNotMatch(loggingCalls, /\$admin_hash/);
    assert.doesNotMatch(loggingCalls, /password=.*\$/i);
    assert.doesNotMatch(loggingCalls, /secret=.*\$/i);
  }
});

test("FreeRADIUS Docker client insertion uses descriptive variable names", () => {
  const radiusInit = read("init-freeradius.sh");

  assert.doesNotMatch(radiusInit, /^\s*(IP|NM|CIDR|SECRET)=/m);
  assert.match(radiusInit, /^\s*(?:if ! )?container_ip_address=/m);
  assert.match(radiusInit, /^\s*(?:if ! )?container_netmask=/m);
  assert.match(radiusInit, /^\s*(?:if ! )?container_cidr=/m);
  assert.match(radiusInit, /^\s*client_secret=/m);
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
  assert.doesNotMatch(webInit, /password_hash\(\$argv\[1\]/);
  assert.doesNotMatch(webInit, /php\b[^\n|;&]*\$DALORADIUS_ADMIN_PASSWORD/);
  assert.match(installer, /INIT_PASSWORD_HASH=\$\(php -r 'echo password_hash\(\$argv\[1\], PASSWORD_DEFAULT\);'/);
});

test("Docker init database writes do not pass secrets through process arguments", () => {
  const webInit = read("init.sh");
  const radiusInit = read("init-freeradius.sh");
  const adminPasswordUpdate = functionBody(webInit, "set_admin_password").match(
    /mysql\b[\s\S]*?fail_step "admin_password_update" "admin_password_update_failed"/,
  );
  const nasInsertMatch = radiusInit.match(/mysql\b[\s\S]*?INSERT INTO nas[\s\S]*?fail_step "nas_client_insert" "client_insert_failed"/);

  assert.notEqual(adminPasswordUpdate, null, "admin password update mysql command should be present");
  assert.doesNotMatch(webInit, /mysql\b[\s\S]*?-e\s+"UPDATE operators SET password=/);
  assert.match(adminPasswordUpdate[0], /<<[-]?'?[A-Z_]*'?/);
  assert.doesNotMatch(adminPasswordUpdate[0].split(/<<[-]?'?[A-Z_]*'?/)[0], /\$admin_hash/);
  assert.match(adminPasswordUpdate[0], /\$admin_hash/);

  assert.notEqual(nasInsertMatch, null, "NAS insert mysql command should be present");
  assert.doesNotMatch(radiusInit, /mysql\b[^\n]*-e\s+"INSERT INTO nas/);
  assert.doesNotMatch(nasInsertMatch[0], /mysql\b[\s\S]*?-e\s+"INSERT INTO nas/);
  assert.match(nasInsertMatch[0], /<<[-]?'?[A-Z_]*'?/);
  assert.match(nasInsertMatch[0], /client_secret_sql/);
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
