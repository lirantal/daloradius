<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Miguel García <miguelvisgarcia@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
include('library/checklogin.php');
$operator = $_SESSION['operator_user'];
include('library/check_operator_perm.php');
include_once('lang/main.php');
include('../common/includes/validation.php');
include('../common/includes/layout.php');
require_once('library/acct_maintenance.php');
$logAction = $logDebugSQL = '';
$log = 'visited page: ';
$preview = null;
$notice = '';
$active = 'close';
$filter = ['action' => 'close', 'scope' => 'username', 'value' => ''];
include('../common/includes/db_open.php');
$dbSocket->setErrorHandling(PEAR_ERROR_RETURN);
// Bind confirmation to the operator and actual accounting backend, not only IDs.
$context = hash('sha256', serialize([$_SESSION['operator_id'], $mydbHost, $mydbPort,
                                   $mydbName, $configValues['CONFIG_DB_TBL_RADACCT']]));
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !is_string($_SESSION['csrf_token'] ?? null) ||
            !dalo_check_csrf_token($_POST['csrf_token'])) {
            throw new InvalidArgumentException('CSRF');
        }
        $filter = dalo_maintenance_filter($_POST);
        $active = $filter['action'];
        $step = $_POST['step'] ?? null;
        if ($step === 'preview') {
            unset($_SESSION['acct_maintenance_preview']);
            $preview = dalo_maintenance_preview($dbSocket, $configValues['CONFIG_DB_TBL_RADACCT'], $filter);
            $preview['context'] = $context;
            $_SESSION['acct_maintenance_preview'] = $preview;
            if (!$preview['rows']) {
                $notice = t('maintenance', 'empty');
            }
        } elseif ($step === 'confirm') {
            $stored = $_SESSION['acct_maintenance_preview'] ?? null;
            // Consume even failed confirmations; replays require a fresh preview.
            unset($_SESSION['acct_maintenance_preview']);
            if (!$stored || $stored['filter'] !== $filter || $stored['context'] !== $context ||
                !is_string($_POST['confirmation'] ?? null) ||
                !hash_equals($stored['token'], $_POST['confirmation'])) {
                throw new InvalidArgumentException('Confirmation');
            }
            $result = dalo_maintenance_apply($dbSocket, $configValues['CONFIG_DB_TBL_RADACCT'], $stored);
            $notice = sprintf(t('maintenance', 'result'), t('maintenance', $active),
                              $result['affected'], $result['skipped'], $result['failed']);
            $logAction = sprintf('Open-session maintenance action=%s scope=%s value=%s affected=%d skipped=%d failed=%d on page: ',
                                 $active, $filter['scope'], json_encode($filter['value']),
                                 $result['affected'], $result['skipped'], $result['failed']);
        } else {
            throw new InvalidArgumentException('Step');
        }
    } else {
        // GET is prefill only; returning/reloading invalidates an older preview.
        unset($_SESSION['acct_maintenance_preview']);
        if (isset($_GET['username'])) {
            $filter = dalo_maintenance_filter(['action' => 'close', 'scope' => 'username', 'value' => $_GET['username']]);
        }
    }
} catch (InvalidArgumentException $e) {
    unset($_SESSION['acct_maintenance_preview']);
    $failureMsg = t('maintenance', 'invalid');
    $logAction = 'Rejected open-session maintenance request on page: ';
} catch (RuntimeException $e) {
    unset($_SESSION['acct_maintenance_preview']);
    $failureMsg = t('maintenance', 'error');
}
include('../common/includes/db_close.php');
function maintenance_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function maintenance_text($key) {
    return maintenance_escape(t('maintenance', $key));
}
$title = t('maintenance', 'title');
print_html_prologue($title, $langCode);
print_title_and_help($title, t('maintenance', 'help'));
include_once('include/management/actionMessages.php');
if ($notice !== '') {
    echo '<div class="alert alert-info" role="status">' . maintenance_escape($notice) . '</div>';
}
$csrfToken = dalo_csrf_token();
?>
<div class="alert alert-warning py-2 mb-3" role="note">
<strong><?= maintenance_text('openWarning') ?></strong>
<button class="btn btn-link btn-sm p-0 ms-1 align-baseline" type="button" data-bs-toggle="collapse" data-bs-target="#maintenance-guidance" aria-expanded="false" aria-controls="maintenance-guidance"><?= maintenance_text('details') ?></button>
<div class="collapse mt-2" id="maintenance-guidance">
<p class="mb-1"><?= maintenance_text('openHelp') ?></p>
<p class="mb-0"><?= maintenance_text('dateHelp') ?></p>
</div>
</div>
<div class="card mb-4" id="maintenance-scope">
<div class="card-header bg-transparent py-3"><h2 class="h5 mb-0"><?= maintenance_text('selectTitle') ?></h2></div>
<div class="card-body">
<ul class="nav nav-tabs mb-3" role="tablist">
<?php foreach (['close', 'delete'] as $action): ?>
<li class="nav-item" role="presentation"><button type="button" class="nav-link <?= $active === $action ? 'active' : '' ?>" id="<?= $action ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $action ?>-panel" role="tab" aria-controls="<?= $action ?>-panel" aria-selected="<?= $active === $action ? 'true' : 'false' ?>"><?= maintenance_text($action) ?></button></li>
<?php endforeach; ?>
</ul>
<div class="tab-content">
<?php foreach (['close', 'delete'] as $action): ?>
<section id="<?= $action ?>-panel" class="tab-pane fade <?= $active === $action ? 'show active' : '' ?>" role="tabpanel" aria-labelledby="<?= $action ?>-tab" tabindex="0">
<p class="mb-2"><?= maintenance_text($action . 'Summary') ?></p>
<button class="btn btn-link btn-sm p-0 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $action ?>-details" aria-expanded="false" aria-controls="<?= $action ?>-details"><?= maintenance_text('details') ?></button>
<div class="collapse" id="<?= $action ?>-details"><div class="alert <?= $action === 'delete' ? 'alert-danger' : 'alert-secondary' ?> py-2 mb-3"><p class="mb-1"><?= maintenance_text($action . 'Help') ?></p><p class="mb-0"><?= maintenance_text('filterDetails') ?></p></div></div>
<form method="post" action="acct-maintenance-cleanup.php" class="maintenance-filter">
<input type="hidden" name="csrf_token" value="<?= maintenance_escape($csrfToken) ?>">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="step" value="preview">
<div class="row g-2 align-items-end">
<div class="col-md-4"><label class="form-label" for="<?= $action ?>-scope"><?= maintenance_text('scope') ?></label>
<select class="form-select" name="scope" id="<?= $action ?>-scope">
<?php foreach (['username', 'date'] as $scope): ?>
<option value="<?= $scope ?>" <?= $active === $action && $filter['scope'] === $scope ? 'selected' : '' ?>><?= maintenance_text($scope) ?></option>
<?php endforeach; ?>
</select></div>
<div class="col-md-5"><label class="form-label" for="<?= $action ?>-value"><?= maintenance_text('value') ?></label>
<input class="form-control" id="<?= $action ?>-value" name="value" maxlength="253" required value="<?= $active === $action ? maintenance_escape($filter['value']) : '' ?>" aria-describedby="<?= $action ?>-filter-help"></div>
<div class="col-md-3"><button class="btn btn-primary" type="submit"><?= maintenance_text('preview') ?></button></div>
</div>
<p id="<?= $action ?>-filter-help" class="form-text mb-0"><?= maintenance_text('filterHelp') ?></p>
</form>
</section>
<?php endforeach; ?>
</div>
</div>
</div>
<?php if ($preview && $preview['rows']):
$previewCount = count($preview['rows']);
$selectionKey = $active . ($previewCount === 1 ? 'SelectionOne' : 'SelectionMany');
$confirmKey = $active . ($previewCount === 1 ? 'ConfirmOne' : 'ConfirmMany');
?>
<section id="maintenance-preview" class="card mb-4">
<div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
<h2 class="h5 mb-0"><?= maintenance_text('previewTitle') ?></h2>
<span class="badge text-bg-secondary fs-6 fw-normal"><?= maintenance_escape(sprintf(t('maintenance', $selectionKey), $previewCount)) ?></span>
</div>
<div class="card-body">
<p class="mb-3"><?= maintenance_text($filter['scope']) ?>: <strong><?= maintenance_escape($filter['value']) ?></strong></p>
<div class="table-responsive"><table class="table table-sm table-striped align-middle mb-0"><thead><tr>
<?php foreach (['id', 'username', 'nas', 'start', 'activity', 'seconds', 'input', 'output'] as $column):
$numeric = in_array($column, ['id', 'seconds', 'input', 'output'], true);
?>
<th scope="col" class="fw-semibold <?= $numeric ? 'text-end' : 'text-start' ?>"><?= maintenance_text($column) ?></th>
<?php endforeach; ?>
</tr></thead><tbody>
<?php foreach ($preview['rows'] as $row): ?>
<tr><?php foreach (['radacctid', 'username', 'nasipaddress', 'acctstarttime', 'acctupdatetime', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets'] as $column):
$numeric = in_array($column, ['radacctid', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets'], true);
?>
<td class="<?= $numeric ? 'text-end' : 'text-start' ?>"><?= maintenance_escape($row[$column] ?? '—') ?></td>
<?php endforeach; ?></tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="mt-3">
<button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#preview-details" aria-expanded="false" aria-controls="preview-details"><?= maintenance_text('previewDetails') ?></button>
<div class="collapse" id="preview-details"><div class="text-muted small pt-2">
<p class="mb-1"><?= maintenance_escape(sprintf(t('maintenance', 'count'), $preview['total'], $previewCount, DALO_MAINTENANCE_LIMIT)) ?></p>
<p class="mb-1"><?= maintenance_text('concurrency') ?></p>
<p class="mb-0"><?= maintenance_text('activityHelp') ?></p>
</div></div>
</div>
</div>
<div class="card-footer bg-transparent py-3">
<form method="post" action="acct-maintenance-cleanup.php" id="maintenance-confirm" class="mb-0">
<input type="hidden" name="csrf_token" value="<?= maintenance_escape($csrfToken) ?>">
<input type="hidden" name="step" value="confirm">
<input type="hidden" name="confirmation" value="<?= maintenance_escape($preview['token']) ?>">
<?php foreach ($filter as $key => $value): ?>
<input type="hidden" name="<?= maintenance_escape($key) ?>" value="<?= maintenance_escape($value) ?>">
<?php endforeach; ?>
<button type="submit" class="btn btn-danger"><?= maintenance_escape(sprintf(t('maintenance', $confirmKey), $previewCount)) ?></button>
</form>
</div>
</section>
<script>
// Editing a visible filter or changing action hides the old confirmation.
// The server independently requires an exact match to its one-use snapshot.
const invalidateMaintenancePreview = () => {
    const preview = document.getElementById('maintenance-preview');
    if (preview) preview.remove();
};
// Autofill/form restoration can emit input/change without changing the
// approved filter (including in the inactive action's form). Compare values,
// not event occurrence, so those notifications cannot erase a valid preview.
const confirmation = document.getElementById('maintenance-confirm');
const approvedAction = confirmation.elements.namedItem('action').value;
document.querySelectorAll('.maintenance-filter').forEach(form => {
    if (form.elements.namedItem('action').value !== approvedAction) return;
    const invalidateChangedFilter = () => {
        if (['scope', 'value'].some(name =>
            form.elements.namedItem(name).value !== confirmation.elements.namedItem(name).value)) {
            invalidateMaintenancePreview();
        }
    };
    form.addEventListener('input', invalidateChangedFilter);
    form.addEventListener('change', invalidateChangedFilter);
});
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('show.bs.tab', invalidateMaintenancePreview);
});
</script>
<?php endif;
print_back_to_previous_page();
include('include/config/logging.php');
print_footer_and_html_epilogue();
