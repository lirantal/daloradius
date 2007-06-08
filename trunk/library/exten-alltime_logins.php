<?php
/*******************************************************************
* Extension name: alltime user logins                              *
*                                                                  *
* Description:    this extension is used to detailed information   *
* of all logins on a daily, monthly and yearly basis              *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
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

	include ("checklogin.php");
	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total logins of days in a month
	$sql = "SELECT count(AcctStartTime), DAY(AcctStartTime) AS Day from radacct group by Day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_logins = array();
	$array_days = array();

        while($ent = mysql_fetch_array($res)) {
        	$logins = $ent[0];
		array_push($array_logins, $logins);
		$days = $ent[1];
		array_push($array_days, $days);
        }

        mysql_free_result($res);

  // start plotting the graphs...
  // plot the logins graph!

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Logins/Hits ");
  $graph->subtitle->Set("alltime record of logins based on daily distribution");

  $graph->xaxis->SetTitle("Days in a week", "middle");
  $graph->xaxis->SetTickLabels($array_days);


  // create Y1 axis
  $plot = new BarPlot($array_logins);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();

  include 'library/closedb.php';
}


function monthly() {

	include ("checklogin.php");
	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total logins of days in a month
	$sql = "SELECT count(AcctStartTime), MONTHNAME(AcctStartTime) AS Month from radacct group by Month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_logins = array();
	$array_months = array();

        while($ent = mysql_fetch_array($res)) {
        	$logins = $ent[0];
		array_push($array_logins, $logins);
		$months = $ent[1];
		array_push($array_months, $months);
        }

        mysql_free_result($res);


  // start plotting the graphs...
  // plot the Logins graph!
  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Logins/Hits ");
  $graph->subtitle->Set("alltime record of logins based on monthly distribution");

  $graph->xaxis->SetTitle("Month a year", "middle");
  $graph->xaxis->SetTickLabels($array_months);

  // create Y1 axis
  $plot = new BarPlot($array_logins);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();


  include 'library/closedb.php';
}





function yearly() {

	include ("checklogin.php");
	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total logins of days in a month
	$sql = "SELECT count(AcctStartTime), YEAR(AcctStartTime) AS Year from radacct group by Year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_logins = array();
	$array_years = array();

        while($ent = mysql_fetch_array($res)) {
        	$logins = $ent[0];
		array_push($array_logins, $logins);
		$years = $ent[1];
		array_push($array_years, $years);
        }

        mysql_free_result($res);


  // start plotting the graphs...
  // plot the logins graph!
  $array_ul = $array_logins;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Logins/Hits ");
  $graph->subtitle->Set("alltime record of logins based on yearly distribution");

  $graph->xaxis->SetTitle("Year", "middle");

  // create Y1 axis
  $plot = new BarPlot($array_ul);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();



  include 'library/closedb.php';
}

?>


