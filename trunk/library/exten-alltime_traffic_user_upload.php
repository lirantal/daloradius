<?php
/*******************************************************************
* Extension name: alltime traffic upload stat                      *
*                                                                  *
* Description:    this extension is used to detailed information   *
* of all uploads on a daily, monthly and yearly basis              *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

$type = $_REQUEST['type'];
$user = $_REQUEST['user'];

if ($type == "daily") {
        daily($user);
} elseif ($type == "monthly") {
        monthly($user);
} elseif ($type == "yearly") {
        yearly($user);
}

function daily($user) {

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total uploads of days in a month
	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day from radacct where username='$user' group by day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();
        $array_days = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[1]/1024/1024);
		array_push($array_uploads, $uploads);
                $days = $ent[2];
                array_push($array_days, $days);
        }

        mysql_free_result($res);

  // start plotting the graphs...
  // plot the UPLOADS graph!
  $array_ul = $array_uploads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Uploads ");
  $graph->subtitle->Set("alltime record of uploads based on daily distribution");

  $graph->xaxis->SetTitle("Days in a week", "middle");
  $graph->xaxis->SetTickLabels($array_days);

  // create Y1 axis
  $plot = new BarPlot($array_ul);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();

  include 'library/closedb.php';
}





function monthly($user) {

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total uploads of days in a month
	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, MONTHNAME(AcctStartTime) AS month from radacct where username='$user' group by month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();
	$array_months = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[1]/1024/1024);
		array_push($array_uploads, $uploads);
		$months = $ent[2];
		array_push($array_months, $months);
        }

        mysql_free_result($res);


  // start plotting the graphs...
  // plot the UPLOADS graph!
  $array_ul = $array_uploads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Uploads ");
  $graph->subtitle->Set("alltime record of uploads based on monthly distribution");

  $graph->xaxis->SetTitle("Month a year", "middle");
  $graph->xaxis->SetTickLabels($array_months);

  // create Y1 axis
  $plot = new BarPlot($array_ul);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();


  include 'library/closedb.php';
}





function yearly($user) {

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	

	// getting total uploads of days in a month
	$sql = "SELECT UserName, sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year from radacct where username='$user' group by year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();
	$array_years = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[1]/1024/1024);
		array_push($array_uploads, $uploads);
		$years = $ent[2];
		array_push($array_years, $years);
        }

        mysql_free_result($res);


  // start plotting the graphs...
  // plot the UPLOADS graph!
  $array_ul = $array_uploads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,60,40);

  $graph->title->Set("Total Uploads ");
  $graph->subtitle->Set("alltime record of uploads based on yearly distribution");

  $graph->xaxis->SetTitle("Year", "middle");

  // create Y1 axis
  $plot = new BarPlot($array_ul);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();



  include 'library/closedb.php';
}

?>


