<?
  // get form data
  if(isset($_REQUEST['graphType'])) $graphType = $_REQUEST['graphType'];
  if(isset($_REQUEST['graphShowValues'])) $graphShowValues = $_REQUEST['graphShowValues'];
  if(isset($_REQUEST['graphValues'])) $graphValues = $_REQUEST['graphValues'];
  if(isset($_REQUEST['graphLabels'])) $graphLabels = $_REQUEST['graphLabels'];
  if(isset($_REQUEST['graphBarWidth'])) $graphBarWidth = $_REQUEST['graphBarWidth'];
  if(isset($_REQUEST['graphLabelSize'])) $graphLabelSize = $_REQUEST['graphLabelSize'];
  if(isset($_REQUEST['graphValuesSize'])) $graphValuesSize = $_REQUEST['graphValuesSize'];
  if(isset($_REQUEST['graphPercSize'])) $graphPercSize = $_REQUEST['graphPercSize'];
  if(isset($_REQUEST['graphBGColor'])) $graphBGColor = $_REQUEST['graphBGColor'];
  if(isset($_REQUEST['graphBarColor'])) $graphBarColor = $_REQUEST['graphBarColor'];
  if(isset($_REQUEST['graphBarBGColor'])) $graphBarBGColor = $_REQUEST['graphBarBGColor'];
  if(isset($_REQUEST['graphLabelColor'])) $graphLabelColor = $_REQUEST['graphLabelColor'];
  if(isset($_REQUEST['graphLabelBGColor'])) $graphLabelBGColor = $_REQUEST['graphLabelBGColor'];
  if(isset($_REQUEST['graphValuesColor'])) $graphValuesColor = $_REQUEST['graphValuesColor'];
  if(isset($_REQUEST['graphValuesBGColor'])) $graphValuesBGColor = $_REQUEST['graphValuesBGColor'];
  if(isset($_REQUEST['graphCreate'])) $graphCreate = $_REQUEST['graphCreate'];

  // initialize values
  if(!$graphCreate) {
    $graphType = 'vBar';
    $graphShowValues = 1;
    $graphValues = '123,456,789,987,654,321';
    $graphLabels = 'Horses,Dogs,Cats,Birds,Pigs,Cows';
    $graphBarWidth = 40;
    $graphLabelSize = 12;
    $graphValuesSize = 12;
    $graphPercSize = 12;
    $graphBGColor = '#ABCDEF';
    $graphBarColor = '#0000FF';
    $graphBarBGColor = '#E0F0FF';
    $graphLabelColor = '#000000';
    $graphLabelBGColor = '#C0E0FF';
    $graphValuesColor = '#000000';
    $graphValuesBGColor = '#FFFFFF';
  }
?>
<html>
<head>
<title>HTML-Graphs Example</title>
<style> <!--
BODY, P, SPAN, DIV, TABLE, TD, TH, UL, OL, LI {
  font-family: Arial, Helvetica;
  font-size: 14px;
  color: black;
}
--> </style>
</head>
<body marginwidth="10" marginheight="10" topmargin="10" leftmargin="10">
<h3>HTML-Graphs Example</h3>
This is an example page for HTML-Graphs. Click "Create Graph" and see how the graph
looks different each time you change the form fields:
<br><br>
<form name="f1" method="post">
<input type="hidden" name="graphCreate" value="1">
<table border="0"><tr valign="top">
<td>
  <table border="0"><tr>
  <td align="right">Graph Type:</td>
  <td><select name="graphType">
  <option value="hBar"<? if($graphType == 'hBar') echo ' selected'; ?>>horizontal
  <option value="vBar"<? if($graphType == 'vBar') echo ' selected'; ?>>vertical
  </select></td>
  </tr><tr>
  <td align="right">Show Values:</td>
  <td><select name="graphShowValues">
  <option value="0"<? if($graphShowValues == 0) echo ' selected'; ?>>% only
  <option value="1"<? if($graphShowValues == 1) echo ' selected'; ?>>abs. and %
  <option value="2"<? if($graphShowValues == 2) echo ' selected'; ?>>abs. only
  <option value="3"<? if($graphShowValues == 3) echo ' selected'; ?>>none
  </select></td>
  </tr><tr>
  <td align="right">Values (comma-separated):</td>
  <td><input type="text" name="graphValues" size="30" maxlength="200" value="<? echo $graphValues; ?>"></td>
  </tr><tr>
  <td align="right">Labels (comma-separated):</td>
  <td><input type="text" name="graphLabels" size="30" maxlength="200" value="<? echo $graphLabels; ?>"></td>
  </tr><tr>
  <td align="right">Bar Width:</td>
  <td><input type="text" name="graphBarWidth" size="3" maxlength="3" value="<? echo $graphBarWidth; ?>"></td>
  </tr><tr>
  <td align="right">Label Font Size:</td>
  <td><input type="text" name="graphLabelSize" size="2" maxlength="2" value="<? echo $graphLabelSize; ?>"></td>
  </tr><tr>
  <td align="right">Values Font Size:</td>
  <td><input type="text" name="graphValuesSize" size="2" maxlength="2" value="<? echo $graphValuesSize; ?>"></td>
  </tr><tr>
  <td align="right">Percentage Font Size:</td>
  <td><input type="text" name="graphPercSize" size="2" maxlength="2" value="<? echo $graphPercSize; ?>"></td>
  </tr></table>
