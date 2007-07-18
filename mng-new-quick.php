<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$username = "";
	$password = "";
	$maxallsession = "";

	if (isset($_POST['submit'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$maxallsession = $_POST['maxallsession'];

		
		include 'library/opendb.php';


		$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "insert into radcheck values (0, '$username', 'User-Password', '==', '$password')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
				if ($maxallsession) {
				$sql = "insert into radcheck values (0, '$username', 'Max-All-Session', ':=', '$maxallsession')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($maxallsession) {
				$sql = "insert into radreply values (0, '$username', 'Session-Timeout', ':=', '$maxallsession')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

	
				echo "<font color='#0000FF'>success<br/></font>";
			}
		} else { 
			echo "<font color='#FF0000'>error: user [$username] already exist <br/></font>"; 
			echo "
				<script language='JavaScript'>
				<!--
				alert('You have tried to add a user that already exist in the database.\\nThe user $username already exist'); 
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
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
 

<SCRIPT TYPE="text/javascript">
<!--

function randomPassword()
{
  length = 8;
  chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
  pass = "";
  for(x=0;x<length;x++)
  {
    i = Math.floor(Math.random() * 62);
    pass += chars.charAt(i);
  }
  document.newuser.password.value = pass;
}

function randomUsername()
{
  length = 8;
  chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
  user = "";
  for(x=0;x<length;x++)
  {
    i = Math.floor(Math.random() * 62);
    user += chars.charAt(i);
  }
  document.newuser.username.value = user;
}

function maxallsession(time)
{
  document.newuser.maxallsession.value = time;
}

function small_window(user,pass,time) {
  var newWindow;
  var currentTime = new Date();
  var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200';
  newWindow = window.open("", "Client Receipt", props);
  newWindow.document.write("<html><title>Customer Receipt</title><body><br/>");
  newWindow.document.write("Thank you. <br/>");
  newWindow.document.write("Your username is: ");
  newWindow.document.write(user);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your password is: ");
  newWindow.document.write(pass);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your timecredit is: ");
  newWindow.document.write(time);
  newWindow.document.write("<br/>");
  newWindow.document.write("<br/>");
  newWindow.document.write("Receipt produced on: ");
  newWindow.document.write(currentTime);
  newWindow.document.write("<br/>");
  newWindow.document.write("Enginx HotSpot System ");
  newWindow.document.write("<br/>");
  newWindow.document.write(" </body></html>");
}


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
<?php

	include ("menu-mng-main.php");
	
?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngnewquick.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngnewquick] ?>
				<br/><br/>
				</p>
				<form name="newuser" action="mng-new-quick.php" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Username] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Password] ?></b>
</td><td>
						<input value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/><br/>
						</font>

</td></tr>
</table>
<center>
<br/>
						<?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?>
						<input type="checkbox" onclick="javascript:toggleShowDiv('attributesMaxAllSession')">
						<b><?php echo $l[FormField][mngnewquick.php][MaxAllSession] ?></b><br/>
<div id="attributesMaxAllSession" style="display:none;visibility:visible" >

						<input value="<?php echo $maxallsession ?>" name="maxallsession" />
<a href="javascript:maxallsession(1800)">1/2hour(s)</a>
<a href="javascript:maxallsession(3600)">1hour(s)</a>
<a href="javascript:maxallsession(10800)">3hour(s)</a>
<a href="javascript:maxallsession(18000)">5hour(s)</a>
<a href="javascript:maxallsession(86400)">1day(s)</a>
<a href="javascript:maxallsession(259200)">3day(s)</a>
<a href="javascript:maxallsession(604800)">1week(s)</a>
<a href="javascript:maxallsession(1209600)">2week(s)</a>
<a href="javascript:maxallsession(2592000)">1month(s)</a>
						<br/>
</div>
<br/>
						</font>

						<input type="submit" name="submit" value="<?php echo $l[buttons][apply]?>" onclick = "javascript:small_window(document.newuser.username.value, document.newuser.password.value, document.newuser.maxallsession.value);" />

				</center>

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





