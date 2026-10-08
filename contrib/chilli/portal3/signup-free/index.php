<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:     Liran Tal <liran@lirantal.com>
 *
 * Credits to the implementation of captcha are due to G.Sujith Kumar of codewalkers
 *
 *********************************************************************************************************
 */


header('Content-Type: text/html; charset=UTF-8');
session_start();
include __DIR__ . '/library/config_read.php';
require_once dirname(__DIR__, 2) . '/common/freeSignup.php';
$signup = dalo_chilli_signup_request($configValues);
$status = $signup['status'];
$username = htmlspecialchars($signup['username'], ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars($signup['password'], ENT_QUOTES, 'UTF-8');
$signupFirstname = htmlspecialchars($signup['firstname'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User Sign-Up</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body onLoad="return setFocus();">
<div id="wrapper">
  <div id="header">
    <div id="nav">	<a href="index.html">Sign-Up</a> &nbsp;|&nbsp; 
			<a href="#">Terms Of Service</a> &nbsp;|&nbsp; 
			<a href="#">About us</a> &nbsp;|&nbsp; 
			<a href="#">Contact us</a> &nbsp;|&nbsp; 
     </div>
    <div id="bg"></div>
  </div>
  <div id="main-content">
    <div id="left-column">
      <div id="logo"><img src="images/big-paw.gif" alt="Pet Logo" width="42" height="45" align="left" />
		<span class="logotxt1">daloRADIUS</span>
		<span class="logotxt2">user Sign-Up</span><br />
      		<span style="margin-left:15px;">daloRADIUS, driving smart hotspots to the limit</span></div>
      <div class="box">

        <h1>Sign-Up</h1>
	<p>

	<?php

		/*************************************************************************************************************************************************
		 *
		 * switch case for status of the sign-up process, whether it's the first time the user accesses it, or rather he already submitted
		 * the form with either successful or errornous result
		 *
		 *************************************************************************************************************************************************/     

		include("library/daloradius.conf.php");

		function showForm() {

			include("library/daloradius.conf.php");

			echo "<b>".$configValues['CONFIG_SIGNUP_MSG_TITLE']."</b>
				<br/><br/>
				<form name='signup' action='".htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."' method='post'>" . dalo_chilli_signup_csrf_field() . "

				<ul>
				        First name:<li> <input type='text' value='' name='firstname' /> <br/></li>
				        Last name:<li> <input type='text' value='' name='lastname' /> <br/></li>
				        Email address:<li> <input type='text' value='' name='email' /> <br/><br/></li>

				        <img src='include/common/php-captcha.php'>
				        <li><input name='formKey' type='text' id='formKey' /> Enter the verification code in the image</li>
	
				        <br/>
				        <input type='submit' name='submit' value='Register' /> <br/>
				<ul>
				</form>
				";
		}


		switch ($status) {
			case "firstload":
				showForm();
				break;


			case "success":
				echo "<font color='blue'>Success</font><br/><br/>".
					$configValues['CONFIG_SIGNUP_SUCCESS_MSG_HEADER']."<b>".$signupFirstname."</b>,<br/><br/>".
					$configValues['CONFIG_SIGNUP_SUCCESS_MSG_BODY'].
					"<ul><li>Username: <b>$username</b></li><li>Password: <b>$password</b><br/></li></ul>".
					$configValues['CONFIG_SIGNUP_SUCCESS_MSG_LOGIN_LINK'];
				break;


			case "databaseFailure":
                                echo "<font color='red'>Signup could not be completed</font>";
                                showForm();
                                break;

                        case "fieldsFailure":
                                echo "<font color='red'>".$configValues['CONFIG_SIGNUP_FAILURE_MSG_FIELDS']."</font><br/><br/>";
				showForm();
				break;


			case "captchaFailure":
                                echo "<font color='red'>".$configValues['CONFIG_SIGNUP_FAILURE_MSG_CAPTCHA']."</font><br/><br/>";
				showForm();
				break;

		}


	?>



	</p>
      </div>

      <h2>News</h2>
      <p><img src="images/dog.jpg" alt="Dog Template" width="92" height="129" align="left" style="margin-right:10px;margin-bottom:10px;" />
		daloRADIUS has released a new captive portal template which provides solutions for Free Sign-Up, PayPal Sign-Up with automatic
		provisioning in daloRADIUS's database server and a custom Hotspot Login/Welcome page.
      </p>
    </div>
    <div id="right-column">
      <div id="main-image"><img src="images/lady.jpg" alt="I love Pets" width="153" height="222" /></div>
      <div class="sidebar">

        <h3>About daloRADIUS</h3>
	<p>
		daloRADIUS is an advanced RADIUS web management application aimed at managing hotspots and
		general-purpose ISP deployments. It features user management, graphical reporting, accounting,
		a billing engine and integrates with GoogleMaps for geo-locating.		
	</p>
        <h3>Resources</h3>
        <div class="box">
          <ul>
            <li><a href="http://www.daloradius.com" target="_blank">daloRADIUS Official homepage</a></li>
            <li><a href="http://daloradius.wiki.sourceforge.net/" target="_blank">daloRADIUS Wiki</a></li>
          </ul>
        </div><a href="http://www.web-designers-directory.org/"></a><a href="http://www.medicine-pet.com/"></a>
      </div>
    </div>
  </div>
  <div id="footer">Copyright &copy; 2008 Liran Tal and daloRADIUS Project, All rights reserved.<br />
    <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a>  |  <a href="http://jigsaw.w3.org/css-validator/check/referer?warning=no&amp;profile=css2" target="_blank">CSS</a>  - Thanks to: <a href="http://www.medicine-pet.com/" target="_blank">Pet Medicine</a> | <span class="crd"><a href="http://www.web-designers-directory.org/">Web site Design</a></span> by : <a href="http://www.web-designers-directory.org/" target="_blank">WDD</a></div>
</div>

</body>
</html>
