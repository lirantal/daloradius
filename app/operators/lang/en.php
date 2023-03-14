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
 * Description:    English language file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/en.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS Management, Reporting, Accounting and Billing by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Pool Name";
$l['all']['CalledStationId'] = "CalledStationId";
$l['all']['CallingStationID'] = "CallingStationID";
$l['all']['ExpiryTime'] = "Expiry Time";
$l['all']['PoolKey'] = "Pool Key";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Dictionary";
$l['all']['VendorID'] = "Vendor ID";
$l['all']['VendorName'] = "Vendor Name";
$l['all']['VendorAttribute'] = "Vendor Attribute";
$l['all']['RecommendedOP'] = "Recommended OP";
$l['all']['RecommendedTable'] = "Recommended Table";
$l['all']['RecommendedTooltip'] = "Recommended Tooltip";
$l['all']['RecommendedHelper'] = "Recommended Helper";
/********************************************************************************/

$l['all']['CSVData'] = "CSV-formatted data";

$l['all']['CPU'] = "CPU";

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "RADIUS Dictionary Path";


$l['all']['DashboardSecretKey'] = "Dashboard Secret Key";
$l['all']['DashboardDebug'] = "Debug";
$l['all']['DashboardDelaySoft'] = "Time in minutes to consider a 'soft' delay limit";
$l['all']['DashboardDelayHard'] = "Time in minutes to consider a 'hard' delay limit";



$l['all']['SendWelcomeNotification'] = "Send Welcome Notification";
$l['all']['SMTPServerAddress'] = "SMTP Server Address";
$l['all']['SMTPServerPort'] = "SMTP Server Port";
$l['all']['SMTPServerFromEmail'] = "From Email Address";

$l['all']['customAttributes'] = "Custom Attributes";

$l['all']['UserType'] = "User Type";

$l['all']['BatchName'] = "Batch Name";
$l['all']['BatchStatus'] = "Batch Status";

$l['all']['Users'] = "Users";

$l['all']['Compare'] = "Compare";
$l['all']['Never'] = "Never";


$l['all']['Section'] = "Section";
$l['all']['Item'] = "Item";

$l['all']['Megabytes'] = "Megabytes";
$l['all']['Gigabytes'] = "Gigabytes";

$l['all']['Daily'] = "Daily";
$l['all']['Weekly'] = "Weekly";
$l['all']['Monthly'] = "Monthly";
$l['all']['Yearly'] = "Yearly";

$l['all']['Month'] = "Month";

$l['all']['RemoveRadacctRecords'] = "Remove Accounting Records";

$l['all']['CleanupSessions'] = "Cleanup sessions older than";
$l['all']['DeleteSessions'] = "Delete sessions older than";

$l['all']['StartingDate'] = "Starting Date";
$l['all']['EndingDate'] = "Ending Date";

$l['all']['Realm'] = "Realm";
$l['all']['RealmName'] = "Realm Name";
$l['all']['RealmSecret'] = "Realm Secret";
$l['all']['AuthHost'] = "Auth Host";
$l['all']['AcctHost'] = "Acct Host";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "hints";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy Name";
$l['all']['ProxySecret'] = "Proxy Secret";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";


$l['all']['Firmware'] = "Firmware";
$l['all']['NASMAC'] = "NAS MAC";

$l['all']['WanIface'] = "Wan Iface";
$l['all']['WanMAC'] = "Wan MAC";
$l['all']['WanIP'] = "Wan IP";
$l['all']['WanGateway'] = "Wan Gateway";

$l['all']['LanIface'] = "Lan Iface";
$l['all']['LanMAC'] = "Lan MAC";
$l['all']['LanIP'] = "Lan IP";

$l['all']['WifiIface'] = "Wifi Iface";
$l['all']['WifiMAC'] = "Wifi MAC";
$l['all']['WifiIP'] = "Wifi IP";

$l['all']['WifiSSID'] = "Wifi SSID";
$l['all']['WifiKey'] = "Wifi Key";
$l['all']['WifiChannel'] = "Wifi Channel";

$l['all']['CheckinTime'] = "Last Checked-In";

$l['all']['FramedIPAddress'] = "Framed-IP-Address";
$l['all']['SimultaneousUse'] = "Simultaneous-Use";
$l['all']['HgID'] = "HG ID";
$l['all']['Hg'] = "HG ";
$l['all']['HgIPHost'] = "HG IP/Host";
$l['all']['HgGroupName'] = "HG GroupName";
$l['all']['HgPortId'] = "HG Port Id";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "NAS Shortname";
$l['all']['NasType'] = "NAS Type";
$l['all']['NasPorts'] = "NAS Ports";
$l['all']['NasSecret'] = "NAS Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "NAS Description";
$l['all']['PacketType'] = "Packet Type";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['HotSpots'] = "HotSpots";
$l['all']['HotSpotName'] = "Hotspot Name";
$l['all']['Name'] = "Name";
$l['all']['Username'] = "Username";
$l['all']['Password'] = "Password";
$l['all']['PasswordType'] = "Password Type";
$l['all']['IPAddress'] = "IP Address";
$l['all']['Profile'] = "Profile";
$l['all']['Group'] = "Group";
$l['all']['Groupname'] = "Groupname";
$l['all']['ProfilePriority'] = "Profile Priority";
$l['all']['GroupPriority'] = "Group Priority";
$l['all']['CurrentGroupname'] = "Current Groupname";
$l['all']['NewGroupname'] = "New Groupname";
$l['all']['Priority'] = "Priority";
$l['all']['Attribute'] = "Attribute";
$l['all']['Operator'] = "Operator";
$l['all']['Value'] = "Value";
$l['all']['NewValue'] = "New Value";
$l['all']['MaxTimeExpiration'] = "Max Time / Expiration";
$l['all']['UsedTime'] = "Used Time";
$l['all']['Status'] = "Status";
$l['all']['Usage'] = "Usage";
$l['all']['StartTime'] = "Start Time";
$l['all']['StopTime'] = "Stop Time";
$l['all']['TotalTime'] = "Total Time";
$l['all']['TotalTraffic'] = "Total Traffic";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Upload";
$l['all']['Download'] = "Download";
$l['all']['Rollback'] = "Roll-back";
$l['all']['Termination'] = "Termination";
$l['all']['NASIPAddress'] = "NAS IP Address";
$l['all']['NASShortName'] = "NAS Short Name";
$l['all']['Action'] = "Action";
$l['all']['UniqueUsers'] = "Unique Users";
$l['all']['TotalHits'] = "Total Hits";
$l['all']['AverageTime'] = "Average Time";
$l['all']['Records'] = "Records";
$l['all']['Summary'] = "Summary";
$l['all']['Statistics'] = "Statistics";
$l['all']['Credit'] = "Credit";
$l['all']['Used'] = "Used";
$l['all']['LeftTime'] = "Time Remains";
$l['all']['LeftPercent'] = "% of Time Left";
$l['all']['TotalSessions'] = "Total Sessions";
$l['all']['LastLoginTime'] = "Last Login Time";
$l['all']['TotalSessionTime'] = "Total Session Time";
$l['all']['RateName'] = "Rate Name";
$l['all']['RateType'] = "Rate Type";
$l['all']['RateCost'] = "Rate Cost";
$l['all']['Billed'] = "Billed";
$l['all']['TotalUsers'] = "Total Users";
$l['all']['ActiveUsers'] = "Active Users";
$l['all']['TotalBilled'] = "Total Billed";
$l['all']['TotalPayed'] = "Total Paid";
$l['all']['Balance'] = "Balance";
$l['all']['CardBank'] = "Card Bank";
$l['all']['Type'] = "Type";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "MAC Address";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN Code";
$l['all']['CreationDate'] = "Creation Date";
$l['all']['CreationBy'] = "Creation By";
$l['all']['UpdateDate'] = "Update Date";
$l['all']['UpdateBy'] = "Update By";

$l['all']['Discount'] = "Discount";
$l['all']['BillAmount'] = "Billed Amount";
$l['all']['BillAction'] = "Billed Action";
$l['all']['BillPerformer'] = "Bill Performer";
$l['all']['BillReason'] = "Billing Reason";
$l['all']['Lead'] = "Lead";
$l['all']['Coupon'] = "Coupon";
$l['all']['OrderTaker'] = "Order Taker";
$l['all']['BillStatus'] = "Bill Status";
$l['all']['LastBill'] = "Last Bill";
$l['all']['NextBill'] = "Next Bill";
$l['all']['BillDue'] = "Bill Due";
$l['all']['NextInvoiceDue'] = "Next Invoice Due";
$l['all']['PostalInvoice'] = "Postal Invoice";
$l['all']['FaxInvoice'] = "Fax Invoice";
$l['all']['EmailInvoice'] = "Email Invoice";

$l['all']['ClientName'] = "Client Name";
$l['all']['Date'] = "Date";

$l['all']['edit'] = "edit";
$l['all']['del'] = "Delete";
$l['all']['groupslist'] = "groups-list";
$l['all']['TestUser'] = "Test User";
$l['all']['Accounting'] = "Accounting";
$l['all']['RADIUSReply'] = "RADIUS Reply";

$l['all']['Disconnect'] = "Disconnect";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Retries";
$l['all']['Count'] = "Count";
$l['all']['Requests'] = "Requests";

$l['all']['DatabaseHostname'] = "Database Hostname";
$l['all']['DatabasePort'] = "Database Port Number";
$l['all']['DatabaseUser'] = "Database User";
$l['all']['DatabasePass'] = "Database Pass";
$l['all']['DatabaseName'] = "Database Name";

$l['all']['PrimaryLanguage'] = "Primary Language";

$l['all']['PagesLogging'] = "Logging of Pages (page visits)";
$l['all']['QueriesLogging'] = "Logging of Queries (reports and graphs)";
$l['all']['ActionsLogging'] = "Logging of Actions (form submits)";
$l['all']['FilenameLogging'] = "Logging filename (full path)";
$l['all']['LoggingDebugOnPages'] = "Logging of Debug info on pages";
$l['all']['LoggingDebugInfo'] = "Logging of Debug Info";

$l['all']['PasswordHidden'] = "Enable Password Hiding (asterisk will be shown)";
$l['all']['TablesListing'] = "Rows/Records per Tables Listing page";
$l['all']['TablesListingNum'] = "Enable Tables Listing Numbering";
$l['all']['AjaxAutoComplete'] = "Enable Ajax Auto-Complete";

$l['all']['RadiusServer'] = "Radius Server";
$l['all']['RadiusPort'] = "Radius Port";

$l['all']['UsernamePrefix'] = "Username Prefix";

$l['all']['batchName'] = "Batch Id/Name";
$l['all']['batchDescription'] = "Batch Description";

$l['all']['NumberInstances'] = "Number of instances to create";
$l['all']['UsernameLength'] = "Length of username string";
$l['all']['PasswordLength'] = "Length of password string";

$l['all']['Expiration'] = "Expiration";
$l['all']['MaxAllSession'] = "Max-All-Session";
$l['all']['SessionTimeout'] = "Session Timeout";
$l['all']['IdleTimeout'] = "Idle Timeout";

