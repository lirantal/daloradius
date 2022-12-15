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
    
    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','rephistory.php');
    $help = t('helpPage','rephistory');
    
    print_html_prologue($title, $langCode);
    
    include ("menu-reports.php");

    // these three variable can be used for validation an presentation purpose
    $cols = array(
                    "section" => t('all','Section'), 
                    "item" => t('all','Item'), 
                    "creationdate" => t('all','CreationDate'), 
                    "creationby" => t('all','CreationBy'), 
                    "updatedate" => t('all','UpdateDate'), 
                    "updateby" => t('all','UpdateBy')
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);

    // validating user passed parameters

    $default_orderBy = array_keys($cols)[2];
    $default_orderType = "desc";

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : $default_orderBy;

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : $default_orderType;

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('include/management/pages_common.php');
    include('library/opendb.php');

    // we use this convenient way to build our SQL query
    $sql_piece_format = "SELECT '%s' AS section, %s AS item, creationdate, creationby, updatedate, updateby FROM %s";

    $sql_pieces = array(
        sprintf($sql_piece_format, 'proxy', 'proxyname', $configValues['CONFIG_DB_TBL_DALOPROXYS']),
        sprintf($sql_piece_format, 'realm', 'realmname', $configValues['CONFIG_DB_TBL_DALOREALMS']),
        sprintf($sql_piece_format, 'userinfo', 'username', $configValues['CONFIG_DB_TBL_DALOUSERINFO']),
        sprintf($sql_piece_format, 'operators', 'username', $configValues['CONFIG_DB_TBL_DALOOPERATORS']),
        sprintf($sql_piece_format, 'invoice', 'id', $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']),
        sprintf($sql_piece_format, 'payment', 'id', $configValues['CONFIG_DB_TBL_DALOPAYMENTS']),
        sprintf($sql_piece_format, 'hotspot', 'name', $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'])
    );
    
    $sql = implode(" UNION ", $sql_pieces);
    
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
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
?>

          <table border="0" class="table1">
            <thead>
                <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
            echo '</td>';
        }
?>
                </tr>
                <tr>
<?php
        printTableHead($cols, $orderBy, $orderType);
?>
                </tr>
            </thead>
            
            <tbody>
<?php
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
            
            // ~ section, item, creationdate, creationby, updatedate, updateby
            
            echo "<tr>";
            
            for ($i = 0; $i < $rowlen; $i++) {
                printf("<td>%s</td>", htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8'));
            }
            
            echo "</tr>";

        }
?>
            </tbody>
            
<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>
            
        </table>
<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('library/closedb.php');
        
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
