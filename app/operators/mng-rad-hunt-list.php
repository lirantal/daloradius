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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    require_once __DIR__ . '/library/huntgroup_pages_pdo.php';

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "id" => t('all','HgID'),
                    "groupname" => t('all','HgGroupName'),
                    "nasipaddress" => t('all','HgIPHost'),
                    "nasportid" => t('all','HgPortId'),
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy=isset($_GET['orderBy']) && is_string($_GET['orderBy']) && in_array($_GET['orderBy'],array_keys($param_cols),true)
             ? $_GET['orderBy'] : 'id';
    $orderType=isset($_GET['orderType']) && is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']),array('desc','asc'),true)
               ? strtolower($_GET['orderType']) : 'asc';

    // print HTML prologue
    $title = t('Intro','mngradhuntlist.php');
    $help = t('helpPage','mngradhuntlist');

    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);

    include('include/management/pages_common.php');
    $numrows=0;
    try {
        $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
        $table=dalo_hunt_table($configValues);
        $numrows=(int)dalo_hunt_query($pdo,"SELECT COUNT(id) FROM $table")->fetchColumn();
    } catch (Throwable $e) { $failureMsg='Unable to load huntgroups'; }

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */

        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php'); // Uses the loaded listing configuration.

        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

        /* END */

        $rows=array();
        try {
            $sql="SELECT id,groupname,nasipaddress,nasportid FROM $table ORDER BY $orderBy $orderType LIMIT ?,?";
            $rows=dalo_hunt_query($pdo,$sql,array((int)$offset,(int)$rowsPerPage))->fetchAll(PDO::FETCH_NUM);
        } catch (Throwable $e) { $failureMsg='Unable to load huntgroups'; }
        $per_page_numrows=count($rows);

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-rad-hunt-del.php";

        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
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
        foreach ($rows as $row) {
            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)$row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($id, $groupname, $nasipaddress, $nasportid) = $row;

            // preparing checkbox
            $item_id = 'huntgroup-' . $id;

            $tooltip = array(
                                'subject' => $groupname,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-hunt-edit.php?item=%s', $item_id, ), 'label' => t('Tooltip','EditHG'), );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-hunt-del.php?item[]=%s', $item_id, ), 'label' => t('Tooltip','RemoveHG'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'item[]', 'value' => $item_id, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // build table row
            $table_row = array( $checkbox, $tooltip, $nasipaddress, $nasportid );

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
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = $failureMsg ?? "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    if (isset($failureMsg)) { include_once('include/management/actionMessages.php'); }

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
