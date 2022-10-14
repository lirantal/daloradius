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
    
    $usernameOnline = $username_enc;
    
    include("menu-reports.php");
?>

<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css">
<![endif]-->
<script src="library/js_date/date-functions.js"></script>
<script src="library/js_date/datechooser.js"></script>
<?php

    include_once("library/tabber/tab-layout.php");

    // these three variable can be used for validation an presentation purpose
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
?>

        <div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','reponline.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
                
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','reponline'); ?><br></div>
            <br>


            <div class="tabber">
                <div class="tabbertab" title="Statistics">
    
<?php

    include('library/opendb.php');
    include('include/management/pages_common.php');
    include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                          // the CONFIG_IFACE_TABLES_LISTING variable from the config file

    // setup php session variables for exporting
    
    // ra is a placeholder in the SQL statements below
    // except for $usernameLastConnect, which has been only partially escaped,
    // all other query parameters have been validated earlier.
    $sql_WHERE = " WHERE (ra.AcctStopTime IS NULL OR ra.AcctStopTime='0000-00-00 00:00:00') ";
    if (!empty($username)) {
        $sql_WHERE .= sprintf(" AND ra.username LIKE '%s%%' ", $dbSocket->escapeSimple($username));
    }
    
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
        $sql .= sprintf(" ORDER BY ra.%s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */
        
        $partial_query_string = (!empty($username_enc) ? "&usernameOnline=$username_enc" : "");
?>
<form name="usersonline" method="GET">
    <table border="0" class="table1">
        <thead>
            <tr>
<?php

    $split_header = $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes" && $maxPage > 1;

    if ($split_header) {
        printf('<th colspan="%d">', (($colspan % 2 === 0) ? $half_colspan : $half_colspan + 1));
        setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
        echo "</th>";
    }
?>
                <th colspan="<?= ($split_header) ? $half_colspan : $colspan ?>" style="text-align: right">
                    Select:
                    <a title="Select All" class="table" href="javascript:SetChecked(1,'clearSessionsUsers[]','usersonline')">All</a>
                    <a title="Select None" class="table" href="javascript:SetChecked(0,'clearSessionsUsers[]','usersonline')">None</a>
                    <br>
                    <input class="button" type="button" value="<?= t('button','ClearSessions') ?>"
                        onclick="javascript:removeCheckbox('usersonline','mng-del.php')">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </th>
            </tr>
            <tr>
<?php

    foreach ($cols as $label => $caption) {
        
        if (is_int($label)) {
            $ordering_controls = "";
        } else {
            $href_format = "?orderBy=%s&orderType=%s" . $partial_query_string;
            $href_asc = sprintf($href_format, $label, "asc");
            $href_desc = sprintf($href_format, $label, "desc");
            
            $title_format = "order by %s, sort %s";
            $title_asc = sprintf($title_format, strip_tags($caption), "ascending");
            $title_desc = sprintf($title_format, strip_tags($caption), "descending");
            
            $a_format = '<a title="%s" class="novisit" href="%s"><img src="%s" alt="%s"></a>';
            
            $ordering_controls = sprintf($a_format, $title_asc, $href_asc, 'images/icons/arrow_up.png', '^')
                               . sprintf($a_format, $title_desc, $href_desc, 'images/icons/arrow_down.png', 'v');


        }
        
        echo "<th>" . $caption . $ordering_controls . "</th>";
    }
?>
            </tr>
        </thead>

        <tbody>
<?php
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        
            $this_username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $this_framedip = htmlspecialchars($row['framedipaddress'], ENT_QUOTES, 'UTF-8');
            $this_nasip = htmlspecialchars($row['nasipaddress'], ENT_QUOTES, 'UTF-8');
            $this_sessionid = htmlspecialchars($row['sessionid'], ENT_QUOTES, 'UTF-8');
            
            $tooltip_disconnect_href = sprintf("config-maint-disconnect-user.php?username=%s&nasaddr=%s"
                                             . "&customattributes=Acct-Session-Id=%s,Framed-IP-Address=%s",
                                               $this_username, $this_nasip, $this_sessionid, $this_framedip);
        
            
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
?>
            <tr>
                <td>
                    <input type="checkbox" name="clearSessionsUsers[]"
                        value="<?= "$this_username||$starttime" ?>">
                    <a class="tablenovisit" href="#" onclick="javascript:return false;"
                        tooltipText="
                            <a class='toolTip' href='mng-edit.php?username=<?= $this_username ?>'><?= t('Tooltip','UserEdit') ?></a>
                            &nbsp;
                            <a class='toolTip' href='<?= $tooltip_disconnect_href ?>'><?= t('all','Disconnect') ?></a>">
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


        <tfoot>
            <tr>
                <th style="text-align: left" colspan="<?= ($maxPage > 1) ? (($colspan % 2 === 0) ? $half_colspan : $half_colspan + 1) : $colspan ?>">
                    #<?= $numrows ?> record(s) shown
                </th>
<?php
    if ($maxPage > 1) {
?>
                <th style="text-align: right" colspan="<?= $half_colspan ?>">
                    <?php
                        setupLinks($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
                    ?>
                </th>
<?php
    }
?>
            </tr>
        </tfoot>


    </table>
</form>    

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
?>
                </div>

                <div class="tabbertab" style="text-align: center" title="Graph">
                    <img src="library/graphs-reports-online-users.php">
                </div>
    
                <div class="tabbertab" style="text-align: center" title="Online Nas">
                    <img src="library/graphs-reports-online-nas.php">
                </div>
            </div>        
        </div>
        
        <div id="footer">
<?php
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if (!empty($username)) {
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div>        
    </div>
</div>

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
