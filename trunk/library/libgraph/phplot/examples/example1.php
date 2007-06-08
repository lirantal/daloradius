<?php
//Include the code
include("../phplot.php");

//Define the object
$graph = new PHPlot;

//Set some data
include("./data.php");
$graph->SetDataValues($example_data);


//Draw it
$graph->DrawGraph();

?>
