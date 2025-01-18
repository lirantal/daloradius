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
                    "selected",
                    "groupname" => t('all','Groupname'),
                    "users" => t('all','TotalUsers')
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
               ? strtolower($_GET['orderType']) : "desc";


    // print HTML prologue
    $title = t('Intro','mngradprofiles.php');
    $help = t('helpPage','mngradprofileslist');
    
    print_html_prologue($title, $langCode);
    
    // start printing content
    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    //orig: used as method to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT DISTINCT(rgr.groupname) AS groupname, COUNT(DISTINCT(rug.username)) AS users
              FROM %s AS rgr LEFT JOIN %s AS rug ON rgr.groupname=rug.groupname
             GROUP BY rgr.groupname
            UNION 
            SELECT DISTINCT(rgc.groupname) AS groupname, COUNT(DISTINCT(rug.username)) AS users
              FROM %s AS rgc LEFT JOIN %s AS rug ON rgc.groupname=rug.groupname
             GROUP BY rgc.groupname";
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADGROUPREPLY'], $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                         $configValues['CONFIG_DB_TBL_RADGROUPCHECK'], $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
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
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-profiles-del.php";
        $form_name = "form_" . rand();

        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => sprintf("removeCheckbox('%s','%s')", $form_name, $action),
                                'label' => 'Delete',
                                'class' => 'btn-danger',
                              );
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        
        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'profile_names[]', 'additional_controls' => $additional_controls );
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
        
            list($groupname, $usercount) = $row;
        
            // preparing checkboxes and tooltips stuff
            $tooltip = array(
                                'subject' => $groupname,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-profiles-edit.php?profile_name=%s', urlencode($groupname), ), 'label' => t('button','EditProfile'), );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-rad-profiles-del.php?profile_name=%s', urlencode($groupname), ), 'label' => t('button','RemoveProfile'), );
        
            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);
            
            // create checkbox
            $d = array( 'name' => 'profile_names[]', 'value' => $groupname );
            $checkbox = get_checkbox_str($d);

            // build table row
            $table_row = array( $checkbox, $tooltip, $usercount );

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
