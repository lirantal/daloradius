#!/usr/bin/env python3
"""Regression test for dictionary attribute creation success rendering.

Runs the production page through its POST path with an isolated synthetic DB.
No real database, session, or application files are modified.
"""

import json
import os
from pathlib import Path
import shlex
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
PHP = shlex.split(os.environ.get("PHP_COMMAND", "php"))


def write(path, content):
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content)


def run():
    with tempfile.TemporaryDirectory(prefix="dalo-attribute-new-test-") as temp:
        base = Path(temp)
        operators = base / "app/operators"
        target = operators / "mng-rad-attributes-new.php"
        target.parent.mkdir(parents=True)
        shutil.copyfile(ROOT / "app/operators/mng-rad-attributes-new.php", target)

        write(operators / "library/checklogin.php", "<?php $_SESSION['operator_user'] = 'fixture-admin';\n")
        write(operators / "library/check_operator_perm.php", "<?php // Allowed by fixture.\n")
        write(
            operators / "lang/main.php",
            "<?php $langCode = 'en'; function t($section, $key) { return $key; }\n",
        )
        write(
            base / "app/common/includes/config_read.php",
            "<?php $configValues = ['CONFIG_DB_TBL_DALODICTIONARY' => 'dictionary'];\n",
        )
        write(
            base / "app/common/includes/validation.php",
            """<?php
$valid_attributeTypes = ['string'];
$datalist_attributeTypes = ['string'];
$valid_ops = [':='];
$valid_recommendedHelpers = [''];
function dalo_check_csrf_token($token) { return $token === 'fixture-token'; }
function dalo_csrf_token() { return 'fixture-token'; }
""",
        )
        write(
            base / "app/common/includes/layout.php",
            """<?php
function print_html_prologue($title, $langCode) {}
function print_title_and_help($title, $help) {}
function open_form() {}
function open_fieldset($descriptor) {}
function print_form_component($descriptor) {}
function close_fieldset() {}
function close_form() {}
function print_back_to_previous_page() {}
function print_footer_and_html_epilogue() {}
""",
        )
        write(
            base / "app/common/includes/db_open.php",
            """<?php
class DB { public static function isError($value) { return false; } }
class FixtureResult { public function fetchrow() { return null; } }
class FixtureDB {
    public function escapeSimple($value) { return str_replace("'", "''", $value); }
    public function query($sql) {
        $GLOBALS['fixture_queries'][] = $sql;
        return str_starts_with($sql, 'SELECT ') ? new FixtureResult() : true;
    }
}
$dbSocket = new FixtureDB();
""",
        )
        write(base / "app/common/includes/db_close.php", "<?php // Synthetic DB has nothing to close.\n")
        write(
            operators / "include/management/actionMessages.php",
            "<?php if (isset($successMsg)) echo $successMsg; if (isset($failureMsg)) echo $failureMsg;\n",
        )
        write(operators / "include/config/logging.php", "<?php // Logging disabled in fixture.\n")

        driver = base / "driver.php"
        write(
            driver,
            f"""<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => 'fixture-token',
    'vendor' => 'Fixture-Vendor',
    'attribute' => 'Fixture-Attribute',
    'type' => 'string',
    'RecommendedOP' => ':=',
    'RecommendedTable' => 'check',
    'RecommendedHelper' => '',
    'RecommendedTooltip' => 'fixture tooltip',
];
$GLOBALS['fixture_queries'] = [];
chdir({json.dumps(str(operators))});
ob_start();
include {json.dumps(str(target))};
$html = ob_get_clean();
echo json_encode(['html' => $html, 'queries' => $GLOBALS['fixture_queries']], JSON_THROW_ON_ERROR);
""",
        )

        result = subprocess.run(PHP + [str(driver)], capture_output=True, text=True)
        assert result.returncode == 0, result.stdout + result.stderr
        assert "Fatal error" not in result.stdout + result.stderr, result.stdout + result.stderr
        payload = json.loads(result.stdout)
        assert len(payload["queries"]) == 2, payload["queries"]
        assert payload["queries"][0].startswith("SELECT DISTINCT(Vendor)"), payload["queries"]
        assert payload["queries"][1].startswith("INSERT INTO dictionary"), payload["queries"]
        expected = (
            'The new attribute has been inserted in the dictionary '
            '(attribute: Fixture-Attribute, vendor: Fixture-Vendor) '
            '[<a href="mng-rad-attributes-edit.php?vendor=Fixture-Vendor&attribute=Fixture-Attribute" '
            'title="Edit">Fixture-Attribute</a>]'
        )
        assert payload["html"] == expected, payload["html"]

    print("PASS: dictionary attribute POST renders its success response after insertion.")


if __name__ == "__main__":
    run()
