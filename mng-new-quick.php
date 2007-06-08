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

		include 'library/config.php';
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
		
				<h2 id="Intro">Quick User Add</h2>
				
				<p>
				The following user/card is of type prepaid.<br/>
				The amount of time specified in Time Credit will be used as the Session-Timeout and Max-All-Session
radius attributes
				<br/><br/>
				</p>
				<form name="newuser" action="mng-new-quick.php" method="post">


						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Username</b>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>
						</font>

						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Password</b>
						<input value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/><br/>
						</font>

<br/>
						<?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?>
						<input type="checkbox" onclick="javascript:toggleShowDiv('attributesMaxAllSession')">
						<b>Time Credit (Max-All-Session) </b><br/>
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

						<input type="submit" name="submit" value="Create" onclick = "javascript:small_window(document.newuser.username.value, document.newuser.password.value, document.newuser.maxallsession.value);" />

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





