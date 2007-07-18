<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	// declaring variables
	$groupname = "";
	$op = "";
	$attribute = "";
	$value = "";	

    if (isset($_POST['submit'])) {

	    
        include 'library/opendb.php';
		
	    $groupname = $_POST['groupname'];
	    $op = $_POST['op'];
	    $attribute = $_POST['attribute'];
		$value = $_POST['value'];

		$counter = 0;
		foreach ($groupname as $group) {

			if ($group == "")
					continue;
					
			$sql = "SELECT * FROM radgroupcheck WHERE GroupName='$group' AND Value='$value[$counter]'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				
			if (mysql_num_rows($res) == 0) {
				if (trim($group) != "" and trim($value[$counter]) != "" and trim($op[$counter]) != "" and trim($attribute[$counter]) != "") {								
					// insert usergroup details
					$sql = "INSERT INTO radgroupcheck values (0,'$group', '$attribute[$counter]', '$op[$counter]', '$value[$counter]')";
					$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
					$counter++;
				}
			} else {
                    echo "<font color='#FF0000'>error: the group [$groupname[$counter]] already exist in the database with value [$value[$counter]] <br/></font>";
					echo "
                        <script language='JavaScript'>
                        <!--
                        alert('The group $groupname[$counter] already exists in the database with value $value[$counter]');
                        -->
                        </script>
                    ";
            }
				
		}
		
		
		
	    $groupnameExtra = $_POST['groupnameExtra'];
	    $opExtra = $_POST['opExtra'];
	    $attributeExtra = $_POST['attributeExtra'];
		$valueExtra = $_POST['valueExtra'];
		
		if ($groupnameExtra) {
		
			$counter = 0;
		
			foreach ($groupnameExtra as $groupExtra) {
			
				if ($groupExtra == "")
					continue; 

				// echo "$group $attribute[$counter] $op[$counter] $value[$counter] <br/> "; 	// for debugging purposes
					
				$sql = "SELECT * FROM radgroupcheck WHERE GroupName='$groupExtra' AND Value='$valueExtra[$counter]'";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
					
				if (mysql_num_rows($res) == 0) {
					if (trim($groupExtra) != "" and trim($valueExtra[$counter]) != "" and trim($opExtra[$counter]) != "" and trim($attributeExtra[$counter]) != "") {								
						// insert usergroup details
						$sql = "INSERT INTO radgroupcheck values (0,'$groupExtra', '$attributeExtra[$counter]', '$opExtra[$counter]', '$valueExtra[$counter]')";
						$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
						$counter++;
					} // end if trim
				} else { 
	                    echo "<font color='#FF0000'>error: the group [$groupnameExtra[$counter]] already exist in the database with value [$valueExtra[$counter]] <br/></font>";
						echo "
	                        <script language='JavaScript'>
	                        <!--
	                            alert('The group $groupnameExtra[$counter] already exists in the database with value $valueExtra[$counter]');
	                        -->
	                        </script>
	                        ";
	            } // end else if mysql
					
			}
		
		}		
                        
        include 'library/closedb.php';
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<SCRIPT TYPE="text/javascript">
<!--
function toggleShowDiv(pass) {

        var divs = document.getElementsByTagName('div');
        for(i=0;i<divs.length;i++) {
                if (divs[i].id.match(pass)) {
                        if (document.getElementById) {                                         
                                if (divs[i].style.display=="inline")
                                        divs[i].style.display="none";
                                else
                                        divs[i].style.display="inline";
                        } else if (document.layers) {                                           
                                if (document.layers[divs[i]].display=='visible')
                                        document.layers[divs[i]].display = 'hidden';
                                else
                                        document.layers[divs[i]].display = 'visible';
                        } else {
                                if (document.all.hideShow.divs[i].visibility=='visible')     
                                        document.all.hideShow.divs[i].visibility = 'hidden';
                                else
                                        document.all.hideShow.divs[i].visibility = 'visible';
                        }
                }
        }
}

<?php
	include("library/javascript/dynamicadd_groupreply.js");
?>

// -->
</script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<?php
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradgroupchecknew.php] ?></a></h2>
				
				<p>

                                <form name="newgroupcheck" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l[FormField][all][Groupname] ?></b>
</td><td>												
                                                <input value="<?php echo $groupname[0] ?>" name="groupname[]"/>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($attribute) == "") { echo "<font color='#FF0000'>";  }?>
												<b><?php echo $l[FormField][all][Attribute] ?></b>
</td><td>												
                                                <input value="<?php echo $attribute[0] ?>" name="attribute[]" /> 
                                                </font><br/>
</td></tr>
<tr><td>												
                                                <?php if (trim($op) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][all][Operator] ?></b>
</td><td>												
												<select name="op[]" />
				<?php include ('include/management/op_select_options.php');
					  drawOptions();
					  ?>
												</select>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($value) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][all][Value] ?></b>
</td><td>												
                                                <input value="<?php echo $value[0] ?>" name="value[]" />
                                                </font><br/>
</td></tr>
</table>

<br/>
<center>
<input type="button" value="Add Groups" onclick="addStuff()"/>
</center>
<br/><br/>

<div id="mydiv">
</div>
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