$l['all']['DBEngine'] = "DB Engine";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "usergroup";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "operators";
$l['all']['operators_acl'] = "operators_acl";
$l['all']['operators_acl_files'] = "operators_acl_files";
$l['all']['billingrates'] = "billing rates";
$l['all']['hotspots'] = "hotspots";
$l['all']['node'] = "node";
$l['all']['nas'] = "nas";
$l['all']['hunt'] = "radhuntgroup";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "radippool";
$l['all']['userinfo'] = "userinfo";
$l['all']['dictionary'] = "dictionary";
$l['all']['realms'] = "realms";
$l['all']['proxys'] = "proxys";
$l['all']['billingpaypal'] = "billing paypal";
$l['all']['billingmerchant'] = "billing merchant";
$l['all']['billingplans'] = "billing plans";
$l['all']['billinghistory'] = "billing history";
$l['all']['billinginfo'] = "billing user info";


$l['all']['CreateIncrementingUsers'] = "Create Incrementing Users";
$l['all']['CreateRandomUsers'] = "Create Random Users";
$l['all']['StartingIndex'] = "Starting Index";
$l['all']['EndingIndex'] = "Ending Index";
$l['all']['RandomChars'] = "Allowed Random Characters";
$l['all']['Memfree'] = "Memory Free";
$l['all']['Uptime'] = "Uptime";
$l['all']['BandwidthUp'] = "Bandwidth Up";
$l['all']['BandwidthDown'] = "Bandwidth Down";

$l['all']['BatchCost'] = "Batch Cost";

$l['all']['PaymentDate'] = "Payment Date";
$l['all']['PaymentStatus'] = "Payment Status";
$l['all']['FirstName'] = "First name";
$l['all']['LastName'] = "Last name";
$l['all']['VendorType'] = "Merchant Vendor";
$l['all']['PayerStatus'] = "Payer Status";
$l['all']['PaymentAddressStatus'] = "Payment Address Status";
$l['all']['PayerEmail'] = "Payer Email";
$l['all']['TxnId'] = "Tranasction Id";
$l['all']['PlanActive'] = "Plan Active";
$l['all']['PlanTimeType'] = "Plan Time Type";
$l['all']['PlanTimeBank'] = "Plan Time Bank";
$l['all']['PlanTimeRefillCost'] = "Plan Refill Cost";
$l['all']['PlanTrafficRefillCost'] = "Plan Refill Cost";
$l['all']['PlanBandwidthUp'] = "Plan Bandwidth Up";
$l['all']['PlanBandwidthDown'] = "Plan Bandwidth Down";
$l['all']['PlanTrafficTotal'] = "Plan Traffic Total";
$l['all']['PlanTrafficDown'] = "Plan Traffic Down";
$l['all']['PlanTrafficUp'] = "Plan Traffic Up";
$l['all']['PlanRecurring'] = "Plan Recurring";
$l['all']['PlanRecurringPeriod'] = "Plan Recurring Period";
$l['all']['planRecurringBillingSchedule'] = "Plan Recurring Billing Schedule";
$l['all']['PlanCost'] = "Plan Cost";
$l['all']['PlanSetupCost'] = "Plan Setup Cost";
$l['all']['PlanTax'] = "Plan Tax";
$l['all']['PlanCurrency'] = "Plan Currency";
$l['all']['PlanGroup'] = "Plan Profile (Group)";
$l['all']['PlanType'] = "Plan Type";
$l['all']['PlanName'] = "Plan Name";
$l['all']['PlanId'] = "Plan Id";

$l['all']['UserId'] = "User Id";

$l['all']['Invoice'] = "Invoice";
$l['all']['InvoiceID'] = "Invoice ID";
$l['all']['InvoiceItems'] = "Invoice Items";
$l['all']['InvoiceStatus'] = "Invoice Status";

$l['all']['InvoiceType'] = "Invoice Type";
$l['all']['Amount'] = "Amount";
$l['all']['Total'] = "Total";
$l['all']['TotalInvoices'] = "Total Invoices";

$l['all']['PayTypeName'] = "Payment Type Name";
$l['all']['PayTypeNotes'] = "Payment Type Description";
$l['all']['payment_type'] = "payment types";
$l['all']['payments'] = "payments";
$l['all']['PaymentId'] = "Payment ID";
$l['all']['PaymentInvoiceID'] = "Invoice ID";
$l['all']['PaymentAmount'] = "Amount";
$l['all']['PaymentDate'] = "Date";
$l['all']['PaymentType'] = "Payment Type";
$l['all']['PaymentNotes'] = "Payment Notes";


$l['all']['Quantity'] = "Quantity";
$l['all']['ReceiverEmail'] = "Receiver Email";
$l['all']['Business'] = "Business";
$l['all']['Tax'] = "Tax";
$l['all']['Cost'] = "Cost";
$l['all']['TotalCost'] = "Total Cost";
$l['all']['TransactionFee'] = "Transaction Fee";
$l['all']['PaymentCurrency'] = "Payment Currency";
$l['all']['AddressRecipient'] = "Address Recipient";
$l['all']['Street'] = "Street";
$l['all']['Country'] = "Country";
$l['all']['CountryCode'] = "Country Code";
$l['all']['City'] = "City";
$l['all']['State'] = "State";
$l['all']['Zip'] = "Zip";

$l['all']['BusinessName'] = "Business Name";
$l['all']['BusinessPhone'] = "Business Phone";
$l['all']['BusinessAddress'] = "Business Address";
$l['all']['BusinessWebsite'] = "Business Website";
$l['all']['BusinessEmail'] = "Business Email";
$l['all']['BusinessContactPerson'] = "Business Contact Person";
$l['all']['DBPasswordEncryption'] = "DB Password Encryption Type";

$l['all']['Calling Station ID'] = "Calling Station ID";
$l['all']['Framed IP Address'] = "Framed IP Address";

/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "Provide an identifier name for this batch creation";
$l['Tooltip']['batchDescriptionTooltip'] = "Provide general description regarding this batch creation";

$l['Tooltip']['hotspotTooltip'] = "Choose the hotspot name of which this batch instance is associated with";

$l['Tooltip']['startingIndexTooltip'] = "Provide the starting index from which to create the user";
$l['Tooltip']['planTooltip'] = "Select a plan to associate the user with";

$l['Tooltip']['InvoiceEdit'] = "Edit Invoice";
$l['Tooltip']['invoiceTypeTooltip'] = "";
$l['Tooltip']['invoiceStatusTooltip'] = "";
$l['Tooltip']['invoiceID'] = "Type the invoice id";
$l['Tooltip']['user_idTooltip'] = "User id";

$l['Tooltip']['amountTooltip'] = "";
$l['Tooltip']['taxTooltip'] = "";

$l['Tooltip']['PayTypeName'] = "Type the Payment Type name";
$l['Tooltip']['EditPayType'] = "Edit Payment Type";
$l['Tooltip']['RemovePayType'] = "Remove Payment Type";
$l['Tooltip']['paymentTypeTooltip'] = "The payment type friendly name,<br/>
                                        to describe the purpose of the payment";
$l['Tooltip']['paymentTypeNotesTooltip'] = "The payment type description, to describe<br/>
                                        the operation of the payment type";
$l['Tooltip']['EditPayment'] = "Edit Payment";
$l['Tooltip']['PaymentId'] = "The Payment Id";
$l['Tooltip']['RemovePayment'] = "Remove Payment";
$l['Tooltip']['paymentInvoiceTooltip'] = "The invoice related to this payment";



$l['Tooltip']['Username'] = "Type in the username";
$l['Tooltip']['BatchName'] = "Type in the batch name";
$l['Tooltip']['UsernameWildcard'] = "Note: a wildcard will be automatically appended to the typed in string.";
$l['Tooltip']['HotspotName'] = "Type in the hotspot name";
$l['Tooltip']['NasName'] = "Type in the NAS name";
$l['Tooltip']['GroupName'] = "Type in the group name";
$l['Tooltip']['AttributeName'] = "Type in the attribute name";
$l['Tooltip']['VendorName'] = "Select the vendor name";
$l['Tooltip']['PoolName'] = "Type in pool name";
$l['Tooltip']['IPAddress'] = "Type in the IP address";
$l['Tooltip']['Filter'] = "Type in a filter. It can be any alphanumeric string. Leave empty to match anything.";
$l['Tooltip']['Date'] = "Select a date";
$l['Tooltip']['RateName'] = "Type in the rate name";
$l['Tooltip']['OperatorName'] = "Type in the operator name";
$l['Tooltip']['BillingPlanName'] = "Type in the billing plan name";
$l['Tooltip']['PlanName'] = "Type in the plan name";

$l['Tooltip']['EditRate'] = "Edit Rate";
$l['Tooltip']['RemoveRate'] = "Remove Rate";

$l['Tooltip']['rateNameTooltip'] = "The rate friendly name,<br/>
                    to describe the purpose of the rate";
$l['Tooltip']['rateTypeTooltip'] = "The rate type, to describe<br/>
                    the operation of the rate";
$l['Tooltip']['rateCostTooltip'] = "The rate cost amount";

$l['Tooltip']['planNameTooltip'] = "The Plan's name. This is a friendly name describing the characeristics of the plan";
$l['Tooltip']['planIdTooltip'] = "";
$l['Tooltip']['planTimeTypeTooltip'] = "";
$l['Tooltip']['planTimeBankTooltip'] = "";
$l['Tooltip']['planTimeRefillCostTooltip'] = "";
$l['Tooltip']['planTrafficRefillCostTooltip'] = "";
$l['Tooltip']['planBandwidthUpTooltip'] = "";
$l['Tooltip']['planBandwidthDownTooltip'] = "";
$l['Tooltip']['planTrafficTotalTooltip'] = "";
$l['Tooltip']['planTrafficDownTooltip'] = "";
$l['Tooltip']['planTrafficUpTooltip'] = "";

$l['Tooltip']['planRecurringTooltip'] = "";
$l['Tooltip']['planRecurringBillingScheduleTooltip'] = "";
$l['Tooltip']['planRecurringPeriodTooltip'] = "";
$l['Tooltip']['planCostTooltip'] = "";
$l['Tooltip']['planSetupCostTooltip'] = "";
$l['Tooltip']['planTaxTooltip'] = "";
$l['Tooltip']['planCurrencyTooltip'] = "";
$l['Tooltip']['planGroupTooltip'] = "";

$l['Tooltip']['EditIPPool'] = "Edit IP-Pool";
$l['Tooltip']['RemoveIPPool'] = "Remove IP-Pool";
$l['Tooltip']['EditIPAddress'] = "Edit IP Address";
$l['Tooltip']['RemoveIPAddress'] = "Remove IP Address";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Proxy name";
$l['Tooltip']['proxyRetryDelayTooltip'] = "The time (in seconds) to wait for a response from the proxy, before re-sending the proxied request.";
$l['Tooltip']['proxyRetryCountTooltip'] = "The number of retries to send before giving up, and sending a reject message to the NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "If the home server does not respond to any of the multiple retries, "
                                      . "then FreeRADIUS will stop sending it proxy requests, and mark it 'dead'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "If all exact matching realms did not respond, we can try the";
