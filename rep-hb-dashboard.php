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

    //include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";
    
    // soft and hard delay (seconds)
    $softDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] * 60;
    $hardDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] * 60;

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','rephbdashboard.php');
    $help = t('helpPage','rephbdashboard');
    
    print_html_prologue($title, $langCode);
    
    include("include/menu/sidebar.php");
    
      // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    'id' => t('all','HotSpot'),
                    t('all','Firmware'),
                    t('all','WanIface'),
                    t('all','LanIface'),
                    t('all','WifiIface'),
                    t('all','Uptime'),
                    t('all','CPU'),
                    t('all','Memfree'),
                    t('all','BandwidthUp'),
                    t('all','BandwidthDown'),
                    t('all','CheckinTime')
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
               ? strtolower($_GET['orderType']) : "asc";
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include('library/opendb.php');
    include('include/management/pages_common.php');


    $sql = sprintf("SELECT hs.name AS hotspotname, no.wan_iface, no.wan_ip, no.wan_mac, no.wan_gateway, no.wifi_iface,
                           no.wifi_ip, no.wifi_mac, no.wifi_ssid, no.wifi_key, no.wifi_channel, no.lan_iface,
                           no.lan_mac, no.lan_ip, no.uptime, no.memfree, no.cpu, no.wan_bup, no.wan_bdown, no.firmware,
                           no.firmware_revision, no.mac, no.time
                      FROM %s AS no LEFT JOIN %s AS hs ON hs.mac=no.mac", $configValues['CONFIG_DB_TBL_DALONODE'],
                                                                          $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
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
        $sql .= sprintf(" ORDER BY hs.%s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
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
        // second line of table header
        printTableHead($cols, $orderBy, $orderType);
?>
            </tr>
            
        </thead>
        
        <tbody>
<?php
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $rowlen = count($row);
            
            // escape row elements
            foreach ($row as $field => $value) {
                $row[$field] = htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8');
            }

            $content = array();
            $value = array();
            $format = '<b>%s</b>: %s'; 

            // first tooltip balloon
            $content[0] = sprintf('<a class="toolTip" href="mng-hs-edit.php?name=%s">%s</a><br/><br/><b>%s:</b> %s',
                                  $row['hotspotname'], t('Tooltip','HotspotEdit'), t('all','NASMAC'), $row['mac']);
            $value[0]   = sprintf('<b>%s</b><br>', $row['hotspotname']);
            
            // second tooltip balloon
            $content[1] = sprintf($format, t('all','WanIface'), $row['wan_iface'])
                        . sprintf($format, t('all','WanMAC'), $row['wan_mac'])
                        . sprintf($format, t('all','WanIP'), $row['wan_ip'])
                        . sprintf($format, t('all','WanGateway'), $row['wan_ip']);
                        
            $value[1]   = sprintf($format, t('all','WanIP'), $row['wan_ip']);
            
            // third tooltip balloon
            $content[2] = sprintf($format, t('all','LanIface'), $row['lan_iface'])
                        . sprintf($format, t('all','LanMAC'), $row['lan_mac'])
                        . sprintf($format, t('all','LanIP'), $row['lan_ip']);
                        
            $value[2]   = sprintf($format, t('all','LanIP'), $row['lan_ip']);

            // fourth tooltip balloon
            $content[3] = sprintf($format, t('all','WifiIface'), $row['wifi_iface'])
                        . sprintf($format, t('all','WifiMAC'), $row['wifi_mac'])
                        . sprintf($format, t('all','WifiIP'), $row['wifi_ip'])
                        . sprintf($format, t('all','WifiSSID'), $row['wifi_ssid'])
                        . sprintf($format, t('all','WifiKey'), $row['wifi_key'])
                        . sprintf($format, t('all','WifiChannel'), $row['wifi_channel']);

            $value[3]   = sprintf($format, t('all','WifiSSID'), $row['wifi_ssid'])
                        . sprintf($format, t('all','WifiKey'), $row['wifi_key']);

            // create tooltip balloons
            $tooltip = array();
            for ($i = 0; $i <= count($content); $i++) {
                $param = array(
                                'content' => $content[$i],
                                'onClick' => '',
                                'value' => $value[$i],
                                'divId' => '',
                              );
                $tooltip[] = addToolTipBalloon($param);
            }
            
            // calculate time delay
            // delta is given by <current time> - <check-in time>
            $delta = time() - strtotime($row['time']);
            
            if ($delta >= $hardDelay) {
                // this is hard delay
                $delayColor = 'red';
            } elseif ($delta >= $softDelay && $delta < $hardDelay) {
                // this is soft delay
                $delayColor = 'orange';
            } else {
                // this is no delay at all, meaning not above 5 minutes delay
                $delayColor = 'green';
            }
?>

        <tr>
            <td><?= $tooltip[0] ?></td>
            <td><?= $row['firmware'] . "<br/>" . $row['firmware_revision'] ?></td>
            <td><?= $tooltip[1] ?></td>
            <td><?= $tooltip[2] ?></td>
            <td><?= $tooltip[3] ?></td>
            <td><?= time2str($row['uptime']) ?></td>
            <td><?= $row['cpu'] ?></td>
            <td><?= $row['memfree'] ?></td>
            <td><?= toxbyte($row['wan_bup']) ?></td>
            <td><?= toxbyte($row['wan_bdown']) ?></td>
            <td><span style="color: <?= $delayColor ?>"><?= $row['time'] ?></span></td>
        </tr>

<?php
        }
?>
         </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
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
