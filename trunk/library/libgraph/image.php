<?
//============================================================================
// Example of use for PostGraph class
// Version: 1.0
// Copyright (c) Maros Fric, Ladislav Tamas, webradev.com 2004
// All rights reserved
// 
// For support contact info@webradev.com
//============================================================================

// include files
error_reporting(E_ALL ^ E_NOTICE);

include('postgraph.class.php'); 

$data = array(1 => 0, 1.2, 2.5, 4.8, 16, 20, 22, 17, 7, 2, 1, 0);

$graph = new PostGraph(550,330);

$graph->setGraphTitles('My title', 'x axis title', 'y axis title');

$graph->setYNumberFormat('integer');

$graph->setYTicks(10);

$graph->setData($data);

//$graph->setBackgroundColor(array(255,255,0));

//$graph->setTextColor(array(144,144,144));

$graph->setXTextOrientation('horizontal');
    
$graph->drawImage();

$graph->printImage();

?>