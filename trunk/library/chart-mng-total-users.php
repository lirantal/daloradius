<?php
/*******************************************************************
* Extension name: chart-mng-total-users.php
*                                                                  
* Description:    
* this extension is used to count all the records 
* (or table entries) in the radcheck table                         
*                                                                  
* Author: Liran Tal <liran@enginx.com>                             
*                                                                  
*******************************************************************/

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


