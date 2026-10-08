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
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);

    require_once __DIR__ . '/library/hotspot_pages_pdo.php';

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    'id' => t('all','ID'),
                    'name' => t('all','HotSpot'),
                    'owner' => t('ContactInfo','OwnerName'),
                    'company' => t('ContactInfo','Company'),
                    'type' => t('ContactInfo','HotspotType')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy=isset($_GET['orderBy']) && is_string($_GET['orderBy']) && in_array($_GET['orderBy'],array_keys($param_cols),true) ? $_GET['orderBy'] : 'id';
    $orderType=isset($_GET['orderType']) && is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']),array('asc','desc'),true) ? strtolower($_GET['orderType']) : 'asc';

    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js"
    );

    $title = t('Intro','mnghslist.php');
    $help = t('helpPage','mnghslist');

    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    $numrows=0;
    try {
        $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');$table=dalo_hotspot_table($configValues);
        $numrows=(int)dalo_hotspot_query($pdo,"SELECT COUNT(id) FROM $table")->fetchColumn();
    } catch (Throwable $e) { $failureMsg='Unable to load hotspots'; }

    if ($numrows > 0) {
        // when $numrows is set, $maxPage is calculated inside this include file
        // Uses the already loaded listing configuration.
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_numbering.php' ]);
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        $rows=array();
        try {
            $rows=dalo_hotspot_query($pdo,"SELECT id,name,owner,company,type FROM $table ORDER BY $orderBy $orderType LIMIT ?,?",array((int)$offset,(int)$rowsPerPage))->fetchAll(PDO::FETCH_NUM);
        } catch (Throwable $e) { $failureMsg='Unable to load hotspots'; }
        $per_page_numrows=count($rows);

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-hs-del.php";
        $form_name = "form_" . rand();

        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => sprintf("removeCheckbox('%s','%s')", $form_name, $action),
                                'label' => 'Delete',
                                'class' => 'btn-danger',
                              );

        $descriptors = array();

        $descriptors['start'] = array( 'common_controls' => 'name[]', 'additional_controls' => $additional_controls );

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        print_table_prologue($descriptors);

        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => $form_name ), );

        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        foreach ($rows as $row) {
            $raw_name=(string)$row[1];
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)$row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($id, $name, $owner, $company, $type) = $row;

            $ajax_id = "divContainerHotspotInfo" . $count;
            $param = sprintf('hotspot=%s', rawurlencode($raw_name));
            $onclick = "daloInfo.hotspot('$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $name,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-hs-edit.php?name=%s', rawurlencode($raw_name) ), 'label' => t('Tooltip','HotspotEdit'), );
            $tooltip['actions'][] = array( 'href' => 'acct-hotspot-compare.php', 'label' => t('all','Compare'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'name[]', 'value' => $raw_name, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // define table row
            $table_row = array( $checkbox, $tooltip, $owner, $company, $type );

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
        $descriptor = array( 'form' => $form_descriptor, 'table_foot' => $table_foot );

        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);
    }

    if (isset($failureMsg)) { include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]); }
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
