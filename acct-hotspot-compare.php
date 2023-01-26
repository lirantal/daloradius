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

    include_once('library/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("library/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    $cols = array(
                    "hotspot" => t('all','HotSpot'),
                    "uniqueusers" => t('all','UniqueUsers'),
                    "totalhits" => t('all','TotalHits'),
                    "avgsessiontime" => t('all','AverageTime'),
                    "totaltime" => t('all','TotalTime'),
                    "sumInputOctets" => "Total Uploads",
                    "sumOutputOctets" => "Total Downloads"
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
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','accthotspotcompare.php');
    $help = t('helpPage','accthotspotcompare');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-accounting-hotspot.php"); 
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');
    
    
    $sql = sprintf("SELECT hs.name AS hotspot, COUNT(DISTINCT(UserName)) AS uniqueusers, COUNT(radacctid) AS totalhits,
                           AVG(AcctSessionTime) AS avgsessiontime, SUM(AcctSessionTime) AS totaltime, 
                           AVG(AcctInputOctets) AS avgInputOctets, SUM(AcctInputOctets) AS sumInputOctets,
                           AVG(AcctOutputOctets) AS avgOutputOctets, SUM(AcctOutputOctets) AS sumOutputOctets
                      FROM %s AS ra JOIN %s AS hs ON ra.calledstationid=hs.mac
                     GROUP BY hotspot", $configValues['CONFIG_DB_TBL_RADACCT'],
                                        $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
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
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        

        // set navbar stuff
        $navkeys = array(
                                array( 'AccountInfo', "Account Info" ),
                                array( 'UniqueUsers', "Unique Users Chart" ),
                                array( 'LoginHits', "Login Hits Chart" ),
                                array( 'TotalSessionTime', "Total Sess. Time Chart" ),
                                array( 'AvgSessionTime', "Average Sess. Time Chart" ),
                            );

        // print navbar controls
        print_tab_header($navkeys);
        
        // tab 0
        open_tab($navkeys, 0, true);
?>

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

        // second line of table header
        echo '<tr>';
        printTableHead($cols, $orderBy, $orderType);
        echo '</tr>';
?>           
            
        </thead>
     
        <tbody>
<?php
        while ($row = $res->fetchRow()) {
            echo '<tr>';
            
            printf('<td>%s</td>', htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'));
            printf('<td>%s</td>', htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'));
            printf('<td>%s</td>', htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'));
            
            printf('<td>%s</td>', htmlspecialchars(time2str($row[3]), ENT_QUOTES, 'UTF-8'));
            printf('<td>%s</td>', htmlspecialchars(time2str($row[4]), ENT_QUOTES, 'UTF-8'));
            
            printf('<td>%s</td>', htmlspecialchars(toxbyte($row[6]), ENT_QUOTES, 'UTF-8'));
            printf('<td>%s</td>', htmlspecialchars(toxbyte($row[8]), ENT_QUOTES, 'UTF-8'));
            
            echo '</tr>';
            
        }
?>
        </tbody>
        
<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>
    </table>

<?php
        // tab 0
        close_tab($navkeys, 0);
        
        $categories = array( "unique_users", "login_hits", "total_session_time", "avg_session_time", );
        $img_format = '<div style="text-align: center; margin-top: 50px"><img src="%s" alt="%s"></div>';
        
        foreach ($categories as $i => $category) {
            
            // tab $i+1
            open_tab($navkeys, $i+1);
            
            $src = sprintf("library/graphs/hotspot_details.php?category=%s", $category);
            $alt = sprintf("hotspot details (category: %s)", str_replace("_", " ", $category));
            printf($img_format, $src, $alt);
            
            close_tab($navkeys, $i+1);
            
        }
    
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
