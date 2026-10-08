<?php
/* UNIT-008: create a billing plan and all its profile mappings on one PDO handle. */

function dalo_plan_table($config, $key) {
    $allowed = array('CONFIG_DB_TBL_DALOBILLINGPLANS',
                     'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES',
                     'CONFIG_DB_TBL_RADGROUPCHECK', 'CONFIG_DB_TBL_RADGROUPREPLY',
                     'CONFIG_DB_TBL_RADUSERGROUP');
    if (!in_array($key, $allowed, true) || !isset($config[$key]) ||
        !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid billing table configuration');
    }
    return '`' . $config[$key] . '`';
}

/** Validate the complete profile selection before starting any database writes. */
function dalo_plan_profiles_from_post($input) {
    if (!is_array($input)) {
        throw new InvalidArgumentException('Invalid profile selection');
    }
    $profiles = array();
    foreach ($input as $value) {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid profile name');
        }
        $name = trim($value);
        if ($name === '') {
            continue;
        }
        if ((function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 256) {
            throw new InvalidArgumentException('Profile name is too long');
        }
        $profiles[$name] = $name;
    }
    return array_values($profiles);
}

/** Validate selected profiles against the three sources shown by the form. */
function dalo_validate_plan_profiles(PDO $pdo, $config, $profiles) {
    $groupTables = array(
        dalo_plan_table($config, 'CONFIG_DB_TBL_RADGROUPCHECK'),
        dalo_plan_table($config, 'CONFIG_DB_TBL_RADGROUPREPLY'),
        dalo_plan_table($config, 'CONFIG_DB_TBL_RADUSERGROUP'),
    );
    $groupChecks = array();
    foreach ($groupTables as $table) {
        $groupChecks[] = $pdo->prepare("SELECT groupname FROM $table WHERE groupname = :name LIMIT 1 FOR UPDATE");
    }
    foreach ($profiles as $profile) {
        $found = false;
        foreach ($groupChecks as $groupCheck) {
            $groupCheck->execute(array(':name' => $profile));
            $found = $groupCheck->fetchColumn() !== false;
            $groupCheck->closeCursor();
            if ($found) {
                break;
            }
        }
        if (!$found) {
            throw new InvalidArgumentException('Unknown profile');
        }
    }
}

/** Return number of profiles; reject duplicates and roll back any failed mapping. */
function dalo_create_billing_plan(PDO $pdo, $config, $values, $profiles, $created, $operator) {
    $plansTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mappingTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    $name = $values['planName'];
    if (!is_string($name) || trim($name) === '' ||
        (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 128) {
        throw new InvalidArgumentException('Invalid plan name');
    }
    // This list is fixed by this operation, never derived from request keys.
    $fields = array('planName', 'planId', 'planType', 'planTimeBank', 'planTimeType',
        'planTimeRefillCost', 'planBandwidthUp', 'planBandwidthDown', 'planTrafficTotal',
        'planTrafficUp', 'planTrafficDown', 'planTrafficRefillCost', 'planRecurring',
        'planRecurringPeriod', 'planRecurringBillingSchedule', 'planCost',
        'planSetupCost', 'planTax', 'planCurrency', 'planGroup', 'planActive');
    $columns = array();
    $placeholders = array();
    $params = array();
    foreach ($fields as $field) {
        if (!isset($values[$field]) || !is_string($values[$field])) {
            throw new InvalidArgumentException('Invalid plan field');
        }
        $columns[] = '`' . $field . '`';
        $placeholders[] = ':' . $field;
        $params[':' . $field] = $values[$field];
    }
    $columns[] = '`creationdate`';
    $columns[] = '`creationby`';
    $columns[] = '`updatedate`';
    $columns[] = '`updateby`';
    $placeholders = array_merge($placeholders, array(':created', ':operator', 'NULL', 'NULL'));
    $params[':created'] = $created;
    $params[':operator'] = $operator;

    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start billing plan transaction');
    }
    try {
        $check = $pdo->prepare("SELECT id FROM $plansTable WHERE planName = :name LIMIT 1 FOR UPDATE");
        $check->execute(array(':name' => $name));
        if ($check->fetchColumn() !== false) {
            throw new DomainException('Billing plan already exists');
        }
        $check->closeCursor();
        dalo_validate_plan_profiles($pdo, $config, $profiles);

        $insert = $pdo->prepare("INSERT INTO $plansTable (" . implode(', ', $columns) .
                                ') VALUES (' . implode(', ', $placeholders) . ')');
        $insert->execute($params);
        $mapping = $pdo->prepare("INSERT INTO $mappingTable (plan_name, profile_name)
                                  VALUES (:plan_name, :profile_name)");
        foreach ($profiles as $profile) {
            $mapping->execute(array(':plan_name' => $name, ':profile_name' => $profile));
        }
        $pdo->commit();
        return count($profiles);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
