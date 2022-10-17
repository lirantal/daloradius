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
	$m_active = "Home";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/home-subnav.php");
?>      

<div id="sidebar">

	<h2>Home</h2>

	<h3>Quick Navigation</h3>

	<ul class="subnav">

	<li><a href="pref-main.php"><b>&raquo;</b><?php echo t('button','Preferences') ?></a></li>
	<li><a href="acct-main.php"><b>&raquo;</b><?php echo t('button','Accounting') ?></a></li>
	<li><a href="graph-main.php"><b>&raquo;</b><?php echo t('button','Graphs') ?></a></li>

	</ul>
	
	<h3>Support</h3>

	<p class="news">
		daloRADIUS <br/>
		RADIUS Management 
		<a href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
	</p>


	

	

</div>
