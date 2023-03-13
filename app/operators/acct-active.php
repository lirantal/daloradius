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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // validate this parameter before including menu
    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
                    ? str_replace("%", "", $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

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
                    "attribute" => t('all','Attribute'),
                    "maxtimeexpiration" => t('all','MaxTimeExpiration'),
                    "usedtime" => t('all','UsedTime'),
                    t('all','Status'),
                    t('all','Usage')    
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";
    $logDebugSQL = "";
    
    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','acctactive.php');
    $help = t('helpPage','acctactive');

    print_html_prologue($title, $langCode, array(), $extra_js);
    
    // start printing content
    print_title_and_help($title, $help);
    

    include('../common/includes/db_open.php');
    include('library/datediff.php');
    include('include/management/pages_common.php');
    
    $currdate = date("j M Y");
    
    //orig: used as maethod to get total rows - this is required for the pages_numbering.php page
    $sql = sprintf("SELECT DISTINCT(ra.username) AS username, rc.attribute AS attribute, rc.Value AS maxtimeexpiration,
                           SUM(ra.AcctSessionTime) AS usedtime
                      FROM %s AS ra, %s AS rc
                     WHERE ra.username=rc.username AND (rc.Attribute = 'Max-All-Session' OR rc.Attribute='Expiration')
                     GROUP BY ra.username", $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
            
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
    
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        $descriptors = array();

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        print_table_prologue($descriptors);

        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();
        
        while($row = $res->fetchRow()) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($username, $attribute, $maxtimeexpiration, $usedtime) = $row;
            
            
            $status = "Active";

            if ($attribute == "Expiration") {        
                if (datediff('d', $maxtimeexpiration, $currdate, false) > 0) {
                    $status = "Expired";
                }
            } else if ($attribute == "Max-All-Session") {        
                if ($usedtime >= $maxtimeexpiration) {
                    $status = "End";
                }
            }

            $usedtime = time2str($usedtime);

            $usage = "";
            if ($attribute == "Expiration") {
                $difference = datediff('d', $maxtimeexpiration, $currdate, false);
                if ($difference > 0) {
                    $usage = "<h100> " . " $difference days since expired" . "</h100> ";
                } else {
                    $usage = substr($difference, 1) . " days until expiration";
                }
            } else if ($attribute == "Max-All-Session") {        
                if ($status == "End") {
                    $usage = "<h100> " . abs($maxtimeexpiration - $usedtime) . " seconds overdue credit" . "</h100>";
                } else {
                    $usage = abs($maxtimeexpiration - $usedtime) . " left on credit";
                }
            } 

            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($username), ), 'label' => t('Tooltip','UserEdit'), );
        
            $tooltip = get_tooltip_list_str($tooltip);
        
            $table_row = array( $tooltip, $attribute, $maxtimeexpiration, $usedtime, $status, $usage );

            // print table row
            print_table_row($table_row);
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
        $descriptor = array(  'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);
        
    } else {
        $failureMsg = "Nothing to display";
    }
    
    include_once("include/management/actionMessages.php");
    
    include('../common/includes/db_close.php');

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
