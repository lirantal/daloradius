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

        $sql = "SELECT UserName, count(AcctStartTime), DAY(AcctStartTime) AS Day from radacct where username='$username' group by Day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[2]", "$ent[1]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Users");
        $chart->render();

        include 'closedb.php';


}






function monthly($username) {

	
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);
	
        $sql = "SELECT UserName, count(AcctStartTime), MONTHNAME(AcctStartTime) AS Month from radacct where username='$username' group by Month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());


        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[2]", "$ent[1]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Users");
        $chart->render();

        include 'closedb.php';
}








function yearly($username) {


        
        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT UserName, count(AcctStartTime), YEAR(AcctStartTime) AS Year from radacct where username='$username' group by Year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[2]", "$ent[1]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Total Users");
        $chart->render();

        include 'closedb.php';

}






?>
