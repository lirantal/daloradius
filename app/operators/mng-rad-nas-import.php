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
    if (!nas_backup_is_valid_utf8($nasname)) {
        return 'binary:' . bin2hex($nasname);
    }
    return function_exists('mb_strtolower') ? mb_strtolower($nasname, 'UTF-8') : strtolower($nasname);
}

function nas_import_table(array $config) {
    $name = $config['CONFIG_DB_TBL_RADNAS'] ?? null;
    if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
        throw new InvalidArgumentException('Invalid NAS table');
    }
    return '`' . $name . '`';
}

function nas_import_existing_names(PDO $pdo, $table) {
    try {
        $res = $pdo->query("SELECT HEX(nasname) FROM $table");
        $names = array();
        while (($value = $res->fetchColumn()) !== false) {
            $nasname = nas_backup_decode_database_hex($value);
            if ($nasname === false) {
                return false;
            }
            $names[nas_import_name_key($nasname)] = true;
        }
        return $names;
    } catch (Throwable $exception) {
        return false;
    }
}

function nas_import_hex_value($value) {
    return ($value === null) ? null : strtoupper(bin2hex($value));
}

function nas_import_row_matches($stored, $entry) {
    foreach (array('nasname', 'shortname', 'type', 'secret', 'server', 'community', 'description') as $field) {
        if (($stored[$field . '_hex'] ?? null) !== nas_import_hex_value($entry[$field])) {
            return false;
        }
    }

    $storedPorts = ($stored['ports'] === null) ? null : intval($stored['ports']);
    return $storedPorts === $entry['ports'];
}

function nas_import_validate_entries($entries, $excludedRows) {
    if (!is_array($entries) || !is_array($excludedRows) ||
        count($entries) + count($excludedRows) > NAS_BACKUP_MAX_ENTRIES) {
        throw new InvalidArgumentException('Invalid NAS import preview');
    }
    $numbers = array();
    $names = array();
    foreach ($entries as $candidate) {
        if (!is_array($candidate) || !is_int($candidate['row_number'] ?? null) ||
            $candidate['row_number'] < 1 || !is_array($candidate['data'] ?? null)) {
            throw new InvalidArgumentException('Invalid NAS import row');
        }
        $number = $candidate['row_number'];
        $data = $candidate['data'];
        if (isset($numbers[$number]) || !is_string($data['nasname'] ?? null) ||
            $data['nasname'] === '' || !is_string($data['secret'] ?? null) ||
            $data['secret'] === '' ||
            !array_key_exists('ports', $data) ||
            ($data['ports'] !== null && (!is_int($data['ports']) ||
                $data['ports'] < 0 || $data['ports'] > 99999))) {
            throw new InvalidArgumentException('Invalid NAS import data');
        }
        foreach (array('shortname', 'type', 'server', 'community', 'description') as $field) {
            if (!array_key_exists($field, $data) ||
                ($data[$field] !== null && !is_string($data[$field]))) {
                throw new InvalidArgumentException('Invalid NAS import field');
            }
        }
        $nameKey = nas_import_name_key($data['nasname']);
        if (isset($names[$nameKey])) {
            throw new InvalidArgumentException('Duplicate NAS import entry');
        }
        $numbers[$number] = true;
        $names[$nameKey] = true;
    }
}

function nas_import_name_exists(PDO $pdo, $table, $nasname) {
    $hex = nas_import_hex_value($nasname);
    $isText = nas_backup_is_valid_utf8($nasname) && !preg_match('/[\x00-\x1F\x7F]/', $nasname);
    $sql = $isText
        ? "SELECT id FROM $table WHERE LOWER(nasname)=LOWER(CONVERT(UNHEX(?) USING utf8mb4)) "
          . "OR HEX(nasname)=? LIMIT 1 FOR UPDATE"
        : "SELECT id FROM $table WHERE HEX(nasname)=? LIMIT 1 FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($isText ? array($hex, $hex) : array($hex));
    return $stmt->fetchColumn() !== false;
}

