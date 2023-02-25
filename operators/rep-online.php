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

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // validate this parameter before including menu
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                   'selected',
                   'username' => t('all','Username'),
                   t('all','Name'),
                   'framedipaddress' => t('all','Framed IP Address',),
                   'calledstationid' => t('all','Calling Station ID',),
                   'acctstarttime' => t('all','StartTime'),
                   'acctsessiontime' => t('all','TotalTime'),
                   'hotspot' => t('all','HotSpot'),
                   'nasshortname' =>  t('all','NasShortname'),
                   t('all','TotalTraffic')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

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

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if (!empty($username)) {
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";


    // print HTML prologue
    $extra_css = array();

    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
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

    include('../common/includes/db_open.php');
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
        $partial_query_string = (!empty($username_enc) ? "&username=" . urlencode($username_enc) : "");

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-del.php";
        
        $descriptors = array();

        $descriptors['start'] = array( 'common_controls' => 'clearSessionsUsers[]' );

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
        $descriptors['end'][] = array(
                                        'onclick' => "location.href='include/management/fileExport.php?reportFormat=csv'",
                                        'label' => 'CSV Export',
                                        'class' => 'btn-light',
                                     );
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
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            //~ username, framedipaddress, callingstationid, starttime, sessiontime, nasipaddress,
            //~ calledstationid, sessionid, upload, download, hotspot, nasshortname, firstname, lastname

            list(
                    $this_username, $this_framedipaddress, $this_callingstationid, $this_starttime, $this_sessiontime,
                    $this_nasipaddress, $this_calledstationid, $this_sessionid, $this_upload, $this_download,
                    $this_hotspot, $this_nasshortname, $this_firstname, $this_lastname
                ) = $row;

            $this_sessiontime = time2str($this_sessiontime);

            $this_hotspot = (!empty($this_hotspot)) ? $this_hotspot : "(n/d)";

            $this_name = $this_firstname . "<br>" . $this_lastname;

            $tooltip1 = "(n/d)";
            $tmp = $this_upload + $this_download;
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
            $custom_attributes = sprintf("Acct-Session-Id=%s,Framed-IP-Address=%s", $this_sessionid, $this_framedipaddress);
            $tooltip_disconnect_href = sprintf("config-maint-disconnect-user.php?username=%s&nasaddr=%s&customattributes=%s",
                                               urlencode($this_username), urlencode($this_nasipaddress), urlencode($custom_attributes));

            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($this_username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip2 = array(
                                'subject' => $this_username,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                             );
                             
            $tooltip2['actions'][] = array( 'href' => sprintf('rep-online.php?username=%s', urlencode($this_username)), 'label' => "Filter this user", );
            $tooltip2['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($this_username)), 'label' => t('Tooltip','UserEdit'), );
            $tooltip2['actions'][] = array( 'href' => $tooltip_disconnect_href, 'label' => t('all','Disconnect'), );

            // create tooltip
            $tooltip2 = get_tooltip_list_str($tooltip2);
            
            // create checkbox
            $d = array( 'name' => 'clearSessionsUsers[]',
                        'value' => sprintf("%s||%s", $this_username, $this_starttime));
            $checkbox = get_checkbox_str($d);

            // define table row
            $table_row = array(
                                $checkbox, $tooltip2, $this_name, $this_framedipaddress, $this_calledstationid,
                                $this_starttime, $this_sessiontime, $this_hotspot, $this_nasshortname, $tooltip1
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
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('../common/includes/db_close.php');

    close_tab($navkeys, 0);

    $img_format = '<div class="my-3 text-center"><img src="%s" alt="%s"></div>';
    open_tab($navkeys, 1);
    printf($img_format, "library/graphs/online_users.php", "Online users");
    close_tab($navkeys, 1);

    open_tab($navkeys, 2);
    printf($img_format, "library/graphs/online_nas.php", "Online NAS");
    close_tab($navkeys, 2);

    // close tab wrapper
    close_tab_wrapper();

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
