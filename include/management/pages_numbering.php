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


// this function returns a string containing paged-navigation controls
function setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    $href_format = '?page=%s&orderBy=%s&orderType=%s%s%s%s';
    $link_format = '<a title="%s" href="%s"><img alt="%s" src="%s" style="border: 0"></a>';
    $link_disabled_format = '<img role="link" aria-disabled="true" alt="%s" src="%s" style="border: 0">';
    
    $labels = array(
                     'prev'  => '[Prev]',
                     'first' => '[First]',
                     'next'  => '[Next]',
                     'last'  => '[Last]'
                   );
    
    // print 'previous' link only if we're not on page one
    if ($pageNum > 1) {
        $page = $pageNum - 1;

        $href_prev   = sprintf($href_format, $page, $orderBy, $orderType, $request1, $request2, $request3);
        $href_first  = sprintf($href_format, 1, $orderBy, $orderType, $request1, $request2, $request3);
        
        $prev  = sprintf($link_format, $labels['prev'], $href_prev, $labels['prev'], "images/icons/r.gif");
        $first = sprintf($link_format, $labels['first'], $href_first, $labels['first'], "images/icons/rw.gif");
        
    } else {
        $prev  = sprintf($link_disabled_format, $labels['prev'], "images/icons/r_non.gif");
        $first = sprintf($link_disabled_format, $labels['first'], "images/icons/rw_non.gif");

    }

    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        
        $href_next = sprintf($href_format, $page, $orderBy, $orderType, $request1, $request2, $request3);
        $href_last = sprintf($href_format, $maxPage, $orderBy, $orderType, $request1, $request2, $request3);
        
        $next = sprintf($link_format, $labels['next'], $href_next, $labels['next'], "images/icons/f.gif");
        $last = sprintf($link_format, $labels['last'], $href_last, $labels['last'], "images/icons/ff.gif");

    } else {
        $next  = sprintf($link_disabled_format, $labels['next'], "images/icons/f_non.gif");    // we're on the last page, don't enable 'next' link
        $last = sprintf($link_disabled_format, $labels['last'], "images/icons/ff_non.gif");    // nor 'last page' link    
    }

    $greyColorBeg = '<span style="color: #5F5A59">';
    $greyColorEnd = '</span>';

    $result = sprintf("%sPage %s%s of %s%s%s<br>", $greyColorBeg, $pageNum, $greyColorEnd, $greyColorBeg, $maxPage, $greyColorEnd)
            . sprintf("%s %s %s %s<br>", $first, $prev, $next, $last);
    
    return $result;
}

// this function echoes the paged-navigation controls produced by the function setupLinks_str()
function setupLinks($pageNum, $maxPage, $orderBy, $orderType, $request1="", $request2="", $request3="") {    
    echo setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $request1, $request2, $request3);
}

// this is an utility function used for printing a "go to <page num.>" link in the function setupNumbering_str()
function print_link($aPageNum, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") { 
    $link_format = '<a class="table" href="?page=%s&orderBy=%s&orderType=%s%s%s%s" style="margin: auto 3px">%s</a>';
    $selected_link_format = '<strong style="color: #5F5A59">%s</strong>';
    
    $tmp = sprintf($link_format, $aPageNum, $orderBy, $orderType, $request1, $request2, $request3, $aPageNum);
    
    return ($aPageNum != $pageNum) ? $tmp : sprintf($selected_link_format, $tmp);
    
}

// this function returns a string containing go to page controls
function setupNumbering_str($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    $numofpages = ceil($numrows / $rowsPerPage);
    
    if (empty($pageNum)) {
        $pageNum = 1;
    }
    
    $result = "";
    
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
    
        $result .= '&hellip;';
    } else if ($pageNum <= $numofpages && $pageNum >= ($numofpages-2) ) {
        $result .= '&hellip;';
        
        $i = $numofpages;
        for ($j = -3; $j <= -1; $j++) {
            $result .= print_link($i + $j, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }

    }  else {
        $result .= '&hellip;';
        $i = $pageNum;
        
        for ($j = -2; $j <= 2; $j++) {
            $result .= print_link($i + $j, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
        }
        
        $result .= '&hellip;';
    }
    
    // last page
    $result .= print_link($numofpages, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
    
    return $result;
}

// this function echoes go to page controls produced by the function setupNumbering_str()
function setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1="", $request2="", $request3="") {
    echo setupNumbering_str($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $request1, $request2, $request3);
}

?>
