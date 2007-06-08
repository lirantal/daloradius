======================================================================================================================
INTRODUCTION
======================================================================================================================
Use the BAR_GRAPH class to create horizontal / vertical bar-graphs, progress bars and faders. You can
create simple graphs and even grouped bar-graphs with legends, add labels, change colors etc. No graphics
or graphical libraries (GD etc.) required.

======================================================================================================================
LICENSE
======================================================================================================================
This script is freeware for non-commercial use. If you like it, please feel free to make a donation!
However, if you intend to use the script in a commercial project, please donate at least EUR 6.
You can make a donation on my website: http://www.gerd-tentler.de/tools/phpgraphs/

======================================================================================================================
USAGE
======================================================================================================================
The BAR_GRAPH class creates the graph and returns the corresponding HTML code.

Syntax:

See examples below.

Required:

  o values => graph data: array or string with comma-separated values

Optional:

  o type => graph type: "hBar", "vBar", "pBar", or "fader"

  o graphBGColor => graph background color: string
  o graphBorder => graph border: string (CSS specification: "size style color", e.g. "1px solid black"; doesn't work with NN4)
  o graphPadding => graph padding: integer (pixels)

  o labels => label names: array or string with comma-separated values
  o labelColor => label font color: string
  o labelBGColor => label background color: string
  o labelBorder => label border: string (CSS specification: "size style color", e.g. "1px solid black"; doesn't work with NN4)
  o labelFont => label font family: string (CSS specification, e.g. "Arial, Helvetica")
  o labelSize => label font size: integer (pixels)
  o labelSpace => additional space between labels: integer (pixels)

  o barWidth => bar width: integer (pixels)
  o barLength => bar length ratio: float (from 0.1 to 2.9, default is 1.0)
  o barColors => bar colors OR bar images: array or string with comma-separated values
  o barBGColor => bar background color: string
  o barBorder => bar border: string (CSS specification: "size style color", e.g. "1px solid black"; doesn't work with NN4)
  o barLevelColors => bar level colors: ascending array (bLevel, bColor[,...]); draw bars >= bLevel with bColor

  o showValues => show values: integer (0 = % only [default], 1 = abs. and %, 2 = abs. only, 3 = none)

  o absValuesColor => abs. values font color: string
  o absValuesBGColor => abs. values background color: string
  o absValuesBorder => abs. values border: string (CSS specification: "size style color", e.g. "1px solid black"; doesn't work with NN4)
  o absValuesFont => abs. values font family: string (CSS specification, e.g. "Arial, Helvetica")
  o absValuesSize => abs. values font size: integer (pixels)
  o absValuesPrefix => abs. values prefix: string (e.g. "$")
  o absValuesSuffix => abs. values suffix: string (e.g. " kg")

  o percValuesColor => perc. values font color: string
  o percValuesFont => perc. values font family: string (CSS specification, e.g. "Arial, Helvetica")
  o percValuesSize => perc. values font size: integer (pixels)
  o percValuesDecimals => perc. values number of decimals: integer

  o charts => number of charts: integer

  hBar/vBar only:
  o legend => legend items: array or string with comma-separated values
  o legendColor => legend font color: string
  o legendBGColor => legend background color: string
  o legendBorder => legend border: string (CSS specification: "size style color", e.g. "1px solid black"; doesn't work with NN4)
  o legendFont => legend font family: string (CSS specification, e.g. "Arial, Helvetica")
  o legendSize => legend font size: integer (pixels)

Example #1 (simple bar graph):

  $graph = new BAR_GRAPH("hBar");
  $graph->values = "380,150,260,310,430";
  echo $graph->create();

Example #2 (grouped bars with legend):

  $graph = new BAR_GRAPH("hBar");
  $graph->values = "50;30;40, 60;80;50, 70;40;60";
  $graph->legend = "cats,dogs,birds";
  echo $graph->create();

Progress bars and faders are a bit different from the other graph types. You have to specify two values
for each bar: The first one is the actual value, the second one is the maximum value of the progress bar
or fader, i.e. the value that equals 100%. The two values are separated by a semicolon.

Example #3 (single progress bar):

  $graph = new BAR_GRAPH("pBar");
  $graph->values = "123;456";
  echo $graph->create();

Example #4 (multiple progress bars with labels):

  $graph = new BAR_GRAPH("pBar");
  $graph->values = "50;100, 60;100, 70;100";
  $graph->labels = "cats,dogs,birds";
  echo $graph->create();

======================================================================================================================
Source code + examples available at http://www.gerd-tentler.de/tools/phpgraphs/.
======================================================================================================================
