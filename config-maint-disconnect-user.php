<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
	isset($_REQUEST['nasaddr']) ? $nasaddr = $_REQUEST['nasaddr'] : $nasaddr = "";
	isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = "";
	isset($_REQUEST['nassecret']) ? $nassecret = $_REQUEST['nassecret'] : $nassecret = "";
	isset($_REQUEST['packettype']) ? $packettype = $_REQUEST['packettype'] : $packettype = "";
		
    if (isset($_REQUEST['submit'])) {

	if ( ($nasaddr == "") || ($nasport == "") || ($nassecret == "") ) {

		$actionStatus = "failure";
		$actionMsg = "One of NAS Address, NAS Port or NAS Secret fields were left empty";
		$logAction = "Failed performing disconnect on user [$username] because of missing NAS fields on page: ";

	} else if ($username == "") {

		$actionStatus = "failure";
		$actionMsg = "The User-Name to disconnect was not provided";
		$logAction = "Failed performing disconnect on user [$username] because of missing User-Name on page: ";

	} else {

		include_once('library/exten-maint-radclient.php');
		
		$username = $_REQUEST['username'];

		// process advanced options to pass to radclient
		isset($_REQUEST['debug']) ? $debug = $_REQUEST['debug'] : $debug = "no";
		isset($_REQUEST['timeout']) ? $timeout = $_REQUEST['timeout'] : $timeout = 3;
		isset($_REQUEST['retries']) ? $retries = $_REQUEST['retries'] : $retries = 3;
		isset($_REQUEST['count']) ? $count = $_REQUEST['count'] : $count = 1;
		isset($_REQUEST['retries']) ? $requests = $_REQUEST['requests'] : $requests = 3;

		if ( (isset($_REQUEST['debug'])) && ( ($debug != "yes") || ($debug != "no") ) )
			$debug = "yes";

		// create the optional arguments variable

		// convert the debug = yes to the actual debug option which is "-x" to pass to radclient
		if ($debug == "yes")
			$debug = "-x";
		else
			$debug = "";

		$options = " $debug -c $count -n $requests -r $retries -t $timeout ";
		

		$actionStatus = "informational";
		$actionMsg = user_disconnect($options,$username,$nasaddr,$nasport,$nassecret,$packettype);
		$logAction = "Informative action performed on user [$username] on page: ";

	} 

    } //if submit

	
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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configmaintdisconnectuser.php'] ?>
				<h144>+</h144> </a></h2>

		                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configmaintdisconnectuser'] ?>
					<br/>
				</div>
				<br/>

				<form name="maintdisconnectuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

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
						<b><?php echo $l['FormField']['configmaintdisconnectuser.php']['PacketType'] ?></b>
</td><td>

<select name="packettype" id="packettype">
	<option value="disconnect"> PoD - Packet of Disconnect</option>
	<option value="coa"> CoA - Change of Authorization </option>
</select>
						</font>
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['configmaintdisconnectuser.php']['NASServer'] ?></b>
</td><td>
						<input value="" name="nasaddr" id="nasaddr" />

<select onChange="javascript:setStringText(this.id,'nasaddr')" id='naslist' tabindex=105>
	<option value=""> Choose NAS... </option>
<?php

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "SELECT distinct(nasname) FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].";";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

        include 'library/closedb.php';
?>
</select>
</td></tr>

<tr><td>
						<b><?php echo $l['FormField']['configmaintdisconnectuser.php']['NASSecret'] ?></b>
</td><td>
						<input value="" name="nassecret" id="nassecret" />
<select onChange="javascript:setStringText(this.id,'nassecret')" id='nassecretlist'>
	<option value=""> Choose NAS Secret... </option>
<?php

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "SELECT distinct(nasname), secret FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].";";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[1]'> NAS: $row[0] - Secret: $row[1] </option>
                        ";

        }

        include 'library/closedb.php';
?>
</select>
						</font>
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['configmaintdisconnectuser.php']['NASPort'] ?></b>
</td><td>
						<input value="3799" name="nasport" id="nasport" />
<select onChange="javascript:setStringText(this.id,'nasport')" id='nasportlist' tabindex=105>
	<option value="3799"> Choose Port... </option>
	<option value="3799"> 3799 </option>
	<option value="1700"> 1700 </option>
</select>
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
