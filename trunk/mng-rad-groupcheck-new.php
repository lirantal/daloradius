<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	// declaring variables
	$groupname = "";

	$logDebugSQL = "";

    if (isset($_POST['submit'])) {
	    

        include 'library/opendb.php';
        include 'include/management/attributes.php';                            // required for checking if an attribute belongs to the

            $groupname = $_REQUEST['groupname'];

        if ($groupname) {

                $counter = 0;
                foreach ($_POST as $attribute=>$value) {

                                        // switch case to rise the flag for several $attribute which we do not
                                        // wish to process (ie: do any sql related stuff in the db)
                                        switch ($attribute) {

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



                        if ($value[0] == "")                            // we don't process attribtues with no values
                                        continue;

                        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='
".$dbSocket->escapeSimple($groupname)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= $sql . "\n";


                        if ($res->numRows() == 0) {
                                        // insert usergroup details
                                        // assuming there's no groupname with that attribute in the table
                                        $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." values (0,'
".$dbSocket->escapeSimple($groupname)."', '".$dbSocket->escapeSimple($attribute)."', '".$dbSocket->escapeSimple($value[1])."',
'".$dbSocket->escapeSimple($value[0])."')";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";
                                        $counter++;

                                        $actionStatus = "success";
                                        $actionMsg = "Added to database new group: <b> $groupname </b> with attribute: <b> $attribute </b> and value: <b> $value[0] </b>";
                                        $logAction = "Successfully added group [$groupname] with attribute: <b> $attribute </b> and value: <b> $value[0] </b> on page: ";
                        } else {
                                $actionStatus = "failure";
                                $actionMsg = "The group <b> $groupname </b> already exists in the database with attribute <b> $attribute </b>";
                                $logAction = "Failed adding already existing group [$groupname] with attribute [$attribute] on page: ";

                        } // end else if mysql



                    }

                } else { // if groupname isset
                        $actionStatus = "failure";
                        $actionMsg = "No groupname was defined";
                        $logAction = "Failed adding missing values for groupname on page: ";
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

</head>
 
<?php
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradgroupchecknew.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradgroupchecknew'] ?>
					<br/>
				</div>
				<br/>
				
                                <form name="newgroupcheck" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['GroupInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l['FormField']['all']['Groupname'] ?></b>
</td><td>												
                                                <input value="<?php echo $groupname ?>" name="groupname"/>
                                                </font><br/>
</td></tr>
</table>

                                                <br/><br/>
<center>
                                                <input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>"/>
</center>


<br/><br/>
<?php
        include_once('include/management/attributes.php');
        drawAttributes();
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