function nas_import_apply(array $config, $location, $entries, $excludedRows) {
    $result = array('status' => 'error', 'lock_error' => true, 'lock_acquired' => false,
                    'release_failed' => false, 'inserted' => array(), 'skipped' => array());
    $pdo = null;
    $lock = null;
    try {
        nas_import_validate_entries($entries, $excludedRows);
        require_once $config['COMMON_INCLUDES'] . '/pdo_connection.php';
        $table = nas_import_table($config);
        $pdo = dalo_pdo_connect($config, $location);
        $engine = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES '
                              . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $engine->execute(array($config['CONFIG_DB_TBL_RADNAS']));
        if (strcasecmp((string)$engine->fetchColumn(), 'InnoDB') !== 0) {
            throw new RuntimeException('NAS import requires InnoDB');
        }
        $lock = nas_backup_acquire_lock($pdo, $config['CONFIG_DB_TBL_RADNAS'], 30);
        $result['lock_error'] = $lock['error'];
        $result['lock_acquired'] = $lock['acquired'];
        if (!$lock['acquired']) {
            $result['status'] = 'lock';
            return $result;
        }
        if (!$pdo->beginTransaction()) {
            throw new RuntimeException('NAS import transaction unavailable');
        }
        $existing = nas_import_existing_names($pdo, $table);
        if ($existing === false) {
            throw new RuntimeException('NAS import name lookup failed');
        }
        $insert = $pdo->prepare("INSERT INTO $table (nasname,shortname,type,ports,secret,server,community,description) "
                              . 'VALUES (UNHEX(?),UNHEX(?),UNHEX(?),?,UNHEX(?),UNHEX(?),UNHEX(?),UNHEX(?))');
        $verify = $pdo->prepare("SELECT HEX(nasname) AS nasname_hex, HEX(shortname) AS shortname_hex, "
                              . "HEX(type) AS type_hex, ports, HEX(secret) AS secret_hex, "
                              . "HEX(server) AS server_hex, HEX(community) AS community_hex, "
                              . "HEX(description) AS description_hex FROM $table WHERE id=?");
        foreach ($entries as $candidate) {
            $entry = $candidate['data'];
            $number = $candidate['row_number'];
            $key = nas_import_name_key($entry['nasname']);
            if (isset($existing[$key]) || nas_import_name_exists($pdo, $table, $entry['nasname'])) {
                $existing[$key] = true;
                $result['skipped'][] = array('row_number' => $number, 'data' => $entry,
                    'status' => 'skipped',
                    'information' => 'NAS name was added after the preview and already exists');
                continue;
            }
            try {
                $insert->execute(array(nas_import_hex_value($entry['nasname']),
                    nas_import_hex_value($entry['shortname']), nas_import_hex_value($entry['type']),
                    $entry['ports'], nas_import_hex_value($entry['secret']),
                    nas_import_hex_value($entry['server']), nas_import_hex_value($entry['community']),
                    nas_import_hex_value($entry['description'])));
            } catch (PDOException $exception) {
                if (!nas_import_is_duplicate_error($exception) ||
                    !nas_import_name_exists($pdo, $table, $entry['nasname'])) {
                    throw $exception;
                }
                $existing[$key] = true;
                $result['skipped'][] = array('row_number' => $number, 'data' => $entry,
                    'status' => 'skipped',
                    'information' => 'NAS name was added after the preview and already exists');
                continue;
            }
            $insertedId = $pdo->lastInsertId();
            $verify->execute(array($insertedId));
            $stored = $verify->fetch(PDO::FETCH_ASSOC);
            if (!is_array($stored) || !nas_import_row_matches($stored, $entry)) {
                throw new RuntimeException('NAS insert verification failed');
            }
            $result['inserted'][] = array('row_number' => $number, 'data' => $entry,
                'status' => 'imported', 'information' => 'NAS added successfully');
            $existing[$key] = true;
        }
        if (!$pdo->commit()) {
            $result['status'] = 'commit_failed';
            throw new RuntimeException('NAS import commit failed');
        }
        $result['status'] = 'success';
    } catch (Throwable $exception) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (Throwable $rollbackException) {
                error_log('NAS import rollback failure: ' . get_class($rollbackException));
            }
        }
        // The driver message can contain a NAS name or secret; log class only.
        error_log('NAS import failure: ' . get_class($exception));
    } finally {
        if ($lock !== null && $lock['acquired'] && $pdo instanceof PDO &&
            !nas_backup_release_lock($pdo, $lock['name'])) {
            $result['release_failed'] = true;
        }
        $pdo = null; // nonpersistent connection also releases a stranded lock
    }
    return $result;
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

