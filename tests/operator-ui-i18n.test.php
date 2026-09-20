<?php
$failures = 0;
function check_ui($label, $condition) {
    global $failures;
    if ($condition) { echo "ok   - $label\n"; } else { echo "FAIL - $label\n"; $failures++; }
}

$root = dirname(__DIR__);
$list = file_get_contents($root . '/app/operators/config-operators-list.php');
$login = file_get_contents($root . '/app/operators/login.php');
$new = file_get_contents($root . '/app/operators/config-operators-new.php');
$dologin = file_get_contents($root . '/app/operators/dologin.php');
$identity = file_get_contents($root . '/app/operators/include/management/operator_identity.php');
$english = file_get_contents($root . '/app/operators/lang/en.php');

check_ui('operator list translates LDAP column captions',
    strpos($list, "t('all','AuthenticationSource')") !== false
    && strpos($list, "t('all','ExternalIdentity')") !== false);
check_ui('operator list translates source and link status values',
    strpos($list, 'operator_auth_source_label($auth_source)') !== false
    && strpos($list, "t('all','Linked')") !== false
    && strpos($list, "t('all','NotLinked')") !== false);
check_ui('operator source label helper is used and translation-aware',
    strpos($list, 'operator_identity.php') !== false
    && strpos($identity, "function_exists('t')") !== false);
check_ui('login translates provider controls',
    strpos($login, "t('all','LocalDatabase')") !== false
    && strpos($login, "t('all','LDAP')") !== false
    && strpos($login, "t('all','AuthenticationProvider')") !== false);
check_ui('login translates location labels',
    strpos($login, "t('all','Default')") !== false
    && strpos($login, "t('all','Location')") !== false);
$postMarker = strpos($new, "if (\$_SERVER['REQUEST_METHOD'] === 'POST')");
check_ui('new operator GET initializes identity fields before POST handling',
    strpos($new, "\$operator_auth_source = 'local';") < $postMarker
    && strpos($new, '\$operator_external_id = null;') < $postMarker);
check_ui('removed dead login default helper',
    strpos($dologin, 'function dalo_operator_auth_default_source') === false);
check_ui('English catalog contains the new UI keys',
    strpos($english, "\$l['all']['AuthenticationSource']") !== false
    && strpos($english, "\$l['all']['NotLinked']") !== false
    && strpos($english, "\$l['all']['LocalDatabase']") !== false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : "$failures FAILURE(S)");
exit($failures === 0 ? 0 : 1);
