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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

    // we validate starting and ending dates
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match(DATE_REGEX, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match(DATE_REGEX, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for new user(s)";
    if (!empty($startdate)) {
         $logQuery .= " from $startdate";
    }
    if (!empty($enddate)) {
         $logQuery .= " to $enddate";
    }
    $logQuery .= "on page: ";


    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','repnewusers.php');
    $help = t('helpPage','repnewusers');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-reports.php");
    
    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    "month" => t('all','Month'),
                    "users" => t('all','Users')
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // validating user passed parameters

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
    
    $sql_WHERE_pieces = array();
    if (!empty($enddate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate <= '%s'", $dbSocket->escapeSimple($enddate));
    }
    
    if (!empty($startdate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate >= '%s'", $dbSocket->escapeSimple($startdate));
    }

    $sql_WHERE = (count($sql_WHERE_pieces) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE_pieces) : "";

    // month is used as a "shadow" parameter for non-lexicographic ordering purpose
    // period and users are used for presentation purpose
    $sql = sprintf("SELECT CONCAT(MONTHNAME(CreationDate), ' ', YEAR(CreationDate)) AS period, COUNT(*) As users,
                           CAST(CONCAT(YEAR(CreationDate), '-', MONTH(CreationDate), '-01') AS DATE) AS month
                      FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO'])
         . $sql_WHERE . " GROUP BY month";                                                
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
        $logDebugSQL = "$sql;\n";
        $res = $dbSocket->query($sql);
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_params = array();
        if (!empty($startdate)) {
            $partial_query_params[] = sprintf("startdate=%s", $startdate);
        }
        if (!empty($enddate)) {
            $partial_query_params[] = sprintf("enddate=%s", $enddate);
        }
        
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");
        
        
        // set navbar stuff
        $navkeys = array( array( 'stats', t('all','Statistics') ), array( 'graphs', t('menu','Graphs') ), );

        // print navbar controls
        print_tab_header($navkeys);
        
        // open first tab (shown)
        open_tab($navkeys, 0, true);
?>
                        <table border="0" class="table1">
                            <thead>
                                <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
                                </tr>

                                <tr>
<?php
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
                                </tr>
                            </thead>
                            
                            <tbody>
<?php
        $count = 0;
        while ($row = $res->fetchRow()) {
            
            // last field is used only for ordering purpose
            $rowlen = count($row) - 1;
            
            // print table row
            printf('<tr id="row-%d">', $count);
            
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
                printf("<td>%s</td>", $row[$i]);
            }
            
            echo '</tr>';
            
            $count++;
        }
?>
                            </tbody>
                            
<?php
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>
                            
                        </table>
<?php
        close_tab($navkeys, 0);
        
        // open second tab
        open_tab($navkeys, 1);
        
        $img_format = '<div style="text-align: center"><img src="%s" alt="%s" style="%s"></div>';
        $src = sprintf('library/graphs-reports-new-users.php?startdate=%s&enddate=%s', $startdate, $enddate);
        $alt = "monthly number of new users";
        printf($img_format, $src, "Online users", "margin: 30px auto");

        close_tab($navkeys, 1);
        
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
