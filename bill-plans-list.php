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
    include("library/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];
    
    $cols = array(
                    "planName" => t('all','PlanName'),
                    "planType" => t('all','PlanType'),
                    "planActive" => t('all','PlanActive'),
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


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );
    
    $title = t('Intro','billplanslist.php');
    $help = t('helpPage','billplanslist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-bill-plans.php");


    // start printing content
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');

    // we use this simplified query just to initialize $numrows
    $sql = sprintf("SELECT COUNT(DISTINCT(planName)) FROM %s", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']);
    $res = $dbSocket->query($sql);
    $numrows = $res->fetchrow()[0];
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
        
        $sql = sprintf("SELECT planName, planType, planActive FROM %s ORDER BY %s %s LIMIT %s, %s",
                       $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "bill-plans-del.php";
?>

<form name="listall" method="POST" action="<?= $action ?>">
    <table border="0" class="table1">
        <thead>
            
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>' . '</tr>';
        }
?>          
            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
        printTableFormControls('planName[]', $action);
?>
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
        $count = 1;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
            
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
        
            list($planName, $planType, $planActive) = $row;
            
            $tooltipText = '<ul style="list-style-type: none">'
                         . sprintf('<li style="%s"><a class="toolTip" href="bill-plans-edit.php?planName=%s">%s</a></li>',
                                   $li_style, urlencode($planName), t('button','EditPlan'))
                         . sprintf('<li style="%s"><a class="toolTip" href="bill-plans-del.php?planName=%s">%s</a></li>',
                                   $li_style, urlencode($planName), t('button','RemovePlan'))
                         . '</ul>';
            $onclick = 'javascript:return false;';
?>
            <tr>
                <td>
                    <input type="checkbox" name="planName[]" value="<?= $planName ?>" id="<?= "checkbox-$count" ?>">
                    <label for="<?= "checkbox-$count" ?>">
                        <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltipText ?>'>
                            <?= $planName ?>
                        </a>
                    </label>
                </td>
                <td><?= $planType ?></td>
                <td><?= $planActive ?></td>
            </tr>
<?php
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

    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">

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
