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

		// create the optional arguments variable

		// convert the debug = yes to the actual debug option which is "-x" to pass to radclient
		if ($debug == "yes")
			$debug = "-x";
		else
			$debug = "";

                $options = array("count" => $count, "requests" => $requests,
                                        "retries" => $retries, "timeout" => $timeout,
                                        "debug" => $debug,
                                        );

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

     <div class="tabbertab" title="<?php echo $l['title']['Settings']; ?>">


	<fieldset>

		<h302> Settings </h302>
		<br/>

                <label for='username' class='form'><?php echo $l['all']['Username']?></label>
                <input name='username' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
                <br />
 
		<label for='packettype' class='form'><?php echo $l['all']['PacketType'] ?></label>
                <select name='packettype' id='packettype' class='form' tabindex=101 >
			<option value="disconnect"> PoD - Packet of Disconnect</option>
			<option value="coa"> CoA - Change of Authorization </option>
                </select>
                <br/>

                <label for='nasaddr' class='form'><?php echo $l['all']['NasIPHost'] ?></label>
                <input name='nasaddr' type='text' id='nasaddr' value='' tabindex=102 />

		<select onChange="javascript:setStringText(this.id,'nasaddr')" id='naslist' tabindex=103 class='form' >
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
                <br/>

                <label for='nassecret' class='form'><?php echo $l['all']['NasSecret'] ?></label>
                <input name='nassecret' type='text' id='nassecret' value='' tabindex=104 />
		<select onChange="javascript:setStringText(this.id,'nassecret')" id='nassecretlist' class='form' 
			tabindex=105 >
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

                <br/>

                <label for='nasport' class='form'><?php echo $l['all']['NasPorts'] ?></label>
                <input name='nasport' type='text' id='nasport' value='3799' tabindex=106 />
		<select onChange="javascript:setStringText(this.id,'nasport')" id='nasportlist' tabindex=107 >
			<option value="3799"> Choose Port... </option>
			<option value="3799"> 3799 </option>
			<option value="1700"> 1700 </option>
		</select>
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='Perform Test' class='button' />

        </fieldset>

	</div>


     <div class="tabbertab" title="<?php echo $l['title']['Advanced']; ?>">

        <fieldset>

                <h302> Advanced </h302>
                <br/>

                <label for='debug' class='form'><?php echo $l['all']['Debug'] ?></label>
                <select name='debug' id='debug' class='form' tabindex=106 >
                        <option value="yes"> Yes </option>
                        <option value="no"> No </option>
                </select>
                <br/>

                <label for='timeout' class='form'><?php echo $l['all']['Timeout'] ?></label>
                <input name='timeout' type='text' id='timeout' value='3' tabindex=107 />
                <br/>

                <label for='retries' class='form'><?php echo $l['all']['Retries'] ?></label>
                <input name='retries' type='text' id='retries' value='3' tabindex=108 />
                <br/>

                <label for='count' class='form'><?php echo $l['all']['Count'] ?></label>
                <input name='count' type='text' id='count' value='' tabindex=109 />
                <br/>

                <label for='requests' class='form'><?php echo $l['all']['Requests'] ?></label>
                <input name='requests' type='text' id='requests' value='3' tabindex=110 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='Perform Test' class='button' />

        </fieldset>

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
