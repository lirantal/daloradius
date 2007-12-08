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

		include_once('library/exten-maint-radclient.php');
		
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];

		// process advanced options to pass to radclient
		isset($_REQUEST['debug']) ? $debug = $_REQUEST['debug'] : $debug = "no";
		isset($_REQUEST['timeout']) ? $timeout = $_REQUEST['timeout'] : $timeout = 3;
		isset($_REQUEST['retries']) ? $retries = $_REQUEST['retries'] : $retries = 3;
		isset($_REQUEST['count']) ? $count = $_REQUEST['count'] : $count = 1;
		isset($_REQUEST['retries']) ? $requests = $_REQUEST['requests'] : $requests = 3;

		if ( (isset($_REQUEST['debug'])) && ( ($debug != "yes") || ($debug != "no") ) )
			$debug = "yes";

		// create the optional arguments variable

		// conver the debug = yes to the actual debug option which is "-x" to pass to radclient
		if ($debug == "yes")
			$debug = "-x";
		else
			$debug = "";

		$options = " $debug -c $count -n $requests -r $retries -t $timeout ";
		

		$actionStatus = "informational";
		$actionMsg = user_auth($options,$username, $password, $radius, $radiusport, $secret);
		$logAction = "Informative action performed on user [$username] on page: ";	
    }

	
	include_once('library/config_read.php');
    $log = "visited page: ";

	
?>		

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-config-maint.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configmainttestuser.php'] ?>
				<h144>+</h144> </a></h2>

		                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configmainttestuser'] ?>
					<br/>
				</div>
				<br/>

				<form name="mainttestuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['Settings']; ?>">

<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['Settings']; ?> </th>
                                                        </tr>
                                        </thead>


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

	</div>


     <div class="tabbertab" title="<?php echo $l['table']['Advanced']; ?>">


<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['Advanced']; ?> </th>
                                                        </tr>
                                        </thead>


<tr><td>
						<b><?php echo $l['FormField']['all']['Debug'] ?></b>
</td><td>
						<select name="debug">
						<option value="yes"> Yes </option>
						<option value="no"> No </option>
						</select>

</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['Timeout'] ?></b>
</td><td>
						<input value="3" name="timeout" />
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['Retries'] ?></b>
</td><td>
						<input value="3" name="retries" />
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['Count'] ?></b>
</td><td>
						<input value="1" name="count" />
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['Requests'] ?></b>
</td><td>
						<input value="3" name="requests" />
</td></tr>
</table>

	</div>


						<center>						
						<br/>
						<input type="submit" name="submit" value="Perform Test" />
						</center>

</div>




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
