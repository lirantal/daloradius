<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_iface_pass_hidden']))
			$configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = $_REQUEST['config_iface_pass_hidden'];
		if (isset($_REQUEST['config_iface_tableslisting']))
			$configValues['CONFIG_IFACE_TABLES_LISTING'] = $_REQUEST['config_iface_tableslisting'];
		if (isset($_REQUEST['config_iface_tableslisting_num']))
			$configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = $_REQUEST['config_iface_tableslisting_num'];			
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configinterface.php'] ?></a></h2>

                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['configinterface'] ?>		
		</div>
				<br/><br/>

				<form name="interfacesettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



<table border='2' class='table1'>
<tr><td>
						<?php if (!($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configinterface.php']['PasswordHidden'] ?></b>
</td><td>
						<select name="config_iface_pass_hidden">
						<option value="<?php echo $configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] ?>"> <?php echo $configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_IFACE_TABLES_LISTING'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configinterface.php']['TablesListing'] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_IFACE_TABLES_LISTING'] ?>" name="config_iface_tableslisting" />
						</font>
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configinterface.php']['TablesListingNum'] ?></b>
</td><td>
						<select name="config_iface_tableslisting_num">
						<option value="<?php echo $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] ?>"> <?php echo $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
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
