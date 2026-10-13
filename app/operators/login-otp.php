<?php
include_once("library/sessions.php");
include_once('../common/includes/config_read.php');
include_once('library/totp.php');

function dalo_operator_auth_otp_prepare_session(array &$session)
{
    if (!empty($session['operator_2fa_pending']) && empty($session['operator_2fa_auth_source'])) {
        $session['operator_2fa_auth_source'] = 'local';
    }
}

function dalo_operator_auth_otp_identity_matches($authSource, array $row, array $session)
{
    if ($authSource !== 'ldap') {
        return true;
    }
    return array_key_exists('operator_2fa_external_id', $session)
        && dalo_operator_auth_external_id_matches(
            isset($row['external_id']) ? $row['external_id'] : null,
            $session['operator_2fa_external_id']
        );
}

function dalo_operator_auth_otp_finalize_session(array &$session)
{
    $finalAuthSource = $session['operator_2fa_auth_source'];
    $session['daloradius_logged_in'] = true;
    $session['operator_user'] = $session['operator_2fa_user'];
    $session['operator_id'] = intval($session['operator_2fa_id']);
    $session['operator_auth_source'] = $finalAuthSource;
    unset($session['operator_2fa_pending'], $session['operator_2fa_id'], $session['operator_2fa_user'], $session['operator_2fa_auth_source'], $session['operator_2fa_external_id'], $session['operator_2fa_attempts']);
}

/** Consume one TOTP counter or recovery code on the same locked PDO row. */
function dalo_operator_auth_otp_consume(PDO $pdo, $table, array $session, $code)
{
    if (!$pdo->beginTransaction()) {
        return false;
    }
    try {
        $id = (int) $session['operator_2fa_id'];
        $username = $session['operator_2fa_user'];
        $source = $session['operator_2fa_auth_source'];
        $select = "SELECT id, username, auth_source, external_id, totp_secret, "
                . "totp_last_counter, totp_recovery_codes FROM $table "
                . "WHERE id=? AND username=? AND totp_enabled=1 FOR UPDATE";
        try {
            $stmt = $pdo->prepare($select);
            $stmt->execute(array($id, $username));
        } catch (PDOException $exception) {
            // Pre-provider-migration local sessions could already be pending MFA.
            // Only a genuinely missing column permits the legacy local projection.
            if ($source !== 'local' || (int) ($exception->errorInfo[1] ?? 0) !== 1054) {
                throw $exception;
            }
            $stmt = $pdo->prepare("SELECT id, username, totp_secret, totp_last_counter, "
                . "totp_recovery_codes FROM $table WHERE id=? AND username=? "
                . "AND totp_enabled=1 FOR UPDATE");
            $stmt->execute(array($id, $username));
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) !== 1 || !hash_equals($username, (string) $rows[0]['username'])) {
            $pdo->rollBack();
            return false;
        }
        $row = $rows[0];
        $rowSource = isset($row['auth_source']) ? (string) $row['auth_source'] : 'local';
        if (!hash_equals($source, $rowSource)
            || !dalo_operator_auth_otp_identity_matches($source, $row, $session)) {
            $pdo->rollBack();
            return false;
        }
        $counter = dalo_totp_verify_once(
            $row['totp_secret'], $code,
            isset($row['totp_last_counter']) ? (int) $row['totp_last_counter'] : null
        );
        $now = date('Y-m-d H:i:s');
        if ($counter !== null) {
            $update = $pdo->prepare("UPDATE $table SET lastlogin=?, totp_last_counter=? WHERE id=?");
            $updated = $update->execute(array($now, $counter, $id)) && $update->rowCount() === 1;
        } else {
            list($recoveryOk, $remaining) = dalo_totp_verify_recovery_code($row['totp_recovery_codes'], $code);
            if (!$recoveryOk) {
                $pdo->rollBack();
                return false;
            }
            $update = $pdo->prepare("UPDATE $table SET lastlogin=?, totp_recovery_codes=? WHERE id=?");
            $updated = $update->execute(array($now, $remaining, $id)) && $update->rowCount() === 1;
        }
        if (!$updated) {
            $pdo->rollBack();
            return false;
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('OTP commit failed');
        }
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

dalo_session_start();

/* Pending sessions created before provider-aware authentication were local. */
dalo_operator_auth_otp_prepare_session($_SESSION);

if (empty($_SESSION['operator_2fa_pending']) || empty($_SESSION['operator_2fa_id'])
    || empty($_SESSION['operator_2fa_user']) || empty($_SESSION['operator_2fa_auth_source'])
    || !in_array($_SESSION['operator_2fa_auth_source'], array('local', 'ldap'), true)
    || ($_SESSION['operator_2fa_auth_source'] === 'ldap'
        && (!array_key_exists('operator_2fa_external_id', $_SESSION)
            || !is_string($_SESSION['operator_2fa_external_id'])
            || $_SESSION['operator_2fa_external_id'] === ''))) {
    header('Location: login.php');
    exit;
}

include("lang/main.php");
$failureMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !dalo_check_csrf_token($_POST['csrf_token'])) {
        $failureMsg = 'CSRF token error';
    } else {
        $otpCode = isset($_POST['otp_code']) && is_string($_POST['otp_code'])
            ? trim($_POST['otp_code']) : '';
        $_SESSION['operator_2fa_attempts'] = (int) ($_SESSION['operator_2fa_attempts'] ?? 0) + 1;
        $authenticated = false;
        try {
            require_once __DIR__ . '/../common/includes/pdo_connection.php';
            require_once __DIR__ . '/library/operator_create.php';
            $table = dalo_operator_create_table($configValues, 'CONFIG_DB_TBL_DALOOPERATORS');
            $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
            dalo_operator_create_innodb($pdo, array($table));
            if ($otpCode !== '' && strlen($otpCode) <= 128) {
                $authenticated = dalo_operator_auth_otp_consume($pdo, $table, $_SESSION, $otpCode);
            }
        } catch (Throwable $exception) {
            // Neither the response nor the log may contain bound OTP/recovery data.
            error_log('Operator OTP failure: ' . get_class($exception));
        } finally {
            $pdo = null;
        }

        if ($authenticated) {
            session_regenerate_id(true);
            dalo_operator_auth_otp_finalize_session($_SESSION);
            header('Location: index.php');
            exit;
        }
        if ((int) $_SESSION['operator_2fa_attempts'] >= 5) {
            unset($_SESSION['operator_2fa_pending'], $_SESSION['operator_2fa_id'],
                  $_SESSION['operator_2fa_user'], $_SESSION['operator_2fa_auth_source'],
                  $_SESSION['operator_2fa_external_id'], $_SESSION['operator_2fa_attempts']);
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
