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

	isset($_POST['operator_username']) ? $operator_username = $_POST['operator_username'] : $operator_username = "";
	
	$logAction = "";
	$logDebugSQL = "";

	if ($operator_username != "") {

                if (!is_array($operator_username))
                        $operator_username = array($operator_username, NULL);
                $allOperators = "";

		include 'library/opendb.php';

                foreach ($operator_username as $variable=>$value) {
                        if (trim($value) != "") {

                                $username = $value;
                                $allOperators .= $username . ", ";

				// delete operator from database
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." where Username='$username'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
	
				$successMsg = "Deleted operator(s): <b> $allOperators";
				$logAction .= "Successfully deleted operator(s) [$allOperators] on page: ";
	
			}  else { 
				$failureMsg = "no operator username was entered, please specify an operator username to remove from database";		
				$logAction .= "Failed deleting operator username [$allOperators] on page: ";
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

</head>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configoperatorsdel.php'] ?>
				<h144>+</h144></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['configoperatorsdel'] ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


        <fieldset>

                <h302>Operator Account Removal</h302>
                <br/>

                <label for='username' class='form'>Operator Username</label>
                <input name='username' type='text' id='username'
                        value='<?php if (isset($username)) echo $username ?>' tabindex=100 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

	</fieldset>

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





