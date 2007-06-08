<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include 'library/config.php';
	include 'library/opendb.php';

	$username = "";
	$username = $_GET['username'];

	/* fill-in username and password in the textboxes

        /* We are searching for both kind of attributes for the password, being User-Password, the more
           common one and the other which is Password, this is also done for considerations of backwards
           compatibility with version 0.7        */

	$sql = "SELECT * FROM radcheck WHERE UserName='$username' AND (Attribute='User-Password' or Attribute='Password')";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$password = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Expiration'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$expiration = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Max-All-Session'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$maxallsession = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='Session-Timeout'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$sessiontimeout = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='Idle-Timeout'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$idletimeout = $nt['Value'];

	// fill-in called-station-id in the textboxes
	$sql = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Called-Station-Id'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$calledstationid = $nt['Value'];

	// fill-in called-station-id in the textboxes
	$sql = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Calling-Station-Id'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$callingstationid = $nt['Value'];


	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Redirection-URL'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$wisprredirectionurl = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Up'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$wisprbandwidthmaxup = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Down'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$wisprbandwidthmaxdown = $nt['Value'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Session-Terminate-Time'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$wisprsessionterminatetime = $nt['Value'];



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


		if (trim($username) != "") {


			if (trim($password) != "") {

			// Like before we want to find either User-Password or Password 

			$sql = "UPDATE radcheck SET Value='$password' WHERE UserName='$username' AND (Attribute='User-Password' or Attribute='Password')";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}
		
			if (trim($expiration) != "") {
			
			$query = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Expiration'";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			
				if (mysql_num_rows($result) == 1) {
					$sql = "UPDATE radcheck SET Value='$expiration' WHERE UserName='$username' AND Attribute='Expiration'";
					$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				} elseif (mysql_num_rows($result) < 1 ) {
					$sql = "INSERT INTO radcheck values (0, '$username', 'Expiration', ':=', '$expiration')";
					$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
				}
			}

			if (trim($maxallsession) != "") {

                        $query = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Max-All-Session'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
					$sql = "UPDATE radcheck SET Value='$maxallsession' WHERE UserName='$username' AND Attribute='Max-All-Session'";
					$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radcheck values (0, '$username', 'Max-All-Session', ':=', '$maxallsession')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($sessiontimeout) != "") {

                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='Session-Timeout'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$sessiontimeout' WHERE UserName='$username' AND Attribute='Session-Timeout'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'Session-Timeout', ':=', '$sessiontimeout')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($idletimeout) != "") {

                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='Idle-Timeout'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$idletimeout' WHERE UserName='$username' AND Attribute='Idle-Timeout'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'Idle-Timeout', ':=', '$idletimeout')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($calledstationid) != "") {

                        $query = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Called-Station-Id'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radcheck SET Value='$calledstationid' WHERE UserName='$username' AND Attribute='Called-Station-Id'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radcheck values (0, '$username', 'Called-Station-Id', '==', '$calledstationid')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($callingstationid) != "") {

                        $query = "SELECT * FROM radcheck WHERE UserName='$username' AND Attribute='Calling-Station-Id'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radcheck SET Value='$callingstationid' WHERE UserName='$username' AND Attribute='Calling-Station-Id'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radcheck values (0, '$username', 'Calling-Station-Id', '==', '$callingstationid')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($wisprsessionterminatetime) != "") {


                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Session-Terminate-Time'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$wisprsessionterminatetime' WHERE UserName='$username' AND Attribute='WISPr-Session-Terminate-Time'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'WISPr-Session-Terminate-Time', ':=', '$wisprsessionterminatetime')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}

			if (trim($wisprbandwidthmaxdown) != "") {

                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Down'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$wisprbandwidthmaxdown' WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Down'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'WISPr-Bandwidth-Max-Down', ':=', '$wisprbandwidthmaxdown')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}


			if (trim($wisprbandwidthmaxup) != "") {

                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Up'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$wisprbandwidthmaxup' WHERE UserName='$username' AND Attribute='WISPr-Bandwidth-Max-Up'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'WISPr-Bandwidth-Max-Up', ':=', '$wisprbandwidthmaxup')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }
			}


			if (trim($wisprredirectionurl) != "") {

                        $query = "SELECT * FROM radreply WHERE UserName='$username' AND Attribute='WISPr-Redirection-URL'";
                        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

                                if (mysql_num_rows($result) == 1) {
                                        $sql = "UPDATE radreply SET Value='$wisprredirectionurl' WHERE UserName='$username' AND Attribute='WISPr-Redirection-URL'";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                } elseif (mysql_num_rows($result) < 1 ) {
                                        $sql = "INSERT INTO radreply values (0, '$username', 'WISPr-Redirection-URL', ':=', '$wisprredirectionurl')";
                                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                                }			}



		}
	}

	include 'library/closedb.php';

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<body>

