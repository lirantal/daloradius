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
 * Description:
 *              daloRADIUS Paypal registration codebase
 *
 * Modification Date:
 *              Sat Sep 13 03:14:23 EDT 2008
 *********************************************************************************************************
 */


$configValues['CONFIG_DB_ENGINE'] = 'mysql';
$configValues['CONFIG_DB_HOST'] = '127.0.0.1';
$configValues['CONFIG_DB_USER'] = 'root';
$configValues['CONFIG_DB_PASS'] = '';
$configValues['CONFIG_DB_NAME'] = 'radius097';
$configValues['CONFIG_DB_TBL_RADCHECK'] = 'radcheck';
$configValues['CONFIG_DB_TBL_RADREPLY'] = 'radreply';
$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = 'radgroupreply';
$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = 'radgroupcheck';
$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'usergroup';
$configValues['CONFIG_DB_TBL_RADNAS'] = 'nas';
$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = 'radpostauth';
$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';
$configValues['CONFIG_DB_TBL_RADIPPOOL'] = 'radippool';
$configValues['CONFIG_DB_TBL_DALOOPERATOR'] = 'operators';
$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = 'rates';
$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = 'hotspots';
$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
$configValues['CONFIG_DB_TBL_DALODICTIONARY'] = 'dictionary';
$configValues['CONFIG_DB_TBL_DALOREALMS'] = 'realms';
$configValues['CONFIG_DB_TBL_DALOPROXYS'] = 'proxys';
$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = 'billing_paypal';
$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';
$configValues['CONFIG_LANG'] = 'en';
$configValues['CONFIG_LOG_FREE_SIGNUP_FILENAME'] = '/tmp/free-signup.log';
$configValues['CONFIG_SIGNUP_MSG_TITLE'] = "We provide free registration service to our hotspots. <br/>".
					"Complete the form and click the Register button to generate a username and password.";
$configValues['CONFIG_SIGNUP_SUCCESS_MSG_HEADER'] = "Welcome to our Hotspot";
$configValues['CONFIG_SIGNUP_SUCCESS_MSG_BODY'] = "we have created a username and password for you to use <br/>".
					" to login to our system, and they are as follows:<br/><br/>";
$configValues['CONFIG_SIGNUP_SUCCESS_MSG_LOGIN_LINK'] = "<br/>Click <b><a href='http://192.168.182.1:3990/prelogin'>here</a></b>".
					" to return to the Login page and start your surfing<br/><br/>";
$configValues['CONFIG_SIGNUP_FAILURE_MSG_FIELDS'] = "You didn't fill in your first and last name, please fill-in the form again";
$configValues['CONFIG_SIGNUP_FAILURE_MSG_CAPTCHA'] = "The image verification code is in-correct, please try again";


$configValues['CONFIG_GROUP_NAME'] = "somegroup";       /* the group name to add the user to */
$configValues['CONFIG_GROUP_PRIORITY'] = 0;             /* an integer only! */
$configValues['CONFIG_USERNAME_PREFIX'] = "GST_";	/* username prefix to append to the automatically generated username */
$configValues['CONFIG_USERNAME_LENGTH'] = "4";		/* the length of the random username to generate */
$configValues['CONFIG_PASSWORD_LENGTH'] = "4";		/* the length of the random password to generate */

?>
