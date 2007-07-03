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

	include 'config.php';
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day from radacct where username='$username' group by day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $downloads = floor($ent[1]/1024/1024);
                $chart->addPoint(new Point("$ent[2]", "$downloads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Downloads based on Daily distribution");
        $chart->render();

        include 'closedb.php';


}






function monthly($username) {

	include 'config.php';
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);
	
        $sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, MONTHNAME(AcctStartTime) AS month from radacct where username='$username' group by month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $downloads = floor($ent[1]/1024/1024);
                $chart->addPoint(new Point("$ent[2]", "$downloads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Downloads based on Monthly distribution");
        $chart->render();

        include 'closedb.php';
}








function yearly($username) {


        include 'config.php';
        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT UserName, sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year from radacct where username='$username' group by year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $downloads = floor($ent[1]/1024/1024);
                $chart->addPoint(new Point("$ent[2]", "$downloads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Downloads based on Yearly distribution");
        $chart->render();

        include 'closedb.php';

}






?>
