<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include 'library/config.php';
	include 'library/opendb.php';

	$name = $_GET['name'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM hotspots WHERE name='$name'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$macaddress = $nt[2];
	$geocode = $nt[3];

	if (isset($_REQUEST['submit'])) {

		$name = $_POST['name'];
		$macaddress = $_POST['macaddress'];
		$geocode = $_POST['geocode'];

		if (trim($name) != "") {

			if (trim($macaddress) != "") {
			$sql = "UPDATE hotspots SET mac='$macaddress' WHERE name='$name'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}

			if (trim($geocode) != "") {
			$sql = "UPDATE hotspots SET geocode='$geocode' WHERE name='$name'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}

		
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
		
				<h2 id="Intro">Edit Hotspots Details</h2>
				
				<p>
				You may edit below details for hotspot 
				<br/><br/>			</p>
				<form action="mng-hs-edit.php" method="post">
						<b>MAC Address</b>
						<input value="<?php echo $macaddress ?>" name="macaddress" /><br/>

						<b>Geocode</b>
						<input value="<?php echo $geocode ?>" name="geocode" /><br/>

						<input type="hidden" value="<?php echo $name ?>" name="name" /><br/>
						
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





