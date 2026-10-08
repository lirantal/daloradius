<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *********************************************************************************************************
 */

include_once("library/sessions.php");
if (!isset($configValues)) {
    include_once('../common/includes/config_read.php');
}
dalo_session_start();

if (array_key_exists('daloradius_logged_in', $_SESSION)
    && $_SESSION['daloradius_logged_in'] !== false) {
    header('Location: index.php');
    exit;
}

include("lang/main.php");

function dalo_operator_login_auth_settings(array $config)
{
    $localEnabled = !array_key_exists('CONFIG_OPERATOR_AUTH_LOCAL_ENABLED', $config)
        || !in_array(strtolower(trim((string) $config['CONFIG_OPERATOR_AUTH_LOCAL_ENABLED'])), array('', '0', 'false', 'no', 'off'), true);
    $ldapEnabled = array_key_exists('CONFIG_OPERATOR_AUTH_LDAP_ENABLED', $config)
        && !in_array(strtolower(trim((string) $config['CONFIG_OPERATOR_AUTH_LDAP_ENABLED'])), array('', '0', 'false', 'no', 'off'), true);
    $default = strtolower(trim((string) ($config['CONFIG_OPERATOR_AUTH_DEFAULT'] ?? 'local')));
    if (!in_array($default, array('local', 'ldap'), true)
        || ($default === 'local' && !$localEnabled)
        || ($default === 'ldap' && !$ldapEnabled)) {
        $default = $localEnabled ? 'local' : ($ldapEnabled ? 'ldap' : '');
    }
    return array(
        'local_enabled' => $localEnabled,
        'ldap_enabled' => $ldapEnabled,
        'show_source' => $localEnabled && $ldapEnabled,
        'default_source' => $default,
    );
}

$onlyDefaultLocation = !(array_key_exists('CONFIG_LOCATIONS', $configValues)
                        && is_array($configValues['CONFIG_LOCATIONS'])
                        && count($configValues['CONFIG_LOCATIONS']) > 0);
$authSettings = dalo_operator_login_auth_settings($configValues);
$localAuthEnabled = $authSettings['local_enabled'];
$ldapAuthEnabled = $authSettings['ldap_enabled'];
$showAuthSource = $authSettings['show_source'];
$configuredAuthSource = $authSettings['default_source'];

$dir = (strtolower($langCode) === 'ar') ? "rtl" : "ltr";
?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>" dir="<?= $dir ?>">
<head>
    <title>daloRADIUS :: Login</title>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="copyright" content="Liran Tal & Filippo Lauria">
    <meta name="robots" content="noindex">
    <link rel="apple-touch-icon" sizes="180x180" href="static/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="static/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="static/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="static/images/favicon/site.webmanifest">
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <style>
html, body { height: 100%; }
body { display: flex; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #f5f5f5; }
.form-login { max-width: 480px; padding: 15px; }
.form-login .form-floating:focus-within { z-index: 2; }
.form-login input[type="text"] { margin-bottom: -1px; border-bottom-right-radius: 0; border-bottom-left-radius: 0; }
.form-login input[type="password"] { margin-bottom: -1px; border-radius: 0; }
.form-login select { margin-bottom: 10px; border-top-left-radius: 0; border-top-right-radius: 0; }
    </style>
</head>
<body>
    <main class="form-login w-100 m-auto">
    <form action="dologin.php" method="POST">
    <img class="mb-4" src="static/images/daloradius_small.png" alt="daloRADIUS" width="135" height="41">
    <h1 class="h3 mb-3 fw-normal"><?= t('text','LoginRequired') ?></h1>

    <div class="form-floating">
    <input type="text" class="form-control" id="operator_user" name="operator_user" placeholder="<?= t('all','Username') ?>" required>
    <label for="operator_user"><?= t('all','Username') ?></label>
    </div>

    <div class="form-floating">
    <input type="password" class="form-control" id="operator_pass" name="operator_pass" placeholder="<?= t('all','Password') ?>" required>
    <label for="operator_pass"><?= t('all','Password') ?></label>
    </div>

<?php if ($showAuthSource): ?>
    <div class="form-floating">
        <select class="form-select" id="operator_auth_source" name="operator_auth_source" required>
            <option value="local" <?= $configuredAuthSource === 'local' ? 'selected' : '' ?>><?= t('all','LocalDatabase') ?></option>
            <option value="ldap" <?= $configuredAuthSource === 'ldap' ? 'selected' : '' ?>><?= t('all','LDAP') ?></option>
        </select>
        <label for="operator_auth_source"><?= t('all','AuthenticationProvider') ?></label>
    </div>
<?php endif; ?>

    <div class="form-floating">
        <select class="form-select" id="location" name="location" <?= ($onlyDefaultLocation) ? " disabled" : "" ?>>
<?php
        $defaultLocationFormat = '<option value="%s">%s</option>' . "\n";
        if ($onlyDefaultLocation) {
            printf($defaultLocationFormat, "default", t('all','Default'));
        } else {
            $locations = array_keys($configValues['CONFIG_LOCATIONS']);
            foreach ($locations as $location) {
                $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
                printf($defaultLocationFormat, $location, $location);
            }
        }
?>
        </select>
        <label for="location"><?= t('all','Location') ?></label>
    </div>

    <button class="w-100 btn btn-lg btn-primary" type="submit"><?= t('text','LoginPlease') ?></button>
    <small class="my-2 text-muted text-center d-block"><?= t('all','daloRADIUS') ?></small>
    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
    </form>

<?php
    if (isset($_SESSION['operator_login_error']) && $_SESSION['operator_login_error'] !== false) {
        $message = t('messages','loginerror');
        echo <<<EOF
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="error-toast" class="toast align-items-start text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">{$message}</div>
                <button type="button" class="btn-close m-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        </div>
        <script>
window.onload = function() {
    var errorToast = document.getElementById('error-toast');
    var toast = new bootstrap.Toast(errorToast); toast.show();
}
        </script>
EOF;
        unset($_SESSION['operator_login_error']);
    }
?>
    </main>
    <script src="static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
