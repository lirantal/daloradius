<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *********************************************************************************************************
 * Preview and add NAS/client entries from a versioned JSON backup.
 *********************************************************************************************************
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
$operator = $_SESSION['operator_user'];

// This page uses the same permission as the NAS list page.
$operator_perm_file = 'mng_rad_nas_list';
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasImportExport.php' ]);

$log = 'visited page: ';
$logAction = '';
$logDebugSQL = '';
$previewRows = array();
$resultRows = array();
$previewToken = '';
$readyCount = 0;
$skippedCount = 0;
$invalidCount = 0;

function nas_import_name_key($nasname) {
    $nasname = (string)$nasname;
    return function_exists('mb_strtolower') ? mb_strtolower($nasname, 'UTF-8') : strtolower($nasname);
}

function nas_import_existing_names($dbSocket, $table) {
    $sql = sprintf('SELECT nasname FROM %s', $table);
    $res = $dbSocket->query($sql);
    if (DB::isError($res)) {
        return false;
    }

    $names = array();
    while ($row = $res->fetchRow()) {
        $names[nas_import_name_key($row[0])] = true;
    }
    return $names;
}

function nas_import_badge($status) {
    switch ($status) {
        case 'ready':
            return '<span class="badge text-bg-success">Ready to import</span>';
        case 'imported':
            return '<span class="badge text-bg-success">Imported</span>';
        case 'skipped':
            return '<span class="badge text-bg-warning">Skipped</span>';
        default:
            return '<span class="badge text-bg-danger">Invalid</span>';
    }
}

