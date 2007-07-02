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
    $m_active = "Help";
    include_once ("include/menu/header.php");
?>

		<div id="sidebar">
		
				<h2>Help</h2>
				
				<h3>Support</h3>
				
				<p class="news">
					daloRADIUS version svn-trnk
					RADIUS Management 
					<a href="http://www.enginx.com" class="more">Read More &raquo;</a>
				</p>
				
			
		
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Help</a></h2>
				<p>
				still under construction
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
