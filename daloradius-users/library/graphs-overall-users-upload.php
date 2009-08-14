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
 *		this graph extension procduces a query of the overall uploads 
 *		made by a particular user on a daily, monthly and yearly basis.
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
$type = $_REQUEST['type'];
$username = $_REQUEST['user'];


if ($type == "daily") {
	daily($username);
} elseif ($type == "monthly") {
	monthly($username);
} elseif ($type == "yearly") {
	yearly($username);
}



function daily($username) {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);
	
	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE username='$username' AND acctstoptime>0 AND AcctStartTime>DATE_SUB(curdate(),INTERVAL (DAY(curdate())-1) DAY) AND AcctStartTime< now() GROUP BY day;";
	$res = $dbSocket->query($sql);


	while($row = $res->fetchRow()) {
		$uploads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$uploads"));
	}

	$chart->setTitle("Total Uploads based on Daily distribution");
	$chart->render();

	include 'closedb.php';


}






function monthly($username) {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);

	header("Content-type: image/png");
	
	$chart = new VerticalChart(680,500);

	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, MONTHNAME(AcctStartTime) AS month FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE username='$username' GROUP BY month;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$uploads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$uploads"));
	}

	$chart->setTitle("Total Uploads based on Monthly distribution");
	$chart->render();

	include 'closedb.php';
}








function yearly($username) {


	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);
	
	header("Content-type: image/png");

	$chart = new VerticalChart(680,500);

	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year FROM ".
		$configValues['CONFIG_DB_TBL_RADACCT']." WHERE username='$username' GROUP BY year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$uploads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$uploads"));
	}

	$chart->setTitle("Total Uploads based on Yearly distribution");
	$chart->render();

	include 'closedb.php';

}






?>
