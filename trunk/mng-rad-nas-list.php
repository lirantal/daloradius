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
						<li><a href="mng-rad-nas.php" class="active"><em>N</em>as</a></li>
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
				
				<h3>NAS Management</h3>
				<ul class="subnav">
				
						<li><a href="mng-rad-nas-list.php"><b>&raquo;</b>List NAS</a></li>
						<li><a href="mng-rad-nas-new.php"><b>&raquo;</b>New NAS</a></li>
						<li><a href="javascript:document.mngradnasedit.submit();""><b>&raquo;</b>Edit NAS<a>
							<form name="mngradnasedit" action="mng-rad-nas-edit.php" method="get" class="sidebar">
							<input name="nashost" type="text">
							</form></li>


						<li><a href="mng-rad-nas-del.php"><b>&raquo;</b>Remove NAS</a></li>	
				</ul>
		
	
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">NAS Listing in Database</a></h2>
				
				<p>

<?php

        include 'library/config.php';
        include 'library/opendb.php';


        $sql = "SELECT * FROM nas";
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
                        <th scope='col'> NAS ID </th>
                        <th scope='col'> NAS IP/Host </th>
                        <th scope='col'> NAS Shortname </th>
                        <th scope='col'> NAS Type </th>
                        <th scope='col'> NAS Ports</th>
                        <th scope='col'> NAS Secret</th>
                        <th scope='col'> NAS Community </th>
                        <th scope='col'> NAS Description </th>
                        <th scope='col'> Action </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[id] </td>
                        <td> $nt[nasname] </td>
                        <td> $nt[shortname] </td>
                        <td> $nt[type] </td>
                        <td> $nt[ports] </td>
                        <td> $nt[secret] </td>
                        <td> $nt[community] </td>
                        <td> $nt[description] </td>
                        <td> <a href='mng-rad-nas-edit.php?nashost=$nt[nasname]'> edit </a>
                             <a href='mng-rad-nas-del.php?nashost=$nt[nasname]'> del </a>
                             </td>

                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
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
