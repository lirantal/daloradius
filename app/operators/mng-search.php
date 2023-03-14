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
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // we partially strip some character and
    // leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username))
                  ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                  : "";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if (!empty($username)) {
         $logQuery .= sprintf("user(s) related to %s ", $username);
    } else {
        $logQuery .= "all users ";
    }
    $logQuery .= "on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    //feed the sidebar variables
    $search_username = $username_enc;


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );

    $title = t('Intro','mngsearch.php');

    print_html_prologue($title, $langCode, array(), $extra_js);

    if (!empty($username_enc)) {
        $title .=  " :: " . $username_enc;
    }

    $help = ((!empty($username_enc))
          ? sprintf("user(s) related to <em>%s</em>", $username_enc)
          : "all users") . " are shown";



    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    //
    // the variables cols, colspan, and half_colspan
    // can be used for validation an presentation purpose
    $cols = array(
                    "id" => t('all','ID'),
                    "fullname" => t('all','Name'),
                    "username" => t('all','Username'),
                 );

    if (!$hiddenPassword) {
        $cols["auth"] = t('all','Password');
    }

    $cols["lastlogin"] = t('all','LastLoginTime');
    $cols[] = t('title','Groups');

    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";



    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    // sql where is like: join_condition AND (nested_condition1) AND (nested_condition2)

    // init nested condition 1
    $nested_condition1 = array( "rc.attribute='Auth-Type'", "rc.attribute LIKE '%%-Password'" );

    // init nested condition 2
    $nested_condition2 = array();
    if (!empty($username)) {
        $arr = array( "username", "firstname", "lastname", "homephone", "workphone", "mobilephone" );
        $value_prefix = $dbSocket->escapeSimple($username);
        foreach ($arr as $field_name) {
            $nested_condition2[] = sprintf("ui.%s LIKE '%s%%'", $field_name, $value_prefix);
        }
    }

    // init SQL WHERE (with join condition already set)
    $sql_WHERE = array( "rc.username=ui.username" );

    // imploding nested condition 1
    $sql_WHERE[] = sprintf("(%s)", implode(" OR ", $nested_condition1));

    //imploding nested_condition 2
    if (count($nested_condition2) > 0) {
        $sql_WHERE[] = sprintf("(%s)", implode(" OR ", $nested_condition2));
    }

    // setup php session variables for exporting
    $_SESSION['reportTable'] = sprintf("%s AS rc LEFT JOIN %s AS ra ON ra.username=rc.username, %s AS ui",
                                       $configValues['CONFIG_DB_TBL_RADCHECK'], $configValues['CONFIG_DB_TBL_RADACCT'],
                                       $configValues['CONFIG_DB_TBL_DALOUSERINFO']);
    $_SESSION['reportQuery'] = " WHERE " . implode(" AND ", $sql_WHERE);
    $_SESSION['reportType'] = "usernameListGeneric";

    // we initialize $numrows
    $sql = sprintf("SELECT ui.id AS id, rc.username AS username, rc.value AS auth, rc.attribute,
                           CONCAT(COALESCE(ui.firstname, ''), ' ', COALESCE(ui.lastname, '')) AS fullname,
                           MAX(ra.acctstarttime) AS lastlogin
                      FROM %s %s
                     GROUP BY rc.username", $_SESSION['reportTable'], $_SESSION['reportQuery']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
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

        // init $records and $usernamelist arrays
        $records = array();
        $usernamelist = array();

        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            // we start storing data...
            // the enable flag is initialized to true
            // and the groups list is empty
            $this_username = $row['username'];

            if (array_key_exists($this_username, $records)) {
                continue;
            }

            // we try to get the type of this user
            if ($row['attribute'] == 'Auth-Type' && $row['auth'] == 'Accept') {
                if (preg_match(MACADDR_REGEX, $this_username) || preg_match(IP_REGEX, $this_username)) {
                    $type = 'MAC';
                } else {
                    $type = 'PIN';
                }
            } else {
                $type = 'USER';
            }

            $records[$this_username] = array(
                'auth' => $row['auth'],
                'fullname' => $row['fullname'],
                'enabled' => true,
                'groups' => array(),
                'type' => $type,
                'id' => $row['id'],
                'lastlogin' => $row['lastlogin'],
            );
            // in the same pass we init the $usernamelist
            $usernamelist[] = sprintf("'%s'", $dbSocket->escapeSimple($this_username));

        }

        $per_page_numrows = count($usernamelist);

        if ($per_page_numrows > 0) {

            // with this second query we retrieve user status (enabled/disabled) and user groups list
            $sql = sprintf("SELECT username, groupname FROM %s WHERE username IN (%s)",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], implode(", ", $usernamelist));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";


            // foreach user we update the enabled flag and the grouplist
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $this_username = $row['username'];
                $this_groupname = $row['groupname'];

                if ($this_groupname === 'daloRADIUS-Disabled-Users') {
                    $records[$this_username]['enabled'] = false;
                } else {
                    array_push($records[$this_username]['groups'],
                               htmlspecialchars($this_groupname, ENT_QUOTES, 'UTF-8'));
                }
            }
        }

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($username_enc) ? "&username=" . urlencode($username_enc) : "");

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-del.php";

        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => "javascript:removeCheckbox('listall','mng-del.php')",
                                'label' => 'Delete',
                                'class' => 'btn-danger',
                              );

        $additional_controls[] = array(
                                'onclick' => "disableCheckbox('listall','library/ajax/user_actions.php')",
                                'label' => 'Disable',
                                'class' => 'btn-primary',
                              );
        $additional_controls[] = array(
                                'onclick' => "enableCheckbox('listall','library/ajax/user_actions.php')",
                                'label' => 'Enable',
                                'class' => 'btn-secondary',
                              );

        $descriptors = array();

        $descriptors['start'] = array( 'common_controls' => 'username[]', 'additional_controls' => $additional_controls );

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                            'partial_query_string' => $partial_query_string
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
        $td_format = '<td>%s</td>';
        foreach ($records as $username => $data) {
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            $type = $data['type'];
            $id = intval($data['id']);

            $img_format = '<i class="bi bi-%s-circle-fill text-%s me-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="%s"></i>';

            $img = (!$data['enabled'])
                 ? sprintf($img_format, 'dash', 'danger', 'disabled')
                 : sprintf($img_format, 'check', 'success', 'enabled');

            $badge_icon = "";
            switch ($type) {
                case 'PIN':
                    $badge_icon = "123";
                    break;

                case 'MAC':
                    $badge_icon = "ethernet";
                    break;

                default:
                case 'USER':
                    $badge_icon = "person-fill";
                    break;
            }

            $badge = sprintf('<i class="bi bi-%s me-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="%s"></i>',
                             $badge_icon, strtolower($type));

            $auth = htmlspecialchars($data['auth'], ENT_QUOTES, 'UTF-8');

            $fullname = htmlspecialchars($data['fullname'], ENT_QUOTES, 'UTF-8');
            $lastlogin = (!empty($data['lastlogin']))
                       ? htmlspecialchars($data['lastlogin'], ENT_QUOTES, 'UTF-8') : "(n/a)";
            $grouplist = implode("<br>", $data['groups']);

            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip = array(
                                'subject' => sprintf('%s%s<span class="badge bg-primary ms-1">%s</span>', $img, $badge, $username),
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('mng-edit.php?username=%s', urlencode($username), ), 'label' => t('Tooltip','UserEdit'), );
            $tooltip['actions'][] = array( 'href' => sprintf('acct-username.php?username=%s', urlencode($username), ), 'label' => t('all','Accounting'), );

            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);

            // create checkbox
            $d = array( 'name' => 'username[]', 'value' => $username, 'label' => $id );
            $checkbox = get_checkbox_str($d);

            // define table row
            $table_row = array( $checkbox, $fullname, $tooltip );
            if (!$hiddenPassword) {
                $table_row[] = ($type == 'USER') ? $auth : "(n/a)";
            }

            $table_row[] = $lastlogin;
            $table_row[] = $grouplist;

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

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
?>
