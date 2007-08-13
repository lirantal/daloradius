<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_lang']))
			$configValues['CONFIG_LANG'] = $_REQUEST['config_lang'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][configlang.php] ?></a></h2>
				<p>
				<?php echo $l[captions][configlang] ?>

				<br/><br/>

				<form name="langsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



<table border='2' class='table1'>
<tr><td>
						<?php if (!($configValues['CONFIG_LANG'])) { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l[FormField][configlang.php][PrimaryLanguage] ?></b>
</td><td>
						<select name="config_lang">
						<option value="en"> en </option>
						<option value="ru"> ru </option>
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
