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
 * Description:    English language file
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/en.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$year = date('Y');
if ($year > 2023) {
    $year = "2023-$year";
}
$l['all']['copyright2'] = <<<EOF
<a target="_blank" href="https://github.com/filippolauria/daloradius">daloRADIUS</a><br>
Copyright &copy; 2007-2022 Liran Tal, Filippo Lauria $year.
EOF;

$l['all']['Amount'] = "Amount";
$l['all']['Balance'] = "Balance";
$l['all']['ClientName'] = "Client Name";
$l['all']['Date'] = "Date";
$l['all']['Download'] = "Download";
$l['all']['EndingDate'] = "Ending Date";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "Invoice";
$l['all']['InvoiceStatus'] = "Invoice Status";
$l['all']['InvoiceType'] = "Invoice Type";
$l['all']['IPAddress'] = "IP Address";
$l['all']['Language'] = "Language";
$l['all']['NASIPAddress'] = "NAS IP Address";
$l['all']['NewPassword'] = "New Password";
$l['all']['Password'] = "Password";
$l['all']['PaymentDate'] = "Date";
$l['all']['StartingDate'] = "Starting Date";
$l['all']['StartTime'] = "Start Time";
$l['all']['Statistics'] = "Statistics";
$l['all']['Status'] = "Status";
$l['all']['StopTime'] = "Stop Time";
$l['all']['Termination'] = "Termination";
$l['all']['TotalBilled'] = "Total Billed";
$l['all']['TotalPaid'] = "Total Paid";
$l['all']['TotalTime'] = "Total Time";
$l['all']['Upload'] = "Upload";
$l['all']['Username'] = "Username";
$l['all']['CurrentPassword'] = "Current Password";
$l['all']['VerifyPassword'] = "Verify Password";

$l['all']['Global'] = "Global";
$l['all']['Daily'] = "Daily";
$l['all']['Weekly'] = "Weekly";
$l['all']['Monthly'] = "Monthly";
$l['all']['Yearly'] = "Yearly";

$l['button']['Accounting'] = "Accounting";
$l['button']['ChangeAuthPassword'] = "Change Auth Password";
$l['button']['ChangePortalPassword'] = "Change Portal Password";
$l['button']['DateAccounting'] = "Date Accounting";
$l['button']['EditUserInfo'] = "Edit Contact Information";
$l['button']['GenerateReport'] = "Generate Report";
$l['button']['Graphs'] = "Graphs";
$l['button']['Preferences'] = "Preferences";
$l['button']['ShowInvoice'] = "Show Invoice";

$l['button']['UserDownloads'] = "Download Traffic";
$l['button']['UserLogins'] = "Login History";
$l['button']['UserUploads'] = "Upload Traffic";

$l['ContactInfo']['Address'] = "Address";
$l['ContactInfo']['City'] = "City";
$l['ContactInfo']['Company'] = "Organization";
$l['ContactInfo']['Country'] = "Country";
$l['ContactInfo']['Department'] = "Operational Unit";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['FirstName'] = "First Name";
$l['ContactInfo']['HomePhone'] = "Home Phone";
$l['ContactInfo']['LastName'] = "Last Name";
$l['ContactInfo']['MobilePhone'] = "Mobile Phone";
$l['ContactInfo']['Notes'] = "Notes";
$l['ContactInfo']['State'] = "State/Region";
$l['ContactInfo']['WorkPhone'] = "Work Phone";
$l['ContactInfo']['Zip'] = "Zip";

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Date Accounting</h2>
<p>Provides detailed accounting information for all sessions between two specified dates for a particular user.</p>
EOF;
$l['helpPage']['acctmain'] = '<h1 class="fs-5">General Accounting</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>Generates a graphical chart showing the amount of data you have downloaded over a given period of time.<br>
The chart is accompanied by a table listing.</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>Generates a graphical chart showing the amount of data you have uploaded over a given period of time.<br>
The chart is accompanied by a table listing.</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>Generates a graphical chart showing your login activity over a given period of time.<br>
The chart displays the number of logins (or 'hits' to the NAS) and is accompanied by a table listing.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Graphs</h1>'
                            . $l['helpPage']['graphsoveralllogins'] . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>Dear User,</p>
<p>Welcome to the Users Portal. We are glad you joined us!</p>

<p>By logging in with your account username and password, you will be able to access a wide range of features. For example, you can easily edit your contact settings, update your personal information, and view some history data through visual graphs.</p>

<p>We take your privacy and security seriously, so please rest assured that all your data is stored securely in our database and is accessible only to you and our authorized staff.</p>

<p>If you need any assistance or have any questions, please do not hesitate to contact our support team. We are always happy to help!</p>

<p>Regards,<br/>
The FiloRADIUS Staff.</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Cannot Log In</h1>
<p>If you are having trouble logging in to your account, it is likely that you have entered the wrong username and/or password. Please ensure that you have correctly entered your login credentials and try again.</p>
<p>If you are still unable to log in after verifying your information, please don't hesitate to contact our support team for assistance. We're always here to help you regain access to your account and get back to using our services as quickly as possible.</p>
EOF;

$l['helpPage']['prefmain'] = "In this section, you can manage your <strong>contact information</strong> as well as the login passwords for the web portal and our services.";
$l['helpPage']['prefpasswordedit'] = "Use the form below to change your password. For security reasons, you will be asked to enter your old password and to enter the new password twice to avoid errors.";
$l['helpPage']['prefuserinfoedit'] = "Use the form below to update your contact information. You can change your first name, last name, email address, phone numbers, and other details as needed. Make sure to review the changes before saving to ensure the accuracy of your updated information.";

$l['Intro']['acctdate.php'] = "Date Sort Accounting";
$l['Intro']['acctmain.php'] = "Accounting Page";
$l['Intro']['billinvoiceedit.php'] = "Showing Invoice";
$l['Intro']['billinvoicereport.php'] = "Invoice Report";
$l['Intro']['billmain.php'] = "Billing Page";
$l['Intro']['graphmain.php'] = "Usage Graphs";
$l['Intro']['graphsoveralldownload.php'] = "User Downlads";
$l['Intro']['graphsoveralllogins.php'] = "User Logins";
$l['Intro']['graphsoverallupload.php'] = "User Uploads";
$l['Intro']['prefmain.php'] = "Preferences Page";
$l['Intro']['prefpasswordedit.php'] = "Change Password";
$l['Intro']['prefuserinfoedit.php'] = "Change User Information";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Billing";
$l['menu']['Graphs'] = "Graphs";
$l['menu']['Home'] = "Home";
$l['menu']['Preferences'] = "Preferences";
$l['menu']['Help'] = "Help";


$l['text']['LoginPlease'] = "Login Please";
$l['text']['LoginRequired'] = "Login Required";
$l['title']['ContactInfo'] = "Contact Info";
$l['title']['BusinessInfo'] = "Business Info";
$l['title']['Invoice'] = "Invoice";
$l['title']['Items'] = "Items";
$l['Tooltip']['invoiceID'] = "Type the invoice id";
