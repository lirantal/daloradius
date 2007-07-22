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
	$sql = "select ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name, count(distinct(UserName)), count(radacctid), avg(AcctSessionTime), sum(AcctSessionTime) from ".$configValues['CONFIG_DB_TBL_RADACCT']." join ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." on (".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid like ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac) group by ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name;";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[0] ($ent[2] users)", "$ent[2]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Distribution of Hits (Logins) per Hotspot");
        $chart->render();

	include 'closedb.php';




?>


