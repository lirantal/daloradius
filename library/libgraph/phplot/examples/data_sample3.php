<p>
Data set as X, Y, E+, E-, Y2, E2+, E2-,... <br />
<?php
//data-data-error
        $num_rows = 6;
		$data = array(
			array("label 0", 0, 1, .5, .1, 1,  .2, .1), 	
			array("label 1", 2, 5, .5, .4, 2,  .1, .3),
			array("label 2", 3, 2, .1, .1, 3,  .3, .1),
			array("label 3", 4, 5, .5, .5, 3.5,.1, .2),
			array("label 4", 5, 1, .1, .1, 5,  .1, .1),
            array("label 5", 6, 2, .1, .2, 0,  .2, .3)
		);
?>
Data type: (data-data-error)
</p>
<input type="hidden" name="which_data_type" value="data-data-error" />
<table border=1>
 <tr><td>Title (data label)</td><td>X data</td> 
  <td>Y data 1</td><td>Error +</td><td>Error -</td><td>Y data 2</td><td>Error +</td><td>Error -</td>
 </tr>
 <tr>
  <td>
  
   <?php
    // MBD: All this is more complicated than before, but allows for easy adding of rows and columns
    echo "<input type=\"hidden\" name=\"num_data_rows\" value=\"$num_rows\" />";
    
    for ($i = 0; $i < $num_rows; $i++) {
        // The label input element must be bigger.
        $lines[0] = "<input type=\"text\" name=\"data_row".$i."[0]\" value=\"".$data[$i][0]."\" size=\"10\" />\n";
        
        // Show <input>s for the rest of the columns
        for ($j=1; $j<8; $j++)
            $lines[$j] = "<input type=\"text\" name=\"data_row".$i."[$j]\" value=\"".$data[$i][$j]."\" size=\"3\" />\n";
        $groups[$i] = join('</td><td>', $lines);
    }
    echo join("</tr><tr><td>\n", $groups);
    ?>
    
  </td>
 </tr>
</table>

<p>
Graph type: 
<select name="which_plot_type">
 <option value="lines">lines</option>
 <option value="linepoints">line and points</option>
 <option value="points">points</option>
</select>
&nbsp; &nbsp;&nbsp;
Error bar type:
<select name="which_error_type"> 
 <option value="tee">tee</option>
 <option value="line">line</option>
</select>
</p>
