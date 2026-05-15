# Docker operations runbook

This runbook covers two supported Docker startup paths:

- start a clean daloRADIUS and FreeRADIUS stack without importing an existing database;
- rebuild a clean stack and import an existing MariaDB dump before starting the full application.

Run every command from the repository root.

## Prerequisites

- Docker Engine or Docker Desktop with Docker Compose v2.
- A project `.env` file created from `.env.example`.
- `gzip` in `PATH` when importing `.sql.gz` dumps.
- A database dump available locally when using the import workflow.

Create `.env`:

On Linux:

```bash
cp .env.example .env
```

On Windows:

```powershell
Copy-Item .env.example .env
```


Edit `.env`, uncomment the required variables, and replace every `CHANGE_ME_...` value:

```dotenv
MYSQL_PASSWORD=CHANGE_ME_RADIUS_DB_PASSWORD
MYSQL_ROOT_PASSWORD=CHANGE_ME_ROOT_DB_PASSWORD
DEFAULT_CLIENT_SECRET=CHANGE_ME_RADIUS_SHARED_SECRET
DALORADIUS_ADMIN_PASSWORD=CHANGE_ME_ADMIN_PASSWORD
FREERADIUS_SQL_TLS=disabled
```

Optional values can be kept at their defaults or customized:

```dotenv
DALORADIUS_OPERATORS_BIND=127.0.0.1:8000
TZ=Europe/Vienna
MAIL_SMTPADDR=127.0.0.1
MAIL_PORT=25
MAIL_FROM=root@daloradius.xdsl.by
MAIL_AUTH=
```

Validate the Compose configuration before starting the stack:

```powershell
docker compose config --quiet
```

## Normal startup without database import

Use this path for a new local instance that should initialize its own database.

1. Confirm `.env` exists and contains all required values.

   On Linux:

   ```bash
   test -f .env
   docker compose config --quiet
   ```

   On Windows:

   ```powershell
   Test-Path .env
   docker compose config --quiet
   ```


2. Build and start the full stack.

   ```powershell
   docker compose up -d --build
   ```

3. Wait until the services are running and healthy.

   ```powershell
   docker compose ps
   ```

   Expected services:

   - `radius-mysql`: healthy MariaDB database;
   - `radius`: healthy FreeRADIUS service;
   - `radius-web`: daloRADIUS web service.

4. Open the web interfaces.

   - Operators UI: `http://127.0.0.1:8000/login.php`
   - Users UI: `http://localhost/`

5. Log in to the operators UI with:

   - username: `administrator`
   - password: the value configured in `DALORADIUS_ADMIN_PASSWORD`

## Changing the administrator password after initialization

`radius-web` applies `DALORADIUS_ADMIN_PASSWORD` to the `administrator` operator every time the container starts. This means the database password is updated after the first initialization too, as long as `radius-web` is recreated with the new environment value.

After changing `DALORADIUS_ADMIN_PASSWORD` in `.env`, recreate `radius-web`:

```powershell
docker compose up -d --force-recreate radius-web
```

Linux:

```bash
docker compose up -d --force-recreate radius-web
```

Then log in again with:

- username: `administrator`
- password: the new `DALORADIUS_ADMIN_PASSWORD` value

A plain container restart is not enough to load a changed `.env` value:

```powershell
docker compose restart radius-web
```

That command restarts the existing container with the environment it already had when it was created. Use `docker compose up -d --force-recreate radius-web` when the goal is to apply a changed `.env` value to the database.

6. Inspect logs if a service does not start.

   ```powershell
   docker compose logs radius-web
   docker compose logs radius
   docker compose logs radius-mysql
   ```

7. Stop the stack without deleting data.

   ```powershell
   docker compose stop
   ```

8. Start an existing stack again without rebuilding.

   ```powershell
   docker compose start
   ```

9. Delete the stack and all Docker volumes only when you intentionally want to remove the local database and runtime data.

   ```powershell
   docker compose down -v --remove-orphans
   ```

