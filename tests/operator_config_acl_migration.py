#!/usr/bin/env python3
"""Integration test for the operator configuration ACL migration.

Runs the migration twice against a disposable MariaDB instance that mimics the
2.3 ACL state. No live daloRADIUS database or application data is modified.
"""

from pathlib import Path
import secrets
import subprocess
import time

ROOT = Path(__file__).resolve().parents[1]
MIGRATION = ROOT / "contrib/db/migrations/2026-09-operator-config-acls.sql"
PREFIX = "dalo-operator-acl-" + secrets.token_hex(5)
DB = PREFIX + "-db"
NETWORK = PREFIX + "-network"


def run(*args, input_text=None, check=True):
    result = subprocess.run(
        args,
        input=input_text,
        capture_output=True,
        text=True,
    )
    if check and result.returncode != 0:
        raise AssertionError(
            f"Command failed ({result.returncode}): {' '.join(args)}\n"
            f"stdout:\n{result.stdout}\nstderr:\n{result.stderr}"
        )
    return result.stdout.strip()


def sql(statement):
    return run(
        "docker", "exec", "-i", DB,
        "mariadb", "-uroot", "radius", "--batch", "--skip-column-names",
        input_text=statement,
    )


def wait_for_database():
    deadline = time.monotonic() + 60
    while time.monotonic() < deadline:
        result = subprocess.run(
            [
                "docker", "exec", DB,
                "mariadb", "-uroot", "--batch", "--skip-column-names",
                "-e", "SELECT @@skip_networking",
            ],
            capture_output=True,
            text=True,
        )
        if result.returncode == 0 and result.stdout.strip() == "0":
            return
        time.sleep(1)
    raise AssertionError("MariaDB final server did not become ready")


def scalar(statement):
    value = sql(statement)
    assert "\n" not in value, value
    return value


def main():
    migration = MIGRATION.read_text()
    try:
        run("docker", "network", "create", "--internal", NETWORK)
        run(
            "docker", "run", "-d", "--name", DB, "--network", NETWORK,
            "--tmpfs", "/var/lib/mysql",
            "-e", "MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1",
            "-e", "MARIADB_DATABASE=radius",
            "mariadb:11.8",
        )
        wait_for_database()

        sql("""
CREATE TABLE operators (
    id INT NOT NULL PRIMARY KEY,
    username VARCHAR(128) NOT NULL
);
CREATE TABLE operators_acl_files (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    file VARCHAR(128) NOT NULL,
    category VARCHAR(128) NOT NULL,
    section VARCHAR(128) NOT NULL
);
CREATE TABLE operators_acl (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    operator_id INT NOT NULL,
    file VARCHAR(128) NOT NULL,
    access TINYINT NOT NULL DEFAULT 0
);
INSERT INTO operators (id, username) VALUES
    (1, 'administrator'),
    (2, 'limited-operator'),
    (3, 'existing-denial');
INSERT INTO operators_acl_files (file, category, section) VALUES
    ('config_mail', 'Configuration', 'Mail');
INSERT INTO operators_acl (operator_id, file, access) VALUES
    (1, 'config_mail_settings', 1),
    (1, 'config_mail_testing', 1),
    (3, 'config_messages', 0);
""")

        sql(migration)
        sql(migration)

        required = "'config_mail_settings','config_mail_testing','config_messages'"
        assert scalar(
            f"SELECT COUNT(*) FROM operators_acl_files WHERE file IN ({required})"
        ) == "3"
        assert scalar(
            f"SELECT COUNT(DISTINCT file) FROM operators_acl_files WHERE file IN ({required})"
        ) == "3"
        assert scalar(
            "SELECT COUNT(*) FROM operators_acl_files WHERE file='config_mail'"
        ) == "1"

        rows = sql(f"""
SELECT operators.username, operators_acl.file, operators_acl.access
FROM operators
JOIN operators_acl ON operators_acl.operator_id = operators.id
WHERE operators_acl.file IN ({required})
ORDER BY operators.id, operators_acl.file;
""").splitlines()
        assert rows == [
            "administrator\tconfig_mail_settings\t1",
            "administrator\tconfig_mail_testing\t1",
            "administrator\tconfig_messages\t1",
            "limited-operator\tconfig_mail_settings\t0",
            "limited-operator\tconfig_mail_testing\t0",
            "limited-operator\tconfig_messages\t0",
            "existing-denial\tconfig_mail_settings\t0",
            "existing-denial\tconfig_mail_testing\t0",
            "existing-denial\tconfig_messages\t0",
        ], rows

        assert scalar(
            f"SELECT COUNT(*) FROM operators_acl WHERE file IN ({required})"
        ) == "9"
        assert scalar("""
SELECT COUNT(*)
FROM operators_acl_files AS opf
LEFT JOIN operators_acl AS opa ON opf.file = opa.file
WHERE opa.operator_id = 2
  AND opf.file IN ('config_mail_settings','config_mail_testing','config_messages');
""") == "3"
        assert scalar("""
SELECT access FROM operators_acl
WHERE operator_id = 1 AND file = 'config_messages';
""") == "1"
        assert scalar("""
SELECT access FROM operators_acl
WHERE operator_id = 2 AND file = 'config_mail_settings';
""") == "0"

        print("PASS: 2.3 ACL gaps repaired; administrator granted; other operators denied")
        print("PASS: existing ACL decisions preserved and ACL editor rows restored")
        print("PASS: migration is idempotent")
    finally:
        run("docker", "rm", "-f", "-v", DB, check=False)
        run("docker", "network", "rm", NETWORK, check=False)
        leftovers = run(
            "docker", "ps", "-aq", "--filter", "name=" + PREFIX, check=False
        )
        assert leftovers == "", leftovers
        print("CLEANUP: disposable MariaDB and network removed; live database untouched")


if __name__ == "__main__":
    main()
