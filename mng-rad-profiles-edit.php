<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$profile = $_REQUEST['profile'];
	$logAction = "";
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

                                if (isset($field[0])) {
                                        if (preg_match('/__/', $field[0]))
                                                list($columnId, $attribute) = explode("__", $field[0]);
                                        else {
                                                $columnId = 0;                          // we need to set a non-existent column id so that the attribute would
                                                                                        // not match in the database (as it is added from the Attributes tab)
                                                                                        // and the if/else check will result in an INSERT instead of an UPDATE for the
                                                                                        // the last attribute
                                                $attribute = $field[0];
                                        }
                                }

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

				$sql = "SELECT Attribute FROM $table WHERE GroupName='".$dbSocket->escapeSimple($profile).
						"' AND Attribute='".$dbSocket->escapeSimple($attribute)."' AND id=".$dbSocket->escapeSimple($columnId);
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				if ($res->numRows() == 0) {

					/* if the returned rows equal 0 meaning this attribute is not found and we need to add it */
					$sql = "INSERT INTO $table (id,GroupName,Attribute,op,Value) ".
							" VALUES (0,'".$dbSocket->escapeSimple($profile)."', '".
							$dbSocket->escapeSimple($attribute)."','".$dbSocket->escapeSimple($op)."', '$value')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

				} else {

					/* we update the $value[0] entry which is the attribute's value */
					$sql = "UPDATE $table SET Value='$value' WHERE GroupName='".
							$dbSocket->escapeSimple($profile)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'".
							" AND id=".$dbSocket->escapeSimple($columnId);
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					/* then we update $value[1] which is the attribute's operator */
					$sql = "UPDATE $table SET Op='".$dbSocket->escapeSimple($op).
							"' WHERE GroupName='".$dbSocket->escapeSimple($profile)."' AND Attribute='".
							$dbSocket->escapeSimple($attribute)."' AND id=".$dbSocket->escapeSimple($columnId);
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

				}

			} //foreach $_POST

			$successMsg = "Updated attributes for: <b> $profile </b>";
			$logAction .= "Successfully updates attributes for profile [$profile] on page:";

		include 'library/closedb.php';

		} else { // $profile is empty

			$failureMsg = "profile name is empty";
			$logAction .= "Failed adding (possibly empty) profile name [$profile] on page: ";

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
</head>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php
	include ("menu-mng-rad-profiles.php");
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradprofilesedit.php') ?>
		:: <?php if (isset($profile)) { echo $profile; } ?><h144>&#x2754;</h144></a></h2>


		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradprofilesedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form name="mngradprofiles" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

			<input type="hidden" value="<?php echo $profile ?>" name="profile" />


<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','RADIUSCheck'); ?>">

        <fieldset>

                <h302> <?php echo t('title','RADIUSCheck')?> </h302>
                <br/>

		<ul>
<?php


        include 'library/opendb.php';
        include 'include/management/pages_common.php';

        $editCounter = 0;

        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".Attribute, ".
                $configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".op, ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".Value, ".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip, ".
                $configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".id ".
                " FROM ".
                $configValues['CONFIG_DB_TBL_RADGROUPCHECK']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
                " ON ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".Attribute=".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute ".
		" AND ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Value IS NULL ".
		" WHERE ".
                $configValues['CONFIG_DB_TBL_RADGROUPCHECK'].".GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($numrows = $res->numRows() == 0) {
			echo "<center>";
			echo t('messages','noCheckAttributesForGroup');
			echo "</center>";
        }

        while($row = $res->fetchRow()) {

                echo "<label class='attributes'>";
                echo "<a class='tablenovisit' href='mng-rad-profiles-del.php?profile=$profile&attribute=$row[5]__$row[0]&tablename=radgroupcheck'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[5]__$row[0]' />";

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

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes

                if (!$row[3])
                        $row[3] = "unavailable";
                if (!$row[4])
                        $row[4] = "unavailable";

                printq("
                        <img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
                        <br/>
                        <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i><b>Type:</b> $row[3]</i><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i><b>Tooltip Description:</b> $row[4]</i><br/>
                                <br/>
                        </div>
                ");

	}
?>

        <br/><br/>
        <hr><br/>
        <br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply')?>' class='button' />

	</ul>

        </fieldset>
        </div>

        <div class='tabbertab' title='<?php echo t('title','RADIUSReply')?>' >

        <fieldset>

                <h302> <?php echo t('title','RADIUSReply')?> </h302>
                <br/>

		<ul>

<?php

        $sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".Attribute, ".
                $configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".op, ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".Value, ".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip, ".
                $configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".id ".
                " FROM ".
                $configValues['CONFIG_DB_TBL_RADGROUPREPLY']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
                " ON ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".Attribute=".
                $configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute ".
		" AND ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Value IS NULL ".
		" WHERE ".
                $configValues['CONFIG_DB_TBL_RADGROUPREPLY'].".GroupName='".$dbSocket->escapeSimple($profile)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($numrows = $res->numRows() == 0) {
                echo "<center>";
                echo t('messages','noReplyAttributesForGroup');
                echo "</center>";
        }

        while($row = $res->fetchRow()) {


                echo "<label class='attributes'>";
                echo "<a class='tablenovisit' href='mng-rad-profiles-del.php?profile=$profile&attribute=$row[5]__$row[0]&tablename=radgroupreply'>
                                <img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

                echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[5]__$row[0]' />";

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

                $editCounter++;                 // we increment the counter for the html elements of the edit attributes

                if (!$row[3])
                        $row[3] = "unavailable";
                if (!$row[4])
                        $row[4] = "unavailable";

                printq("
                        <img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
                        <br/>
                        <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i><b>Type:</b> $row[3]</i><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i><b>Tooltip Description:</b> $row[4]</i><br/>
                                <br/>
                        </div>
                ");

        }

?>


        <br/><br/>
        <hr><br/>
        <br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply')?>' class='button' />
        <br/>

	</ul>

        </fieldset>
	</div>

<?php
	include 'library/closedb.php';
?>



     <div class="tabbertab" title="<?php echo t('title','Attributes'); ?>">
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
