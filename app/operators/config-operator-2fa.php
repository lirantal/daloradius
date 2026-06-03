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
$pending_qr = !empty($pending_uri) ? dalo_totp_generate_qr_svg_data_uri($pending_uri) : '';
$csrf_token = dalo_csrf_token();

$title = "Two-factor authentication";
$help = "Configure TOTP two-factor authentication for your operator account. This is compatible with Google Authenticator and other RFC 6238 authenticator apps.";

print_html_prologue($title, $langCode);
print_title_and_help($title, $help);
include_once('include/management/actionMessages.php');
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">Two-factor authentication</h4>
                <p class="mb-1">Operator: <strong><?= htmlspecialchars($operator, ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p class="text-muted mb-0">Add an extra login step using an authenticator app.</p>
                <?php if ($totp_enabled && !empty($row['totp_confirmed_at'])): ?>
                    <p class="text-muted small mb-0">Enabled on <?= htmlspecialchars($row['totp_confirmed_at'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($totp_enabled): ?>
                    <span class="badge text-bg-success">Enabled</span>
                <?php else: ?>
                    <span class="badge text-bg-secondary">Disabled</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($generated_recovery_codes)): ?>
<div class="alert alert-warning">
    <h5>Recovery codes</h5>
    <p class="mb-2">Save these codes now. They will not be shown again. Each code can be used once if you lose access to your authenticator app.</p>
    <pre class="mb-0"><?php foreach ($generated_recovery_codes as $code) { echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "\n"; } ?></pre>
</div>
<?php endif; ?>

<?php if (!$totp_enabled && empty($pending_secret)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title">Protect your operator account</h4>
        <p class="card-text">Two-factor authentication requires a 6-digit code from your authenticator app after your password.</p>
        <form method="POST" action="config-operator-2fa.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="start_enable">
            <button type="submit" class="btn btn-primary">Enable two-factor authentication</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!$totp_enabled && !empty($pending_secret)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title">Set up your authenticator app</h4>
        <p class="text-muted">Complete these steps to link your operator account to an authenticator app.</p>

        <div class="row g-4 align-items-start">
            <div class="col-lg-5 text-center">
                <h5>1. Scan the QR code</h5>
                <?php if (!empty($pending_qr)): ?>
                    <img src="<?= htmlspecialchars($pending_qr, ENT_QUOTES, 'UTF-8') ?>" alt="TOTP setup QR code" class="img-fluid border rounded p-2 bg-white" style="max-width: 260px;">
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <h5>2. Or enter this secret manually</h5>
                <div class="input-group mb-3">
                    <span class="input-group-text">Secret</span>
                    <input type="text" class="form-control font-monospace" readonly value="<?= htmlspecialchars($pending_secret, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <details class="mb-3">
                    <summary class="text-muted">Advanced: show provisioning URI</summary>
                    <textarea class="form-control font-monospace mt-2" rows="3" readonly><?= htmlspecialchars($pending_uri, ENT_QUOTES, 'UTF-8') ?></textarea>
                </details>

                <h5>3. Confirm setup</h5>
                <div class="mb-3">
                    <label for="otp_code" class="form-label">Verification code</label>
                    <input type="text" class="form-control font-monospace" id="otp_code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456" form="confirm-totp-form" required>
                    <div class="form-text">Enter the 6-digit code shown in your authenticator app.</div>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="config-operator-2fa.php" id="confirm-totp-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="confirm_enable">
                        <button type="submit" class="btn btn-success">Confirm and enable</button>
                    </form>
                    <form method="POST" action="config-operator-2fa.php">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="cancel_enable">
                        <button type="submit" class="btn btn-outline-secondary">Cancel setup</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($totp_enabled): ?>
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title">Two-factor authentication is enabled</h4>
        <p class="card-text">Use recovery codes if you lose access to your authenticator app. Regenerating recovery codes invalidates any previous unused codes.</p>
        <div class="d-flex gap-2">
            <form method="POST" action="config-operator-2fa.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="regenerate_recovery">
                <button type="submit" class="btn btn-outline-primary">Regenerate recovery codes</button>
            </form>
            <form method="POST" action="config-operator-2fa.php" onsubmit="return confirm('Disable two-factor authentication for your operator account?');">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="disable">
                <button type="submit" class="btn btn-danger">Disable two-factor authentication</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include('include/config/logging.php');
print_footer_and_html_epilogue();
?>