function nas_import_display_value($value) {
    if ($value === null) {
        return '';
    }
    $value = (string)$value;
    if (!nas_backup_is_valid_utf8($value) || preg_match('/[\x00-\x1F\x7F]/', $value)) {
        return sprintf('Binary value (%d bytes)', strlen($value));
    }
    return $value;
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
            htmlspecialchars(nas_import_display_value($data['nasname'] ?? null), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(nas_import_display_value($data['shortname'] ?? null), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(nas_import_display_value($data['type'] ?? null), ENT_QUOTES, 'UTF-8'),
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
    $csrfValid = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        && dalo_check_csrf_token($_POST['csrf_token']);
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
                $existingNames = false;
                $pdo = null;
                try {
                    require_once $configValues['COMMON_INCLUDES'] . '/pdo_connection.php';
                    $table = nas_import_table($configValues);
                    $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                    $existingNames = nas_import_existing_names($pdo, $table);
                } catch (Throwable $exception) {
                    // Driver errors can include connection or bound details.
                    error_log('NAS preview lookup failure: ' . get_class($exception));
                } finally {
                    $pdo = null;
                }

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

        if (!is_array($preview) || !is_string($submittedToken) ||
            !is_string($preview['token'] ?? null) ||
            !hash_equals($preview['token'], $submittedToken)) {
            $failureMsg = 'The NAS import preview is missing or no longer valid';
        } elseif (time() - intval($preview['created_at'] ?? 0) > 1800) {
            unset($_SESSION['nas_import_preview']);
            $failureMsg = 'The NAS import preview has expired; upload the backup again';
        } else {
            $entries = $preview['entries'] ?? array();
            $excludedRows = $preview['excluded_rows'] ?? array();
            $operation = nas_import_apply($configValues, $_SESSION['location_name'] ?? 'default',
                                          $entries, $excludedRows);
            $importLockAcquired = $operation['lock_acquired'];
            if ($operation['release_failed']) {
                $logAction .= 'NAS import advisory lock release could not be confirmed on page: ';
            }

            if ($operation['status'] === 'lock') {
                $failureMsg = $operation['lock_error']
                    ? 'Unable to acquire the NAS import lock; please retry the import'
                    : 'Another NAS import is currently running; please retry in a moment';
                $logAction .= 'NAS import lock was unavailable on page: ';
                $readyCount = is_array($entries) ? count($entries) : 0;
                $previewToken = (string)($preview['token'] ?? '');
                foreach ($entries as $candidate) {
                    $previewRows[] = array(
                        'row_number' => (int)$candidate['row_number'],
                        'data' => $candidate['data'],
                        'errors' => array(),
                        'status' => 'ready',
                        'information' => 'New NAS name',
                    );
                }
                foreach ($excludedRows as $excludedRow) {
                    $previewRows[] = $excludedRow;
                    if (($excludedRow['status'] ?? '') === 'skipped') {
                        $skippedCount++;
                    } elseif (($excludedRow['status'] ?? '') === 'invalid') {
                        $invalidCount++;
                    }
                }
                usort($previewRows, function ($a, $b) {
                    return (int)$a['row_number'] <=> (int)$b['row_number'];
                });
            } elseif ($operation['status'] !== 'success') {
                $failureMsg = $operation['status'] === 'commit_failed'
                    ? 'The NAS import could not be committed'
                    : 'The NAS import failed and all new rows were rolled back';
                if (is_array($entries)) {
                    foreach ($entries as $candidate) {
                        if (is_array($candidate) && isset($candidate['row_number'], $candidate['data'])) {
                            $resultRows[] = array(
                                'row_number' => (int)$candidate['row_number'],
                                'data' => $candidate['data'],
                                'status' => 'invalid',
                                'information' => 'Not imported because the transaction was rolled back',
                            );
                        }
                    }
                }
                $logAction .= 'NAS import failed and was rolled back on page: ';
            } else {
                $insertedRows = $operation['inserted'];
                $skippedRows = $operation['skipped'];
                $resultRows = array_merge($insertedRows, $skippedRows, $excludedRows);
                usort($resultRows, function ($a, $b) {
                    return (int)$a['row_number'] <=> (int)$b['row_number'];
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
                    count($insertedRows), $totalSkipped, $previewInvalidCount
                );
            }
            if ($importLockAcquired) {
                unset($_SESSION['nas_import_preview']);
            }
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
    printf(
        '<p class="card-text">All valid NAS entries with a new, unique NAS name will be added. Existing NAS entries are skipped and are never modified or deleted. The maximum file size is %d MiB.</p>',
        intval(NAS_BACKUP_MAX_BYTES / 1048576)
    );
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
