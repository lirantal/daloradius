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
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

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
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );

    $title = t('Intro','mnghslist.php');
    $help = t('helpPage','mnghslist');

    print_html_prologue($title, $langCode, array(), $extra_js);

    // start printing content
    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    // we use this simplified query just to initialize $numrows
    $sql = sprintf("SELECT COUNT(id) FROM %s", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);
    $numrows = $res->fetchrow()[0];

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        // we execute and log the actual query
        $sql = "SELECT id, name, owner, company, type FROM %s ORDER BY %s %s LIMIT %s, %s";
        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                             $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        $per_page_numrows = $res->numRows();

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
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($id, $name, $owner, $company, $type) = $row;

            $ajax_id = "divContainerHotspotInfo" . $count;
            $param = sprintf('hotspot=%s', urlencode($name));
            $onclick = "ajaxGeneric('library/ajax/hotspot_info.php','retHotspotGeneralStat','$ajax_id','$param')";
            $tooltip = array(
                                'subject' => $name,
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-hs-edit.php?name=%s', urlencode($name) ), 'label' => t('Tooltip','HotspotEdit'), );
            $tooltip['actions'][] = array( 'href' => 'acct-hotspot-compare.php', 'label' => t('all','Compare'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'name[]', 'value' => $name, 'label' => $id );
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
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('../common/includes/db_close.php');

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
