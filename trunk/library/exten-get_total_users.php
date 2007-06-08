<?php
/*******************************************************************
* Extension name: get_total_users                                  *
*                                                                  *
* Description:    this extension is used to count all the records  *
* (or table entries) in the radcheck table                         *
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

        $sql = "SELECT COUNT(DISTINCT(UserName)) from radcheck;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_users = array();

        while($ent = mysql_fetch_array($res)) {
        	$get_total_users_radcheck = $ent[0];
		array_push($array_users, $get_total_users_radcheck);
        }

        mysql_free_result($res);

  $array = $array_users;

  $graph = new Graph(400,250);
  $graph->SetScale('textint');

  $graph->img->SetMargin(40,30,20,40);

  $graph->title->Set("Total Users in database");
  $graph->subtitle->Set("users from radcheck table");

  $graph->xaxis->SetTitle("Number of Users", "middle");
  $graph->xaxis->SetTickLabels("n");

  $plot = new BarPlot($array);
  $plot->value->Show();
  $plot->SetValuePos('center');

  $graph->Add($plot);
  $graph->Stroke();



  include 'library/closedb.php';

?>