$l['Tooltip']['realmNameTooltip'] = "Realm name";
$l['Tooltip']['realmTypeTooltip'] = "Set to radius for default";
$l['Tooltip']['realmSecretTooltip'] = "Realm RADIUS shared secret";
$l['Tooltip']['realmAuthhostTooltip'] = "Realm authentication host";
$l['Tooltip']['realmAccthostTooltip'] = "Realm accounting host";
$l['Tooltip']['realmLdflagTooltip'] = "Allows for load balancing. Allowed values are 'fail_over' and 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Whether to strip or not the realm suffix";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "The name of the vendor (e.g. Cisco, Mikrotik, etc.).";
$l['Tooltip']['typeTooltip'] = "The data type of this attribute (e.g. string, integer, date, ipaddr, etc.).";
$l['Tooltip']['attributeTooltip'] = "The name of the attribute (e.g. Framed-IPAddress, Expiration, etc.).";

$l['Tooltip']['RecommendedOPTooltip'] = "The recommended operator for this attribute (e.g. :=, ==, !=, etc.).";
$l['Tooltip']['RecommendedTableTooltip'] = "The recommended target table for this attribute (e.g. check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "The text to show as a tooltip when choosing this attribute (e.g. the IP address for the user, etc.).";
$l['Tooltip']['RecommendedHelperTooltip'] = "The helper function which will be available when choosing this attribute.";



$l['Tooltip']['AttributeEdit'] = "Edit Attribute";

$l['Tooltip']['BatchDetails'] = "Batch Details";

$l['Tooltip']['UserEdit'] = "Edit User";
$l['Tooltip']['HotspotEdit'] = "Edit Hotspot";
$l['Tooltip']['EditNAS'] = "Edit NAS";
$l['Tooltip']['RemoveNAS'] = "Remove NAS";
$l['Tooltip']['EditHG'] = "Edit HuntGroup";
$l['Tooltip']['RemoveHG'] = "Remove HuntGroup";
$l['Tooltip']['hgNasIpAddress'] = "Type the Host/Ip address";
$l['Tooltip']['hgGroupName'] = "Type the Groupname for the NAS";
$l['Tooltip']['hgNasPortId'] = "Type the Nas Port Id";
$l['Tooltip']['EditUserGroup'] = "Edit User Group";
$l['Tooltip']['ListUserGroups'] = "List User Groups";
$l['Tooltip']['DeleteUserGroup'] = "Delete User Group Association";

$l['Tooltip']['EditProfile'] = "Edit Profile";

$l['Tooltip']['EditRealm'] = "Edit Realm";
$l['Tooltip']['EditProxy'] = "Edit Proxy";

$l['Tooltip']['EditGroup'] = "Edit Group";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(descriptive name)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!";


$l['Tooltip']['usernameTooltip'] = "The exact username the user will use to connect to the system";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in RADIUS.";
$l['Tooltip']['passwordTooltip'] = "Some systems use case-sensetive passwords. Take extra care!";
$l['Tooltip']['groupTooltip'] = "The user will be added to the specified group. By adding a user to a specific group they are subject to the group's attributes";
$l['Tooltip']['macaddressTooltip'] = "Example: 00:AA:BB:CC:DD:EE<br/>&nbsp;&nbsp;&nbsp;
                    The MAC Address format should be the same<br/>&nbsp;&nbsp;&nbsp;
                    as the NAS sends it. Mostly this is without<br/>&nbsp;&nbsp;&nbsp;
                    any characters.";
$l['Tooltip']['pincodeTooltip'] = "Example: khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    This is the exact pincode as the user will enter it.<br/>&nbsp;&nbsp;&nbsp;
                    You may use alpha numeric characters, case is sensituve";
$l['Tooltip']['usernamePrefixTooltip'] = "Example: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    This username prefix will be added to<br/>&nbsp;&nbsp;&nbsp;
                    the generated username finally.";
$l['Tooltip']['instancesToCreateTooltip'] = "Example: 100<br/>&nbsp;&nbsp;&nbsp;
                    The amount of random users to create<br/>&nbsp;&nbsp;&nbsp;
                    with the specified profile.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Example: 8<br/>&nbsp;&nbsp;&nbsp;
                    The characters length of the username<br/>&nbsp;&nbsp;&nbsp;
                    to be created. Recommended 8-12 chars.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Example: 8<br/>&nbsp;&nbsp;&nbsp;
                    The characters length of the password<br/>&nbsp;&nbsp;&nbsp;
                    to be created. Recommended 8-12 chars.";


$l['Tooltip']['hotspotNameTooltip'] = "Example: Hotel Stratocaster<br/>&nbsp;&nbsp;&nbsp;
                    a friendly name of the hotspot<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "Example: 00-aa-bb-cc-dd-ee<br/>&nbsp;&nbsp;&nbsp;
                    The MAC address of the NAS<br/>";

$l['Tooltip']['geocodeTooltip'] = "Example: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    This is the GooleMaps location code used<br/>&nbsp;&nbsp;&nbsp;
                    to pin the Hotspot/NAS on the map (see GIS).";

$l['Tooltip']['reassignplanprofiles'] = "If toggled on, when applying user information <br/>
                    the Profiles listed in the Profiles tab will be ignored and <br/>
                    profiles will be re-assigned based on the Plans profile association";

/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/

$l['button']['DashboardSettings'] = "Dashboard Settings";


$l['button']['GenerateReport'] = "Generate Report";

$l['button']['ListPayTypes'] = "List Payment Types";
$l['button']['NewPayType'] = "New Payment Type";
$l['button']['EditPayType'] = "Edit Payment Type";
$l['button']['RemovePayType'] = "Remove Payment Type";
$l['button']['ListPayments'] = "List Payments";
$l['button']['NewPayment'] = "New Payment";
$l['button']['EditPayment'] = "Edit Payment";
$l['button']['RemovePayment'] = "Remove Payment";

$l['button']['NewUsers'] = "New Users";

$l['button']['ClearSessions'] = "Clear Sessions";
$l['button']['Dashboard'] = "Dashboard";
$l['button']['MailSettings'] = "Mail Settings";

$l['button']['Batch'] = "Batch";
$l['button']['BatchHistory'] = "Batch History";
$l['button']['BatchDetails'] = "Batch Details";

$l['button']['ListRates'] = "List Rates";
$l['button']['NewRate'] = "New Rate";
$l['button']['EditRate'] = "Edit Rate";
$l['button']['RemoveRate'] = "Remove Rate";

$l['button']['ListPlans'] = "List Plans";
$l['button']['NewPlan'] = "New Plan";
$l['button']['EditPlan'] = "Edit Plan";
$l['button']['RemovePlan'] = "Remove Plan";

$l['button']['ListInvoices'] = "List Invoices";
$l['button']['NewInvoice'] = "New Invoice";
$l['button']['EditInvoice'] = "Edit Invoice";
$l['button']['RemoveInvoice'] = "Remove Invoice";

$l['button']['ListRealms'] = "List Realms";
$l['button']['NewRealm'] = "New Realm";
$l['button']['EditRealm'] = "Edit Realm";
$l['button']['RemoveRealm'] = "Remove Realm";

$l['button']['ListProxys'] = "List Proxys";
$l['button']['NewProxy'] = "New Proxy";
$l['button']['EditProxy'] = "Edit Proxy";
$l['button']['RemoveProxy'] = "Remove Proxy";

$l['button']['ListAttributesforVendor'] = "List Attributes for Vendor:";
$l['button']['NewVendorAttribute'] = "New Vendor Attribute";
$l['button']['EditVendorAttribute'] = "Edit Vendor's Attribute";
$l['button']['SearchVendorAttribute'] = "Search Attribute";
$l['button']['RemoveVendorAttribute'] = "Remove Vendor's Attribute";
$l['button']['ImportVendorDictionary'] = "Import Vendor Dictionary";


$l['button']['BetweenDates'] = "Between Dates:";
$l['button']['Where'] = "Where";
$l['button']['AccountingFieldsinQuery'] = "Accounting Fields in Query:";
$l['button']['OrderBy'] = "Order By";
$l['button']['HotspotAccounting'] = "Hotspot Accounting";
$l['button']['HotspotsComparison'] = "Hotspots Comparison";

$l['button']['CleanupStaleSessions'] = "Cleanup Stale Sessions";
$l['button']['DeleteAccountingRecords'] = "Delete Accounting Records";

$l['button']['ListUsers'] = "List Users";
$l['button']['ListBatches'] = "List Batches";
$l['button']['RemoveBatch'] = "Remove Batch";
$l['button']['ImportUsers'] = "Import Users";
$l['button']['NewUser'] = "New User";
$l['button']['NewUserQuick'] = "New User - Quick Add";
$l['button']['BatchAddUsers'] = "Batch Add Users";
$l['button']['EditUser'] = "Edit User";
$l['button']['SearchUsers'] = "Search Users";
$l['button']['RemoveUsers'] = "Remove Users";
$l['button']['ListHotspots'] = "List Hotspots";
$l['button']['NewHotspot'] = "New Hotspot";
$l['button']['EditHotspot'] = "Edit Hotspot";
$l['button']['RemoveHotspot'] = "Remove Hotspot";

$l['button']['ListIPPools'] = "List IP-Pools";
$l['button']['NewIPPool'] = "New IP-Pool";
$l['button']['EditIPPool'] = "Edit IP-Pool";
$l['button']['RemoveIPPool'] = "Remove IP-Pool";

$l['button']['ListNAS'] = "List NAS";
$l['button']['NewNAS'] = "New NAS";
$l['button']['EditNAS'] = "Edit NAS";
$l['button']['RemoveNAS'] = "Remove NAS";
$l['button']['ListHG'] = "List HuntGroup";
$l['button']['NewHG'] = "New HuntGroup";
$l['button']['EditHG'] = "Edit HuntGroup";
$l['button']['RemoveHG'] = "Remove HuntGroup";
$l['button']['ListUserGroup'] = "List User-Group Mappings";
$l['button']['ListUsersGroup'] = "List  User's Group Mappings";
$l['button']['NewUserGroup'] = "New User-Group Mappings";
$l['button']['EditUserGroup'] = "Edit User-Group Mappings";
$l['button']['RemoveUserGroup'] = "Remove User-Group Mappings";

$l['button']['ListProfiles'] = "List Profiles";
$l['button']['NewProfile'] = "New Profile";
$l['button']['EditProfile'] = "Edit Profile";
$l['button']['DuplicateProfile'] = "Duplicate Profile";
$l['button']['RemoveProfile'] = "Remove Profile";

$l['button']['ListGroupReply'] = "List Group Reply Mappings";
$l['button']['SearchGroupReply'] = "Search Group Reply";
$l['button']['NewGroupReply'] = "New Group Reply Mapping";
$l['button']['EditGroupReply'] = "Edit Group Reply Mapping";
$l['button']['RemoveGroupReply'] = "Remove Group Reply Mapping";

