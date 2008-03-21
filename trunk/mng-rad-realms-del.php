<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        isset($_REQUEST['realmname']) ? $realmnameArray = $_REQUEST['realmname'] : $realmnameArray = "";

	$logAction = "";
	$logDebugSQL = "";

        if (isset($_REQUEST['realmname'])) {

                if (!is_array($realmnameArray))
                        $realmnameArray = array($realmnameArray, NULL);

		$allRealms = "";

		include 'library/opendb.php';

                $filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
                $fileFlag = 1;
	
                foreach ($realmnameArray as $variable=>$value) {
			if (trim($value) != "") {

                                $realmname = $value;
                                $allRealms .= $realmname . ", ";

				// delete all realms
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." WHERE realmname='".
					$dbSocket->escapeSimple($realmname)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$successMsg = "Deleted realm(s): <b> $allRealms </b>";
				$logAction .= "Successfully deleted realm(s) [$allRealms] on page: ";
				
			} else { 
				$failureMsg = "no realm was entered, please specify a realm name to remove from database";
				$logAction .= "Failed deleting realm(s) [$allRealms] on page: ";
			}

		} //foreach

               /*******************************************************************/
               /* enumerate from database all realm entries */
               include_once('include/management/saveRealmsProxys.php');
               /*******************************************************************/

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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php

	include ("menu-mng-rad-realms.php");
	
?>		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradrealmsdel.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradrealmsdel'] ?>
					<br/>
				</div>
                <?php   
                        include_once('include/common/actionMessages.php');
                ?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo $l['title']['RealmInfo'] ?> </h302>
		<br/>

                <label for='realmname' class='form'><?php echo $l['all']['RealmName'] ?></label>
                <input name='realmname[]' type='text' id='realmname' value='<?php echo $realmname ?>' tabindex=100 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=1000 
			class='button' />

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





