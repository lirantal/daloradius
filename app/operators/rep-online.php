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

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
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

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    unset($_SESSION['reportExport']);

    // validate this parameter before including menu
    $username = dalo_accounting_scalar($_GET, 'username');
    $username_enc = ($username !== '') ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                   'selected',
                   'username' => t('all','Username'),
                   t('all','Name'),
                   'framedipaddress' => t('all','Framed IP Address'),
                   'calledstationid' => t('all','Calling Station ID'),
                   'nasshortname' => t('all','Nas'),
                   'hotspot' => t('all','HotSpot'),
                   'acctstarttime' => t('all','StartTime'),
                   'acctsessiontime' => t('all','TotalTime'),
                   t('all','TotalTraffic')
                 );
    $colspan = count($cols);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    $param_col_keys = array_keys($param_cols);

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], $param_col_keys))
             ? $_GET['orderBy'] : $param_col_keys[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if ($username !== '') {
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";


    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/chart.umd.min.js",
        "static/js/daloradius-charts.js",
        "static/js/request.js",
        "static/js/readonly_info.js",
    );

    $title = t('Intro','reponline.php');
    $help = t('helpPage','reponline');

    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    print_title_and_help($title, $help);

    // set navbar stuff
    $navkeys = array(
                        array( 'stats', t('all','Statistics') ),
                        array( 'online-users', "Online/offline users" ),
                        array( 'online-nas', "Online NAS", ),
                    );

    // print navbar controls
    print_tab_header($navkeys);

    // open tab wrapper
    open_tab_wrapper();

    // open first tab (shown)
    open_tab($navkeys, 0, true);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);

    // The selected-backend provider binds the validated scalar filters.
    $_SESSION['reportType'] = 'reportsOnlineUsers';
    $_SESSION['reportExport'] = array('source'=>'rep-online','type'=>'reportsOnlineUsers','filters'=>array('username'=>$username));
    $reportPDO = null;
    $reportRows = array();
    $numrows = 0;
    try {
        $reportPDO = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        list($reportSQL, $reportBindings) = dalo_operator_query('rep-online', array('username'=>$username), $configValues);
        $numrows = dalo_accounting_count($reportPDO, $reportSQL, $reportBindings, $configValues);
    } catch (Throwable $exception) { dalo_accounting_failure($exception); }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        // must follow configuration initialization because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        // we execute and log the actual query
        try {
            $reportRows = dalo_operator_rows($reportPDO, $reportSQL, $reportBindings, 'rep-online',
                $orderBy, $orderType, (int)$offset, (int)$rowsPerPage, false);
        } catch (Throwable $exception) { dalo_accounting_failure($exception); }
        $per_page_numrows = count($reportRows);




        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = ($username_enc !== '' ? "&username=" . urlencode($username) : "");

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-del.php";

        $descriptors = array();

        $descriptors['start'] = array(
                                        'common_controls' => 'clearSessionsUsers[]',
                                        'additional_controls' => array(
                                            array(
                                                'onclick' => "removeCheckbox('listall','$action')",
                                                'label' => t('button','ClearSessions'),
                                                'class' => 'btn-danger',
                                            ),
                                        ),
                                     );

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
        if (!isset($failureMsg)) { $descriptors['end'][] = get_csv_export_control(); }
        print_table_prologue($descriptors);

        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );

        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($reportRows as $row) {
            $rawIdentity = array_map(static function ($value) { return (string)($value ?? ''); }, $row);

            // escape row elements
            foreach ($row as $i => $value) {
                $row[$i] = htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
            }

            list(
                    $this_username, $this_framedipaddress, $this_callingstationid, $this_starttime, $this_sessiontime,
                    $this_nasipaddress, $this_calledstationid, $this_sessionid, $this_upload, $this_download,
                    $this_hotspot, $this_nasshortname, $this_nasid, $this_firstname, $this_lastname
                ) = $row;

            $this_sessiontime = time2str($this_sessiontime);
            $this_hotspot = (!empty($this_hotspot)) ? $this_hotspot : t('all','NotDefined');
            $this_name = (!empty((trim($this_firstname) . trim($this_lastname)))) ? $this_firstname . "<br>" . $this_lastname : t('all','NotDefined');

            $this_nasid = intval($this_nasid);
            $nas_tooltip = get_nas_tooltip_str($this_nasshortname, $this_nasipaddress);

            $tooltip1 = t('all','NotDefined');
            $tmp = (is_numeric($this_upload) ? $this_upload : 0) + (is_numeric($this_download) ? $this_download : 0);
            if ($tmp > 0) {
                $this_upload = toxbyte($this_upload);
                $this_download = toxbyte($this_download);
                $this_traffic = t('all','Upload') . ": " . $this_upload
                              . "<br>"
                              . t('all','Download') . ": " . $this_download;

                $tooltip1 = array(
                                'subject' => toxbyte($tmp),
                                'content' => $this_traffic
                             );

                $tooltip1 = get_tooltip_list_str($tooltip1);
            }

            // tooltip and ajax stuff
            $custom_attributes = sprintf("Acct-Session-Id=%s,Framed-IP-Address=%s", $rawIdentity[7], $rawIdentity[1]);
            $tooltip_disconnect_href = sprintf("config-maint-disconnect-user.php?username=%s&nas_id=nas-%d&customAttributes=%s",
                                               urlencode($rawIdentity[0]), $this_nasid, urlencode($custom_attributes));

            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($rawIdentity[0]));
            $onclick = "daloInfo.user('$ajax_id','$param')";
            $tooltip2 = array(
                                'subject' => $this_username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                             );

            $tooltip2['actions'][] = array( 'href' => sprintf('rep-online.php?username=%s', urlencode($rawIdentity[0])), 'label' => "Filter this user", );
            $tooltip2['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($rawIdentity[0])), 'label' => t('Tooltip','UserEdit'), );
            $tooltip2['actions'][] = array( 'href' => $tooltip_disconnect_href, 'label' => t('all','Disconnect'), );

            // create tooltip
            $tooltip2 = get_tooltip_list_str($tooltip2);

            // create checkbox
            $d = array( 'name' => 'clearSessionsUsers[]',
                        'value' => sprintf("%s||%s", $this_username, $this_starttime));
            $checkbox = get_checkbox_str($d);

            // define table row
            $table_row = array(
                                $checkbox, $tooltip2, $this_name, $this_framedipaddress, $this_callingstationid,
                                $nas_tooltip, $this_hotspot, $this_starttime, $this_sessiontime, $tooltip1
                              );

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
        $descriptor = array(  'form' => $form_descriptor, 'table_foot' => $table_foot );
        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }


    close_tab($navkeys, 0);

    $img_format = '<div class="my-3 text-center" style="height:384px"><canvas data-chart-source="%s" aria-label="%s" role="img"></canvas></div>';
    open_tab($navkeys, 1);
    printf($img_format, "library/graphs/online_users.php", t('button', 'OnlineUsers'));
    close_tab($navkeys, 1);

    open_tab($navkeys, 2);
    printf($img_format, "library/graphs/online_nas.php", "Online NAS");
    close_tab($navkeys, 2);

    // close tab wrapper
    close_tab_wrapper();

    if (isset($failureMsg)) {
        include_once $configValues['OPERATORS_INCLUDE_MANAGEMENT'] . '/actionMessages.php';
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    if (isset($failureMsg) || empty($numrows)) {
        unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    }
    unset($reportPDO, $reportRows);
    print_footer_and_html_epilogue();