## Startup with existing database import

Use this path when migrating a database from a previous non-Docker daloRADIUS or FreeRADIUS instance.

The import workflow must run before `radius` and `radius-web` start against the migrated database. The provided scripts enforce this order:

1. validate `.env` through `docker compose config --quiet`;
2. validate the compressed dump with `gzip -t`;
3. start only `radius-mysql`;
4. import the dump into the `radius` database;
5. apply SQL migrations from `docker/post-import-migrations`;
6. build `radius-web`;
7. convert imported daloRADIUS operator passwords to PHP password hashes;
8. validate required imported tables and `operators.password`;
9. start the complete stack.

The password conversion only updates daloRADIUS operators in the `operators` table. It does not rewrite user portal passwords or FreeRADIUS authentication attributes such as `radcheck`.

### Replacing an existing Docker database

To replace an existing database that already lives in the Docker Compose volume, remove the Compose volumes before running the import script:

On Linux:

```bash
docker compose down -v --remove-orphans
bash scripts/docker/import-backup.sh ./backup_radius.sql.gz
```

On Windows:
```powershell
docker compose down -v --remove-orphans
.\scripts\docker\import-backup.ps1 -DumpPath .\backup_radius.sql.gz
```


This is the supported clean replacement path. It deletes the `radius_mysql` volume, recreates an empty Docker database, imports the dump, applies post-import migrations, hashes imported operator passwords, validates the schema, and only then starts the complete stack.

Do not use the import script alone when you need a guaranteed full replacement of the Docker database. If the existing `radius_mysql` volume is kept, the script imports the dump into the current database. Tables included in the dump may be replaced depending on the dump contents, but extra tables or residual state not covered by the dump can remain.

### Clean import on Linux

1. Place the dump in the repository root or note its absolute path.

   Example:

   ```bash
   test -f ./backup_radius.sql.gz
   ```

2. Delete any existing Compose stack and volumes.

   This removes the local Docker database.

   ```bash
   docker compose down -v --remove-orphans
   ```

3. Run the import script.

   ```bash
   bash scripts/docker/import-backup.sh ./backup_radius.sql.gz
   ```

4. Confirm the stack is up.

   ```bash
   docker compose ps
   ```

5. Confirm imported operator passwords are already hashed.

   ```bash
   docker compose run --rm --no-deps --entrypoint php radius-web -r '$pdo=new PDO("mysql:host=".getenv("MYSQL_HOST").";port=".getenv("MYSQL_PORT").";dbname=".getenv("MYSQL_DATABASE").";charset=utf8mb4",getenv("MYSQL_USER"),getenv("MYSQL_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $rows=$pdo->query("SELECT username,password FROM operators")->fetchAll(PDO::FETCH_ASSOC); $non=0; foreach($rows as $r){ $info=password_get_info($r["password"]); if(($info["algo"] ?? 0)===0){ $non++; } } echo "operator_rows=".count($rows)."\nnon_hash_operator_passwords=$non\n";'
   ```

   Expected result:

   ```text
   non_hash_operator_passwords=0
   ```

6. Confirm the `administrator` operator uses the password configured in `.env`.

   ```bash
   docker compose run --rm --no-deps --entrypoint php radius-web -r '$pdo=new PDO("mysql:host=".getenv("MYSQL_HOST").";port=".getenv("MYSQL_PORT").";dbname=".getenv("MYSQL_DATABASE").";charset=utf8mb4",getenv("MYSQL_USER"),getenv("MYSQL_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $hash=$pdo->query("SELECT password FROM operators WHERE username=\"administrator\"")->fetchColumn(); echo "administrator_env_password_valid=".(password_verify(getenv("DALORADIUS_ADMIN_PASSWORD"),$hash) ? "valid" : "invalid")."\n";'
   ```

   Expected result:

   ```text
   administrator_env_password_valid=valid
   ```




### Clean import on Windows

1. Place the dump in the repository root or note its absolute path.

   Example:

   ```powershell
   Test-Path .\backup_radius.sql.gz
   ```

