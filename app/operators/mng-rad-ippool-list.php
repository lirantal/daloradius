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

    require_once __DIR__ . '/library/ip_pool_pages_pdo.php';
    $pool_name=$pool_name_enc=$partial_query_string='';
    $filter_invalid=false;
    try {
        $value=$_GET['pool_name'] ?? '';
        if (!is_string($value) || strlen($value)>4096 || strpos($value,"\0")!==false || !preg_match('//u',$value)) {
            throw new InvalidArgumentException('Invalid pool filter');
        }
        // Keep the historical LIKE wildcard policy for search, not stored names.
        $pool_name=str_replace('%','',trim($value));
        $pool_name_enc=htmlspecialchars($pool_name,ENT_QUOTES,'UTF-8');
        $partial_query_string=$pool_name!=='' ? '&pool_name=' . rawurlencode($pool_name) : '';
    } catch (Throwable $e) { $filter_invalid=true; }

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
    $orderBy=isset($_GET['orderBy']) && is_string($_GET['orderBy']) && in_array($_GET['orderBy'],array_keys($param_cols),true)
            ? $_GET['orderBy'] : array_keys($param_cols)[6];
    $orderType=isset($_GET['orderType']) && is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']),array('desc','asc'),true)
              ? strtolower($_GET['orderType']) : 'desc';

    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js",
    );

    $title = t('Intro','mngradippoollist.php');
    if ($pool_name_enc!=='') {
        $title .=  " :: " . $pool_name_enc;
    }

    $help = t('helpPage','mngradippoollist');

    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    $numrows=0; $sql_WHERE=''; $bindings=array();
    try {
        if ($filter_invalid) { throw new InvalidArgumentException('Invalid pool filter'); }
        $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
        $table=dalo_ippool_table($configValues);
        if ($pool_name!=='') { $sql_WHERE=' WHERE pool_name LIKE ?'; $bindings[]='%' . $pool_name . '%'; }
        $numrows=(int)dalo_ippool_query($pdo,"SELECT COUNT(id) FROM $table" . $sql_WHERE,$bindings)->fetchColumn();
    } catch (Throwable $e) { $failureMsg='Unable to load IP pools'; }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        // must be included after opendb because it needs to read
        // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        $rows=array();
        try {
            $sql="SELECT id,pool_name,framedipaddress,nasipaddress,calledstationid,
                         callingstationid,expiry_time,username,pool_key FROM $table" . $sql_WHERE .
                 " ORDER BY $orderBy $orderType LIMIT ?,?";
            $values=array_merge($bindings,array((int)$offset,(int)$rowsPerPage));
            $rows=dalo_ippool_query($pdo,$sql,$values)->fetchAll(PDO::FETCH_NUM);
        } catch (Throwable $e) { $failureMsg='Unable to load IP pools'; }
        $per_page_numrows=count($rows);

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
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($rows as $row) {
            $raw_pool_name=(string)$row[1]; $raw_username=(string)$row[7];
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)$row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($id, $pool_name, $framedipaddress, $nasipaddress, $calledstationid,
                 $callingstationid, $expiry_time, $username, $pool_key) = $row;

            // preparing checkbox
            $item_id = 'ippool-' . $id;

            // create checkbox
            $d = array( 'name' => 'item[]', 'value' => $item_id, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // IP-Pool actions tooltip
            $tooltip1 = [
                'subject' => $pool_name,
                'actions' => [
                    [
                        'href'  => sprintf('mng-rad-ippool-list.php?pool_name=%s', rawurlencode($raw_pool_name)),
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
                $tooltip2 = (!empty($framedipaddress)) ? $framedipaddress : t('all','NotAvailable');
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
                $tooltip3 = (!empty($nasipaddress)) ? $nasipaddress : t('all','NotAvailable');
            }

            // username tooltip
            if ($raw_username!=='') {
                $ajax_id = sprintf("divContainerUserInfo_%d", $count);
                $param = sprintf("username=%s", rawurlencode($raw_username));
                $onclick = sprintf(
                    "daloInfo.user('%s','%s')",
                    $ajax_id, $param
                );
                $tooltip4 = [
                    'subject' => $username,
                    'onclick' => $onclick,
                    'ajax_id' => $ajax_id,
                    'actions' => [],
                ];
                try {
                    if (user_exists($pdo, $raw_username, 'CONFIG_DB_TBL_RADACCT')) {
                        $tooltip4['actions'][]=array('href'=>'acct-username.php?username=' . rawurlencode($raw_username), 'label'=>t('button','UserAccounting'));
                    }
                    if (user_exists($pdo, $raw_username, 'CONFIG_DB_TBL_RADCHECK')) {
                        $tooltip4['actions'][]=array('href'=>'mng-edit.php?username=' . rawurlencode($raw_username), 'label'=>t('Tooltip','UserEdit'));
                    }
                } catch (Throwable $e) { $failureMsg='Unable to load IP pool user links'; }
                $tooltip4 = get_tooltip_list_str($tooltip4);
            } else {
                $tooltip4 = t('all','NotAvailable');
            }

            // expiry time badge
            if (!empty($expiry_time)) {
                $is_future = strtotime($expiry_time) > time();
                $badge_class = $is_future ? "text-bg-success" : "text-bg-danger";
                $badge1 = sprintf('<span class="badge %s">%s</span>', $badge_class, $expiry_time);
            } else {
                $badge1 = t('all','NotAvailable');
            }

            // build table row
            $table_row = array(
                $checkbox,
                $tooltip1,
                $tooltip2,
                $tooltip3,
                (!empty($calledstationid)) ? $calledstationid : t('all','NotAvailable'),
                (!empty($callingstationid)) ? $callingstationid : t('all','NotAvailable'),
                $badge1,
                $tooltip4,
                (!empty($pool_key)) ? $pool_key : t('all','NotAvailable'),
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
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    if (isset($failureMsg)) { include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]); }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    print_footer_and_html_epilogue();
?>
