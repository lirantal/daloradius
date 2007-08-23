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

if ($type == "daily") {
	daily();
} elseif ($type == "monthly") {
	monthly();
} elseif ($type == "yearly") {
	yearly();
}



function daily() {

	
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by day;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
                $downloads = floor($row[0]/1024/1024);
                $chart->addPoint(new Point("$row[1]", "$downloads"));
        }

        $chart->setTitle("Alltime Downloads based on Daily distribution");
        $chart->render();

        include 'closedb.php';


}






function monthly() {

	
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT sum(AcctOutputOctets) as Downloads, MONTHNAME(AcctStartTime) AS month from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by month;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
                $downloads = floor($row[0]/1024/1024);
                $chart->addPoint(new Point("$row[1]", "$downloads"));
        }

        $chart->setTitle("Alltime Downloads based on Monthly distribution");
        $chart->render();

        include 'closedb.php';
}








function yearly() {


        
        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by year;";
	$res = $dbSocket->query($sql);

	while($row = $res->fetchRow()) {
                $downloads = floor($row[0]/1024/1024);
                $chart->addPoint(new Point("$row[1]", "$downloads"));
        }

        $chart->setTitle("Alltime Downloads based on Yearily distribution");
        $chart->render();

        include 'closedb.php';

}






?>
