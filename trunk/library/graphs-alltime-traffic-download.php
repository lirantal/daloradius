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
 *		this graph extension procduces a query of the alltime downloads
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

include('checklogin.php');

$type = $_REQUEST['type'];
$size = $_REQUEST['size'];
switch ($size) {
	case "gigabytes":
		$sizeDivision = "1073741824"; 
		break;
	case "megabytes":
		$sizeDivision = "1048576";
		break;
	default:
		$sizeDivision = "1048576";
		break;
}

if ($type == "daily") {
	daily();
} elseif ($type == "monthly") {
	monthly();
} elseif ($type == "yearly") {
	yearly();
}


function daily() {

	global $sizeDivision;
	global $size;
	
	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by day;";
	$res = $dbSocket->query($sql);
	
	while($row = $res->fetchRow()) {
		$downloads = floor($row[0]/$sizeDivision);
		$chart->addPoint(new Point("$row[1]", "$downloads"));
	}
	

	$chart->setTitle("Alltime Downloads based on Daily distribution ($size)");
	$chart->render();

	include 'closedb.php';


}





function monthly() {

	global $sizeDivision;
	global $size;
	

	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT sum(AcctOutputOctets) as Downloads, MONTHNAME(AcctStartTime) AS month from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by month;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$downloads = floor($row[0]/$sizeDivision);
		$chart->addPoint(new Point("$row[1]", "$downloads"));
	}

	$chart->setTitle("Alltime Downloads based on Monthly distribution ($size)");
	$chart->render();

	include 'closedb.php';
}








function yearly() {

	global $sizeDivision;
	global $size;

	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." group by year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$downloads = floor($row[0]/$sizeDivision);
		$chart->addPoint(new Point("$row[1]", "$downloads"));
	}

	$chart->setTitle("Alltime Downloads based on Yearily distribution ($size)");
	$chart->render();

	include 'closedb.php';

}






?>
