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

<table>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_HOST'])) { echo "<font color='#FF0000'>";  }?>
						<b>Database Hostname</b>

</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_HOST'] ?>" name="dbhost"/>
						</font>
</td></tr>
<tr><td>
						<?php if (!($configValues['CONFIG_DB_NAME'])) { echo "<font color='#FF0000'>";  }?>
</td><td>
						<b>Database Name</b>
						<input value="<?php echo $configValues['CONFIG_DB_NAME'] ?>" name="dbname" />
						</font>
</td></tr>
<tr><td>

						<br/>
						<input type="submit" name="submit" value="Apply" />
</td></tr>
</table>
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
