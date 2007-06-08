<?php

// PieChart usage example
require_once('class_PieChart.php');
$pie = &new PieChart();

	$pie_unser = $_GET['data'];
	$piedata = urldecode($pie_unser);


/*
// setup our data; value corresponds to the percentage
$piedata = array(
array('value'=>10,'title'=>'Fruit'),
array('value'=>25,'title'=>'Vegetables'),
array('value'=>40,'title'=>'Meat'),
array('value'=>10,'title'=>'Dairy'),
array('value'=>15,'title'=>'Pepsi'),
);

*/


// pass the data to the pie chart class
$pie->data($piedata);

// create a 170x110px image with a 150x150px pie chart, with a
// drop shadow under the pie chart, antialiasing enabled, and legend disabled
$pie->create_image(170,110,150,150,true,true,false);

// display the image (outputs appropriate headers to the browser for a PNG
// image, then displays the PNG)
$pie->display();

// clean up
$pie->destroy_image();



?>
