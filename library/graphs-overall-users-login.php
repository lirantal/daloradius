<?php
/*******************************************************************
* Extension name: graphs-overall-users-login.php
*                                                                  
* Description:    
* this graph extension procduces a query of the overall logins 
* made by a particular user on a daily, monthly and yearly basis.                                                
*                                                                  
* Author: Liran Tal <liran@enginx.com>                             
*                                                                  
*******************************************************************/


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

	$chart = new VerticalChart(920,500);

	$sql = "SELECT UserName, count(AcctStartTime), DAY(AcctStartTime) AS Day from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by Day;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[2]", "$row[1]"));
	}

	$chart->setTitle("Total Users");
	$chart->render();

	include 'closedb.php';


}






function monthly($username) {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);
	
	header("Content-type: image/png");

	$chart = new VerticalChart(920,500);

	$sql = "SELECT UserName, count(AcctStartTime), MONTHNAME(AcctStartTime) AS Month from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by Month;";
	$res = $dbSocket->query($sql);


	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[2]", "$row[1]"));
	}

	$chart->setTitle("Total Users");
	$chart->render();

	include 'closedb.php';
}








function yearly($username) {


	include 'opendb.php';
	include 'libchart/libchart.php';
	
	$username = $dbSocket->escapeSimple($username);

	header("Content-type: image/png");

	$chart = new VerticalChart(920,500);

	$sql = "SELECT UserName, count(AcctStartTime), YEAR(AcctStartTime) AS Year from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by Year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$chart->addPoint(new Point("$row[2]", "$row[1]"));
	}

	$chart->setTitle("Total Users");
	$chart->render();

	include 'closedb.php';

}






?>
