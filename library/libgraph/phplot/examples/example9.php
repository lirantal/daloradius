<?php
//Include the code
include("../phplot.php");

//Define the object
$graph = new PHPlot();

//Define some data
include("./data.php");
$graph->SetDataValues($example_data);

//Don't print until we say so
$graph->SetPrintImage(0);

//Draw it
$graph->DrawGraph();

//Define some colors
$ndx_color = $graph->SetIndexColor("blue");
$ndx_color1 = $graph->SetIndexColor("orange");

//The image data colors are now ndx_data_color[]
$graph->DrawDashedLine(250,$graph->plot_area[1],250,250,4,0,$graph->ndx_data_colors[0]);

$graph->DrawDashedLine($graph->xtr(5),$graph->ytr(12),
		$graph->xtr(20),$graph->ytr(42),5,3,$ndx_color);

$graph->DrawDashedLine($graph->plot_area[0],250,$graph->plot_area[2],250,2,0,$ndx_color1);
$graph->DrawDashedLine($graph->plot_area[0],251,$graph->plot_area[2],251,2,0,$ndx_color1);


//Now print the image
$graph->PrintImage();

?>
