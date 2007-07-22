<?php
/*******************************************************************
* Extension name: hotspot compare unique users                     *
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
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($ent = mysql_fetch_array($res)) {
                $chart->addPoint(new Point("$ent[0] ($ent[1] users)", "$ent[1]"));
        }

        mysql_free_result($res);

        $chart->setTitle("Distribution of Unique users per Hotspot");
        $chart->render();

	include 'closedb.php';




?>


