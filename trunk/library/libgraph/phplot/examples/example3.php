<?php
//Include the code
include("../phplot.php");

//Include the code for manipulating data (scaling, moving averages, etc.)
include("../phplot_data.php");

//Define the object
$graph = new PHPlot_Data();

//Define some data
include("./data.php");

//Set the data type 
$graph->SetDataType("data-data");

//Load the data into the data array
$graph->SetDataValues($example_data);
$graph->DoMovingAverage(4,2,TRUE);

//Call Scaling Function (in phplot_data.php)
//$graph->DoScaleData(1,1);

//Draw a Legend at pixel location 100,100
$graph->SetLegendPixels(100,100,"");

//have no labels on Y axis
//$graph->SetYGridLabelType("none");

//Print that puppy!
$graph->DrawGraph();
?>
