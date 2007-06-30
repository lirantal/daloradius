<?php

	include ("menu-config.php");
	include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_lang']))
			$configValues['CONFIG_LANG'] = $_REQUEST['config_lang'];
			
            include ("library/config_write.php");
    }
	

	
?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Database Configuration</a></h2>
				<p>
				Below are the settings that daloRADIUS will make use of to connect to your
				MySQL database server and manage it.

				<br/><br/>

				<form name="langsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



<table>
<tr><td>
						<?php if (!($configValues['CONFIG_LANG'])) { echo "<font color='#FF0000'>";  }?>

						<b>Primary Language</b>
</td><td>
						<select name="config_lang">
						<option value="en"> en </option>
						</select>
						</font>
</td></tr>


</table>

						<center>						
						<br/>
						<input type="submit" name="submit" value="Apply" />
						</center>						


				</form>

	
				<br/><br/>
				
				</p>
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>
		
		</div>
		
</div>
</div>


</body>
</html>