$l['button']['ListGroupCheck'] = "List Group Check Mappings";
$l['button']['SearchGroupCheck'] = "Search Group Check";
$l['button']['NewGroupCheck'] = "New Group Check Mapping";
$l['button']['EditGroupCheck'] = "Edit Group Check Mapping";
$l['button']['RemoveGroupCheck'] = "Remove Group Check Mapping";

$l['button']['UserAccounting'] = "User Accounting";
$l['button']['IPAccounting'] = "IP Accounting";
$l['button']['NASIPAccounting'] = "NAS IP Accounting";
$l['button']['NASIPAccountingOnlyActive'] = "Show only active";
$l['button']['DateAccounting'] = "Date Accounting";
$l['button']['AllRecords'] = "All Records";
$l['button']['ActiveRecords'] = "Active Records";

$l['button']['PlanUsage'] = "Plan Usage";

$l['button']['OnlineUsers'] = "Online Users";
$l['button']['LastConnectionAttempts'] = "Last Connection Attempts";
$l['button']['TopUser'] = "Top User";
$l['button']['History'] = "History";

$l['button']['ServerStatus'] = "Server Status";
$l['button']['ServicesStatus'] = "Services Status";

$l['button']['daloRADIUSLog'] = "daloRADIUS Log";
$l['button']['RadiusLog'] = "Radius Log";
$l['button']['SystemLog'] = "System Log";
$l['button']['BootLog'] = "Boot Log";

$l['button']['UserLogins'] = "User Logins";
$l['button']['UserDownloads'] = "User Downloads";
$l['button']['UserUploads'] = "User Uploads";
$l['button']['TotalLogins'] = "Total Logins";
$l['button']['TotalTraffic'] = "Total Traffic";
$l['button']['LoggedUsers'] = "Logged Users";

$l['button']['ViewMAP'] = "View MAP";
$l['button']['EditMAP'] = "Edit MAP";
$l['button']['RegisterGoogleMapsAPI'] = "RegisterGoogleMaps API";

$l['button']['UserSettings'] = "User Settings";
$l['button']['DatabaseSettings'] = "Database Settings";
$l['button']['LanguageSettings'] = "Language Settings";
$l['button']['LoggingSettings'] = "Logging Settings";
$l['button']['InterfaceSettings'] = "Interface Settings";

$l['button']['ReAssignPlanProfiles'] = "Re-Assign Plan Profiles";

$l['button']['TestUserConnectivity'] = "Test User Connectivity";
$l['button']['DisconnectUser'] = "Disconnect User";

$l['button']['ManageBackups'] = "Manage Backups";
$l['button']['CreateBackups'] = "Create Backups";

$l['button']['ListOperators'] = "List Operators";
$l['button']['NewOperator'] = "New Operator";
$l['button']['EditOperator'] = "Edit Operator";
$l['button']['RemoveOperator'] = "Remove Operator";

$l['button']['ProcessQuery'] = "Process Query";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['ImportUsers'] = "Import Users";


$l['title']['Dashboard'] = "Dashboard";
$l['title']['DashboardAlerts'] = "Alerts";

$l['title']['Invoice'] = "Invoice";
$l['title']['Invoices'] = "Invoices";
$l['title']['InvoiceRemoval'] = "Invoice Removal";
$l['title']['Payments'] = "Payments";
$l['title']['Items'] = "Items";

$l['title']['PayTypeInfo'] = "Payment Type Information";
$l['title']['PaymentInfo'] = "Payment Information";


$l['title']['RateInfo'] = "Rate Information";
$l['title']['PlanInfo'] = "Plan Information";
$l['title']['TimeSettings'] = "Time Settings";
$l['title']['BandwidthSettings'] = "Bandwidth Settings";
$l['title']['PlanRemoval'] = "Plan Removal";

$l['title']['BatchRemoval'] = "Batch Removal";

$l['title']['Backups'] = "Backups";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS Tables";
$l['title']['daloRADIUSTables'] = "daloRADIUS Tables";

$l['title']['IPPoolInfo'] = "IP-Pool Info";

$l['title']['BusinessInfo'] = "Business Info";

$l['title']['CleanupRecordsByUsername'] = "By Username";
$l['title']['CleanupRecordsByDate'] = "By Date";
$l['title']['DeleteRecords'] = "Delete Records";

$l['title']['RealmInfo'] = "Realm Info";

$l['title']['ProxyInfo'] = "Proxy Info";

$l['title']['VendorAttribute'] = "Vendor Attribute";

$l['title']['AccountRemoval'] = "Account Removal";
$l['title']['AccountInfo'] = "Account Info";

$l['title']['Profiles'] = "Profiles";
$l['title']['ProfileInfo'] = "Profile Info";

$l['title']['GroupInfo'] = "Group Info";
$l['title']['GroupAttributes'] = "Group Attributes";

$l['title']['NASInfo'] = "NAS Info";
$l['title']['NASAdvanced'] = "NAS Advanced";
$l['title']['HGInfo'] = "HG Info";
$l['title']['UserInfo'] = "User Info";
$l['title']['BillingInfo'] = "Billing Info";

$l['title']['Attributes'] = "Attributes";
$l['title']['ProfileAttributes'] = "Profile Attributes";

$l['title']['HotspotInfo'] = "Hotspot Info";
$l['title']['HotspotRemoval'] = "Hotspot Removal";

$l['title']['ContactInfo'] = "Contact Info";

$l['title']['Plan'] = "Plan";

$l['title']['Profile'] = "Profile";
$l['title']['Groups'] = "Groups";
$l['title']['RADIUSCheck'] = "Check Attributes";
$l['title']['RADIUSReply'] = "Reply Attributes";

$l['title']['Settings'] = "Settings";
$l['title']['DatabaseSettings'] = "Database Settings";
$l['title']['DatabaseTables'] = "Database Tables";
$l['title']['AdvancedSettings'] = "Advanced Settings";

$l['title']['Advanced'] = "Advanced";
$l['title']['Optional'] = "Optional";

/* ********************************************************************************** */

/* **********************************************************************************
 * Graphs
 * General graphing text
 ************************************************************************************/
$l['graphs']['Day'] = "Day";
$l['graphs']['Month'] = "Month";
$l['graphs']['Year'] = "Year";
$l['graphs']['Jan'] = "January";
$l['graphs']['Feb'] = "February";
$l['graphs']['Mar'] = "March";
$l['graphs']['Apr'] = "April";
$l['graphs']['May'] = "May";
$l['graphs']['Jun'] = "June";
$l['graphs']['Jul'] = "July";
$l['graphs']['Aug'] = "August";
$l['graphs']['Sep'] = "September";
$l['graphs']['Oct'] = "October";
$l['graphs']['Nov'] = "November";
$l['graphs']['Dec'] = "December";


/* ********************************************************************************** */

/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Login Required";
$l['text']['LoginPlease'] = "Login Please";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "First Name";
$l['ContactInfo']['LastName'] = "Last Name";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Department";
$l['ContactInfo']['WorkPhone'] = "Work Phone";
$l['ContactInfo']['HomePhone'] = "Home Phone";
$l['ContactInfo']['Phone'] = "Phone";
$l['ContactInfo']['MobilePhone'] = "Mobile Phone";
$l['ContactInfo']['Notes'] = "Notes";
$l['ContactInfo']['EnableUserUpdate'] = "Enable User Update";
$l['ContactInfo']['EnablePortalLogin'] = "Enable User Portal Login";
$l['ContactInfo']['PortalLoginPassword'] = "User Portal Login Password";

$l['ContactInfo']['OwnerName'] = "Owner Name";
$l['ContactInfo']['OwnerEmail'] = "Owner Email";
$l['ContactInfo']['ManagerName'] = "Manager Name";
$l['ContactInfo']['ManagerEmail'] = "Manager Email";
$l['ContactInfo']['Company'] = "Company";
$l['ContactInfo']['Address'] = "Address";
$l['ContactInfo']['City'] = "City";
$l['ContactInfo']['State'] = "State";
$l['ContactInfo']['Country'] = "Country";
$l['ContactInfo']['Zip'] = "Zip";
$l['ContactInfo']['Phone1'] = "Phone 1";
$l['ContactInfo']['Phone2'] = "Phone 2";
$l['ContactInfo']['HotspotType'] = "Hotspot Type";
$l['ContactInfo']['CompanyWebsite'] = "Company Website";
$l['ContactInfo']['CompanyPhone'] = "Company Phone";
$l['ContactInfo']['CompanyEmail'] = "Company Email";
$l['ContactInfo']['CompanyContact'] = "Company Contact";

$l['ContactInfo']['PlanName'] = "Plan Name";
$l['ContactInfo']['ContactPerson'] = "Contact Person";
$l['ContactInfo']['PaymentMethod'] = "Payment Method";
$l['ContactInfo']['Cash'] = "Cash";
$l['ContactInfo']['CreditCardNumber'] = "Credit Card Number";
$l['ContactInfo']['CreditCardName'] = "Credit Card Name";
$l['ContactInfo']['CreditCardVerificationNumber'] = "Credit Card Verification Number";
$l['ContactInfo']['CreditCardType'] = "Credit Card Type";
$l['ContactInfo']['CreditCardExpiration'] = "Credit Card Expiration";

/* ********************************************************************************** */

$l['Intro']['configdashboard.php'] = "Dashbard Settings";



$l['Intro']['paymenttypesmain.php'] = "Payment Types Page";
$l['Intro']['paymenttypesdel.php'] = "Delete Payment Type entry";
$l['Intro']['paymenttypesedit.php'] = "Edit Payment Type Details";
$l['Intro']['paymenttypeslist.php'] = "Payment Types Table";
$l['Intro']['paymenttypesnew.php'] = "New Payment Type entry";
$l['Intro']['paymenttypeslist.php'] = "Payment Types Table";
$l['Intro']['paymentslist.php'] = "Payments Table";
$l['Intro']['paymentsmain.php'] = "Payments Page";
$l['Intro']['paymentsdel.php'] = "Delete Payment entry";
$l['Intro']['paymentsedit.php'] = "Edit Payment Details";
$l['Intro']['paymentsnew.php'] = "New Payment entry";

$l['Intro']['billhistorymain.php'] = "Billing History";
$l['Intro']['msgerrorpermissions.php'] = "Error";

$l['Intro']['repnewusers.php'] = "Listing New Users";

$l['Intro']['mngradproxys.php'] = "Proxys Management";
$l['Intro']['mngradproxysnew.php'] = "New Proxy";
$l['Intro']['mngradproxyslist.php'] = "List Proxy";
$l['Intro']['mngradproxysedit.php'] = "Edit Proxy";
$l['Intro']['mngradproxysdel.php'] = "Remove Proxy";

$l['Intro']['mngradrealms.php'] = "Realms Management";
$l['Intro']['mngradrealmsnew.php'] = "New Realm";
$l['Intro']['mngradrealmslist.php'] = "List Realm";
$l['Intro']['mngradrealmsedit.php'] = "Edit Realm";
$l['Intro']['mngradrealmsdel.php'] = "Remove Realm";

