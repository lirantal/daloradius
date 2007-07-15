<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

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
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "insert into radcheck values (0, '$username', 'User-Password', '==', '$password')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
				// insert username/password
				if ($expiration) {
				$sql = "insert into radcheck values (0, '$username', 'Expiration', ':=', '$expiration')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}
	
				if ($maxallsession) {
				// insert username/password
				$sql = "insert into radcheck values (0, '$username', 'Max-All-Session', ':=', '$maxallsession')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($sessiontimeout) {
				// insert username/password
				$sql = "insert into radreply values (0, '$username', 'Session-Timeout', ':=', '$sessiontimeout')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($idletimeout) {
				// insert username/password
				$sql = "insert into radreply values (0, '$username', 'Idle-Timeout', ':=', '$idletimeout')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}


				if ($calledstationid) {
				// insert called-station-id
				$sql = "insert into radcheck values (0, '$username', 'Called-Station-Id', '==', '$calledstationid')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}


				if ($callingstationid) {
				// insert calling-station-id
				$sql = "insert into radcheck values (0, '$username', 'Calling-Station-Id', '==', '$callingstationid')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($wisprredirectionurl) {
				// insert WISPr-Redirection-URL
				$sql = "insert into radreply values (0, '$username', 'WISPr-Redirection-URL', '=', '$wisprredirectionurl')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($wisprbandwidthmaxup) {
				// insert WISPr-Bandwidth-Max-Up
				$sql = "insert into radreply values (0, '$username', 'WISPr-Bandwidth-Max-Up', '=', '$wisprbandwidthmaxup')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}


				if ($wisprbandwidthmaxdown) {
				// insert WISPr-Bandwidth-Max-Down
				$sql = "insert into radreply values (0, '$username', 'WISPr-Bandwidth-Max-Down', '=', '$wisprbandwidthmaxdown')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}

				if ($wisprsessionterminatetime) {
				// insert WISPr-Session-Terminate-Time
				$sql = "insert into radreply values (0, '$username', 'WISPr-Session-Terminate-Time', '=', '$wisprsessionterminatetime')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
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
		
				<h2 id="Intro">New User</h2>
				
				<p>
				You may fill below details for new user addition to database
				<br/><br/>
				</p>
				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Username</b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Password</b>
</td><td>
						<input value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/>
						</font>
</td></tr>
<tr><td
						<?php if (trim($expiration) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Expiration</b>
</td><td>
<input name="expiration" type="text" id="expiration" value="<?php echo $expiration ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						<br/>
						</font>
</td></tr>
</table>
	<br/><br/>
	<h4> Session Attributes </h4>

						<?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?> 
						<input type="checkbox" onclick="javascript:toggleShowDiv('attributesMaxAllSession')"> 
						<b>Max-All-Session</b> <br/>

<div id="attributesMaxAllSession" style="display:none;visibility:visible" > 
						<input value="<?php echo $maxallsession ?>" name="maxallsession" />
<a href="javascript:maxallsession(86400)">1day(s)</a>
<a href="javascript:maxallsession(259200)">3day(s)</a>
<a href="javascript:maxallsession(604800)">1week(s)</a>
<a href="javascript:maxallsession(1209600)">2week(s)</a>
<a href="javascript:maxallsession(1814400)">3week(s)</a>
<a href="javascript:maxallsession(2592000)">1month(s)</a>
<a href="javascript:maxallsession(5184000)">2month(s)</a>
<a href="javascript:maxallsession(7776000)">3month(s)</a>
						<br/><br/>
						</font>

</div>

						<?php if (trim($sessiontimeout) == "") { echo "<font color='#FF0000'>";  }?>
						<input type="checkbox" onclick="javascript:toggleShowDiv('attributesSessionTimeout')"> 
						<b>Session Timeout</b><br/>
<div id="attributesSessionTimeout" style="display:none;visibility:visible" > 
						<input value="<?php echo $sessiontimeout ?>" name="sessiontimeout" />
<a href="javascript:sessiontimeout(86400)">1day(s)</a>
<a href="javascript:sessiontimeout(259200)">3day(s)</a>
<a href="javascript:sessiontimeout(604800)">1week(s)</a>
<a href="javascript:sessiontimeout(1209600)">2week(s)</a>
<a href="javascript:sessiontimeout(1814400)">3week(s)</a>
<a href="javascript:sessiontimeout(2592000)">1month(s)</a>
<a href="javascript:sessiontimeout(5184000)">2month(s)</a>
<a href="javascript:sessiontimeout(7776000)">3month(s)</a>
						<br/><br/>
						</font>
</div>

						<?php if (trim($idletimeout) == "") { echo "<font color='#FF0000'>";  }?>
						<input type="checkbox" onclick="javascript:toggleShowDiv('attributesIdleTimeout')"> 
						<b>Idle Timeout</b><br/>
<div id="attributesIdleTimeout" style="display:none;visibility:visible" >
						<input value="<?php echo $idletimeout ?>" name="idletimeout" />
<a href="javascript:idletimeout(86400)">1day(s)</a>
<a href="javascript:idletimeout(259200)">3day(s)</a>
<a href="javascript:idletimeout(604800)">1week(s)</a>
<a href="javascript:idletimeout(1209600)">2week(s)</a>
<a href="javascript:idletimeout(1814400)">3week(s)</a>
<a href="javascript:idletimeout(2592000)">1month(s)</a>
<a href="javascript:idletimeout(5184000)">2month(s)</a>
<a href="javascript:idletimeout(7776000)">3month(s)</a>
						<br/>
						</font>
</div>


	<br/>
	<h4> NAS Attributes </h4>


						<?php if (trim($callingstationid) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesCallingStationId')">
						<b>Calling-Station-Id</b><br/>
<div id="attributesCallingStationId" style="display:none;visibility:visible" >
						<input value="<?php echo $callingstationid ?>" name="callingstationid" />
						</font> force the user to login from this computer/nic MAC addresss only <br/><br/>
</div>
						<?php if (trim($calledstationid) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesCalledStationId')">
						<b>Called-Station-Id</b><br/>
<div id="attributesCalledStationId" style="display:none;visibility:visible" >
						<input value="<?php echo $calledstationid ?>" name="calledstationid" />
						</font> force the user to login from this AP only <br/><br/>
</div>

	<br/>
	<h4> WISPr Attributes </h4>

						<?php if (trim($wisprredirectionurl) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesWISPr-Redirection-URL')">
						<b>WISPr-Redirection-URL</b><br/>
<div id="attributesWISPr-Redirection-URL" style="display:none;visibility:visible" >
						<input value="<?php echo $wisprredirectionurl ?>" name="wisprredirectionurl" />
						</font> If present the client will be redirected to this URL once authenticated. <br/><br/>
</div>

						<?php if (trim($wisprbandwidthmaxup) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesWISPr-Bandwidth-Max-Up')">
						<b>WISPr-Bandwidth-Max-Up</b><br/>
<div id="attributesWISPr-Bandwidth-Max-Up" style="display:none;visibility:visible" >

						<input value="<?php echo $wisprbandwidthmaxup ?>" name="wisprbandwidthmaxup" />
<a href="javascript:wisprbandwidthmaxup(128000)">128kbit</a>
<a href="javascript:wisprbandwidthmaxup(256000)">256kbit</a>
<a href="javascript:wisprbandwidthmaxup(512000)">512kbit</a>
<a href="javascript:wisprbandwidthmaxup(1048576)">1mbit</a>
<a href="javascript:wisprbandwidthmaxup(1572864)">1.5mbit</a>
<a href="javascript:wisprbandwidthmaxup(2097152)">2mbit</a>
<a href="javascript:wisprbandwidthmaxup(3145728)">3mbit</a>
<a href="javascript:wisprbandwidthmaxup(10485760)">10mbit</a>
						</font> <br/> Maximum transmit rate (b/s). This attribute is specified in bits per second. <br/><br/>

</div>

						<?php if (trim($wisprbandwidthmaxdown) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesWISPr-Bandwidth-Max-Down')">
						<b>WISPr-Bandwidth-Max-Down</b><br/>
<div id="attributesWISPr-Bandwidth-Max-Down" style="display:none;visibility:visible" >

						<input value="<?php echo $wisprbandwidthmaxdown ?>" name="wisprbandwidthmaxdown" />
<a href="javascript:wisprbandwidthmaxdown(128000)">128kbit</a>
<a href="javascript:wisprbandwidthmaxdown(256000)">256kbit</a>
<a href="javascript:wisprbandwidthmaxdown(512000)">512kbit</a>
<a href="javascript:wisprbandwidthmaxdown(1048576)">1mbit</a>
<a href="javascript:wisprbandwidthmaxdown(1572864)">1.5mbit</a>
<a href="javascript:wisprbandwidthmaxdown(2097152)">2mbit</a>
<a href="javascript:wisprbandwidthmaxdown(3145728)">3mbit</a>
<a href="javascript:wisprbandwidthmaxdown(10485760)">10mbit</a>
						</font> <br/> Maximum receiving rate (b/s). This attribute is specified in bits per second. <br/><br/>
</div>

						<?php if (trim($wisprsessionterminatetime) == "") { echo "<font color='#FF0000'>";  }?>
                                                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesWISPr-Session-Terminate-Time')">
						<b>WISPr-Session-Terminate-Time</b><br/>
<div id="attributesWISPr-Session-Terminate-Time" style="display:none;visibility:visible" >
			<input name="wisprsessionterminatetime" type="text" id="wisprsessionterminatetime" value="<?php echo $wisprsessionterminatetime ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'wisprsessionterminatetime', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
</div>

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





