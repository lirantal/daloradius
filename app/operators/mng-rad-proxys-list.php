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
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "id" => t('all','ID'),
                    "proxyname" => t('all','ProxyName'),
                    "creationdate" => t('all','CreationDate'),
                    "creationby" => t('all','CreationBy'),
                    "updatedate" => t('all','UpdateDate'),
                    "updateby" => t('all','UpdateBy')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                is_string($_GET['orderBy']) && in_array($_GET['orderBy'], array_keys($param_cols), true))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']), array( "desc", "asc" ), true))
               ? strtolower($_GET['orderType']) : "asc";


    // print HTML prologue
    $title = t('Intro','mngradproxys.php');
    $help = t('helpPage','mngradproxyslist');

    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);

    require_once __DIR__ . '/include/management/selectbox_read.php';
    require_once __DIR__ . '/include/management/read_helpers_pdo.php';
    include('include/management/pages_common.php');
    $catalog_pdo = null;
    $catalog_rows = array();
    $numrows = 0;
    $catalog_failed = false;
    try {
        $catalog_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $table = dalo_selectbox_table($catalog_pdo, $configValues, 'CONFIG_DB_TBL_DALOPROXYS');
        $sql = "SELECT COUNT(id) FROM $table";
        $numrows = (int) $catalog_pdo->query($sql)->fetchColumn();
        $logDebugSQL .= "$sql;\n";
        if ($numrows > 0) {
            $page_size = $configValues['CONFIG_IFACE_TABLES_LISTING'] ?? null;
            if ((!is_int($page_size) && !is_string($page_size)) ||
                !ctype_digit((string) $page_size) || (int) $page_size < 1 ||
                (float) $page_size > PHP_INT_MAX) {
                throw new InvalidArgumentException('Invalid catalog page size');
            }
            include('include/management/pages_numbering.php');
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
            $sort = dalo_read_identifier($catalog_pdo, $orderBy);
            $sql = "SELECT id, proxyname, creationdate, creationby, updatedate, updateby FROM $table ORDER BY $sort $orderType LIMIT :limit OFFSET :offset";
            $catalog_stmt = $catalog_pdo->prepare($sql);
            $catalog_stmt->bindValue(':limit', (int) $rowsPerPage, PDO::PARAM_INT);
            $catalog_stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $catalog_stmt->execute();
            $catalog_rows = $catalog_stmt->fetchAll(PDO::FETCH_NUM);
            $logDebugSQL .= "$sql;\n";
        }
    } catch (Throwable $error) {
        $catalog_failed = true;
        error_log('Catalog read failed: ' . get_class($error));
    } finally {
        $catalog_stmt = null;
        $catalog_pdo = null;
    }
    if ($catalog_failed) {
        $failureMsg = "Unable to load catalog";
        include_once("include/management/actionMessages.php");
    } elseif ($numrows > 0) {
        $per_page_numrows = count($catalog_rows);

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-rad-proxys-del.php";

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
        foreach ($catalog_rows as $row) {

            $rowlen = count($row);

            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string) $row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($id, $proxyname, $creationdate, $creationby, $updatedate, $updateby) = $row;

            // preparing checkboxes and tooltips stuff
            $id = intval($id);
            $item_id = sprintf("proxy-%d", $id);

            $tooltip = array(
                                'subject' => $proxyname,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-proxys-edit.php?item=%s', $item_id, ), 'label' => t('Tooltip','EditProxy'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'item[]', 'value' => $item_id, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // build table row
            $table_row = array( $checkbox, $tooltip, $creationdate, $creationby, $updatedate, $updateby );

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
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    unset($catalog_rows);

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
