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
 *		this extension creates a pie chart of online users
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */ 
	include('checklogin.php');

	include 'opendb.php';
	include 'libchart/libchart.php';

	header("Content-type: image/png");

	$chart = new PieChart(620,320);

	// getting total users
	$sql = "SELECT DISTINCT(UserName) FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'];
	$res = $dbSocket->query($sql);
	$totalUsers = $res->numRows();

	$chart->addPoint(new Point("$totalUsers ($totalUsers users total)", "$totalUsers"));

	// get total users online
	$sql = "SELECT DISTINCT(UserName) FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL OR AcctStopTime = '0000-00-00 00:00:00')";
	$res = $dbSocket->query($sql);
	$totalUsersOnline = $res->numRows();


	$chart->addPoint(new Point("$totalUsersOnline ($totalUsersOnline users online)", "$totalUsersOnline"));

	$chart->setTitle("Online users");
	$chart->render();

	include 'closedb.php';




?>


