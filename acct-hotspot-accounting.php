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
    include("library/layout.php");

    $hotspot = (array_key_exists('hotspot', $_GET) && isset($_GET['hotspot']) && !is_array($_GET['hotspot']))
             ? array( $_GET['hotspot'] ) : $_GET['hotspot'];
    
    $tmp = array();
    foreach ($hotspot as $item) {
        $item = str_replace("%", "", trim($item));
        if (empty($item) && in_array($item, $tmp)) {
            continue;
        }
        
        $tmp[] = $item;
    }
    
    $hotspot = $tmp;
    
    $hotspot_enc = "";
    if (count($hotspot) > 0) {
        
        $hotspot_enc = htmlspecialchars($hotspot[0], ENT_QUOTES, 'UTF-8');
        
        if (count($hotspot) > 1) {
            $hotspot_enc .= ", &hellip;";
        }
    }
    
    $cols = array(
                    "radacctid" => t('all','ID'),
                    "name" => t('all','HotSpot'),
                    "username" => t('all','Username'),
                    "framedipaddress" => t('all','IPAddress'),
                    "acctstarttime" => t('all','StartTime'),
                    "acctstoptime" => t('all','StopTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    "acctinputoctets" => t('all','Upload'),
                    "acctoutputoctets" => t('all','Download'),
                    "acctterminatecause" => t('all','Termination'),
                    "nasipaddress" => t('all','NASIPAddress'),
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
    if (!empty($hotspot)) {
        $logQuery = "performed query for hotspot [$hotspot] on page: ";
    } else {
        $logQuery = "performed query on page: ";
    }
    $logDebugSQL = "";


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );
    
    $title = t('Intro','accthotspot.php');
    $help = t('helpPage','accthotspotaccounting');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-accounting-hotspot.php"); 

    if (!empty($hotspot_enc)) {
        $title .= " :: $hotspot_enc";
    }
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');

    $tmp = array();
    if (count($hotspot) > 0) {
        foreach ($hotspot as $item) {
            $tmp[] = $dbSocket->escapeSimple($item);
        }
    }

    $sql_WHERE = (count($tmp) > 0)
               ? sprintf(" WHERE name IN ('%s')", implode("', '", $tmp))
               : "";

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = $sql_WHERE;
    $_SESSION['reportType'] = "accountingGeneric";

    $sql = sprintf("SELECT ra.RadAcctId, hs.name, ra.UserName, ra.FramedIPAddress, ra.AcctStartTime,
                           ra.AcctStopTime, ra.AcctSessionTime, ra.AcctInputOctets, ra.AcctOutputOctets,
                           ra.AcctTerminateCause, ra.NASIPAddress
                      FROM %s AS ra LEFT JOIN %s AS hs ON ra.calledstationid=hs.mac",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'])
         . $sql_WHERE;
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
        $tmp = array();
        if (count($hotspot) > 0) {
            foreach ($hotspot as $item) {
                $tmp[] = "hotspot[]=" . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        }
        
        $partial_query_string = (count($tmp) > 0) ? "&" . implode("&", $tmp) : "";
?>
    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if needed
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
                <td colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>" style="text-align: right">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>
            </tr>
            <tr>
<?php
        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
        </thead>
        
        <tbody>
<?php
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
            
            echo '<tr>';
            
            for ($i = 0; $i < $rowlen; $i++) {
                
                // 6 (acctsessiontime),
                // 7 (acctinputoctets),
                // 8 (acctoutputoctets)
                // are special cases
                if ($i == 6) {
                    $row[$i] = time2str($row[$i]);
                } else if ($i == 7 || $i == 8) {
                    $row[$i] = toxbyte($row[$i]);
                }
                
                $row[$i] = (!empty($row[$i]))
                         ? htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8')
                         : "(n/a)";

                // 2 (username) should be surrounded by <a></a>
                if ($i != 2) {
                    printf('<td>%s</td>', $row[$i]);
                } else {
                    $onclick = "ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo',"
                             . sprintf("'username=%s');return false;", $row[2]);
                    
                    $tooltipText = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a>',
                                           urlencode($row[2]),t('Tooltip','UserEdit'))
                                 . '<div id="divContainerUserInfo" style="margin: 30px auto">Loading...</div>';
                                 
                    printf('<td><a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a></td>",
                           $onclick, $tooltipText, $row[2]);
                }

            }
            
            echo '</tr>';
        }
?>
        </tbody>
        
<?php
        // tfoot
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
