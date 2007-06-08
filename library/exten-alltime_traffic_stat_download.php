<?php
/*******************************************************************
* Extension name: alltime traffic stat download                    *
*                                                                  *
* Description:    this extension is used to create detailed graphs *
* of download information on a daily, monthly and yearly basis     *
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

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	


	// getting total downloads of days in a month
	$sql = "SELECT sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day from radacct group by day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_downloads = array();
        $array_days = array();
	
        while($ent = mysql_fetch_array($res)) {
        	$downloads = floor($ent[0]/1024/1024);
		array_push($array_downloads, $downloads);
                $days = $ent[1];
                array_push($array_days, $days);
        }

        mysql_free_result($res);

  // start plotting the graphs...
  // plot the DOWNLOADS graph!
  $array_dl = $array_downloads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,20,40);

  $graph->title->Set("Total Downloads ");
  $graph->subtitle->Set("alltime record of downloads based on daily distribution");

  $graph->xaxis->SetTitle("Days in a week", "middle");
  $graph->xaxis->SetTickLabels($array_days);

  // create Y1 axis
  $plot = new BarPlot($array_dl);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();

  include 'library/closedb.php';
}





function monthly() {

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	


	// getting total downloads of days in a month
	$sql = "SELECT sum(AcctOutputOctets) as Downloads, MONTHNAME(AcctStartTime) AS month from radacct group by month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_downloads = array();
	$array_months = array();

        while($ent = mysql_fetch_array($res)) {
        	$downloads = floor($ent[0]/1024/1024);
		$months = $ent[1];
		array_push($array_downloads, $downloads);
		array_push($array_months, $months);
        }

        mysql_free_result($res);



  // start plotting the graphs...
  // plot the DOWNLOADS graph!
  $array_dl = $array_downloads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,20,40);

  $graph->title->Set("Total Downloads ");
  $graph->subtitle->Set("alltime record of downloads based on daily distribution");

  $graph->xaxis->SetTitle("Months in a Yaer", "middle");
  $graph->xaxis->SetTickLabels($array_months);

  // create Y1 axis
  $plot = new BarPlot($array_dl);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();

  include 'library/closedb.php';
}





function yearly() {

	include 'config.php';
	include 'opendb.php';
	include 'jpgraph-1.21a/src/jpgraph.php';
	include 'jpgraph-1.21a/src/jpgraph_pie.php';
	include 'jpgraph-1.21a/src/jpgraph_pie3d.php';	
	include 'jpgraph-1.21a/src/jpgraph_bar.php';	
	include 'jpgraph-1.21a/src/jpgraph_line.php';	


	// getting total downloads of days in a month
	$sql = "SELECT sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year from radacct group by year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_downloads = array();
	$array_years = array();

        while($ent = mysql_fetch_array($res)) {
        	$downloads = floor($ent[0]/1024/1024);
		$years = $ent[1];
		array_push($array_downloads, $downloads);
		array_push($array_years, $years);
        }

        mysql_free_result($res);

  // start plotting the graphs...
  // plot the DOWNLOADS graph!
  $array_dl = $array_downloads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,20,40);

  $graph->title->Set("Total Downloads ");
  $graph->subtitle->Set("alltime record of downloads based on daily distribution");

  $graph->xaxis->SetTitle("Years", "middle");
  $graph->xaxis->SetTickLabels($array_years);

  // create Y1 axis
  $plot = new BarPlot($array_dl);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();


  include 'library/closedb.php';
}

?>


