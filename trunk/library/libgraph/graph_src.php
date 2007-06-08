<?php
/************************************************************\
*
*		easyGraph Copyright 2005 Howard Yeend
*		www.puremango.co.uk
*
*    This file is part of easyGraph.
*
*    easyGraph is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    easyGraph is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with easyGraph; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*
\************************************************************/

/*
Visit my website and leave a comment if you use/like/hate it.
I will try to provide support as well, so if you've got a problem, ask me.

Usage:
---
<?
// start the session
session_start();

// register an array of ("title"=>value) in the session
$_SESSION['amounts'] = Array("title1: "=>100,"title2: "=>200);

// spit out the html for an img, calling this file, graph.php
echo '<img src="graph.php">';
?>
---

Known Bugs:

-If the largest amount is shorter than the graph title length (in pixels), the graph title gets cut off.

-Multiple line title/footer not supported

If you fix the bugs, or add any other features (horizontal/vertical option would be nice) let me know, and I'll post an update with credit to you.

enjoy.
*/

// user defined vars:
// all measurements are in pixels

	// any less than 12 will cut off the text
	$bar_height = 15;

	// how many pixels to leave between bars (vertically)
	$bar_spacing = 5;

	// set grid spacing, any less than 25 will fudge the text. min 50 is preferable.
	$grid_space = 50;

	// amount of space to give for bar titles (horizontally)
	$bar_title_space = 70;

	// (optional) title of graph (could also come from session)
	$graph_title = "Graph of random stuff. Refresh for more.";

	// vertical space to leave for title
	$graph_title_space = 20;

	// (optional) graph footer (could also come from session)
	$graph_footer = "Coded By Howard Yeend. puremango.co.uk";

	// vertical space to leave for footer
	$graph_footer_space = 20;

	// colour of bars
	// 0=red, 1=green, 2=blue, 3=random
	$bar_colour = 0;

// end setup

// do not modify below this line (I know you will anyway :)

session_start();

// tell browser we're an image
header("Content-type: image/png");

// get amounts and titles from session.
$amounts = $_SESSION['amounts'];

// sort amounts (lowest>highest)
asort($amounts);

// calculate required width and height of image
$pic_width = $bar_title_space+max($amounts)+($grid_space*1.5);
$pic_height = ($bar_height+$bar_spacing+2)*sizeof($amounts)+20+$graph_title_space+$graph_footer_space;

// create image
$pic = ImageCreate($pic_width+1,$pic_height+1);

// allocate colours
$white = ImageColorAllocate($pic,255,255,255);
$grey  = ImageColorAllocate($pic,200,200,200);
$lt_grey  = ImageColorAllocate($pic,210,210,210);
$black = ImageColorAllocate($pic,0,0,0);

// fill background of image with white
ImageFilledRectangle($pic,0,0,$pic_width,$pic_height,$white);

// draw graph title
ImageString($pic,5,($pic_width/2)-(strlen($graph_title)*5),0,$graph_title,$black);

// draw graph footer
ImageString($pic, 2,($pic_width/2)-(strlen($graph_footer)*3),$pic_height-$graph_footer_space, $graph_footer, $grey);

// draw grid markers
for($x_axis=$bar_title_space ; ($x_axis-$bar_title_space)<max($amounts)+$grid_space ; $x_axis+=$grid_space)
{
	// draw vertical grid marker
	ImageLine($pic,$x_axis,$graph_title_space,$x_axis,$pic_height-$graph_footer_space,$grey);

	// draw horizontal line above grid numbers
	ImageLine($pic,$x_axis,($pic_height-$graph_footer_space-25),$x_axis-($bar_title_space+$grid_space),($pic_height-$graph_footer_space-25),$grey);

	// draw grid numbers
	ImageString($pic, 3, $x_axis+5, ($pic_height-$graph_footer_space-20), $x_axis-($bar_title_space), $black);
}

// draw bars
$y_axis=$graph_title_space;

if($bar_colour!=3)
{
	// for a nice smooth fade of colour.
	$col = 180;
	$decrement = intval($col/count($amounts));
}

foreach($amounts as $key=>$amount)
{
	// write the bar title
	ImageString($pic, 2, ($bar_title_space-(strlen($key)*6)), $y_axis, $key, $black);

	// allocate a colour for this bar
	if($bar_colour==3)
	{
		// random colour
		$tempCol = ImageColorAllocate($pic,rand(50,200),rand(50,200),rand(50,200));
	} else {

		$col -= $decrement;
		if($bar_colour==0)
		{
			// faded red
			$tempCol = ImageColorAllocate($pic,255,$col,$col);
		} else if($bar_colour==1)
		{
			// faded green
			// 200 because green just looks too bright otherwise
			$tempCol = ImageColorAllocate($pic,$col,200,$col);
		} else if($bar_colour==2)
		{
			// faded blue
			$tempCol = ImageColorAllocate($pic,$col,$col,255);
		}
	}

	// draw the bar
	ImageFilledRectangle($pic,($bar_title_space+1),$y_axis,$amount+$bar_title_space,($y_axis+$bar_height),$tempCol);

	if(($amount)<15)
	{
		// if it's a tiny amount, write the amount outside the bar in black
		ImageString($pic, 2, ($amount+3)+$bar_title_space, $y_axis, $amount, $black);
	} else {
		// or if over 15, write the amount inside the bar in white
		// the strlen stuff is to ensure number is aligned with the end of the bar. works quite well, too.
		ImageString($pic, 2, ($amount-(strlen($amount)*6))+$bar_title_space, $y_axis, $amount, $white);
	}

	// move down
	$y_axis+=($bar_spacing+1)+$bar_height;
}

// output image
ImagePNG($pic);

// remove image from memory
ImageDestroy($pic);
?>