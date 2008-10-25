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
 *
 *********************************************************************************************************
 */

include('checklogin.php');

$type = $_REQUEST['type'];

if ($type == "daily") {
	daily();
} elseif ($type == "monthly") {
	monthly();
} elseif ($type == "yearly") {
	yearly();
}



function daily() {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT count(AcctStartTime), DAY(AcctStartTime) AS Day from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by Day;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[1]", "$row[0]"));
	}

	$chart->setTitle("Alltime Login records based on Daily distribution");
	$chart->render();

	include 'closedb.php';


}






function monthly() {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT count(AcctStartTime), MONTHNAME(AcctStartTime) AS Month from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by Month;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[1]", "$row[0]"));
	}

	$chart->setTitle("Alltime Login records based on Monthly distribution");
	$chart->render();

	include 'closedb.php';
}








function yearly() {

 
	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT count(AcctStartTime), YEAR(AcctStartTime) AS Year from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by Year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[1]", "$row[0]"));
	}

	$chart->setTitle("Alltime Login records based on Yearily distribution");
	$chart->render();

	include 'closedb.php';

}






?>
