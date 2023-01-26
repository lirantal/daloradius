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
    include_once('library/config_read.php');

    include_once("lang/main.php");
    include_once("library/validation.php");
    include("library/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logQuery = "performed query for all usernames on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );

    $title = t('Intro','mnglistall.php');
    $help = t('helpPage','mnglistall');

    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-users.php");

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


    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';

    include('library/opendb.php');
    include('include/management/pages_common.php');

    // sql where is like: join_condition AND (nested_condition1)

    // init nested condition 1
    $nested_condition1 = array( "rc.attribute='Auth-Type'", "rc.attribute LIKE '%%-Password'" );

    // init SQL WHERE (with join condition already set)
    $sql_WHERE = array( "rc.username=ui.username" );

    // imploding nested condition 1
    $sql_WHERE[] = sprintf("(%s)", implode(" OR ", $nested_condition1));

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
                if (filter_var($this_username, FILTER_VALIDATE_MAC)) {
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

        // this can be passed as form attribute and
        // printTableFormControls function parameter
        $action = "mng-del.php";
?>

<form name="listall" method="POST" action="<?= $action ?>">

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>';
        }
?>
                <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>

            </tr>

            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
        printTableFormControls('username[]', $action);
?>
                    <input class="button" type="button" value="Disable"
                        onclick="javascript:disableCheckbox('listall','include/management/userOperations.php')">
                    <input class="button" type="button" value="Enable"
                        onclick="javascript:enableCheckbox('listall','include/management/userOperations.php')">
                </th>
            </tr>

            <tr>
<?php
        // second line of table header
        printTableHead($cols, $orderBy, $orderType);
?>
            </tr>

        </thead>

        <tbody>
<?php

        $li_style = 'margin: 7px auto';
        $count = 0;
        foreach ($records as $username => $data) {
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            $type = $data['type'];
            $id = intval($data['id']);

            $img = (!$data['enabled'])
                 ? '<img title="user is disabled" src="static/images/icons/userStatusDisabled.gif" alt="[disabled]">'
                 : '<img title="user is enabled" src="static/images/icons/userStatusActive.gif" alt="[enabled]">';

            $auth = htmlspecialchars($data['auth'], ENT_QUOTES, 'UTF-8');

            $fullname = htmlspecialchars($data['fullname'], ENT_QUOTES, 'UTF-8');
            $lastlogin = (!empty($data['lastlogin']))
                       ? htmlspecialchars($data['lastlogin'], ENT_QUOTES, 'UTF-8') : "(n/a)";
            $grouplist = implode("<br>", $data['groups']);

            // tooltip and ajax stuff
            $tooltipText = '<ul style="list-style-type: none">'
                     . sprintf('<li style="%s"><a class="toolTip" href="mng-edit.php?username=%s">%s</a></li>',
                               $li_style, urlencode($username), t('Tooltip','UserEdit'))
                     . sprintf('<li style="%s"><a class="toolTip" href="config-maint-test-user.php?username=%s&password=%s">%s</a></li>',
                               $li_style, urlencode($username), urlencode($fullname), t('all','TestUser'))
                     . sprintf('<li style="%s"><a class="toolTip" href="acct-username.php?username=%s">%s</a></li>',
                               $li_style, urlencode($username), t('all','Accounting'))
                     . '</ul>'
                     . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>';

            $onclick = "javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo','username="
                     . urlencode($username) . "');return false;";

            echo '<tr>';
            printf('<td><input type="checkbox" name="username[]" value="%s" id="checkbox-%d">', $username, $count);
            printf('<label for="checkbox-%d">%d</label></td>', $count, $id);
            printf('<td>%s</td>', $fullname);
            printf('<td>%s <a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a>",
                   $img, $onclick, $tooltipText, $username);
            printf(' <span class="badge badge-%s">%s</span></td>', strtolower($type), $type);

            if (!$hiddenPassword) {
                echo '<td>'
                   . (($type == 'USER') ? $auth : "(n/a)")
                   . '</td>';
            }

            printf('<td>%s</td>', $lastlogin);
            printf('<td>%s</td>', $grouplist);

            echo '</tr>';

            $count++;
        }
?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>

    </table>

    <input type="hidden" name="csrf_token" value="<?= dalo_csrf_token() ?>">

</form>

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('library/closedb.php');

    include('include/config/logging.php');

    $inline_extra_js = "
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip()";

    print_footer_and_html_epilogue($inline_extra_js);
?>
