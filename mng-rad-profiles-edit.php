<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        $profile = $_REQUEST['profile'];
	$logDebugSQL = "";

        if (isset($_REQUEST['submit'])) {

                $profile = $_REQUEST['profile'];

			include 'library/opendb.php';
		
		if ($profile != "") {

		
                        foreach( $_POST as $element=>$field ) { 


                                        switch ($element) {

                                                case "submit":
                                                case "profile":
                                                        $skipLoopFlag = 1; 
                                                        break;
                                        }
                                
                                        if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;             
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

                                        if ($table == 'check')
                                                $table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
                                        if ($table == 'reply')
                                                $table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];

                                        if (!($value))
                                                continue;

					$value = $dbSocket->escapeSimple($value);

  $sql = "SELECT Attribute FROM $table WHERE GroupName='".$dbSocket->escapeSimple($profile)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= $sql . "\n";
                                if ($res->numRows() == 0) {

                                        /* if the returned rows equal 0 meaning this attribute is not found and we need to add it */

                                        $sql = "INSERT INTO $table values(0,'".$dbSocket->escapeSimple($profile)."', '".$dbSocket->escapeSimple($attribute)."','".$dbSocket->escapeSimple($op)."', '$value')";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

                                } else {

                                        /* we update the $value[0] entry which is the attribute's value */
                                        $sql = "UPDATE $table SET Value='$value' WHERE GroupName='".$dbSocket->escapeSimple($profile)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";


                                        /* then we update $value[1] which is the attribute's operator */
                                        $sql = "UPDATE $table SET Op='".$dbSocket->escapeSimple($op)."' WHERE GroupName='".$dbSocket->escapeSimple($profile)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

                                }

                } //foreach $_POST

                        $actionStatus = "success";
                        $actionMsg = "Updated attributes for: <b> $profile </b>";
                        $logAction = "Successfully updates attributes for profile [$profile] on page:";

		include 'library/closedb.php';


		} else { // $profile is empty

                                $actionStatus = "failure";
                                $actionMsg = "profile name is empty";
                                $logAction = "Failed adding (possibly empty) profile name [$profile] on page: ";

		}


	} //if isset($submit)

/*

	// declaring variables
	$username = "";
	$group = "";
	$groupOld = "";
	$priority = "";

	$username = $_REQUEST['username'];
	$groupOld = $_REQUEST['group'];

	$logDebugSQL = "";

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";
	$row = $res->fetchRow();		// array fetched with values from $sql query

					// assignment of values from query to local variables
					// to be later used in html to display on textboxes (input)
					
	$priority = $row[2];

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$groupOld = $_REQUEST['groupOld'];;
		$group = $_REQUEST['group'];;
		$priority = $_REQUEST['priority'];;

		
		include 'library/opendb.php';

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($username) != "" and trim($group) != "") {

				if (!$priority) {
					$priority = 1;
				}

				// insert nas details
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." SET GroupName='".$dbSocket->escapeSimple($group)."',
priority='".$dbSocket->escapeSimple($priority)."' WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
						
				$actionStatus = "success";
				$actionMsg = "Updated User-Group mapping in database: User<b> $username </b> and Group: <b> $group </b> ";
				$logAction = "Successfully updated attributes for user-group mapping of user [$username] with group [$group] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "no username or groupname was entered, it is required that you specify both username and groupname";
				$logAction = "Failed updating (missing attributes) attributes on page: ";
			}
		} else {
			$actionStatus = "failure";
			$actionMsg = "The user $username already exists in the user-group mapping database
			<br/> It seems that you have duplicate entries for User-Group mapping. Check your database";
			$logAction = "Failed updating already existing user [$username] with group [$group] on page: ";
		} 

		include 'library/closedb.php';
	}
	
	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];
	else
		$username = "";

	if (isset($_REQUEST['group']))
		$group = $_REQUEST['group'];
	else
		$group = "";
		
	if (trim($username) == "" OR trim($group) == "") {
		$actionStatus = "failure";
		$actionMsg = "no username or groupname was entered, please specify a username and groupname to edit ";
	}	
*/


	include_once('library/config_read.php');
    $log = "visited page: ";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<?php   
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php
	include ("menu-mng-rad-profiles.php");
?>
		
		<div id="contentnorightbar">

				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradprofilesedit.php'] ?> </a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradprofilesedit'] ?>
					<br/>
				</div>
				<br/>
				
                                <form name="mngradprofiles" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <input type="hidden" value="<?php echo $profile ?>" name="profile" />


<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['RADIUSCheck']; ?>">

