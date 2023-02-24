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
 * Description:    provides common operations on different management
 *                 pages and other categories
 *
 * Authors:        Liran Tal <liran@enginx.com>
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
    if (!$chars) {
        $chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
    }

    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '';

    while ($i <= ($length - 1)) {
        $num = rand() % (strlen($chars));
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
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

// set of functions to ease the usage of escaping " chars in echo or print functions
// thanks to php.net
function qq($text) {return str_replace('`','"',$text); }
function printq($text) { print qq($text); }
function printqn($text) { print qq($text)."\n"; }

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

// return next billing date (Y-m-d format) based on
// the billing recurring period and billing schedule type
function getNextBillingDate($planRecurringBillingSchedule = "Fixed", $planRecurringPeriod, $billDates = null) {

    // initialize next bill date string (Y-m-d style)

    if ($billDates == null) {
        $nextBillDate = "0000-00-00";
        $prevBillDate = "0000-00-00";
    } else {
        $nextBillDate = $billDates['nextBillDate'];
        $prevBillDate = $billDates['prevBillDate'];
    }

    switch ($planRecurringBillingSchedule) {

        case "Anniversary":
            switch ($planRecurringPeriod) {
                case "Daily":
                    // current day is the start of the period and it's also the end of it
                    // confused? so are we!
                    $nextBillDate = date('Y-m-d', strtotime("+1 day"));
                    break;
                case "Weekly":
                    // add 1 week
                    $nextBillDate = date('Y-m-d', strtotime("+1 week"));
                    break;
                case "Monthly":
                    // add 1 month of time
                    $nextBillDate = date('Y-m-d', strtotime("+1 month"));
                    break;
                case "Quarterly":
                    // add 3 months worth of time
                    $nextBillDate = date('Y-m-d', strtotime("+3 month"));
                    break;
                case "Semi-Yearly":
                    // add 6 months worth of time
                    $nextBillDate = date('Y-m-d', strtotime("+6 month"));
                    break;
                case "Yearly":
                    // add 1 year (same month/day, next year)
                    $nextBillDate = date('Y-m-d', strtotime("+1 year"));
                    break;
            }
            break;

        case "Fixed":
        default:
            switch ($planRecurringPeriod) {
                case "Daily":
                    // current day is the start of the period and it's also the end of it
                    // confused? so are we!
                    $nextBillDate = date("Y-m-d");
                    break;
                case "Weekly":
                    // set to the end of this week
                    // +6 used to get the last day of this week, +7 will be the start of next week (i.e: sunday)
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w') + 6, date('Y')));
                    break;
                case "Monthly":
                    // set to the end of the current month
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('t'), date('Y')));
                    break;
                case "Quarterly":
                    // set to the end of this quarter
                    $currMonth = (int)date('n');
                    $quarterMonth = 1;
                    if ( ($currMonth >= 1) && ($currMonth <= 3) )
                        $quarterMonth = 3;
                    if ( ($currMonth >= 4) && ($currMonth <= 6) )
                        $quarterMonth = 6;
                    if ( ($currMonth >= 7) && ($currMonth <= 9) )
                        $quarterMonth = 9;
                    if ( ($currMonth >= 10) && ($currMonth <= 12) )
                        $quarterMonth = 12;
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, date('t', mktime(0,0,0, $quarterMonth, 1, date('Y'))), date('Y')));
                    break;
                case "Semi-Yearly":
                    $currMonth = (int)date('n');
                    $quarterMonth = 1;
                    if ( ($currMonth >= 1) && ($currMonth <= 6) )
                        $quarterMonth = 6;
                    else if ( ($currMonth >= 7) && ($currMonth <= 12) )
                        $quarterMonth = 12;

                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, (date('t', mktime(0,0,0, $quarterMonth, 1, date('Y')))), date('Y')));
                    break;
                case "Yearly":
                    // set to the end of the year
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, 12, (date('t', mktime(0,0,0, 12, 1, date('Y')))), date('Y')));
                    break;
            }

            break;

    }

    return $nextBillDate;

}

