<?php
/*******************************************************************
* Extension name: hotspot compare hits                             *
*                                                                  *
* Description:    this extension creates a pie chart of the        *
* comparison of hotspots per unique users                          *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

	include 'opendb.php';
	include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new PieChart(800,450);

	// getting total users
	$sql = "SELECT DISTINCT(UserName) FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'];
	$res = $dbSocket->query($sql);
	$totalUsers = $res->numRows();

        $chart->addPoint(new Point("$totalUsers ($totalUsers users total)", "$totalUsers"));

	// get total users online
	$sql = "SELECT UserName FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL)";
	$res = $dbSocket->query($sql);
	$totalUsersOnline = $res->numRows();


        $chart->addPoint(new Point("$totalUsersOnline ($totalUsersOnline users online)", "$totalUsersOnline"));

        $chart->setTitle("Online users");
        $chart->render();

	include 'closedb.php';




?>


