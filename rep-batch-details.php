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

    //~ include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    
    // validate this parameter before including menu
    $batch_name = (array_key_exists('batch_name', $_GET) && !empty(str_replace("%", "", trim($_GET['batch_name']))))
                ? str_replace("%", "", trim($_GET['batch_name'])) : "";
    $batch_name_enc = (!empty($batch_name)) ? htmlspecialchars($batch_name, ENT_QUOTES, 'UTF-8') : "";

    // feed the sidebar
    $batch_name_details = $batch_name_enc;

    $log = "visited page: ";
    $logQuery = "performed query for batch [$batch_name] on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    include_once("lang/main.php");
    
    // first table
    $cols1 = array(
                    t('all','BatchName'),
                    t('all','HotSpot'),
                    t('all','BatchStatus'),
                    t('all','TotalUsers'),
                    t('all','ActiveUsers'),
                    t('all','PlanName'),
                    t('all','PlanCost'),
                    t('all','BatchCost'),
                    t('all','CreationDate'),
                    t('all','CreationBy')
                  );
    $colspan1 = count($cols1);
    $half_colspan1 = intval($colspan1 / 2);
    
    // second table
    $cols2 = array(
                    "batch_name" => t('all','BatchName'),
                    t('all','Username'),
                    t('all','StartTime')
                  );
    $colspan2 = count($cols2);
    $half_colspan2 = intval($colspan2 / 2);
    
    $param_cols2 = array();
    foreach ($cols2 as $k => $v) { if (!is_int($k)) { $param_cols2[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols2)))
             ? $_GET['orderBy'] : array_keys($param_cols2)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";
    
    include("library/layout.php");

    // print HTML prologue   
    $title = t('Intro','repbatchdetails.php');
    $help = t('helpPage','repbatchdetails');
    
    print_html_prologue($title, $langCode);

    include ("menu-reports-batch.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    // get $batch_id
    $batch_id = -1;
    
    if ($batch_name) {
        $sql = sprintf("SELECT bh.id FROM %s AS bh WHERE bh.batch_name = '%s' LIMIT 1", 
                       $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $dbSocket->escapeSimple($batch_name));
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        $logDebugSQL .= "$sql;\n";
        
        if ($numrows > 0) {
            $row = $res->fetchRow();
            $batch_id = intval($row[0]);
        }
    }

    if ($batch_id > 0) {

        $_SESSION['reportParams']['batch_id'] = $batch_id;
        
        $sql = "SELECT bh.id, bh.batch_name, bh.batch_description, bh.batch_status, COUNT(DISTINCT(ubi.id)) AS total_users,
                       COUNT(DISTINCT(ra.username)) AS active_users, ubi.planname, bp.plancost, bp.plancurrency,
                       hs.name AS HotspotName, bh.creationdate, bh.creationby, bh.updatedate, bh.updateby
                  FROM %s AS bh LEFT JOIN %s AS ubi ON bh.id = ubi.batch_id
                                LEFT JOIN %s AS bp ON bp.planname = ubi.planname
                                LEFT JOIN %s AS hs ON bh.hotspot_id = hs.id
                                LEFT JOIN %s AS ra ON ra.username = ubi.username
                 WHERE bh.batch_name = '%s'
                 GROUP BY bh.batch_name";
        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                             $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                             $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                             $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                             $configValues['CONFIG_DB_TBL_RADACCT'],
                             $dbSocket->escapeSimple($batch_name));
        
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
?>

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
                <td style="text-align: right" colspan="<?= $colspan1 ?>">
                    <input class="button" type="button" value="Download Invoice"
                        onclick="window.open('include/common/notificationsBatchDetails.php?batch_name=<?= urlencode($batch_name_enc) ?>&destination=download')">
                    <input class="button" type="button" value="Email Invoice to Business/Hotspot"
                        onclick="location.href='include/common/notificationsBatchDetails.php?batch_name=<?= urlencode($batch_name_enc) ?>&destination=email'">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv&reportType=reportsBatchTotalUsers'">
                </td>
            </tr>
            
            <tr>
<?php
            foreach ($cols1 as $caption) {
                printf("<th>%s</th>", $caption);
            }
?>
            </tr>
            
        </thead>
    
        <tbody>

<?php
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
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
            
            $onclick = 'javascript:return false;';
            $tooltipText = sprintf('<div id="divContainerUserInfo"><b>%s</b>:<br><br>%s</div>',
                                   t('all','batchDescription'), $this_batch_desc);
?>

            <tr>
                <td>
                    <a class="tablenovisit" href="#" onclick="<?= $onclick ?>" tooltipText='<?= $tooltipText ?>'>
                        <?= $this_batch_name ?>
                    </a>
                </td>
                
                <td><?= $hotspot_name ?></td>
                <td><?= $batch_status ?></td>
                <td><?= $total_users ?></td>
                <td><?= $active_users ?></td>
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
    </table>
    
<?php
    
        // setup php session variables for exporting
        $_SESSION['reportTable'] = "";
        //reportQuery is assigned below to the SQL statement  in $sql
        $_SESSION['reportQuery'] = "";
        $_SESSION['reportType'] = "reportsBatchActiveUsers";
        
        //orig: used as method to get total rows - this is required for the pages_numbering.php page
        $sql = "SELECT ubi.id, ubi.username, ra.acctstarttime, bh.batch_name
                  FROM %s AS ubi, %s AS ra, %s AS bh
                 WHERE ubi.batch_id=bh.id
                   AND ubi.batch_id=%s
                   AND ubi.username=ra.username
                 GROUP BY ubi.username";

        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                             $configValues['CONFIG_DB_TBL_RADACCT'],
                             $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                             $batch_id);

        // assigning the session reportQuery
        $_SESSION['reportQuery'] = $sql;
        
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        $logDebugSQL .= "$sql;\n";

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
?>

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan2 + ($colspan2 % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>';
        }
?>
                <td style="text-align: right" colspan="<?= ($drawNumberLinks) ? $half_colspan2 : $colspan2 ?>">
                    <input class="button" type="button" value="Active Users CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>
            </tr>
            
<?php
        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType);
        echo "</tr>";
?>
        </thead>
        <tbody>
<?php
            while ($row = $res->fetchRow()) {
                $rowlen = count($row);
            
                // escape row elements
                for ($i = 0; $i < $rowlen; $i++) {
                    $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                }
            
                echo "<tr>";
                // simply print row elements
                for ($i = 1; $i < $rowlen; $i++) {
                    echo "<td>" . $row[$i] . "</td>";
                }
                echo "</tr>";
            }
?>
        </tbody>
<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);

        }

    } else {
        $failureMsg = "Batch name not valid";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
