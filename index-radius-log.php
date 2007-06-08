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
				
						<li><a href="index.php" class="active"><em>H</em>ome</a></li>
						
						<li><a href="mng-main.php"><em>M</em>anagment</a></li>
						
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
		
				<h2>Home</h2>
				
				<h3>Status</h3>
				

                                <ul class="subnav">
                                
                                                <li><a href="index-server-stat.php"><b>&raquo;</b>Server Status</a></li>
                                                <li><a href="index-radius-stat.php"><b>&raquo;</b>RADIUS Status </a></li>
                                                <li><a href="index-last-connect.php"><b>&raquo;</b>Last Connection Attempts</a></li>

				<h3>Logs</h3>
				
                                                <li><a href="index-radius-log.php"><b>&raquo;</b>radius log</a></li>
                                                <li><a href="index-system-log.php"><b>&raquo;</b>system log</a></li>
				
				<h3>Support</h3>
				
				<p class="news">
                                        daloRADIUS <br/>
                                        RADIUS Management
                                        <a href="http://www.enginx.com" class="more">Read More &raquo;</a>
				</p>
				
			
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">RADIUS Server Logfile</a></h2>
				<p>

<?php
	include 'library/exten-radius_log.php';
?>
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
