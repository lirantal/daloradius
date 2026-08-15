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
const NAS_BACKUP_MAX_BYTES = 2097152;
const NAS_BACKUP_MAX_ENTRIES = 5000;

function nas_backup_string_length($value) {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function nas_backup_normalize_optional_string($value, $field, $max_length, &$errors) {
    if ($value === null) {
        return null;
    }

    if (!is_string($value)) {
        $errors[] = sprintf('%s must be a string or null', $field);
        return null;
    }

    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
        $errors[] = sprintf('%s contains control characters', $field);
    }

    if (nas_backup_string_length($value) > $max_length) {
        $errors[] = sprintf('%s exceeds %d characters', $field, $max_length);
    }

    return $value;
}

function nas_backup_normalize_entry($entry, $row_number) {
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
        $nasname = trim($nasname);
        if ($nasname === '') {
            $errors[] = 'nasname is required';
        }
        if (nas_backup_string_length($nasname) > 128) {
            $errors[] = 'nasname exceeds 128 characters';
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $nasname)) {
            $errors[] = 'nasname contains control characters';
        }
    }

    $secret = $entry['secret'] ?? null;
    if (!is_string($secret) || $secret === '') {
        $errors[] = 'secret is required and must be a string';
        $secret = '';
    } elseif (nas_backup_string_length($secret) > 60) {
        $errors[] = 'secret exceeds 60 characters';
    } elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $secret)) {
        $errors[] = 'secret contains control characters';
    }

    $shortname = nas_backup_normalize_optional_string($entry['shortname'] ?? null, 'shortname', 32, $errors);
    $type = nas_backup_normalize_optional_string($entry['type'] ?? 'other', 'type', 30, $errors);
    $server = nas_backup_normalize_optional_string($entry['server'] ?? null, 'server', 64, $errors);
    $community = nas_backup_normalize_optional_string($entry['community'] ?? null, 'community', 50, $errors);
    $description = nas_backup_normalize_optional_string($entry['description'] ?? null, 'description', 200, $errors);

    if ($type === null || trim($type) === '') {
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
    if (($document['version'] ?? null) !== NAS_BACKUP_VERSION) {
        $errors[] = sprintf('Unsupported backup version; expected %d', NAS_BACKUP_VERSION);
    }
    if (!isset($document['nas']) || !is_array($document['nas'])) {
        $errors[] = 'The backup does not contain a NAS list';
        return array('rows' => array(), 'errors' => $errors);
    }
    if (count($document['nas']) > NAS_BACKUP_MAX_ENTRIES) {
        $errors[] = sprintf('The backup contains more than %d NAS entries', NAS_BACKUP_MAX_ENTRIES);
        return array('rows' => array(), 'errors' => $errors);
    }

    $rows = array();
    foreach ($document['nas'] as $index => $entry) {
        $rows[] = nas_backup_normalize_entry($entry, $index + 1);
    }

    return array('rows' => $rows, 'errors' => $errors);
}
