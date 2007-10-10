<?php 
	isset($_REQUEST['error']) ? $error = $_REQUEST['error'] : $error = "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<body>
<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

		<div id="header">
                <h1><a href="index.php"> <img src="images/daloradius_small.jpg" border=0/></a></h1>
				
				<h2>
				
				<?echo $l['all']['copyright1']; ?>	
				</h2>
				<br/>
				
				<ul id="subnav">
				
				<li><? echo $l['all']['daloRADIUS']." ".$l[captions][loginpage]?></li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
		<h2><? echo $l['captions']['loginrequired']; ?></h2>
				
		<h3><? echo $l['captions']['loginplease']; ?></h3>

				<form action="dologin.php" class="sidebar" method="post" >
					<ul class="subnav">
						<li><a href="#" >Username</a> </li>
						<input name="operator_user" value="administrator" type="text" />
						<li><a href="#" >Password</a> </li>
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
		echo $l['messages']['loginerror'];
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
