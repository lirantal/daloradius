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
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        while($ent = mysql_fetch_array($res)) {
                $downloads = floor($ent[0]/1024/1024);
                $chart->addPoint(new Point("$ent[1]", "$downloads"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Login records based on Daily distribution");
        $chart->render();

        include 'closedb.php';


}






function monthly() {

	
	include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);
	
        $sql = "SELECT count(AcctStartTime), MONTHNAME(AcctStartTime) AS Month from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by Month;";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[1]", "$ent[0]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Login records based on Monthly distribution");
        $chart->render();

        include 'closedb.php';
}








function yearly() {


        
        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(920,500);

        $sql = "SELECT count(AcctStartTime), YEAR(AcctStartTime) AS Year from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by Year;";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[1]", "$ent[0]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Alltime Login records based on Yearily distribution");
        $chart->render();

        include 'closedb.php';

}






?>
