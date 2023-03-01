<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@enginx.com>
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
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('../common/includes/db_open.php');
    
    // get valid statuses
    $sql = sprintf("SELECT id, value FROM %s", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_statuses = array();
    while ($row = $res->fetchrow()) {
        list($id, $value) = $row;
        
        $valid_statuses["status-$id"] = $value;
    }
    
    // get valid types
    $sql = sprintf("SELECT id, value FROM %s", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_types = array();
    while ($row = $res->fetchrow()) {
        list($id, $value) = $row;
        
        $valid_types["type-$id"] = $value;
    }
    
    // get valid users
    $sql = sprintf("SELECT id, username FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_users = array();
    while ($row = $res->fetchrow()) {
        list($id, $value) = $row;
        
        $valid_users["user-$id"] = $value;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $invoice_id = (array_key_exists('invoice_id', $_POST) && intval(trim($_POST['invoice_id'])) > 0)
                    ? intval(trim($_POST['invoice_id'])) : "";
    } else {
        $invoice_id = (array_key_exists('invoice_id', $_REQUEST) && intval(trim($_REQUEST['invoice_id'])) > 0)
                    ? intval(trim($_REQUEST['invoice_id'])) : "";
    }
    
    // check if this invoice exists
    $sql = sprintf("SELECT COUNT(id) FROM %s WHERE id=%d", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $invoice_id);
    $res = $dbSocket->query($sql);
    
    $exists = intval($res->fetchrow()[0]) == 1;

    if (!$exists) {
        // we reset the invoice if it does not exist
        $invoice_id = "";
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            if (empty($invoice_id)) {
                // required
                $failureMsg = "invalid or empty invoice id, please specify a valid invoice id to edit.";
                $logAction .= "invalid or empty invoice id on page: ";
            } else {
                $sql_SET = array();
                
                // required later
                $currDate = date('Y-m-d H:i:s');
                $currBy = $operator;
            
                $sql_SET[] = sprintf("updatedate='%s'", $currDate);
                $sql_SET[] = sprintf("updateby='%s'", $currBy);
            
                $user_id = (array_key_exists('user_id', $_POST) && !empty(trim($_POST['user_id'])) &&
                                    in_array(trim($_POST['user_id']), array_keys($valid_users)))
                                 ? intval(str_replace("user-", "", trim($_POST['user_id']))) : "";
                if (!empty($user_id)) {
                    $sql_SET[] = sprintf("user_id=%d", $user_id);
                }
            
                $invoice_type_id = (array_key_exists('invoice_type_id', $_POST) && !empty(trim($_POST['invoice_type_id'])) &&
                                    in_array(trim($_POST['invoice_type_id']), array_keys($valid_types)))
                                 ? intval(str_replace("type-", "", trim($_POST['invoice_type_id']))) : "";
                if (!empty($invoice_type_id)) {
                    $sql_SET[] = sprintf("type_id=%d", $invoice_type_id);
                }
            
                $invoice_status_id = (array_key_exists('invoice_status_id', $_POST) && !empty(trim($_POST['invoice_status_id'])) &&
                                    in_array(trim($_POST['invoice_status_id']), array_keys($valid_statuses)))
                                 ? intval(str_replace("status-", "", trim($_POST['invoice_status_id']))) : "";
                if (!empty($invoice_status_id)) {
                    $sql_SET[] = sprintf("status_id=%d", $invoice_status_id);
                }
            
                $invoice_date = (
                                    array_key_exists('invoice_date', $_POST) &&
                                    !empty(trim($_POST['invoice_date'])) &&
                                    preg_match(DATE_REGEX, trim($_POST['invoice_date']), $m) !== false &&
                                    checkdate($m[2], $m[3], $m[1])
                                ) ? trim($_POST['invoice_date']) : "";
                if (!empty($invoice_date)) {
                    $sql_SET[] = sprintf("date='%s'", $invoice_date);
                }
            
                $invoice_notes = (array_key_exists('invoice_notes', $_POST) && !empty(trim($_POST['invoice_notes'])))
                               ? trim($_POST['invoice_notes']) : "";
                if (!empty($invoice_notes)) {
                    $sql_SET[] = sprintf("notes='%s'", $dbSocket->escapeSimple($invoice_notes));
                }
            
                $sql = sprintf("UPDATE %s SET ", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'])
                     . implode(", ", $sql_SET)
                     . sprintf(" WHERE id=%d", $invoice);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $items = add_invoice_items($dbSocket, $invoice_id, true);
                    $successMsg = sprintf("Successfully added new invoice (id: #<strong>%d</strong>) with %d item(s)",
                                          $invoice_id, $items);
                    $logAction .= sprintf("Successfully added new invoice [id: #%d, items: %d] on page: ",
                                          $invoice_id, $items);
                } else {
                    $failureMsg = sprintf("Failed to add new invoice (id: #<strong>%d</strong>)", $invoice_id);
                    $logAction .= sprintf("Failed to add new invoice [id: #%d] on page: ", $payment_id);
                }
            
            }
        
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    $inline_extra_js = "";
    
    if (empty($invoice_id)) {
        $failureMsg = "invalid or empty invoice id, please specify a valid invoice id to edit.";
        $logAction .= "invalid or empty invoice id on page: ";
    } else {
        // get invoice details
        $sql = sprintf("SELECT a.id, a.date, a.status_id, a.type_id, a.user_id, a.notes, b.contactperson, b.username,
                               b.city, b.state, f.value AS type, c.value AS status, COALESCE(e2.totalpayed, 0) AS totalpayed,
                               COALESCE(d2.totalbilled, 0) AS totalbilled
                          FROM %s AS a INNER JOIN %s AS b ON a.user_id = b.id
                                       INNER JOIN %s AS c ON a.status_id = c.id
                                       INNER JOIN %s AS f ON a.type_id = f.id
                                        LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id, amount,
                                                          tax_amount, notes, plan_id
                                                     FROM %s AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id = a.id
                                        LEFT JOIN %s AS bp2 ON bp2.id = d2.plan_id
                                        LEFT JOIN (SELECT SUM(e.amount) as totalpayed, invoice_id
                                                     FROM %s AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id = a.id
                         WHERE a.id = %d
                         GROUP BY a.id",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'], $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                       $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'], $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'],
                       $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'], $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                       $configValues['CONFIG_DB_TBL_DALOPAYMENTS'], $invoice_id);
        
        $res = $dbSocket->query($sql);
		$logDebugSQL .= "$sql;\n";
	
		$edit_invoiceid = $invoice_id;
		$row = $res->fetchRow();
        
        list(
                $invoice_id, $invoice_date, $invoice_status_id, $invoice_type_id, $user_id,
                $invoice_notes, $contactperson, $username, $city, $state, $type, $status,
                $totalpayed, $totalbilled
            ) = $row;
        
        // select for active plans
        $planSelect = '<select class="form-select" name="itemXXXXXXX[plan]">';
        
        
        $sql = sprintf("SELECT DISTINCT(planName), id
                          FROM %s WHERE planActive = 'yes'
                         ORDER BY planName ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']);
        $res = $dbSocket->query($sql);

        while ($row = $res->fetchRow()) {
            list($planName, $id) = $row;
            
            $planSelect .= sprintf('<option value="%d">%s</option>',
                                   intval($id), htmlspecialchars($planName, ENT_QUOTES, 'UTF-8'));
        }
        
        $planSelect .= '</select>';
        
        $inline_extra_js = <<<EOF

function addTableRow() {
    var container = document.getElementById('container'),
        counter = document.getElementById('counter'),
        num = parseInt(counter.value) + 1,
        trContainer = document.createElement('tr'),
        trIdName = 'itemsRow' + num,
        plansSelect = '$planSelect',
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
	td3.innerHTML = `<input type="number" class="form-control" min="0" step=".01" id="item\${num}_tax" name="\${td3_name}">`;

	var td4 = document.createElement('td');
	td4.innerHTML = `<input type="text" class="form-control" id="item\${num}_notes" name="\${td4_name}">`;

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
	
	include('../common/includes/db_close.php');
	

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/dynamic_attributes.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','billinvoiceedit.php');
    $help = t('helpPage','billinvoiceedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js, "", $inline_extra_js);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    if (!empty($invoice_id)) {

        // print customer info
        printf('<div><strong>Customer</strong>: <a href="bill-pos-edit.php?username=%s">%s</a><br>',
               htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
               htmlspecialchars($contactperson, ENT_QUOTES, 'UTF-8'));
        
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
		
        echo '<div class="btn-group my-3" role="group">';
        printf('<a class="btn btn-primary" href="bill-payments-new.php?payment_invoice_id=%d">%s</a> ', $invoice_id, "New Payment");
        printf('<a class="btn btn-secondary" href="bill-payments-list.php?payment_invoice_id=%d">%s</a>', $invoice_id, "Show Payments");
        echo '</div>';
        
        echo '</div>';

        // set navbar stuff
        $navkeys = array( 'Invoice', 'Items', );

        // print navbar controls
        print_tab_header($navkeys);
    
        // descriptors 0
        $input_descriptors0 = array();
        $input_descriptors0[] = array(
                                        "name" => "totalbilled",
                                        "caption" => t('all','TotalBilled'),
                                        "type" => "number",
                                        "value" => $totalbilled,
                                        "min" => 0,
                                        "step" => ".01",
                                        "disabled" => true,
                                     );
                                     
        $input_descriptors0[] = array(
                                        "name" => "totalpayed",
                                        "caption" => t('all','TotalPayed'),
                                        "type" => "number",
                                        "value" => $totalpayed,
                                        "min" => 0,
                                        "step" => ".01",
                                        "disabled" => true,
                                     );
        
        $balance = floatval($totalpayed - $totalbilled);
        $input_descriptors0[] = array(
                                        "name" => "balance",
                                        "caption" => t('all','Balance'),
                                        "type" => "number",
                                        "value" => $balance,
                                        "min" => "0",
                                        "step" => ".01",
                                        "disabled" => true,
                                     );
        
        $options = $valid_statuses;
        array_unshift($options , '');
		$input_descriptors0[] = array(
                                        "name" => "invoice_status_id",
                                        "caption" => t('all','InvoiceStatus'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => "status-$invoice_status_id",
                                        "tooltipText" => t('Tooltip','invoiceStatusTooltip'),
                                     );
        
        $options = $valid_types;
        array_unshift($options , '');
		$input_descriptors0[] = array(
                                        "name" => "invoice_type_id",
                                        "caption" => t('all','InvoiceType'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => "type-$invoice_type_id",
                                        "tooltipText" => t('Tooltip','invoiceTypeTooltip'),
                                     );
    
        $options = $valid_users;
        array_unshift($options , '');
		$input_descriptors0[] = array(
                                        "name" => "user_id",
                                        "caption" => t('all','UserId'),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => "user-$user_id",
                                        "tooltipText" => t('Tooltip','user_idTooltip'),
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


        // descriptors 2
        $input_descriptors2 = array();

        $input_descriptors2[] = array(
                                        "name" => "invoice_id",
                                        "type" => "hidden",
                                        "value" => $invoice_id,
                                     );
    
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
        
        $onclick = "window.location.href='include/common/notificationsUserInvoice.php?destination=%s&invoice_id=%d'";
        $button_descriptors1 = array();
        $button_descriptors1[] = array(
                                        "type" => "button",
                                        "name" => "PreviewInvoice",
                                        "value" => "Preview Invoice",
                                        "onclick" => sprintf($onclick, 'preview', $invoice_id),
                                      );

        $button_descriptors1[] = array(
                                        "type" => "button",
                                        "name" => "DownloadInvoice",
                                        "value" => "Download Invoice",
                                        "onclick" => sprintf($onclick, 'download', $invoice_id),
                                      );
        
        $button_descriptors1[] = array(
                                        "type" => "button",
                                        "name" => "EmailInvoice",
                                        "value" => "Email Invoice to Customer",
                                        "onclick" => sprintf($onclick, 'email', $invoice_id),
                                      );
        
        // custom actions
        echo <<<EOF
    <div class="dropdown dropup">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Actions
        </button>
  
        <ul class="dropdown-menu">
EOF;

        foreach ($button_descriptors1 as $desc) {
            printf('<li><button class="dropdown-item" name="%s" onclick="%s">%s</button></li>', $desc['name'], $desc['onclick'], $desc['value']);
        }


        echo <<<EOF
        </ul>
    </div>
EOF;
        
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
                            t('all','Tax'),
                            t('all','Amount'),
                            t('ContactInfo','Notes'),
                            'Action(s)'
                       );
        
        foreach ($headers as $header) {
            printf("<th>%s</th>", $header);
        }
        
        echo '</tr>' . "\n";

		if (!empty($invoice_id)) {
		
            include('../common/includes/db_open.php');
		
            $sql = sprintf("SELECT a.id, a.plan_id, a.amount, a.tax_amount, a.notes, b.planName
                              FROM %s a LEFT JOIN %s b ON a.plan_id=b.id
                             WHERE a.invoice_id=%d
                             ORDER BY a.id ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'],
                                                 $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                 $invoice_id);
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            while($row = $res->fetchRow()) {
                
                list( $this_id, $this_plan_id, $this_amount, $this_tax_amount, $this_notes, $this_planName ) = $row;
                
                $itemRowId = sprintf("itemsRow_%d", $this_id);
                
                printf('<tr id="%s">', $itemRowId);
                
                $this_select = str_replace("itemXXXXXXX[plan]", sprintf("item%d[plan]", $this_id), $planSelect);
                printf("<td>%s</td>", $this_select);
                
                $input_name_value = array(
                                            "amount" => $this_amount,
                                            "tax" => $this_tax_amount,
                                         );
                
                foreach ($input_name_value as $name => $value) {
                    printf('<td><input type="number" class="form-control" min="0" step=".01" id="item%d_%s" name="item%d[%s]" value="%s"></td>',
                           $this_id, $name, $this_id, $name, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                }
                
                printf('<td><input type="text" class="form-control" id="item%d_%s" name="item%d[%s]" value="%s"></td>',
                       $this_id, "notes", $this_id, "notes", htmlspecialchars($this_notes, ENT_QUOTES, 'UTF-8'));
                
                $onclick = sprintf("removeTableRow('%s')", $itemRowId);
                printf('<td><button type="button" name="remove" onclick="%s" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Remove Item</button></td>', $onclick);
                
                echo '</tr>' . "\n";
                
            }
            
            include('../common/includes/db_close.php');

        }
        
        
        echo '</tbody>'
           . '</table>';
    
        close_fieldset();
        
        close_tab($navkeys, 1);
    
        // close tab wrapper
        close_tab_wrapper();
    
        foreach ($input_descriptors2 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();
    
    }
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
	
?>
