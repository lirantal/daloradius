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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    include_once("lang/main.php");
    include("library/layout.php");

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
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";

    //feed the sidebar variables
    $search_username = $username_enc;


    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js"
    );
    
    $title = t('Intro','mngsearch.php');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    if (!empty($username_enc)) {
        $title .=  " :: " . $username_enc;
    }

    $help = (!empty($username_enc)) ? "looked for user $username_enc" : "no user specified";

    include ("menu-mng-users.php");
    
    // these three variable can be used for validation an presentation purpose
    $cols = array(
                   'id' => t('all','ID'),
                   'username' => t('all','Username'),
                   'fullname' => 'Full name'
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include('library/opendb.php');
    include('include/management/pages_common.php');

    $sql_WHERE = array();
    if (!empty($username)) {
        array_push($sql_WHERE,
                   sprintf("ui.username LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.username LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.firstname LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.lastname LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.homephone LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.workphone LIKE '%s%%'", $dbSocket->escapeSimple($username)),
                   sprintf("ui.mobilephone LIKE '%s%%'", $dbSocket->escapeSimple($username)));
    }

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADCHECK'];
    $_SESSION['reportQuery'] = sprintf(" WHERE username LIKE '%s%%'", $dbSocket->escapeSimple($username));
    $_SESSION['reportType'] = "usernameListGeneric";

    $sql_format = "SELECT DISTINCT(ui.username) AS username,
                          CONCAT(COALESCE(ui.firstname, ''), ' ', COALESCE(ui.lastname, '')) AS fullname,
                          ui.id, IFNULL(rug.username, 0) AS disabled
                   FROM %s AS ui LEFT JOIN %s AS rug ON rug.username=ui.username AND rug.groupname='daloRADIUS-Disabled-Users'";
    $sql = sprintf($sql_format, $configValues['CONFIG_DB_TBL_DALOUSERINFO'], $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
    if (count($sql_WHERE) > 0) {
        $sql .= " WHERE " . implode(" OR ", $sql_WHERE);
    }
    $sql .= " GROUP BY username";
    
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
        $sql .= " ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($username_enc) ? "&usernameOnline=" . urlencode($username_enc) : "");
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-del.php";
?>
        
<form name="searchusers" method="POST" action="<?= $action ?>">
    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
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
        printTableFormControls('username[]', $action, 'searchusers');
?>
                </th>
            </tr>
            
            <tr>
<?php
        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
        </thead>
        
        <tbody>
<?php
        while ($row = $res->fetchRow()) {
            // username, fullname, ui.id, disabled
            
            $disabled = $row[3] !== '0';
            
            $img = ($disabled)
                 ? '<img title="user is disabled" src="images/icons/userStatusDisabled.gif" alt="[disabled]">'
                 : '<img title="user is enabled" src="images/icons/userStatusActive.gif" alt="[enabled]">';

            $this_username_enc = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
            $this_fullname_enc = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');

            $id = intval($row[2]);

            // tooltip and ajax stuff
            $li_style = 'margin: 7px auto';
            $tooltip = '<ul style="list-style-type: none">'
                     . sprintf('<li style="%s"><a class="toolTip" href="mng-edit.php?username=%s">%s</a></li>',
                               $li_style, urlencode($this_username_enc), t('Tooltip','UserEdit'))
                     . sprintf('<li style="%s"><a class="toolTip" href="config-maint-test-user.php?username=%s&password=%s">%s</a></li>',
                               $li_style, urlencode($this_username_enc), urlencode($this_fullname_enc), t('all','TestUser'))
                     . sprintf('<li style="%s"><a class="toolTip" href="acct-username.php?username=%s">%s</a></li>',
                               $li_style, urlencode($this_username_enc), t('all','Accounting'))
                     . '</ul>'
                     . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>';

            $onclick = "javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo','username="
                     . urlencode($this_username_enc) . "');return false;";
?>

            <tr>
                <td>
                    <input type="checkbox" name="username[]" value="<?= $this_username_enc ?>">
                    <?= $id ?>
                </td>
                <td>
                    <?= $img ?>
                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltip ?>'>
                        <?= $this_username_enc ?>
                    </a>
                </td>
                <td><?= $this_fullname_enc ?></td>
            </tr>

<?php
        }
?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
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
