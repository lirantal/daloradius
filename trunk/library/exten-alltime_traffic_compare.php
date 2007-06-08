<?php
/*******************************************************************
* Extension name: alltime traffic compare                          *
*                                                                  *
* Description:    this extension is used to create a cross-        *
* reference of all the traffic (upload and download) information   *
* on a daily, monthly and yearly basis                             *
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



	// getting total uploads of days in a month
	$sql = "SELECT sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day from radacct group by day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[0]/1024/1024);
		array_push($array_uploads, $uploads);
        }

        mysql_free_result($res);


  // start plotting the graphs....
  $array_dl = $array_downloads;
  $array_ul = $array_uploads;

$ydata = $array_dl;
$y2data = $array_ul;

// Create the graph and specify the scale for both Y-axis
$graph = new Graph(850,540,"auto");
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Adjust the margin
$graph->img->SetMargin(40,40,20,70);

// Create the two linear plot
$lineplot=new LinePlot($ydata);
$lineplot2=new LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Adjust the axis color
$graph->y2axis->SetColor("orange");
$graph->yaxis->SetColor("blue");

$graph->title->Set("Alltime Traffic Distribution in MBs (downloads vs uploads)");
$graph->xaxis->title->Set("Day of the week");
$graph->yaxis->title->Set("MBs");

//$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetTickLabels($array_days);

// Set the colors for the plots
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Set the legends for the plots
$lineplot->SetLegend("Downloads");
$lineplot2->SetLegend("Uploads");

// Adjust the legend position
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.4,0.95,"center","bottom");

// Display the graph
$graph->Stroke();




  // start plotting the graphs...
  // plot the UPLOADS graph!
  $array_ul = $array_uploads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,20,40);

  $graph->title->Set("Total Uploads ");
  $graph->subtitle->Set("alltime record of uploads based on daily distribution");

  $graph->xaxis->SetTitle("Days in a week", "middle");

  // create Y1 axis
  $plot = new BarPlot($array_ul);
  $plot->value->Show();

  $graph->Add($plot);
  $graph->Stroke();



  // start plotting the graphs...
  // plot the DOWNLOADS graph!
  $array_dl = $array_downloads;

  $graph = new Graph(900,550);
  $graph->SetScale('textint');

  $graph->img->SetMargin(80,30,20,40);

  $graph->title->Set("Total Downloads ");
  $graph->subtitle->Set("alltime record of downloads based on daily distribution");

  $graph->xaxis->SetTitle("Days in a week", "middle");

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



	// getting total uploads of days in a month
	$sql = "SELECT sum(AcctInputOctets) as Uploads, MONTHNAME(AcctStartTime) AS month from radacct group by month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();
	$array_months = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[0]/1024/1024);
		array_push($array_uploads, $uploads);
		$months = $ent[1];
		array_push($array_months, $months);
        }

        mysql_free_result($res);


  // start plotting the graphs....
  $array_dl = $array_downloads;
  $array_ul = $array_uploads;

$ydata = $array_dl;
$y2data = $array_ul;

// Create the graph and specify the scale for both Y-axis
$graph = new Graph(850,540,"auto");
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Adjust the margin
$graph->img->SetMargin(40,40,20,70);

// Create the two linear plot
$lineplot=new LinePlot($ydata);
$lineplot2=new LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Adjust the axis color
$graph->y2axis->SetColor("orange");
$graph->yaxis->SetColor("blue");

$graph->title->Set("Alltime Traffic Distribution in MBs (downloads vs uploads)");
$graph->xaxis->title->Set("Month of the Year");
$graph->yaxis->title->Set("MBs");

//$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetTickLabels($array_months);

// Set the colors for the plots
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Set the legends for the plots
$lineplot->SetLegend("Downloads");
$lineplot2->SetLegend("Uploads");

// Adjust the legend position
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.4,0.95,"center","bottom");

// Display the graph
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



	// getting total uploads of days in a month
	$sql = "SELECT sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year from radacct group by year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$array_uploads = array();
	$array_years = array();

        while($ent = mysql_fetch_array($res)) {
        	$uploads = floor($ent[0]/1024/1024);
		array_push($array_uploads, $uploads);
		$years = $ent[1];
		array_push($array_years, $years);
        }

        mysql_free_result($res);


  // start plotting the graphs....
  $array_dl = $array_downloads;
  $array_ul = $array_uploads;

$ydata = $array_dl;
$y2data = $array_ul;

// Create the graph and specify the scale for both Y-axis
$graph = new Graph(850,540,"auto");
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Adjust the margin
$graph->img->SetMargin(40,40,20,70);

// Create the two linear plot
$lineplot=new LinePlot($ydata);
$lineplot2=new LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Adjust the axis color
$graph->y2axis->SetColor("orange");
$graph->yaxis->SetColor("blue");

$graph->title->Set("Alltime Traffic Distribution in MBs (downloads vs uploads)");
$graph->xaxis->title->Set("Year");
$graph->yaxis->title->Set("MBs");

//$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetTickLabels($array_months);

// Set the colors for the plots
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Set the legends for the plots
$lineplot->SetLegend("Downloads");
$lineplot2->SetLegend("Uploads");

// Adjust the legend position
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.4,0.95,"center","bottom");

// Display the graph
$graph->Stroke();





  include 'library/closedb.php';
}

?>


