<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$xdata = array(128,87,2,79,65,154);
$ydata = array(12,17,22,19,5,15);

$graph = new Graph(450,400);
$graph->SetScale("textlin",3,25);
$graph->SetTickDensity(TICKD_DENSE);
$graph->yscale->SetAutoTicks();

$graph->title->Set('Manual scale, auto ticks');
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$line = new LinePlot($ydata);
$line2 = new LinePlot($xdata);
$graph->Add($line2);
$graph->Add($line);


// Output graph
$graph->Stroke();

?>


