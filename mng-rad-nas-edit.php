<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');



    include 'library/opendb.php';

	$nashost = "";
	$nassecret = "";
	$nasname = "";
	$nasports = "";
	$nastype = "";
	$nasdescription = "";
	$nascommunity = "";

	isset($_REQUEST['nashost']) ? $nashost = $_REQUEST['nashost'] : $nashost = "";

	$logDebugSQL = "";

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM nas WHERE nasname='".$dbSocket->escapeSimple($nashost)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();		// array fetched with values from $sql query

					// assignment of values from query to local variables
					// to be later used in html to display on textboxes (input)
	$nassecret = $row[5];
	$nasname = $row[2];
	$nasports = $row[4];
	$nastype = $row[3];
	$nascommunity = $row[6];
	$nasdescription = $row[7];

	if (isset($_POST['submit'])) {
	
		$nashostold = $_REQUEST['nashostold'];
		$nashost = $_REQUEST['nashost'];
		$nassecret = $_REQUEST['nassecret'];;
		$nasname = $_REQUEST['nasname'];;
		$nasports = $_REQUEST['nasports'];;
		$nastype = $_REQUEST['nastype'];;
		$nasdescription = $_REQUEST['nasdescription'];;
		$nascommunity = $_REQUEST['nascommunity'];;

			
		include 'library/opendb.php';

		$sql = "SELECT * FROM nas WHERE nasname='".$dbSocket->escapeSimple($nashostold)."' ";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($nashost) != "" and trim($nassecret) != "") {

				if (!$nasports) {
					$nasports = 0;
				}

				// insert nas details
				$sql = "UPDATE nas SET nasname='".$dbSocket->escapeSimple($nashost)."', shortname='".$dbSocket->escapeSimple($nasname)."',
type='".$dbSocket->escapeSimple($nastype)."', ports=".$dbSocket->escapeSimple($nasports).", secret='".$dbSocket->escapeSimple($nassecret)."',
community='".$dbSocket->escapeSimple($nascommunity)."', description='".$dbSocket->escapeSimple($nasdescription)."'
WHERE nasname='".$dbSocket->escapeSimple($nashostold)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Updated NAS settings in database: <b> $nashost </b>  ";
				$logAction = "Successfully updated attributes for nas [$nashost] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
				$logAction = "Failed updating attributes for nas [$nashost] on page: ";
			}
			
		} elseif ($res->numRows() > 1) {
			$actionStatus = "failure";
			$actionMsg = "The NAS IP/Host <b> $nashost </b> already exists in the database
			<br/> Please check that there are no duplicate entries in the database";
			$logAction = "Failed updating attributes for already existing nas [$nashost] on page: ";
		} else {
			$actionStatus = "failure";
			$actionMsg = "The NAS IP/Host <b> $nashost </b> doesn't exist at all in the database.
			<br/>Please re-check the nashost ou specified.";
			$logAction = "Failed updating empty nas on page: ";
		}

		include 'library/closedb.php';
	}

	if (isset($_REQUEST['nashost']))
		$nashost = $_REQUEST['nashost'];
	else
		$nashost = "";

	if (trim($nashost) == "") {
		$actionStatus = "failure";
		$actionMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
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
        include_once ("library/tabber/tab-layout.php");
?>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradnasedit.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradnasedit'] ?>
					<br/>
				</div>
				<br/>


                                <form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['NASInfo']; ?>">
                                                <input type="hidden" value="<?php echo $nashost ?>" name="nashostold" />

<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['NASInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
                                                <?php if (trim($nashost) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasIPHost'] ?></b>
</td><td>
                                                <input value="<?php echo $nashost ?>" name="nashost" tabindex=100 />
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($nassecret) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasSecret'] ?></b>
</td><td>
                                                <input value="<?php echo $nassecret ?>" name="nassecret" tabindex=101 />
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($nastype) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasType'] ?></b>
</td><td>
                                                <input value="<?php echo $nastype ?>" name="nastype" id="nastype" tabindex=102 />

                                                <select onChange="javascript:setStringText(this.id,'nastype')" id="optionSele" tabindex=103 >
                                                <option value="other">other</option>
                                                <option value="cisco">cisco</option>
                                                <option value="livingston">livingston</option>
                                                <option value="computon">computon</option>
                                                <option value="max40xx">max40xx</option>
                                                <option value="multitech">multitech</option>
                                                <option value="natserver">natserver</option>
                                                <option value="pathras">pathras</option>
                                                <option value="patton">patton</option>
                                                <option value="portslave">portslave</option>
                                                <option value="tc">tc</option>
                                                <option value="usrhiper">usrhiper</option>
                                                </select>

                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($nasname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasShortname'] ?></b>
</td><td>
                                                <input value="<?php echo $nasname ?>" name="nasname" tabindex=104 />
                                                                                                <?php echo $l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] ?>
                                                </font><br/>
</td></tr>
</table>

     </div>
     <div class="tabbertab" title="<?php echo $l['table']['NASAdvanced']; ?>">

<table border='2' class='table1' width='600'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['NASAdvanced']; ?> </th>
                                                        </tr>
                                        </thead>

<tr><td>




                                                <?php if (trim($nasports) == "") { echo "<font color='#FF0000'>";  }?>
                        <input type="checkbox" onclick="javascript:toggleShowDiv('attributesPorts')">
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasPorts'] ?></b>
</td><td>
<div id="attributesPorts" style="display:none;visibility:visible" >
                                                <br/>
                                                <input value="<?php echo $nasports ?>" name="nasports" tabindex=105 />
                                                </font>
</div><br/>
</td></tr>
<tr><td>



                                                <?php if (trim($nascommunity) == "") { echo "<font color='#FF0000'>";  }?>
                        <input type="checkbox" onclick="javascript:toggleShowDiv('attributesCommunity')">
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasCommunity'] ?></b>
</td><td>
<div id="attributesCommunity" style="display:none;visibility:visible" >
                                                <br/>
                                                <input value="<?php echo $nascommunity ?>" name="nascommunity" tabindex=106 />
                                                </font>
</div><br/>
</td></tr>
<tr><td>




                                                <?php if (trim($nasdescription) == "") { echo "<font color='#FF0000'>";  }?>
                        <input type="checkbox" onclick="javascript:toggleShowDiv('attributesDescription')">
                                                <b><?php echo $l['FormField']['mngradnasnew.php']['NasDescription'] ?></b>
</td><td>
<div id="attributesDescription" style="display:none;visibility:visible" >
                                                <br/>
                                                <input value="<?php echo $nasdescription ?>" name="nasdescription" tabindex=107 />
                                                </font>
</div><br/>
</td></tr>
</table>

</div>


</div>

<br/><br/>
<center>												
                                                <input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=10000/>
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