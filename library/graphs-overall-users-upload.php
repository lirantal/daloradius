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
 * Description:    this graph extension produces a query of the overall uploads
 *                 made by a particular user on a daily, monthly and yearly basis.
 *
 * Authors:	       Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include('checklogin.php');

// validate parameters
$type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
         in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
      ? strtolower($_GET['type']) : "daily";

$orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
            in_array(strtolower($_GET['orderBy']), array( "uploads", "day", "month", "year" )))
         ? strtolower($_GET['orderBy']) : "day";

$size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
         in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
      ? strtolower($_GET['size']) : "megabytes";

$username = (array_key_exists('user', $_GET) && isset($_GET['user']))
          ? str_replace('%', '', $_GET['user']) : "";

// used for presentation purpose
$size_division = array("gigabytes" => 1073741824, "megabytes" => 1048576);

include('opendb.php');
include('libchart/libchart.php');

$is_valid = false;

if (!empty($username)) {
    $sql = "SELECT DISTINCT(username) FROM %s WHERE username='%s'";
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);
	$numrows = $res->numRows();
    
    $is_valid = $numrows == 1;
}

$chart = new VerticalChart(800, 600);
$limit = 48;

if ($is_valid) {
    switch ($type) {
        case "yearly":
            $selected_param = "year";
            $sql = "SELECT YEAR(AcctStartTime) AS year, SUM(AcctInputOctets) AS uploads
                      FROM %s
                     WHERE username='%s' AND AcctStopTime>0
                     GROUP BY year ORDER BY year DESC LIMIT %s";
            break;
            
        case "monthly":
            $selected_param = "month";
            $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'),
                           SUM(AcctInputOctets) AS uploads,
                           CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month
                      FROM %s WHERE username='%s'  AND AcctStopTime>0
                     GROUP BY month ORDER BY month DESC LIMIT %s";
            break;
            
        default:
        case "daily":
            $selected_param = "day";
            $sql = "SELECT DATE(AcctStartTime) AS day, SUM(AcctInputOctets) AS uploads
                      FROM %s
                     WHERE username='%s' AND AcctStopTime>0
                     GROUP BY day ORDER BY day DESC LIMIT %s";
            break;
    }
    
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username), $limit);
    
    $res = $dbSocket->query($sql);
    while ($row = $res->fetchRow()) {
        $label = number_format((float)($row[1] / $size_division[$size]), 3, ".", "");
        $chart->addPoint(new Point($row[0], $label));
    }

    $title = sprintf("%s of traffic in upload %s produced by user %s", $size, $type, $username);
    
} else {
    $title = "Please select a valid user";
}

include('closedb.php');

header("Content-type: image/png");
$chart->setTitle($title);
$chart->render();

?>
