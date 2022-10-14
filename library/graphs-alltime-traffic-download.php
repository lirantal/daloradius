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
 * Description:    this graph extension produces a query of the alltime downloads.
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

$size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
         in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
      ? strtolower($_GET['size']) : "megabytes";

// used for presentation purpose
$size_division = array("gigabytes" => 1073741824, "megabytes" => 1048576);

include('opendb.php');
include('libchart/libchart.php');

$chart = new VerticalChart(800, 600);
$limit = 24;

switch ($type) {
    case "yearly":
        $sql = "SELECT YEAR(AcctStartTime) AS year, SUM(AcctOutputOctets) AS downloads
                  FROM %s GROUP BY year ORDER BY year DESC LIMIT %s";
        break;
    
    case "monthly":
        $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'),
                       SUM(AcctOutputOctets) AS downloads,
                       CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month
                  FROM %s GROUP BY month ORDER BY month DESC LIMIT %s";
        break;
        
    default:
    case "daily":
        $sql = "SELECT DATE(AcctStartTime) AS day, SUM(AcctOutputOctets) AS downloads
                  FROM %s GROUP BY day ORDER BY day DESC LIMIT %s";
        break;
}

$sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $limit);
$res = $dbSocket->query($sql);
while ($row = $res->fetchRow()) {
    $label = number_format((float)($row[1] / $size_division[$size]), 3, ".", "");
    $chart->addPoint(new Point($row[0], $label));
}

include('closedb.php');

$title = ucfirst($type) . " all-time download traffic (in " . $size . ") statistics";

$chart->setTitle($title);
$chart->render();

?>
