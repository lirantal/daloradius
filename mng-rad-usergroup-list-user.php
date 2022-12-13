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

    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // feed the sidebar
    $usernameList = $username_enc;
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradusergrouplistuser');
    $help = t('helpPage','mngradusergrouplistuser');
    
    print_html_prologue($title, $langCode);

    if (!empty($username_enc)) {
        $title .= " :: $username_enc";
    }

    include("menu-mng-rad-usergroup.php");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    "groupname" => t('all','Groupname'),
                    "priority" => t('all','Priority')
                 );

    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[1];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);


    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    $sql = sprintf("SELECT COUNT(DISTINCT(groupname)) FROM %s WHERE username='%s'",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    $numrows = $res->fetchrow()[0];
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
    
        $sql = sprintf("SELECT groupname, priority FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
         $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($username_enc)) ? "&username=" . urlencode($username_enc) : "";
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-usergroup-del.php";
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
        printTableFormControls('usergroup[]', $action);
?>
                </th>
            </tr>
<?php
        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        echo "</tr>";
?>
        </thead>
        
        <tbody>
<?php
        $li_style = 'margin: 7px auto';
        $counter = 1;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
            
            echo '<tr>';
            
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                
                if ($i == 0) {
                    $tooltipText = '<ul style="list-style-type: none">'
                                 . sprintf('<li style="%s"><a class="toolTip" href="mng-rad-usergroup-edit.php?username=%s&group=%s">%s</a></li>',
                                           $li_style, urlencode($username_enc), urlencode($row[$i]), t('Tooltip','EditUserGroup'))
                                 . sprintf('<li style="%s"><a class="toolTip" href="mng-rad-usergroup-del.php?username=%s&group=%s">%s</a></li>',
                                           $li_style, urlencode($username_enc), urlencode($row[$i]), t('Tooltip','DeleteUserGroup'))
                                 . '</ul>';
                    
                    echo '<td>';
                    printf('<input type="checkbox" name="usergroup[]" id="checkbox-%s" value="%s||%s">',
                           $counter, $username_enc, $row[$i]);
                    printf('<label for="checkbox-%s">', $counter);
                    echo '<a class="tablenovisit" href="#" ' . sprintf("tooltipText='%s'>", $tooltipText);
                    printf('%s</a>', $row[$i]);
                    echo '</label>'
                       . '</td>';
                    
                } else {
                    printf('<td>%s</td>', $row[$i]);
                }
                
            }
            
            echo '</tr>';
            
            $counter++;
        }
?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links, $partial_query_string);
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
