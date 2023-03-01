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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("include/management/functions.php");
    include("../common/includes/layout.php");

	//setting values for the order by and order type variables
	isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "username";
	isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";

	$username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
	$username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $planname = (array_key_exists('planname', $_GET) && !empty(str_replace("%", "", trim($_GET['planname']))))
              ? str_replace("%", "", trim($_GET['planname'])) : "";
    $planname_enc = (!empty($planname)) ? htmlspecialchars($planname, ENT_QUOTES, 'UTF-8') : "";
    
	// we validate starting and ending dates
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    $cols = array(
                    "username" => t('all','Username'),
                    "planname" => t('all','PlanName'),
                    "sessiontime" => t('all','UsedTime'),
                    "plantimebank" => t('all','TotalTime'),
                    t('all','TotalTraffic')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query";
    if (!empty($username)) {
        $logQuery .= " for user $username";
    }
    
    if (!empty($planname)) {
        $logQuery .= "for plan $planname";
    }
    
    if (!empty($startdate)) {
         $logQuery .= " from $startdate";
    }
    if (!empty($enddate)) {
         $logQuery .= " to $enddate";
    }
    $logQuery .= "on page: ";
    $logDebugSQL = "";

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "static/js/pages_common.js",
    );
    
    $title = t('Intro','acctplans.php');
    $help = t('helpPage','acctplans');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    print_title_and_help($title, $help);


    // we can only use the $dbSocket after we have included '../common/includes/db_open.php' which initialzes the connection and the $dbSocket object
    include('../common/includes/db_open.php');
    include_once('include/management/pages_common.php');

	
    $sql_WHERE = array();
    $partial_query_params = array();
    
    $sql_WHERE[] = "ubi.username = ra.username";
    $sql_WHERE[] = "ubi.planname = bp.planname";
    
    $userExists = false;
    if (!empty($username)) {
        $sql_WHERE[] = sprintf("ubi.username = '%s'", $dbSocket->escapeSimple($username));
        $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        
        $userExists = user_exists($dbSocket, $username, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    }
    
    if (!empty($planname)) {
        $sql_WHERE[] = sprintf("bp.planname = '%s'", $dbSocket->escapeSimple($planname));
        $partial_query_params[] = sprintf("planname=%s", urlencode($planname_enc));
        
    }
    
    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("ra.AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_params[] = sprintf("startdate=%s", $startdate);
    }

    if (!empty($enddate)) {
        $sql_WHERE[] = sprintf("ra.AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_params[] = sprintf("enddate=%s", $enddate);
    }

    // setup php session variables for exporting
    $_SESSION['reportTable'] = sprintf("%s AS ubi, %s AS ra, %s AS bp", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                                        $configValues['CONFIG_DB_TBL_RADACCT'],
                                                                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']);
    $_SESSION['reportQuery'] = " WHERE " . implode(" AND ", $sql_WHERE) . " GROUP BY ubi.username";
    $_SESSION['reportType'] = "reportsPlansUsage";

    $sql = sprintf("SELECT ubi.username AS username, ubi.planname AS planname, SUM(ra.acctsessiontime) AS sessiontime,
                           SUM(ra.acctinputoctets) AS upload, SUM(ra.acctoutputoctets) AS download,
                           bp.plantimebank AS plantimebank, bp.planTimeType AS planTimeType
                      FROM %s %s", $_SESSION['reportTable'], $_SESSION['reportQuery']);
    $logDebugSQL .= "$sql;\n";
    $res = $dbSocket->query($sql);
    
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        $sql = sprintf("SELECT ubi.username AS username, ubi.planname AS planname, SUM(ra.acctsessiontime) AS sessiontime,
                           SUM(ra.acctinputoctets) AS upload, SUM(ra.acctoutputoctets) AS download,
                           bp.plantimebank AS plantimebank, bp.planTimeType AS planTimeType
                      FROM %s %s", $_SESSION['reportTable'], $_SESSION['reportQuery'])
             . sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        $partial_query_string = (count($partial_query_params) > 0)
                              ? ("&" . implode("&", $partial_query_params)) : "";
                              
        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                            'partial_query_string' => $partial_query_string,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        $descriptors['end'] = array();
        $descriptors['end'][] = array(
                                        'onclick' => "location.href='include/management/fileExport.php?reportFormat=csv'",
                                        'label' => 'CSV Export',
                                        'class' => 'btn-light',
                                     );
        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list( $this_username, $this_planname, $this_sessiontime,
                  $this_upload, $this_download, $this_plantimebank, $this_plantimetype ) = $row;
            
            $tmp = number_format(($this_sessiontime / $this_plantimebank) * 100, 2);
            $this_percentage = sprintf('<span style="color: %s">%s%%</span>', (($tmp - 100 > 0) ? "red" : "green"), $tmp);
        
            $this_sessiontime = time2str($this_sessiontime);
            $this_plantimebank = time2str($this_plantimebank);
        
            $this_traffic = toxbyte( $this_upload + $this_download );
            
            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-pos-edit.php?username=%s', urlencode($this_username), ), 'label' => t('Tooltip','UserEdit'), );
            
            $tooltip = get_tooltip_list_str($tooltip);
            
            // define table row
            $table_row = array( $this_username, $this_planname, $this_sessiontime, $this_plantimebank, $this_traffic );
            
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
                                'colspan' => $colspan,
                                'multiple_pages' => $drawNumberLinks
                           );
        $descriptor = array( 'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);
        
        if ($userExists) {
            
            echo '<div class="accordion m-2" id="accordion-parent">';
            include_once('include/management/userReports.php');
            userPlanInformation($username, 1);
            userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
            userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
            echo '</div>';
        }
    } else {
        $failureMsg = "Nothing to display";
    }

    include_once("include/management/actionMessages.php");

    include('../common/includes/db_close.php');

	include('include/config/logging.php');
    
    $inline_extra_js = ($userExists)
                     ? "window.onload = function() { setupAccordion() };" : "";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
