<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_iface_pass_hidden']))
			$configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = $_REQUEST['config_iface_pass_hidden'];
		if (isset($_REQUEST['config_iface_tableslisting']))
			$configValues['CONFIG_IFACE_TABLES_LISTING'] = $_REQUEST['config_iface_tableslisting'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][configinterface.php] ?></a></h2>
				<p>
				<?php echo $l[captions][configinterface] ?>

				<br/><br/>

				<form name="interfacesettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



<table border='2' class='table1'>
<tr><td>
						<?php if (!($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l[FormField][configinterface.php][PasswordHidden] ?></b>
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

						<b><?php echo $l[FormField][configinterface.php][TablesListing] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_IFACE_TABLES_LISTING'] ?>" name="config_iface_tableslisting" />
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