<div id="wrapper">
<div id="innerwrapper">

		<div id="header">
		
				<form action="">
				<input value="Search" />
				</form>
				
				<h1><a href="index.php">daloRADIUS</a></h1>
				
				<h2>
				
						Radius Reporting, Billing and Management by <a href="http://www.enginx.com">Enginx</a>
				
				</h2>
				
				<ul id="nav">
				
						<li><a href="index.php"><em>H</em>ome</a></li>
						
						<li><a href="mng-main.php" class="active"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php"><em>R</em>eports</a></li>
						
						<li><a href="acct-main.php"><em>A</em>ccounting</a></li>

						<li><a href="bill-main.php"><em>B</em>illing</a></li>
						<li><a href="gis-main.php"><em>GIS</em></a></li>
						<li><a href="graph-main.php"><em>G</em>raphs</a></li>

						<li><a href="help-main.php"><em>H</em>elp</a></li>
				
				</ul>
				<ul id="subnav">
				
						<li>Welcome, <?php echo $operator; ?></li>

						<li><a href="logout.php">[logout]</a></li>
				
				</ul>
		
		</div>
		
				<div id="sidebar">
		
				<h2>Management</h2>
				
				<h3>Users Management</h3>
				<ul class="subnav">
				
						<li><a href="mng-new.php"><b>&raquo;</b>New User</a></li>
						<li><a href="mng-new-quick.php"><b>&raquo;</b>New User - Quick add </a></li>
						<li><a href="mng-batch.php"><b>&raquo;</b>Batch-Add Users <a></li>
						<li><a href="javascript:document.mngedit.submit();""><b>&raquo;</b>Edit User<a>
							<form name="mngedit" action="mng-edit.php" method="get" class="sidebar">
							<input name="username" type="text">
							</form></li>


						<li><a href="mng-del.php"><b>&raquo;</b>Remove User</a></li>	
				</ul>
		
				<h3>Hotspots Management</h3>
				<ul class="subnav">
				
						<li><a href="mng-hs-list.php"><b>&raquo;</b>List Hotspots</a></li>
						<li><a href="mng-hs-new.php"><b>&raquo;</b>New Hotspot</a></li>
						<li><a href="javascript:document.mnghsedit.submit();""><b>&raquo;</b>Edit Hotspot<a>
							<form name="mnghsedit" action="mng-hs-edit.php" method="get" class="sidebar">
							<input name="name" type="text">
							</form></li>


						<li><a href="mng-hs-del.php"><b>&raquo;</b>Remove Hotspot</a></li>
				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro">Edit User Details</h2>
				
				<p>
				You may fill below details for new user addition to database
				<br/><br/>			</p>
				<form action="mng-edit.php" method="post">
						<b>Password</b>
						<input value="<?php echo $password ?>" name="password" /><br/>

						<b>Expiration</b>
						<input value="<?php echo $expiration ?>" name="expiration" /><br/><br/>

						<b>Max-All-Session</b>
						<input value="<?php echo $maxallsession ?>" name="maxallsession" /><br/>

						<b>Session Timeout</b>
						<input value="<?php echo $sessiontimeout ?>" name="sessiontimeout" /><br/>

						<b>Idle Timeout</b>
						<input value="<?php echo $idletimeout ?>" name="idletimeout" /><br/><br/>

						<b>Called-Sation-Id</b>
						<input value="<?php echo $calledstationid ?>" name="calledstationid" /><br/>

						<b>Calling-Sation-Id</b>
						<input value="<?php echo $callingstationid ?>" name="callingstationid" /><br/><br/>

						<b>WISPr-Redirection-URL</b>
						<input value="<?php echo $wisprredirectionurl ?>" name="wisprredirectionurl" /><br/>

						<b>WISPr-Bandwidth-Max-Up</b>
						<input value="<?php echo $wisprbandwidthmaxup ?>" name="wisprbandwidthmaxup" /><br/>

						<b>WISPr-Bandwidth-Max-Down</b>
						<input value="<?php echo $wisprbandwidthmaxdown ?>" name="wisprbandwidthmaxdown" /><br/>

						<b>WISPr-Session-Terminate-Time</b>
						<input value="<?php echo $wisprsessionterminatetime ?>" name="wisprsessionterminatetime" /><br/>



						<input type="hidden" value="<?php echo $username ?>" name="username" /><br/>
						
						<br/><br/>
						<input type="submit" name="submit" value="Save Settings"/>

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





