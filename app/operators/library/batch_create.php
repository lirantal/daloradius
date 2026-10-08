<?php
/* UNIT-014: one batch history and all generated users on one PDO transaction. */
require_once __DIR__ . '/pos_provision.php';

function dalo_batch_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOBATCHHISTORY',
                              'CONFIG_DB_TBL_DALOHOTSPOTS'), true) ||
        !isset($config[$key]) || !is_string($config[$key]) ||
        !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $config[$key])) {
        throw new InvalidArgumentException('Invalid batch table configuration');
    }
    return '`' . $config[$key] . '`';
}

/** Create all rows or none. Returns the committed batch history ID. */
function dalo_create_user_batch(PDO $pdo, $config, $name, $description, $hotspotId,
                                $planName, $group, $priority, $users, $userInfo,
                                $billing, $attributes, $created, $operator) {
    if (!is_string($name) || trim($name) === '' || strlen($name) > 64 ||
        !is_string($description) || !is_string($planName) ||
        !is_string($group) || !is_int($priority) || $priority < 0 ||
        !is_array($users) || count($users) < 1 || count($users) > 1000 ||
        !is_array($userInfo) || !is_array($billing) || !is_array($attributes) ||
        !is_string($created) || !is_string($operator) ||
        !($hotspotId === '' || (is_int($hotspotId) && $hotspotId > 0))) {
        throw new InvalidArgumentException('Invalid batch request');
    }
    $batchTable = dalo_batch_table($config, 'CONFIG_DB_TBL_DALOBATCHHISTORY');
    $hotspotTable = dalo_batch_table($config, 'CONFIG_DB_TBL_DALOHOTSPOTS');
    $checkTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADCHECK');
    $replyTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADREPLY');
    $groupsTable = dalo_pos_table($config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $infoTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $billingTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $plansTable = dalo_pos_table($config, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $userFields = array('firstname','lastname','email','department','company','workphone',
        'homephone','mobilephone','address','city','state','country','zip','notes',
        'changeuserinfo','enableportallogin','portalloginpassword');
    $billingFields = array('contactperson','company','email','phone','address','city',
        'state','country','zip','paymentmethod','cash','creditcardname','creditcardnumber',
        'creditcardexp','creditcardverification','creditcardtype','notes','lead','coupon',
        'ordertaker','billstatus','lastbill','nextbill','postalinvoice','faxinvoice',
        'emailinvoice','changeuserbillinfo');
    foreach ($userFields as $field) {
        if (!isset($userInfo[$field]) || !is_string($userInfo[$field])) {
            throw new InvalidArgumentException('Invalid batch user info');
        }
    }
    foreach ($billingFields as $field) {
        if (!isset($billing[$field]) || !is_string($billing[$field])) {
            throw new InvalidArgumentException('Invalid batch billing info');
        }
    }
    $unique = array();
    foreach ($users as $record) {
        if (!is_array($record) || !isset($record['username'], $record['password'],
                                      $record['attribute'], $record['value']) ||
            !is_string($record['username']) || $record['username'] === '' ||
            strlen($record['username']) > 64 || !is_string($record['password']) ||
            !is_string($record['attribute']) || !is_string($record['value']) ||
            $record['value'] === '' || strpos($record['username'], "\0") !== false ||
            isset($unique[$record['username']])) {
            throw new InvalidArgumentException('Invalid or repeated generated username');
        }
        $unique[$record['username']] = true;
    }
    foreach ($attributes as $attr) {
        if (!is_array($attr) || !in_array($attr['table'] ?? null,
            array('CONFIG_DB_TBL_RADCHECK','CONFIG_DB_TBL_RADREPLY'), true) ||
            !is_string($attr['attribute'] ?? null) ||
            !is_string($attr['value'] ?? null) ||
            !is_string($attr['op'] ?? null)) {
            throw new InvalidArgumentException('Invalid batch RADIUS attribute');
        }
    }
    if (!$pdo->beginTransaction()) {
        throw new RuntimeException('Could not start batch creation');
    }
    try {
        $select = $pdo->prepare("SELECT id FROM $batchTable WHERE batch_name=:name LIMIT 1 FOR UPDATE");
        $select->execute(array(':name' => $name));
        $alreadyExists = $select->fetchColumn() !== false;
        $select->closeCursor();
        if ($alreadyExists) {
            throw new DomainException('Batch name already exists');
        }
        if ($hotspotId !== '') {
            $select = $pdo->prepare("SELECT id FROM $hotspotTable WHERE id=:id LIMIT 1 FOR UPDATE");
            $select->execute(array(':id' => $hotspotId));
            $found = $select->fetchColumn() !== false;
            $select->closeCursor();
            if (!$found) {
                throw new InvalidArgumentException('Hotspot no longer exists');
            }
        }
        if ($planName !== '') {
            $select = $pdo->prepare("SELECT id FROM $plansTable WHERE planName=:name AND planActive='yes' LIMIT 1 FOR UPDATE");
            $select->execute(array(':name' => $planName));
            $found = $select->fetchColumn() !== false;
            $select->closeCursor();
            if (!$found) {
                throw new InvalidArgumentException('Plan no longer active');
            }
        }
        if ($group !== '') {
            dalo_validate_plan_profiles($pdo, $config, array($group));
        }
        foreach (array($checkTable, $infoTable, $billingTable) as $table) {
            $select = $pdo->prepare("SELECT id FROM $table WHERE username=:username LIMIT 1 FOR UPDATE");
            foreach ($users as $record) {
                $select->execute(array(':username' => $record['username']));
                $exists = $select->fetchColumn() !== false;
                $select->closeCursor();
                if ($exists) {
                    throw new DomainException('Generated username already exists');
                }
            }
        }
        // Keep an empty hotspot as the legacy empty string, not SQL NULL.
        $insert = $pdo->prepare("INSERT INTO $batchTable
            (batch_name,batch_description,hotspot_id,creationdate,creationby)
            VALUES (:name,:description,:hotspot,:created,:operator)");
        $insert->execute(array(':name' => $name, ':description' => $description,
            ':hotspot' => $hotspotId, ':created' => $created, ':operator' => $operator));
        $batchId = (int) $pdo->lastInsertId();
        if ($batchId < 1) {
            throw new RuntimeException('No batch ID returned');
        }
        $checkInsert = $pdo->prepare("INSERT INTO $checkTable (username,attribute,op,value)
            VALUES (:username,:attribute,:op,:value)");
        $replyInsert = $pdo->prepare("INSERT INTO $replyTable (username,attribute,op,value)
            VALUES (:username,:attribute,:op,:value)");
        $groupInsert = $pdo->prepare("INSERT INTO $groupsTable (username,groupname,priority)
            VALUES (:username,:group,:priority)");
        foreach ($users as $record) {
            $username = $record['username'];
            $checkInsert->execute(array(':username' => $username,
                ':attribute' => $record['attribute'], ':op' => ':=', ':value' => $record['value']));
            if ($group !== '') {
                $groupInsert->execute(array(':username' => $username, ':group' => $group,
                    ':priority' => normalize_user_group_priority($group, $priority)));
            }
            $info = $userInfo;
            if ($info['portalloginpassword'] !== '') {
                $info['portalloginpassword'] = dalo_portal_password_hash($info['portalloginpassword']);
                if (!is_string($info['portalloginpassword'])) {
                    throw new RuntimeException('Could not hash portal password');
                }
            } else {
                unset($info['portalloginpassword']);
            }
            $info['username'] = $username;
            $info['creationdate'] = $created;
            $info['creationby'] = $operator;
            dalo_pos_insert($pdo, $infoTable, $info,
                array_merge($userFields, array('username','creationdate','creationby')));
            $bill = $billing;
            $bill['username'] = $username;
            $bill['planName'] = $planName;
            $bill['hotspot_id'] = (string) $hotspotId;
            $bill['batch_id'] = (string) $batchId;
            $bill['creationdate'] = $created;
            $bill['creationby'] = $operator;
            dalo_pos_insert($pdo, $billingTable, $bill,
                array_merge($billingFields, array('username','planName','hotspot_id','batch_id',
                                                  'creationdate','creationby')));
            foreach ($attributes as $attr) {
                $statement = $attr['table'] === 'CONFIG_DB_TBL_RADREPLY' ? $replyInsert : $checkInsert;
                $statement->execute(array(':username' => $username,
                    ':attribute' => $attr['attribute'], ':op' => $attr['op'],
                    ':value' => $attr['value']));
            }
        }
        $pdo->commit();
        return $batchId;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
