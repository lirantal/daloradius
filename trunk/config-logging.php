<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_pageslogging']))
			$configValues['CONFIG_LOG_PAGES'] = $_REQUEST['config_pageslogging'];
		if (isset($_REQUEST['config_actionslogging']))
			$configValues['CONFIG_LOG_ACTIONS'] = $_REQUEST['config_actionslogging'];
		if (isset($_REQUEST['config_filenamelogging']))
			$configValues['CONFIG_LOG_FILE'] = $_REQUEST['config_filenamelogging'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][configlogging.php] ?></a></h2>
				<p>
				<?php echo $l[captions][configlogging] ?>

				<br/><br/>

				<form name="loggingsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (!($configValues['CONFIG_LOG_PAGES'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l[FormField][configlogging.php][PagesLogging] ?></b>
</td><td>
						<select name="config_pageslogging">
						<option value="<?php echo $configValues['CONFIG_LOG_PAGES'] ?>"> <?php echo $configValues['CONFIG_LOG_PAGES'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_LOG_ACTIONS'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l[FormField][configlogging.php][ActionsLogging] ?></b>
</td><td>
						<select name="config_actionslogging">
						<option value="<?php echo $configValues['CONFIG_LOG_ACTIONS'] ?>"> <?php echo $configValues['CONFIG_LOG_ACTIONS'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_LOG_FILE'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l[FormField][configlogging.php][FilenameLogging] ?></b>
</td><td>
						<input value="<?php echo $configValues['CONFIG_LOG_FILE'] ?>" name="config_filenamelogging" />
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
