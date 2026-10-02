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

require_once '../common/includes/pdo_connection.php';
require_once 'library/operator_mfa_config.php';
$mfa_pdo = null;
$row = null;
$pending_context = array('id' => $operator_id, 'operator' => $operator,
                         'location' => $_SESSION['location_name'] ?? 'default');
// Do not reuse an enrollment started for another identity/backend or before context binding.
if (($_SESSION['operator_totp_pending_context'] ?? null) !== $pending_context ||
    !is_string($_SESSION['operator_totp_pending_secret'] ?? '') ||
    !preg_match('/\A[A-Z2-7]{16,128}\z/', $_SESSION['operator_totp_pending_secret'] ?? '') ||
    strlen($_SESSION['operator_totp_pending_secret'] ?? '') % 8 !== 0) {
    unset($_SESSION['operator_totp_pending_secret'], $_SESSION['operator_totp_pending_context']);
}
try {
    $mfa_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
    $row = dalo_operator_mfa_row($mfa_pdo, $configValues, $operator_id, $operator);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
        } else {
            $action = $_POST['action'] ?? '';
            if (!is_string($action) || !in_array($action, array('start_enable', 'cancel_enable',
                    'confirm_enable', 'disable', 'regenerate_recovery'), true)) {
                throw new InvalidArgumentException('Invalid MFA operation');
            }
            if ($action === 'start_enable') {
                if ((int) $row['totp_enabled'] === 1) {
                    throw new DomainException('Two-factor authentication is already enabled');
                }
                $_SESSION['operator_totp_pending_secret'] = dalo_totp_generate_secret();
                $_SESSION['operator_totp_pending_context'] = $pending_context;
                $successMsg = 'Scan or enter the new TOTP secret, then confirm with a verification code.';
            } elseif ($action === 'cancel_enable') {
                unset($_SESSION['operator_totp_pending_secret'], $_SESSION['operator_totp_pending_context']);
                $successMsg = 'Two-factor authentication setup cancelled.';
            } else {
                $secret = $_SESSION['operator_totp_pending_secret'] ?? '';
                $otp = $_POST['otp_code'] ?? '';
                if ($action === 'confirm_enable' && !is_string($otp)) {
                    throw new DomainException('Invalid verification code');
                }
                $result = dalo_operator_mfa_apply($mfa_pdo, $configValues, $operator_id,
                                                 $operator, $action, $secret,
                                                 is_string($otp) ? trim($otp) : '');
                $row = $result['row'];
                $generated_recovery_codes = $result['codes'];
                if ($action === 'confirm_enable' || $action === 'disable') {
                    unset($_SESSION['operator_totp_pending_secret'], $_SESSION['operator_totp_pending_context']);
                }
                $messages = array(
                    'confirm_enable' => 'Two-factor authentication has been enabled. Save these recovery codes now; they will not be shown again.',
                    'disable' => 'Two-factor authentication has been disabled.',
                    'regenerate_recovery' => 'New recovery codes generated. Save them now; they will not be shown again.'
                );
                $successMsg = $messages[$action];
            }
        }
    }
} catch (DomainException $error) {
    $failureMsg = $error->getMessage(); // Only fixed, non-sensitive domain messages.
} catch (Throwable $error) {
    $failureMsg = 'Unable to update two-factor authentication.';
    error_log('Operator MFA configuration failed: ' . get_class($error));
} finally {
    $mfa_pdo = null;
}
$totp_enabled = is_array($row) && intval($row['totp_enabled']) === 1;
$pending_secret = is_array($row) && !$totp_enabled ? ($_SESSION['operator_totp_pending_secret'] ?? '') : '';
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