2. Delete any existing Compose stack and volumes.

   This removes the local Docker database.

   ```powershell
   docker compose down -v --remove-orphans
   ```

3. Run the import script.

   ```powershell
   .\scripts\docker\import-backup.ps1 -DumpPath .\backup_radius.sql.gz
   ```

4. Confirm the stack is up.

   ```powershell
   docker compose ps
   ```

5. Confirm imported operator passwords are already hashed.

   ```powershell
   docker compose run --rm --no-deps --entrypoint php radius-web -r '$pdo=new PDO("mysql:host=".getenv("MYSQL_HOST").";port=".getenv("MYSQL_PORT").";dbname=".getenv("MYSQL_DATABASE").";charset=utf8mb4",getenv("MYSQL_USER"),getenv("MYSQL_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $rows=$pdo->query("SELECT username,password FROM operators")->fetchAll(PDO::FETCH_ASSOC); $non=0; foreach($rows as $r){ $info=password_get_info($r["password"]); if(($info["algo"] ?? 0)===0){ $non++; } } echo "operator_rows=".count($rows)."\nnon_hash_operator_passwords=$non\n";'
   ```

   Expected result:

   ```text
   non_hash_operator_passwords=0
   ```

6. Confirm the `administrator` operator uses the password configured in `.env`.

   ```powershell
   docker compose run --rm --no-deps --entrypoint php radius-web -r '$pdo=new PDO("mysql:host=".getenv("MYSQL_HOST").";port=".getenv("MYSQL_PORT").";dbname=".getenv("MYSQL_DATABASE").";charset=utf8mb4",getenv("MYSQL_USER"),getenv("MYSQL_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $hash=$pdo->query("SELECT password FROM operators WHERE username=\"administrator\"")->fetchColumn(); echo "administrator_env_password_valid=".(password_verify(getenv("DALORADIUS_ADMIN_PASSWORD"),$hash) ? "valid" : "invalid")."\n";'
   ```

   Expected result:

   ```text
   administrator_env_password_valid=valid
   ```


## Post-import checks

Run these checks after either import script finishes.

Confirm service state:

```powershell
docker compose ps
```

Confirm `operators.password` can store PHP password hashes:

```powershell
docker compose run --rm --no-deps --entrypoint php radius-web -r '$pdo=new PDO("mysql:host=".getenv("MYSQL_HOST").";port=".getenv("MYSQL_PORT").";dbname=".getenv("MYSQL_DATABASE").";charset=utf8mb4",getenv("MYSQL_USER"),getenv("MYSQL_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $row=$pdo->query("SELECT CHARACTER_MAXIMUM_LENGTH AS width, IS_NULLABLE AS nullable FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=\"operators\" AND COLUMN_NAME=\"password\"")->fetch(PDO::FETCH_ASSOC); echo "operators_password_width=".$row["width"]."\noperators_password_nullable=".$row["nullable"]."\n";'
```

Expected result:

```text
operators_password_width=95
operators_password_nullable=NO
```

Confirm the conversion is idempotent:

```powershell
docker compose run --rm --no-deps --entrypoint php radius-web /usr/local/bin/daloradius-hash-imported-passwords.php
```

Expected result after a completed import:

```text
operator_passwords_converted=0
administrator_password_updated=0
```

## Troubleshooting

If the dump import fails before the full stack starts, inspect the database service:

```powershell
docker compose ps
docker compose logs radius-mysql
```

If `radius-web` starts but login fails, confirm `administrator` exists and validates against `DALORADIUS_ADMIN_PASSWORD` using the check above.

If a local import needs to be repeated from scratch, remove volumes again before rerunning the import script:

On Linux:

```bash
docker compose down -v --remove-orphans
bash scripts/docker/import-backup.sh ./backup_radius.sql.gz
```

On Windows:
```powershell
docker compose down -v --remove-orphans
.\scripts\docker\import-backup.ps1 -DumpPath .\backup_radius.sql.gz
```
