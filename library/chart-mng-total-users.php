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
 *		this extension is used to count all the records (or table entries) in the radcheck table
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
include ("checklogin.php");
include 'opendb.php';

include "libchart/libchart.php";

header("Content-type: image/png");

$chart = new VerticalChart(500,250);

$sql = "SELECT COUNT(DISTINCT(UserName)) from ".$configValues['CONFIG_DB_TBL_RADCHECK'].";";
$res = $dbSocket->query($sql);

$array_users = array();

while($row = $res->fetchRow()) {
	$chart->addPoint(new Point("Users", "$row[0]"));
}

$chart->setTitle("Total Users");
$chart->render();

include 'closedb.php';


?>


