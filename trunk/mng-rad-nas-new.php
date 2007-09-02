<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');



	// declaring variables
	$nashost = "";
	$nassecret = "";
	$nasname = "";
	$nasports = "";
	$nastype = "";
	$nasdescription = "";
	$nascommunity = "";

	if (isset($_POST['submit'])) {
	
		$nashost = $_REQUEST['nashost'];
		$nassecret = $_REQUEST['nassecret'];;
		$nasname = $_REQUEST['nasname'];;
		$nasports = $_REQUEST['nasports'];;
		$nastype = $_REQUEST['nastype'];;
		$nasdescription = $_REQUEST['nasdescription'];;
		$nascommunity = $_REQUEST['nascommunity'];;

		
		include 'library/opendb.php';

		$sql = "SELECT * FROM nas WHERE nasname='$nashost'";
		$res = $dbSocket->query($sql);

		if ($res->numRows() == 0) {

			if (trim($nashost) != "" and trim($nassecret) != "") {

				if (!$nasports) {
					$nasports = 0;
				}
				
				// insert nas details
				$sql = "INSERT INTO nas values (0, '$nashost', '$nasname', '$nastype', $nasports, '$nassecret', '$nascommunity', '$nasdescription')";
				$res = $dbSocket->query($sql);
			
				$actionStatus = "success";
				$actionMsg = "Added new NAS to database: <b> $nashost </b>  ";
				$logAction = "Successfully added nas [$nashost] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
				$logAction = "Failed adding (missing nas/secret) nas [$nashost] on page: ";
			}
		} else {
			$actionStatus = "failure";
			$actionMsg = "The NAS IP/Host $nashost already exists in the database";	
			$logAction = "Failed adding already existing nas [$nashost] on page: ";
		}

		include 'library/closedb.php';
	}
	

	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	

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
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradnasnew.php] ?></a></h2>
				
				<p>

                                <form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($nashost) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasIPHost] ?></b>
</td><td>
                                                <input value="<?php echo $nashost ?>" name="nashost"/>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($nassecret) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][mngradnasnew.php][NasSecret] ?></b>
</td><td>											
                                                <input value="<?php echo $nassecret ?>" name="nassecret" /> 
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($nasname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasShortname] ?></b> 
</td><td>												
                                                <input value="<?php echo $nasname ?>" name="nasname" /> 
												<?php echo $l[FormField][mngradnasnew.php][ToolTip][NasShortname] ?>
                                                </font><br/>
</td></tr>
</table>

        <br/>
		<center>
        <h4> Advnaced NAS Attributes </h4>
		</center>

<table border='2' class='table1' width='600'>
<tr><td>
                                                <?php if (trim($nastype) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesNasType')">
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasType] ?></b>
</td><td>												
<div id="attributesNasType" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nastype ?>" name="nastype" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>




                                                <?php if (trim($nasports) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesPorts')">
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasPorts] ?></b> 
</td><td>												
<div id="attributesPorts" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasports ?>" name="nasports" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>



                                                <?php if (trim($nascommunity) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesCommunity')">
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasCommunity] ?></b> 
</td><td>												
<div id="attributesCommunity" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nascommunity ?>" name="nascommunity" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>




                                                <?php if (trim($nasdescription) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesDescription')">
                                                <b><?php echo $l[FormField][mngradnasnew.php][NasDescription] ?></b> 
</td><td>
<div id="attributesDescription" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasdescription ?>" name="nasdescription" />
                                                </font>
</div><br/>
</td></tr>
</table>
                                                <br/><br/>
<center>												
                                                <input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
</center>

                                </form>


				</p>
				
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
