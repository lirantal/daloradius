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

	require_once(dirname(__FILE__)."/../../notifications/processNotificationWelcome.php");
	
	if (!empty($email))
		$invoice_email = $email;
	else if (!empty($bi_emailinvoice))
		$invoice_email = $bi_emailinvoice;
	else if (!empty($bi_email))
		$invoice_email = $bi_email;
	else
		$invoice_email = "";
	
	if (!empty($mobilephone))
		$invoice_phone = $mobilephone;
	else if (!empty($workphone))
		$invoice_phone = $workphone;
	else if (!empty($homephone))
		$invoice_phone = $homephone;
	else
		$invoice_phone = "Unavailable";
		
	$invoice_address = "";
	if (!empty($ui_address))
		$invoice_address = $ui_address;
	
	if (!empty($ui_city))
		$invoice_address .= ", ".$ui_city;
	
	if (!empty($ui_state))
		$invoice_address .= "<br/>".$ui_state;
	
	if (!empty($ui_zip))
		$invoice_address .= " ".$ui_zip;
	
	if (empty($invoice_address))
		$invoice_address = "Unavailable";
	
	$customerInfo = array();
	$customerInfo['customer_name'] = $firstname ." ".$lastname;
	$customerInfo['customer_address'] = $invoice_address;
	
	$customerInfo['customer_phone'] = $invoice_phone;
	$customerInfo['customer_email'] = $invoice_email;
	$customerInfo['service_plan_name'] = $planName;
	
	$smtpInfo['host'] = $configValues['CONFIG_MAIL_SMTPADDR'];
	$smtpInfo['port'] = $configValues['CONFIG_MAIL_SMTPPORT'];
	$smtpInfo['auth'] = $configValues['CONFIG_MAIL_SMTPAUTH'];
	$from = $configValues['CONFIG_MAIL_SMTPFROM'];
	
	@sendWelcomeNotification($customerInfo, $smtpInfo, $from);

?>