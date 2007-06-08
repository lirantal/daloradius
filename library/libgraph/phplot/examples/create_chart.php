<?php
/* $Id: create_chart.php,v 1.17 2004/10/24 15:44:13 migueldb Exp $ */

error_reporting(E_ALL);

/*
// Check if we are processing the form
if (! array_key_exists('submit', $_POST)) {
    echo "<p>This file is intended to be called from format_chart.php, the example-o-matic.".
         "Please <a href=\"format_chart.php\">click here</a> to try it.</p>";
    exit;
}    
*/

extract ($_GET, EXTR_OVERWRITE);
extract ($_POST, EXTR_OVERWRITE);


//Sample functions

// data-data as a function
if ($which_data_type == 'function') { 
	//Put function here
	$dx = ".3";
	$max = 6.4;
	$maxi = $max/$dx;
	for ($i=0; $i<$maxi; $i++) {
		$a = 4;
		$x = $dx*$i;
		$data[$i] = array("", $x, $a*sin($x),$a*cos($x),$a*cos($x+1)); 	
	}
	$which_data_type = "data-data";
}
// data-data-error as a random function
else if ($which_data_type == 'randfunction') {
    srand ((double) microtime() * 1000000);
    $a = 9.62;
    $label[0] = "October"; $label[5] = "Day 5"; $label[10] = "Day 10";
    $label[15] = "Day 15"; $label[20] = "Day 20"; $label[25] = "Day 25";

    for ($i = 0; $i <= 30; $i++) {
        $a += rand(-1, 2);
        $b = rand(0,1);
        $c = rand(0,1);
        $data[] = @ array($label[$i],$i+1,$a,$b,$c);
    }
    $which_data_type = 'data-data-error';
}
// MBD, this is for data_sample3.php, $num_data_rows is set there
else if ($which_data_type == 'data-data-error') {
    for ($i = 0; $i < $num_data_rows; $i++) {
        eval ("\$data[\$i] = \$data_row$i; ");
    }
} else {
    foreach($data_row0 as $key=>$val) {
        $data[$key] = array($data_row0[$key],$data_row1[$key],$data_row2[$key],$data_row3[$key],$data_row4[$key]);
    }
}


////////////////////////////////////////////////

//Required Settings
    include("../phplot.php");
    $graph = new PHPlot($xsize_in, $ysize_in);
    $graph->SetDataType($which_data_type);  // Must be first thing

    $graph->SetDataValues($data);

//Optional Settings (Don't need them)

//  $graph->SetTitle("This is a\n\rmultiple line title\n\rspanning three lines.");
    $graph->SetTitle($title);
    $graph->SetXTitle($xlbl, $which_xtitle_pos);
    $graph->SetYTitle($ylbl, $which_ytitle_pos);
    $graph->SetLegend(array("2000","2001","2002","2003"));

    $graph->SetFileFormat($which_fileformat);
    $graph->SetPlotType($which_plot_type);

    $graph->SetUseTTF($which_use_ttf);

    $graph->SetYTickIncrement($which_yti);
    $graph->SetXTickIncrement($which_xti);
    $graph->SetXTickLength($which_xtl);
    $graph->SetYTickLength($which_ytl);
    $graph->SetXTickCrossing($which_xtc);
    $graph->SetYTickCrossing($which_ytc);
    $graph->SetXTickPos($which_xtick_pos);
    $graph->SetYTickPos($which_ytick_pos);


    $graph->SetShading($which_shading);
    $graph->SetLineWidth($which_line_width);
    $graph->SetErrorBarLineWidth($which_errorbar_line_width);

    $graph->SetDrawDashedGrid($which_dashed_grid);
    switch($which_draw_grid) {
    case 'x':
        $graph->SetDrawXGrid(TRUE);
        $graph->SetDrawYGrid(FALSE);
        break;
    case 'y':
        $graph->SetDrawXGrid(FALSE);
        $graph->SetDrawYGrid(TRUE);
        break;
    case 'both':
        $graph->SetDrawXGrid(TRUE);
        $graph->SetDrawYGrid(TRUE);
        break;
    case 'none':
        $graph->SetDrawXGrid(FALSE);
        $graph->SetDrawYGrid(FALSE);
    }

    $graph->SetXTickLabelPos($which_xtick_label_pos);
    $graph->SetYTickLabelPos($which_ytick_label_pos);
    $graph->SetXDataLabelPos($which_xdata_label_pos);
    $graph->SetYDataLabelPos($which_ydata_label_pos);

    // Please remember that angles other than 90 are taken as 0 when working fith fixed fonts.
    $graph->SetXLabelAngle($which_xlabel_angle);
    $graph->SetYLabelAngle($which_ylabel_angle);

    // Tests...
    //$graph->SetLineStyles(array("dashed","dashed","solid","solid"));
    //$graph->SetPointShapes(array("plus", "circle", "trianglemid", "diamond"));
    //$graph->SetPointSizes(array(15,10));

    $graph->SetPointShapes($which_point);
    $graph->SetPointSizes($which_point_size);
    $graph->SetDrawBrokenLines($which_broken);

    // Some forms in format_chart.php don't set this variable, suppress errors.
    @ $graph->SetErrorBarShape($which_error_type);

    $graph->SetXAxisPosition($which_xap);
    $graph->SetYAxisPosition($which_yap);
    $graph->SetPlotBorderType($which_btype);

    if ($maxy_in) {
    if ($which_data_type = "text-data") {
        $graph->SetPlotAreaWorld(0,$miny_in,count($data),$maxy_in);
        }
    }

/*
//Even more settings

    $graph->SetPlotAreaWorld(0,100,5.5,1000);
    $graph->SetPlotAreaWorld(0,-10,6,35);
    $graph->SetPlotAreaPixels(150,50,600,400);

    $graph->SetDataColors(
            array("blue","green","yellow","red"),   //Data Colors
            array("black")                          //Border Colors
    );

    $graph->SetPlotBgColor(array(222,222,222));
    $graph->SetBackgroundColor(array(200,222,222)); //can use rgb values or "name" values
    $graph->SetTextColor("black");
    $graph->SetGridColor("black");
    $graph->SetLightGridColor(array(175,175,175));
    $graph->SetTickColor("black");
    $graph->SetTitleColor(array(0,0,0)); // Can be array or name
*/

//      $graph->SetPrintImage(false);
      $graph->DrawGraph();
//      xdebug_dump_function_profile(XDEBUG_PROFILER_FS_SUM);
?>
