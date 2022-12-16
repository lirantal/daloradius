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

    // get attribute name passed to us from menu-mng-rad-attributes.php
    $attribute = (array_key_exists('attribute', $_GET) && !empty(str_replace("%", "", trim($_GET['attribute']))))
               ? str_replace("%", "", trim($_GET['attribute'])) : "";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js"
    );
    
    $title = t('Intro','mngradattributessearch.php');
    $help = t('helpPage','mngradattributessearch');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-rad-attributes.php");
    
    $cols = array(
                    "id" => t('all','VendorID'),
                    "vendor" => t('all','VendorName'),
                    "attribute" => t('all','VendorAttribute')
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
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');

    $sql_WHERE = array();
    $sql_WHERE[] = "(type <> '' OR type IS NOT NULL)";
    if (!empty($attribute)) {
        $sql_WHERE[] = sprintf("attribute LIKE '%s%%'", $dbSocket->escapeSimple($attribute));
    }
    
    // we use this simplified query just to initialize $numrows
    $sql = sprintf("SELECT COUNT(id) FROM %s", $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
    $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
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
        $sql = sprintf("SELECT id, vendor, attribute FROM %s", $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
        $sql .= " WHERE " . implode(" AND ", $sql_WHERE);
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($attribute))
                              ? "&attribute=" . urlencode(htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8')) : "";
                              
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-attributes-del.php";
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
        printTableFormControls('vendor__attribute[]', $action);
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
        $counter = 1;
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            list($this_id, $this_vendor, $this_attribute) = $row;
            
            $tooltipText = sprintf('<a class="toolTip" href="mng-rad-attributes-edit.php?vendor=%s&attribute=%s">%s</a>',
                                   urlencode($this_vendor), urlencode($this_attribute), t('Tooltip','AttributeEdit'))
                         . '<div style="margin: 15px auto" id="divContainerAttributeInfo">Loading...</div>';
            
            $onclick = sprintf('javascript:ajaxGeneric("include/management/retVendorAttributeInfo.php","retAttributeInfo",'
                             . '"divContainerAttributeInfo","attribute=%s");return false;', urlencode($this_attribute));
?>
            <tr>
                <td>
                    <input type="checkbox" name="vendor__attribute[]" id="checkbox-<?= $counter ?>"
                        value="<?= urlencode($this_vendor) . "__" . urlencode($this_attribute) ?>">
                    <label for="checkbox-<?= $counter ?>"><?= $this_id ?></label>
                </td>
                <td><?= $this_vendor ?></td>
                <td>
                    <a class="tablenovisit" href="#" onclick='<?= $onclick ?>' tooltipText='<?= $tooltipText ?>'>
                        <?= $this_attribute ?>
                    </a>
                </td>
            </tr>
        
<?php
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
tooltipObj.initFormFieldTooltip();";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
