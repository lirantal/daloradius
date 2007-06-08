<p>
Data type: <i>data-data-error</i> calculated from a function.
</p>
<pre>
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
</pre>
<p>
Chart type: 
<select name="which_plot_type">
    <option value="lines">Lines</option>
    <option value="linepoints">Lines and points</option>
    <option value="points">Points</option>
</select>
&nbsp; &nbsp;&nbsp;
Error bar type:
<select name="which_error_type">
 <option value="tee">tee</option>
 <option value="line">line</option>
</select>
<input type="hidden" name="which_data_type" value="randfunction" />
</p>
