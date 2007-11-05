<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$username_prefix = "";
	$number = "";
	$length_pass = "";
	$length_user = "";
	$group = "";
	$group_priority = "";

	$logDebugSQL = "";

function createPassword($length) {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= ($length - 1)) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

}


	if (isset($_POST['submit'])) {
		$username_prefix = $_REQUEST['username_prefix'];
		$number = $_REQUEST['number'];
		$length_pass = $_REQUEST['length_pass'];
		$length_user = $_REQUEST['length_user'];
		$group = $_REQUEST['group'];
		$group_priority = $_REQUEST['group_priority'];
		
		include 'library/opendb.php';
		include 'include/management/attributes.php';                            // required for checking if an attribute

		$actionMsgBadUsernames = "";
		$actionMsgGoodUsernames = "";
		
		for ($i=0; $i<$number; $i++) {
			$username = createPassword($length_user);
			$password = createPassword($length_pass);

			// append the prefix to the username
			$username  = $username_prefix . $username;

//			echo "username: $username <br/>";
//			echo "password: $password <br/>";

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() > 0) {
			$actionStatus = "failure";
			$actionMsgBadUsernames = $actionMsgBadUsernames . $username . ", " ;
			$actionMsg = "skipping matching entry: <b> $actionMsgBadUsernames </b>";
		} else {
		
			// insert username/password
			$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'User-Password', '==', '$password')";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			// if a group was defined to add the user to in the form let's add it to the database
			if (isset($group)) {
				$sql = "INSERT INTO ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." values ('$username', '$group', $group_priority) ";
	                        $res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}

				 foreach( $_POST as $attribute=>$value ) { 

                                        // switch case to rise the flag for several $attribute which we do not
                                        // wish to process (ie: do any sql related stuff in the db)
                                        switch ($attribute) {

                                                case "username_prefix":
                                                case "length_pass":
                                                case "length_user":
                                                case "number":
                                                case "submit":
                                                case "group":
                                                case "group_priority":
                                                        $skipLoopFlag = 1;      // if any of the cases above has been met we set a flag
                                                                                // to skip the loop (continue) without entering it as
                                                                                // we do not want to process this $attribute in the following
                                                                                // code block
                                                        break;

                                        }

                                        if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;              // resetting the loop flag
                                                continue;
                                        }


					if (!($value[0]))
						continue;
						
						$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
																		// or radcheck table (using include/management/attributes.php function)

				        $counter = 0;

					$sql = "INSERT INTO $useTable values (0, '$username', '$attribute', '" . $value[1] ."', '$value[0]')  ";
		                        $res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$counter++;

				} // foreach

				$actionMsgGoodUsernames = $actionMsgGoodUsernames . $username . ", " ;
				$actionStatus = "success";
				$actionMsg = "Added to database new user: <b> $actionMsgGoodUsernames </b>";
				$logAction = "Successfully added to database new users [$actionMsgGoodUsernames] on page: ";
		} 
		
		}

		include 'library/closedb.php';

	}




	include_once('library/config_read.php');
    $log = "visited page: ";

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>


<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-main.php");
	
?>

		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngbatch.php'] ?></a></h2>

                                <div id="helpPage" style="display:none;visibility:visible" >	
					<?php echo $l['helpPage']['mngbatch'] ?>
				</div>

				<form name="batchuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['AccountInfo']; ?>">
<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['AccountInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
						<b><?php echo $l['FormField']['mngbatch.php']['UsernamePrefix'] ?></b>
</td><td>
						<input value="<?php echo $username_prefix ?>" name="username_prefix" tabindex=100 /><br/>
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['mngbatch.php']['NumberInstances'] ?></b>
</td><td>
						<input value="<?php echo $number ?>" name="number" tabindex=101 /><br/>
</td></tr>
<tr><td>

						<b><?php echo $l['FormField']['mngbatch.php']['UsernameLength'] ?></b>
</td><td>
	<SELECT name="length_user" tabindex=102>
          <OPTION id="4"> 4 </OPTION>
          <OPTION id="5"> 5 </OPTION>
          <OPTION id="6"> 6 </OPTION>
          <OPTION id="8"> 8 </OPTION>
          <OPTION id="10"> 10 </OPTION>
          <OPTION id="12"> 12 </OPTION>
        </SELECT><br/>
</td></tr>
<tr><td>

						<b><?php echo $l['FormField']['mngbatch.php']['PasswordLength'] ?></b>
</td><td>
	<SELECT name="length_pass" tabindex=103>
          <OPTION id="4"> 4 </OPTION>
          <OPTION id="5"> 5 </OPTION>
          <OPTION id="6"> 6 </OPTION>
          <OPTION id="8"> 8 </OPTION>
          <OPTION id="10"> 10 </OPTION>
          <OPTION id="12"> 12 </OPTION>
        </SELECT><br/>
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['Group']; ?></b>
</td><td>
						<input value="<?php if (isset($group)) echo $group ?>" name="group" id="group" tabindex=104 />

<select onChange="javascript:setStringText(this.id,'group')" id='usergroup' tabindex=105>
<?php

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table
        
	$sql = "SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']."";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0]
                        ";

        }

        include 'library/closedb.php';
?>
</select>


	
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['all']['GroupPriority']; ?></b>
</td><td>
						<input value="<?php if (isset($group_priority)) echo $group_priority ?>" name="group_priority" tabindex=106 />
</td></tr>
</table>

     </div>
     <div class="tabbertab" title="<?php echo $l['table']['Attributes']; ?>">
	<?php
	        include_once('include/management/attributes.php');
	        drawAttributes();
	?>
	<br/>

     </div>

</div>

			<br/><br/>
<center>
			<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?> " tabindex=1000 />
</center>

				</form>


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





