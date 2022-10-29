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
 * Description:    this graph extension procduces a query of the overall logins 
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Tiago Ratto <tiagoratto@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include('checklogin.php');

$day = (array_key_exists('day', $_GET) && isset($_GET['day']) &&
        intval($_GET['day']) > 0 && intval($_GET['day']) <= 31)
     ? intval($_GET['day']) : "";

$current_month = intval(date('m'));
$month = (array_key_exists('month', $_GET) && isset($_GET['month']) &&
          intval($_GET['month']) > 0 && intval($_GET['month']) <= 12)
       ? intval($_GET['month']) : $current_month;

$current_year = intval(date('Y'));
$year = (array_key_exists('year', $_GET) && isset($_GET['year']) &&
         intval($_GET['year']) > 1970 && intval($_GET['year']) <= $current_year)
      ? intval($_GET['year']) : $current_year;

if (empty($day)) {
    graph_month($month, $year);
} else {
    if (!checkdate($month, $day, $year)) {
        $year = $current_year;
        $month = $current_month;
        $day = intval(date("j"));
    }
    graph_day($day, $month, $year);
}

function graph_day($day,$month,$year) {

    include('opendb.php');
    include('libchart/classes/libchart.php');
    
    header("Content-type: image/png");

    $chart = new VerticalBarChart(800, 600);
	$dataSet = new XYDataSet();

    for ($i=0; $i < 24; $i++) { //24 hours a day
        $date = "$year-$month-$day $i:00:00";
        $sql = "select count(radacctid) from radacct where (acctstarttime <= '$date' and acctstoptime >= '$date') or (acctstarttime <= '$date' and acctsessiontime = 0 and acctinputoctets = 0 and acctoutputoctets = 0);";
        $result = $dbSocket->query($sql);
        $row = $result->fetchRow();
        $dataSet->addPoint(new Point("$i","$row[0]"));
        if (($i > date("G")) and ($day == date("j"))) {
            break;
        }
    }
    $chart->setTitle("Logged users by hour on $day/$month/$year");
    $chart->setDataSet($dataSet);
    $chart->render();

    include('closedb.php');
}

function graph_month($month,$year) {

    include('opendb.php');
    include('libchart/classes/libchart.php');

    header("Content-type: image/png");

    $chart = new VerticalBarChart(800, 600);
	$dataSet = new XYDataSet();

    $lastDay = date("d", mktime(0, 0, 0, $month+1 , 0, date("Y")));

    for ($i=1;$i<=$lastDay;$i++) {
        $measure[$i]['min'] = 100000;
        $measure[$i]['max'] = 0;
        for ($j=0;$j < 24;$j++) { //24 hours a day
            $date = "$year-$month-$i $j:00:00";
            $sql = "select count(radacctid) from radacct where (acctstarttime <= '$date' and acctstoptime >= '$date') or (acctstarttime <= '$date' and acctsessiontime = 0 and acctinputoctets = 0 and acctoutputoctets = 0);";
            $result = $dbSocket->query($sql);
            $row = $result->fetchRow();
            $cnt = $row[0];
            if ($cnt < $measure[$i]['min']) {
                $measure[$i]['min'] = $cnt;
            } else if ($cnt > $measure[$i]['max']) {
                $measure[$i]['max'] = $cnt;
            }
        }
    }
    for ($i=1;$i<=$lastDay;$i++) {
        $dataSet->addPoint(new Point("$i - Min",$measure[$i]['min']));
        $dataSet->addPoint(new Point("$i - Max",$measure[$i]['max']));
    }
    $chart->setTitle("Logged users by month");
    $chart->setDataSet($dataSet);
    $chart->render();

    include('closedb.php');
}

?>
