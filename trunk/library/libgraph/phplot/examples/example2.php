<?php
//Include the code
include("../phplot.php");

//Define the Object
$graph = new PHPlot;

//Define some data
include("./data.php");

//Set the data type
$graph->SetDataType("linear-linear");

//Remove the X data labels
//$graph->SetXGridLabelType("none");

//Load the data into data array
$graph->SetDataValues($example_data);

//Draw the graph
$graph->DrawGraph();
?>
