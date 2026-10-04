<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include("include/management/functions.php");
    require_once("library/invoice_create.php");
    include_once("include/management/populate_selectbox.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    require_once __DIR__.'/library/invoice_reads_pdo.php';
    $invoiceReadPDO=null; $inline_extra_js=''; $username=''; $invoice_id='';
    $invoice_status_id='';
    $invoice_type_id=''; $invoice_date=''; $invoice_notes='';
    $this_amount=''; $this_tax_amount=''; $this_notes='';
    try {
    $invoiceReadPDO=dalo_invoice_read_open($configValues);
    // get valid statuses
    $valid_statuses = array();
    $statusTable=dalo_invoice_read_table($configValues,'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
    foreach(dalo_invoice_read_rows($invoiceReadPDO, "SELECT id,value FROM $statusTable ORDER BY value ASC") as $statusRow) { $valid_statuses[(int)$statusRow[0]]=$statusRow[1]; }

    // get valid types
    $readBindings = array();
    $sql = sprintf("SELECT id, value FROM %s", dalo_invoice_read_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'));
    $res = dalo_invoice_read_rows($invoiceReadPDO, $sql, $readBindings);
    $logDebugSQL .= "$sql;\n";
    
    $valid_types = array();
    foreach ($res as $row) {
        list($id, $value) = $row;
        
        $valid_types[$id] = $value;
    }
    
    // get valid users
    $readBindings = array();
    $sql = sprintf("SELECT id, username FROM %s ORDER BY username ASC", dalo_invoice_read_table($configValues, 'CONFIG_DB_TBL_DALOUSERINFO'));
    $res = dalo_invoice_read_rows($invoiceReadPDO, $sql, $readBindings);
    $logDebugSQL .= "$sql;\n";

    $valid_users = array();
    foreach ($res as $row) {
        list($id, $value) = $row;
        $id = intval($id);
        
        $valid_users[$id] = $value;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
                
                // required later
                $current_datetime = date('Y-m-d H:i:s');
                $currBy = $operator;
                        
                $invalidHeader = false;
                foreach (array('user_id', 'invoice_type_id', 'invoice_status_id',
                               'invoice_date', 'invoice_notes') as $field) {
                    if (array_key_exists($field, $_POST) && !is_string($_POST[$field])) {
                        $invalidHeader = true;
                    }
                }
                $user_id = (isset($_POST['user_id']) && is_string($_POST['user_id']) &&
                            in_array(trim($_POST['user_id']), array_keys($valid_users)))
                         ? intval(trim($_POST['user_id'])) : 0;

                $invoice_type_id = (isset($_POST['invoice_type_id']) && is_string($_POST['invoice_type_id']) &&
                                    in_array(trim($_POST['invoice_type_id']), array_keys($valid_types)))
                                 ? intval(trim($_POST['invoice_type_id'])) : 0;

                $invoice_status_id = (isset($_POST['invoice_status_id']) && is_string($_POST['invoice_status_id']) &&
                                      in_array(trim($_POST['invoice_status_id']), array_keys($valid_statuses)))
                                   ? intval(trim($_POST['invoice_status_id'])) : 1;
                if (isset($_POST['invoice_status_id']) && is_string($_POST['invoice_status_id']) &&
                    trim($_POST['invoice_status_id']) !== '' &&
                    !in_array(trim($_POST['invoice_status_id']), array_keys($valid_statuses))) {
                    $invalidHeader = true;
                }

                $validDate = isset($_POST['invoice_date']) && is_string($_POST['invoice_date']) &&
                             trim($_POST['invoice_date']) !== '' &&
                             preg_match(DATE_REGEX, trim($_POST['invoice_date']), $m) === 1 &&
                             checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
                if (isset($_POST['invoice_date']) && is_string($_POST['invoice_date']) &&
                    trim($_POST['invoice_date']) !== '' && !$validDate) {
                    $invalidHeader = true;
                }
                $invoice_date = $validDate ? trim($_POST['invoice_date']) : date('Y-m-d');

                $invoice_notes = (isset($_POST['invoice_notes']) && is_string($_POST['invoice_notes']))
                               ? trim($_POST['invoice_notes']) : "";

                if ($invalidHeader || $user_id === 0 || $invoice_type_id === 0) {
                    $failureMsg = "Required invoice fields are empty or invalid";
                    $logAction .= "$failureMsg on page: ";
                } else {
                    try {
                        // Parse every item before the first write. Both the header and
                        // all children must use this one PDO handle and transaction.
                        $submittedItems = dalo_invoice_items_from_post($_POST);
                        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
                        list($invoice_id, $items) = dalo_create_invoice($pdo, $configValues,
                            array('user_id' => $user_id, 'date' => $invoice_date,
                                  'status_id' => $invoice_status_id, 'type_id' => $invoice_type_id,
                                  'notes' => $invoice_notes, 'created' => $current_datetime,
                                  'creator' => $currBy), $submittedItems);
                        $successMsg = sprintf("Successfully added new invoice (id: #<strong>%d</strong>) with %d item(s)",
                                              $invoice_id, $items);
                        $logAction .= sprintf("Successfully added new invoice [id: #%d, items: %d] on page: ",
                                              $invoice_id, $items);
                    } catch (Throwable $error) {
                        // Never echo SQL, connection details or input in the HTTP error.
                        $failureMsg = "Failed to add new invoice or its items";
                        $logAction .= "$failureMsg on page: ";
                    }
                }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
        
    } else {
        $user_id = (array_key_exists('user_id', $_GET) && is_string($_GET['user_id']) && !empty(trim($_GET['user_id'])) &&
                    in_array(trim($_GET['user_id']), array_keys($valid_users)))
                 ? intval(trim($_GET['user_id'])) : "";
    }

    $username = (!empty($user_id)) ? $valid_users[$user_id] : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    $inline_extra_js = "";
    // with an invalid user_id we get an invalid username
    // if the username is invalid we cannot procede
    if (empty($username)) {
        // required
        $failureMsg = sprintf("The required field '%s' is empty or invalid", t('all','UserId'));
        $logAction .= "$failureMsg on page: ";
    } else {
        
        // select for active plans
        $planSelect = '<select class="form-select" name="itemXXXXXXX[plan]">';

        $readBindings = array();
        $sql = sprintf("SELECT DISTINCT(planName), id
                          FROM %s WHERE planActive = 'yes'
                         ORDER BY planName ASC", dalo_invoice_read_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGPLANS'));
        $res = dalo_invoice_read_rows($invoiceReadPDO, $sql, $readBindings);

        foreach ($res as $row) {
            list($planName, $id) = $row;
            
            $planSelect .= sprintf('<option value="%d">%s</option>',
                                   intval($id), htmlspecialchars((string)($planName ?? ''), ENT_QUOTES, 'UTF-8'));
        }
        
        $planSelect .= '</select>';
        $planSelectJS=json_encode($planSelect, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        
        $inline_extra_js = <<<EOF

function addTableRow() {
    var container = document.getElementById('container'),
        counter = document.getElementById('counter'),
        num = parseInt(counter.value) + 1,
        trContainer = document.createElement('tr'),
        trIdName = 'itemsRow' + num,
        plansSelect = $planSelectJS,
        td1_name = `item\${num}[plan]`,
        td2_name = `item\${num}[amount]`,
        td3_name = `item\${num}[tax]`,
        td4_name = `item\${num}[notes]`,
        td5_onclick = `removeTableRow('\${trIdName}')`;
    
    trContainer.setAttribute('id', trIdName);
    
    var td1 = document.createElement('td');
    td1.innerHTML = plansSelect.replace("itemXXXXXXX[plan]", td1_name);

    var td2 = document.createElement('td');
    td2.innerHTML = `<input type="number" class="form-control" min="0" step=".01" id="item\${num}_amount" name="\${td2_name}">`;

	var td3 = document.createElement('td');
	td3.innerHTML = `<input type="number"  class="form-control"min="0" step=".01" id="item\${num}_tax" name="\${td3_name}">`;

	var td4 = document.createElement('td');
	td4.innerHTML = `<input type="text"  class="form-control"id="item\${num}_notes" name="\${td4_name}">`;

	var td5 = document.createElement('td');
	td5.innerHTML = `<button type="button" name="remove\${num}_button" onclick="\${td5_onclick}" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Remove Item</button>`;

	trContainer.appendChild(td1);
	trContainer.appendChild(td2);
	trContainer.appendChild(td3);
	trContainer.appendChild(td4);
	trContainer.appendChild(td5);
	container.appendChild(trContainer);
    
    counter.value = num;
}

function removeTableRow(rowId) {
    document.getElementById('container').removeChild(document.getElementById(rowId));
}
EOF;

    }

    if ($username !== '') {
        $readBindings = array();
        $sql = sprintf("SELECT contactperson, city, state FROM %s WHERE id=:invoice_id",
                       dalo_invoice_read_table($configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO'));
        $readBindings = array(':invoice_id'=>$user_id);
        $res = dalo_invoice_read_rows($invoiceReadPDO, $sql, $readBindings);
        $logDebugSQL .= "$sql;\n";

        $row = $res[0] ?? null;
        if ($row === null) { throw new RuntimeException('Missing invoice billing contact'); }
        list( $contactperson, $city, $state ) = $row;

    }
    } catch (Throwable $error) {
        dalo_invoice_read_failure($error);
        $invoice_id=''; $username=''; $inline_extra_js='';
    }
    unset($invoiceReadPDO);
    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
    );

    $title = t('Intro','billinvoicenew.php');
    $help = t('helpPage','billinvoicenew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js, "", $inline_extra_js);

    if (!empty($username_enc)) {
        $title .=  " :: " . $username_enc;
    }

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!empty($username)) {

        // print customer info
        printf('<div><strong>Customer</strong>: <a href="bill-pos-edit.php?username=%s">%s</a><br>',
               urlencode($username), htmlspecialchars((string)($contactperson ?? ''), ENT_QUOTES, 'UTF-8'));
        
        $arr = array();
        
        if (!empty($city)) {
            $arr[] = htmlspecialchars($city, ENT_QUOTES, 'UTF-8');
        }
        
        if (!empty($state)) {
            $arr[] = htmlspecialchars($state, ENT_QUOTES, 'UTF-8');
        }
        
        if (count($arr) > 0) {
            echo implode(", ", $arr);
        }
        
        echo '</div>';
        
        // set navbar stuff
        $navkeys = array( 'Invoice', 'Items', );

        // print navbar controls
        print_tab_header($navkeys);
        
        $input_descriptors0 = array();
        
        $options = $valid_statuses;
        $input_descriptors0[] = array(
                                        "name" => "invoice_status_id",
                                        "caption" => t('all','InvoiceStatus'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $invoice_status_id,
                                        "tooltipText" => t('Tooltip','invoiceStatusTooltip'),
                                        "integer_value" => true,
                                     );
        
        $options = $valid_types;
        $input_descriptors0[] = array(
                                        "name" => "invoice_type_id",
                                        "caption" => t('all','InvoiceType'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $invoice_type_id,
                                        "tooltipText" => t('Tooltip','invoiceTypeTooltip'),
                                        "integer_value" => true,
                                     );
    
        $options = $valid_users;
        $input_descriptors0[] = array(
                                        "name" => "user_id",
                                        "caption" => t('all','UserId'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $user_id,
                                        "tooltipText" => t('Tooltip','user_idTooltip'),
                                        "integer_value" => true,
                                     );

        $input_descriptors0[] = array(
                                        "name" => "invoice_date",
                                        "caption" => t('all','PaymentDate'),
                                        "type" => "date",
                                        "value" => $invoice_date,
                                        "min" => date("1970-m-01"),
                                     );
        
        $input_descriptors0[] = array(
                                        "name" => "invoice_notes",
                                        "caption" => t('ContactInfo','Notes'),
                                        "type" => "textarea",
                                        "content" => $invoice_notes,
                                     );
        
        // opening form
        open_form();
        
        // open tab wrapper
        open_tab_wrapper();
        
        // tab 0
        open_tab($navkeys, 0, true);
        
        $fieldset0_descriptor = array( "title" => t('title','Invoice') );
        
        open_fieldset($fieldset0_descriptor);
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_fieldset();
        
        close_tab($navkeys, 0);
        
        // tab 1
        open_tab($navkeys, 1);
    
        $fieldset1_descriptor = array( "title" => t('title','Items') );
        
        open_fieldset($fieldset1_descriptor);

        $input_descriptors1 = array();
        $input_descriptors1[] = array(
                                        "type" => "button",
                                        "name" => "addItem",
                                        "value" => "Add Item",
                                        "onclick" => "addTableRow()",
                                        "icon" => "plus-circle-fill",
                                      );
        
        $input_descriptors1[] = array(
                                        "name" => "counter",
                                        "type" => "hidden",
                                        "value" => "0",
                                     );
        
        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        echo '<table class="table table-striped table-hover my-2">'
           . '<tbody id="container">'
           . '<tr>';
        
        $headers = array(
                            t('title','Plan'),
                            t('all','Amount'),
                            t('all','Tax'),
                            t('ContactInfo','Notes'),
                            'Action(s)'
                       );
        
        foreach ($headers as $header) {
            printf("<th>%s</th>", $header);
        }
        
        echo '</tr>' . "\n";
        
        $this_id = rand();
        $itemRowId = sprintf("itemsRow%d", $this_id);
                
        printf('<tr id="%s">', $itemRowId);
                
        $this_select = str_replace("itemXXXXXXX[plan]", sprintf("item%d[plan]", $this_id), $planSelect);
        printf("<td>%s</td>", $this_select);
        
        $input_name_value = array(
                                    "amount" => $this_amount,
                                    "tax" => $this_tax_amount,
                                 );
        
        foreach ($input_name_value as $name => $value) {
            printf('<td><input type="number" class="form-control" min="0" step=".01" id="item%d_%s" name="item%d[%s]" value="%s"></td>',
                   $this_id, $name, $this_id, $name, htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'));
        }
        
        printf('<td><input type="text" class="form-control" id="item%d_%s" name="item%d[%s]" value="%s"></td>',
               $this_id, "notes", $this_id, "notes", htmlspecialchars((string)($this_notes ?? ''), ENT_QUOTES, 'UTF-8'));
        
        $onclick = sprintf("removeTableRow('%s')", $itemRowId);
        printf('<td><button type="button" name="remove" onclick="%s" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Remove Item</button></td>', $onclick);
        
        echo '</tr>'
           . '</tbody>'
           . '</table>';
        
        close_fieldset();
        
        close_tab($navkeys, 1);
        
        // close tab wrapper
        close_tab_wrapper();
        
        // descriptors 2
        $input_descriptors2 = array();
    
        $input_descriptors2[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        
        $input_descriptors2[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
        
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    }
    
    print_back_to_previous_page();
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
   
?>
