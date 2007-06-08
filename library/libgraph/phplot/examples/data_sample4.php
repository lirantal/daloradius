<p>
Data type: <i>data-data</i> calculated from a function.
</p>
<pre>
	$dx = ".3";
	$max = 6.4;
	$maxi = $max/$dx;
	for ($i=0; $i<$maxi; $i++) {
		$a = 4;
		$x = $dx*$i;
		$data[$i] = array("", $x, $a*sin($x), 
                              $a*cos($x), $a*cos($x+1)); 	
	}
</pre>
<p>
Chart type: 
<select name="which_plot_type">
    <option value="lines">Lines</option>
    <option value="linepoints">Lines and points</option>
    <option value="points">Points</option>
    <option value="thinbarline">Thin bars</option>
</select>
<input type="hidden" name="which_data_type" value="function" />
</p>
