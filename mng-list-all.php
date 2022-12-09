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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    include_once('library/config_read.php');
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js"
    );
    
    $title = t('Intro','mnglistall.php');
    $help = t('helpPage','mnglistall');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-users.php");
    
    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    "selected",
                    "fullname" => t('all','Name'),
                    "username" => t('all','Username'),
                 );

    if (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes") {
        $cols[] = t('all','Password');
    } else {
        $cols["auth"] = t('all','Password');
    }
    
    $cols[] = t('title','Groups');

    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
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

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADCHECK'];
    $_SESSION['reportQuery'] = "";
    $_SESSION['reportType'] = "usernameListGeneric";

    // we use this simplified query just to initialize $numrows
    $sql0 = sprintf("SELECT COUNT(DISTINCT(username)) AS username
                       FROM %s
                      WHERE attribute='Auth-Type' OR attribute LIKE '%%-Password'", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql0);
    $logDebugSQL .= "$sql0;\n";
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
        // sql1 get id, username, password, firstname and lastname
        $sql1 = sprintf("SELECT rc.username AS username, rc.value AS auth, rc.attribute,
                                CONCAT(ui.firstname, ' ', ui.lastname) AS fullname
                           FROM %s AS rc, %s AS ui
                          WHERE rc.username=ui.username
                            AND (rc.attribute='Auth-Type' OR rc.attribute LIKE '%%-Password')
                          ORDER BY %s %s LIMIT %s, %s",
                        $configValues['CONFIG_DB_TBL_RADCHECK'], $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                        $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql1);
        $logDebugSQL .= "$sql1;\n";
        
        $per_page_numrows = $res->numRows();
        
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
                'type' => $type
            );
            // in the same pass we init the $usernamelist
            $usernamelist[] = sprintf("'%s'", $dbSocket->escapeSimple($this_username));
        }
        
        // with this second query we retrieve user status (enabled/disabled) and user groups list
        $sql2 = sprintf("SELECT username, groupname FROM %s WHERE username IN (%s)",
                        $configValues['CONFIG_DB_TBL_RADUSERGROUP'], implode(", ", $usernamelist));
        $res = $dbSocket->query($sql2);
        $logDebugSQL .= "$sql2;\n";

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
        $count = 0;
        foreach ($records as $username => $data) {
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            $type = $data['type'];
            
            $img = (!$data['enabled'])
                 ? '<img title="user is disabled" src="images/icons/userStatusDisabled.gif" alt="[disabled]">'
                 : '<img title="user is enabled" src="images/icons/userStatusActive.gif" alt="[enabled]">';
            
            $auth = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes")
                  ? "[Password is hidden]" : htmlspecialchars($data['auth'], ENT_QUOTES, 'UTF-8');
            
            $fullname = htmlspecialchars($data['fullname'], ENT_QUOTES, 'UTF-8');
            $grouplist = implode("<br>", $data['groups']);
            
            // tooltip and ajax stuff
            $onclick = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo',"
                             . "'divContainerUserInfo','username=%s');", urlencode($username));
            $content = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a>',
                               urlencode($username), t('Tooltip','UserEdit'));
            $arr = array(
                            'content' => $content,
                            'onClick' => $onclick,
                            'value' => urlencode($username),
                            'divId' => 'divContainerUserInfo'
                        );
            $tooltip = addToolTipBalloon($arr);
?>
            <tr>
                <td>
                    <label for="<?= "checkbox-$count" ?>">
                        <input type="checkbox" name="username[]" id="<?= "checkbox-$count" ?>" value="<?= $username ?>">
                    </label>
                </td>
                <td><?= "$fullname" ?></td>
                <td><?= "$img $tooltip" . sprintf(' <span class="badge badge-%s">%s</span>', strtolower($type), $type); ?></td>
                <td><?= ($type == 'USER') ? $auth : "" ?></td>
                <td><?= $grouplist ?></td>
            </tr>
<?php
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
