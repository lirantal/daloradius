<?php
/* UNIT-010: remove plans and profile mappings in one PDO transaction. */
require_once __DIR__ . '/plan_create.php';

/** Reject the entire malformed selection before any write. */
function dalo_plan_names_from_post($input) {
    $values = is_array($input) ? $input : array($input);
    if (count($values) === 0) {
        throw new InvalidArgumentException('No billing plan selected');
    }
    $names = array();
    foreach ($values as $value) {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid billing plan name');
        }
        $name = trim($value);
        if ($name === '' ||
            (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 128) {
            throw new InvalidArgumentException('Invalid billing plan name');
        }
        $names[$name] = $name;
    }
    $names = array_values($names);
    sort($names, SORT_STRING); // Consistent lock order across overlapping batches.
    return $names;
}

/** Return [deleted plans, deleted associations], or restore all rows on failure. */
function dalo_delete_billing_plans(PDO $pdo, $config, $names) {
    $plansTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mappingTable = dalo_plan_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start billing plan deletion');
    }
    try {
        $check = $pdo->prepare("SELECT planName FROM $plansTable WHERE planName = :name FOR UPDATE");
        // An obsolete or forged name fails the entire batch rather than causing
        // a partial deletion. No legacy read is used to authorize the write.
        foreach ($names as $name) {
            $check->execute(array(':name' => $name));
            $found = $check->fetchColumn() !== false;
            $check->closeCursor();
            if (!$found) {
                throw new DomainException('Billing plan no longer exists');
            }
        }
        $deleteMapping = $pdo->prepare("DELETE FROM $mappingTable WHERE plan_name = :name");
        $deletePlan = $pdo->prepare("DELETE FROM $plansTable WHERE planName = :name");
        $counts = array(0, 0);
        foreach ($names as $name) {
            $args = array(':name' => $name);
            $deleteMapping->execute($args);
            $counts[1] += $deleteMapping->rowCount();
            $deletePlan->execute($args);
            $counts[0] += $deletePlan->rowCount();
        }
        $pdo->commit();
        return $counts;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
