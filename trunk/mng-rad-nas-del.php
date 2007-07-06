<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$nashost = "";
        $nashost = $_REQUEST['nashost'];


        if (isset($_POST['submit'])) {
                if (trim($nashost) != "") {
                        include 'library/config.php';
                        include 'library/opendb.php';

                        // delete all attributes associated with a username
                        $sql = "DELETE FROM nas WHERE nasname='$nashost'";
                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

                        echo "<font color='#0000FF'>success<br/></font>";
                        include 'library/closedb.php';

                }  else {
                        echo "<font color='#FF0000'>error: no nashost was entered, please specify a nas ip/host to remove from database<br/></font>";
                        echo "
                                <script language='JavaScript'>
                                <!--
                                alert('No nas ip/host was entered, please specify a nas ip/host to remove from database');
                                -->
                                </script>
                                ";
                }
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



// -->
</script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Remove NAS Record</a></h2>
				
                                <p>
                                To remove a nas ip/host entry from the database you must provide the ip/host of the account.
                                <br/><br/>
                                </p>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <?php if (trim($nashost) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>NAS IP/Host</b>
                                                <input value="<?php echo $nashost ?>" name="nashost"/><br/>
                                                </font>

                                                <br/><br/>
                                                <input type="submit" name="submit" value="Apply"/>

                                </form>
				
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
