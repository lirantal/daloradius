<?php

	include ("menu-config.php");
	include ("library/config_read.php");


	
?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Database Configuration</a></h2>
				<p>
				Below are the settings that daloRADIUS will make use of to connect to your
				MySQL database server and manage it.

				<br/><br/>

				<form name="dbsettings" action="config-db.php" method="post">




<li>
						<?php if (!($configValues['CONFIG_LANG'])) { echo "<font color='#FF0000'>";  }?>

						<b>Primary Language</b>
<li>
						<input value="<?php echo $configValues['CONFIG_LANG'] ?>" name="lang" />
						</font>
</li></li>


<br/><br/>

<li>
						<?php if (!($configValues['CONFIG_DB_PASS'])) { echo "<font color='#FF0000'>";  }?>

						<b>Secondary Language</b>
</li><li>
						<input value="<?php echo $configValues['CONFIG_DB_PASS'] ?>" name="lang" />
						</font>
</li>


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
