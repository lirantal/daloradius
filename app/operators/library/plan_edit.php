<?php
/* UNIT-009: update one billing plan and replace its profiles atomically. */
require_once(__DIR__ . '/plan_create.php');

/** The plan name is immutable on this page; all editable columns are fixed. */
function dalo_edit_billing_plan(PDO $pdo, $config, $name, $values, $profiles, $updated, $operator) {
    if (!is_string($name) || trim($name) === '' ||
        (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 128) {
        throw new InvalidArgumentException('Invalid plan name');
    }
    $fields = array('planId', 'planType', 'planTimeType', 'planTimeBank',
        'planTimeRefillCost', 'planBandwidthUp', 'planBandwidthDown',
        'planTrafficTotal', 'planTrafficDown', 'planTrafficUp', 'planTrafficRefillCost',
        'planRecurring', 'planRecurringPeriod', 'planRecurringBillingSchedule',
        'planCost', 'planSetupCost', 'planTax', 'planCurrency', 'planActive', 'planGroup');
    $assignments = array();
    $params = array();
    foreach ($fields as $field) {
        if (!isset($values[$field]) || !is_string($values[$field])) {
            throw new InvalidArgumentException('Invalid plan field');
        }
        $assignments[] = '`' . $field . '` = :' . $field;
        $params[':' . $field] = $values[$field];
    }
    $params[':updated'] = $updated;
    $params[':operator'] = $operator;
    $params[':name'] = $name;
    $plansTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mappingTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');

    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start billing plan transaction');
    }
    try {
        $check = $pdo->prepare("SELECT id FROM $plansTable WHERE planName = :name LIMIT 1 FOR UPDATE");
        $check->execute(array(':name' => $name));
        if ($check->fetchColumn() === false) {
            throw new DomainException('Billing plan no longer exists');
        }
        $check->closeCursor();
        dalo_validate_plan_profiles($pdo, $config, $profiles);

        $update = $pdo->prepare("UPDATE $plansTable SET " . implode(', ', $assignments) .
                                ', `updatedate` = :updated, `updateby` = :operator WHERE planName = :name');
        $update->execute($params);
        $delete = $pdo->prepare("DELETE FROM $mappingTable WHERE plan_name = :name");
        $delete->execute(array(':name' => $name));
        $insert = $pdo->prepare("INSERT INTO $mappingTable (plan_name, profile_name) VALUES (:name, :profile)");
        foreach ($profiles as $profile) {
            $insert->execute(array(':name' => $name, ':profile' => $profile));
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
