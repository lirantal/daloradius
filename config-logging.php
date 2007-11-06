<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_pageslogging']))
			$configValues['CONFIG_LOG_PAGES'] = $_REQUEST['config_pageslogging'];
		if (isset($_REQUEST['config_querieslogging']))
			$configValues['CONFIG_LOG_QUERIES'] = $_REQUEST['config_querieslogging'];
		if (isset($_REQUEST['config_actionslogging']))
			$configValues['CONFIG_LOG_ACTIONS'] = $_REQUEST['config_actionslogging'];
		if (isset($_REQUEST['config_filenamelogging']))
			$configValues['CONFIG_LOG_FILE'] = $_REQUEST['config_filenamelogging'];

		if (isset($_REQUEST['config_debuglogging']))
			$configValues['CONFIG_DEBUG_SQL'] = $_REQUEST['config_debuglogging'];
		if (isset($_REQUEST['config_debugpageslogging']))
			$configValues['CONFIG_DEBUG_SQL_ONPAGE'] = $_REQUEST['config_debugpageslogging'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configlogging.php'] ?></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['configlogging'] ?>		
		</div>
				<br/><br/>

				<form name="loggingsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (!($configValues['CONFIG_LOG_PAGES'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configlogging.php']['PagesLogging'] ?></b>
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

						<?php if (!($configValues['CONFIG_LOG_QUERIES'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configlogging.php']['QueriesLogging'] ?></b>
</td><td>
						<select name="config_querieslogging">
						<option value="<?php echo $configValues['CONFIG_LOG_QUERIES'] ?>"> <?php echo $configValues['CONFIG_LOG_QUERIES'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>


</td></tr>
<tr><td>


						<?php if (!($configValues['CONFIG_LOG_ACTIONS'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configlogging.php']['ActionsLogging'] ?></b>
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


						<?php if (!($configValues['CONFIG_DEBUG_SQL'])) { echo "<font color='#FF0000'>";  }?>

						<b>Logging of Debug info</b>
</td><td>
						<select name="config_debuglogging">
						<option value="<?php echo $configValues['CONFIG_DEBUG_SQL'] ?>"> <?php echo $configValues['CONFIG_DEBUG_SQL'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>
</td></tr>
<tr><td>


						<?php if (!($configValues['CONFIG_DEBUG_SQL_ONPAGE'])) { echo "<font color='#FF0000'>";  }?>

						<b>Logging of Debug info on pages</b>
</td><td>
						<select name="config_debugpageslogging">
						<option value="<?php echo $configValues['CONFIG_DEBUG_SQL_ONPAGE'] ?>"> <?php echo $configValues['CONFIG_DEBUG_SQL_ONPAGE'] ?> </option>
						<option value="">  </option>
						<option value="no"> no </option>
						<option value="yes"> yes </option>
						</select>
						</font>
</td></tr>
<tr><td>

						<?php if (!($configValues['CONFIG_LOG_FILE'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configlogging.php']['FilenameLogging'] ?></b>
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
