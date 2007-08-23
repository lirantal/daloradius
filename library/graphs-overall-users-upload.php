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

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by day;";
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

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);
	
        $sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, MONTHNAME(AcctStartTime) AS month from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by month;";
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

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year from ".$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by year;";
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
