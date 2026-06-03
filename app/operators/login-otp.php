<?php
include_once("library/sessions.php");
include_once('../common/includes/config_read.php');
include_once('library/totp.php');

dalo_session_start();

if (empty($_SESSION['operator_2fa_pending']) || empty($_SESSION['operator_2fa_id']) || empty($_SESSION['operator_2fa_user'])) {
    header('Location: login.php');
    exit;
}

include("lang/main.php");

$failureMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || !dalo_check_csrf_token($_POST['csrf_token'])) {
        $failureMsg = 'CSRF token error';
    } else {
        $otp_code = (array_key_exists('otp_code', $_POST) && isset($_POST['otp_code'])) ? trim($_POST['otp_code']) : '';
        $_SESSION['operator_2fa_attempts'] = intval($_SESSION['operator_2fa_attempts'] ?? 0) + 1;

        include('../common/includes/db_open.php');

        $operator_id = intval($_SESSION['operator_2fa_id']);
        $operator_user = $dbSocket->escapeSimple($_SESSION['operator_2fa_user']);
        $sql = sprintf("SELECT id, username, totp_secret, totp_last_counter, totp_recovery_codes FROM %s WHERE id=%d AND username='%s' AND totp_enabled=1",
                       $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_id, $operator_user);
        $res = $dbSocket->query($sql);

        $authenticated = false;
        if ($res->numRows() === 1) {
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
            $matched_counter = dalo_totp_verify_once($row['totp_secret'], $otp_code, isset($row['totp_last_counter']) ? intval($row['totp_last_counter']) : null);

            if ($matched_counter !== null) {
                $sql = sprintf("UPDATE %s SET lastlogin='%s', totp_last_counter=%d WHERE id=%d",
                               $configValues['CONFIG_DB_TBL_DALOOPERATORS'], date('Y-m-d H:i:s'), $matched_counter, $operator_id);
                $dbSocket->query($sql);
                $authenticated = true;
            } else {
                list($recovery_ok, $new_recovery_codes) = dalo_totp_verify_recovery_code($row['totp_recovery_codes'], $otp_code);
                if ($recovery_ok) {
                    $sql = sprintf("UPDATE %s SET lastlogin='%s', totp_recovery_codes='%s' WHERE id=%d",
                                   $configValues['CONFIG_DB_TBL_DALOOPERATORS'], date('Y-m-d H:i:s'),
                                   $dbSocket->escapeSimple($new_recovery_codes), $operator_id);
                    $dbSocket->query($sql);
                    $authenticated = true;
                }
            }
        }

        include('../common/includes/db_close.php');

        if ($authenticated) {
            $_SESSION['daloradius_logged_in'] = true;
            $_SESSION['operator_user'] = $_SESSION['operator_2fa_user'];
            $_SESSION['operator_id'] = intval($_SESSION['operator_2fa_id']);
            unset($_SESSION['operator_2fa_pending'], $_SESSION['operator_2fa_id'], $_SESSION['operator_2fa_user'], $_SESSION['operator_2fa_attempts']);
            header('Location: index.php');
            exit;
        }

        if (intval($_SESSION['operator_2fa_attempts']) >= 5) {
            unset($_SESSION['operator_2fa_pending'], $_SESSION['operator_2fa_id'], $_SESSION['operator_2fa_user'], $_SESSION['operator_2fa_attempts']);
            $_SESSION['operator_login_error'] = true;
            header('Location: login.php');
            exit;
        }

        $failureMsg = 'Invalid verification code';
    }
}

$dir = (strtolower($langCode) === 'ar') ? "rtl" : "ltr";
?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>" dir="<?= $dir ?>">
<head>
    <title>daloRADIUS :: Two-factor authentication</title>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <style>
html, body { height: 100%; }
body { display: flex; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #f5f5f5; }
.form-login { max-width: 480px; padding: 15px; }
    </style>
</head>
<body>
    <main class="form-login w-100 m-auto">
    <form action="login-otp.php" method="POST">
    <img class="mb-4" src="static/images/daloradius_small.png" alt="daloRADIUS" width="135" height="41">
    <h1 class="h3 mb-3 fw-normal">Two-factor authentication</h1>
    <p class="text-muted">Enter the 6-digit code from your authenticator app, or a recovery code.</p>

    <?php if (!empty($failureMsg)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($failureMsg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="form-floating mb-3">
        <input type="text" class="form-control" id="otp_code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" placeholder="Verification code" required autofocus>
        <label for="otp_code">Verification code</label>
    </div>

    <button class="w-100 btn btn-lg btn-primary" type="submit">Verify</button>
    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
    </form>
    </main>
    <script src="static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
