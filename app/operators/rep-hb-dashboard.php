<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    include('library/check_operator_perm.php');
    require_once __DIR__ . '/library/operator_reports_pdo.php';
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    try {
        dalo_accounting_validate_request($_GET);
        foreach (array('batch_name','radiusReply') as $key) {
            if (isset($_GET[$key])) { dalo_accounting_scalar($_GET, $key); }
        }
    } catch (Throwable $exception) {
        http_response_code(400); exit('Invalid operator report filters');
    }
    if (isset($_REQUEST['page']) && !is_string($_REQUEST['page'])) { $_REQUEST['page'] = '1'; }

    include_once('../common/includes/config_read.php');
    
    include_once("lang/main.php");
    include("../common/includes/layout.php");
    
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
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";
    
    // soft and hard delay (seconds)
    $softDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] * 60;
    $hardDelay = $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] * 60;


    // print HTML prologue
    $title = t('Intro','rephbdashboard.php');
    $help = t('helpPage','rephbdashboard');
    
    print_html_prologue($title, $langCode);
    
    print_title_and_help($title, $help);
    
    include('include/management/pages_common.php');


    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('rep-hb-dashboard', array(), $configValues);
        $numrows = dalo_accounting_count($reportPDO, $reportSQL, $reportBindings, $configValues);
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must follow configuration initialization because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        // we execute and log the actual query
        try {
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'rep-hb-dashboard',
                $orderBy, $orderType, (int)$offset, (int)$rowsPerPage, true);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($reportRows);


        
        $partial_query_string = '';

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

        print_table_prologue($descriptors);
        
        // print table top
        print_table_top();

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();
        
        foreach ($reportRows as $row) {
            $rawHotspotName = (string)($row['hotspotname'] ?? '');
            $rowlen = count($row);
            
            // escape row elements
            foreach ($row as $field => $value) {
                $row[$field] = htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES, 'UTF-8');
            }

            $content = array();
            $value = array();
            $format = '<strong>%s</strong>: %s'; 

            // tooltip0
            $tooltip = array(
                                'subject' => $row['hotspotname'],
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-hs-edit.php?name=%s', urlencode($rawHotspotName), ),
                                           'label' => t('Tooltip','HotspotEdit'), );
        
            $tooltip['content'] = sprintf($format, t('all','NASMAC'), $row['mac']);
        
            $tooltip0 = get_tooltip_list_str($tooltip);
            
            
            // tooltip1
            $tooltip = array(
                                'subject' => sprintf($format, t('all','WanIP'), $row['wan_ip']),
                                'actions' => array(),
                            );
            
        
            $tooltip['content'] = sprintf($format, t('all','WanIface'), $row['wan_iface'])
                                . sprintf($format, t('all','WanMAC'), $row['wan_mac'])
                                . sprintf($format, t('all','WanIP'), $row['wan_ip'])
                                . sprintf($format, t('all','WanGateway'), $row['wan_gateway']);
                
            $tooltip1 = get_tooltip_list_str($tooltip);

            // tooltip2
            $tooltip = array(
                                'subject' => sprintf($format, t('all','LanIP'), $row['lan_ip']),
                                'actions' => array(),
                            );
            
        
            $tooltip['content'] = sprintf($format, t('all','LanIface'), $row['lan_iface'])
                                . sprintf($format, t('all','LanMAC'), $row['lan_mac'])
                                . sprintf($format, t('all','LanIP'), $row['lan_ip']);
                
            $tooltip2 = get_tooltip_list_str($tooltip);
            
            // tooltip3
            $tooltip = array(
                                'subject' => (sprintf($format, t('all','WifiSSID'), $row['wifi_ssid']) .
                                              sprintf($format, t('all','WifiKey'), $row['wifi_key'])),
                                'actions' => array(),
                            );
            
        
            $tooltip['content'] = sprintf($format, t('all','WifiIface'), $row['wifi_iface'])
                                . sprintf($format, t('all','WifiMAC'), $row['wifi_mac'])
                                . sprintf($format, t('all','WifiIP'), $row['wifi_ip'])
                                . sprintf($format, t('all','WifiSSID'), $row['wifi_ssid'])
                                . sprintf($format, t('all','WifiKey'), $row['wifi_key'])
                                . sprintf($format, t('all','WifiChannel'), $row['wifi_channel']);
                
            $tooltip3 = get_tooltip_list_str($tooltip);

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
            <td><?= $tooltip0 ?></td>
            <td><?= $row['firmware'] . "<br/>" . $row['firmware_revision'] ?></td>
            <td><?= $tooltip1 ?></td>
            <td><?= $tooltip2 ?></td>
            <td><?= $tooltip3 ?></td>
            <td><?= time2str($row['uptime']) ?></td>
            <td><?= $row['cpu'] ?></td>
            <td><?= $row['memfree'] ?></td>
            <td><?= toxbyte($row['wan_bup']) ?></td>
            <td><?= toxbyte($row['wan_bdown']) ?></td>
            <td><span style="color: <?= $delayColor ?>"><?= $row['time'] ?></span></td>
        </tr>

<?php
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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    

    if (isset($failureMsg)) {
        include_once $configValues['OPERATORS_INCLUDE_MANAGEMENT'] . '/actionMessages.php';
    }

    include('include/config/logging.php');
    
    if (isset($failureMsg) || empty($numrows)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($reportPDO, $reportRows);
    print_footer_and_html_epilogue();
?>
