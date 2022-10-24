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

    // print HTML prologue
    $title = t('Intro','mngradnaslist.php');
    
    print_html_prologue($title, $langCode);
    
    include("menu-mng-rad-nas.php");
    
    $cols = array(
                    'id' => t('all','NasID'),
                    'nasname' => t('all','NasIPHost'),
                    'shortname' => t('all','NasShortname'),
                    'type' => t('all','NasType'),
                    'ports' => t('all','NasPorts'),
                    'secret' => t('all','NasSecret'),
                    'server' => t('all','NasVirtualServer'),
                    'community' => t('all','NasCommunity'),
                    'description' => t('all','NasDescription'),
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
               ? strtolower($_GET['orderType']) : "asc";
?>
        
        <div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','mngradnaslist.php') ?><h144>&#x2754;</h144>
                </a>
            </h2>
                
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','mngradnaslist') ?><br></div>
            <br>

<?php

    include('library/opendb.php');
    include('include/management/pages_common.php');

    // we use this simplified query just to initialize $numrows
    $sql = sprintf("SELECT COUNT(id) FROM %s", $configValues['CONFIG_DB_TBL_RADNAS']);
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
                     
        // we execute and log the actual query
        $sql = sprintf("SELECT id, nasname, shortname, type, ports, secret, server, community, description
                          FROM %s ORDER BY %s %s LIMIT %s, %s", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
?>
<form name="listall" method="POST" action="mng-rad-nas-del.php">
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
        printTableFormControls('nashost[]', 'mng-rad-nas-del.php')
?>
                </th>
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
            
            list($id, $nasname, $shortname, $type, $ports, $secret, $server, $community, $description) = $row;
            
            $li_style = 'margin: 7px auto';
            $tooltipText = '<ul style="list-style-type: none">'
                         . sprintf('<li style="%s"><a class="toolTip" href="mng-rad-nas-edit.php?nashost=%s">%s</a></li>',
                                   $li_style, urlencode($nasname), t('Tooltip','EditNAS'))
                         . sprintf('<li style="%s"><a class="toolTip" href="mng-rad-nas-del.php?nashost=%s">%s</a></li>',
                                   $li_style, urlencode($nasname), t('Tooltip','RemoveNAS'))
                         . '</ul>';
            $onclick = sprintf('javascript:return false;');
?>
            <tr>
                <td><input type="checkbox" name="nashost[]" value="<?= $nasname ?>"><?= $id ?></td>
                <td>
                    <a class="tablenovisit" href="#" onclick='<?= $onclick ?>' tooltipText='<?= $tooltipText ?>'>
                        <?= $nasname ?>
                    </a>
                </td>
<?php
            // simply print remaining row elements
            for ($i = 2; $i < $rowlen; $i++) {
                echo "<td>" . $row[$i] . "</td>";
            }
?>
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
</form>

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
?>
                
        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
