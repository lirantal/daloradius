<p>
Data type: (Text-data)<br />
</p>
<input type="hidden" name="which_data_type" value="text-data" />
<table border=1>
 <tr>
  <td>Title (x axis)</td><td>Y data 1</td><td>Y data 2</td>
  <td>Y data 3</td> <td>Y data 4</td>
 </tr>
 
<?php 
	srand ((double) microtime() * 12341234);
	$a = 25;
	$b = 10;
	$c = -5;
	for ($i=0; $i<5; $i++) {
		$a += rand(-2, 2);
		$b += rand(-5, 5);
		$c += rand(-2, 2);

?>
 <tr>
  <td>
   <input type="text" name="data_row0[<?php echo $i?>]" value="Year <?php echo $i?>" />
  </td><td>
   <input type="text" name="data_row1[<?php echo $i?>]" value="<?php echo $a?>" size="3" />
  </td><td>
   <input type="text" name="data_row2[<?php echo $i?>]" value="<?php echo $b?>" size="3" />
  </td><td>
   <input type="text" name="data_row3[<?php echo $i?>]" value="<?php echo $c?>" size="3" />
  </td><td>
   <input type="text" name="data_row4[<?php echo $i?>]" value="<?php echo $c+1?>" size="3" />
  </td>
 </tr>
<?php 
	}
?>

</table>

<p>
Graph type:
<select name="which_plot_type">
  <option value="bars">Bars (*)</option>
  <option value="stackedbars">Stacked bars (*)</option>
  <option value="thinbarline">Thin bars</option>
  <option value="lines">Lines</option>
  <option value="squared">Squared lines</option>
  <option value="pie">Pie (*)</option>
  <option value="linepoints">Line and points</option>
  <option value="points">Points</option>
  <option value="area">Area</option>
</select>
</p>
<div style="text-align:right; font-size: smaller;">
Please note when writing your application that the graph <br />
types marked with an asterisk only support the data <br />
type for this form, "text-data".
</div>
