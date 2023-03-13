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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // setting table-related parameters first
    switch($configValues['FREERADIUS_VERSION']) {
    case '1':
        $tableSetting['postauth']['user'] = 'user';
        $tableSetting['postauth']['date'] = 'date';
        break;
        
    case '2':
    case '3':
    default:
        $tableSetting['postauth']['user'] = 'username';
        $tableSetting['postauth']['date'] = 'authdate';
        break;
    }
    
    // in other cases we just check that syntax is ok
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : date("Y-m-01");

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : date("Y-m-01", mktime(0, 0, 0, date('n') + 1, 1, date('Y')));

    $radiusReply = (array_key_exists('radiusReply', $_GET) && !empty(trim($_GET['radiusReply'])) &&
                    in_array(trim($_GET['radiusReply']), $valid_radiusReplys))
                 ? trim($_GET['radiusReply']) : $valid_radiusReplys[0];

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes");
    
    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array( 
                   "fullname" => t('all','Name'),
                   $tableSetting['postauth']['user'] => t('all','Username'),
                 );
    
    if (!$hiddenPassword) {
        $cols["pass"] = t('all','Password');
    }
    
    $date_label = $tableSetting['postauth']['date'];
    $cols[$date_label] = t('all','StartTime');
    
    if ($radiusReply == 'Any') {
        $cols["reply"] = t('all','RADIUSReply');
    } else {
        $cols[] = t('all','RADIUSReply');
    }
    
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    // validating user passed parameters

    $default_orderBy = array_keys($cols)[count($cols) - 2];
    $default_orderType = "desc";
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : $default_orderBy;

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : $default_orderType;
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";
    
    // print HTML prologue
    $title = t('Intro','replastconnect.php');
    $help = t('helpPage','replastconnect');
    
    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);


    include('include/management/pages_common.php');
    include('../common/includes/db_open.php');

    // pa is a placeholder in the SQL statements below
    // except for $username, which has been only partially escaped,
    // all other query parameters have been validated earlier.
    $sql_WHERE = array();
    if (!empty($username)) {
        $sql_WHERE[] = sprintf("pa.%s LIKE '%s%%'", $tableSetting['postauth']['user'],
                                                    $dbSocket->escapeSimple($username));
    }
    $sql_WHERE[] = sprintf("pa.%s BETWEEN '%s' AND '%s'", $tableSetting['postauth']['date'],
                                                          $dbSocket->escapeSimple($startdate),
                                                          $dbSocket->escapeSimple($enddate));
    if ($radiusReply != "Any") {
        $sql_WHERE[] = sprintf("pa.reply='%s'", $dbSocket->escapeSimple($radiusReply));
    }
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = sprintf("%s AS pa LEFT JOIN %s AS ui ON pa.%s = ui.username",
                                       $configValues['CONFIG_DB_TBL_RADPOSTAUTH'],
                                       $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                                       $tableSetting['postauth']['user']);
    $_SESSION['reportQuery'] = " WHERE " . implode(" AND ", $sql_WHERE);
    $_SESSION['reportType'] = "reportsLastConnectionAttempts";

    
    $sql = sprintf("SELECT CONCAT(COALESCE(ui.firstname, ''), ' ', COALESCE(ui.lastname, '')) AS fullname,
                           pa.%s AS username, pa.pass, pa.reply, pa.%s
                      FROM %s %s", $tableSetting['postauth']['user'], $tableSetting['postauth']['date'],
                                   $_SESSION['reportTable'], $_SESSION['reportQuery']);
    
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
        
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_params = array();
        if (!empty($startdate)) {
            $partial_query_params[] = sprintf("startdate=%s", $startdate);
        }
        if (!empty($enddate)) {
            $partial_query_params[] = sprintf("enddate=%s", $enddate);
        }
        if (!empty($username_enc)) {
            $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        }
        if (!empty($radiusReply)) {
            $partial_query_params[] = sprintf("radiusReply=%s", $radiusReply);
        }
        
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");

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
                $row[$i] = htmlspecialchars(trim($row[$i]), ENT_QUOTES, 'UTF-8');
            }

            // The table that is being produced is in the format of:
            // +-------------+-------------+---------------+-----------+-----------+
            // | fullname    | user        | pass (opt.)   | reply     | date      |   
            // +-------------+-------------+---------------+-----------+-----------+

            list($fullname, $user, $pass, $reply, $starttime) = $row;

            $fullname = (!empty($fullname) ? $fullname : "(n/a)");
            $reply = sprintf('<span class="text-%s">%s</span>',
                             (($reply == "Access-Reject") ? "danger" : "success"), $reply);

            $table_row = array( $fullname, $user );
            if (!$hiddenPassword) {
                $table_row[] = $pass;
            }
        
            $table_row[] = $reply;
            $table_row[] = $starttime;

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

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('../common/includes/db_close.php');
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
