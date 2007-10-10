<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');


    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_dbengine']))
			$configValues['CONFIG_DB_ENGINE'] = $_REQUEST['config_dbengine'];
	
		if (isset($_REQUEST['config_dbhost']))
			$configValues['CONFIG_DB_HOST'] = $_REQUEST['config_dbhost'];

		if (isset($_REQUEST['config_dbuser']))
			$configValues['CONFIG_DB_USER'] = $_REQUEST['config_dbuser'];

		if (isset($_REQUEST['config_dbpass']))
			$configValues['CONFIG_DB_PASS'] = $_REQUEST['config_dbpass'];

		if (isset($_REQUEST['config_dbname']))
			$configValues['CONFIG_DB_NAME'] = $_REQUEST['config_dbname'];


		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADCHECK'] = $_REQUEST['config_dbtbl_radcheck'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADREPLY'] = $_REQUEST['config_dbtbl_radreply'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = $_REQUEST['config_dbtbl_radgroupcheck'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = $_REQUEST['config_dbtbl_radgroupreply'];

		if (isset($_REQUEST['config_dbtbl_usergroup']))
			$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = $_REQUEST['config_dbtbl_usergroup'];

		if (isset($_REQUEST['config_dbtbl_radacct']))
			$configValues['CONFIG_DB_TBL_RADACCT'] = $_REQUEST['config_dbtbl_radacct'];

		if (isset($_REQUEST['config_dbtbl_operators']))
			$configValues['CONFIG_DB_TBL_DALOOPERATOR'] = $_REQUEST['config_dbtbl_operators'];

		if (isset($_REQUEST['config_dbtbl_rates']))
			$configValues['CONFIG_DB_TBL_DALORATES'] = $_REQUEST['config_dbtbl_rates'];

		if (isset($_REQUEST['config_dbtbl_hotspots']))
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = $_REQUEST['config_dbtbl_hotspots'];

			
			
		// this should probably move to some other page at some point
		if (isset($_REQUEST['config_db_pass_encrypt']))
			$configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = $_REQUEST['config_db_pass_encrypt'];
			
        include ("library/config_write.php");
    }	

	
?>		


<?php
        include_once ("library/tabber/tab-layout.php");
?>
		
<?php

    include ("menu-config.php");

?>		
			
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l['Intro']['configdb.php']?></a></h2>
				<p>
				<?php echo $l['captions']['configdb']['db'] ?>

				<form name="dbsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="Global Settings">
        <br/>

<table border='2' class='table1'>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_ENGINE'])) { echo "<font color='#FF0000'>";  }?>
						<b>DB Engine</b>

</td><td>
						<select name="config_dbengine">
						<option value="<?php echo $configValues['CONFIG_DB_ENGINE'] ?>"> <?php echo $configValues['CONFIG_DB_ENGINE'] ?> </option>
						<option value=""></option>
						<option value="mysql"> MySQL </option>
						<option value="pgsql"> PostgreSQL </option>
						<option value="odbc"> ODBC </option>
						<option value="mssql"> MsSQL </option>
						<option value="mysqli"> MySQLi </option>
						<option value="msql"> MsQL </option>
						<option value="sybase"> Sybase </option>
						<option value="sqlite"> Sqlite </option>
						<option value="oci8"> Oci8  </option>
						<option value="ibase"> ibase </option>
						<option value="fbsql"> fbsql </option>
						<option value="informix"> informix </option>
						</select>
						</font>						
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_HOST'])) { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['configdb.php']['DatabaseHostname'] ?></b>

</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_HOST'] ?>" name="config_dbhost"/>
						</font>
</td></tr>
<tr><td>
						<?php if (!($configValues['CONFIG_DB_USER'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configdb.php']['DatabaseUser'] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_USER'] ?>" name="config_dbuser" />
						</font>
</td></tr>



<tr><td>
						<?php if (!($configValues['CONFIG_DB_PASS'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configdb.php']['DatabasePass'] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_PASS'] ?>" name="config_dbpass" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_NAME'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo  $l['FormField']['configdb.php']['DatabaseName'] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_NAME'] ?>" name="config_dbname" />
						</font>
</td></tr>

</table>

	</div>

     <div class="tabbertab" title="Tables Settings">
        <br/>

<table border='2' class='table1'>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_TBL_RADCHECK'])) { echo "<font color='#FF0000'>";  }?>
						<b>radcheck</b>

</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADCHECK'] ?>" name="config_dbtbl_radcheck"/>
						</font>
</td></tr>

<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_RADREPLY'])) { echo "<font color='#FF0000'>";  }?>

						<b>radreply</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADREPLY'] ?>" name="config_dbtbl_radreply" />
						</font>
</td></tr>



<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_RADGROUPREPLY'])) { echo "<font color='#FF0000'>";  }?>

						<b>radgroupreply</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADGROUPREPLY'] ?>" name="config_dbtbl_radgroupreply" />
						</font>
</td></tr>






<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_RADGROUPCHECK'])) { echo "<font color='#FF0000'>";  }?>

						<b>radgroupcheck</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADGROUPCHECK'] ?>" name="config_dbtbl_radgroupcheck" />
						</font>
</td></tr>





<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_RADUSERGROUP'])) { echo "<font color='#FF0000'>";  }?>

						<b>usergroup</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ?>" name="config_dbtbl_usergroup" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_RADACCT'])) { echo "<font color='#FF0000'>";  }?>

						<b>radacct</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_RADACCT'] ?>" name="config_dbtbl_radacct" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_DALOOPERATOR'])) { echo "<font color='#FF0000'>";  }?>

						<b>operators</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOOPERATOR'] ?>" name="config_dbtbl_operators" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_DALORATES'])) { echo "<font color='#FF0000'>";  }?>

						<b>rates</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_DALORATES'] ?>" name="config_dbtbl_rates" />
						</font>
</td></tr>




<tr><td>
						<?php if (!($configValues['CONFIG_DB_TBL_DALOHOTSPOTS'])) { echo "<font color='#FF0000'>";  }?>

						<b>hotspots</b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] ?>" name="config_dbtbl_hotspots" />
						</font>
</td></tr>



</table>

</div>

     <div class="tabbertab" title="Advanced Settings">
        <br/>

<table border='2' class='table1'>
<tr><td>

						<?php if (!($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) { echo "<font color='#FF0000'>";  }?>
						<b>DB Password Encryption Type</b>

</td><td>
						<select name="config_db_pass_encrypt">
						<option value="<?php echo $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] ?>"> <?php echo $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] ?> </option>
						<option value=""></option>
						<option value="cleartext"> cleartext </option>
						<option value="crypt"> unix crypt </option>
						<option value="md5"> md5 </option>
						</select>
						</font>						
</td></tr>
</table>

	</div>
</div>

						<center>
						
						<br/><br/>
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
