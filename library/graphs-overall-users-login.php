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
 * Description:    this graph extension produces a query of the overall logins 
 *                 made by a particular user on a daily, monthly and yearly basis.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include('checklogin.php');

    include('opendb.php');
    include('libchart/classes/libchart.php');

    $username = (array_key_exists('user', $_GET) && isset($_GET['user']))
              ? str_replace('%', '', $_GET['user']) : "";

    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "montly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    $is_valid = false;

    if (!empty($username)) {
        $sql = sprintf("SELECT DISTINCT(username) FROM %s WHERE username='%s'",
                       $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username));
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        
        $is_valid = $numrows == 1;
    }

    $chart = new VerticalBarChart(800, 600);
    $dataSet = new XYDataSet();
    $limit = 48;

    if ($is_valid) {

        switch ($type) {
            case "yearly":
                $sql = "SELECT YEAR(AcctStartTime) AS year, COUNT(AcctStartTime) AS logins
                          FROM %s
                         WHERE username='%s'
                         GROUP BY year ORDER BY year DESC LIMIT %s";
                break;
            
            case "montly":
                $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'),
                               COUNT(username) AS logins,
                               CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month
                          FROM %s WHERE username='%s' GROUP BY month ORDER BY month DESC LIMIT %s";
                break;
                
            default:
            case "daily":
                $sql = "SELECT DATE(AcctStartTime) AS day, COUNT(username) AS logins
                          FROM %s
                         WHERE username='%s' AND acctstoptime>0
                         GROUP BY day ORDER BY day DESC LIMIT %s";
                break;
        }

        $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username), $limit);
        $res = $dbSocket->query($sql);

        while ($row = $res->fetchRow()) {
            $value = intval($row[1]);
            $label = strval($row[0]);

            $point = new Point($label, $value);
            $dataSet->addPoint($point);
        }

        $title = ucfirst($type) . " login/hit statistics for user $username";
    } else {
        $title = "Please select a valid user";
    }

    include('closedb.php');

    header("Content-type: image/png");
    $chart->setTitle($title);
    $chart->setDataSet($dataSet);
    $chart->render();

?>
