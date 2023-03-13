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
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/validation.php') !== false) {
    http_response_code(404);
    exit;
}

// commonly used regexes collection
define("DATE_REGEX", '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/');
define("ORDER_TYPE_REGEX", '/^(de|a)sc$/');
define("HOSTNAME_REGEX", '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/');
define("IP_REGEX", '/^(((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9]).){3}((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])$/');
define("NETMASK_LENGTH_REGEX", '/^3[0-2]|[1-2][0-9]|[1-9]$/');
define("MACADDR_REGEX", '/^[0-9A-Fa-f]{12}|(?:[0-9A-Fa-f]{2}([-:]))(?:[0-9A-Fa-f]{2}\1){4}[0-9A-Fa-f]{2}$/');
define("PINCODE_REGEX", '/^[a-zA-Z0-9]+$/');

// this regex allows input like (e.g.) 127, 127., 127.0, 127.0., 127.0.0, 127.0.0 and 127.0.0.1
define("LOOSE_IP_REGEX", '/^(((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])\.?){1,4}$/');

define("ALL_PRINTABLE_CHARS_REGEX", '/^[ -~]+$/');

define("DB_TABLE_NAME_REGEX", '/^[a-zA-Z0-9_]+$/');
define("ALLOWED_RANDOM_CHARS_REGEX", DB_TABLE_NAME_REGEX);

// some parameters can be validated using a whitelist.
// here we collect some useful whitelist.
// this lists can be also used for presentation purpose.
// whitelists naming convention:
// $valid_ [param_name] s
$valid_radiusReplys = array( "Any", "Access-Accept", "Access-Reject" );


$valid_backupActions = array( "download" => t('all','Download'), "rollback" => t('all','Rollback'), "delete" => t('all','del'));

$valid_authTypes = array(
                            "userAuth" => "Based on username and password",
                            "macAuth" => "Based on MAC address",
                            "pincodeAuth" => "Based on PIN code"
                        );

$valid_passwordTypes = array(
                                "Cleartext-Password",
                                "NT-Password",
                                "MD5-Password",
                                "SHA1-Password",
                                "User-Password",
                                "Crypt-Password",
                                //~ "CHAP-Password"
                             );

$valid_ops = array(
                    "=", ":=", "==", "+=", "!=", ">",
                    ">=", "<", "<=", "=~", "!~", "=*", "!*"
                  );

$valid_recommendedHelpers = array(
                                    "date", "datetime", "authtype", "framedprotocol", "servicetype",
                                    "kbitspersecond", "bitspersecond", "volumebytes", "mikrotikRateLimit",
                                 );

$valid_attributeTypes = array(
                                "string",
                                "integer",
                                "ipaddr",
                                "date",
                                "octets",
                                "ipv6addr",
                                "ifid",
                                "abinary",
                             );

$valid_db_engines = array(
                            "mysql" => "MySQL",
                            "pgsql" => "PostgreSQL",
                            "odbc" => "ODBC",
                            "mssql" => "MsSQL",
                            "mysqli" => "MySQLi",
                            "msql" => "MsQL",
                            "sybase" => "Sybase",
                            "sqlite" => "Sqlite",
                            "oci8" => "Oci8",
                            "ibase" => "ibase",
                            "fbsql" => "fbsql",
                            "informix" => "informix"
                         );

// values taken from an instance of freeradius 3.0.21
$valid_nastypes = array(
                         "other", "cisco", "computone", "livingston", "juniper", "max40xx", "multitech",
                         "netserver", "pathras", "patton", "portslave", "tc", "usrhiper"
                       );

// accounting custom-query options list
$acct_custom_query_options_all = array(
                                        "RadAcctId",
                                        "AcctSessionId",
                                        "AcctUniqueId",
                                        "UserName",
                                        "Realm",
                                        "NASIPAddress",
                                        "NASPortId",
                                        "NASPortType",
                                        "AcctStartTime",
                                        "AcctStopTime",
                                        "AcctSessionTime",
                                        "AcctAuthentic",
                                        "ConnectInfo_start",
                                        "ConnectInfo_stop",
                                        "AcctInputOctets",
                                        "AcctOutputOctets",
                                        "CalledStationId",
                                        "CallingStationId",
                                        "AcctTerminateCause",
                                        "ServiceType",
                                        "FramedProtocol",
                                        "FramedIPAddress",
                                        "AcctStartDelay",
                                        "AcctStopDelay"
                                    );

// accounting custom-query options selected by default
$acct_custom_query_options_default = array(
                                            "UserName", "Realm", "NASIPAddress", "AcctStartTime", "AcctStopTime",
                                            "AcctSessionTime", "AcctInputOctets", "AcctOutputOctets", "CalledStationId",
                                            "CallingStationId", "AcctTerminateCause", "FramedIPAddress"
                                          );

