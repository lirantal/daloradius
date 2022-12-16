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
    include_once('library/config_read.php');

    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

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
    
    //feed the sidebar variables
    $accounting_date_username = $username_enc;
    $accounting_date_startdate = $startdate;
    $accounting_date_enddate = $enddate;

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for user [$username] and start date [$startdate] and end date [$enddate] on page: ";
    $logDebugSQL = "";
    
    // print HTML prologue
    $title = t('Intro','acctactive.php');
    $help = t('helpPage','acctactive');

    print_html_prologue($title, $langCode);
    
    include("menu-accounting.php");

    $cols = array(
                    "username" => t('all','Username'),
                    "attribute" => t('all','Attribute'),
                    "maxtimeexpiration" => t('all','MaxTimeExpiration'),
                    "usedtime" => t('all','UsedTime'),
                    t('all','Status'),
                    t('all','Usage')    
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";

    // start printing content
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    

    include('library/opendb.php');
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
?>

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>';
        }
?>
            </tr>
            
            <tr>
<?php
        printTableHead($cols, $orderBy, $orderType);
?>
            </tr>
        </thead>
        
        <tbody>
<?php
        while($row = $res->fetchRow()) {
            $status="Active";

            if ($row[1] == "Expiration") {        
                if (datediff('d', $row[2], "$currdate", false) > 0) {
                    $status = "Expired";
                }
            } 

            if ($row[1] == "Max-All-Session") {        
                if ($row[3] >= $row[2]) {
                    $status = "End";
                }
            }

            foreach ($row as $i => $value) {
                $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            echo "<tr>";
        
            $onclick = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo','username=%s');"
                     . "return false;", $row[0]);
        
            $tooltip = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a>', $row[2], t('Tooltip','UserEdit'))
                         . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>';
        
            printf('<td><a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a></td>",
                   $onclick, $tooltip, $row[0]);
            
            printf("<td>%s</td>", $row[1]);
            printf("<td>%s</td>", $row[2]);
            printf("<td>%s</td>", time2str($row[3]));
            printf("<td>%s</td>", $status);

            echo "<td>";

            if ($row[1] == "Expiration") {        
                $difference = datediff('d', $row[2], "$currdate", false);
                if ($difference > 0)
                    echo "<h100> " . " $difference days since expired" . "</h100> ";
                else 
                    echo substr($difference, 1) . " days until expiration";
            } 

            if ($row[1] == "Max-All-Session") {        
                if ($status == "End") {
                    echo "<h100> " . abs($row[2] - $row[3]) . " seconds overdue credit" . "</h100>";
                } else {
                    echo $row[2] - $row[3];
                    echo " left on credit";
                }
            } 


            echo "</td>"
               . "</tr>";
        }

        echo '</tbody>';
    
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);

        echo '</table>';
    } else {
        $failureMsg = "Nothing to display";
    }
    
    include_once("include/management/actionMessages.php");
    
    include('library/closedb.php');

    include('include/config/logging.php');
    
    $inline_extra_js = "
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip()";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
