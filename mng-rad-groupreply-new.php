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

	// declaring variables
	$groupname = "";

	$logAction = "";
	$logDebugSQL = "";
	$allValues = "";
	$allAttributes = "";

    if (isset($_POST['submit'])) {
	
		include 'library/opendb.php';	

		$groupname = $_REQUEST['groupname'];

		if ($groupname) {
	
			$counter = 0;
			foreach ($_POST as $element=>$field) {

				// switch case to rise the flag for several $attribute which we do not
				// wish to process (ie: do any sql related stuff in the db)
				switch ($element) {
					case "groupname":
					case "submit":
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

				if (isset($field[0]))
					$attribute = $field[0];
				if (isset($field[1]))
					$value = $field[1];
				if (isset($field[2]))
					$op = $field[2];

				// we explicitly set the table target to be radgroupreply
				$table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];

				if (!($value))
					continue;	// we don't process empty values attributes

				$allValues .= $value . "\n";
				$allAttributes .= $attribute . "\n";

				$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
						" WHERE GroupName='".$dbSocket->escapeSimple($groupname)."' AND Attribute='".
						$dbSocket->escapeSimple($attribute)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				if ($res->numRows() == 0) {
					// insert radgroupreply details
					// assuming there's no groupname with that attribute in the table
					$sql = "INSERT INTO $table (id, GroupName, Attribute, Op, Value) ".
						" VALUES (0,'".$dbSocket->escapeSimple($groupname)."','".
						$dbSocket->escapeSimple($attribute)."', '".
						$dbSocket->escapeSimple($op)."', '".
						$dbSocket->escapeSimple($value)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
					$counter++;
					
					$successMsg = "Added to database new group: <b> $groupname </b> with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b>";
					$logAction .= "Successfully added group [$groupname] with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b> on page: ";
				} else { 
					$failureMsg = "The group <b> $groupname </b> already exist in the database with attribute(s) <b> $allAttributes </b>";
					$logAction .= "Failed adding already existing group [$groupname] with attribute(s) [$allAttributes] on page: ";
		        } // end else if mysql
		    }

   		} else { // if groupname isset
			$failureMsg = "No groupname was defined";
			$logAction .= "Failed adding missing values for groupname on page: ";
   		}
        include 'library/closedb.php';	
	}


	isset($groupname) ? $groupname = $groupname : $groupname = "";

	include_once('library/config_read.php');
    $log = "visited page: ";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

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

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>

</head>
 
<?php
	include ("menu-mng-rad-groups.php");
?>

	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradgroupreplynew.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradgroupreplynew') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form name="newgroupreply" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


        <fieldset>

			<h302> <?php echo t('title','GroupInfo') ?> </h302>
			<br/>

			<label for='groupname' class='form'><?php echo t('all','Groupname') ?></label>
			<input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=100 />
			<br />

			<br/><br/>
			<hr><br/>

			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

	<br/>


<?php
	include_once('include/management/attributes.php');
?>


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
