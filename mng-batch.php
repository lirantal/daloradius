<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
	include('include/management/pages_common.php');

	$username_prefix = "";
	$number = "";
	$length_pass = "";
	$length_user = "";
	$group = "";
	$group_priority = "";

	$logDebugSQL = "";

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

		$exportCSV = "Username,Password||";
		
		
		for ($i=0; $i<$number; $i++) {
			$username = createPassword($length_user);
			$password = createPassword($length_pass);

			// append the prefix to the username
			$username  = $username_prefix . $username;

			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			if ($res->numRows() > 0) {
				$actionStatus = "failure";
				$actionMsgBadUsernames = $actionMsgBadUsernames . $username . ", " ;
				$actionMsg = "skipping matching entry: <b> $actionMsgBadUsernames </b>";
			} else {
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '".$dbSocket->escapeSimple($username)."',  'User-Password', ':=', '".$dbSocket->escapeSimple($password)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				// if a group was defined to add the user to in the form let's add it to the database
				if (isset($group)) {

					if (!($group_priority))
						$group_priority=0;		// if group priority wasn't set we
										// initialize it to 0 by default
					$sql = "INSERT INTO ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." values ('".$dbSocket->escapeSimple($username)."', 
'".$dbSocket->escapeSimple($group)."', ".$dbSocket->escapeSimple($group_priority).") ";
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

					$sql = "INSERT INTO $useTable values (0, '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($attribute)."', 
'".$dbSocket->escapeSimple($value[1])."', '".$dbSocket->escapeSimple($value[0])."')  ";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$counter++;

				} // foreach

				$actionMsgGoodUsernames = $actionMsgGoodUsernames . $username . ", " ;
				$exportCSV .= "$username,$password||";
				
				$actionStatus = "success";
				$actionMsg = "Exported Usernames -  <a href='include/common/fileExportCSV.php?csv_output=$exportCSV'>download</a><br/>
				Added to database new user: <b> $actionMsgGoodUsernames </b><br/>";

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngbatch.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngbatch'] ?>
					<br/>
				</div>
				<br/>

				<form name="batchuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['AccountInfo']; ?>">

	<fieldset>

                <h302> Account Info </h302>

                <label for='usernamePrefix'><?php echo $l['FormField']['mngbatch.php']['UsernamePrefix']?></label>
                <input name='username_prefix' type='text' id='username_prefix' value='' tabindex=100 />
		<br/>

                <label for='numberInstances'><?php echo $l['FormField']['mngbatch.php']['NumberInstances']?></label>
                <input name='number' type='text' id='number' value='' tabindex=101 />
		<br/><br/><br/>

                <label for='usernameLength'><?php echo $l['FormField']['mngbatch.php']['UsernameLength']?></label>
		<select name="length_user" tabindex=102 class='form' >
			<option id="4"> 4 </option>
			<option id="5"> 5 </option>
			<option id="6"> 6 </option>
		        <option id="8"> 8 </option>
			<option id="10"> 10 </option>
	        	<option id="12"> 12 </option>
	        </select>
		<br/>
		<br/>

                <label for='passwordLength'><?php echo $l['FormField']['mngbatch.php']['PasswordLength']?></label>
		<select name="length_pass" tabindex=103 class='form' >
		        <OPTION id="4"> 4 </OPTION>
		        <OPTION id="5"> 5 </OPTION>
		        <OPTION id="6"> 6 </OPTION>
		        <OPTION id="8"> 8 </OPTION>
		        <OPTION id="10"> 10 </OPTION>
			<OPTION id="12"> 12 </OPTION>
		</select>
		<br/>
		<br/>


                <label for='group'><?php echo $l['FormField']['all']['Group']?></label>
                <input name='group' type='text' id='group' value='' tabindex=104 />
		<?php
		        include 'include/management/populate_selectbox.php';
		        populate_groups("Select Groups");
		?>
		<br/>

                <label for='groupPriority'><?php echo $l['FormField']['all']['GroupPriority']?></label>
                <input name='group_priority' type='text' id='group_priority' value='0' tabindex=105 />

		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?> " tabindex=1000 
			class='button' />

	</fieldset>


     </div>
     <div class="tabbertab" title="<?php echo $l['table']['Attributes']; ?>">
	<?php
	        include_once('include/management/attributes.php');
	        drawAttributes();
	?>
	<br/>

     </div>

</div>

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