$l['Intro']['mngradattributes.php'] = "Vendor's Attributes Management";
$l['Intro']['mngradattributeslist.php'] = "Vendor's Attributes List";
$l['Intro']['mngradattributesnew.php'] = "New Vendor Attributes";
$l['Intro']['mngradattributesedit.php'] = "Edit Vendor's Attributes";
$l['Intro']['mngradattributessearch.php'] = "Search Attributes";
$l['Intro']['mngradattributesdel.php'] = "Remove Vendor's Attributes";
$l['Intro']['mngradattributesimport.php'] = "Import Vendor Dictionary";
$l['Intro']['mngimportusers.php'] = "Import Users";


$l['Intro']['acctactive.php'] = "Active Records Accounting";
$l['Intro']['acctall.php'] = "All Users Accounting";
$l['Intro']['acctdate.php'] = "Date Sort Accounting";
$l['Intro']['accthotspot.php'] = "Hotspot Accounting";
$l['Intro']['acctipaddress.php'] = "IP Accounting";
$l['Intro']['accthotspotcompare.php'] = "Hotspot Comparison";
$l['Intro']['acctmain.php'] = "Accounting Page";
$l['Intro']['acctplans.php'] = "Plans Accounting Page";
$l['Intro']['acctnasipaddress.php'] = "NAS IP Accounting";
$l['Intro']['acctusername.php'] = "Users Accounting";
$l['Intro']['acctcustom.php'] = "Custom Accountings";
$l['Intro']['acctcustomquery.php'] = "Custom Query Accounting";
$l['Intro']['acctmaintenance.php'] = "Accounting Records Maintenance";
$l['Intro']['acctmaintenancecleanup.php'] = "Cleanup Stale-connections";
$l['Intro']['acctmaintenancedelete.php'] = "Delete Accounting Records";

$l['Intro']['billmain.php'] = "Billing Page";
$l['Intro']['ratesmain.php'] = "Rates Billing Page";
$l['Intro']['billratesdate.php'] = "Rates Prepaid Accounting";
$l['Intro']['billratesdel.php'] = "Delete Rate entry";
$l['Intro']['billratesedit.php'] = "Edit Rate Details";
$l['Intro']['billrateslist.php'] = "Rates Table";
$l['Intro']['billratesnew.php'] = "New Rate entry";

$l['Intro']['paypalmain.php'] = "PayPal Transactions Page";
$l['Intro']['billpaypaltransactions.php'] = "PayPal Transactions Page";

$l['Intro']['billhistoryquery.php'] = "Billing History";

$l['Intro']['billinvoice.php'] = "Billing Invoices";
$l['Intro']['billinvoicedel.php'] = "Delete Invoices entry";
$l['Intro']['billinvoiceedit.php'] = "Edit Invoice";
$l['Intro']['billinvoicelist.php'] = "List Invoices";
$l['Intro']['billinvoicereport.php'] = "Invoices Report";
$l['Intro']['billinvoicenew.php'] = "New Invoice";

$l['Intro']['billplans.php'] = "Billing Plans Page";
$l['Intro']['billplansdel.php'] = "Delete Plan entry";
$l['Intro']['billplansedit.php'] = "Edit Plan Details";
$l['Intro']['billplanslist.php'] = "Plans Table";
$l['Intro']['billplansnew.php'] = "New Plan entry";

$l['Intro']['billpos.php'] = "Billing Point of Sales Page";
$l['Intro']['billposdel.php'] = "Delete User";
$l['Intro']['billposedit.php'] = "Edit User";
$l['Intro']['billposlist.php'] = "List Users";
$l['Intro']['billposnew.php'] = "New User";

$l['Intro']['giseditmap.php'] = "Edit MAP Mode";
$l['Intro']['gismain.php'] = "GIS Mapping";
$l['Intro']['gisviewmap.php'] = "View MAP Mode";

$l['Intro']['graphmain.php'] = "Usage Graphs";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Total Traffic Comparison Usage";
$l['Intro']['graphsalltimelogins.php'] = "Total Logins";
$l['Intro']['graphsloggedusers.php'] = "Logged Users";
$l['Intro']['graphsoveralldownload.php'] = "User Downlads";
$l['Intro']['graphsoveralllogins.php'] = "User Logins";
$l['Intro']['graphsoverallupload.php'] = "User Uploads";

$l['Intro']['rephistory.php'] = "Action History";
$l['Intro']['replastconnect.php'] = "Last Connection Attempts";
$l['Intro']['repstatradius.php'] = "Daemons Information";
$l['Intro']['repstatserver.php'] = "Server Status and Information";
$l['Intro']['reponline.php'] = "Listing Online Users";
$l['Intro']['replogssystem.php'] = "System Logfile";
$l['Intro']['replogsradius.php'] = "RADIUS Server Logfile";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS Logfile";
$l['Intro']['replogsboot.php'] = "Boot Logfile";
$l['Intro']['replogs.php'] = "Logs";
$l['Intro']['rephb.php'] = "Heartbeat";
$l['Intro']['rephbdashboard.php'] = "daloRADIUS NAS Dashboard";
$l['Intro']['repbatch.php'] = "Batch";
$l['Intro']['mngbatchlist.php'] = "Batch Sessions List";
$l['Intro']['repbatchlist.php'] = "Batch Users List";
$l['Intro']['repbatchdetails.php'] = "Batch Details";

$l['Intro']['rephsall.php'] = "Hotspots Listing";
$l['Intro']['repmain.php'] = "Reports Page";
$l['Intro']['repstatus.php'] = "Status Page";
$l['Intro']['reptopusers.php'] = "Top Users";
$l['Intro']['repusername.php'] = "Users Listing";

$l['Intro']['mngbatch.php'] = "Create batch users";
$l['Intro']['mngbatchdel.php'] = "Delete batch sessions";

$l['Intro']['mngdel.php'] = "Remove User";
$l['Intro']['mngedit.php'] = "Edit User Details";
$l['Intro']['mnglistall.php'] = "Users Listing";
$l['Intro']['mngmain.php'] = "Users and Hotspots Management";
$l['Intro']['mngbatch.php'] = "Batch Users Management";
$l['Intro']['mngnew.php'] = "New User";
$l['Intro']['mngnewquick.php'] = "Quick User Add";
$l['Intro']['mngsearch.php'] = "User Search";

$l['Intro']['mnghsdel.php'] = "Remove Hotspots";
$l['Intro']['mnghsedit.php'] = "Edit Hotspots Details";
$l['Intro']['mnghslist.php'] = "List Hotspots";
$l['Intro']['mnghsnew.php'] = "New Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Remove User-Group Mapping";
$l['Intro']['mngradusergroup.php'] = "User-Group Configuration";
$l['Intro']['mngradusergroupnew.php'] = "New User-Group Mapping";
$l['Intro']['mngradusergrouplist'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergrouplistuser'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergroupedit'] = "Edit User-Group Mapping for User:";

$l['Intro']['mngradippool.php'] = "IP-Pool Configuration";
$l['Intro']['mngradippoolnew.php'] = "New IP-Pool";
$l['Intro']['mngradippoollist.php'] = "List IP-Pools";
$l['Intro']['mngradippooledit.php'] = "Edit IP-Pool";
$l['Intro']['mngradippooldel.php'] = "Remove IP-Pool";

$l['Intro']['mngradnas.php'] = "NAS Configuration";
$l['Intro']['mngradnasnew.php'] = "New NAS Record";
$l['Intro']['mngradnaslist.php'] = "NAS Listing in Database";
$l['Intro']['mngradnasedit.php'] = "Edit NAS Record";
$l['Intro']['mngradnasdel.php'] = "Remove NAS Record";

$l['Intro']['mngradhunt.php'] = "HuntGroup Configuration";
$l['Intro']['mngradhuntnew.php'] = "New HuntGroup Record";
$l['Intro']['mngradhuntlist.php'] = "HuntGroup Listing in Database";
$l['Intro']['mngradhuntedit.php'] = "Edit HuntGroup Record";
$l['Intro']['mngradhuntdel.php'] = "Remove HuntGroup Record";

$l['Intro']['mngradprofiles.php'] = "Profiles Configuration";
$l['Intro']['mngradprofilesedit.php'] = "Edit Profiles";
$l['Intro']['mngradprofilesduplicate.php'] = "Duplicate Profiles";
$l['Intro']['mngradprofilesdel.php'] = "Delete Profiles";
$l['Intro']['mngradprofileslist.php'] = "List Profiles";
$l['Intro']['mngradprofilesnew.php'] = "New Profile";

$l['Intro']['mngradgroups.php'] = "Groups Configuration";

$l['Intro']['mngradgroupreplynew.php'] = "New Group Reply Mapping";
$l['Intro']['mngradgroupreplylist.php'] = "Group Reply Mapping in Database";
$l['Intro']['mngradgroupreplyedit.php'] = "Edit Group Reply Mapping for Group:";
$l['Intro']['mngradgroupreplydel.php'] = "Remove Group Reply Mapping";
$l['Intro']['mngradgroupreplysearch.php'] = "Search Group Reply Mapping";

$l['Intro']['mngradgroupchecknew.php'] = "New Group Check Mapping";
$l['Intro']['mngradgroupchecklist.php'] = "Group Check Mapping in Database";
$l['Intro']['mngradgroupcheckedit.php'] = "Edit Group Check Mapping for Group:";
$l['Intro']['mngradgroupcheckdel.php'] = "Remove Group Check Mapping";
$l['Intro']['mngradgroupchecksearch.php'] = "Search Group Check Mapping";

$l['Intro']['configuser.php'] = "User Configuration";
$l['Intro']['configmail.php'] = "Mail Configuration";

$l['Intro']['configdb.php'] = "Database Configuration";
$l['Intro']['configlang.php'] = "Language Configuration";
$l['Intro']['configlogging.php'] = "Logging Configuration";
$l['Intro']['configinterface.php'] = "Web Interface Configuration";
$l['Intro']['configmainttestuser.php'] = "Test User Connectivity";
$l['Intro']['configmain.php'] = "System Configuration";
$l['Intro']['configmaint.php'] = "Maintenance";
$l['Intro']['configmaintdisconnectuser.php'] = "Disconnect User";
$l['Intro']['configbusiness.php'] = "Business Details";
$l['Intro']['configbusinessinfo.php'] = "Business Information";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupcreatebackups.php'] = "Create Backups";
$l['Intro']['configbackupmanagebackups.php'] = "Manage Backups";

$l['Intro']['configoperators.php'] = "Operators Configuration";
$l['Intro']['configoperatorsdel.php'] = "Remove Operator";
$l['Intro']['configoperatorsedit.php'] = "Edit Operator Settings";
$l['Intro']['configoperatorsnew.php'] = "New Operator";
$l['Intro']['configoperatorslist.php'] = "Operators Listing";

$l['Intro']['login.php'] = "Login";

