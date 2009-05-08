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
 * Description:
 *		this graph extension procduces a query of the overall logins 
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *		Tiago Ratto <tiagoratto@gmail.com>
 *
 *********************************************************************************************************
 */

include('checklogin.php');

$day = $_REQUEST['day'];
$month = $_REQUEST['month'];
$year = $_REQUEST['year'];

if ($day == "all") {
	graph_month($month,$year);
} else {
	graph_day($day,$month,$year);
}

function month_alpha_to_number($month) {
	switch ($month) {
		case 'jan':
			return '01';
		break;
		case 'feb':
			return '02';
		break;
		case 'mar':
			return '03';
		break;
		case 'apr':
			return '04';
		break;
		case 'may':
			return '05';
		break;
		case 'jun':
			return '06';
		break;
		case 'jul':
			return '07';
		break;
		case 'aug':
			return '08';
		break;
		case 'sep':
			return '09';
		break;
		case 'oct':
			return '10';
		break;
		case 'nov':
			return '11';
		break;
		case 'dec':
			return '12';
		break;
	}
}

function graph_day($day,$month,$year) {

	include 'opendb.php';
	include 'libchart/libchart.php';
	
	header("Content-type: image/png");

	$chart = new VerticalChart(780,500);

	$month = month_alpha_to_number($month);

	for ($i=0;$i < 24;$i++) { //24 hours a day
		$date = "$year-$month-$day $i:00:00";
		$sql = "select count(radacctid) from radacct where (acctstarttime <= '$date' and acctstoptime >= '$date') or (acctstarttime <= '$date' and acctsessiontime = 0 and acctinputoctets = 0 and acctoutputoctets = 0);";
		$result = $dbSocket->query($sql);
		$row = $result->fetchRow();
		$chart->addPoint(new Point("$i","$row[0]"));
		if (($i > date("G")) and ($day == date("j"))) {
			$i = 24;
		}
	}
	$chart->setTitle("Logged users by hour on $day/$month/$year");
	$chart->render();

	include 'closedb.php';
}

function graph_month($month,$year) {
	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(780,500);

	$month = month_alpha_to_number($month);

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
		$chart->addPoint(new Point("$i - Min",$measure[$i]['min']));
		$chart->addPoint(new Point("$i - Max",$measure[$i]['max']));
	}
	$chart->setTitle("Logged users by month");
	$chart->render();

	include 'closedb.php';
}

?>
