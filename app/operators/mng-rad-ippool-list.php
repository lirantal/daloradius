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
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    $operator = $_SESSION['operator_user'];

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);

    // we partially strip some characters and
    // leave validation/escaping to other functions used later in the script
    $pool_name = (array_key_exists('pool_name', $_GET) && !empty(str_replace("%", "", trim($_GET['pool_name']))))
               ? str_replace("%", "", trim($_GET['pool_name'])) : "";
    $pool_name_enc = (!empty($pool_name))
                   ? htmlspecialchars($pool_name, ENT_QUOTES, 'UTF-8')
                   : "";

    // keep filter when ordering/paginating
    $partial_query_string = (!empty($pool_name_enc) ? "&pool_name=" . $pool_name_enc : "");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "id" => t('all','ID'),
                    "pool_name" => t('all','PoolName'),
                    "framedipaddress" => t('all','IPAddress'),
                    "nasipaddress" => t('all','NASIPAddress'),
                    "CalledStationId" => t('all','CalledStationId'),
                    "CallingStationID" => t('all','CallingStationID'),
                    "expiry_time" => t('all','ExpiryTime'),
                    "username" => t('all','Username'),
                    "pool_key" => t('all','PoolKey')
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


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js",
    );

    $title = t('Intro','mngradippoollist.php');
    if (!empty($pool_name_enc)) {
        $title .=  " :: " . $pool_name_enc;
    }

    $help = t('helpPage','mngradippoollist');

    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    // filter on pool_name
    $sql_WHERE = "";
    if (!empty($pool_name)) {
        $value_prefix = $dbSocket->escapeSimple($pool_name);
        $sql_WHERE = sprintf(" WHERE pool_name LIKE '%%%s%%'", $value_prefix);
    }

    // we use this simplified query just to initialize $numrows
    $sql = sprintf("SELECT COUNT(id) FROM %s", $configValues['CONFIG_DB_TBL_RADIPPOOL']) . $sql_WHERE;

    $res = $dbSocket->query($sql);
    $numrows = $res->fetchrow()[0];

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        // must be included after opendb because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        // we execute and log the actual query
        $sql = sprintf("SELECT id, pool_name, framedipaddress, nasipaddress, calledstationid,
                               callingstationid, expiry_time, username, pool_key
                          FROM %s", $configValues['CONFIG_DB_TBL_RADIPPOOL']) . $sql_WHERE;
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-rad-ippool-del.php";

        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                            'partial_query_string' => $partial_query_string,
                        );

        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'item[]', );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
        print_table_prologue($descriptors);

        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );

        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

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

            list($id, $pool_name, $framedipaddress, $nasipaddress, $calledstationid,
                 $callingstationid, $expiry_time, $username, $pool_key) = $row;

            // preparing checkbox
            $id = intval($id);
            $item_id = sprintf("ippool-%d", $id);

            // create checkbox
            $d = array( 'name' => 'item[]', 'value' => $item_id, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // IP-Pool actions tooltip
            $tooltip1 = [
                'subject' => $pool_name,
                'actions' => [
                    [
                        'href'  => sprintf('mng-rad-ippool-list.php?pool_name=%s', urlencode($pool_name)),
                        'label' => 'Apply Filter',
                    ],
                    [
                        'href'  => sprintf('mng-rad-ippool-edit.php?item=%s', $item_id),
                        'label' => t('Tooltip', 'EditIPAddress'),
                    ],
                    [
                        'href'  => sprintf('mng-rad-ippool-del.php?item[]=%s', $item_id),
                        'label' => t('Tooltip', 'RemoveIPAddress'),
                    ],
                ],
            ];

            $tooltip1 = get_tooltip_list_str($tooltip1);

            // framed IP address accounting tooltip
            if (preg_match(LOOSE_IP_REGEX, $framedipaddress, $m)) {
                $tooltip2 = [
                    'subject' => $framedipaddress,
                    'actions' => [],
                ];
                $tooltip2['actions'][] = [
                    'href'  => sprintf('acct-ipaddress.php?ipaddress=%s', urlencode($framedipaddress)),
                    'label' => t('button', 'IPAccounting'),
                ];
                $tooltip2 = get_tooltip_list_str($tooltip2);
            } else {
                $tooltip2 = (!empty($framedipaddress)) ? $framedipaddress : "(n/a)";
            }

            // NAS IP accounting tooltip
            if (preg_match(IP_REGEX, $nasipaddress, $m) || preg_match(HOSTNAME_REGEX, $nasipaddress, $m)) {
                $tooltip3 = [
                    'subject' => $nasipaddress,
                    'actions' => [],
                ];
                $tooltip3['actions'][] = [
                    'href'  => sprintf('acct-nasipaddress.php?ipaddress=%s', urlencode($nasipaddress)),
                    'label' => t('button', 'NASIPAccounting'),
                ];
                $tooltip3 = get_tooltip_list_str($tooltip3);
            } else {
                $tooltip3 = (!empty($nasipaddress)) ? $nasipaddress : "(n/a)";
            }

            // username tooltip
            if (!empty($username)) {
                $ajax_id = sprintf("divContainerUserInfo_%d", $count);
                $param = sprintf("username=%s", urlencode($username));
                $onclick = sprintf(
                    "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','%s','%s')",
                    $ajax_id, $param
                );
                $tooltip4 = [
                    'subject' => $username,
                    'onclick' => $onclick,
                    'ajax_id' => $ajax_id,
                    'actions' => [],
                ];
                if (user_exists($dbSocket, $username, 'CONFIG_DB_TBL_RADACCT')) {
                    $tooltip4['actions'][] = [
                        'href'  => sprintf('acct-username.php?username=%s', urlencode($username)),
                        'label' => t('button', 'UserAccounting'),
                    ];
                }
                if (user_exists($dbSocket, $username, 'CONFIG_DB_TBL_RADCHECK')) {
                    $tooltip4['actions'][] = [
                        'href'  => sprintf('mng-edit.php?username=%s', urlencode($username)),
                        'label' => t('Tooltip', 'UserEdit'),
                    ];
                }
                $tooltip4 = get_tooltip_list_str($tooltip4);
            } else {
                $tooltip4 = "(n/a)";
            }

            // expiry time badge
            if (!empty($expiry_time)) {
                $is_future = strtotime($expiry_time) > time();
                $badge_class = $is_future ? "text-bg-success" : "text-bg-danger";
                $badge1 = sprintf('<span class="badge %s">%s</span>', $badge_class, $expiry_time);
            } else {
                $badge1 = "(n/a)";
            }

            // build table row
            $table_row = array(
                $checkbox,
                $tooltip1,
                $tooltip2,
                $tooltip3,
                (!empty($calledstationid)) ? $calledstationid : "(n/a)",
                (!empty($callingstationid)) ? $callingstationid : "(n/a)",
                $badge1,
                $tooltip4,
                (!empty($pool_key)) ? $pool_key : "(n/a)",
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

        $descriptor = array( 'table_foot' => $table_foot );
        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = "Nothing to display";
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    print_footer_and_html_epilogue();
?>
