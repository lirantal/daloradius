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
 * Description:    this extension creates a pie chart of new users
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('checklogin.php');

    $date_regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';

    // we validate starting and ending dates
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match($date_regex, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match($date_regex, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    include('opendb.php');
    include('libchart/classes/libchart.php');

    $chart = new VerticalBarChart(800, 600);
	$dataSet = new XYDataSet();
    $limit = 24;

    $sql_WHERE_pieces = array();
    if (!empty($enddate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate <= '%s'", $dbSocket->escapeSimple($enddate));
    }
    
    if (!empty($startdate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate >= '%s'", $dbSocket->escapeSimple($startdate));
    }

    $sql_WHERE = (count($sql_WHERE_pieces) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE_pieces) : "";

    $sql = sprintf("SELECT COUNT(*), CONCAT(YEAR(CreationDate), ' ', LEFT(MONTHNAME(CreationDate), 3)) AS period,
                           CAST(CONCAT(YEAR(CreationDate), '-', MONTH(CreationDate), '-01') AS DATE) AS month
                      FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO'])
                 . $sql_WHERE . " GROUP BY month ORDER BY month";
    $res = $dbSocket->query($sql);

    while ($row = $res->fetchRow()) {
        $value = intval($row[0]);
        $label = strval($row[1]);

        $point = new Point($label, $value);
        $dataSet->addPoint($point);
    }

    include('closedb.php');

    header("Content-type: image/png");
    $chart->setTitle("monthly number of new users");
    $chart->setDataSet($dataSet);
    $chart->render();

?>
