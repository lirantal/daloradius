<?php
/*
 * Shared helpers for daloRADIUS NAS JSON import/export.
 */

if (strpos($_SERVER['PHP_SELF'] ?? '', '/include/management/nasImportExport.php') !== false) {
    http_response_code(404);
    exit;
}

const NAS_BACKUP_FORMAT = 'daloradius-nas-backup';
const NAS_BACKUP_VERSION = 1;
const NAS_BACKUP_BINARY_VERSION = 2;
const NAS_BACKUP_MAX_BYTES = 8388608;
const NAS_BACKUP_MAX_ENTRIES = 5000;

function nas_backup_lock_name($dbSocket, $table) {
    $database = $dbSocket->getOne('SELECT DATABASE()');
    if (DB::isError($database) || !is_string($database) || $database === '') {
        return false;
    }

    return 'daloradius:nas:' . substr(hash('sha256', $database . "\0" . $table), 0, 48);
}

function nas_backup_acquire_lock($dbSocket, $table, $timeout) {
    $lockName = nas_backup_lock_name($dbSocket, $table);
    if ($lockName === false) {
        return array('name' => '', 'acquired' => false, 'error' => true);
    }

    $result = $dbSocket->getOne(sprintf(
        "SELECT GET_LOCK('%s', %d)",
        $dbSocket->escapeSimple($lockName),
        max(0, intval($timeout))
    ));

    return array(
        'name' => $lockName,
        'acquired' => !DB::isError($result) && intval($result) === 1,
        'error' => DB::isError($result),
    );
}

function nas_backup_release_lock($dbSocket, $lockName) {
    if (!is_string($lockName) || $lockName === '') {
        return false;
    }

    $result = $dbSocket->getOne(sprintf(
        "SELECT RELEASE_LOCK('%s')",
        $dbSocket->escapeSimple($lockName)
    ));
    return !DB::isError($result) && intval($result) === 1;
}

function nas_backup_is_valid_utf8($value) {
    return !is_string($value) || preg_match('//u', $value) === 1;
}

function nas_backup_decode_database_hex($value) {
    if ($value === null) {
        return null;
    }
    if ($value === '') {
        return '';
    }
    if (!is_string($value) || strlen($value) % 2 !== 0 || !ctype_xdigit($value)) {
        return false;
    }
    return hex2bin($value);
}

function nas_backup_encode_export_value($value, &$usesBinaryEncoding) {
    if (!is_string($value) ||
        (nas_backup_is_valid_utf8($value) && !preg_match('/[\x00-\x1F\x7F]/', $value))) {
        return $value;
    }

    $usesBinaryEncoding = true;
    return array(
        'encoding' => 'base64',
        'data' => base64_encode($value),
        'byte_length' => strlen($value),
    );
}

function nas_backup_decode_entry($entry, $version, &$errors, &$binaryFields) {
    if (!is_array($entry) || $version !== NAS_BACKUP_BINARY_VERSION) {
        return $entry;
    }

    $fields = array('nasname', 'shortname', 'type', 'secret', 'server', 'community', 'description');
    foreach ($fields as $field) {
        if (!array_key_exists($field, $entry) || !is_array($entry[$field])) {
            continue;
        }

        $encoded = $entry[$field];
        if (($encoded['encoding'] ?? '') !== 'base64' || !is_string($encoded['data'] ?? null) ||
            !is_int($encoded['byte_length'] ?? null) || $encoded['byte_length'] < 0) {
            $errors[] = sprintf('%s has an invalid binary encoding descriptor', $field);
            $entry[$field] = null;
            continue;
        }

        $decoded = base64_decode($encoded['data'], true);
        if ($decoded === false || base64_encode($decoded) !== $encoded['data']) {
            $errors[] = sprintf('%s contains invalid Base64 data', $field);
            $entry[$field] = null;
            continue;
        }
        if (strlen($decoded) !== $encoded['byte_length']) {
            $errors[] = sprintf('%s byte length does not match its binary data', $field);
            $entry[$field] = null;
            continue;
        }
        $entry[$field] = $decoded;
        $binaryFields[$field] = true;
    }

    return $entry;
}

