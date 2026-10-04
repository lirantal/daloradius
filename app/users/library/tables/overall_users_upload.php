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
 * Description:    this graph extension produces a query of the alltime uploads
 *                 made by all users on a daily, monthly and yearly basis.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$extension_file = '/library/tables/overall_users_upload.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../portal_widgets_pdo.php';
$username = $_SESSION['login_user'];
$username_enc = htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8');
$type = dalo_portal_widget_choice($_GET, 'type', array('daily', 'monthly', 'yearly'), 'daily');
$size = dalo_portal_widget_choice($_GET, 'size', array('gigabytes', 'megabytes'), 'megabytes');
$orderType = dalo_portal_widget_choice($_GET, 'orderType', array('desc', 'asc'), 'asc', false);
$selected_param = array('daily' => 'day', 'monthly' => 'month', 'yearly' => 'year')[$type];
$orderBy = dalo_portal_widget_choice($_GET, 'orderBy', array('uploads', $selected_param), 'uploads');
$label_param = array('day' => 'Day of month', 'month' => 'Month of year', 'year' => 'Year');
$size_division = array('gigabytes' => 1073741824, 'megabytes' => 1048576);
$short_size = array('gigabytes' => 'GBs', 'megabytes' => 'MBs');
include_once('include/management/pages_common.php');
$widgetPdo = null;
$numrows = 0;
try {
    $widgetPdo = dalo_portal_handle($configValues);
    $allRows = dalo_portal_widget_statistics($widgetPdo, $configValues, $username, 'upload', $type, $orderBy, $orderType);
    $numrows = count($allRows);
    include_once('include/management/pages_numbering.php');
    $total_data = 0;
    foreach ($allRows as $row) { $total_data += intval($row[1]); }
    $pageRows = $numrows ? dalo_portal_widget_statistics($widgetPdo, $configValues, $username, 'upload', $type, $orderBy, $orderType, (int) $offset, (int) $rowsPerPage) : array();
} catch (Throwable $exception) {
    $numrows = 0;
    $failureMsg = 'Portal statistics unavailable';
} finally { $widgetPdo = null; }

if ($numrows > 0) {
    // $cols is needed only if $numwrows > 0
    $cols = array(
                   $selected_param => $label_param[$selected_param],
                   "uploads" => "Uploads count in " . $size
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == 'yes' && $maxPage > 1;
    $per_page_numrows = count($pageRows);
    $total_data = number_format(floatval($total_data / $size_division[$size]), 1, ".", "");

    // the partial query is built starting from user input
    // and for being passed to setupNumbering and setupLinks functions
    $partial_query_string = sprintf("&type=%s&size=%s&goto_stats=true", $type, $size);

    echo '<div class="my-3 text-center">';
    printf("<h4>%s of traffic in upload %s produced by user %s</h4>", $size, $type, $username_enc);

    $descriptors = array();

    $params = array(
                        'num_rows' => $numrows,
                        'rows_per_page' => $rowsPerPage,
                        'page_num' => $pageNum,
                        'order_by' => $orderBy,
                        'order_type' => $orderType,
                        'partial_query_string' => $partial_query_string,
                    );
    $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

    print_table_prologue($descriptors);

    // print table top
    print_table_top();

    // second line of table header
    printTableHead($cols, $orderBy, $orderType, $partial_query_string);

    // closes table header, opens table body
    print_table_middle();

    $per_page_data = 0;
    foreach ($pageRows as $row) {
        $data = intval($row[1]);
        $per_page_data += $data;

        echo "<tr>"
           . "<td>" . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . "</td>"
           . "<td>" . number_format(floatval($data / $size_division[$size]), 1, ".", "") . " " . $short_size[$size] . "</td>"
           . "</tr>";

    }
    $per_page_data = number_format(floatval($per_page_data / $size_division[$size]), 1, ".", "");

    // close tbody,
    // print tfoot
    // and close table + form (if any)
    $table_foot = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $per_page_numrows,
                            'colspan' => $colspan,
                            'multiple_pages' => $drawNumberLinks,
                       );
    $descriptor = array( 'table_foot' => $table_foot );

    print_table_bottom($descriptor);

    // get and print "links"
    $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
    printLinks($links, $drawNumberLinks);

    echo '</div>';

} else {
    // $numrows <= 0
    $failureMsg = $failureMsg ?? "No upload(s) found";
}


if (!empty($failureMsg)) {
    include_once("include/management/actionMessages.php");
}



?>
