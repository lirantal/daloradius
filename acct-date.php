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
    $title = t('Intro','acctdate.php');
    $help = t('helpPage','acctdate');

    print_html_prologue($title, $langCode);
    
    include("menu-accounting.php");

    $cols = array(
                    "radacctid" => t('all','ID'),
                    "hotspot" => t('all','HotSpot'),
                    "username" => t('all','Username'),
                    "framedipaddress" => t('all','IPAddress'),
                    "acctstarttime" => t('all','StartTime'),
                    "acctstoptime" => t('all','StopTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    "acctinputoctets" => sprintf("%s (%s)", t('all','Upload'), t('all','Bytes')),
                    "acctoutputoctets" => sprintf("%s (%s)", t('all','Download'), t('all','Bytes')),
                    "acctterminatecause" => t('all','Termination'),
                    "nasipaddress" => t('all','NASIPAddress'),
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
    
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($acct_custom_query_options_all)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";


    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    $sql_WHERE = array();
    $partial_query_params = array();

    if (!empty($startdate)) {
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
        $partial_query_params[] = sprintf("startdate=%s", $startdate);
    }

    if (!empty($enddate)) {
        $sql_WHERE[] = sprintf("AcctStartTime < '%s'", $dbSocket->escapeSimple($enddate));
        $partial_query_params[] = sprintf("enddate=%s", $enddate);
    }

    if (!empty($username)) {
        $sql_WHERE[] = sprintf("username='%s'", $dbSocket->escapeSimple($username));
        $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        $_SESSION['reportQuery'] = (count($sql_WHERE) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE) : "";
        $_SESSION['reportType'] = "accountingGeneric";

    
        // we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object
        include('library/opendb.php');
        include('include/management/pages_common.php');
    
        $sql = sprintf("SELECT COUNT(radacctid) FROM %s", $configValues['CONFIG_DB_TBL_RADACCT']);
        if (count($sql_WHERE) > 0) {
            $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
        }
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $numrows = $res->fetchrow()[0];
        
        if ($numrows > 0) {
            /* START - Related to pages_numbering.php */
            
            // when $numrows is set, $maxPage is calculated inside this include file
            include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                                  // the CONFIG_IFACE_TABLES_LISTING variable from the config file
            
            // here we decide if page numbers should be shown
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
            
            $sql = sprintf("SELECT ra.RadAcctId, dhs.name AS hotspot, ra.username, ra.FramedIPAddress, ra.AcctStartTime,
                                   ra.AcctStopTime, ra.AcctSessionTime, ra.AcctInputOctets, ra.AcctOutputOctets,
                                   ra.AcctTerminateCause, ra.NASIPAddress
                              FROM %s AS ra LEFT JOIN %s AS dhs ON ra.calledstationid=dhs.mac",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
            if (count($sql_WHERE) > 0) {
                $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
            }

            $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            $per_page_numrows = $res->numRows();

    
            $partial_query_string = (count($partial_query_params) > 0)
                                  ? ("&" . implode("&", $partial_query_params)) : "";
?>
<table border="0" class="table1">
    <thead>
        <tr style="background-color: white">
<?php
            // page numbers are shown only if there is more than one page
            if ($drawNumberLinks) {
                printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
                echo '</td>';
            }
?>
            <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>">
                <input class="button" type="button" value="CSV Export"
                    onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
            </td>

        </tr>

<?php
            // second line of table header
            echo "<tr>";
            printTableHead($cols, $orderBy, $orderType, $partial_query_string);
            echo "</tr>";
?>
    </thead>

    <tbody>
<?php
            $simple_td_format = '<td>%s</td>';
            $li_style = 'margin: 7px auto';

            while ($row = $res->fetchRow()) {
                foreach ($row as $i => $value) {
                    $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                
                echo "<tr>";
                printf($simple_td_format, $row[0]);
                
                $onclick = "javascript:ajaxGeneric('include/management/retHotspotInfo.php','retHotspotGeneralStat','divContainerHotspotInfo'"
                         . sprintf(",'hotspot=%s');return false;", $row[1]);
                $tooltip = '<ul style="list-style-type: none">'
                         . sprintf('<li style="%s"><a class="toolTip" href="mng-hs-edit.php?name=%s">%s</a></li>',
                                   $li_style, urlencode($row[1]), t('Tooltip','HotspotEdit'))
                         . sprintf('<li style="%s"><a class="toolTip" href="acct-hotspot-compare.php">%s</a></li>',
                                   $li_style, t('all','Compare'))
                         . '</ul>'
                         . '<div style="margin: 15px auto" id="divContainerHotspotInfo">Loading...</div>';
                
                printf('<td><a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a></td>",
                       $onclick, $tooltip, $row[1]);
                
                $onclick = "javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo',"
                         . sprintf("'username=%s');return false;", $row[2]);
                $tooltip = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a>', $row[2], t('Tooltip','UserEdit'))
                         . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>';
                printf('<td><a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a></td>",
                       $onclick, $tooltip, $row[2]);

                printf($simple_td_format, $row[3]);
                printf($simple_td_format, $row[4]);
                printf($simple_td_format, $row[5]);
                
                printf($simple_td_format, time2str($row[6]));
                printf($simple_td_format, toxbyte($row[7]));
                printf($simple_td_format, toxbyte($row[8]));
                printf($simple_td_format, $row[9]);
                printf($simple_td_format, $row[10]);
            
                echo "</tr>";
            
            }

            echo '</tbody>';

            // tfoot
            $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
            printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);

            echo '</table>';
            
        } else {
            $failureMsg = "Nothing to display";
        }
    
        include_once('include/management/userReports.php');
        userPlanInformation($username, 1);
        userSubscriptionAnalysis($username, 1);                 // userSubscriptionAnalysis with argument set to 1 for drawing the table
        userConnectionStatus($username, 1);                     // userConnectionStatus (same as above)
    
    } else {
        $failureMsg = "Please specify a valid username";
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
