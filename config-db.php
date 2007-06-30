<?php

	include ("menu-config.php");
	include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_dbhost']))
			$configValues['CONFIG_DB_HOST'] = $_REQUEST['config_dbhost'];

		if (isset($_REQUEST['config_dbuser']))
			$configValues['CONFIG_DB_USER'] = $_REQUEST['config_dbuser'];

		if (isset($_REQUEST['config_dbpass']))
			$configValues['CONFIG_DB_PASS'] = $_REQUEST['config_dbpass'];

		if (isset($_REQUEST['config_dbname']))
			$configValues['CONFIG_DB_NAME'] = $_REQUEST['config_dbname'];

			
            include ("library/config_write.php");
    }	

	
?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Database Configuration</a></h2>
				<p>
				Below are the settings that daloRADIUS will make use of to connect to your
				MySQL database server and manage it.

				<br/><br/>

				<form name="dbsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_HOST'])) { echo "<font color='#FF0000'>";  }?>
						<b>Database Hostname</b>

</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_HOST'] ?>" name="config_dbhost"/>
						</font>
</td></tr>

<tr><td>
						<?php if (!($configValues['CONFIG_DB_USER'])) { echo "<font color='#FF0000'>";  }?>

						<b>Database User</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_USER'] ?>" name="config_dbuser" />
						</font>
</td></tr>



<tr><td>
						<?php if (!($configValues['CONFIG_DB_PASS'])) { echo "<font color='#FF0000'>";  }?>

						<b>Database Pass</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_PASS'] ?>" name="config_dbpass" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_NAME'])) { echo "<font color='#FF0000'>";  }?>

						<b>Database Name</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_NAME'] ?>" name="config_dbname" />
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
