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
<?php
        include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">
		
<?php
    include_once ("include/menu/header.php");
?>      

		<div id="sidebar">
		
				<h2>Configuration</h2>
				
				<h3>Global Settings</h3>
				

                                <ul class="subnav">
                                
                                                <li><a href="config-db.php"><b>&raquo;</b>Database Settings</a></li>
												<li><a href="config-lang.php"><b>&raquo;</b>Language Settings</a></li>

			
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
