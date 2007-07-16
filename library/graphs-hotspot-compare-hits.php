<?php
/*******************************************************************
* Extension name: hotspot compare hits                             *
*                                                                  *
* Description:    this extension creates a pie chart of the        *
* comparison of hotspots per unique users                          *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

	include 'opendb.php';
	include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new PieChart(800,450);

	// getting total downloads of days in a month
	$sql = "select hotspots.name, count(distinct(UserName)), count(radacctid), avg(AcctSessionTime), sum(AcctSessionTime) from radacct join hotspots on (radacct.calledstationid like hotspots.mac) group by hotspots.name;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[0] ($ent[2] users)", "$ent[2]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Distribution of Hits (Logins) per Hotspot");
        $chart->render();

	include 'closedb.php';




?>