</td>
<td>
  <table border="0"><tr>
  <td align="right">Graph BG Color:</td>
  <td><input type="text" name="graphBGColor" size="12" maxlength="12" value="<? echo $graphBGColor; ?>"></td>
  </tr><tr>
  <td align="right">Bars Color:</td>
  <td><input type="text" name="graphBarColor" size="12" maxlength="12" value="<? echo $graphBarColor; ?>"></td>
  </tr><tr>
  <td align="right">Bars BG Color:</td>
  <td><input type="text" name="graphBarBGColor" size="12" maxlength="12" value="<? echo $graphBarBGColor; ?>"></td>
  </tr><tr>
  <td align="right">Labels Color:</td>
  <td><input type="text" name="graphLabelColor" size="12" maxlength="12" value="<? echo $graphLabelColor; ?>"></td>
  </tr><tr>
  <td align="right">Labels BG Color:</td>
  <td><input type="text" name="graphLabelBGColor" size="12" maxlength="12" value="<? echo $graphLabelBGColor; ?>"></td>
  </tr><tr>
  <td align="right">Values Color:</td>
  <td><input type="text" name="graphValuesColor" size="12" maxlength="12" value="<? echo $graphValuesColor; ?>"></td>
  </tr><tr>
  <td align="right">Values BG Color:</td>
  <td><input type="text" name="graphValuesBGColor" size="12" maxlength="12" value="<? echo $graphValuesBGColor; ?>"></td>
  </tr></table>
</td>
</tr><tr>
<td colspan="2" align="right">
<input type="button" value="Reset" onClick="document.f1.graphCreate.value=''; document.f1.submit()">
<input type="submit" value="Create Graph">
</td>
</tr></table><br>
</form>
<?
  if($graphValues) {
    include('graphs.inc.php');
    $graph = new BAR_GRAPH($graphType);
    $graph->values = $graphValues;
    $graph->labels = $graphLabels;
    $graph->showValues = $graphShowValues;
    $graph->barWidth = $graphBarWidth;
    $graph->labelSize = $graphLabelSize;
    $graph->absValuesSize = $graphValuesSize;
    $graph->percValuesSize = $graphPercSize;
    $graph->graphBGColor = $graphBGColor;
    $graph->barColors = $graphBarColor;
    $graph->barBGColor = $graphBarBGColor;
    $graph->labelColor = $graphLabelColor;
    $graph->labelBGColor = $graphLabelBGColor;
    $graph->absValuesColor = $graphValuesColor;
    $graph->absValuesBGColor = $graphValuesBGColor;
    $graph->graphPadding = 20;
    $graph->graphBorder = '1px solid blue';
    echo $graph->create();
  }
  else echo '<h4>No values!</h4>';
?>
</body>
</html>
