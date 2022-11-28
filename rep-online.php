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

    // validate this parameter before including menu
    $username = (array_key_exists('usernameOnline', $_GET) && isset($_GET['usernameOnline']))
                    ? str_replace("%", "", $_GET['usernameOnline']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // feed the sidebar
    $usernameOnline = $username_enc;
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if (!empty($username)) {
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";
    
    include_once("lang/main.php");
    
    include("library/layout.php");
    
    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','reponline.php');
    $help = t('helpPage','reponline');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-reports.php");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                   'username' => t('all','Username'),
                   t('all','Name'),
                   'framedipaddress' => "Framed IP Address",
                   'calledstationid' => "Calling Station ID",
                   'acctstarttime' => t('all','StartTime'),
                   'acctsessiontime' => t('all','TotalTime'),
                   'hotspot' => t('all','HotSpot'),
                   'nasshortname' =>  t('all','NasShortname'),
                   t('all','TotalTraffic')
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";


    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    // set navbar stuff
    $navbuttons = array(
                            "Statistics-tab" => "Statistics",
                            "Online-users-tab" => "Online/offline users",
                            "Online-NAS-tab" => "Online NAS",
                       );

    print_tab_navbuttons($navbuttons);
    
?>
                <div class="tabcontent" id="Statistics-tab" style="display: block">
<?php

    include('library/opendb.php');
    include('include/management/pages_common.php');

    // ra is a placeholder in the SQL statements below
    // except for $usernameLastConnect, which has been only partially escaped,
    // all other query parameters have been validated earlier.
    $sql_WHERE = " WHERE (ra.AcctStopTime IS NULL OR ra.AcctStopTime='0000-00-00 00:00:00') ";
    if (!empty($username)) {
        $sql_WHERE .= sprintf(" AND ra.username LIKE '%s%%' ", $dbSocket->escapeSimple($username));
    }
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = $sql_WHERE;
    $_SESSION['reportType'] = "reportsOnlineUsers";
    
    //orig: used as maethod to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT ra.username AS username,
                   ra.framedipaddress AS framedipaddress,
                   ra.callingstationid AS callingstationid,
                   ra.acctstarttime AS starttime,
                   ra.acctsessiontime AS sessiontime,
                   ra.nasipaddress AS nasipaddress,
                   ra.calledstationid AS calledstationid,
                   ra.acctsessionid AS sessionid, 
                   ra.acctinputoctets AS upload,
                   ra.acctoutputoctets AS download,
                   hs.name AS hotspot,
                   rn.shortname AS nasshortname, 
                   ui.firstname AS firstname,
                   ui.lastname AS lastname
              FROM %s AS ra LEFT JOIN %s AS hs ON hs.mac=ra.calledstationid
                            LEFT JOIN %s AS rn ON rn.nasname=ra.nasipaddress
                            LEFT JOIN %s AS ui ON ra.username=ui.username";
                 
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'],
                         $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                         $configValues['CONFIG_DB_TBL_RADNAS'],
                         $configValues['CONFIG_DB_TBL_DALOUSERINFO']) . $sql_WHERE;
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
        $partial_query_string = (!empty($username_enc) ? "&usernameOnline=" . urlencode($username_enc) : "");
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-del.php";
?>
<form name="listall" method="POST" action="<?= $action ?>">
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
                <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>
            </tr>

            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
                    printTableFormControls('clearSessionsUsers[]', $action);
?>
                </th>
            </tr>

            <tr>
<?php
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
        </thead>

        <tbody>
<?php
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        
            $this_username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $this_framedip = htmlspecialchars($row['framedipaddress'], ENT_QUOTES, 'UTF-8');
            $this_nasip = htmlspecialchars($row['nasipaddress'], ENT_QUOTES, 'UTF-8');
            $this_sessionid = htmlspecialchars($row['sessionid'], ENT_QUOTES, 'UTF-8');
            
            $name = htmlspecialchars($row['firstname'], ENT_QUOTES, 'UTF-8')
                  . "<br>"
                  . htmlspecialchars($row['lastname'], ENT_QUOTES, 'UTF-8');
            $callingstationid = htmlspecialchars($row['callingstationid'], ENT_QUOTES, 'UTF-8');
            $starttime = htmlspecialchars($row['starttime'], ENT_QUOTES, 'UTF-8');
            
            $totalTime = htmlspecialchars(time2str($row['sessiontime']), ENT_QUOTES, 'UTF-8');
            
            $hotspot = htmlspecialchars($row['hotspot'], ENT_QUOTES, 'UTF-8');
            $nasshortname = htmlspecialchars($row['nasshortname'], ENT_QUOTES, 'UTF-8');

            $upload = htmlspecialchars(toxbyte($row['upload']), ENT_QUOTES, 'UTF-8');
            $download = htmlspecialchars(toxbyte($row['download']), ENT_QUOTES, 'UTF-8');
            $traffic = toxbyte($row['upload'] + $row['download']);
            
            // tooltip and ajax stuff
            $custom_attributes = sprintf("Acct-Session-Id=%s,Framed-IP-Address=%s", $this_sessionid, $this_framedip);
            $tooltip_disconnect_href = sprintf("config-maint-disconnect-user.php?username=%s&nasaddr=%s&customattributes=%s",
                                               urlencode($this_username), urlencode($this_nasip), urlencode($custom_attributes));
            $li_style = 'margin: 7px auto';
            $tooltip = '<ul style="list-style-type: none">'
                     . sprintf('<li style="%s"><a class="toolTip" href="mng-edit.php?username=%s">%s</a></li>',
                               $li_style, urlencode($this_username), t('Tooltip','UserEdit'))
                     . sprintf('<li style="%s"><a class="toolTip" href="%s">%s</a></li>',
                               $li_style, $tooltip_disconnect_href, t('all','Disconnect'))
                     . '</ul>'
                     . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>';
            $onclick = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo',"
                             . "'divContainerUserInfo','username=%s');", urlencode($this_username));
?>
            <tr>
                <td>
                    <input type="checkbox" name="clearSessionsUsers[]" value="<?= "$this_username||$starttime" ?>">
                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltip ?>'>
                        <?= $this_username ?>
                    </a>
                </td>
                <td><?= $name ?></td>
                <td><?= $this_framedip ?></td>
                <td><?= $callingstationid ?></td>
                <td><?= $starttime ?></td>
                <td><?= $totalTime ?></td>
                <td><?= (!empty($hotspot)) ? $hotspot : "(n/d)" ?></td>
                <td><?= $nasshortname ?></td>
                <td>
<?php
            if (!empty($traffic)) {
                echo t('all','Upload') . ": " . $upload
                   . "<br>"
                   . t('all','Download') . ": " . $download
                   . "<br>"
                   . t('all','TotalTraffic') . ": <strong>" . htmlspecialchars($traffic, ENT_QUOTES, 'UTF-8') . "</strong>";
            } else {
                echo "(n/d)";
            }
?>
                </td>
            </tr>
<?php
        }
?>
        </tbody>

<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>

    </table>
    
    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
    
</form>    

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
?>
            </div>

            <div class="tabcontent" style="text-align: center" id="Online-users-tab">
                <img src="library/graphs-reports-online-users.php" alt="Online users" style="margin: 30px auto">
            </div>

            <div class="tabcontent" style="text-align: center" id="Online-NAS-tab">
                <img src="library/graphs-reports-online-nas.php" alt="Online NAS"  style="margin: 30px auto">
            </div>
<?php

    include('include/config/logging.php');
    
    $inline_extra_js = "
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip()";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
