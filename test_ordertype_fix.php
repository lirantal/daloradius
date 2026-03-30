<?php
/**
 * Proof-of-no-regression test for the orderType SQL injection fix.
 *
 * Compares OLD (broken) behavior vs NEW (fixed) behavior for all
 * relevant input categories to demonstrate:
 * 1. Legitimate inputs still work identically
 * 2. Malicious inputs are now correctly blocked
 *
 * Run: php test_ordertype_fix.php
 */

define("ORDER_TYPE_REGEX", '/^(de|a)sc$/');

// ---- OLD validation (BROKEN) ----
function validate_old($input) {
    $orderType = (isset($input) &&
                  preg_match(ORDER_TYPE_REGEX, $input) !== false)
               ? strtolower($input) : "asc";
    return $orderType;
}

// ---- NEW validation (FIXED) ----
function validate_new($input) {
    $orderType = (isset($input) &&
                  in_array(strtolower($input), array("asc", "desc")))
               ? strtolower($input) : "asc";
    return $orderType;
}

// ---- Test cases ----
$test_cases = [
    // [input, category, description]
    // --- Legitimate inputs (should be accepted by both) ---
    ["asc",   "LEGIT",     "lowercase asc"],
    ["desc",  "LEGIT",     "lowercase desc"],
    ["ASC",   "LEGIT",     "uppercase ASC"],
    ["DESC",  "LEGIT",     "uppercase DESC"],
    ["Asc",   "LEGIT",     "mixed case Asc"],
    ["Desc",  "LEGIT",     "mixed case Desc"],
    ["DeSc",  "LEGIT",     "mixed case DeSc"],

    // --- Malicious inputs (should be blocked) ---
    [",IF(1>0,updatexml(null,concat(0x0a,(select username from operators LIMIT 1)),0),0)", "MALICIOUS", "SQLi payload (username exfil)"],
    [",IF(1>0,updatexml(null,concat(0x0a,(select password from operators LIMIT 1)),0),0)", "MALICIOUS", "SQLi payload (password exfil)"],
    ["asc; DROP TABLE users; --",        "MALICIOUS", "SQL drop table"],
    ["asc UNION SELECT * FROM operators", "MALICIOUS", "UNION-based injection"],
    ["1 OR 1=1",                          "MALICIOUS", "Boolean-based injection"],
    ["",                                  "EDGE",      "empty string"],
    [" ",                                 "EDGE",      "space only"],
    ["ascending",                         "EDGE",      "similar word"],
    ["asc ",                              "EDGE",      "trailing space"],
    [" asc",                              "EDGE",      "leading space"],
    ["null",                              "EDGE",      "null string"],
];

// ---- Run tests ----
$pass = 0;
$fail = 0;
$total = count($test_cases);

echo "=" . str_repeat("=", 99) . "\n";
echo sprintf("  %-10s | %-30s | %-15s | %-15s | %s\n", "CATEGORY", "INPUT (truncated)", "OLD RESULT", "NEW RESULT", "STATUS");
echo "=" . str_repeat("=", 99) . "\n";

foreach ($test_cases as [$input, $category, $desc]) {
    $old_result = validate_old($input);
    $new_result = validate_new($input);

    // For LEGIT inputs: both should produce the same output (the lowercased input)
    // For MALICIOUS/EDGE: NEW should return "asc" (default), OLD was broken and returned the malicious value
    if ($category === "LEGIT") {
        // Both should accept and produce same result
        $expected_new = strtolower($input);
        $test_pass = ($new_result === $expected_new);
        $note = $test_pass ? "OK (accepted)" : "REGRESSION!";
    } else {
        // New should block (return default "asc")
        $expected_new = "asc";
        $test_pass = ($new_result === $expected_new);
        $old_was_broken = ($old_result !== "asc");
        $note = $test_pass
            ? ($old_was_broken ? "FIXED (was vulnerable)" : "OK (both block)")
            : "REGRESSION!";
    }

    if ($test_pass) { $pass++; } else { $fail++; }

    $display_input = strlen($input) > 28 ? substr($input, 0, 25) . "..." : $input;
    $display_input = str_replace(["\n", "\r"], "", $display_input);
    if ($display_input === "") $display_input = "(empty)";
    if ($display_input === " ") $display_input = "(space)";

    $status_icon = $test_pass ? "PASS" : "FAIL";

    echo sprintf("  %-10s | %-30s | %-15s | %-15s | %s %s\n",
        $category,
        $display_input,
        '"' . (strlen($old_result) > 12 ? substr($old_result, 0, 9) . '...' : $old_result) . '"',
        '"' . $new_result . '"',
        $status_icon,
        $note
    );
}

echo "=" . str_repeat("=", 99) . "\n";
echo sprintf("\n  Results: %d/%d passed, %d failed\n\n", $pass, $total, $fail);

if ($fail === 0) {
    echo "  ✅ CONCLUSION: Zero regressions. All legitimate inputs still work.\n";
    echo "     Malicious inputs are now correctly blocked (defaulted to 'asc').\n\n";
} else {
    echo "  ❌ REGRESSIONS DETECTED! Review failed tests.\n\n";
}

exit($fail > 0 ? 1 : 0);
