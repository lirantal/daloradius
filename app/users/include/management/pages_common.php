<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Description:    provides common operations on different management
 *                 pages and other categories
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Evgeniy Kozhuhovskiy <ugenk@xdsl.by>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/pages_common.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

/* returns a random alpha-numeric string of length $length */
function createPassword($length, $chars) {
    if ($length <= 0) {
        return '';
    }

    if (!$chars) {
        $chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
    }

    $charsLength = strlen($chars);
    $pass = '';

    for ($i = 0; $i < $length; $i++) {
        $pass .= substr($chars, random_int(0, $charsLength - 1), 1);
    }

    return $pass;
}

/* convert byte to to size */
function toxbyte($size) {
    $magnitudes = array(
                         "GB" => 1073741824, // Gigabytes
                         "MB" => 1048576,    // Megabytes
                         "KB" => 1024        // Kilobytes
                       );

    foreach ($magnitudes as $label => $magnitude) {
        if ($size > $magnitude) {
            $ret = round($size / $magnitude, 2);
            return "$ret $label";
        }
    }

    // Bytes
    if (!empty($size) && $size <= 1024) {
        return "$size B";
    }
}

// function taken from dialup_admin
function time2str($time) {

    $str = "";                // initialize variable
    $time = floor($time);
    if (!$time)
        return "0 seconds";
    $d = $time/86400;
    $d = floor($d);
    if ($d){
        $str .= "$d days, ";
        $time = $time % 86400;
    }
    $h = $time/3600;
    $h = floor($h);
    if ($h){
        $str .= "$h hours, ";
        $time = $time % 3600;
    }
    $m = $time/60;
    $m = floor($m);
    if ($m){
        $str .= "$m minutes, ";
        $time = $time % 60;
    }
    if ($time)
        $str .= "$time seconds, ";
    $str = preg_replace("/, $/",'',$str);
    return $str;
}

/*
 * function for printing table heading
 *
 * @param    $cols                    an associative array in the form of "orderingType" => "caption"
 *                                    whenever the "orderingType" key is missing, the heading is simply
 *                                    printed with no ordering options
 *
 * @param    $partial_query_string    contains the remaining part of the query string
 */
function printTableHead($cols, $orderBy="", $orderType="asc", $partial_query_string="") {
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    if (empty($orderBy) || !in_array($orderBy, array_keys($param_cols))) {
        $orderBy = array_keys($param_cols)[0];
    }

    if (empty($orderType) || !in_array($orderType, array( "desc", "asc" ))) {
        $orderType = "asc";
    }

    // a standard way of creating table heading
    foreach ($cols as $param => $caption) {

        if (is_int($param)) {
            $ordering_controls = "";

            if ($caption === 'selected') {
                $caption = '<abbr title="selected">sel.</abbr>';
            }

        } else {
            $title_format = 'order by %s, sort %s';
            $title_asc = sprintf($title_format, strip_tags($caption), 'ascending');
            $title_desc = sprintf($title_format, strip_tags($caption), 'descending');

            $partial_query_string_safe = str_replace('%', '%%', $partial_query_string);
            $href_format = '?orderBy=%s&orderType=%s' . $partial_query_string_safe; 
            $href_asc = sprintf($href_format, $param, 'asc');
            $href_desc = sprintf($href_format, $param, 'desc');

            $img_format = '<i class="bi bi-%s ms-1 text-dark"></i>';
            $img_asc = sprintf($img_format, 'sort-alpha-up');
            $img_desc = sprintf($img_format, 'sort-alpha-down');

            $enabled_a_format = '<a title="%s" class="novisit" href="%s">%s</a>';
            $disabled_a_format = '<a title="%s" role="link" aria-disabled="true" style="opacity:0.25">%s</a>';

            if ($orderBy == $param) {
                if ($orderType == "asc") {
                    $link_asc = sprintf($disabled_a_format, $title_asc, $img_asc);
                    $link_desc = sprintf($enabled_a_format, $title_asc, $href_desc, $img_desc);
                } else {
                    $link_asc = sprintf($enabled_a_format, $title_asc, $href_asc, $img_asc);
                    $link_desc = sprintf($disabled_a_format, $title_desc, $img_desc);
                }
            } else {
                $link_asc = sprintf($enabled_a_format, $title_asc, $href_asc, $img_asc);
                $link_desc = sprintf($enabled_a_format, $title_asc, $href_desc, $img_desc);
            }

            $ordering_controls = $link_asc . $link_desc;
        }

        echo '<th>' . $caption . $ordering_controls . '</th>';
    }
}

?>
