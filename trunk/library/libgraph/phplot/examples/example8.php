<?php
include("./data_date2.php");
include("../phplot.php");
$graph = new PHPlot(600,400);
$graph->SetPrintImage(0); //Don't draw the image yet

$graph->SetDataType("data-data-error");  //Must be called before SetDataValues

$graph->SetNewPlotAreaPixels(90,40,540,190);
$graph->SetDataValues($example_data);

$graph->SetXLabelType("time");
$graph->SetXLabelAngle(90);
$graph->SetXTitle("");
$graph->SetYTitle("Price");
$graph->SetYTickIncrement(20);
$graph->SetXTickIncrement(2679000);
$graph->SetXTimeFormat("%b %y");
$graph->SetPlotType("lines");
$graph->SetErrorBarShape("line");
$graph->SetPointShape("halfline");
$graph->SetYScaleType("log");
$graph->SetLineWidths(array(1));
$graph->SetPlotAreaWorld(883634400,1,915095000,140);
$graph->SetXDataLabelPos('none');
$graph->DrawGraph();

//Now do the second chart on the image

unset($example_data);

$graph->SetYScaleType("linear");
include("./data_date.php");

$graph->SetDataType("data-data");  //Must be called before SetDataValues

$graph->SetDataValues($example_data);
$graph->SetNewPlotAreaPixels(90,260,540,350);
$graph->SetDataValues($example_data);

$graph->SetXLabelType("time");
$graph->SetXLabelAngle(90);
$graph->SetXTitle("");
$graph->SetYTitle("Volume");
$graph->SetYTickIncrement(30);
$graph->SetPlotType("thinbarline");

//Set how to display the x-axis ticks
$graph->SetXTimeFormat("%b %y");
$graph->SetXTickIncrement(2679000);
$graph->SetXAxisPosition(0);  //Have to reset it after log plots

//Set Plot to go from x = Jan 1 1998, to x = Dec 31 1998
//	and from y = 0 to y = 90
$graph->SetPlotAreaWorld(883634400,0,915095000,90);

$graph->DrawGraph();

//Print the image
$graph->PrintImage();
?>
