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
 * Description:
 *              daloRADIUS Paypal registration codebase
 *
 * Modification Date:
 *              Sat Sep 13 03:14:23 EDT 2008
 *********************************************************************************************************
 */


$configValues['CONFIG_DB_ENGINE'] = 'mysql';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = '3306';
$configValues['CONFIG_DB_USER'] = 'dbuser';
$configValues['CONFIG_DB_PASS'] = 'dbpass';
$configValues['CONFIG_DB_NAME'] = 'radius';
$configValues['CONFIG_DB_TBL_RADCHECK'] = 'radcheck';
$configValues['CONFIG_DB_TBL_RADREPLY'] = 'radreply';
$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = 'radgroupreply';
$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = 'radgroupcheck';
$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'radusergroup';
$configValues['CONFIG_DB_TBL_RADNAS'] = 'nas';
$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = 'radpostauth';
$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';
$configValues['CONFIG_DB_TBL_RADIPPOOL'] = 'radippool';
$configValues['CONFIG_DB_TBL_DALOOPERATORS'] = 'operator';
$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = 'operators_acl';
$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] = 'operators_acl_files';
$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = 'rates';
$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = 'hotspots';
$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
$configValues['CONFIG_DB_TBL_DALODICTIONARY'] = 'dictionary';
$configValues['CONFIG_DB_TBL_DALOREALMS'] = 'realms';
$configValues['CONFIG_DB_TBL_DALOPROXYS'] = 'proxys';
$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = 'userbillinfo';
$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = 'billing_paypal';
$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'] = 'billing_merchant';
$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';
$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] = 'billing_history';
$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'] = 'batch_history';
$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'] = 'billing_plans_profiles';
$configValues['CONFIG_LANG'] = 'en';
$configValues['CONFIG_MERCHANT_IPN_SECRET'] = '';
$configValues['CONFIG_MERCHANT_WEB_PAYMENT'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
$configValues['CONFIG_MERCHANT_IPN_URL_ROOT'] = 'https://portal.daloradius.com/signup-paypal';
$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_DIR'] = 'paypal-ipn.php';
$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_SUCCESS'] = 'success.php';
$configValues['CONFIG_MERCHANT_IPN_URL_RELATIVE_FAILURE'] = 'cancelled.php';
$configValues['CONFIG_MERCHANT_BUSINESS_ID'] = 'liran.tal@gmail.com';
$configValues['CONFIG_LOG_MERCHANT_IPN_FILENAME'] = '/tmp/paypal-transactions.csv';
$configValues['CONFIG_MERCHANT_SUCCESS_MSG_PRE'] = "Dear customer, we thank you for completing your PayPal payment.<br/><br/>".
                        "It takes a couple of seconds until PayPal performs payment validation with our systems ".
                        "which upon successful validation we will <b>enable</b> your account and provide you with access.<br/><br/>".
                        "Please be patient, this web page will refresh automatically every 5 seconds to check for payment completion";
$configValues['CONFIG_MERCHANT_SUCCESS_MSG_POST'] = "Please write this down carefully<br/>".
                                        " Enter this as your username on the login page and leave the password blank";
$configValues['CONFIG_MERCHANT_SUCCESS_MSG_HEADER'] = "Thanks for paying!<br/>";
$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
$configValues['CONFIG_USERNAME_LENGTH'] = "8";		/* the length of the random username to generate */
$configValues['CONFIG_PASSWORD_LENGTH'] = "8";		/* the length of the random password to generate */


?>
