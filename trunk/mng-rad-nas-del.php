<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


        isset($_REQUEST['nashost']) ? $nashost = $_REQUEST['nashost'] : $nashost = "";

	$logDebugSQL = "";

        if (isset($_REQUEST['nashost'])) {

		$allNASs = "";

                /* since the foreach loop will report an error/notice of undefined variable $value because
                   it is possible that the $nashost is not an array, but rather a simple GET request
                   with just some value, in this case we check if it's not an array and convert it to one with
                   a NULL 2nd element
                */

                if (!is_array($nashost))
                        $nashost = array($nashost, NULL);

                foreach ($nashost as $variable=>$value) {

                        if (trim($variable) != "") {

				include 'library/opendb.php';

                                $nashost = $value;
                                $allNASs .= $nashost . ", ";
                                //echo "nas: $nashost <br/>";


				// delete all attributes associated with a username
				$sql = "DELETE FROM nas WHERE nasname='".$dbSocket->escapeSimple($nashost)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted all NASs from database: <b> $allNASs </b>";
				$logAction = "Successfully deleted nas(s) [$allNASs] on page: ";
					
				include 'library/closedb.php';
	
			}  else {
				$actionStatus = "failure";
				$actionMsg = "No nas ip/host was entered, please specify a nas ip/host to remove from database";
				$logAction = "Failed deleting empty nas on page: ";
			} //if trim

		} //foreach

	} 

	include_once('library/config_read.php');
    $log = "visited page: ";

	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradnasdel.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradnasdel'] ?>
					<br/>
				</div>
				<br/>


                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['NASInfo']; ?> </th>
                                                        </tr>
                                        </thead>

<tr><td>
                                                <?php if (trim($nashost) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasIPHost'] ?></b>
</td><td>												
                                                <input value="<?php echo $nashost ?>" name="nashost[]" tabindex=100 /><br/>
                                                </font>
</td></tr>
</table>
                                                <br/><br/>
<center>
                                                <input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=10000 />
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
