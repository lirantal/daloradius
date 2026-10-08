<?php
/* UNIT-018: one caller-owned PDO transaction for the entire user edit. */
require_once __DIR__ . '/pos_update.php';

function dalo_user_edit_table($config, $key) {
    if ($key === 'CONFIG_DB_TBL_DALODICTIONARY') {
        $table = $config[$key] ?? null;
        if (!is_string($table) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
            throw new InvalidArgumentException('Invalid user edit table');
        }
        return '`' . $table . '`';
    }
    return dalo_pos_table($config, $key);
}

/** Parse the four-slot controls before starting a transaction. */
function dalo_user_edit_attributes($post, $skip, $validOps) {
    $attributes = array();
    foreach ($post as $name => $field) {
        if (in_array($name, $skip, true) || !is_array($field)) {
            continue;
        }
        if (array_keys($field) !== array(0, 1, 2, 3)) {
            throw new InvalidArgumentException('Invalid user attribute');
        }
        foreach ($field as $part) {
            if (!is_string($part)) {
                throw new InvalidArgumentException('Invalid user attribute field');
            }
        }
        list($idAndAttribute, $value, $op, $table) = array_map('trim', $field);
        if (strpos($idAndAttribute, '__') !== false) {
            list($id, $attribute) = explode('__', $idAndAttribute, 2);
            if (!ctype_digit($id) || strlen($id) > 10) {
                throw new InvalidArgumentException('Invalid attribute ID');
            }
            $id = (int) $id;
        } else {
            $id = 0;
            $attribute = $idAndAttribute;
        }
        if ($attribute === '' && $value === '') {
            continue;
        }
        if ($attribute === '' || $value === '' || !in_array($op, $validOps, true) ||
            strlen($attribute) > 128 || strlen($value) > 4096) {
            throw new InvalidArgumentException('Invalid user attribute');
        }
        if ($table !== 'radcheck' && $table !== 'radreply') {
            throw new InvalidArgumentException('Invalid attribute table');
        }
        if (is_passwordlike_attribute($attribute) &&
            !dalo_cleartext_password_allowed() &&
            in_array($attribute, dalo_cleartext_password_attributes(), true)) {
            throw new InvalidArgumentException('Cleartext password attribute is disabled');
        }
        $attributes[] = array($id, $attribute, $value, $op, $table);
    }
    return $attributes;
}

function dalo_user_edit_fields($values, $names) {
    $result = array();
    foreach ($names as $name) {
        if (!isset($values[$name]) || !is_string($values[$name])) {
            throw new InvalidArgumentException('Invalid user information');
        }
        $result[$name] = trim($values[$name]);
    }
    return $result;
}

function dalo_user_edit_groups($groups) {
    if (!is_array($groups)) {
        throw new InvalidArgumentException('Invalid user groups');
    }
    $out = array();
    foreach ($groups as $group) {
        if (!is_array($group) || array_keys($group) !== array(0, 1) ||
            !is_string($group[0]) || !is_string($group[1]) ||
            !preg_match('/^-?[0-9]+$/D', $group[1])) {
            throw new InvalidArgumentException('Invalid group priority');
        }
        $name = trim($group[0]);
        if ($name === '' || strlen($name) > 128 || isset($out[$name])) {
            throw new InvalidArgumentException('Invalid or duplicate group');
        }
        $out[$name] = normalize_user_group_priority($name, $group[1]);
    }
    return $out;
}

