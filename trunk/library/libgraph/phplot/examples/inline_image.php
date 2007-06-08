<?php
/* $Id: inline_image.php,v 1.8 2004/10/24 15:44:13 migueldb Exp $ */

if (! isset($_GET['which_title'])) {
echo <<<EOF
<pre>
           *************************************************
           * This file is meant to be called only from the *
           *                   <a href="test_setup.php">test page</a>                   *
           * It will fail if called by itself.             *
           *************************************************
</pre>
EOF
;
exit;
}

// From PHP 4.?, register_globals is off, take it into account (MBD)

include('../phplot.php');
$graph = new PHPlot;
include('./data.php');
$graph->SetTitle("$_GET[which_title]");
$graph->SetDataValues($example_data);
$graph->SetIsInline(true);
$graph->SetFileFormat("$_GET[which_format]");
$graph->DrawGraph();
?>
