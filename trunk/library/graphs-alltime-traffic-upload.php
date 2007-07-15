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

        $sql = "SELECT sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day from radacct group by day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $uploads = floor($ent[0]/1024/1024);
                $chart->addPoint(new Point("$ent[1]", "$uploads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Uploads based on Daily distribution");
        $chart->render();

        include 'closedb.php';


}






function monthly() {

	
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT sum(AcctInputOctets) as Uploads, MONTHNAME(AcctStartTime) AS month from radacct group by month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $uploads = floor($ent[0]/1024/1024);
                $chart->addPoint(new Point("$ent[1]", "$uploads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Uploads based on Monthly distribution");
        $chart->render();

        include 'closedb.php';
}








function yearly() {


        
        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year from radacct group by year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $uploads = floor($ent[0]/1024/1024);
                $chart->addPoint(new Point("$ent[1]", "$uploads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Uploads based on Yearily distribution");
        $chart->render();

        include 'closedb.php';

}






?>
