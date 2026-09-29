<?php
/* UNIT-036: migrate group attribute create/edit writes to caller-owned PDO. */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'pdo_connection.php' ]);
    require_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'attributes_pdo.php' ]);

    $log = 'visited page: ';
    $logAction = '';
    $logDebugSQL = '';

    $item_prefix = 'groupreply-';
    $item_table_key = 'CONFIG_DB_TBL_RADGROUPREPLY';
    $item_table = $configValues[$item_table_key] ?? '';
    if (!is_string($item_table) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $item_table)) {
        $item_table = '';
    }
    $pdo = null;
    $item_raw = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['item'] ?? null) : ($_REQUEST['item'] ?? null);
    $item = is_string($item_raw) ? trim($item_raw) : '';
    $internal_id = null;
    if (preg_match('/^groupreply-([1-9][0-9]*)$/D', $item, $match)) {
        $internal_id = (int) $match[1];
    } else {
        $item = '';
    }
    $exists = false;
    $valid_attributes = array();

    try {
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $table = dalo_attribute_pdo_identifier($configValues, $item_table_key);
        $dictionary_name = $configValues['CONFIG_DB_TBL_DALODICTIONARY'] ?? '';
        if (!is_string($dictionary_name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $dictionary_name)) {
            throw new InvalidArgumentException('Invalid dictionary table configuration');
        }
        $dict = '`' . $dictionary_name . '`';
        $statement = $pdo->prepare("SELECT DISTINCT(attribute) FROM $dict
            WHERE RecommendedTable IS NULL OR RecommendedTable='' OR RecommendedTable=? ORDER BY attribute ASC");
        $statement->execute(array($item_table));
        while ($attributeRow = $statement->fetch(PDO::FETCH_NUM)) { $valid_attributes[] = $attributeRow[0]; }
        $valid_attributes = dalo_filter_cleartext_password_attributes($valid_attributes);
        if ($internal_id !== null) {
            $statement = $pdo->prepare("SELECT id FROM $table WHERE id=?");
            $statement->execute(array($internal_id));
            $exists = $statement->fetchColumn() !== false;
        }
    } catch (Throwable $exception) {
        error_log('groupreply read: ' . get_class($exception));
        $failureMsg = 'Unable to load groupreply; please retry';
    } finally {
        $pdo = null;
    }

    $selected_groupreply_item = $item;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_token'] ?? null) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg = 'CSRF token error';
            $logAction .= 'Failed updating groupreply: CSRF on page: ';
        } elseif ($internal_id === null) {
            $failureMsg = 'Selected an empty/invalid groupreply item';
            $logAction .= 'Failed updating groupreply: invalid item on page: ';
        } else {
            $opRaw = $_POST['op'] ?? null;
            $groupRaw = $_POST['groupname'] ?? null;
            $attributeRaw = $_POST['attribute'] ?? null;
            $valueRaw = $_POST['value'] ?? null;
            $op = is_string($opRaw) ? trim($opRaw) : '';
            $groupname = is_string($groupRaw) ? trim($groupRaw) : '';
            $attribute = is_string($attributeRaw) ? trim($attributeRaw) : '';
            $value = is_string($valueRaw) ? trim($valueRaw) : '';
            try {
                if (!in_array($op, $valid_ops, true) || $groupname === '' || $attribute === '' || $value === '') {
                    throw new InvalidArgumentException('Invalid required group attribute field');
                }
                if (dalo_attribute_pdo_length($groupname) > 64 || dalo_attribute_pdo_length($attribute) > 64 ||
                    dalo_attribute_pdo_length($value) > 253) {
                    throw new InvalidArgumentException('Group attribute field is too long');
                }
                if (!in_array($groupname, array_keys(get_groups()), true)) {
                    throw new InvalidArgumentException('Group does not exist');
                }
                $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                if (!$pdo->beginTransaction()) { throw new RuntimeException('Attribute transaction unavailable'); }
                $table = dalo_attribute_pdo_identifier($configValues, $item_table_key);
                $lock = $pdo->prepare("SELECT groupname,attribute,op,value FROM $table WHERE id=? FOR UPDATE");
                $lock->execute(array($internal_id));
                $current = $lock->fetch(PDO::FETCH_ASSOC);
                if (!$current) { throw new InvalidArgumentException('Stale or foreign group attribute ID'); }
                $duplicate = $pdo->prepare("SELECT 1 FROM $table WHERE groupname=? AND attribute=? AND value=? AND id<>? LIMIT 1");
                $duplicate->execute(array($groupname, $attribute, $value, $internal_id));
                if ($duplicate->fetchColumn() !== false) { throw new DomainException('Duplicate group attribute'); }
                $update = $pdo->prepare("UPDATE $table SET groupname=?,attribute=?,op=?,value=? WHERE id=?");
                $update->execute(array($groupname, $attribute, $op, $value, $internal_id));
                if (!$pdo->commit()) { throw new RuntimeException('Attribute commit failed'); }
                $exists = true;
                $successMsg = 'Successfully updated groupreply item';
                $logAction .= 'Successfully updated groupreply on page: ';
            } catch (Throwable $exception) {
                if ($pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
                error_log('groupreply update: ' . get_class($exception));
                $failureMsg = $exception instanceof DomainException
                    ? 'Failed to update groupreply item, duplicate entry'
                    : ($exception instanceof InvalidArgumentException
                        ? 'Empty or invalid required field(s)'
                        : 'Unable to update groupreply; please retry');
                $logAction .= 'Failed updating groupreply on page: ';
            } finally {
                $pdo = null;
            }
        }
    }

    // Keep the legacy PEAR read for the edit form and shared presentation contract.
    $groupname = $attribute = $op = $value = '';
    if ($internal_id !== null) {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
        $read = $dbSocket->prepare("SELECT groupname,attribute,op,value FROM $item_table WHERE id=?");
        $readResult = $dbSocket->execute($read, array($internal_id));
        $readRow = $readResult->fetchRow();
        if ($readRow) {
            list($groupname, $attribute, $op, $value) = $readRow;
            $exists = true;
        } else {
            $exists = false;
        }
        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    }
    if (!$exists) {
        $internal_id = null;
        $item = '';
    }

    // print HTML prologue
    $title = t('Intro','mngradgroupreplyedit.php');
    $help = t('helpPage','mngradgroupreplyedit');
    
    print_html_prologue($title, $langCode);

    if (!empty($groupname)) {
        $title .= sprintf(" %s", htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8'));
    }

    
    

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!empty($internal_id)) {
        
        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        'name' => 'groupname',
                                        'caption' => t('all','Groupname'),
                                        'type' => 'text',
                                        'value' => $groupname,
                                        'required' => true
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'attribute',
                                        'caption' => t('all','Attribute'),
                                        'type' => 'text',
                                        'value' => $attribute,
                                        'required' => true,
                                        'datalist' => $valid_attributes
                                     );
                                     
        $options = $valid_ops;
        $input_descriptors0[] = array(
                                        "name" => "op",
                                        "caption" => t('all','Operator'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $op
                                     );
                                     
        $input_descriptors0[] = array(
                                        'name' => 'value',
                                        'caption' => t('all','Value'),
                                        'type' => 'text',
                                        'value' => $value,
                                        'required' => true,
                                     );
        
        // descriptors 1
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "item",
                                        "type" => "hidden",
                                        "value" => sprintf("%s%d", $item_prefix, $internal_id),
                                     );

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );

        open_form();

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','GroupInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

        
    }
    
    print_back_to_previous_page();

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
    
?>
