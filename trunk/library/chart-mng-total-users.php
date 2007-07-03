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
	include 'config.php';
	include 'opendb.php';

	include "libchart/libchart.php";

	header("Content-type: image/png");
	
	$chart = new VerticalChart(500,250);

        $sql = "SELECT COUNT(DISTINCT(UserName)) from radcheck;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_users = array();

        while($ent = mysql_fetch_array($res)) {
		$chart->addPoint(new Point("Users", "$ent[0]"));
        }

        mysql_free_result($res);

	$chart->setTitle("Total Users");
	$chart->render();

	include 'library/closedb.php';


?>