// return prev/start billing date (Y-m-d format) based on
// the billing recurring period and billing schedule type
function getPrevBillingDate($planRecurringBillingSchedule = "Fixed", $planRecurringPeriod, $billDates = null) {

    // initialize next bill date string (Y-m-d style)

    if ($billDates == null) {
        $nextBillDate = "0000-00-00";
        $prevBillDate = "0000-00-00";
    } else {
        $nextBillDate = $billDates['nextBillDate'];
        $prevBillDate = $billDates['prevBillDate'];
    }

    switch ($planRecurringBillingSchedule) {

        case "Anniversary":
            switch ($planRecurringPeriod) {
                case "Daily":
                    // current day is the start of the period and it's also the end of it
                    // confused? so are we!
                    $nextBillDate = date('Y-m-d', strtotime("+1 day"));
                    break;
                case "Weekly":
                    // add 1 week
                    $nextBillDate = date('Y-m-d', strtotime("+1 week"));
                    break;
                case "Monthly":
                    // add 1 month of time
                    $nextBillDate = date('Y-m-d', strtotime("+1 month"));
                    break;
                case "Quarterly":
                    // add 3 months worth of time
                    $nextBillDate = date('Y-m-d', strtotime("+3 month"));
                    break;
                case "Semi-Yearly":
                    // add 6 months worth of time
                    $nextBillDate = date('Y-m-d', strtotime("+6 month"));
                    break;
                case "Yearly":
                    // add 1 year (same month/day, next year)
                    $nextBillDate = date('Y-m-d', strtotime("+1 year"));
                    break;
            }
            break;

        case "Fixed":
        default:
            switch ($planRecurringPeriod) {
                case "Daily":
                    // current day is the start of the period and it's also the end of it
                    // confused? so are we!
                    $nextBillDate = date("Y-m-d");
                    break;
                case "Weekly":
                    // set to the start of this week
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y')));
                    break;
                case "Monthly":
                    // set to the start of the current month
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 01, date('Y')));
                    break;
                case "Quarterly":
                    // set to the start of this quarter
                    $currMonth = (int)date('n');
                    $quarterMonth = 1;
                    if ( ($currMonth >= 1) && ($currMonth <= 3) )
                        $quarterMonth = 1;
                    if ( ($currMonth >= 4) && ($currMonth <= 6) )
                        $quarterMonth = 4;
                    if ( ($currMonth >= 7) && ($currMonth <= 9) )
                        $quarterMonth = 7;
                    if ( ($currMonth >= 10) && ($currMonth <= 12) )
                        $quarterMonth = 10;
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, date(01, mktime(0,0,0, $quarterMonth, 1, date('Y'))), date('Y')));
                    break;
                case "Semi-Yearly":
                    // set to the start of the occurrence of either half of the year (start of june or start of january)
                    $currMonth = (int)date('n');
                    $quarterMonth = 1;
                    if ( ($currMonth >= 1) && ($currMonth <= 6) )
                        $quarterMonth = 1;
                    else if ( ($currMonth >= 7) && ($currMonth <= 12) )
                        $quarterMonth = 6;

                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, 01, date('Y')));
                    break;
                case "Yearly":
                    // set to the start of the year
                    $nextBillDate = date('Y-m-d', mktime(0, 0, 0, 01, 01, date('Y')));
                    break;
            }

            break;

    }

    return $nextBillDate;

}

/*
 * wrapper function to add a tooltip balloon
 *
 * @param        $view            array of view parameters
 * @return        $string            returns string
 */
function addToolTipBalloon($view) {

    if ($view['divId'])
        $viewId = '<div id="'.$view['divId'].'">Loading...</div>';
    else
        $viewId = '';

    $sep = ($view['onClick'] != '' && substr($view['onClick'], -1) != ';' ? ';' : '');

    $str = "<a class='tablenovisit' href='#'
                onClick=\"".$view['onClick'].$sep."return false;\"
                tooltipText='".$view['content']."
                            <br/><br/>
                            $viewId
                            <br/>'
            >".$view['value']."</a>";

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

            $href_format = '?orderBy=%s&orderType=%s' . $partial_query_string;
            $href_asc = sprintf($href_format, $param, 'asc');
            $href_desc = sprintf($href_format, $param, 'desc');

            //~ $img_format = '<img src="%s" alt="%s">';
            //~ $img_asc = sprintf($img_format, 'static/images/icons/arrow_up.png', '^');
            //~ $img_desc = sprintf($img_format, 'static/images/icons/arrow_down.png', 'v');

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

/*
 * function for printing table heading
 *
 * @param    $per_page_numrows    the number of records shown in the current page
 * @param    $numrows             the total number of records
 * @param    $colspan             the colspan
 * @param    $drawNumberLinks     whether to draw links or not
 * @param    $links               the output of the function setupLinks()
 */
function printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links) {
?>
        <tfoot>
            <tr>
                <th scope="col" colspan="<?= $colspan ?>">
<?php
                    echo "displayed <strong>$per_page_numrows</strong> record(s)";
                    if ($drawNumberLinks) {
                        echo " out of <strong>$numrows</strong>";
                    }
?>
                </th>
            </tr>

        </tfoot>
<?php
}

/* prints common controls needed when listing records */
function printTableFormControls($setChecked_param, $removeCheckbox_filename, $form_name='listall') {
?>
    <a title="Select All" class="table" href="javascript:SetChecked(1,'<?= $setChecked_param ?>','<?= $form_name ?>')">Select All</a>
    <a title="Select None" class="table" href="javascript:SetChecked(0,'<?= $setChecked_param ?>','<?= $form_name ?>')">Select None</a>
    <input class="button delete" type="button" value="Delete"
        onclick="javascript:removeCheckbox('<?= $form_name ?>','<?= $removeCheckbox_filename ?>')">
<?php
}
?>