<?php

                echo "<table border='0' class='table1'>";
                echo "  
                        <thead> 
                                <tr>
                                <th colspan='10'>".$l['table']['RADIUSCheck']."</th>
                                </tr>
                        </thead>
                ";

        include 'library/opendb.php';
        include ('include/management/op_select_options.php');

        $editCounter = 0;

        $sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";
        while($row = $res->fetchRow()) {

                echo "<tr>";
                echo "<td>";
                echo "<a class='tablenovisit' href='mng-rad-profiles-del?profile=$profile&attribute=$row[0]&tablename=radgroupcheck'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
                echo "<b>$row[0]</b>";
                echo "</td>";

                echo "<td>";
                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

//              if (preg_match("/.*-Password/", $row[0])) {
                        if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
                                echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
                                echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
                                echo "&nbsp;";
                                echo "<select name='editValues".$editCounter."[]' style='width: 45px'>";
                                echo "<option value='$row[1]'>$row[1]</option>";
                                drawOptions();
                                echo "</select>";
                        } else {
                                echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
                                echo "&nbsp;";
                                echo "<select name='editValues".$editCounter."[]' style='width: 45px'>";
                                echo "<option value='$row[1]'>$row[1]</option>";
                                drawOptions();
                                echo "</select>";
                        }
//              }

                echo "  
                        <input type='hidden' name='editValues".$editCounter."[]' value='radgroupcheck' style='width: 90px'>
                ";

                echo "</td>";
                echo "</tr>";

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes


        }


                echo "</table>";
                echo "</div>";

                echo "<div class='tabbertab' title='".$l['table']['RADIUSReply']."'>";

                echo "<table border='0' class='table1'>";
                echo "  
                        <thead>
                                <tr>
                                <th colspan='10'>".$l['table']['RADIUSReply']."</th>
                                </tr>
                        </thead>
                ";



        $sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE 
GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        while($row = $res->fetchRow()) {

                echo "<tr>";
                echo "<td>";
                echo "<a class='tablenovisit' href='mng-rad-profiles-del?profile=$profile&attribute=$row[0]&tablename=radgroupreply'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
                echo "<b>$row[0]</b>";
                echo "</td>";

                echo "<td>";
                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

                if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
                        echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
                        echo "&nbsp;";
                        echo "<select name='editValues".$editCounter."[]' style='width: 45px'>";
                        echo "<option value='$row[1]'>$row[1]</option>";
                        drawOptions();
                        echo "</select>";
                } else {
                        echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
                        echo "&nbsp;";
                        echo "<select name='editValues".$editCounter."[]' style='width: 45px'>";
                        echo "<option value='$row[1]'>$row[1]</option>";
                        drawOptions();
                        echo "</select>";
                }

                echo "       
                        <input type='hidden' name='editValues".$editCounter."[]' value='radgroupreply' style='width: 90px'>
                ";

                echo "</td>";
                echo "</tr>";

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes


        }


                echo "</table>";
                echo "</div>";
		echo "<br/>";
        include 'library/closedb.php';

?>


     <div class="tabbertab" title="<?php echo $l['table']['Attributes']; ?>">

<table border='0' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='10'> <?php echo $l['table']['Attributes']; ?> </th>
                                                        </tr>
                                        </thead>
        <tr>
                <td>Vendor:
                <select id='dictVendors0' onchange="getAttributesList(this,'dictAttributes0')"
                        style='width: 215px' onclick="getVendorsList('dictVendors0')" >
                        <option value=''>Select Vendor...</option>
                        <option value='other'>other</option>
                </select>

                &nbsp;&nbsp;
                Attribute:
                <select id='dictAttributes0' name='dictValues0[]' onchange="getValuesList(this,'dictValues0','dictOP0','dictTable0','dictTooltip0','dictType0')" style='width: 270px'>

                </select>
                </td>
        </tr>
        <tr>
                <td>
                &nbsp;
                Value:
                <input type='text' id='dictValues0' name='dictValues0[]' style='width: 115px'>

                &nbsp;
                Op:
                <select id='dictOP0' name='dictValues0[]' style='width: 45px'>

                </select>

                &nbsp;
                Table:
                <select id='dictTable0' name='dictValues0[]' style='width: 90px'>

                </select>



                &nbsp;
                Function:
                <select id='dictFunc' name='dictFunc'>

                </select>
                </td>

        </tr>

        <tr>
                <td>
                <div id='dictInfo0' style='display:inline;visibility:visible'>
                        <span id='dictTooltip0'>
                                <b>Attribute Tooltip:</b>
                        </span>
                        <br/>

                        <span id='dictType0'>
                                <b>Type:<b/>
                        </span>
                </div>
                </td>
        </tr>


        <td>
        <a href="javascript:;" onclick="addElement();">Add</a>
        <a href="javascript:;" onclick="toggleShowDiv('dictInfo0');">Help</a>
        </td>

</table>
<br/>
        <input type="hidden" value="0" id="divCounter" />
        <div id="divContainer"> </div> 

        <br/>
     </div>

</div>





<center>
                                                <input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>"/>
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
