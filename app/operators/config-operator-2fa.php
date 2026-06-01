<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Operator two-factor authentication management.
 *********************************************************************************************************
 */

include("library/checklogin.php");
$operator = $_SESSION['operator_user'];
$operator_id = intval($_SESSION['operator_id']);

include('library/check_operator_perm.php');
include_once('../common/includes/config_read.php');
include_once("lang/main.php");
include("../common/includes/layout.php");
include_once('library/totp.php');

$log = "visited page: ";
$logAction = "";
$logDebugSQL = "";
$generated_recovery_codes = array();

include('../common/includes/db_open.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || !dalo_check_csrf_token($_POST['csrf_token'])) {
        $failureMsg = "CSRF token error";
        $logAction .= "$failureMsg on page: ";
    } else {
        $action = (array_key_exists('action', $_POST) && isset($_POST['action'])) ? $_POST['action'] : '';

        if ($action === 'start_enable') {
            $_SESSION['operator_totp_pending_secret'] = dalo_totp_generate_secret();
            $successMsg = "Scan or enter the new TOTP secret, then confirm with a verification code.";
        } elseif ($action === 'cancel_enable') {
            unset($_SESSION['operator_totp_pending_secret']);
            $successMsg = "Two-factor authentication setup cancelled.";
        } elseif ($action === 'confirm_enable') {
            $pending_secret = $_SESSION['operator_totp_pending_secret'] ?? '';
            $otp_code = (array_key_exists('otp_code', $_POST) && isset($_POST['otp_code'])) ? trim($_POST['otp_code']) : '';

            if (empty($pending_secret) || !dalo_totp_verify($pending_secret, $otp_code)) {
                $failureMsg = "Invalid verification code";
            } else {
                $generated_recovery_codes = dalo_totp_generate_recovery_codes();
                $recovery_hashes = dalo_totp_hash_recovery_codes($generated_recovery_codes);
                $sql = sprintf("UPDATE %s SET totp_enabled=1, totp_secret='%s', totp_last_counter=NULL, totp_confirmed_at='%s', totp_recovery_codes='%s' WHERE id=%d",
                               $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $dbSocket->escapeSimple($pending_secret),
                               date('Y-m-d H:i:s'), $dbSocket->escapeSimple($recovery_hashes), $operator_id);
                $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                unset($_SESSION['operator_totp_pending_secret']);
                $successMsg = "Two-factor authentication has been enabled. Save these recovery codes now; they will not be shown again.";
            }
        } elseif ($action === 'disable') {
            $sql = sprintf("UPDATE %s SET totp_enabled=0, totp_secret=NULL, totp_last_counter=NULL, totp_confirmed_at=NULL, totp_recovery_codes=NULL WHERE id=%d",
                           $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_id);
            $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            unset($_SESSION['operator_totp_pending_secret']);
            $successMsg = "Two-factor authentication has been disabled.";
        } elseif ($action === 'regenerate_recovery') {
            $generated_recovery_codes = dalo_totp_generate_recovery_codes();
            $recovery_hashes = dalo_totp_hash_recovery_codes($generated_recovery_codes);
            $sql = sprintf("UPDATE %s SET totp_recovery_codes='%s' WHERE id=%d AND totp_enabled=1",
                           $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $dbSocket->escapeSimple($recovery_hashes), $operator_id);
            $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            $successMsg = "New recovery codes generated. Save them now; they will not be shown again.";
        }
    }
}

$sql = sprintf("SELECT username, totp_enabled, totp_secret, totp_confirmed_at, totp_recovery_codes FROM %s WHERE id=%d",
               $configValues['CONFIG_DB_TBL_DALOOPERATORS'], $operator_id);
$res = $dbSocket->query($sql);
$logDebugSQL .= "$sql;\n";
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

include('../common/includes/db_close.php');

$totp_enabled = is_array($row) && intval($row['totp_enabled']) === 1;
$pending_secret = $_SESSION['operator_totp_pending_secret'] ?? '';
$pending_uri = !empty($pending_secret) ? dalo_totp_generate_uri($pending_secret, $operator) : '';

$title = "Two-factor authentication";
$help = "Configure TOTP two-factor authentication for your operator account. This is compatible with Google Authenticator and other RFC 6238 authenticator apps.";

print_html_prologue($title, $langCode);
print_title_and_help($title, $help);
include_once('include/management/actionMessages.php');
?>

<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title">Operator account</h4>
        <p><strong><?= htmlspecialchars($operator, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p>Status:
            <?php if ($totp_enabled): ?>
                <span class="badge text-bg-success">Enabled</span>
            <?php else: ?>
                <span class="badge text-bg-secondary">Disabled</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($generated_recovery_codes)): ?>
<div class="alert alert-warning">
    <h5>Recovery codes</h5>
    <p>Save these recovery codes now. Each code can be used once if you lose access to your authenticator app.</p>
    <pre class="mb-0"><?php foreach ($generated_recovery_codes as $code) { echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "\n"; } ?></pre>
</div>
<?php endif; ?>

<?php if (!$totp_enabled && empty($pending_secret)): ?>
<form method="POST" action="config-operator-2fa.php">
    <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
    <input type="hidden" name="action" value="start_enable">
    <button type="submit" class="btn btn-primary">Enable two-factor authentication</button>
</form>
<?php endif; ?>

<?php if (!$totp_enabled && !empty($pending_secret)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title">Set up authenticator app</h4>
        <p>Add a new TOTP account in your authenticator app using this secret:</p>
        <div class="input-group mb-3">
            <span class="input-group-text">Secret</span>
            <input type="text" class="form-control font-monospace" readonly value="<?= htmlspecialchars($pending_secret, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <p class="text-muted">URI for QR-code tools, if you choose to generate one locally:</p>
        <textarea class="form-control font-monospace" rows="3" readonly><?= htmlspecialchars($pending_uri, ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
</div>

<form method="POST" action="config-operator-2fa.php" class="mb-3">
    <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
    <input type="hidden" name="action" value="confirm_enable">
    <div class="mb-3">
        <label for="otp_code" class="form-label">Verification code</label>
        <input type="text" class="form-control" id="otp_code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" required>
    </div>
    <button type="submit" class="btn btn-success">Confirm and enable</button>
</form>
<form method="POST" action="config-operator-2fa.php">
    <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
    <input type="hidden" name="action" value="cancel_enable">
    <button type="submit" class="btn btn-outline-secondary">Cancel setup</button>
</form>
<?php endif; ?>

<?php if ($totp_enabled): ?>
<div class="d-flex gap-2">
    <form method="POST" action="config-operator-2fa.php">
        <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
        <input type="hidden" name="action" value="regenerate_recovery">
        <button type="submit" class="btn btn-outline-primary">Regenerate recovery codes</button>
    </form>
    <form method="POST" action="config-operator-2fa.php" onsubmit="return confirm('Disable two-factor authentication for your operator account?');">
        <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">
        <input type="hidden" name="action" value="disable">
        <button type="submit" class="btn btn-danger">Disable two-factor authentication</button>
    </form>
</div>
<?php endif; ?>

<?php
include('include/config/logging.php');
print_footer_and_html_epilogue();
?>
