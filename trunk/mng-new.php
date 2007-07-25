<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    // declaring variables
    $username = "";
    $password = "";
    $expiration = "";
    $maxallsession = "";
    $sessiontimeout = "";
    $calledstationid = "";
    $callingstationid = "";
    $idletimeout = "";
    $wisprredirectionurl = "";
    $wisprbandwidthmaxup = "";
    $wisprbandwidthmaxdown = "";
    $wisprsessionterminatetime = "";

	if (isset($_POST['submit'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
        $passwordtype = $_POST['passwordType'];	
		$expiration = $_POST['expiration'];
		$maxallsession = $_POST['maxallsession'];
		$sessiontimeout = $_POST['sessiontimeout'];
		$calledstationid = $_POST['calledstationid'];
		$callingstationid = $_POST['callingstationid'];
		$idletimeout = $_POST['idletimeout'];
		$wisprredirectionurl = $_POST['wisprredirectionurl'];
		$wisprbandwidthmaxup = $_POST['wisprbandwidthmaxup'];
		$wisprbandwidthmaxdown = $_POST['wisprbandwidthmaxdown'];
		$wisprsessionterminatetime = $_POST['wisprsessionterminatetime'];

		
		include 'library/opendb.php';


		$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', '$passwordtype', '==', '$password')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	
				// insert username/password
				if ($expiration) {
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Expiration', ':=', '$expiration')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}
	
				if ($maxallsession) {
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Max-All-Session', ':=', '$maxallsession')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($sessiontimeout) {
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'Session-Timeout', ':=', '$sessiontimeout')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($idletimeout) {
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'Idle-Timeout', ':=', '$idletimeout')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}


				if ($calledstationid) {
				// insert called-station-id
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Called-Station-Id', '==', '$calledstationid')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}


				if ($callingstationid) {
				// insert calling-station-id
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Calling-Station-Id', '==', '$callingstationid')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($wisprredirectionurl) {
				// insert WISPr-Redirection-URL
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'WISPr-Redirection-URL', '=', '$wisprredirectionurl')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($wisprbandwidthmaxup) {
				// insert WISPr-Bandwidth-Max-Up
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'WISPr-Bandwidth-Max-Up', '=', '$wisprbandwidthmaxup')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}


				if ($wisprbandwidthmaxdown) {
				// insert WISPr-Bandwidth-Max-Down
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'WISPr-Bandwidth-Max-Down', '=', '$wisprbandwidthmaxdown')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($wisprsessionterminatetime) {
				// insert WISPr-Session-Terminate-Time
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'WISPr-Session-Terminate-Time', '=', '$wisprsessionterminatetime')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}




	
				//echo "<font color='#0000FF'>success<br/></font>";
				$msg = "Added new user <b> $username </b> to database";
				header("location: mng-success.php?task=$msg");
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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>
 
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

<SCRIPT TYPE="text/javascript">
<!--

function setText(srcObj,dstObj) {

var srcElem = document.getElementById(srcObj);
var elemVal = srcElem.options[srcElem.selectedIndex].value;
alert(elemVal);

var dstElem = document.getElementById(dstObj);
dstElem.value = elemVal;

}


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



function sessiontimeout(time)
{
  document.newuser.sessiontimeout.value = time;
}

function idletimeout(time)
{
  document.newuser.idletimeout.value = time;
}



function maxallsession(time)
{
  document.newuser.maxallsession.value = time;

}

function wisprbandwidthmaxup(speed)
{
  document.newuser.wisprbandwidthmaxup.value = speed;
}


function wisprbandwidthmaxdown(speed)
{
  document.newuser.wisprbandwidthmaxdown.value = speed;
}


function toggleShowDiv(pass) {

	var divs = document.getElementsByTagName('div');
	for(i=0;i<divs.length;i++) {
		if (divs[i].id.match(pass)) {
			if (document.getElementById) {							// compatible with IE5 and NS6
//				if (divs[i].style.visibility=="visible")
				if (divs[i].style.display=="inline")
//					divs[i].style.visibility="hidden";
					divs[i].style.display="none";
				else
//					divs[i].style.visibility="visible";
	 				divs[i].style.display="inline";
			} else if (document.layers) {							// compatible with Netscape 4
				if (document.layers[divs[i]].display=='visible')
					document.layers[divs[i]].display = 'hidden';
				else
					document.layers[divs[i]].display = 'visible';
			} else {
				if (document.all.hideShow.divs[i].visibility=='visible')		// compatible with IE4
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
		
				<h2 id="Intro"><?php echo $l[Intro][mngnew.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngnew] ?>
				<br/><br/>
				</p>
				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Username] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>

<a href="javascript:toggleShowDiv('showPasswordType')">advanced</a><br/>
<div id="showPasswordType" style="display:none;visibility:visible" >
<br/>
<input type="radio" name="passwordType" value="User-Password" checked>User-Password<br>
<input type="radio" name="passwordType" value="Chap-Password">Chap-Password<br>
<input type="radio" name="passwordType" value="Cleartext-Password">Cleartext-Password<br>
<input type="radio" name="passwordType" value="Crypt-Password">Crypt-Password<br>
<input type="radio" name="passwordType" value="MD5-Password">MD5-Password<br>
<input type="radio" name="passwordType" value="SHA1-Password">SHA1-Password<br>
</div>


						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Password] ?></b>
</td><td>
						<input value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/>
						</font>
</td></tr>
<tr><td
						<?php if (trim($expiration) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Expiration] ?></b>
</td><td>
<input name="expiration" type="text" id="expiration" value="<?php echo $expiration ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						<br/>
						</font>
</td></tr>
</table>
	<br/><br/>

<?php
        include('include/management/attributes.php');
        drawAttributes();
?>
		
						<br/><br/>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>


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





