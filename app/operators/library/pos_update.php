<?php
/* UNIT-012: POS edit writes share one PDO transaction. */
require_once __DIR__ . '/pos_provision.php';

function dalo_pos_update(PDO $pdo, $config, $username, $planName, $reassign,
                         $groups, $userInfo, $billing, $updated, $operator) {
    if (!is_string($username) || $username === '' || strlen($username) > 128 ||
        !is_string($planName) || strlen($planName) > 128 || !is_bool($reassign)) {
        throw new InvalidArgumentException('Invalid POS edit selection');
    }
    $checkTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $userTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $billingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $groupsTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $plansTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mappingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    $userFields = array('firstname','lastname','email','department','company','workphone',
        'homephone','mobilephone','address','city','state','country','zip','notes',
        'changeuserinfo','enableportallogin');
    $billingFields = array('contactperson','company','email','phone','address','city',
        'state','country','zip','paymentmethod','cash','creditcardname','creditcardnumber',
        'creditcardverification','creditcardtype','creditcardexp','notes','changeuserbillinfo',
        'lead','coupon','ordertaker','billstatus','nextinvoicedue','billdue',
        'postalinvoice','faxinvoice','emailinvoice');
    foreach ($userFields as $field) {
        if (!isset($userInfo[$field]) || !is_string($userInfo[$field])) {
            throw new InvalidArgumentException('Invalid POS user information');
        }
    }
    if (!isset($userInfo['portalloginpassword']) || !is_string($userInfo['portalloginpassword'])) {
        throw new InvalidArgumentException('Invalid portal password');
    }
    foreach ($billingFields as $field) {
        if (!isset($billing[$field]) || !is_string($billing[$field])) {
            throw new InvalidArgumentException('Invalid POS billing information');
        }
    }
    if (!is_array($groups)) {
        throw new InvalidArgumentException('Invalid POS profiles');
    }
    $normalized = array();
    if (!$reassign) {
        foreach ($groups as $group) {
            if (!is_array($group) || array_keys($group) !== array(0, 1) ||
                !is_string($group[0]) || !is_string($group[1]) ||
                !preg_match('/^-?[0-9]+$/D', $group[1])) {
                throw new InvalidArgumentException('Invalid POS profile priority');
            }
            $name = trim($group[0]);
            if ($name === '' || strlen($name) > 128) {
                throw new InvalidArgumentException('Invalid POS profile');
            }
            $normalized[] = array($name, normalize_user_group_priority($name, $group[1]));
        }
    }
    if ($userInfo['portalloginpassword'] !== '') {
        $userInfo['portalloginpassword'] = dalo_portal_password_hash($userInfo['portalloginpassword']);
        if (!is_string($userInfo['portalloginpassword'])) {
            throw new RuntimeException('Could not hash portal password');
        }
        $userFields[] = 'portalloginpassword';
    }
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start POS edit');
    }
    try {
        // Lock the account and recheck existence on the write connection.
        $select = $pdo->prepare("SELECT id FROM $checkTable WHERE username=:username LIMIT 1 FOR UPDATE");
        $select->execute(array(':username' => $username));
        $exists = $select->fetchColumn() !== false;
        $select->closeCursor();
        if (!$exists) {
            throw new DomainException('User no longer exists');
        }
        $select = $pdo->prepare("SELECT id FROM $userTable WHERE username=:username LIMIT 1 FOR UPDATE");
        $select->execute(array(':username' => $username));
        $hasUserInfo = $select->fetchColumn() !== false;
        $select->closeCursor();
        $select = $pdo->prepare("SELECT id, planName FROM $billingTable WHERE username=:username LIMIT 1 FOR UPDATE");
        $select->execute(array(':username' => $username));
        $oldBilling = $select->fetch(PDO::FETCH_ASSOC);
        $select->closeCursor();
        if ($planName !== '' && (!$oldBilling || $planName !== $oldBilling['planName'])) {
            $select = $pdo->prepare("SELECT id FROM $plansTable WHERE planName=:name AND planActive='yes' LIMIT 1 FOR UPDATE");
            $select->execute(array(':name' => $planName));
            $validPlan = $select->fetchColumn() !== false;
            $select->closeCursor();
            if (!$validPlan) {
                throw new InvalidArgumentException('Invalid or inactive plan');
            }
        }
        if ($reassign) {
            $select = $pdo->prepare("SELECT profile_name FROM $mappingTable WHERE plan_name=:name FOR UPDATE");
            $select->execute(array(':name' => $planName));
            $mapped = $select->fetchAll(PDO::FETCH_COLUMN);
            $select->closeCursor();
            $normalized = array();
            foreach ($mapped as $name) {
                $normalized[] = array($name, normalize_user_group_priority($name, 0));
            }
        } else {
            dalo_validate_plan_profiles($pdo, $config, array_column($normalized, 0));
        }
        $userInfo['updatedate'] = $updated;
        $userInfo['updateby'] = $operator;
        if ($hasUserInfo) {
            $fields = array_merge($userFields, array('updatedate', 'updateby'));
            dalo_pos_update_fields($pdo, $userTable, $username, $userInfo, $fields);
        } else {
            $userInfo['username'] = $username;
            $userInfo['creationdate'] = $updated;
            $userInfo['creationby'] = $operator;
            if (!in_array('portalloginpassword', $userFields, true)) {
                unset($userInfo['portalloginpassword']);
            }
            dalo_pos_insert($pdo, $userTable, $userInfo,
                array_merge($userFields, array('username','creationdate','creationby','updatedate','updateby')));
        }
        $billing['planName'] = $planName;
        if ($oldBilling) {
            $billing['updatedate'] = $updated;
            $billing['updateby'] = $operator;
            dalo_pos_update_fields($pdo, $billingTable, $username, $billing,
                array_merge($billingFields, array('planName','updatedate','updateby')));
        } else {
            $billing['username'] = $username;
            $billing['creationdate'] = $updated;
            $billing['creationby'] = $operator;
            dalo_pos_insert($pdo, $billingTable, $billing,
                array_merge($billingFields, array('username','planName','creationdate','creationby')));
        }
        $delete = $pdo->prepare("DELETE FROM $groupsTable WHERE username=:username");
        $delete->execute(array(':username' => $username));
        $insert = $pdo->prepare("INSERT INTO $groupsTable (username,groupname,priority) VALUES (:username,:group,:priority)");
        foreach ($normalized as $group) {
            $insert->execute(array(':username' => $username, ':group' => $group[0], ':priority' => $group[1]));
        }
        $pdo->commit();
        return count($normalized);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function dalo_pos_update_fields(PDO $pdo, $table, $username, $values, $allowed) {
    $set = array();
    $params = array(':username' => $username);
    foreach ($allowed as $field) {
        if (!array_key_exists($field, $values) || !is_string($values[$field]) ||
            !preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/D', $field)) {
            throw new InvalidArgumentException('Invalid POS edit field');
        }
        $set[] = '`' . $field . '`=:' . $field;
        $params[':' . $field] = $values[$field];
    }
    $statement = $pdo->prepare("UPDATE $table SET " . implode(', ', $set) . ' WHERE username=:username');
    $statement->execute($params);
}
