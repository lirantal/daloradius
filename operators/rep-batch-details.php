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

    include('../common/includes/config_read.php');
    //~ include('library/check_operator_perm.php');
    
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");
    include_once("include/management/functions.php");

    // validate this parameter before including menu
    $batch_name = (array_key_exists('batch_name', $_GET) && !empty(str_replace("%", "", trim($_GET['batch_name']))))
                ? str_replace("%", "", trim($_GET['batch_name'])) : "";
    $batch_name_enc = (!empty($batch_name)) ? htmlspecialchars($batch_name, ENT_QUOTES, 'UTF-8') : "";

    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // table1
    $cols1 = array(
                    t('all','BatchName'),
                    t('all','HotSpot'),
                    t('all','BatchStatus'),
                    t('all','TotalUsers'),
                    t('all','ActiveUsers'),
                    t('all','PlanName'),
                    t('all','PlanCost'),
                    t('all','BatchCost'),
                    t('all','CreationDate'),
                    t('all','CreationBy')
                  );
    $colspan1 = count($cols1);
    $half_colspan1 = intval($colspan1 / 2);

    // table2
    $cols2 = array(
                    'selected',
                    'username' => t('all','Username'),
                    'status' => "Accounted",
                    'acctstarttime' => "First accounted on"
                  );
    $colspan2 = count($cols2);
    $half_colspan2 = intval($colspan2 / 2);

    $param_cols2 = array();
    foreach ($cols2 as $k => $v) { if (!is_int($k)) { $param_cols2[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols2)))
             ? $_GET['orderBy'] : array_keys($param_cols2)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    $log = "visited page: ";
    $logQuery = "performed query for batch [$batch_name] on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

   
    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );
    
    $title = t('Intro','repbatchdetails.php');
    $help = t('helpPage','repbatchdetails');

    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    // get $batch_id
    $batch_id = -1;

    if ($batch_name) {
        $sql = sprintf("SELECT bh.id FROM %s AS bh WHERE bh.batch_name='%s' LIMIT 1",
                       $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $dbSocket->escapeSimple($batch_name));
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        $logDebugSQL .= "$sql;\n";

        if ($numrows > 0) {
            $row = $res->fetchRow();
            $batch_id = intval($row[0]);
        }
    }

    if ($batch_id > 0) {

        $_SESSION['reportParams']['batch_id'] = $batch_id;

        $sql = "SELECT bh.id, bh.batch_name, bh.batch_description, bh.batch_status, COUNT(DISTINCT(ubi.id)) AS total_users,
                       COUNT(DISTINCT(ra.username)) AS active_users, ubi.planname, bp.plancost, bp.plancurrency,
                       hs.name AS HotspotName, bh.creationdate, bh.creationby, bh.updatedate, bh.updateby
                  FROM %s AS bh LEFT JOIN %s AS ubi ON bh.id = ubi.batch_id
                                LEFT JOIN %s AS bp ON bp.planname = ubi.planname
                                LEFT JOIN %s AS hs ON bh.hotspot_id = hs.id
                                LEFT JOIN %s AS ra ON ra.username = ubi.username
                 WHERE bh.batch_name = '%s'
                 GROUP BY bh.batch_name";
        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                             $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                             $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                             $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                             $configValues['CONFIG_DB_TBL_RADACCT'],
                             $dbSocket->escapeSimple($batch_name));



        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

        $additional_controls = array();
        $additional_controls[] = array(
                                        'onclick' => sprintf("window.open('include/common/notificationsBatchDetails.php?batch_name=%s&destination=download')", urlencode($batch_name_enc)),
                                        'label' => 'Download Invoice',
                                        'class' => 'btn-light',
                                      );
        $additional_controls[] = array(
                                        'onclick' => sprintf("location.href='include/common/notificationsBatchDetails.php?batch_name=%s&destination=email'", urlencode($batch_name_enc)),
                                        'label' => 'Email Invoice to Business/Hotspot',
                                        'class' => 'btn-light',
                                      );
        $additional_controls[] = array(
                                        'onclick' => "location.href='include/management/fileExport.php?reportFormat=csv&reportType=reportsBatchTotalUsers'",
                                        'label' => 'CSV Export',
                                        'class' => 'btn-light',
                                     );

        $descriptors = array( 'end' => $additional_controls );

        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        foreach ($cols1 as $caption) {
            printf("<th>%s</th>", $caption);
        }

        // closes table header, opens table body
        print_table_middle();

        // table1 content
        $count = 0;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
        
        
            list($id, $this_batch_name, $this_batch_desc, $batch_status, $total_users, $active_users, $planname,
                 $plancost, $plancurrency, $hotspot_name, $creationdate, $creationby, $updatedate, $updateby) = $row;
        
            $total_users = intval($total_users);
            $active_users = intval($active_users);
            $plancost = intval($plancost);

            $batch_cost = $active_users * $plancost;

            if (empty($this_batch_desc)) {
                $this_batch_desc = "(n/a)";
            }

            if (empty($plan_name)) {
                $plan_name = "(n/d)";
            }

            if (empty($hotspot_name)) {
                $hotspot_name = "(n/d)";
            }

            // tooltip stuff
            $tooltip = array(
                                'subject' => $this_batch_name,
                                'actions' => array(),
                                'content' => sprintf('<strong>%s</strong>:<br>%s', t('all','batchDescription'), $this_batch_desc),
                            );
            
            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // build table row
            $table_row = array( $tooltip, $hotspot_name, $batch_status, $total_users, $active_users,
                                $plan_name, $plancost, $batch_cost, $creationdate, $creationby );

            // print table row
            print_table_row($table_row);

            $count++;
        }

        print_table_bottom();

        // setup php session variables for exporting
        $_SESSION['reportTable'] = "";
        //reportQuery is assigned below to the SQL statement  in $sql
        $_SESSION['reportQuery'] = "";
        $_SESSION['reportType'] = "reportsBatchActiveUsers";

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_params = array( sprintf('batch_name=%s', urlencode($batch_name_enc)) );

        $sql_WHERE = array( "ubi.batch_id=bh.id" );
        $sql_WHERE[] = sprintf("ubi.batch_id=%d", $batch_id);
        
        if (!empty($username)) {
            $sql_WHERE[] = sprintf("ubi.username LIKE '%s%%'", $dbSocket->escapeSimple($username));
            $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        }

        $sql = "SELECT ubi.username, IFNULL(ra.acctstarttime, '0') AS status, MIN(ra.acctstarttime)
                  FROM %s AS ubi LEFT JOIN %s AS ra ON ubi.username=ra.username, %s AS bh 
                 WHERE %s GROUP BY ubi.username";

        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                             $configValues['CONFIG_DB_TBL_RADACCT'],
                             $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                             implode(" AND ", $sql_WHERE));

        // assigning the session reportQuery
        $_SESSION['reportQuery'] = $sql;

        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        $logDebugSQL .= "$sql;\n";

        echo "<h4>Users in this batch</h4>";
        
        $form0_descriptor = array( "method" => "GET", "name" => "form_" . rand(), );
        open_form($form0_descriptor);
        
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "name" => "orderBy",
                                        "value" => $orderBy,
                                     );
        
        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "name" => "orderType",
                                        "value" => $orderType,
                                     );
                                     
        $input_descriptors0[] = array(
                                        "type" => "hidden",
                                        "name" => "batch_name",
                                        "value" => $batch_name,
                                     );
                                     
        $input_descriptors0[] = array(
                                        "type" => "text",
                                        "name" => "username",
                                        "value" => $username,
                                        "caption" => t('button','SearchUsers'),
                                     );
        
        $input_descriptors0[] = array(
                                        "type" => "submit",
                                        "value" => t('button','SearchUsers'),
                                        "icon" => "search",
                                        "name" => "button_" . rand(),
                                     );
        
        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }
        
        close_form();

        if ($numrows > 0) {
            
            /* START - Related to pages_numbering.php */

            // when $numrows is set, $maxPage is calculated inside this include file
            include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                                  // the CONFIG_IFACE_TABLES_LISTING variable from the config file

            // here we decide if page numbers should be shown
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

            /* END */

            // we execute and log the actual query
            $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $per_page_numrows = $res->numRows();

            // the partial query is built starting from user input
            // and for being passed to setupNumbering and setupLinks functions
            $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");

            // this can be passed as form attribute and
            // printTableFormControls function parameter
            $action = "mng-del.php";
            $form_name = "form_" . rand();

            // we prepare the "controls bar" (aka the table prologue bar)
            $additional_controls = array();
            $additional_controls[] = array(
                                    'onclick' => sprintf("removeCheckbox('%s','mng-del.php')", $form_name),
                                    'label' => 'Delete',
                                    'class' => 'btn-danger',
                                  );

            $additional_controls[] = array(
                                    'onclick' => sprintf("disableCheckbox('%s','library/ajax/user_actions.php')", $form_name),
                                    'label' => 'Disable',
                                    'class' => 'btn-primary',
                                  );
            $additional_controls[] = array(
                                    'onclick' => sprintf("enableCheckbox('%s','library/ajax/user_actions.php')", $form_name),
                                    'label' => 'Enable',
                                    'class' => 'btn-secondary',
                                  );

            $descriptors = array();

            $descriptors['start'] = array( 'common_controls' => 'username[]', 'additional_controls' => $additional_controls );

            $params = array(
                                'num_rows' => $numrows,
                                'rows_per_page' => $rowsPerPage,
                                'page_num' => $pageNum,
                                'order_by' => $orderBy,
                                'order_type' => $orderType,
                                'partial_query_string' => $partial_query_string
                            );
            $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
            
            $descriptors['end'] = array();
            $descriptors['end'][] = array(
                                            'onclick' => "location.href='include/management/fileExport.php?reportFormat=csv'",
                                            'label' => 'Active Users CSV Export',
                                            'class' => 'btn-light',
                                         );

            print_table_prologue($descriptors);

            $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => $form_name ), );

            // print table top
            print_table_top($form_descriptor);

            // second line of table2 header
            printTableHead($cols2, $orderBy, $orderType, $partial_query_string);

            // closes table header, opens table body
            print_table_middle();

            // table2 content
            $count = 0;
            while ($row = $res->fetchRow()) {
                $rowlen = count($row);

                // escape row elements
                for ($i = 0; $i < $rowlen; $i++) {
                    $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                }

                list($username, $active, $starttime) = $row;
                $badge = ($active !== '0') ? "success" : "danger";
                if (empty($starttime)) {
                    $starttime = "(n/a)";
                }
                
                $status = sprintf('<span class="badge bg-%s ms-1">%s</span>', $badge, (($active !== '0') ? "yes" : "no"));
                
                // check if user is disabled
                $disabled = in_array('daloRADIUS-Disabled-Users',
                                     get_user_group_mappings($dbSocket, $username));
                
                $img_format = '<i class="bi bi-%s-circle-fill text-%s me-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="%s"></i>';

                $img = ($disabled)
                     ? sprintf($img_format, 'dash', 'danger', 'disabled')
                     : sprintf($img_format, 'check', 'success', 'enabled');
                
                $ajax_id = "divContainerUserInfo_" . $count;
                $param = sprintf('username=%s', urlencode($username));
                $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
                $tooltip = array(
                                    'subject' => $img . $username,
                                    'onclick' => $onclick,
                                    'ajax_id' => $ajax_id,
                                    'actions' => array(),
                                );
                $tooltip['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($username), ), 'label' => t('Tooltip','UserEdit'), );
                if ($active !== '0') {
                    $tooltip['actions'][] = array( 'href' => sprintf('acct-username.php?username=%s', urlencode($username), ), 'label' => t('all','Accounting'), );
                }
                
                // create tooltip
                $tooltip = get_tooltip_list_str($tooltip);
                
                // create checkbox
                $d = array( 'name' => 'username[]', 'value' => $username );
                $checkbox = get_checkbox_str($d);
                
                $table_row = array( $checkbox, $tooltip, $status, $starttime );

                // print table row
                print_table_row($table_row);
                
                $count++;
            }

            // close tbody,
            // print tfoot
            // and close table + form (if any)
            $table_foot = array(
                                    'num_rows' => $numrows,
                                    'rows_per_page' => $per_page_numrows,
                                    'colspan' => $colspan2,
                                    'multiple_pages' => $drawNumberLinks
                               );
            $descriptor = array( 'form' => $form_descriptor, 'table_foot' => $table_foot );

            print_table_bottom($descriptor);

            // get and print "links"
            $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
            printLinks($links, $drawNumberLinks);

        } else {
            $failureMsg = "No active users in this batch";
        }

    } else {
        $failureMsg = "Batch name not valid";
    }

    include_once("include/management/actionMessages.php");

    include('../common/includes/db_close.php');

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
