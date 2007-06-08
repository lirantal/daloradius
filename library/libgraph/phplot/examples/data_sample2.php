<?php 
//data-data as just data
		$data = array(
			array("label 0", 0,   2, 5, 3 ), 	
			array("label 1", 2,   3, 4, 2 ),
			array("label 2", 3,   4, 3, 7 ),
			array("label 3", 4.5, 5, 2, 4 ),
			array("label 4", 5,   6, 1, 1 )
		);
?>
<p>
Data type: (data-data)<br />
<input type="hidden" name="which_data_type" value="data-data" />
<table border=1>
 <tr>
  <td>Title (data label)</td><td>X data</td><td>Y data 1</td>
  <td>Y data 2</td><td>Y data 3</td>
 </tr>
 
  <?php 
    for ($i=0; $i<5; $i++) {
  ?>
 <tr>
  <td>
   <input type="text" name="data_row0[<?php echo $i?>]" value="<?php echo $data[$i][0]?>" />
  </td><td>
   <input type="text" name="data_row1[<?php echo $i?>]" value="<?php echo $data[$i][1]?>" size="3" />
  </td><td>
   <input type="text" name="data_row2[<?php echo $i?>]" value="<?php echo $data[$i][2]?>" size="3" />
  </td><td>
   <input type="text" name="data_row3[<?php echo $i?>]" value="<?php echo $data[$i][3]?>" size="3" />
  </td><td>
   <input type="text" name="data_row4[<?php echo $i?>]" value="<?php echo $data[$i][4]?>" size="3" />
  </td>
 </tr>
 
  <?php 
    }
  ?>

</table>

<p>
Graph type:
<select name="which_plot_type">
  <option value="area">Area</option>
  <option value="lines">Lines</option>
  <option value="squared">Squared lines</option>
  <option value="linepoints">Line and points</option>
  <option value="points">Points</option>
</select>
</p>
