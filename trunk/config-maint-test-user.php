<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
	isset($_REQUEST['password']) ? $password = $_REQUEST['password'] : $password = "";
	isset($_REQUEST['radius']) ? $radius = $_REQUEST['radius'] : $radius = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'];
	isset($_REQUEST['radiusport']) ? $radiusport = $_REQUEST['radiusport'] : $radiusport = $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'];
	isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = $configValues['CONFIG_MAINT_TEST_USER_NASPORT'];
	isset($_REQUEST['secret']) ? $secret = $_REQUEST['secret'] : $secret = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'];
		
    if (isset($_REQUEST['submit'])) {

		include_once('library/exten-maint-test-user.php');	
		
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
		$radius = $_REQUEST['radius'];
		$radiusport = $_REQUEST['radiusport'];
		$nasport = $_REQUEST['nasport'];
		$secret = $_REQUEST['secret'];

		$actionStatus = "informational";
		$actionMsg = user_login_test($username, $password, $radius, $radiusport, $nasport, $secret);
		$logAction = "Informative action performed on user [$username] on page: ";	
    }

	
	include_once('library/config_read.php');
    $log = "visited page: ";

	
?>		

<?php

    include ("menu-config-maint.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configmainttestuser.php'] ?>	
				</a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configmainttestuser'] ?>
					<br/>
				</div>

				<form name="mainttestuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['all']['Username'] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username" />
						</font>
</td></tr>
<tr><td>

						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['all']['Password'] ?></b>
</td><td>
						<input value="<?php echo $password ?>" name="password" />
						</font>


</td></tr>
<tr><td>
						<?php if (trim($radius) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configmainttestuser.php']['RadiusServer'] ?></b>
</td><td>
						<input value="<?php echo $radius ?>" name="radius" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($radiusport) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configmainttestuser.php']['RadiusPort'] ?></b>
</td><td>
						<input value="<?php echo $radiusport ?>" name="radiusport" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($nasport) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configmainttestuser.php']['NASPort'] ?></b>
</td><td>
						<input value="<?php echo $nasport ?>" name="nasport" />
						</font>
</td></tr>
<tr><td>

						<?php if (trim($secret) == "") { echo "<font color='#FF0000'>";  }?>

						<b><?php echo $l['FormField']['configmainttestuser.php']['Secret'] ?></b>
</td><td>
						<input value="<?php echo $secret ?>" name="secret" />
						</font>
</td></tr>


</table>

						<center>						
						<br/>
						<input type="submit" name="submit" value="Perform Test" />
						</center>						


				</form>

	
				<br/><br/>
				

				
<?php
	include('include/config/logging.php');
?>				
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
