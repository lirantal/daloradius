<?php
include("../phplot.php");
$graph = new PHPlot(600,200);
include("./data_date.php");
$graph->SetDataType("data-data");  //Must be called before SetDataValues

$graph->SetXGridLabelType("time");
$graph->SetXDataLabelAngle(90);
$graph->SetXLabel("");
$graph->SetYLabel("Volume");
$graph->SetVertTickIncrement(30);
$graph->SetXTimeFormat("%b %y");
$graph->SetDataValues($example_data);
$graph->SetHorizTickIncrement(2679000);
$graph->SetPlotType("thinbarline");
$graph->SetDrawXDataLabels(false);
$graph->SetPlotAreaWorld(883634400,0,915095000,90);
$graph->DrawGraph();
?>
