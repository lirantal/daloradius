<?php
include("./data_date2.php");
include("../phplot.php");
$graph = new PHPlot;
$graph->SetDataType("data-data-error");  //Must be called before SetDataValues

$graph->SetImageArea(600,400);
$graph->SetPrecisionY(0);
$graph->SetXLabel("");
$graph->SetYLabel("Volume");
$graph->SetVertTickIncrement(20);
$graph->SetXAxisPosition(1);
//$graph->SetSkipBottomTick(1);

//Set Unixtime Increment and X Axis Settings
$graph->SetHorizTickIncrement(2679000);
$graph->SetXGridLabelType("time");
$graph->SetXTimeFormat("%b %y");
$graph->SetXDataLabelAngle(90);

$graph->SetDataValues($example_data);
$graph->SetPlotType("lines");
$graph->SetErrorBarShape("line");
$graph->SetPointShape("halfline");
$graph->SetYScaleType("log");
$graph->SetLineWidth(1);
$graph->SetDrawXDataLabels(false);

//Since X axis is in Unixtime format we set the limits accordingly
$graph->SetPlotAreaWorld(883634400,1,915095000,140);

$graph->DrawGraph();
?>
