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
    $orderBy = (array_key_exists('orderBy', $_GET) && is_string($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && is_string($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    $username = is_string($_GET['username'] ?? null) ? str_replace('%', '', trim($_GET['username'])) : '';
    $username_enc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');


    // print HTML prologue
    $title = t('Intro','mngradusergrouplist');
    $help = t('helpPage','mngradusergrouplist');

    print_html_prologue($title, $langCode);

    // start printing content
    print_title_and_help($title, $help);

    require_once __DIR__ . '/library/user_group_pages_pdo.php';
    $pdo = null;
    try {
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $mapping_table = dalo_usergroup_table($configValues, 'CONFIG_DB_TBL_RADUSERGROUP');
        include('include/management/pages_common.php');

        $info_table = dalo_usergroup_table($configValues, 'CONFIG_DB_TBL_DALOUSERINFO');
        $sql_filter = $username !== '' ? ' WHERE rug1.username LIKE ?' : '';
        $filter_values = $username !== '' ? array('%' . $username . '%') : array();
        $numrows = (int)dalo_usergroup_query($pdo, "SELECT COUNT(DISTINCT(rug1.username)) FROM $mapping_table AS rug1$sql_filter",
                                           $filter_values)->fetchColumn();
        $sql0 = "SELECT rug1.username,MAX(CONCAT(dui.firstname,' ',dui.lastname)) AS fullname
                   FROM $mapping_table AS rug1 LEFT JOIN $info_table AS dui ON rug1.username=dui.username$sql_filter
                  GROUP BY rug1.username";

        if ($numrows > 0) {
            /* START - Related to pages_numbering.php */

            // when $numrows is set, $maxPage is calculated inside this include file
            include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                                  // the CONFIG_IFACE_TABLES_LISTING variable from the config file

            // here we decide if page numbers should be shown
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;

            /* END */

            $records = array();

            $sql0 .= " ORDER BY `$orderBy` $orderType LIMIT ?,?";
            $rows0 = dalo_usergroup_query($pdo, $sql0, array_merge($filter_values, array((int)$offset, (int)$rowsPerPage)))->fetchAll(PDO::FETCH_NUM);
            $per_page_numrows = count($rows0);

            // the partial query is built starting from user input
            // and for being passed to setupNumbering and setupLinks functions
            $partial_query_string = (!empty($username_enc) ? "&username=" . urlencode($username) : "");

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
                                    'subject' => sprintf('%s (%s)', htmlspecialchars($this_groupname, ENT_QUOTES, 'UTF-8'), $this_priority),
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
                $d = array( 'name' => 'usergroup[]', 'value' => htmlspecialchars(dalo_usergroup_selection($this_username, $this_groupname), ENT_QUOTES, 'UTF-8') );
                print_checkbox($d);
                echo '</td>';

            }

            $usernames = array();
            foreach ($rows0 as $row0) {
                list($this_username, $fullname) = $row0;
                $usernames[] = $this_username;
                $fullname = (string)$fullname;
                $records[$this_username] = array(
                    'fullname' => trim($fullname) !== '' ? htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') : '(n/d)',
                    'groups' => array()
                );
            }
            if ($usernames) {
                $placeholders = implode(',', array_fill(0, count($usernames), '?'));
                $sql1 = "SELECT username,groupname,priority FROM $mapping_table WHERE username IN ($placeholders)
                          ORDER BY username ASC,priority ASC,groupname ASC";
                $rows1 = dalo_usergroup_query($pdo, $sql1, $usernames)->fetchAll(PDO::FETCH_NUM);
                foreach ($rows1 as $row1) {
                    list($this_username, $this_groupname, $this_priority) = $row1;
                    $records[$this_username]['groups'][] = array('groupname' => $this_groupname, 'priority' => $this_priority);
                }
            }

            foreach ($records as $this_username => $data) {
                if (!$data['groups']) { continue; }
                $rowspan = count($data['groups']);
                $group = $data['groups'][0];

                echo "<tr>";

                printf('<td rowspan="%s">%s</td>', $rowspan, htmlspecialchars((string)$this_username, ENT_QUOTES, 'UTF-8'));
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

    } catch (Throwable $error) {
        echo '<div class="alert alert-danger">Unable to load user-group mappings.</div>';
    } finally { $pdo = null; }

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
