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


                                foreach($_POST as $element=>$field) {

                                        // switch case to rise the flag for several $attribute which we do not
                                        // wish to process (ie: do any sql related stuff in the db)
                                        switch ($element) {

						case "username_prefix":
						case "passwordType":
						case "length_pass":
						case "length_user":
						case "number":
						case "submit":
						case "group":
						case "group_priority":
                                                        $skipLoopFlag = 1;      // if any of the cases above has been met weset a flag
                                                                                // to skip the loop (continue) without entering it as
                                                                                // we do not want to process this $attributein the following
                                                                                // code block
                                                        break;

                                        }

                                        if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;              // resetting the loop flag
                                                continue;
                                        }


                                        if (isset($field[0]))
                                                $attribute = $field[0];
                                        if (isset($field[1]))
                                                $value = $field[1];
                                        if (isset($field[2]))
                                                $op = $field[2];
                                        if (isset($field[3]))
                                                $table = $field[3];

                                        if ( isset($table) && ($table == 'check') )
                                                $table = $configValues['CONFIG_DB_TBL_RADCHECK'];
                                        if ( isset($table) && ($table == 'reply') )
                                                $table = $configValues['CONFIG_DB_TBL_RADREPLY'];

                                        if ( (isset($field)) && (!isset($field[1])) )
                                                continue;
                                
                                        $sql = "INSERT INTO $table values (0, '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($attribute)."', 
'".$dbSocket->escapeSimple($op)."', '".$dbSocket->escapeSimple($value)."')  ";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

                                } // foreach

				$actionMsgGoodUsernames = $actionMsgGoodUsernames . $username . ", " ;
				$exportCSV .= "$username,$password||";
				
				$actionStatus = "success";
				$actionMsg = "Exported Usernames -  <a href='include/common/fileExportCSV.php?csv_output=$exportCSV'>download</a><br/>
				Added to database new user: <b> $actionMsgGoodUsernames </b><br/>";

				$logAction = "Successfully added to database new users [$actionMsgGoodUsernames] with prefix [$username_prefix] on page: ";
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
<link rel="stylesheet" href="css/auto-complete.css" media="screen" type="text/css">

<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>


<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-users.php");
	
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

     <div class="tabbertab" title="<?php echo $l['title']['AccountInfo']; ?>">

	<fieldset>

                <h302> <?php echo $l['title']['AccountInfo']; ?> </h302>
		<br/>

		<ul>
		<li class='fieldset'>
                <label for='usernamePrefix' class='form'><?php 
			echo $l['all']['UsernamePrefix'] ?></label>
                <input name='username_prefix' type='text' id='username_prefix' value='' tabindex100 
                        onfocus="javascript:toggleShowDiv('usernamePrefixTooltip')"
                        onblur="javascript:toggleShowDiv('usernamePrefixTooltip')" />
		<br/>
                <div id='usernamePrefixTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['usernamePrefixTooltip'] ?>
                </div>
		</li>

		<li class='fieldset'>
                <label for='numberInstances' class='form'><?php 
			echo $l['all']['NumberInstances'] ?></label>
                <input class="integer" name='number' type='text' id='number' value='1' tabindex=101
                        onfocus="javascript:toggleShowDiv('instancesToCreateTooltip')"
                        onblur="javascript:toggleShowDiv('instancesToCreateTooltip')" />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('number','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('number','decrement')"/>
		<br/>
                <div id='instancesToCreateTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['instancesToCreateTooltip'] ?>
                </div>
		<li>


		<li class='fieldset'>
                <label for='usernameLength' class='form'><?php 
			echo $l['all']['UsernameLength'] ?></label>
                <input class="integer" name='length_user' type='text' id='length_user' value='8' tabindex=102
                        onfocus="javascript:toggleShowDiv('lengthOfUsernameTooltip')"
                        onblur="javascript:toggleShowDiv('lengthOfUsernameTooltip')" />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('length_user','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('length_user','decrement')"/>
		<br/>
                <div id='lengthOfUsernameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['lengthOfUsernameTooltip'] ?>
                </div>
		</li>

		<li class='fieldset'>
                <label for='passwordLength' class='form'><?php 
			echo $l['all']['PasswordLength'] ?></label>

                <input class="integer" name='length_pass' type='text' id='length_pass' value='8' tabindex=103
                        onfocus="javascript:toggleShowDiv('lengthOfUsernameTooltip')"
                        onblur="javascript:toggleShowDiv('lengthOfUsernameTooltip')" />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('length_pass','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('length_pass','decrement')"/>
		<br/>
                <div id='lengthOfPasswordTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['lengthOfPasswordTooltip'] ?>
                </div>
		</li>

		<li class='fieldset'>
                <label for='group' class='form'><?php echo $l['all']['Group']?></label>
		<?php
		        include 'include/management/populate_selectbox.php';
		        populate_groups("Select Groups","group");
		?>
                <div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['groupTooltip'] ?>
                </div>
		</li>

		<li class='fieldset'>
                <label for='groupPriority' class='form'><?php echo $l['all']['GroupPriority'] ?></label>
                <input class="integer" name='group_priority' type='text' id='group_priority' value='0' tabindex=105 />
		<img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('group_priority','increment')" />
		<img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('group_priority','decrement')"/>
		</li>

		<li class='fieldset'>
		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?> " tabindex=1000 
			class='button' />
		</li>
		</ul>
	</fieldset>


     </div>
     <div class="tabbertab" title="<?php echo $l['title']['Attributes']; ?>">

        <fieldset>

                <h302> <?php echo $l['title']['Attributes']; ?> </h302>
                <br/>

                <label for='vendor' class='form'>Vendor:</label>
                <select id='dictVendors0' onchange="getAttributesList(this,'dictAttributesDatabase')"
                        style='width: 215px' class='form' >
                        <option value=''>Select Vendor...</option>
                        <?php
                                include 'library/opendb.php';

                                $sql = "SELECT distinct(Vendor) as Vendor FROM dictionary WHERE Vendor>'' ".
					" ORDER BY Vendor ASC";
                                $res = $dbSocket->query($sql);

                                while($row = $res->fetchRow()) {
                                        echo "<option value=$row[0]>$row[0]</option>";
                                }

                                include 'library/closedb.php';
                        ?>
                </select>
                <input type='button' name='reloadAttributes' value='Reload Vendors'
                        onclick="javascript:getVendorsList('dictVendors0');" class='button'>
                <br/>

                <label for='attribute' class='form'>
                        Attribute:</label>
                <select id='dictAttributesDatabase' style='width: 270px' class='form' >
                </select>
                <input type='button' name='addAttributes' value='Add Attribute'
                        onclick="javascript:parseAttribute(1);" class='button'>
                <br/>

                <label for='attribute' class='form'>
                        Custom Attribute:</label>
                <input type='text' id='dictAttributesCustom' style='width: 264px' />
                <br/>


<?php

        include_once('library/config_read.php');

        if ( (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE'])) &&
                (strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") ) {

                echo "
                        <script type=\"text/javascript\" src=\"library/javascript/dhtmlSuite-common.js\"></script>
                        <script type=\"text/javascript\" src=\"library/javascript/auto-complete.js\"></script>

                        <script type=\"text/javascript\">
                                autoCom = new DHTMLSuite.autoComplete();
                                autoCom.add('dictAttributesCustom','include/management/dynamicAutocomplete.php','_large','getAjaxAutocompleteAttributes');
                        </script>
                ";
        }
?>


	<br/>
        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

	</fieldset>
	<br/>

        <input type="hidden" value="0" id="divCounter" />
        <div id="divContainer"> </div> <br/>
     </div>		

</div>



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