function nas_import_render_rows($rows, $table_id) {
    if (count($rows) === 0) {
        return;
    }

    printf(
        '<div class="row g-2 mb-2"><div class="col-12 col-md-8"><input type="search" class="form-control nas-import-search" data-table="%s" placeholder="Filter by NAS name or short name"></div>',
        htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8')
    );
    printf(
        '<div class="col-12 col-md-4"><select class="form-select nas-import-status" data-table="%s"><option value="">All statuses</option><option value="ready">Ready</option><option value="imported">Imported</option><option value="skipped">Skipped</option><option value="invalid">Invalid</option></select></div></div>',
        htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8')
    );

    echo '<div class="table-responsive border rounded nas-import-table-wrap">';
    printf('<table id="%s" class="table table-striped table-hover table-sm align-middle mb-0">', htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8'));
    echo '<thead class="table-light"><tr><th>#</th><th>NAS name</th><th>Short name</th><th>Type</th><th>Status</th><th>Information</th></tr></thead><tbody>';

    foreach ($rows as $row) {
        $data = $row['data'];
        $status = $row['status'];
        $information = $row['information'] ?? '';
        printf(
            '<tr data-status="%s"><td>%d</td><td><code>%s</code></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
            htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
            intval($row['row_number']),
            htmlspecialchars((string)($data['nasname'] ?? ''), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string)($data['shortname'] ?? ''), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string)($data['type'] ?? ''), ENT_QUOTES, 'UTF-8'),
            nas_import_badge($status),
            htmlspecialchars($information, ENT_QUOTES, 'UTF-8')
        );
    }

    echo '</tbody></table></div>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['nas_import_preview']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfValid = isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token']);
    $action = $_POST['nas_import_action'] ?? '';

    if (!$csrfValid) {
        $failureMsg = 'CSRF token error';
        $logAction .= 'NAS import rejected due to CSRF validation failure on page: ';
    } elseif ($action === 'preview') {
        unset($_SESSION['nas_import_preview']);

        if (!isset($_FILES['nas_backup']) || !is_array($_FILES['nas_backup'])) {
            $failureMsg = 'Select a daloRADIUS NAS JSON backup file';
        } elseif ($_FILES['nas_backup']['error'] !== UPLOAD_ERR_OK) {
            $failureMsg = sprintf('NAS backup upload failed with error code %d', intval($_FILES['nas_backup']['error']));
        } elseif (intval($_FILES['nas_backup']['size']) <= 0 || intval($_FILES['nas_backup']['size']) > NAS_BACKUP_MAX_BYTES) {
            $failureMsg = sprintf('NAS backup must be between 1 byte and %d MiB', intval(NAS_BACKUP_MAX_BYTES / 1048576));
        } elseif (!is_uploaded_file($_FILES['nas_backup']['tmp_name'])) {
            $failureMsg = 'The NAS backup upload could not be verified';
        } else {
            $contents = file_get_contents($_FILES['nas_backup']['tmp_name']);
            $parsed = nas_backup_parse_document($contents);

            if (count($parsed['errors']) > 0) {
                $failureMsg = implode('; ', $parsed['errors']);
            } else {
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
                $existingNames = nas_import_existing_names($dbSocket, $configValues['CONFIG_DB_TBL_RADNAS']);
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

                if ($existingNames === false) {
                    $failureMsg = 'Unable to read the current NAS list';
                } else {
                    $seenNames = array();
                    $readyEntries = array();
                    $excludedRows = array();

                    foreach ($parsed['rows'] as $row) {
                        $data = $row['data'];
                        $nasname = (string)($data['nasname'] ?? '');
                        $nasnameKey = nas_import_name_key($nasname);
                        $status = 'ready';
                        $information = 'New NAS name';

                        if (count($row['errors']) > 0) {
                            $status = 'invalid';
                            $information = implode('; ', $row['errors']);
                            $invalidCount++;
                        } elseif (isset($seenNames[$nasnameKey])) {
                            $status = 'skipped';
                            $information = 'Duplicate NAS name in the imported file';
                            $skippedCount++;
                        } elseif (isset($existingNames[$nasnameKey])) {
                            $status = 'skipped';
                            $information = 'NAS name already exists in daloRADIUS';
                            $skippedCount++;
                        } else {
                            $readyEntries[] = array(
                                'row_number' => $row['row_number'],
                                'data' => $data,
                            );
                            $readyCount++;
                        }

                        if ($nasname !== '' && count($row['errors']) === 0) {
                            $seenNames[$nasnameKey] = true;
                        }

                        $row['status'] = $status;
                        $row['information'] = $information;
                        $previewRows[] = $row;
                        if ($status !== 'ready') {
                            $excludedRows[] = $row;
                        }
                    }

                    $previewToken = bin2hex(random_bytes(16));
                    $_SESSION['nas_import_preview'] = array(
                        'token' => $previewToken,
                        'created_at' => time(),
                        'entries' => $readyEntries,
                        'excluded_rows' => $excludedRows,
                    );

                    $logAction .= sprintf(
                        'Previewed NAS import with %d ready, %d skipped and %d invalid entries on page: ',
                        $readyCount,
                        $skippedCount,
                        $invalidCount
                    );
                }
            }
        }
    } elseif ($action === 'confirm') {
        $preview = $_SESSION['nas_import_preview'] ?? null;
        $submittedToken = $_POST['preview_token'] ?? '';

        if (!is_array($preview) || !hash_equals((string)($preview['token'] ?? ''), (string)$submittedToken)) {
            $failureMsg = 'The NAS import preview is missing or no longer valid';
        } elseif (time() - intval($preview['created_at'] ?? 0) > 1800) {
            unset($_SESSION['nas_import_preview']);
            $failureMsg = 'The NAS import preview has expired; upload the backup again';
        } else {
            $entries = $preview['entries'] ?? array();
            $excludedRows = $preview['excluded_rows'] ?? array();
            include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
            $dbSocket->setErrorHandling(PEAR_ERROR_RETURN);

            $importLock = $dbSocket->getOne("SELECT GET_LOCK('daloradius_nas_import', 30)");
            $importLockAcquired = !DB::isError($importLock) && intval($importLock) === 1;
            $transaction = $importLockAcquired ? $dbSocket->query('START TRANSACTION') : false;
            $importError = !$importLockAcquired || DB::isError($transaction);
            $insertedRows = array();
            $skippedRows = array();

            $insertSql = sprintf(
                'INSERT INTO %s (nasname, shortname, type, ports, secret, server, community, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                $configValues['CONFIG_DB_TBL_RADNAS']
            );
            $prepared = $dbSocket->prepare($insertSql);
            if (DB::isError($prepared)) {
                $importError = true;
            }

            foreach ($entries as $index => $candidate) {
                if ($importError) {
                    break;
                }

                $entry = $candidate['data'];
                $rowNumber = intval($candidate['row_number']);
                $existsSql = sprintf(
                    "SELECT COUNT(id) FROM %s WHERE LOWER(nasname)=LOWER('%s')",
                    $configValues['CONFIG_DB_TBL_RADNAS'],
                    $dbSocket->escapeSimple($entry['nasname'])
                );
                $exists = $dbSocket->getOne($existsSql);

                if (DB::isError($exists)) {
                    $importError = true;
                    break;
                }

                if (intval($exists) > 0) {
                    $skippedRows[] = array(
                        'row_number' => $rowNumber,
                        'data' => $entry,
                        'status' => 'skipped',
                        'information' => 'NAS name was added after the preview and already exists',
                    );
                    continue;
                }

                $res = $dbSocket->execute($prepared, array(
                    $entry['nasname'],
                    $entry['shortname'],
                    $entry['type'],
                    $entry['ports'],
                    $entry['secret'],
                    $entry['server'],
                    $entry['community'],
                    $entry['description'],
                ));

                if (nas_import_is_duplicate_error($res)) {
                    $skippedRows[] = array(
                        'row_number' => $rowNumber,
                        'data' => $entry,
                        'status' => 'skipped',
                        'information' => 'NAS name was added after the preview and already exists',
                    );
                    continue;
                }

                if (DB::isError($res)) {
                    $importError = true;
                    break;
                }

                $insertedRows[] = array(
                    'row_number' => $rowNumber,
                    'data' => $entry,
                    'status' => 'imported',
                    'information' => 'NAS added successfully',
                );
            }

            if ($importError) {
                $dbSocket->query('ROLLBACK');
                $failureMsg = 'The NAS import failed and all new rows were rolled back';
                foreach ($entries as $candidate) {
                    $resultRows[] = array(
                        'row_number' => intval($candidate['row_number']),
                        'data' => $candidate['data'],
                        'status' => 'invalid',
                        'information' => 'Not imported because the transaction was rolled back',
                    );
                }
                $logAction .= 'NAS import failed and was rolled back on page: ';
            } else {
                $commit = $dbSocket->query('COMMIT');
                if (DB::isError($commit)) {
                    $dbSocket->query('ROLLBACK');
                    $failureMsg = 'The NAS import could not be committed';
                    $logAction .= 'NAS import commit failed on page: ';
                } else {
                    $resultRows = array_merge($insertedRows, $skippedRows, $excludedRows);
                    usort($resultRows, function ($a, $b) {
                        return intval($a['row_number']) <=> intval($b['row_number']);
                    });
                    $previewSkippedCount = 0;
                    $previewInvalidCount = 0;
                    foreach ($excludedRows as $excludedRow) {
                        if (($excludedRow['status'] ?? '') === 'skipped') {
                            $previewSkippedCount++;
                        } elseif (($excludedRow['status'] ?? '') === 'invalid') {
                            $previewInvalidCount++;
                        }
                    }
                    $totalSkipped = count($skippedRows) + $previewSkippedCount;
                    $successMsg = sprintf(
                        'Imported %d NAS entr%s; skipped %d duplicate or existing entr%s; rejected %d invalid entr%s. Restart or reload FreeRADIUS for the changes to take effect.',
                        count($insertedRows),
                        count($insertedRows) === 1 ? 'y' : 'ies',
                        $totalSkipped,
                        $totalSkipped === 1 ? 'y' : 'ies',
                        $previewInvalidCount,
                        $previewInvalidCount === 1 ? 'y' : 'ies'
                    );
                    $logAction .= sprintf(
                        'Imported %d NAS entries, skipped %d duplicate or existing entries and rejected %d invalid entries on page: ',
                        count($insertedRows),
                        $totalSkipped,
                        $previewInvalidCount
                    );
                }
            }

            if (!DB::isError($prepared)) {
                $dbSocket->freePrepared($prepared);
            }
            if ($importLockAcquired) {
                $dbSocket->query("SELECT RELEASE_LOCK('daloradius_nas_import')");
            }
            $dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');
            include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
            unset($_SESSION['nas_import_preview']);
        }
    } else {
        $failureMsg = 'Unsupported NAS import action';
    }
}