$l['captions']['providebillratetodel'] = "Provide the rate entry type which you would like to remove";
$l['captions']['detailsofnewrate'] = "You may fill below details for the new rate";
$l['captions']['filldetailsofnewrate'] = "Fill below the details for the new rate entry";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "Dashboard Settings";


$l['helpPage']['repnewusers'] = "The following table lists new users created each month.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "List all PayPal transactions";
$l['helpPage']['billhistoryquery'] = "List all billing history for a user(s)";

$l['helpPage']['billinvoicereport'] = "";

$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billinvoicenew'] = "";
$l['helpPage']['billinvoiceedit'] = "";
$l['helpPage']['billinvoicedel'] = "";

$l['helpPage']['paymenttypeslist'] = "";
$l['helpPage']['paymenttypesnew'] = "";
$l['helpPage']['paymenttypesedit'] = "";
$l['helpPage']['paymenttypesdel'] = "";
$l['helpPage']['paymenttypesdate'] = "";

$l['helpPage']['paymentslist'] = "";
$l['helpPage']['paymentsnew'] = "";
$l['helpPage']['paymentsedit'] = "";
$l['helpPage']['paymentsdel'] = "";
$l['helpPage']['paymentsdate'] = "";

$l['helpPage']['billplanslist'] = "";
$l['helpPage']['billplansnew'] = "";
$l['helpPage']['billplansedit'] = "";
$l['helpPage']['billplansdel'] = "";

$l['helpPage']['billposlist'] = "";
$l['helpPage']['billposnew'] = "";
$l['helpPage']['billposedit'] = "";
$l['helpPage']['billposdel'] = "";

$l['helpPage']['billrateslist'] = "";
$l['helpPage']['billratesnew'] = "";
$l['helpPage']['billratesedit'] = "";
$l['helpPage']['billratesdel'] = "";
$l['helpPage']['billratesdate'] = "";

$l['helpPage']['mngradproxys'] = "";
$l['helpPage']['mngradproxyslist'] = "";
$l['helpPage']['mngradproxysnew'] = "";
$l['helpPage']['mngradproxysedit'] = "";
$l['helpPage']['mngradproxysdel'] = "";

$l['helpPage']['mngradrealms'] = "";
$l['helpPage']['mngradrealmslist'] = "";
$l['helpPage']['mngradrealmsnew'] = "";
$l['helpPage']['mngradrealmsedit'] = "";
$l['helpPage']['mngradrealmsdel'] = "";

$l['helpPage']['mngradattributes'] = "";
$l['helpPage']['mngradattributeslist'] = "";
$l['helpPage']['mngradattributesnew'] = "";
$l['helpPage']['mngradattributesedit'] = "";
$l['helpPage']['mngradattributessearch'] = "";
$l['helpPage']['mngradattributesdel'] = "";
$l['helpPage']['mngradattributesimport'] = "";
$l['helpPage']['mngimportusers'] = "";

$l['helpPage']['msgerrorpermissions'] = "You do not have permissions to access the page. <br/>
Please consult with your System Administrator. <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "To remove a user entry from the database you must provide the username of the account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";

// profiles help pages
$l['helpPage']['mngradprofilesnew'] = <<<EOF
<h2 class="fs-6">New Profile</h2>
<p>Use this feature to create a new profile. You will need to specify the Reply and Check attributes that should be associated with the profile. Once you have created the profile, it will be available for use by users in the system.</p>
EOF;
$l['helpPage']['mngradprofileslist'] = <<<EOF
<h2 class="fs-6">List Profiles</h2>
<p>This feature allows you to view a list of all available profiles in the system. You can select a profile to view its details or to edit or delete it.</p>
EOF;
$l['helpPage']['mngradprofiles'] = <<<EOF
<h1 class="fs-5">Profiles Management</h1>
<p>Profiles are used to manage sets of Reply Attributes and Check Attributes for users.<br>
Essentially, a profile is a combination of a Group Reply and a Group Check.</p>
EOF;
$l['helpPage']['mngradprofilesedit'] = <<<EOF
<h2 class="fs-6">Edit Profile</h2>
<p>If you need to make changes to an existing profile, you can use this feature. You will be able to modify the Reply and Check attributes associated with the profile.</p>
EOF;
$l['helpPage']['mngradprofilesduplicate'] = <<<EOF
<h2 class="fs-6">Duplicate Profile</h2>
<p>This feature allows you to quickly create a new profile based on an existing one.
   Simply select the profile you want to duplicate, provide a new name for the duplicated profile and click on the "Duplicate" button.
   The new profile will have the same Reply Attributes and Check Attributes as the original profile, allowing you to easily make adjustments as needed.</p>
EOF;
$l['helpPage']['mngradprofilesdel'] = <<<EOF
<h2 class="fs-6">Delete Profile</h2>
<p>If you no longer need a profile, you can delete it using this feature. Be aware that deleting a profile will also remove any associations between the profile and users in the system.</p>
EOF;

$l['helpPage']['mngradprofiles'] .= $l['helpPage']['mngradprofilesnew'] . $l['helpPage']['mngradprofileslist']
                                  . $l['helpPage']['mngradprofilesedit'] . $l['helpPage']['mngradprofilesduplicate']
                                  . $l['helpPage']['mngradprofilesdel'];

// group check/reply help pages
$l['helpPage']['mngradgroupchecknew'] = <<<EOF
<h2 class="fs-6">Add a New Group Reply/Check Mapping</h2>
<p>Create a new Group Reply/Check Mapping with ease using the intuitive interface.</p>
EOF;
$l['helpPage']['mngradgroupreplynew'] = $l['helpPage']['mngradgroupchecknew'];

$l['helpPage']['mngradgroupchecklist'] = <<<EOF
<h2 class="fs-6">List Group Reply/Check Mappings</h2>
<p>Quickly view a list of all existing Group Reply/Check Mappings.</p>
EOF;
$l['helpPage']['mngradgroupreplylist'] = $l['helpPage']['mngradgroupchecklist'];

$l['helpPage']['mngradgroupchecksearch'] = <<<EOF
<h2 class="fs-6">Search Group Reply/Check Mappings</h2>
<p>Search for specific Group Reply/Check Mappings using the name, attribute, or value. A wildcard character is automatically added as a suffix to the search text to help refine your search results.</p>
EOF;
$l['helpPage']['mngradgroupreplysearch'] = $l['helpPage']['mngradgroupchecksearch'];

$l['helpPage']['mngradgroupcheckedit'] = <<<EOF
<h2 class="fs-6">Edit Group Reply/Check Mappings</h2>
<p>Make modifications to existing Group Reply/Check Mappings to ensure that your network is operating efficiently.</p>
EOF;
$l['helpPage']['mngradgroupreplyedit'] = $l['helpPage']['mngradgroupcheckedit'];

$l['helpPage']['mngradgroupcheckdel'] = <<<EOF
<h2 class="fs-6">Delete Group Reply/Check Mappings</h2>
<p>Remove unnecessary Group Reply/Check Mappings to keep your database up-to-date and organized.</p>
EOF;
$l['helpPage']['mngradgroupreplydel'] = $l['helpPage']['mngradgroupcheckdel'];

$l['helpPage']['mngradgroups'] = <<<EOF
<h1 class="fs-5">Groups Management</h1>
<p>Efficiently manage Group Reply and Group Check mappings within the radgroupreply/radgroupcheck tables.</p>
EOF;
$l['helpPage']['mngradgroups'] .= $l['helpPage']['mngradgroupreplynew'] . $l['helpPage']['mngradgroupreplylist']
                                . $l['helpPage']['mngradgroupchecksearch'] . $l['helpPage']['mngradgroupcheckedit']
                                . $l['helpPage']['mngradgroupcheckdel'];

// ip pool help pages
$l['helpPage']['mngradippoolnew'] = <<<EOF
<h2 class="fs-6">New IP Pool</h2>
<p>Add a new IP address to an already configured IP Pool.</p>
EOF;
$l['helpPage']['mngradippoollist'] = <<<EOF
<h2 class="fs-6">List IP Pools</h2>
<p>List all the configured IP Pools and their assigned IP addresses.</p>
EOF;
$l['helpPage']['mngradippooledit'] = <<<EOF
<h2 class="fs-6">Edit IP Pool</h2>
<p>Edit an IP address for an already configured IP Pool.</p>
EOF;
$l['helpPage']['mngradippooldel'] = <<<EOF
<h2 class="fs-6">Remove IP Pool</h2>
<p>Remove an IP address from an already configured IP Pool.</p>
EOF;

$l['helpPage']['mngradippool'] = <<<EOF
<h1 class="fs-5">IP pools Management</h1>
<p>IP pools are groups of IP addresses that can be assigned to various devices, virtual machines, or applications within a network. Managing IP pools is important to ensure that there are enough IP addresses available for all devices that need them, while also avoiding the use of duplicate or invalid IP addresses.</p>
EOF;

$l['helpPage']['mngradippool'] .= $l['helpPage']['mngradippoolnew'] . $l['helpPage']['mngradippoollist']
                                . $l['helpPage']['mngradippooledit'] . $l['helpPage']['mngradippooldel'];

// nas help pages
$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "To remove a nas ip/host entry from the database you must provide the ip/host of the account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

// huntgroup help pages
$l['helpPage']['mngradhunt'] = <<<EOF
<p>Before starting work with HuntGroup, please read the <a href="https://wiki.freeradius.org/guide/SQL-Huntgroup-HOWTO" target="_blank">SQL_Huntgroup_HOWTO</a> on the FreeRADIUS wiki.</p>
<p>In particular:</p>
<p><i>Locate the authorize section in your radiusd.conf or sites-enabled/default configuration file and edit it. At the top of the authorize section after the preprocess module insert these lines:</i></p>
<pre>
update request {
    Huntgroup-Name := "%{sql:select groupname from radhuntgroup where nasipaddress=\"%{NAS-IP-Address}\"}"
}
</pre>
<p><i>What this does is perform a lookup in the radhuntgroup table using the IP address as a key to return the huntgroup name. It then adds an attribute/value pair to the request where the name of the attribute is Huntgroup-Name and its value is whatever was returned from the SQL query. If the query did not find anything, then the value is the empty string.</i></p>
EOF;


$l['helpPage']['mngradhuntdel'] = "To remove a huntgroup entry from the database you must provide the ip/host and port id of the huntgroup";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

// hotspots help pages
$l['helpPage']['mnghsdel'] = "To remove a hotspot from the database you must provide the hotspot's name<br/>";
$l['helpPage']['mnghsedit'] = "You may edit below details for hotspot<br/>";
$l['helpPage']['mnghsnew'] = "You may fill below details for new hotspot addition to database";
$l['helpPage']['mnghslist'] = "List of all hotspots in the database. You may use the quick links to edit or delete a hotspot from the database.";


$l['helpPage']['configuser'] = <<<EOF
<h2 class="fs-6">User Settings</h2>
<p>Choose if cleartext passwords are allowed in the database and what characters are allowed for randomly creating passwords and/or usernames.</p>
EOF;

