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


	} 


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
<link rel="stylesheet" href="css/auto-complete.css" media="screen" type="text/css">
</head>
 
<?php   
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php
	include ("menu-mng-rad-profiles.php");
?>
		
		<div id="contentnorightbar">

				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradprofilesedit.php'] ?>
				:: <?php if (isset($profile)) { echo $profile; } ?><h144>+</h144></a></h2>
				

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradprofilesedit'] ?>
					<br/>
				</div>
				<br/>
				
                                <form name="mngradprofiles" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <input type="hidden" value="<?php echo $profile ?>" name="profile" />


<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['RADIUSCheck']; ?>">

        <fieldset>

                <h302> <?php echo $l['title']['RADIUSCheck']?> </h302>
                <br/>

		<ul>
<?php


        include 'library/opendb.php';

        $editCounter = 0;

        $sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
		" WHERE GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($numrows = $res->numRows() == 0) {  
                echo "<center>";
                echo $l['messages']['noCheckAttributesForGroup'];
                echo "</center>";
        }

        while($row = $res->fetchRow()) {

                echo "<label class='attributes'>";
                echo "<a class='tablenovisit' href='mng-rad-groupcheck-del?groupname=$profile&attribute=$row[0]&value=$row[2]'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

                        if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
                                echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
                                echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
                                echo "&nbsp;";
                                echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
                                echo "<option value='$row[1]'>$row[1]</option>";
                                drawOptions();
                                echo "</select>";
                        } else {
                                echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
                                echo "&nbsp;";
                                echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
                                echo "<option value='$row[1]'>$row[1]</option>";
                                drawOptions();
                                echo "</select>";
                        }

                echo "  
                        <input type='hidden' name='editValues".$editCounter."[]' value='radgroupcheck' style='width: 90px'>
                ";
                echo "<br/>";

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes


        }

?>

        <br/><br/>
        <hr><br/>
        <br/>
        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply']?>' class='button' />

	</ul>

        </fieldset>
        </div>

        <div class='tabbertab' title='<?php echo $l['title']['RADIUSReply']?>' >

        <fieldset>

                <h302> <?php echo $l['title']['RADIUSReply']?> </h302>
                <br/>

		<ul>

<?php

        $sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
		" WHERE GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($numrows = $res->numRows() == 0) {
                echo "<center>";
                echo $l['messages']['noReplyAttributesForGroup'];
                echo "</center>";
        }

        while($row = $res->fetchRow()) {


                echo "<label class='attributes'>";
                echo "<a class='tablenovisit' href='mng-rad-groupreply-del?groupname=$profile&attribute=$row[0]&value=$row[2]'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

                if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
                        echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
                        echo "&nbsp;";
                        echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
                        echo "<option value='$row[1]'>$row[1]</option>";
                        drawOptions();
                        echo "</select>";
                } else {
                        echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
                        echo "&nbsp;";
                        echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
                        echo "<option value='$row[1]'>$row[1]</option>";
                        drawOptions();
                        echo "</select>";
                }

                echo "       
                        <input type='hidden' name='editValues".$editCounter."[]' value='radgroupreply' style='width: 90px'>
                ";
		echo "<br/>";

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes


        }

?>


        <br/><br/>
        <hr><br/>
        <br/>
        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply']?>' class='button' />
        <br/>

	</ul>

        </fieldset>
	</div>

<?php   
        include 'library/closedb.php';
?>  



     <div class="tabbertab" title="<?php echo $l['title']['Attributes']; ?>">
        <?php
                include_once('include/management/attributes.php');
        ?>
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
