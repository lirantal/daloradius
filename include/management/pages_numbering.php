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
 * Description:    This script provides support for spanning a lot of table results
 *                 across several pages with full numbering support, first and
 *                 last links, etc.
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/pages_numbering.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

/* Should be called after the include for opendb and before the $sql declaration and execution. */

$rowsPerPage = $configValues['CONFIG_IFACE_TABLES_LISTING'];

if (isset($numrows)) {
    $maxPage = ceil($numrows / $rowsPerPage);
}

$pageNum = (array_key_exists('page', $_REQUEST) && isset($_REQUEST['page']) &&
            intval($_REQUEST['page']) > 0 && isset($maxPage) && intval($_REQUEST['page']) <= $maxPage)
         ? intval($_REQUEST['page']) : 1;

$offset = ($pageNum - 1) * $rowsPerPage;

function printLinks($links, $drawNumberLinks) {
    // page navigation controls are shown only if there is more than one page
    if ($drawNumberLinks) {
        echo '<div class="d-flex flex-row justify-content-center">';
        echo $links;
        echo '</div>';
    }
}

// this function returns a string containing paged-navigation controls
function setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    $href_format = '?page=%s&orderBy=%s&orderType=%s%s%s%s';
    $link_format = '<a title="%s" href="%s"><img alt="%s" src="%s" style="border: 0"></a>';
    $link_disabled_format = '<img role="link" aria-disabled="true" alt="%s" src="%s" style="border: 0">';
    
    $link_disabled_format = '<li class="page-item disabled"><span class="page-link">%s</span></li>';
    $link_current_format = '<li class="page-item active" aria-current="page"><span class="page-link">%s</span></li>';
    $link_format = '<li class="page-item"><a class="page-link" href="%s">%s</a></li>';
    
    $labels = array(
                     'prev'  => '<i class="bi bi-caret-left-fill"></i>',
                     'first' => '<i class="bi bi-rewind-fill"></i>',
                     'curr' => '[Current]',
                     'next'  => '<i class="bi bi-caret-right-fill"></i>',
                     'last'  => '<i class="bi bi-skip-forward-fill"></i>'
                   );
    
    
    
    // print 'previous' link only if we're not on page one
    if ($pageNum > 1) {
        $page = $pageNum - 1;

        $href_prev   = sprintf($href_format, $page, $orderBy, $orderType, $request1, $request2, $request3);
        $href_first  = sprintf($href_format, 1, $orderBy, $orderType, $request1, $request2, $request3);
        
        $prev = sprintf($link_format, $href_prev, $labels['prev']);
        $first = sprintf($link_format, $href_first, $labels['first']);
    } else {
        $prev  = sprintf($link_disabled_format, $labels['prev']);
        $first = sprintf($link_disabled_format, $labels['first']);

    }

    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        
        $href_next = sprintf($href_format, $page, $orderBy, $orderType, $request1, $request2, $request3);
        $href_last = sprintf($href_format, $maxPage, $orderBy, $orderType, $request1, $request2, $request3);
        
        $next = sprintf($link_format, $href_next, $labels['next']);
        $last = sprintf($link_format, $href_last, $labels['last']);

    } else {
        // we're on the last page, don't enable 'next' link
        $next  = sprintf($link_disabled_format, $labels['next']);
        
        // nor 'last page' link 
        $last = sprintf($link_disabled_format, $labels['last']);
    }

    $curr = sprintf($link_current_format, $pageNum);

    $result = '<nav><ul class="pagination pagination-sm">';
    $result .= $first . $prev . $curr . $next . $last;
    $result .= '</ul></nav>';

    return $result;
}

// this function echoes the paged-navigation controls produced by the function setupLinks_str()
function setupLinks($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {    
    echo setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $request1, $request2, $request3);
}

// this is an utility function used for printing a "go to <page num.>" link in the function setupNumbering_str()
function print_link($aPageNum, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {     
    $link_format = '<li class="page-item"><a class="page-link" href="?page=%s&orderBy=%s&orderType=%s%s%s%s">%s</a></li>';
    $selected_link_format = '<li class="page-item active" aria-current="page"><span class="page-link">%s</span></li>';
    
    if ($aPageNum != $pageNum) {
        return sprintf($link_format, $aPageNum, $orderBy, $orderType, $request1, $request2, $request3, $aPageNum);
    }
    
    return sprintf($selected_link_format, $aPageNum);
}

//~ $params = array(
                    //~ 'num_rows' => $numrows,
                    //~ 'rows_per_page' => $rowsPerPage,
                    //~ 'page_num' => $pageNum,
                    //~ 'order_by' => $orderBy,
                    //~ 'order_type' => $orderType,
                    //~ 'partial_query_string' => $partial_query_string
                //~ );
function print_page_numbering($params) {
    
    $partial_query_string = (isset($params['partial_query_string'])) ? $params['partial_query_string'] : "";
    
    return setupNumbering($params['num_rows'],
                          $params['rows_per_page'],
                          $params['page_num'],
                          $params['order_by'],
                          $params['order_type'],
                          $partial_query_string);
}

// this function returns a string containing go to page controls
function setupNumbering_str($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    $hellip = '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
    
    $numofpages = ceil($numrows / $rowsPerPage);
    
    if (empty($pageNum)) {
        $pageNum = 1;
    }
    
    $result = '<nav><ul class="pagination pagination-sm">';
    
    if ($numofpages <= 20) {
        for ($i = 1; $i <= $numofpages; $i++) {
            $result .= print_link($i, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }
        
        return $result;
    }
    
    // 1st page
    $result .= print_link(1, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
    
    if ($pageNum >= 1 && $pageNum <= 3) {
        
        $i = 1;
        for ($j = 1; $j <= 3; $j++) {
            $result .= print_link($i + $j, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }
    
        $result .= $hellip;
    } else if ($pageNum <= $numofpages && $pageNum >= ($numofpages-2) ) {
        $result .= $hellip;
        
        $i = $numofpages;
        for ($j = -3; $j <= -1; $j++) {
            $result .= print_link($i + $j, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }

    }  else {
        $result .= $hellip;
        $i = $pageNum;
        
        for ($j = -2; $j <= 2; $j++) {
            $result .= print_link($i + $j, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }
        
        $result .= $hellip;
    }
    
    // last page
    $result .= print_link($numofpages, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
    
    $result .= "</ul></nav>";
    
    return $result;
}

// this function echoes go to page controls produced by the function setupNumbering_str()
function setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    echo setupNumbering_str($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
}

?>