$l['helpPage']['configdb_short'] = <<<EOF
<h2 class="fs-6">Database Settings</h2>
<p>Configure the database engine, connection settings, and table names if not using the default ones.</p>
EOF;

$l['helpPage']['configdb'] = $l['helpPage']['configdb_short'];
$l['helpPage']['configdb'] .= <<<EOF
<h3 class="fs-6">Global Settings</h3>
<p>Select the database storage engine</p>
<h3 class="fs-6">Tables Settings</h3>
<p>If not using the default FreeRADIUS schema, you can change the table names</p>
EOF;

$l['helpPage']['configlang'] = <<<EOF
<h2 class="fs-6">Language Settings</h2>
<p>Configure the interface language.</p>
EOF;

$l['helpPage']['configlogging'] = <<<EOF
<h2 class="fs-6">Logging Settings</h2>
<p>Configure logging rules and facilities.<br>Please make sure that the filename that you specify has write permissions by the webserver</p>
EOF;

$l['helpPage']['configinterface'] = <<<EOF
<h2 class="fs-6">Interface Settings</h2>
<p>Configure interface layout settings and behavior.</p>
EOF;

$l['helpPage']['configmail'] = <<<EOF
<h2 class="fs-6">Mail Settings</h2>
<p>Configure mail settings.</p>
EOF;

$l['helpPage']['configmaint'] = <<<EOF
<h1 class="fs-5">Maintenance</h1>
<h6>Test User Connectivity</h6>
<p>Send an Access-Request to the RADIUS Server to check if a user's credentials are valid.</p>
<h6>Disconnect User</h6>
<p>Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server to disconnect a user and terminate their session in a given NAS.</p>
EOF;

$l['helpPage']['configmainttestuser'] = <<<EOF
<h1 class="fs-5">Test User Connectivity</h1>
<p>Send an Access-Request to the RADIUS Server to check if a user's credentials are valid.</p>
<p>daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes.</p>
<p>daloRADIUS relies on the radclient binary being available in your <code>\$PATH</code> environment variable. If it is not, please make corrections to the <code>library/extensions/maintenance_radclient.php</code> file.</p>
<p>Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and radclient will retransmit the packets.</p>
<p>In the Advanced tab, it is possible to fine-tune the options for the test:</p>
<ul>
<li>Timeout - Wait 'timeout' seconds before retrying (may be a floating point number)</li>
<li>Retries - If timeout, retry sending the packet 'retries' times</li>
<li>Count - Send each packet 'count' times</li>
<li>Requests - Send 'num' packets from a file in parallel</li>
</ul>
EOF;

$l['helpPage']['configmaintdisconnectuser'] = <<<EOF
<h1 class="fs-5">Disconnect User</h1>
<p>Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server to disconnect a user and terminate his/her session in a given NAS.</p>
<p>For terminating a user's session it is required that the NAS support the PoD or CoA packet types, please consult your NAS vendor or documentation for this. Moreover, it is required to know the NAS ports for PoD or CoA packets, whereas newer NASs use port 3799 while other ones are configured to receive the packet on port 1700.</p>
<p>daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes.</p>
<p>daloRADIUS counts on the radclient binary being available in your <code>\$PATH</code> environment variable, if it is not, please make corrections to the <code>library/extensions/maintenance_radclient.php</code> file.</p>
<p>Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and radclient will retransmit the packets.</p>
<p>In the Advanced tab it is possible to fine-tune the options for the test:</p>
<ul>
<li>Timeout - Wait 'timeout' seconds before retrying (may be a floating point number)</li>
<li>Retries - If timeout, retry sending the packet 'retries' times</li>
<li>Count - Send each packet 'count' times</li>
<li>Requests - Send 'num' packets from a file in parallel</li>
</ul>
EOF;

$l['helpPage']['configoperators'] = <<<EOF
<h1 class="fs-5">Operators</h1>
<p>Configure operators settings and behavior.</p>
EOF;

$l['helpPage']['configoperatorsdel'] = "To remove an operator from the database you must provide the username.";
$l['helpPage']['configoperatorsedit'] = "Edit the operator user details below";
$l['helpPage']['configoperatorsnew'] = "You may fill below details for a new operator user addition to database";
$l['helpPage']['configoperatorslist'] = "Listing all Operators in database";

$l['helpPage']['configbackup'] = <<<EOF
<h1 class="fs-5">Backup</h1>
<p>Manage database backups</p>
EOF;
$l['helpPage']['configbackupcreatebackups'] = "Create Backups";
$l['helpPage']['configbackupmanagebackups'] = "Manage Backups";

$l['helpPage']['configmain'] = <<<EOF
<h1 class="fs-5">Global Settings</h1>
EOF;
$l['helpPage']['configmain'] .= $l['helpPage']['configuser'] . $l['helpPage']['configdb_short']
                              . $l['helpPage']['configlang'] . $l['helpPage']['configlogging']
                              . $l['helpPage']['configinterface'] . $l['helpPage']['configmail']
                              . $l['helpPage']['configmaint'] . $l['helpPage']['configoperators']
                              . $l['helpPage']['configbackup'];

// graphs help pages
$l['helpPage']['graphsalltimelogins'] = <<<EOF
<h2 class="fs-6">All-time Logins/Hits</h2>
<p>Generates a graphical chart showing the number of logins to the server over a given period of time.</p>
EOF;
$l['helpPage']['graphsoveralldownload'] = <<<EOF
<h2 class="fs-6">Overall Download Statistics</h2>
<p>Generates a graphical chart showing the amount of data downloaded by a specific user over a given period of time. The chart is accompanied by a table listing.</p>
EOF;
$l['helpPage']['graphsoverallupload'] = <<<EOF
<h2 class="fs-6">Overall Upload Statistics</h2>
<p>Generates a graphical chart showing the amount of data uploaded by a specific user over a given period of time. The chart is accompanied by a table listing.</p>
EOF;
$l['helpPage']['graphsoveralllogins'] = <<<EOF
<h2 class="fs-6">Overall Logins/Hits</h2>
<p>Generates a graphical chart showing the usage of a specific user over a given period of time. The chart displays the number of logins (or 'hits' to the NAS) and is accompanied by a table listing.</p>
EOF;
$l['helpPage']['graphsalltimetrafficcompare'] = <<<EOF
<h2 class="fs-6">All-time Traffic Comparison</h2>
<p>Generates a graphical chart comparing the amount of data downloaded and uploaded over a given period of time.</p>
EOF;
$l['helpPage']['graphsloggedusers'] = <<<EOF
<h2 class="fs-6">Logged Users</h2>
<p>Generates a graphical chart showing the number of logged-in users over a specified period. Users can be filtered by day, month, and year to create an hourly graph, or filtered only by month and year (select "―" on the day field) to graph the minimum and maximum logged-in users over the selected month.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Graphs</h1>'
                            . $l['helpPage']['graphsoveralllogins'] . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'] . $l['helpPage']['graphsoveralllogins']
                            . $l['helpPage']['graphsalltimetrafficcompare'] . $l['helpPage']['graphsloggedusers'];


$l['helpPage']['rephistory'] = "Lists all activity performed on management items and provides information on <br/>
Creation Date, Creation By as well as Updated Date and Update By history fields";
$l['helpPage']['replastconnect'] = "Lists all login attempts to the RADIUS server, both successful and failed logins";
$l['helpPage']['replogsboot'] = "Monitor Operating System Boot log - equivalent to running the dmesg command.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS's Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS's Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Operating System Logfile.";
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "Provides details on a specific batch";
$l['helpPage']['replogs'] = <<<EOF
<h1 class="fs-5">Logs</h1>
<h2 class="fs-6">daloRADIUS Log</h2>
<p>Monitors the daloRADIUS logfile.</p>
<h2 class="fs-6">RADIUS Log</h2>
<p>Monitors the FreeRADIUS logfile, equivalent to <code>/var/log/freeradius/radius.log</code> or <code>/usr/local/var/log/radius/radius.log</code>. Other possible locations for the logfile may be used, so please adjust the configuration accordingly.</p>
<h2 class="fs-6">System Log</h2>
<p>Monitors the operating system logfile, equivalent to <code>/var/log/syslog</code> or <code>/var/log/messages</code> on most platforms. Other possible locations for the logfile may be used, so please adjust the configuration accordingly.</p>
<h2 class="fs-6">Boot Log</h2>
<p>Monitors the operating system boot log, equivalent to running the <code>dmesg</code> command.</p>
EOF;
$l['helpPage']['repmain'] = <<<EOF
<h1 class="fs-5">General Reports</h1>
<h2 class="fs-6">Online Users</h2>
<p>Provides a list of all users currently online by checking the accounting table in the database. The check is for users with no end time (AcctStopTime) set. It's important to note that some of these users may have stale sessions due to NAS failures in sending accounting-stop packets. Note that this tab will only be visible if there are online users.</p>
<h2 class="fs-6">Last Connection Attempts</h2>
<p>Provides a list of all Access-Accept and Access-Reject (successful and failed) logins for users. These are retrieved from the database's postauth table, which must be defined in FreeRADIUS's config file to enable logging.</p>
<h2 class="fs-6">Top Users</h2>
<p>Provides a list of the top N users for bandwidth consumption and session time used.</p>
<h1 class="fs-5">Sub-Category Reports</h1>
<h2 class="fs-6">Logs</h2>
<p>Provides access to the daloRADIUS logfile, FreeRADIUS's logfile, the system's logfile, and the boot logfile.</p>
<h2 class="fs-6">Status</h2>
<p>Provides information on server status and RADIUS component status.</p>
EOF;
$l['helpPage']['repstatradius'] = "Provides general information about the FreeRADIUS daemon and MySQL/MariaDB Database server";
$l['helpPage']['repstatserver'] = "Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.";
$l['helpPage']['repstatus'] = <<<EOF
<h1 class="fs-5">Status</h1>
<h2 class="fs-6">Server Status</h2>
<p>Displays general information about the server, including CPU usage, number of running processes, uptime, memory usage, and more.</p>
<h2 class="fs-6">RADIUS Status</h2>
<p>Displays general information about the FreeRADIUS daemon and the MySQL database server.</p>
EOF;
$l['helpPage']['reptopusers'] = "Records for top users, those which are listed below have gained the highest consumption of session time or bandwidth usage. Listing users of category: ";
$l['helpPage']['repusername'] = "Records found for user:";
$l['helpPage']['reponline'] = "The following table lists users who are currently connected to the system. It is very much possible that there are stale connections, meaning that users got disconnected but the NAS didn't send or wasn't able to send a STOP accounting packet to the RADIUS server.";


$l['helpPage']['mnglistall'] = "Listing users in database";
$l['helpPage']['mngsearch'] = "Searching for user: ";
$l['helpPage']['mngnew'] = "You may fill below details for new user addition to database<br/>";
$l['helpPage']['mngedit'] = "Edit the user details below.<br/>";
$l['helpPage']['mngdel'] = "To remove a user entry from the database you must provide the username of the account<br/>";
$l['helpPage']['mngbatch'] = "You may fill below details for new user addition to database.<br/>
Note that these settings will apply for all the users that you are creating.<br/>";
$l['helpPage']['mngnewquick'] = "The following user/card is of type prepaid.<br/>
The amount of time specified in Time Credit will be used as the Session-Timeout and Max-All-Session
radius attributes";

