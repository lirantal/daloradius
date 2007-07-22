<?php
/*******************************************************************
* Extension name: hotspot compare time                             *
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
	$sql = "select hotspots.name, count(distinct(UserName)), count(radacctid), avg(AcctSessionTime), sum(AcctSessionTime) from ".$configValues['CONFIG_DB_TBL_RADACCT']." join hotspots on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid like hotspots.mac) group by hotspots.name;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[0] ($ent[3] seconds)", "$ent[3]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Distribution of Time usage per Hotspot");
        $chart->render();

	include 'closedb.php';




?>


