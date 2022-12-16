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
    include_once('library/config_read.php');

    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $sqlfields = (array_key_exists('sqlfields', $_GET) && !empty($_GET['sqlfields']) && is_array($_GET['sqlfields']) &&
                  array_intersect($_GET['sqlfields'], array_keys($bill_history_query_options_all)) == $_GET['sqlfields'])
               ? $_GET['sqlfields'] : $bill_history_query_options_default;
    
    $cols = array();
    foreach ($sqlfields as $sqlfield) {
        $cols[$sqlfield] = $bill_history_query_options_all[$sqlfield];
    }
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($bill_history_query_options_all)))
             ? $_GET['orderBy'] : array_keys($bill_history_query_options_all)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";
    
    //~ $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  //~ preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  //~ checkdate($m[2], $m[3], $m[1]))
               //~ ? $_GET['startdate'] : "";

    //~ $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                //~ preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                //~ checkdate($m[2], $m[3], $m[1]))
             //~ ? $_GET['enddate'] : "";

    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $billaction = (array_key_exists('billaction', $_GET) && isset($_GET['billaction']) &&
                   in_array($_GET['billaction'], array_slice($valid_billactions, 1))) // avoid inserting "Any" in the SQL query
                ? $_GET['billaction'] : "";
    $billaction_enc = (!empty($billaction)) ? htmlspecialchars($billaction, ENT_QUOTES, 'UTF-8') : "";
    
    // print HTML prologue
    $title = t('Intro','billhistoryquery.php');
    $help = t('helpPage','billhistoryquery');
    
    print_html_prologue($title, $langCode);
    

    //feed the sidebar variables
    $billing_date_startdate = $startdate;
    $billing_date_enddate = $enddate;
    $billing_history_username = $username;
    $billing_history_billaction = $billaction;

    include("menu-bill-history.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    // preparing the custom query
    
    $sql_WHERE = array();
    $partial_query_string_pieces = array();
    
    foreach ($sqlfields as $sqlfield) {
        $partial_query_string_pieces[] = sprintf("sqlfields[]=%s", $sqlfield);
    }
    
    //~ if (!empty($startdate)) {
        //~ $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
        //~ $partial_query_string_pieces[] = sprintf("startdate=%s", $startdate);
    //~ }
    
    //~ if (!empty($startdate)) {
        //~ $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
        //~ $partial_query_string_pieces[] = sprintf("enddate=%s", $enddate);
    //~ }
    
    if (!empty($username)) {
        $sql_WHERE[] = sprintf("username LIKE '%s%%'", $dbSocket->escapeSimple($username));
        $partial_query_string_pieces[] = sprintf("username=%s", $username_enc);
    }
    
    if (!empty($billaction)) {
        $sql_WHERE[] = sprintf("billaction LIKE '%s%%'", $dbSocket->escapeSimple($billaction));
        $partial_query_string_pieces[] = sprintf("billaction=%s", $billaction_enc);
    }
    
    // executing the custom query

    $sql = sprintf("SELECT %s FROM %s", implode(", ", $sqlfields), $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY']);
    
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }

    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
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
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (count($partial_query_string_pieces) > 0)
                              ? "&" . implode("&", $partial_query_string_pieces) : "";
        
        echo '<table border="0" class="table1">'
           . '<thead>';
            
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>' . '</tr>';
        }

        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        echo "</tr>";
        
        echo '</thead>'
           . '<tbody>';

        // inserting the values of each field from the database to the table
        $count = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            printf('<tr id="row-%d">', $count);
            foreach ($sqlfields as $field) {
                printf("<td>%s</td>", htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8'));
            }
            echo '</tr>';
            $count++;
        }

        echo '</tbody>';

        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);

        echo '</table>';
    
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
        
    include('library/closedb.php');    

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
