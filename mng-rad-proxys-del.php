<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        isset($_REQUEST['proxyname']) ? $proxynameArray = $_REQUEST['proxyname'] : $proxynameArray = "";

	$logAction = "";
	$logDebugSQL = "";

        if (isset($_REQUEST['proxyname'])) {

                if (!is_array($proxynameArray))
                        $proxynameArray = array($proxynameArray, NULL);

		$allProxys = "";

		include 'library/opendb.php';

                if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
                        $filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
                        $fileFlag = 1;
                } else {
                        $filenameRealmsProxys = "";
                        $fileFlag = 0;
                }

                foreach ($proxynameArray as $variable=>$value) {
			if (trim($value) != "") {

                                $proxyname = $value;
                                $allProxys .= $proxyname . ", ";

				// delete all proxys
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." WHERE proxyname='".$dbSocket->escapeSimple($proxyname)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$successMsg = "Deleted proxy(s): <b> $allProxys </b>";
				$logAction .= "Successfully deleted proxy(s) [$allProxys] on page: ";
				
			} else { 
				$failureMsg = "no proxy was entered, please specify a proxy name to remove from database";
				$logAction .= "Failed deleting proxy(s) [$allProxys] on page: ";
			}

		} //foreach

               /*******************************************************************/
               /* enumerate from database all proxy entries */
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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradproxysdel.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradproxysdel'] ?>
					<br/>
				</div>
                <?php   
                        include_once('include/management/actionMessages.php');
                ?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo $l['title']['ProxyInfo'] ?> </h302>
		<br/>

                <label for='proxyname' class='form'><?php echo $l['all']['ProxyName'] ?></label>
                <input name='proxyname[]' type='text' id='proxyname' value='<?php echo $proxyname ?>' tabindex=100 />
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





