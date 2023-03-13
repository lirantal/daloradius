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
        "username" => t('all','Username'),
        "fullname" => t('all','Name'),
        t('all','Groupname') . " (" . t('all','Priority') . ")",
        "selected"
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

    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    
    // print HTML prologue
    $title = t('Intro','mngradusergrouplist');
    $help = t('helpPage','mngradusergrouplist');
    
    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    $sql0 = sprintf("SELECT DISTINCT(rug1.username), CONCAT(dui.firstname, ' ', dui.lastname) AS fullname
                           FROM %s AS rug1 LEFT JOIN %s AS dui ON rug1.username=dui.username",
                         $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
        if (!empty($username)) {
            $sql0 .= sprintf(" WHERE rug1.username LIKE '%s%%'", $dbSocket->escapeSimple($username));
        }
    
    $res = $dbSocket->query($sql0);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        $records = array();
        
        $sql0 .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res0 = $dbSocket->query($sql0);

        $per_page_numrows = $res0->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($username_enc) ? "&username=" . urlencode($username_enc) : "");
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-usergroup-del.php";
        $form_name = "form_" . rand();

        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => sprintf("removeCheckbox('%s','%s')", $form_name, $action),
                                'label' => 'Delete',
                                'class' => 'btn-danger',
                              );
        
        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        
        $descriptors = array();
        $descriptors['start'] = array( 'common_controls' => 'usergroup[]', 'additional_controls' => $additional_controls );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );
        print_table_prologue($descriptors);
        
        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => $form_name ), );
        
        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        echo "</tr>";

        // closes table header, opens table body
        print_table_middle();
        
        function print_user_group_prio($this_username, $this_groupname, $this_priority) {
            
            // preparing checkboxes and tooltips stuff
            $tooltip = array(
                                'subject' => sprintf('%s (%s)', $this_groupname, $this_priority),
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 
                                            'href' => sprintf('mng-rad-usergroup-edit.php?username=%s&current_group=%s',
                                                              urlencode($this_username), urlencode($this_groupname) ),
                                            'label' => t('Tooltip','EditUserGroup'),
                                         );
            $tooltip['actions'][] = array(
                                            'href' => sprintf('mng-rad-usergroup-list-user.php?username=%s&group=%s',
                                                              urlencode($this_username), urlencode($this_groupname) ),
                                            'label' => t('Tooltip','ListUserGroups'), );

            echo '<td>';
            print_tooltip_list($tooltip);
            echo '</td>';
            
            echo '<td>';
            $d = array( 'name' => 'usergroup[]', 'value' => sprintf("%s||%s", $this_username, $this_groupname) );
            print_checkbox($d);
            echo '</td>';

        }
        
        while ($row0 = $res0->fetchRow()) {
            $row0len = count($row0);
        
            // escape row elements
            for ($i = 0; $i < $row0len; $i++) {
                $row0[$i] = htmlspecialchars($row0[$i], ENT_QUOTES, 'UTF-8');
            }
        
            list($this_username, $fullname) = $row0;
            $records[$this_username] = array(
                'fullname' => (!empty(trim($fullname))) ? $fullname : "(n/d)",
                'groups' => array()
            );
            
            $sql1 = sprintf("SELECT groupname, priority FROM %s WHERE username='%s' ORDER BY priority ASC, groupname ASC",
                            $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($this_username));
            $res1 = $dbSocket->query($sql1);
            
            while ($row1 = $res1->fetchRow()) {
                $row1len = count($row1);
        
                // escape row elements
                for ($i = 0; $i < $row1len; $i++) {
                    $row1[$i] = htmlspecialchars($row1[$i], ENT_QUOTES, 'UTF-8');
                }
            
                list($this_groupname, $this_priority) = $row1;
                $records[$this_username]['groups'][] = array( 'groupname' => $this_groupname, 'priority' => $this_priority );
            }
        }
        
        
        foreach ($records as $this_username => $data) {
            $rowspan = count($data['groups']);
            $group = $data['groups'][0];
            
            echo "<tr>";
            
            printf('<td rowspan="%s">%s</td>', $rowspan, $this_username);
            printf('<td rowspan="%s">%s</td>', $rowspan, $data['fullname']);
            
            print_user_group_prio($this_username, $group['groupname'], $group['priority']);
            
            echo "</tr>";
            
            if ($rowspan > 1) {
                for ($i = 1; $i < $rowspan; $i++) {
                    $group = $data['groups'][$i];
                    echo "<tr>";
                    print_user_group_prio($this_username, $group['groupname'], $group['priority']);                    
                    echo "</tr>";
                }
            }
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

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
