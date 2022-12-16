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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");
    
    $limit = (array_key_exists('limit', $_GET) && isset($_GET['limit']) && intval($_GET['limit']) > 0)
           ? intval($_GET['limit']) : "";
    
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    

    // print HTML prologue
    $title = t('Intro','reptopusers.php');
    $help = t('helpPage','reptopusers') . " " . $orderBy;
    
    print_html_prologue($title, $langCode);
    
    include ("menu-reports.php");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    'username' => t('all','Username'),
                    'framedipaddress' => t('all','IPAddress'),
                    'acctstarttime' => t('all','StartTime'),
                    'acctstoptime' => t('all','StopTime'),
                    'Time' => t('all','TotalTime'),
                    'Upload' => t('all','Upload') . " (" . t('all','Bytes') . ")",
                    'Download' => t('all','Download') . " (" . t('all','Bytes') . ")",
                    'acctterminatecause' => t('all','Termination'),
                    'nasipaddress' => t('all','NASIPAddress')
    );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for [order by: $orderBy";
    if (!empty($limit)) {
        $logQuery .= " / limit: $limit";
    }
    $logQuery .= "] on page: ";

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    // the partial query is built starting from user input
    // and for being passed to setupNumbering and setupLinks functions
    $partial_query_params = array();
    
    // creating $sql_WHERE for SQL query
    $sql_WHERE = array();
    $sql_WHERE[] = "AcctStopTime > '0000-00-00 00:00:01'";
    if (!empty($startdate)) {
        $partial_query_params[] = sprintf("startdate=%s", urlencode(htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
    }
    
    if (!empty($enddate)) {
        $partial_query_params[] = sprintf("enddate=%s", urlencode(htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($enddate));
    }
    
    if (!empty($username)) {
        $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        $sql_WHERE[] = sprintf("username LIKE '%s%%'", $dbSocket->escapeSimple($username));
    }
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = (count($sql_WHERE) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE) : "";
    $_SESSION['reportType'] = "TopUsers";
    
    $sql = "SELECT DISTINCT(ra.username) AS username, ra.FramedIPAddress, ra.AcctStartTime, MAX(ra.AcctStopTime), 
                   SUM(ra.AcctSessionTime) AS Time, SUM(ra.AcctInputOctets) AS Upload,
                   SUM(ra.AcctOutputOctets) AS Download, ra.AcctTerminateCause, ra.NASIPAddress,
                   SUM(ra.AcctInputOctets + ra.AcctOutputOctets) AS Bandwidth
            FROM " . $configValues['CONFIG_DB_TBL_RADACCT'] . " AS ra";
    
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
    }
    
    $sql .= " GROUP BY username";
    
    if (!empty($limit)) {
        $partial_query_params[] = sprintf("limit=%d", $limit);
        $sql .= sprintf(" LIMIT %d", $limit);
    }
    
    $logDebugSQL = "$sql;\n";
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
                     
        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");
?>

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
                <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>

            </tr>
            <tr>
<?php
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
        </thead>
        
        <tbody>
<?php
        $count = 0;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
            
            // print table row
            printf('<tr id="row-%d">', $count);
            
            for ($i = 0; $i < $rowlen; $i++) {
                
                if ($i == 4) {
                    //~ Time
                    $row[$i] = time2str($row[$i]);
                } else if ($i == 5 || $i == 6) {
                    //~ Upload, Download
                    $row[$i] = toxbyte($row[$i]);
                }
                
                printf("<td>%s</td>", htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8'));
            }
            
            echo "</tr>";
            
            $count++;
        }
?>
        </tbody>

<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>

    </table>
<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
