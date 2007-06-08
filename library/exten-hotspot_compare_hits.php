<?php
/*******************************************************************
* Extension name: hotspot compare hit                              *
*                                                                  *
* Description:    this extension creates a pie chart of the        *
* comparison of hotspots per hits                                  *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

	include ("checklogin.php");
	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	


	// getting total downloads of days in a month
	$sql = "select hotspots.name, count(distinct(UserName)), count(radacctid), avg(AcctSessionTime), sum(AcctSessionTime) from radacct join hotspots on (radacct.calledstationid like hotspots.mac) group by hotspots.name;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_name = array();
	$array_hits = array();

        while($ent = mysql_fetch_array($res)) {
        	$name = $ent[0];
		array_push($array_name, $name);
        	$hits = $ent[2];
		array_push($array_hits, $hits);
        }

        mysql_free_result($res);

$data = $array_hits;

// Create the Pie Graph.
$graph = new PieGraph(500,300,'auto');
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Hits (Logins) per Hotspot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create
$p1 = new PiePlot3D($data);
$p1->SetLegends($array_name);
$p1->SetCSIMTargets($array_name,$array_name);

// Use absolute labels
$p1->SetLabelType(1);
$p1->value->SetFormat("%d hits");

// Move the pie slightly to the left
$p1->SetCenter(0.4,0.5);

$graph->Add($p1);

// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->Stroke();



   include 'library/closedb.php';




?>