// accounting section
$l['helpPage']['acctusername'] = <<<EOF
<h2 class="fs-6">User Accounting</h2>
<p>Provides detailed accounting information for all sessions in the database associated with a specific user.</p>
EOF;

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Date Accounting</h2>
<p>Provides detailed accounting information for all sessions between two specified dates for a particular user.</p>
EOF;

$l['helpPage']['acctipaddress'] = <<<EOF
<h2 class="fs-6">IP Accounting</h2>
<p>Provides detailed accounting information for all sessions originating from a specific IP address.</p>
EOF;

$l['helpPage']['acctnasipaddress'] = <<<EOF
<h2 class="fs-6">NAS Accounting</h2>
<p>Provides detailed accounting information for all sessions handled by a specific NAS IP address.</p>
EOF;

$l['helpPage']['acctactive'] = <<<EOF
<h2 class="fs-6">Active Accounting Records</h2>
<p>Provides information that would be useful for tracking active or expired users in the database, such as users with an expiration attribute or a max-all-session attribute.</p>
EOF;

$l['helpPage']['acctall'] = <<<EOF
<h2 class="fs-6">All Accounting Records</h2>
<p>Provides detailed accounting information for all sessions in the database.</p>
EOF;

$l['helpPage']['acctcustom_short'] = <<<EOF
<h1 class="fs-5">Custom Query</h1>
<p>Provides the most flexible custom query to run on the database. You can adjust the query settings in the left sidebar to your advantage.</p>
EOF;


$l['helpPage']['acctcustom'] = $l['helpPage']['acctcustom_short'] . <<<EOF
<p>Provides the most flexible custom query to run on the database. You can adjust the query settings in the left sidebar to your maximum advantage.</p>
<h2 class="fs-6">Between Dates</h2>
<p>Set the beginning and ending date to retrieve data within the specified range.</p>
<h2 class="fs-6">Where</h2>
<p>Set the field in the database that you want to match, like a key. Choose whether the value to match should be equal (=) or contain part of the value you search for, like a regex. If you choose to use the Contains operator, you shouldn't add any wildcards like the common form "*", but rather the value you input will automatically be searched in this form: *value* (or in mysql style: %value%).</p>
<h2 class="fs-6">Query Accounting Fields</h2>
<p>You may choose which fields you would like to present in the resulting list.</p>
<h2 class="fs-6">Order By</h2>
<p>Choose by which field you would like to order the results and its type, either ascending or descending.</p>
EOF;
$l['helpPage']['acctcustomquery'] = "";


$l['helpPage']['acctplans'] = "";
$l['helpPage']['acctmain'] = '<h1 class="fs-5">General Accounting</h1>'
                           . $l['helpPage']['acctusername'] . $l['helpPage']['acctdate']
                           . $l['helpPage']['acctipaddress'] . $l['helpPage']['acctnasipaddress']
                           . $l['helpPage']['acctall'] . $l['helpPage']['acctactive']
                           . $l['helpPage']['acctcustom_short'] . <<<EOF
<h1 class="fs-5">Hotspots</h1>
<p>Provides information on different managed hotspots, comparison, and other useful information.</p>
EOF;



// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Provides full accounting information for all sessions which originated from this specific Hotspot.
    This list is computed by listing only those records in the radacct table with the CalledStationId
    field which match a Hotspot's MAC Address entry in the Hotspot's management database.
<br/>
";
$l['helpPage']['accthotspotcompare'] = <<<EOF
<h1 class="fs-5">Hotspot Comparison</h1>
<h2 class="fs-6">Basic Information</h2>
<p>This section provides basic accounting information for comparing all active hotspots found in the database. The following accounting information is included: 
<ul>
<li>Hotspot Name: The name of the hotspot</li>
<li>Unique Users: The number of users who have logged in only through this hotspot</li>
<li>Total Hits: The total number of logins performed from this hotspot (unique and non-unique)</li>
<li>Average Time: The average time a user spent in this hotspot</li>
<li>Total Time: The accumulated time spent by all users in this hotspot</li>
</ul>
</p>
<h2 class="fs-6">Graphs</h2>
<p>
This section provides graphical comparisons for the different hotspots. The following graphs are available: 
<ul>
<li>Distribution of Unique Users per hotspot</li>
<li>Distribution of Hits per hotspot</li>
<li>Distribution of Time Usage per hotspot</li>
</ul>
</p>
EOF;
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    Provides full accounting information for all sessions which originated from this specific Hotspot.
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Provides a graph plot of different comparisons made.
<br/>
";

$l['helpPage']['acctmaintenance'] = <<<EOF
<h2 class="fs-6">Cleanup stale-sessions</h2> 
<p>Stale-sessions can often exist when the NAS is unable to provide an accounting STOP record for the user session. This results in a stale open session in the accounting records, which simulates a fake logged-in user record, leading to false positive results.</p>
<h2 class="fs-6">Delete accounting records</h2>
<p>This page allows deletion of accounting records from the database. It is recommended to only allow supervised administrators to access this page as it may not be wise to perform this action without careful consideration.</p>
EOF;
$l['helpPage']['acctmaintenancecleanup'] = <<<EOF
<h2 class="fs-6">Cleanup Stale Sessions</h2>
<p>This feature is used to clean up stale user sessions that remain active in FreeRADIUS (and thus in daloRADIUS), even though the user is no longer connected to the NAS. Stale sessions can occur when the NAS fails to provide an accounting STOP record, resulting in false positive logged-in user records.</p>
<p>There are two ways to clean up stale sessions:
<ul>
<li>Cleanup by Username: This option will <b>close</b> all open sessions for a specific username in the FreeRADIUS database. Use this option with caution.</li>
<li>Cleanup by Date: This option will <b>delete</b> all open sessions that are older than a specified date in the FreeRADIUS database. Use this option with caution as well.</li>
</ul>
</p>
EOF;
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = <<<EOF
<h1 class="fs-5">Edit Map Mode</h1>
<p>This mode allows you to add or delete hotspots by clicking on the map or a hotspot icon respectively.</p>
<h2 class="fs-6">Adding Hotspot</h2>
<p>To add a hotspot, click on a clear location on the map. You will be prompted to provide the hotspot's name and MAC address, which are crucial details used to identify the hotspot in the accounting table. Make sure to provide the correct MAC Address!</p>
<h2 class="fs-6">Deleting Hotspot</h2>
<p>To delete a hotspot, simply click on the hotspot's icon and confirm the deletion from the database.</p>
EOF;
$l['helpPage']['gisviewmap'] = <<<EOF
<h1 class="fs-5">View Map Mode</h1>
<p>In this mode, you can browse hotspots laid out as icons across the map.</p>
<p>By clicking on a hotspot, you can access more detailed information about it, including contact information and other relevant details.</p>
EOF;

$l['helpPage']['gismain'] = <<<EOF
<p>The <strong>GIS feature</strong> provides visual mappings of hotspot locations around the world.</p>
<p>When adding a new hotspot, you can specify its geolocation by providing its latitude and longitude coordinates, which are used to pinpoint its exact location on the map.</p>
<p>The GIS feature offers two different modes of operation:</p>
<ul>
    <li>in <strong>View MAP</strong> mode, you can explore the world map and view the current locations of all hotspots in the database by simply clicking on their icons;</li>
    <li>in <strong>Edit MAP</strong> mode, you can visually add new hotspots to the map by left-clicking on any clear location, or remove existing hotspots by left-clicking on their icons.</li>
</ul>
EOF;

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "This user has no check attributes associated with it";
$l['messages']['noReplyAttributesForUser'] = "This user has no reply attributes associated with it";

$l['messages']['noCheckAttributesForGroup'] = "This group has no check attributes associated with it";
$l['messages']['noReplyAttributesForGroup'] = "This group has no reply attributes associated with it";

$l['messages']['nogroupdefinedforuser'] = "This user has no Groups associated with it";
$l['messages']['wouldyouliketocreategroup'] = "Would you like to create one?";


$l['messages']['missingratetype'] = "error: missing rate type to delete";
$l['messages']['missingtype'] = "error: missing type";
$l['messages']['missingcardbank'] = "error: missing cardbank";
$l['messages']['missingrate'] = "error: missing rate";
$l['messages']['success'] = "success";
$l['messages']['gisedit1'] = "Welcome, you are currently in Edit mode";
$l['messages']['gisedit2'] = "Remove current marker from map and database?";
$l['messages']['gisedit3'] = "Please enter name of HotSpot";
$l['messages']['gisedit4'] = "Add current marker to database?";
$l['messages']['gisedit5'] = "Please enter name of HotSpot";
$l['messages']['gisedit6'] = "Please enter the MAC Address of the Hotspot";

$l['messages']['gismain1'] = "Successfully updated GoogleMaps API Registration code";
$l['messages']['gismain2'] = "error: could not open the file for writing:";
$l['messages']['gismain3'] = "Check file permissions. The file should be writable by the webserver's user/group";
$l['messages']['gisviewwelcome'] = "Welcome to Enginx Visual Maps";

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Cannot log in.</h1>
<p>This usually happens for one of these reasons:
    <ul>
        <li>wrong username and/or password;</li>
        <li>an administrator is already logged-in<br>(only one instance is allowed);</li>
        <li>there appears to be more than one 'administrator' user in the database.</li>
    </ul>
</p>
EOF;

$l['buttons']['savesettings'] = "Save Settings";
$l['buttons']['apply'] = "Apply";

$l['menu']['Home'] = "Home";
$l['menu']['Managment'] = "Management";
$l['menu']['Reports'] = "Reports";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Billing";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Graphs";
$l['menu']['Config'] = "Config";
$l['menu']['Help'] = "Help";

$l['submenu']['General'] = "General";
$l['submenu']['Reporting'] = "Reporting";
$l['submenu']['Maintenance'] = "Maintenance";
$l['submenu']['Operators'] = "Operators";
$l['submenu']['Backup'] = "Backup";
$l['submenu']['Logs'] = "Logs";
$l['submenu']['Status'] = "Status";
$l['submenu']['Batch Users'] = "Batch Users";
$l['submenu']['Dashboard'] = "Dashboard";
$l['submenu']['Users'] = "Users";
$l['submenu']['Hotspots'] = "Hotspots";
$l['submenu']['Nas'] = "Nas";
$l['submenu']['User-Groups'] = "User-Groups";
$l['submenu']['Profiles'] = "Profiles";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "Attributes";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "POS";
$l['submenu']['Plans'] = "Plans";
$l['submenu']['Rates'] = "Rates";
$l['submenu']['Merchant-Transactions'] = "Merchant-Transactions";
$l['submenu']['Billing-History'] = "Billing-History";
$l['submenu']['Invoices'] = "Invoices";
$l['submenu']['Payments'] = "Payments";
$l['submenu']['Custom'] = "Custom";
$l['submenu']['Hotspot'] = "Hotspot";

?>
