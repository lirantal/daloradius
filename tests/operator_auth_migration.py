#!/usr/bin/env python3
"""Integration test for the operator LDAP schema migration.

Runs the migration twice against a disposable MariaDB 11.8 instance. The
legacy rows and password values are checked before and after the upgrade.
"""

from pathlib import Path
import secrets
import subprocess
import time

ROOT = Path(__file__).resolve().parents[1]
MIGRATION = ROOT / "contrib/db/migrations/2026-09-operator-ldap.sql"
PREFIX = "dalo-operator-ldap-" + secrets.token_hex(5)
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
    raise AssertionError("MariaDB server did not become ready")


def scalar(statement):
    value = sql(statement)
    assert "\n" not in value, value
    return value


def main():
    migration = MIGRATION.read_text()
    legacy_password = "$2y$10$abcdefghijklmnopqrstuuC6wQ7m0O3xJ7mV5Q4mN2pL1"
    old_operator_password = "old-local-password"
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

        sql(f"""
CREATE TABLE operators (
    id INT NOT NULL PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    password VARCHAR(128) NOT NULL
);
INSERT INTO operators (id, username, password) VALUES
    (1, 'administrator', '{legacy_password}'),
    (2, 'legacy-operator', '{old_operator_password}');
""")

        before = sql("SELECT id, username, password FROM operators ORDER BY id")
        assert before == (
            f"1\tadministrator\t{legacy_password}\n"
            f"2\tlegacy-operator\t{old_operator_password}"
        ), before

        sql(migration)
        sql(migration)

        assert scalar("SELECT COUNT(*) FROM operators") == "2"
        assert scalar(
            "SELECT COLUMN_DEFAULT FROM information_schema.columns "
            "WHERE table_schema = DATABASE() AND table_name = 'operators' "
            "AND column_name = 'auth_source'"
        ) == "'local'"
        assert scalar(
            "SELECT CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT "
            "FROM information_schema.columns "
            "WHERE table_schema = DATABASE() AND table_name = 'operators' "
            "AND column_name = 'password'"
        ) == "95\tYES\tNULL"
        assert scalar(
            "SELECT CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT "
            "FROM information_schema.columns "
            "WHERE table_schema = DATABASE() AND table_name = 'operators' "
            "AND column_name = 'external_id'"
        ) == "255\tYES\tNULL"

        rows = sql("""
SELECT id, username, password, auth_source, IFNULL(external_id, '<NULL>')
FROM operators
ORDER BY id;
""").splitlines()
        assert rows == [
            f"1\tadministrator\t{legacy_password}\tlocal\t<NULL>",
            f"2\tlegacy-operator\t{old_operator_password}\tlocal\t<NULL>",
        ], rows

        sql("""
INSERT INTO operators (id, username, password, auth_source, external_id)
VALUES (3, 'ldap-operator', NULL, 'ldap',
        'uid=ldap-operator,ou=people,dc=example,dc=org');
""")
        ldap_row = sql(
            "SELECT username, auth_source, external_id, IFNULL(password, '<NULL>') "
            "FROM operators WHERE username = 'ldap-operator'"
        )
        assert ldap_row == (
            "ldap-operator\tldap\tuid=ldap-operator,ou=people,dc=example,dc=org\t<NULL>"
        )
        assert scalar("SELECT COUNT(*) FROM operators") == "3"
        assert scalar(
            "SELECT NON_UNIQUE FROM information_schema.statistics "
            "WHERE table_schema = DATABASE() AND table_name = 'operators' "
            "AND index_name = 'operators_external_id_uq'"
        ) == "0"
        duplicate = subprocess.run(
            ["docker", "exec", "-i", DB, "mariadb", "-uroot", "radius"],
            input=("INSERT INTO operators (id, username, password, auth_source, external_id) "
                   "VALUES (4, 'duplicate-ldap', NULL, 'ldap', "
                   "'uid=ldap-operator,ou=people,dc=example,dc=org');"),
            capture_output=True,
            text=True,
        )
        assert duplicate.returncode != 0, "duplicate external_id was accepted"

        print("PASS: legacy operator rows and password values preserved")
        print("PASS: auth_source defaults to local and external_id/password are nullable")
        print("PASS: LDAP operator row accepts external identity without a local password")
        print("PASS: duplicate LDAP external identities are rejected")
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
