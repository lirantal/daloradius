<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
	isset($_REQUEST['error']) ? $error = $_REQUEST['error'] : $error = "";

	// clean up error code to avoid XSS
	$error = strip_tags(htmlspecialchars($error));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

<style type="text/css">
body {
	font: 78.5%/1.6em "Lucida Grande", "Lucida Sans Unicode", verdana, geneva, sans-serif;
	word-spacing:2px;
	color:#444;
	margin-top:20px;
	margin-left:130px;
	margin-right:200px;
	margin-bottom:20px;
	background:url(images/body.jpg) #f6f6f6;
}
</style>

</head>
 
<body onLoad="document.login.operator_user.focus()">
<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

	<div id="header">
		<h1><a href="index.php"> <img src="images/daloradius_small.png" border=0/></a></h1>
		
		<h2>
		
		<?echo $l['all']['copyright1']; ?>	
		</h2>
		<br/>
		
		<ul id="subnav">
		
		<li><? echo $l['all']['daloRADIUS'] ?></li>
		
		</ul>
	</div>
	
	<div id="sidebar">
	
	<h2><? echo $l['text']['LoginRequired'] ?></h2>

	<h3><? echo $l['text']['LoginPlease'] ?></h3>

		<form name="login" action="dologin.php" class="sidebar" method="post" >
			<ul class="subnav">
				<li><a href="#" >Username</a> </li>
				<input name="login_user" value="" type="text" tabindex=1 />
				<li><a href="#" >Password</a> </li>
				<input name="login_pass" value="" type="password" tabindex=2 />
				<br/><br/>
				<input type="submit" value="Login" tabindex=3 />
			</ul>
		</form>
	</div>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['login.php'] ?></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['login'] ?>
		</div>

		<?php echo $l['helpPage']['loginUsersPortal'] ?>

<?php
	if ($error) {
		echo $error;
		echo $l['messages']['loginerror'];
}
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