function nas_backup_string_length($value) {
    if (function_exists('mb_strlen') && nas_backup_is_valid_utf8($value)) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function nas_backup_normalize_optional_string($value, $field, $max_length, &$errors, $allowBinary = false) {
    if ($value === null) {
        return null;
    }

    if (!is_string($value)) {
        $errors[] = sprintf('%s must be a string or null', $field);
        return null;
    }

    if (!$allowBinary && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
        $errors[] = sprintf('%s contains control characters', $field);
    }

    if (nas_backup_string_length($value) > $max_length) {
        $errors[] = sprintf('%s exceeds %d characters', $field, $max_length);
    }

    return $value;
}

function nas_backup_normalize_entry($entry, $row_number, $preserveValues = false, $binaryFields = array()) {
    $errors = array();

    if (!is_array($entry)) {
        return array(
            'row_number' => $row_number,
            'data' => array('nasname' => sprintf('Row %d', $row_number)),
            'errors' => array('NAS entry must be a JSON object'),
        );
    }

    $nasname = $entry['nasname'] ?? null;
    if (!is_string($nasname)) {
        $errors[] = 'nasname must be a string';
        $nasname = '';
    } else {
        if (!$preserveValues) {
            $nasname = trim($nasname);
        }
        if ($nasname === '') {
            $errors[] = 'nasname is required';
        }
        if (nas_backup_string_length($nasname) > 128) {
            $errors[] = 'nasname exceeds 128 characters';
        }
        if (!isset($binaryFields['nasname']) && preg_match('/[\x00-\x1F\x7F]/', $nasname)) {
            $errors[] = 'nasname contains control characters';
        }
    }

    $secret = $entry['secret'] ?? null;
    if (!is_string($secret) || $secret === '') {
        $errors[] = 'secret is required and must be a string';
        $secret = '';
    } elseif (nas_backup_string_length($secret) > 60) {
        $errors[] = 'secret exceeds 60 characters';
    } elseif (!isset($binaryFields['secret']) && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $secret)) {
        $errors[] = 'secret contains control characters';
    }

    $shortname = nas_backup_normalize_optional_string($entry['shortname'] ?? null, 'shortname', 32, $errors, isset($binaryFields['shortname']));
    $typeValue = array_key_exists('type', $entry) ? $entry['type'] : 'other';
    $type = nas_backup_normalize_optional_string($typeValue, 'type', 30, $errors, isset($binaryFields['type']));
    $server = nas_backup_normalize_optional_string($entry['server'] ?? null, 'server', 64, $errors, isset($binaryFields['server']));
    $community = nas_backup_normalize_optional_string($entry['community'] ?? null, 'community', 50, $errors, isset($binaryFields['community']));
    $description = nas_backup_normalize_optional_string($entry['description'] ?? null, 'description', 200, $errors, isset($binaryFields['description']));

    if (!$preserveValues && $type !== null && trim($type) === '') {
        $type = 'other';
    }

    $ports = $entry['ports'] ?? null;
    if ($ports === '') {
        $ports = null;
    }
    if ($ports !== null) {
        if (is_int($ports)) {
            // already normalized
        } elseif (is_string($ports) && preg_match('/^[0-9]+$/', $ports)) {
            $ports = intval($ports);
        } else {
            $errors[] = 'ports must be an integer or null';
            $ports = null;
        }

        if ($ports !== null && ($ports < 0 || $ports > 99999)) {
            $errors[] = 'ports must be between 0 and 99999';
        }
    }

    return array(
        'row_number' => $row_number,
        'data' => array(
            'nasname' => $nasname,
            'shortname' => $shortname,
            'type' => $type,
            'ports' => $ports,
            'secret' => $secret,
            'server' => $server,
            'community' => $community,
            'description' => $description,
        ),
        'errors' => array_values(array_unique($errors)),
    );
}

function nas_backup_parse_document($contents) {
    $document = json_decode($contents, true);

    if (!is_array($document)) {
        return array('rows' => array(), 'errors' => array('The uploaded file is not valid JSON'));
    }

    $errors = array();
    if (($document['format'] ?? '') !== NAS_BACKUP_FORMAT) {
        $errors[] = sprintf('Unsupported backup format; expected %s', NAS_BACKUP_FORMAT);
    }
    $version = $document['version'] ?? null;
    if (!in_array($version, array(NAS_BACKUP_VERSION, NAS_BACKUP_BINARY_VERSION), true)) {
        $errors[] = sprintf(
            'Unsupported backup version; expected %d or %d',
            NAS_BACKUP_VERSION,
            NAS_BACKUP_BINARY_VERSION
        );
    }
    if (!isset($document['nas']) || !is_array($document['nas']) ||
        array_values($document['nas']) !== $document['nas']) {
        $errors[] = 'The backup does not contain a NAS list';
        return array('rows' => array(), 'errors' => $errors);
    }
    if (count($document['nas']) > NAS_BACKUP_MAX_ENTRIES) {
        $errors[] = sprintf('The backup contains more than %d NAS entries', NAS_BACKUP_MAX_ENTRIES);
        return array('rows' => array(), 'errors' => $errors);
    }

    $rows = array();
    foreach ($document['nas'] as $index => $entry) {
        $decodeErrors = array();
        $binaryFields = array();
        $entry = nas_backup_decode_entry($entry, $version, $decodeErrors, $binaryFields);
        $row = nas_backup_normalize_entry(
            $entry,
            $index + 1,
            $version === NAS_BACKUP_BINARY_VERSION,
            $binaryFields
        );
        $row['errors'] = array_values(array_unique(array_merge($decodeErrors, $row['errors'])));
        $rows[] = $row;
    }

    return array('rows' => $rows, 'errors' => $errors);
}

function nas_import_is_duplicate_error($error) {
    if (!DB::isError($error)) {
        return false;
    }

    $details = $error->getMessage();
    foreach (array('getUserInfo', 'getDebugInfo') as $method) {
        if (method_exists($error, $method)) {
            $details .= ' ' . $error->{$method}();
        }
    }

    return preg_match('/(?:nativecode[=:\\s]*1062|duplicate entry|duplicate key|unique constraint)/i', $details) === 1;
}