$title = 'Import NAS JSON backup';
$help = 'Upload a JSON backup exported by daloRADIUS. New NAS entries are added. Existing NAS names are skipped and never modified.';
$inline_extra_css = <<<CSS
.nas-import-table-wrap { max-height: 30rem; overflow: auto; }
.nas-import-table-wrap thead th { position: sticky; top: 0; z-index: 2; white-space: nowrap; }
.nas-import-table-wrap td { max-width: 28rem; overflow-wrap: anywhere; }
CSS;

print_html_prologue($title, $langCode, array(), array(), $inline_extra_css);
print_title_and_help($title, $help);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

if (count($previewRows) > 0) {
    echo '<div class="row g-2 mb-3">';
    printf('<div class="col-12 col-md-4"><div class="alert alert-success mb-0"><strong>%d</strong> ready to import</div></div>', $readyCount);
    printf('<div class="col-12 col-md-4"><div class="alert alert-warning mb-0"><strong>%d</strong> skipped</div></div>', $skippedCount);
    printf('<div class="col-12 col-md-4"><div class="alert alert-danger mb-0"><strong>%d</strong> invalid</div></div>', $invalidCount);
    echo '</div>';

    echo '<h5>Import preview</h5>';
    echo '<p class="text-muted">Secrets are intentionally hidden. Existing NAS entries will not be changed.</p>';
    nas_import_render_rows($previewRows, 'nas-import-preview-table');

    echo '<div class="d-flex flex-wrap gap-2 mt-3">';
    if ($readyCount > 0) {
        $csrfToken = dalo_csrf_token();
        echo '<form method="POST" action="mng-rad-nas-import.php">';
        printf('<input type="hidden" name="csrf_token" value="%s">', htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'));
        echo '<input type="hidden" name="nas_import_action" value="confirm">';
        printf('<input type="hidden" name="preview_token" value="%s">', htmlspecialchars($previewToken, ENT_QUOTES, 'UTF-8'));
        echo '<button type="submit" class="btn btn-primary">Import ready NAS entries</button></form>';
    }
    echo '<a class="btn btn-light" href="mng-rad-nas-import.php">Cancel and choose another file</a>';
    echo '<a class="btn btn-light" href="mng-rad-nas-list.php">Back to NAS list</a>';
    echo '</div>';
} elseif (count($resultRows) > 0) {
    echo '<h5>Import result</h5>';
    nas_import_render_rows($resultRows, 'nas-import-result-table');
    echo '<div class="d-flex flex-wrap gap-2 mt-3">';
    echo '<a class="btn btn-primary" href="mng-rad-nas-list.php">Back to NAS list</a>';
    echo '<a class="btn btn-light" href="mng-rad-nas-import.php">Import another backup</a>';
    echo '</div>';
} else {
    $csrfToken = dalo_csrf_token();
    echo '<div class="card my-3"><div class="card-body">';
    echo '<h5 class="card-title">Select a NAS JSON backup</h5>';
    echo '<p class="card-text">All valid NAS entries with a new, unique NAS name will be added. Existing NAS entries are skipped and are never modified or deleted. The maximum file size is 2 MiB.</p>';
    echo '<form method="POST" action="mng-rad-nas-import.php" enctype="multipart/form-data">';
    printf('<input type="hidden" name="csrf_token" value="%s">', htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'));
    echo '<input type="hidden" name="nas_import_action" value="preview">';
    printf('<input type="hidden" name="MAX_FILE_SIZE" value="%d">', NAS_BACKUP_MAX_BYTES);
    echo '<div class="mb-3"><label for="nas_backup" class="form-label">JSON backup file</label>';
    echo '<input class="form-control" type="file" id="nas_backup" name="nas_backup" accept="application/json,.json" required></div>';
    echo '<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary">Preview import</button>';
    echo '<a class="btn btn-light" href="mng-rad-nas-list.php">Back to NAS list</a></div>';
    echo '</form></div></div>';
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

$inline_extra_js = <<<JS
function filterNasImportTable(tableId) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var search = document.querySelector('.nas-import-search[data-table="' + tableId + '"]');
    var status = document.querySelector('.nas-import-status[data-table="' + tableId + '"]');
    var needle = search ? search.value.toLowerCase() : '';
    var wantedStatus = status ? status.value : '';
    table.querySelectorAll('tbody tr').forEach(function (row) {
        var matchesText = row.textContent.toLowerCase().indexOf(needle) !== -1;
        var matchesStatus = !wantedStatus || row.dataset.status === wantedStatus;
        row.style.display = matchesText && matchesStatus ? '' : 'none';
    });
}

document.querySelectorAll('.nas-import-search, .nas-import-status').forEach(function (control) {
    control.addEventListener('input', function () { filterNasImportTable(control.dataset.table); });
    control.addEventListener('change', function () { filterNasImportTable(control.dataset.table); });
});
JS;

print_footer_and_html_epilogue($inline_extra_js);
