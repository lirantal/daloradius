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
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradprofiles.php');
    $help = t('helpPage','mngradprofileslist');
    
    print_html_prologue($title, $langCode);
    
    include ("menu-mng-rad-profiles.php");
    
    $cols = array(
                    "selected",
                    "groupname" => t('all','Groupname'),
                    "users" => t('all','TotalUsers')
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

    //orig: used as method to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT DISTINCT(rgr.groupname) AS groupname, COUNT(DISTINCT(rug.username)) AS users
              FROM %s AS rgr LEFT JOIN %s AS rug ON rgr.groupname=rug.groupname
             GROUP BY rgr.groupname
            UNION 
            SELECT DISTINCT(rgc.groupname) AS groupname, COUNT(DISTINCT(rug.username)) AS users
              FROM %s AS rgc LEFT JOIN %s AS rug ON rgc.groupname=rug.groupname
             GROUP BY rgc.groupname";
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADGROUPREPLY'], $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                         $configValues['CONFIG_DB_TBL_RADGROUPCHECK'], $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
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
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-profiles-del.php";
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
        printTableFormControls('profile_names[]', $action);
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
        $count = 0;
        $li_style = 'margin: 7px auto';
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
        
            list($groupname, $usercount) = $row;
        
            // tooltip stuff
            $onclick = 'javascript:return false;';
        
            // tooltip stuff
            $tooltipText = '<ul style="list-style-type: none">'
                     . sprintf('<li style="%s">', $li_style)
                     . sprintf('<a class="toolTip" href="mng-rad-profiles-edit.php?profile_name=%s">%s</a></li>',
                               urlencode($groupname), t('button','EditProfile'))
                     . sprintf('<li style="%s">', $li_style)
                     . sprintf('<a class="toolTip" href="mng-rad-profiles-del.php?profile_name=%s">%s</a></li>',
                               urlencode($groupname), t('button','RemoveProfile'))
                     . '</ul>';
        
            echo "<tr>";
            
            echo '<td>';
            printf('<label for="checkbox-%s">', $count);
            printf('<input type="checkbox" name="profile_names[]" id="checkbox-%s" value="%s">',
                   $count, urlencode($groupname));
            echo '</label></td>';
            
            printf('<td><a class="tablenovisit" href="#" onclick="%s" ' . "tooltipText='%s'>%s</a></td>",
                   $onclick, $tooltipText, $groupname);
            
            
            
            printf("<td>%s</td>", $usercount);
            
            echo "</tr>";
            
            $count++;
            
        }
?>
        </tbody>
        
<?php
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
tooltipObj.initFormFieldTooltip();";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
