<?php
/*
 * Hash legacy plaintext user portal passwords.
 *
 * Usage:
 *   php hash-user-portal-passwords.php --dry-run
 *   php hash-user-portal-passwords.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$options = getopt('', array('dry-run', 'batch-size::', 'help'));
if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php hash-user-portal-passwords.php [--dry-run] [--batch-size=N]\n");
    exit(0);
}

$dry_run = isset($options['dry-run']);
$batch_size = isset($options['batch-size']) ? intval($options['batch-size']) : 100;
if ($batch_size < 1 || $batch_size > 1000) {
    fwrite(STDERR, "batch-size must be between 1 and 1000\n");
    exit(2);
}

$root = dirname(__DIR__, 3);
include_once $root . '/app/common/includes/config_read.php';
include_once $root . '/app/common/includes/portal_password.php';
include $root . '/app/common/includes/db_open.php';

// PEAR DB interpolates prepared parameters in error debug information. Never
// invoke the application's verbose callback while credentials are in a query.
$dbSocket->setErrorHandling(PEAR_ERROR_RETURN);

$table = $configValues['CONFIG_DB_TBL_DALOUSERINFO'];
$last_id = 0;
$counts = array(
    'scanned' => 0,
    'empty' => 0,
    'already_hashed' => 0,
    'migrated' => 0,
    'conflicted' => 0,
    'failed' => 0,
);

while (true) {
    $sql = sprintf(
        "SELECT id, portalloginpassword FROM %s WHERE id > ? ORDER BY id ASC LIMIT %d",
        $table,
        $batch_size
    );
    $stmt = $dbSocket->prepare($sql);
    $res = $dbSocket->execute($stmt, array($last_id));
    $dbSocket->freePrepared($stmt);

    if (DB::isError($res)) {
        $counts['failed']++;
        break;
    }

    $rows = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $rows[] = $row;
    }
    $res->free();

    if (count($rows) === 0) {
        break;
    }

    foreach ($rows as $row) {
        $id = intval($row['id']);
        $stored_password = (string) $row['portalloginpassword'];
        $last_id = $id;
        $counts['scanned']++;

        if ($stored_password === '') {
            $counts['empty']++;
            continue;
        }

        if (dalo_portal_password_is_hash($stored_password)) {
            $counts['already_hashed']++;
            continue;
        }

        if ($dry_run) {
            $counts['migrated']++;
            continue;
        }

        $hash = dalo_portal_password_hash($stored_password);
        if ($hash === false) {
            $counts['failed']++;
            continue;
        }

        $sql = sprintf(
            "UPDATE %s SET portalloginpassword=? WHERE id=? AND portalloginpassword=?",
            $table
        );
        $stmt = $dbSocket->prepare($sql);
        $update = $dbSocket->execute($stmt, array($hash, $id, $stored_password));
        $dbSocket->freePrepared($stmt);

        if (DB::isError($update)) {
            $counts['failed']++;
            continue;
        }

        if ($dbSocket->affectedRows() === 1) {
            $counts['migrated']++;
        } else {
            $counts['conflicted']++;
        }
    }
}

include $root . '/app/common/includes/db_close.php';

fwrite(STDOUT, sprintf(
    "%s: scanned=%d empty=%d already_hashed=%d %s=%d conflicted=%d failed=%d\n",
    $dry_run ? 'DRY RUN' : 'COMPLETE',
    $counts['scanned'],
    $counts['empty'],
    $counts['already_hashed'],
    $dry_run ? 'would_migrate' : 'migrated',
    $counts['migrated'],
    $counts['conflicted'],
    $counts['failed']
));

exit($counts['failed'] === 0 ? 0 : 1);
