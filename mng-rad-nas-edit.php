<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');


	
        include 'library/opendb.php';

	$nashost = "";
        $nassecret = "";
        $nasname = "";
        $nasports = "";
        $nastype = "";
        $nasdescription = "";
        $nascommunity = "";

	$nashost = $_REQUEST['nashost'];

        // fill-in nashost details in html textboxes
        $sql = "SELECT * FROM nas WHERE nasname='$nashost'";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
        $row = mysql_fetch_array($res);		// array fetched with values from $sql query

						// assignment of values from query to local variables
						// to be later used in html to display on textboxes (input)
        $nassecret = $row['secret'];
        $nasname = $row['shortname'];
        $nasports = $row['ports'];
        $nastype = $row['type'];
        $nascommunity = $row['community'];
        $nasdescription = $row['description'];

        if (isset($_POST['submit'])) {
	        $nashost = $_POST['nashost'];
	        $nassecret = $_POST['nassecret'];;
	        $nasname = $_POST['nasname'];;
	        $nasports = $_POST['nasports'];;
	        $nastype = $_POST['nastype'];;
	        $nasdescription = $_POST['nasdescription'];;
	        $nascommunity = $_POST['nascommunity'];;

                
                include 'library/opendb.php';

                $sql = "SELECT * FROM nas WHERE nasname='$nashost'";
                $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

                if (mysql_num_rows($res) == 1) {

                        if (trim($nashost) != "" and trim($nassecret) != "") {

				if (!$nasports) {
					$nasports = 0;
				}

                                // insert nas details
                                $sql = "UPDATE nas SET shortname='$nasname', type='$nastype', ports=$nasports, secret='$nassecret', community='$nascommunity', description='$nasdescription' WHERE nasname='$nashost'";
                                $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
                        
			echo "<font color='#0000FF'>success<br/></font>";

			}

                } elseif (mysql_num_rows($res) > 1) {
                        echo "<font color='#FF0000'>error: NAS IP/Host [$nashost] already exist <br/></font>";
						echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The NAS IP/Host $nashost already exists in the database.\\nPlease check that there are no duplicate entries in the database.');
                                -->
                                </script>
                                ";
                } else {
                        echo "<font color='#FF0000'>error: NAS IP/Host [$nashost] doesn't exist <br/></font>";
						echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The NAS IP/Host $nashost doesn't exist at all in the database.\\nPlease re-check the username.');
                                -->
                                </script>
                                ";
				}

                include 'library/closedb.php';
        }

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
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradnasedit.php] ?></a></h2>
				
				<p>

                                <form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1' width='600'>
<tr><td>
                                                <input type="hidden" value="<?php echo $nashost ?>" name="nashost" /><br/>

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

<table border='2' class='table1'>
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
</div>
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
