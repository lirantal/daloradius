# AGENTS.md

Guidance for AI coding agents working on daloRADIUS. Human contributors should read [CONTRIBUTING.md](CONTRIBUTING.md) too.

## Project

daloRADIUS is a PHP web UI for FreeRADIUS. It has no framework, no Composer root, and no build step; pages are plain PHP files served directly. Data access goes through PDO with the MySQL driver (`pdo_mysql`) against MariaDB, which is shared with FreeRADIUS.

## Layout

- `app/operators/`: operators (admin) web root. Each page is a top-level `*.php` file; shared logic lives in `library/` and `include/`; translations live in `lang/` (`en.php` is the fallback).
- `app/users/`: end-user portal web root.
- `app/common/includes/`: shared config, PDO connection, validation, layout. `daloradius.conf.php` is generated from `daloradius.conf.php.sample` and is gitignored.
- `app/common/library/`: vendored third-party PHP (dompdf, htmlpurifier, phpmailer, php-svg-qrcode, totp-php). Do not edit or lint it as first-party code.
- `app/common/static/`: shared JS, CSS, and images. The minified Bootstrap and Chart.js bundles (`*.min.*` and their source maps) and `css/icons/` are vendored; the other JS and CSS files (e.g. `request.js`, `dynamic_attributes.js`, `daloradius-responsive.css`) are first-party.
- `contrib/db/`: base schemas (`fr3-mariadb-freeradius.sql`, `mariadb-daloradius.sql`, dictionaries) and `migrations/` for upgrades.
- `init.sh`, `Dockerfile`, `docker-compose.yml`: Docker image and runtime upgrade logic. `setup/install.sh`: bare-metal Debian installer.
- `doc/`: install and setup guides.

## Local environment

`.agents/setup` prepares a Debian 12 machine (including Amp orbs): PHP 8.2 CLI with the extensions from the `Dockerfile`, a local MariaDB with `raddb` seeded from `contrib/db`, and a local `daloradius.conf.php`. It is idempotent; rerun it after pulling new migrations.

- DB: `raddb` on `localhost:3306`, user `raduser` / `radpass` (the sample config defaults). Inspect with `sudo mariadb raddb`. Setup records imported SQL files in `orb_setup.applied_sql` and imports new migrations on rerun; to reseed from scratch, `DROP DATABASE raddb` and rerun setup.
- Operators login: `administrator` / `radius` (local dev seed only).
- Web: `.amp/services.yaml` serves `app/operators` and `app/users` with `php -S`. In an orb, run `amp orb services ensure`; elsewhere run `php -S 127.0.0.1:8000 -t app/operators`.
- App log: `var/log/daloradius.log`.

## Verifying changes

There is no automated test suite or CI in this repository. Verify by:

- Linting changed PHP: `php -l <file>`, or all first-party code with `find app -name '*.php' -not -path 'app/common/library/*' -print0 | xargs -0 -n1 -P8 sh -c 'php -l "$0" >/dev/null || exit 1'` (prints only errors; exits non-zero if any file fails).
- Exercising the affected page against the local MariaDB, signed in as an operator. State-changing requests require the `csrf_token` from the form.
- Checking the PHP server output for warnings and fatals.

## Conventions

- Use PDO prepared statements for all SQL; never interpolate request data into queries.
- Escape HTML output with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`, as neighboring pages do.
- Operator pages are gated by ACL entries in `operators_acl_files` / `operators_acl`. A new page needs ACL rows in the base schema and in a migration.
- Schema changes need a new file in `contrib/db/migrations/`, the matching change in `contrib/db/mariadb-daloradius.sql`, and, when existing Docker deployments must be upgraded, an idempotent step in `init.sh`.
- `contrib/db/mariadb-daloradius.sql` has mixed CRLF/LF line endings; preserve them when editing.
- New user-facing strings go in `app/operators/lang/en.php`; other languages fall back to English.
- Commits follow [Conventional Commits](https://www.conventionalcommits.org/), e.g. `fix(users): ...`.
- Agent-authored PR titles end with `🤖🤖🤖` (see CONTRIBUTING.md). This repository has no `package.json`, Changesets, or devcontainer, so those CONTRIBUTING.md sections do not apply.
