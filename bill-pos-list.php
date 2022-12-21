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
    
    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";
    
    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js"
    );
    
    $title = t('Intro','billposlist.php');
    $help = t('helpPage','billposlist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    // we partially strip some character and
    // leave validation/escaping to other functions used later in the script
    $planname = (array_key_exists('planname', $_GET) && isset($_GET['planname']))
              ? str_replace("%", "", $_GET['planname']) : "";
    
    $planname_enc = (!empty($planname))
                  ? htmlspecialchars($planname, ENT_QUOTES, 'UTF-8')
                  : "";

    if (!empty($planname_enc)) {
        $title .=  " :: " . $planname_enc;
    }

    include("menu-bill-pos.php");

    $cols = array(
                    "id" => t('all','ID'),
                    "contactperson" => t('ContactInfo','ContactPerson'),
                    "company" => t('ContactInfo','Company'),
                    "username" => t('all','Username'),
                    t('all','Password'),
                    "planname" => t('ContactInfo','PlanName')
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


    // start printing content
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';


    include('library/opendb.php');
    include('include/management/pages_common.php');

    $sql_WHERE = array();
    $sql_WHERE[] = "rc.username = ui.username";
    $sql_WHERE[] = "(rc.attribute LIKE '%-Password' OR rc.attribute = 'Auth-Type')";
    
    if (!empty($planname)) {
        $sql_WHERE[] = sprintf("ubi.planname LIKE '%s%%' ", $dbSocket->escapeSimple($planname));
    }

    $sql = sprintf("SELECT DISTINCT(rc.username) AS username, rc.id, rc.value, rc.attribute, ubi.contactperson,
                           ubi.billstatus, ubi.planname, ubi.company, ui.firstname, IFNULL(rug.username, 0) AS disabled
                      FROM %s AS ui, %s AS rc
                      LEFT JOIN %s AS ubi ON rc.username=ubi.username
                      LEFT JOIN %s AS rug ON rug.username=rc.username AND rug.groupname='daloRADIUS-Disabled-Users'",
                   $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                   $configValues['CONFIG_DB_TBL_RADCHECK'],
                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'])
         . " WHERE " . implode(" AND ", $sql_WHERE) . "GROUP BY username";
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
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($planname_enc))
                              ? sprintf("&vendor=%s", urlencode($planname_enc)) : "";
                              
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-del.php";
?>

<form name="listall" method="POST" action="<?= $action ?>">
    <table border="0" class="table1">
        <thead>
            
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>' . '</tr>';
        }
?>
            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
        printTableFormControls('username[]', $action);
?>
                    <input class="button" type="button" value="Disable"
                        onclick="javascript:disableCheckbox('listall', 'include/management/userOperations.php')">
                    
                    <input class="button" type="button" value="Enable"
                        onclick="javascript:enableCheckbox('listall', 'include/management/userOperations.php')">
                    
                    <br>
                    
                    <input class="button" type="button" value="Refill Session Time"
                        onclick="javascript:refillSessionTimeCheckbox('listall', 'include/management/userOperations.php')">
                    <input class="button" type="button" value="Refill Session Traffic"
                        onclick="javascript:refillSessionTrafficCheckbox('listall','include/management/userOperations.php'">
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
        $count = 1;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($username, $id, $value, $attribute, $contactperson, $billstatus, $planname, $company, $firstname, $disabled) = $row;
            
            $img = (boolval($disabled))
                 ? '<img title="user is disabled" src="images/icons/userStatusDisabled.gif" alt="[disabled]">'
                 : '<img title="user is enabled" src="images/icons/userStatusActive.gif" alt="[enabled]">';
            
            $auth = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes")
                  ? "[Password is hidden]" : $value;
            
            $tooltipText = sprintf('<a class="toolTip" href="bill-pos-edit.php?username=%s">%s</a><br><br>'
                                 . '<div style="margin: 15px auto" id="divContainerUserInfo">Loading...</div>',
                                  urlencode($username), t('Tooltip','UserEdit'));
            $onclick = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo',"
                             . "'divContainerUserInfo','username=%s');return false;", $username);
?>
            <tr>
                <td>
                    <input type="checkbox" name="username[]" value="<?= $username ?>" id="<?= "checkbox-$count" ?>">
                    <label for="<?= "checkbox-$count" ?>"><?= $id ?></label>
                </td>
                <td><?= $contactperson ?></td>
                <td><?= $company ?></td>
                <td>
                    <?= $img ?>
                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltipText ?>'>
                        <?= $username ?>
                    </a>
                </td>
                <td><?= $auth ?></td>
                <td><?= $planname ?></td>
            </tr>
<?php
            $count++;
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
