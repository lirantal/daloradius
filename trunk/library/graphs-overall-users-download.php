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

	$sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day from ".$configValues['CONFIG_DB_TBL_RADACCT']." where 
	username='$username' group by day;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$downloads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$downloads"));
	}

	$chart->setTitle("Total Downloads based on Daily distribution");
	$chart->render();

	include 'closedb.php';


}






function monthly($username) {

	
	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);
	
	header("Content-type: image/png");

	$chart = new VerticalChart(920,500);

	$sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, MONTHNAME(AcctStartTime) AS month from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by month;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$downloads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$downloads"));
	}

	$chart->setTitle("Total Downloads based on Monthly distribution");
	$chart->render();

	include 'closedb.php';
}








function yearly($username) {


	include 'opendb.php';
	include 'libchart/libchart.php';

	$username = $dbSocket->escapeSimple($username);
	
	header("Content-type: image/png");

	$chart = new VerticalChart(920,500);

	$sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year from ".$configValues['CONFIG_DB_TBL_RADACCT']." 
	where username='$username' group by year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
		$downloads = floor($row[1]/1024/1024);
		$chart->addPoint(new Point("$row[2]", "$downloads"));
	}

	$chart->setTitle("Total Downloads based on Yearly distribution");
	$chart->render();

	include 'closedb.php';

}






?>
