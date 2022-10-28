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
 * Description:    this graph extension procduces a query of the overall logins.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('checklogin.php');

    // validate parameters
    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    include('opendb.php');
    include('libchart/libchart.php');

    $chart = new VerticalChart(800, 600);
    $limit = 48;

    switch ($type) {
        case "yearly":
            $sql = "SELECT YEAR(AcctStartTime) AS year, COUNT(username) AS numberoflogins
                      FROM %s GROUP BY year ORDER BY year DESC LIMIT %s";
            break;
            
        case "monthly":
            $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'),
                           COUNT(username) AS numberoflogins,
                           CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month
                      FROM %s GROUP BY month ORDER BY month DESC LIMIT %s";
            break;
            
        default:
        case "daily":
            $sql = "SELECT DATE(AcctStartTime) AS day, COUNT(username) AS numberoflogins
                      FROM %s GROUP BY day ORDER BY day DESC LIMIT %s";
            break;
    }

    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $limit);

    $res = $dbSocket->query($sql);

    $numrows = $res->numRows();

    if ($numrows > 0) {
        while($row = $res->fetchRow()) {
            $value = intval($row[1]);
            $label = strval($row[0]);
            
            $point = new Point($label, $value);
            $chart->addPoint($point);
        }
        $title = ucfirst($type) . " all-time login/hit statistics";
    } else {
        $title = "No login(s) found";
    }

    include('closedb.php');

    header("Content-type: image/png");
    $chart->setTitle($title);
    $chart->render();

?>
