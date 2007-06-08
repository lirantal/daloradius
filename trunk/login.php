<?php 
	$error = $_REQUEST['error'];
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
						<h1><a href="index.php">daloRADIUS</a></h1>
				
				<h2>
				
						Radius Reporting, Billing and Management by <a href="http://www.enginx.com">Enginx</a>
				
				</h2>
				<br/>
				
				<ul id="subnav">
				
						<li>daloRADIUS Login Page:</li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
				<h2>Login Required</h2>
				
				<h3>Log-in please</h3>

				<form action="dologin.php" class="sidebar" method="post" >

						<label>Username</label>
						<input name="operator_user" value="administrator" type="text" />
						<label>Password</label>
						<input name="operator_pass" value="" type="password"/>
						<br/><br/>
						<input type="submit" value="Login"/>
				</form>
						
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"></a></h2>
				
				<p>
				
<?php
	 if ($error) { 
		echo $error;
		echo "<br/><br/>either of the following:<br/>";
		echo "1. bad username/password<br/>";
		echo "2. an administrator is already logged-in (only one instance is allowed) <br/>";
		echo "3. there appears to be more than one 'administrator' user in the database <br/>";
	}
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