function dalo_user_edit_attribute_write(PDO $pdo, $config, $username, $attributes) {
    $checkTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $replyTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_RADREPLY');
    $seen = array();
    foreach ($attributes as $entry) {
        list($id, $attribute, $value, $op, $kind) = $entry;
        $table = $kind === 'radreply' ? $replyTable : $checkTable;
        if ($id !== 0) {
            $key = $kind . ':' . $id;
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Duplicate attribute ID');
            }
            $seen[$key] = true;
            $select = $pdo->prepare("SELECT attribute, op, value FROM $table WHERE id=:id AND username=:username FOR UPDATE");
            $select->execute(array(':id' => $id, ':username' => $username));
            $old = $select->fetch(PDO::FETCH_ASSOC);
            $select->closeCursor();
            if (!$old || $old['attribute'] !== $attribute) {
                throw new InvalidArgumentException('Stale or foreign user attribute');
            }
            if (is_passwordlike_attribute($attribute) && $old['value'] === $value) {
                if ($old['op'] !== $op) {
                    $update = $pdo->prepare("UPDATE $table SET op=:op WHERE id=:id AND username=:username");
                    $update->execute(array(':op' => $op, ':id' => $id, ':username' => $username));
                }
                continue;
            }
        }
        if (is_passwordlike_attribute($attribute)) {
            $value = hashPasswordAttribute($attribute, $value);
            if (!is_string($value)) {
                throw new RuntimeException('Could not hash user attribute');
            }
        }
        $present = $pdo->prepare("SELECT id FROM $table WHERE username=:username AND attribute=:attribute
                                  AND op=:op AND value=:value LIMIT 1");
        $present->execute(array(':username' => $username, ':attribute' => $attribute,
                                ':op' => $op, ':value' => $value));
        $duplicate = $present->fetchColumn() !== false;
        $present->closeCursor();
        if ($duplicate) {
            continue;
        }
        if ($id === 0) {
            $insert = $pdo->prepare("INSERT INTO $table (username,attribute,op,value)
                                     VALUES (:username,:attribute,:op,:value)");
            $insert->execute(array(':username' => $username, ':attribute' => $attribute,
                                   ':op' => $op, ':value' => $value));
        } else {
            $update = $pdo->prepare("UPDATE $table SET value=:value, op=:op
                                     WHERE id=:id AND username=:username AND attribute=:attribute");
            $update->execute(array(':value' => $value, ':op' => $op, ':id' => $id,
                                   ':username' => $username, ':attribute' => $attribute));
        }
    }
}

/** Persist attributes, user info, billing info and groups on one PDO handle. */
function dalo_user_edit(PDO $pdo, $config, $username, $planName, $oldplanName,
                        $groups, $post, $skip, $validOps, $userInfo, $billing, $updated, $operator) {
    if (!is_string($username) || $username === '' || strlen($username) > 128 ||
        !is_string($planName) || strlen($planName) > 128 ||
        !is_string($oldplanName) || strlen($oldplanName) > 128) {
        throw new InvalidArgumentException('Invalid user selection');
    }
    $userFields = array('firstname','lastname','email','department','company','workphone',
        'homephone','mobilephone','address','city','state','country','zip','notes',
        'changeuserinfo','enableportallogin','portalloginpassword');
    $billingFields = array('contactperson','company','email','phone','address','city',
        'state','country','zip','postalinvoice','faxinvoice','emailinvoice',
        'paymentmethod','cash','creditcardname','creditcardnumber','creditcardverification',
        'creditcardtype','creditcardexp','lead','coupon','ordertaker','notes',
        'changeuserbillinfo','billdue','nextinvoicedue');
    $userInfo = dalo_user_edit_fields($userInfo, $userFields);
    $billing = dalo_user_edit_fields($billing, $billingFields);
    $normalized = dalo_user_edit_groups($groups);
    $attributes = dalo_user_edit_attributes($post, $skip, $validOps);
    if ($userInfo['portalloginpassword'] !== '') {
        $userInfo['portalloginpassword'] = dalo_portal_password_hash($userInfo['portalloginpassword']);
        if (!is_string($userInfo['portalloginpassword'])) {
            throw new RuntimeException('Could not hash portal password');
        }
    } else {
        unset($userInfo['portalloginpassword']);
        $userFields = array_values(array_diff($userFields, array('portalloginpassword')));
    }
    $checkTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $userTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $billingTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $groupTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $plansTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $mapTable = dalo_user_edit_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES');
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not begin user edit');
    }
    try {
        $check = $pdo->prepare("SELECT id FROM $checkTable WHERE username=:username LIMIT 1 FOR UPDATE");
        $check->execute(array(':username' => $username));
        $found = $check->fetchColumn() !== false;
        $check->closeCursor();
        if (!$found) {
            throw new DomainException('User no longer exists');
        }
        $select = $pdo->prepare("SELECT id,portalloginpassword FROM $userTable
                                 WHERE username=:username LIMIT 1 FOR UPDATE");
        $select->execute(array(':username' => $username));
        $oldUser = $select->fetch(PDO::FETCH_ASSOC);
        $select->closeCursor();
        if (($userInfo['changeuserinfo'] === '1' || $userInfo['enableportallogin'] === '1' ||
             $billing['changeuserbillinfo'] === '1') &&
            (!$oldUser || !$oldUser['portalloginpassword']) &&
            !isset($userInfo['portalloginpassword'])) {
            throw new InvalidArgumentException('Portal access requires a password');
        }
        $select = $pdo->prepare("SELECT id,planName FROM $billingTable
                                 WHERE username=:username LIMIT 1 FOR UPDATE");
        $select->execute(array(':username' => $username));
        $oldBilling = $select->fetch(PDO::FETCH_ASSOC);
        $select->closeCursor();
        $currentPlan = $oldBilling ? (string) $oldBilling['planName'] : '';
        if ($oldplanName !== $currentPlan) {
            throw new DomainException('User plan has changed since the form was opened');
        }
        if ($planName !== '' && $planName !== $currentPlan) {
            $select = $pdo->prepare("SELECT id FROM $plansTable WHERE planName=:name
                                     AND planActive='yes' LIMIT 1 FOR UPDATE");
            $select->execute(array(':name' => $planName));
            $validPlan = $select->fetchColumn() !== false;
            $select->closeCursor();
            if (!$validPlan) {
                throw new InvalidArgumentException('Invalid or inactive plan');
            }
        }
        dalo_validate_plan_profiles($pdo, $config, array_keys($normalized));
        if ($planName !== $currentPlan) {
            $select = $pdo->prepare("SELECT profile_name FROM $mapTable WHERE plan_name=:name FOR UPDATE");
            $select->execute(array(':name' => $currentPlan));
            $oldProfiles = $select->fetchAll(PDO::FETCH_COLUMN);
            $select->closeCursor();
            foreach ($oldProfiles as $name) {
                unset($normalized[$name]);
            }
            if ($planName !== '') {
                $select->execute(array(':name' => $planName));
                foreach ($select->fetchAll(PDO::FETCH_COLUMN) as $name) {
                    $normalized[$name] = normalize_user_group_priority($name, 0);
                }
                $select->closeCursor();
            }
        }
        dalo_user_edit_attribute_write($pdo, $config, $username, $attributes);
        $userInfo['updatedate'] = $updated;
        $userInfo['updateby'] = $operator;
        if ($oldUser) {
            dalo_pos_update_fields($pdo, $userTable, $username, $userInfo,
                array_merge($userFields, array('updatedate','updateby')));
        } else {
            $userInfo['username'] = $username;
            $userInfo['creationdate'] = $updated;
            $userInfo['creationby'] = $operator;
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
        $delete = $pdo->prepare("DELETE FROM $groupTable WHERE username=:username");
        $delete->execute(array(':username' => $username));
        $insert = $pdo->prepare("INSERT INTO $groupTable (username,groupname,priority)
                                 VALUES (:username,:name,:priority)");
        foreach ($normalized as $name => $priority) {
            $insert->execute(array(':username' => $username, ':name' => $name, ':priority' => $priority));
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit user edit');
        }
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/** PDO reads for the edit page's own fields; shared widgets remain legacy. */
function dalo_user_edit_read(PDO $pdo, $config, $key, $username, $columns, $suffix = '') {
    $table = dalo_user_edit_table($config, $key);
    $sql = 'SELECT ' . $columns . " FROM $table WHERE username=:username " . $suffix;
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':username' => $username));
    return $stmt->fetch(PDO::FETCH_NUM);
}
