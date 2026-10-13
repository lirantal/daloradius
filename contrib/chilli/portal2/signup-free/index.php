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
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body onLoad="return setFocus();">

<div id="wrap">

                <div class="header"><p>Hotspot<span>Login</span><sup>
                        By <a href="http://templatefusion.org">TemplateFusion.org</a></sup></p>
                </div>

        <div id="navigation">
                <ul class="glossymenu">
                        <li><a href="index.php" class="current"><b>Home</b></a></li>
                        <li><a href="#"><b>Services</b></a></li>
                        <li><a href="#"><b>About Us</b></a></li>
                        <li><a href="#"><b>Contact</b></a></li>
                </ul>
        </div>

        <div id="body">
                <h1>Sign-Up</h1>
                <p>
                        <center>

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

                        echo "  <b>
				".$configValues['CONFIG_SIGNUP_MSG_TITLE']."
				</b>

                                <br/><br/>
                                <form name='signup' action='".htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."' method='post'>" . dalo_chilli_signup_csrf_field() . "

                                <table>
                                        <tr><td><b>First name:</b></td><td> <input type='text' value='' name='firstname' /> </td></tr>
                                        <tr><td><b>Last name:</b></td><td> <input type='text' value='' name='lastname' /> </td></tr>
                                        <tr><td><b>Email address:</b></td><td> <input type='text' value='' name='email' /> </td></tr>

                                        <tr><td><b>Enter the verification code in the image:</b> <img src='include/common/php-captcha.php'></td>
                                        <td><input name='formKey' type='text' id='formKey' /></td></tr>
				</table>
				<br/><br/>

                                        <tr><td><input type='submit' name='submit' value='Register' /> </td></tr>
				<br/><br/>
                                </form>
                                ";
                }


                switch ($status) {
                        case "firstload":
                                showForm();
                                break;


                        case "success":
                                echo "<font color='blue'><b>Success</b><br/><br/>".
                                        $configValues['CONFIG_SIGNUP_SUCCESS_MSG_HEADER']."<b>".$signupFirstname."</b>,<br/><br/>".
					$configValues['CONFIG_SIGNUP_SUCCESS_MSG_BODY']."<table>"
                                        ."<tr><td>Username:</td><td><b>$username</b></td></tr><tr><td>Password:</td><td><b>$password</b></td></tr>"
					."</table>".$configValues['CONFIG_SIGNUP_SUCCESS_MSG_LOGIN_LINK']
					."</font>";

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
                                echo "<font color='red'><b>".$configValues['CONFIG_SIGNUP_FAILURE_MSG_CAPTCHA']."</b></font><br/><br/>";
                                showForm();
                                break;

                }


        ?>



                        </center>
                </p>


                <h1>Hotspot References</h1>
                <a href="#"><img src="images/portfolio1.jpg" alt="portfolio1" /></a>
                <a href="#"><img src="images/portfolio2.jpg" alt="portfolio2" /></a>
                <a href="#"><img src="images/portfolio3.jpg" alt="portfolio3" /></a>
                <a href="#"><img src="images/portfolio4.jpg" alt="portfolio4" /></a>
        </div>



        <div id="footer">Enginx&copy;2008 All Rights Reserved &bull; Enginx and daloRADIUS Hotspot Systems <br/>
                Design by <a href="http://templatefusion.org">TemplateFusion</a>
        </div>


</div>

</body>
</html>

