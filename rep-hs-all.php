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
						
						<li><a href="mng-main.php"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php" class="active"><em>R</em>eports</a></li>
						
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
		
				<h2>Reports</h2>
				
				<h3>Users Reports</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.searchusername.submit();"><b>&raquo;</b>Search User<a>
							<form name="searchusername" action="rep-username.php" method="post" class="sidebar">
							<input name="username" type="text">
							</form></li>

						<li><a href="javascript:document.topusers.submit();"><b>&raquo;</b>Top User<a>
							<form name="topusers" action="rep-topusers.php" method="post" class="sidebar">
							<select name="limit" type="text">
								<option value="5"> 5
								<option value="10"> 10
								<option value="20"> 20
								<option value="50"> 50
								<option value="100"> 100
								<option value="500"> 500
								<option value="1000"> 1000
							</select>
							<select name="order" type="text">
								<option value="AcctInputOctets"> bandwidth
								<option value="AcctSessionTime"> time
							</select>
							</form></li>
						<li><a href="rep-all.php"><b>&raquo;</b>List All Users</a></li>
	
				</ul>
		

				<h3>Hotspots Reports</h3>
				<ul class="subnav">
				
						<li><a href="rep-hs-all.php"><b>&raquo;</b>List all Hotspots</a></li>
				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>
		
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Hotspots Listing</a></h2>
				
				<p>
				Listing hotspots in database<br/>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';

        $sql = "SELECT * FROM hotspots;";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> Account ID </th>
                        <th scope='col'> HotSpots Name </th>
                        <th scope='col'> MAC Address </th>
                        <th scope='col'> Geocode </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[name] </td>
                        <td> $nt[mac] </td>
                        <td> $nt[geocode] </td>
                        <td> <a href='mng-hs-edit.php?name=$nt[name]'> edit </a>
	                     <a href='mng-hs-del.php?name=$nt[name]'> del </a>
			     </td>

                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
?>




				
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
