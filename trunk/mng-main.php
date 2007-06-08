<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

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
						<li><a href="mng-rad-nas.php"><em>N</em>as</a></li>
						<li><a href="mng-rad-usergroup.php"><em>U</em>ser-group Mapping</a></li>
						<li><a href="mng-rad-groupreply.php"><em>G</em>roup-reply</a></li>
						<li><a href="mng-rad-groupcheck.php"><em>G</em>roup-check</a></li>
						<li><a href="mng-rad-postauth.php"><em>P</em>ostauth</a></li>
						<li><a href="mng-rad-operators.php"><em>O</em>perators</a></li>

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
		
				<h2 id="Intro"><a href="#">Users and Hotspots Management</a></h2>
				
				<p>
	<table><center><br/>
	<img src="library/exten-get_total_users.php" />
	</table></center>

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
