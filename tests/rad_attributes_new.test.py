#!/usr/bin/env python3
"""Regression test for dictionary attribute creation and edit-link rendering.

Runs the production create and edit pages with an isolated stateful synthetic DB.
No real database, session, or application files are modified.
"""

from html.parser import HTMLParser
import json
import os
from pathlib import Path
import shlex
import shutil
import subprocess
import tempfile
from urllib.parse import parse_qs, urlsplit

ROOT = Path(__file__).resolve().parents[1]
PHP = shlex.split(os.environ.get("PHP_COMMAND", "php"))
VENDOR = 'Fixture & "Vendor"'
ATTRIBUTE = 'Fixture <Attribute> & "quoted"'


class LinkParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = []

    def handle_starttag(self, tag, attrs):
        if tag == "a":
            self.links.append(dict(attrs))


def write(path, content):
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content)


def run_php(driver):
    result = subprocess.run(PHP + [str(driver)], capture_output=True, text=True)
    assert result.returncode == 0, result.stdout + result.stderr
    assert result.stderr == "", result.stderr
    assert "Fatal error" not in result.stdout, result.stdout
    return json.loads(result.stdout)


def run():
    with tempfile.TemporaryDirectory(prefix="dalo-attribute-new-test-") as temp:
        base = Path(temp)
        operators = base / "app/operators"
        common_includes = base / "app/common/includes"
        create_page = operators / "mng-rad-attributes-new.php"
        edit_page = operators / "mng-rad-attributes-edit.php"
        operators.mkdir(parents=True)
        shutil.copyfile(ROOT / "app/operators/mng-rad-attributes-new.php", create_page)
        shutil.copyfile(ROOT / "app/operators/mng-rad-attributes-edit.php", edit_page)

        write(operators / "library/checklogin.php", "<?php $_SESSION['operator_user'] = 'fixture-admin';\n")
        write(operators / "library/check_operator_perm.php", "<?php // Allowed by fixture.\n")
        write(
            operators / "lang/main.php",
            "<?php $langCode = 'en'; function t($section, $key) { return $key; }\n",
        )
        config = {
            "CONFIG_DB_TBL_DALODICTIONARY": "dictionary",
            "COMMON_INCLUDES": str(common_includes),
            "OPERATORS_LIBRARY": str(operators / "library"),
            "OPERATORS_LANG": str(operators / "lang"),
            "OPERATORS_INCLUDE_MANAGEMENT": str(operators / "include/management"),
            "OPERATORS_INCLUDE_CONFIG": str(operators / "include/config"),
        }
        write(
            common_includes / "config_read.php",
            "<?php $configValues = json_decode("
            + json.dumps(json.dumps(config))
            + ", true, 512, JSON_THROW_ON_ERROR);\n",
        )
        write(
            common_includes / "validation.php",
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
            common_includes / "layout.php",
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
            common_includes / "db_open.php",
            """<?php
class DB { public static function isError($value) { return false; } }
class FixtureResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function fetchrow() { return count($this->rows) > 0 ? array_shift($this->rows) : null; }
}
class FixtureDB {
    private $statePath;
    public function __construct($statePath) { $this->statePath = $statePath; }
    public function escapeSimple($value) { return str_replace("'", "''", $value); }
    private function loadRows() { return json_decode(file_get_contents($this->statePath), true); }
    private function saveRows($rows) { file_put_contents($this->statePath, json_encode($rows, JSON_THROW_ON_ERROR)); }
    private function condition($sql, $column) {
        $pattern = "/`?" . preg_quote($column, "/") . "`?='((?:[^']|'')*)'/i";
        if (!preg_match($pattern, $sql, $matches)) throw new Exception("Missing SQL condition: " . $column);
        return str_replace("''", "'", $matches[1]);
    }
    public function query($sql) {
        $GLOBALS['fixture_queries'][] = $sql;
        $rows = $this->loadRows();
        if (str_starts_with($sql, 'SELECT DISTINCT(Vendor)')) {
            $attribute = $this->condition($sql, 'attribute');
            return new FixtureResult(array_values(array_map(
                fn($row) => [$row['vendor']],
                array_filter($rows, fn($row) => $row['attribute'] === $attribute)
            )));
        }
        if (str_starts_with($sql, 'INSERT INTO dictionary')) {
            $pattern = "/VALUES \\(0, '([^']*)', '([^']*)', '', '', '([^']*)', '([^']*)', '([^']*)', '([^']*)', '([^']*)'\\)/s";
            if (!preg_match($pattern, $sql, $matches)) throw new Exception('Unexpected INSERT: ' . $sql);
            $rows[] = [
                'type' => $matches[1], 'attribute' => $matches[2], 'value' => '', 'format' => '',
                'vendor' => $matches[3], 'recommendedOP' => $matches[4],
                'recommendedTable' => $matches[5], 'recommendedHelper' => $matches[6],
                'recommendedTooltip' => $matches[7],
            ];
            $this->saveRows($rows);
            return true;
        }
        $attribute = $this->condition($sql, 'attribute');
        $vendor = $this->condition($sql, 'vendor');
        $matching = array_values(array_filter(
            $rows,
            fn($row) => $row['attribute'] === $attribute && $row['vendor'] === $vendor
        ));
        if (str_starts_with($sql, 'SELECT COUNT(DISTINCT(id))')) {
            return new FixtureResult([[count($matching)]]);
        }
        if (str_starts_with(ltrim($sql), 'SELECT `type`')) {
            if (count($matching) === 0) return new FixtureResult([]);
            $row = $matching[0];
            return new FixtureResult([[
                $row['type'], $row['value'], $row['format'], $row['recommendedOP'],
                $row['recommendedTable'], $row['recommendedHelper'], $row['recommendedTooltip'],
            ]]);
        }
        throw new Exception('Unexpected SQL: ' . $sql);
    }
}
$dbSocket = new FixtureDB($GLOBALS['fixture_state_path']);
""",
        )
        write(common_includes / "db_close.php", "<?php // Synthetic DB has nothing to close.\n")
        write(
            operators / "include/management/actionMessages.php",
            "<?php if (isset($successMsg)) echo $successMsg; if (isset($failureMsg)) echo $failureMsg;\n",
        )
        write(operators / "include/config/logging.php", "<?php // Logging disabled in fixture.\n")

        state = base / "dictionary.json"
        state.write_text("[]")
        create_driver = base / "create.php"
        write(
            create_driver,
            f"""<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = json_decode({json.dumps(json.dumps({
    'csrf_token': 'fixture-token',
    'vendor': VENDOR,
    'attribute': ATTRIBUTE,
    'type': 'string',
    'RecommendedOP': ':=',
    'RecommendedTable': 'check',
    'RecommendedHelper': '',
    'RecommendedTooltip': 'fixture tooltip',
}))}, true, 512, JSON_THROW_ON_ERROR);
$GLOBALS['fixture_state_path'] = {json.dumps(str(state))};
$GLOBALS['fixture_queries'] = [];
chdir({json.dumps(str(operators))});
ob_start();
include {json.dumps(str(create_page))};
$html = ob_get_clean();
echo json_encode(['html' => $html, 'queries' => $GLOBALS['fixture_queries']], JSON_THROW_ON_ERROR);
""",
        )

        created = run_php(create_driver)
        rows = json.loads(state.read_text())
        assert rows == [{
            "type": "string",
            "attribute": ATTRIBUTE,
            "value": "",
            "format": "",
            "vendor": VENDOR,
            "recommendedOP": ":=",
            "recommendedTable": "check",
            "recommendedHelper": "",
            "recommendedTooltip": "fixture tooltip",
        }], rows
        assert len(created["queries"]) == 2, created["queries"]
        assert created["queries"][0].startswith("SELECT DISTINCT(Vendor)"), created["queries"]
        assert created["queries"][1].startswith("INSERT INTO dictionary"), created["queries"]

        attribute_html = "Fixture &lt;Attribute&gt; &amp; &quot;quoted&quot;"
        vendor_html = "Fixture &amp; &quot;Vendor&quot;"
        assert f"(attribute: {attribute_html}, vendor: {vendor_html})" in created["html"], created["html"]
        assert f">{attribute_html}</a>" in created["html"], created["html"]
        assert "<Attribute>" not in created["html"], created["html"]

        parser = LinkParser()
        parser.feed(created["html"])
        assert len(parser.links) == 1, parser.links
        href = parser.links[0]["href"]
        parsed_href = urlsplit(href)
        assert parsed_href.scheme == "" and parsed_href.netloc == "", href
        assert parsed_href.path == "mng-rad-attributes-edit.php", href
        assert parsed_href.fragment == "", href
        linked_edit_page = operators / parsed_href.path
        assert linked_edit_page.resolve() == edit_page.resolve(), linked_edit_page
        query = parse_qs(parsed_href.query, strict_parsing=True)
        assert query == {"vendor": [VENDOR], "attribute": [ATTRIBUTE]}, (href, query)

        edit_driver = base / "edit.php"
        edit_request = {"vendor": query["vendor"][0], "attribute": query["attribute"][0]}
        write(
            edit_driver,
            f"""<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_REQUEST = json_decode({json.dumps(json.dumps(edit_request))}, true, 512, JSON_THROW_ON_ERROR);
$GLOBALS['fixture_state_path'] = {json.dumps(str(state))};
$GLOBALS['fixture_queries'] = [];
chdir({json.dumps(str(operators))});
ob_start();
include {json.dumps(str(linked_edit_page))};
ob_end_clean();
echo json_encode([
    'exists' => $exists, 'vendor' => $vendor, 'attribute' => $attribute,
    'type' => $type ?? null, 'queries' => $GLOBALS['fixture_queries'],
], JSON_THROW_ON_ERROR);
""",
        )
        edited = run_php(edit_driver)
        assert edited["exists"] is True, edited
        assert edited["vendor"] == VENDOR and edited["attribute"] == ATTRIBUTE, edited
        assert edited["type"] == "string", edited
        assert len(edited["queries"]) == 2, edited["queries"]

    print("PASS: dictionary attribute POST persists escaped values and its edit link resolves them.")


if __name__ == "__main__":
    run()
