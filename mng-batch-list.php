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
                    "bid" => t('all','BatchName'),
                    t('all','HotSpot'),
                    t('all','BatchStatus'),
                    t('all','TotalUsers'),
                    t('all','PlanName'),
                    t('all','PlanCost'),
                    t('all','BatchCost'),
                    "creationdate" => t('all','CreationDate'),
                    "creationby" => t('all','CreationBy')
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
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js"
    );
    
    $title = t('Intro','mngbatchlist.php');
    $help = t('helpPage','mngbatchlist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-batch.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = "";
    
    //reportQuery is assigned below to the SQL statement  in $sql
    $_SESSION['reportQuery'] = "";
    $_SESSION['reportType'] = "reportsBatchList";
    
    //orig: used as method to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT bh.id AS bid, bh.batch_name, bh.batch_description, bh.batch_status, COUNT(DISTINCT(ubi.id)) AS total_users,
                   ubi.planname, bp.plancost, bp.plancurrency, hs.name AS HotspotName, bh.creationdate, bh.creationby,
                   bh.updatedate, bh.updateby
              FROM %s AS bh LEFT JOIN %s AS ubi ON bh.id = ubi.batch_id
                           LEFT JOIN %s AS bp ON bp.planname = ubi.planname
                           LEFT JOIN %s AS hs ON bh.hotspot_id = hs.id
             GROUP BY bh.batch_name";
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                         $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                         $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                         $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);

    // set the session variable for report query (export)
    $_SESSION['reportQuery'] = $sql;
    
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
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-batch-del.php";
        
        $csrf_token = array(
                                "name" => "csrf_token",
                                "type" => "hidden",
                                "value" => dalo_csrf_token(),
                           );
?>
<form name="listall" method="POST" action="<?= $action ?>">

    <?= print_form_component($csrf_token); ?>

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
        printTableFormControls('batch_id[]', $action);
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
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

            //~ bh.id AS bid, bh.batch_name, bh.batch_description, bh.batch_status, COUNT(DISTINCT(ubi.id)) AS total_users,
            //~ ubi.planname, bp.plancost, bp.plancurrency, hs.name AS HotspotName, bh.creationdate, bh.creationby,
            //~ bh.updatedate, bh.updateby

            $id = intval($row['bid']);
            $hotspot_name = htmlspecialchars($row['HotspotName'], ENT_QUOTES, 'UTF-8');
            $batch_status = htmlspecialchars($row['batch_status'], ENT_QUOTES, 'UTF-8');
            $plancost = htmlspecialchars($row['plancost'], ENT_QUOTES, 'UTF-8');
            $total_users = htmlspecialchars($row['total_users'], ENT_QUOTES, 'UTF-8');
            $active_users = htmlspecialchars($row['active_users'], ENT_QUOTES, 'UTF-8');
            $batch_cost = htmlspecialchars(($row['active_users'] * $row['plancost']), ENT_QUOTES, 'UTF-8');
            $plan_currency = htmlspecialchars($row['plancurrency'], ENT_QUOTES, 'UTF-8');
        
            $plan_name = htmlspecialchars($row['planname'], ENT_QUOTES, 'UTF-8');
        
            $this_batch_name = htmlspecialchars($row['batch_name'], ENT_QUOTES, 'UTF-8');
            $this_batch_desc = htmlspecialchars($row['batch_description'], ENT_QUOTES, 'UTF-8');
            
            // tooltip stuff
            $onclick = 'javascript:return false;';
            
            $tooltipText = sprintf('<a class="toolTip" href="rep-batch-details.php?batch_name=%s">%s</a>',
                                   urlencode($this_batch_name), t('Tooltip','BatchDetails'))
                         . sprintf('<div style="margin: 15px auto" id="divContainerUserInfo"><strong>%s</strong>:<br>%s</div>',
                                   t('all','batchDescription'), $this_batch_desc);
?>

            <tr>
                <td>
                    <input type="checkbox" name="batch_id[]" value="<?= $id ?>">
                    <a class="tablenovisit" href="#" onclick='<?= $onclick ?>'
                        tooltipText='<?= $tooltipText ?>'><?= $this_batch_name ?></a>
                </td>
                
                <td><?= $hotspot_name ?></td>
                <td><?= $batch_status ?></td>
                <td><?= $total_users ?></td>
                <td><?= $plan_name ?></td>

                <td><?= $plancost ?></td>
                <td><?= $batch_cost ?></td>
                
                <td><?= htmlspecialchars($row['creationdate'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['creationby'], ENT_QUOTES, 'UTF-8') ?></td>
                
            </tr>

<?php
    }
?>
        </tbody>
        
<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>
        
    </table>
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