// billing history query options list
$bill_history_query_options_all = array(
                                            "id" => t('all','ID'),
                                            "username" => t('all','Username'),
                                            "planId" => t('all','PlanId'),

                                            "billAmount" => t('all','BillAmount'),
                                            "billAction" => t('all','BillAction'),
                                            "billPerformer" => t('all','BillPerformer'),
                                            "billReason" => t('all','BillReason'),

                                            "paymentmethod" => t('ContactInfo','PaymentMethod'),
                                            "cash" => t('ContactInfo','Cash'),

                                            "creditcardname" => t('ContactInfo','CreditCardName'),
                                            "creditcardnumber" => t('ContactInfo','CreditCardNumber'),
                                            "creditcardverification" => t('ContactInfo','CreditCardVerificationNumber'),
                                            "creditcardtype" => t('ContactInfo','CreditCardType'),
                                            "creditcardexp" => t('ContactInfo','CreditCardExpiration'),
                                            "coupon" => t('all','Coupon'),
                                            "discount" => t('all','Discount'),
                                            "notes" => t('ContactInfo','Notes'),
                                            "creationdate" => t('all','CreationDate'),
                                            "creationby" => t('all','CreationBy'),
                                            "updatedate" => t('all','UpdateDate'),
                                            "updateby" => t('all','UpdateBy')
                                       );

// billing history query options selected by default
$bill_history_query_options_default = array(
                                                "username",
                                                "planId",
                                                "billAmount",
                                                "billAction",
                                                "billPerformer",
                                                "paymentmethod"
                                           );

$bill_merchant_transactions_options_all = array(
                                                    "id" => t('all','ID'),
                                                    "username" => t('all','Username'),
                                                    "password"  => t('all','Password'),
                                                    "txnId"  => t('all','TxnId'),
                                                    "planName" => t('all','PlanName'),
                                                    "planId"  => t('all','PlanId'),
                                                    "quantity"  => t('all','Quantity'),
                                                    "business_email"  => t('all','ReceiverEmail'),
                                                    "business_id"  => t('all','Business'),
                                                    "payment_tax" => t('all','Tax'),
                                                    "payment_cost"  => t('all','Cost'),
                                                    "payment_fee" => t('all','TransactionFee'),
                                                    "payment_total" => t('all','TotalCost'),
                                                    "payment_currency" => t('all','PaymentCurrency'),
                                                    "first_name" => t('all','FirstName'),
                                                    "last_name" => t('all','LastName'),
                                                    "payer_email" => t('all','PayerEmail'),
                                                    "payer_address_name"  => t('all','AddressRecipient'),
                                                    "payer_address_street"  => t('all','Street'),
                                                    "payer_address_country" => t('all','Country'),
                                                    "payer_address_country_code"  => t('all','CountryCode'),
                                                    "payer_address_city" => t('all','City'),
                                                    "payer_address_state" => t('all','State'),
                                                    "payer_address_zip"  => t('all','Zip'),
                                                    "payment_date" => t('all','PaymentDate'),
                                                    "payment_status" => t('all','PaymentStatus'),
                                                    "payer_status" => t('all','PayerStatus'),
                                                    "payment_address_status" => t('all','PaymentAddressStatus'),
                                                    "vendor_type" => t('all','VendorType')
                                               );

$bill_merchant_transactions_options_default = array(
                                                        "username",
                                                        "planName",
                                                        "payment_fee",
                                                        "payment_total",
                                                        "payment_currency",
                                                        "first_name",
                                                        "last_name",
                                                        "payer_email",
                                                        "payer_address_country",
                                                        "payer_address_city",
                                                        "payer_address_state",
                                                        "payment_date",
                                                        "payment_status",
                                                        "vendor_type"
                                                   );

// validating values

$valid_paymentStatus = array(
                              "Any", "Completed",  "Denied",  "Expired",  "Failed",  "In-Progress",  "Pending",
                              "Processed",  "Refunded",  "Reversed",  "Canceled-Reversal",  "Voided",
                            );
$valid_vendorTypes = array( "Any", "2Checkout", "PayPal" );
$valid_billactions = array( "Any", "Refill Session Time", "Refill Session Traffic" );

$valid_planTypes = array( "Prepaid", "Postpaid", "2Checkout", "PayPal", );
$valid_planRecurringPeriods = array( "Never", "Daily", "Weekly", "Monthly", "Quarterly", "Semi-Yearly", "Yearly", );
$valid_planRecurringBillingSchedules = array( "Fixed", "Anniversary", );
$valid_planCurrencys = array(
                                "USD", "EUR", "GBP", "CAD", "JPY", "AUD", "NZD", "CHF", "HKD", "SGD",
                                "SEK", "DKK", "PLN", "NOK", "HUF", "CZK", "ILS", "MXN", "KSH",
                            );
$valid_planTimeTypes = array( "Accumulative", "Time-To-Finish" );

$valid_timeUnits = array( "second", "minute", "hour", "day", "week", "month", );

// ordered by country code
$valid_languages = array(
                            "ar" => "Arabic",
                            "en" => "English",
                            "es_ve" => "Spanish - Venezuelan",
                            "hu" => "Hungarian",
                            "it" => "Italian",
                            "ja" => "Japanese",
                            "pt_br" => "Portuguese - Brazilian",
                            "ro" => "Romanian",
                            "ru" => "Russian",
                            "tr" => "Turkish",
                            "zh" => "Chinese",
                        );

?>
