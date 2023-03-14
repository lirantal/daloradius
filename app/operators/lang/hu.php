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
 * Description:    Hungarian language file
 *
 * Authors:        Krisztián Kabódi <chriss at szegedshop dot hu>
 *                 Attila Zsiros <zsirmo at zsirmo dot hu>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/hu.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS Management, Reporting, Accounting and Billing by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "Azonosító";
$l['all']['PoolName'] = "Készlet neve";
$l['all']['CalledStationId'] = "Hivott állomás azonosító";
$l['all']['CallingStationID'] = "Hívó állomás azonosító";
$l['all']['ExpiryTime'] = "Lejárati idő";
$l['all']['PoolKey'] = "Készlet kulcs";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Dictionary";
$l['all']['VendorID'] = "Gyártó azonosító";
$l['all']['VendorName'] = "Gyártó neve";
$l['all']['VendorAttribute'] = "Gyártó leírása";
$l['all']['RecommendedOP'] = "Szükséges OP";
$l['all']['RecommendedTable'] = "Szükséges tábla";
$l['all']['RecommendedTooltip'] = "Szükséges segítség";
$l['all']['RecommendedHelper'] = "Szükséges segítség";
/********************************************************************************/

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "RADIUS Dictionary Path";

$l['all']['Compare'] = "Összehasonlitas";

$l['all']['Section'] = "Választ";
$l['all']['Item'] = "Tétel";

$l['all']['RemoveRadacctRecords'] = "Fiók rekordok eltávolítása";

$l['all']['CleanupSessions'] = "A session-ök eltávolítása melyek régebbiek mint";
$l['all']['DeleteSessions'] = "Törli a sessiont amely öregebb mint";

$l['all']['StartingDate'] = "Dátum kezdet";
$l['all']['EndingDate'] = "Dátum vége";

$l['all']['Realm'] = "Tartomány";
$l['all']['RealmName'] = "Tartomány Név";
$l['all']['RealmSecret'] = "Tartomány kulcs";
$l['all']['AuthHost'] = "Azonosított felhasználó";
$l['all']['AcctHost'] = "Aktuális felhasználó";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "nemTartomány";
$l['all']['Hints'] = "hints";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy Neve";
$l['all']['ProxySecret'] = "Proxy Titok";
$l['all']['DeadTime'] = "Használaton kívüli idő";
$l['all']['RetryDelay'] = "Ujrapróbálkozási idő";
$l['all']['RetryCount'] = "Ujrapróbálkozások száma";
$l['all']['DefaultFallback'] = "Default Fallback";

$l['all']['SimultaneousUse'] = "Simultaneous-Use";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Felhasználó";
$l['all']['NasShortname'] = "NAS Rövid neve";
$l['all']['NasType'] = "NAS Típusa";
$l['all']['NasPorts'] = "NAS Portok";
$l['all']['NasSecret'] = "NAS Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "NAS Description";
$l['all']['PacketType'] = "Csomag Típusa";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['HotSpots'] = "HotSpots";
$l['all']['HotSpotName'] = "Hotspot Név";
$l['all']['Name'] = "Name";
$l['all']['Username'] = "Felhasználó név";
$l['all']['Password'] = "Jelszó";
$l['all']['PasswordType'] = "Jelszó típusa";
$l['all']['IPAddress'] = "IP Cím";
$l['all']['Profile'] = "Profile";
$l['all']['Group'] = "Csoport";
$l['all']['Groupname'] = "Csoport név";
$l['all']['ProfilePriority'] = "Profile Priority";
$l['all']['GroupPriority'] = "Csoport prioritása";
$l['all']['CurrentGroupname'] = "Jelenlegi csoport neve";
$l['all']['NewGroupname'] = "Uj csoport neve";
$l['all']['Priority'] = "Prioritás";
$l['all']['Attribute'] = "Jellemzők";
$l['all']['Operator'] = "Operátor";
$l['all']['Value'] = "Érték";
$l['all']['NewValue'] = "Új érték";
$l['all']['MaxTimeExpiration'] = "Max Idő / Lejárat";
$l['all']['UsedTime'] = "Haszálati idő";
$l['all']['Status'] = "Státusz";
$l['all']['Usage'] = "Használat";
$l['all']['StartTime'] = "Kezdetei idő";
$l['all']['StopTime'] = "Befejezési idő";
$l['all']['TotalTime'] = "Teljes idő";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Feltöltés";
$l['all']['Download'] = "Letöltés";
$l['all']['Rollback'] = "Roll-back";
$l['all']['Termination'] = "Megszakítás";
$l['all']['NASIPAddress'] = "NAS IP Cím";
$l['all']['NASShortName'] = "NAS Short Name";
$l['all']['Action'] = "Folyamat";
$l['all']['UniqueUsers'] = "Egyedi Felhasználók";
$l['all']['TotalHits'] = "Minden találat";
$l['all']['AverageTime'] = "Átlag idő";
$l['all']['Records'] = "Recordok";
$l['all']['Summary'] = "Összefoglalás";
$l['all']['Statistics'] = "Statisztika";
$l['all']['Credit'] = "Hitel";
$l['all']['Used'] = "Használt";
$l['all']['LeftTime'] = "Hátralévő idő";
$l['all']['LeftPercent'] = "% idő van hátra";
$l['all']['TotalSessions'] = "Összes Session";
$l['all']['LastLoginTime'] = "Utolsó belépés időpontja";
$l['all']['TotalSessionTime'] = "Összes használat ideje";
$l['all']['RateName'] = "Rate Name";
$l['all']['RateType'] = "Rate Type";
$l['all']['RateCost'] = "Rate Cost";
$l['all']['Billed'] = "Számlázott";
$l['all']['TotalUsers'] = "Összes felhasználó";
$l['all']['TotalBilled'] = "Összes számlázott";
$l['all']['CardBank'] = "Bank neve";
$l['all']['Type'] = "Típusa";
$l['all']['CardBank'] = "Bank";
$l['all']['MACAddress'] = "MAC Cím";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN Code";
$l['all']['CreationDate'] = "Létrehozás dátuma";
$l['all']['CreationBy'] = "Létrehozta";
$l['all']['UpdateDate'] = "Frissítés dátuma";
$l['all']['UpdateBy'] = "Frissítette";

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
$l['all']['PostalInvoice'] = "Postal Invoice";
$l['all']['FaxInvoice'] = "Fax Invoice";
$l['all']['EmailInvoice'] = "Email Invoice";

$l['all']['edit'] = "szerkesztés";
$l['all']['del'] = "törlés";
$l['all']['groupslist'] = "Csoport-lista";
$l['all']['TestUser'] = "Teszt felhasználó";
$l['all']['Accounting'] = "Számlázás";
$l['all']['RADIUSReply'] = "RADIUS válasz";

$l['all']['Disconnect'] = "Szétkapcsolás";

$l['all']['Debug'] = "Hiba keresés";
$l['all']['Timeout'] = "Lejárt az idő";
$l['all']['Retries'] = "Ujrapróbálkozások";
$l['all']['Count'] = "Számlál";
$l['all']['Requests'] = "Kérések";

$l['all']['DatabaseHostname'] = "Adatbázis szerver";
$l['all']['DatabaseUser'] = "Adatbázis felhasználó";
$l['all']['DatabasePass'] = "Adatbázis jelszó";
$l['all']['DatabaseName'] = "Adatbázis neve";

$l['all']['PrimaryLanguage'] = "Elsődleges nyelv";

$l['all']['PagesLogging'] = "Loggolt lapok (látogatott oldalak)";
$l['all']['QueriesLogging'] = "Loggolt kérések (jelentések és grafikonok)";
$l['all']['ActionsLogging'] = "Loggolt folyamatok (ürlapok)";
$l['all']['FilenameLogging'] = "Loggolt file-ok (teljes utvonal)";
$l['all']['LoggingDebugOnPages'] = "Loggolt hibakeresések az oldalon";
$l['all']['LoggingDebugInfo'] = "Loggolt hibakeresések adatai";

$l['all']['PasswordHidden'] = "Engedélyezi a jelszavak elrejtését (csillagok lesznek mutatva)";
$l['all']['TablesListing'] = "Sorok/Oszlopok száma az oldalon";
$l['all']['TablesListingNum'] = "Engedi a táblák listázásának számozását";
$l['all']['AjaxAutoComplete'] = "Engedélyezi az Ajax Auto-Complete funkciót";

$l['all']['RadiusServer'] = "Radius Szerver";
$l['all']['RadiusPort'] = "Radius Port";

$l['all']['UsernamePrefix'] = "Felhasználónév Prefix";
$l['all']['NumberInstances'] = "Készítendő folyamatok száma";
$l['all']['UsernameLength'] = "Felhasználó név bejegyzés hossza";
$l['all']['PasswordLength'] = "Jelszó bejegyzés hossza";

$l['all']['Expiration'] = "Lejárat";
$l['all']['MaxAllSession'] = "Minden - Maximális - Folyamat";
$l['all']['SessionTimeout'] = "Folyamat lejárati idő";
$l['all']['IdleTimeout'] = "Ideális lejárati idő";

$l['all']['DBEngine'] = "Adatbázis motor";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "felhasználói csoport";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "kezelők";
$l['all']['billingrates'] = "billing rates";
$l['all']['hotspots'] = "hotspot-ok";
$l['all']['nas'] = "nas";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "radippool";
$l['all']['userinfo'] = "userinfo";
$l['all']['dictionary'] = "dictionary";
$l['all']['realms'] = "realms";
$l['all']['proxys'] = "proxys";
$l['all']['billingpaypal'] = "billing paypal";
$l['all']['billingplans'] = "billing plans";
$l['all']['billinghistory'] = "billing history";
$l['all']['billinginfo'] = "billing user info";

$l['all']['Month'] = "Hónap";

$l['all']['PaymentDate'] = "Payment Date";
$l['all']['PaymentStatus'] = "Payment Status";
$l['all']['FirstName'] = "First name";
$l['all']['LastName'] = "Last name";
$l['all']['PayerStatus'] = "Payer Status";
$l['all']['PaymentAddressStatus'] = "Payment Address Status";
$l['all']['PayerEmail'] = "Payer Email";
$l['all']['TxnId'] = "Tranasction Id";
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
$l['all']['PlanCost'] = "Plan Cost";
$l['all']['PlanSetupCost'] = "Plan Setup Cost";
$l['all']['PlanTax'] = "Plan Tax";
$l['all']['PlanCurrency'] = "Plan Currency";
$l['all']['PlanGroup'] = "Plan Profile (Group)";
$l['all']['PlanType'] = "Plan Type";
$l['all']['PlanName'] = "Plan Name";
$l['all']['PlanId'] = "Plan Id";
$l['all']['Quantity'] = "Quantity";
$l['all']['ReceiverEmail'] = "Receiver Email";
$l['all']['Business'] = "Business";
$l['all']['Tax'] = "Tax";
$l['all']['Cost'] = "Cost";
$l['all']['TransactionFee'] = "Transaction Fee";
$l['all']['PaymentCurrency'] = "Payment Currency";
$l['all']['AddressRecipient'] = "Address Recipient";
$l['all']['Street'] = "Street";
$l['all']['Country'] = "Country";
$l['all']['CountryCode'] = "Country Code";
$l['all']['City'] = "City";
$l['all']['State'] = "State";
$l['all']['Zip'] = "Zip";

$l['all']['BusinessName'] = "Cégnév";
$l['all']['BusinessPhone'] = "Céges telefon";
$l['all']['BusinessAddress'] = "Céges cím";
$l['all']['BusinessWebsite'] = "Céges honlap";
$l['all']['BusinessEmail'] = "Céges email";
$l['all']['BusinessContactPerson'] = "Céges kapcsolattartó";

$l['all']['DBPasswordEncryption'] = "Adatbázi jelszó titkosításának típusa";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['Username'] = "Type the Username";
$l['Tooltip']['UsernameWildcard'] = "Hint: you may use the char * or % to specify a wildcard";
$l['Tooltip']['HotspotName'] = "Type the Hotspot name";
$l['Tooltip']['NasName'] = "Type the NAS name";
$l['Tooltip']['GroupName'] = "Type the Group name";
$l['Tooltip']['AttributeName'] = "Type the Attribute name";
$l['Tooltip']['VendorName'] = "Type the Vendor name";
$l['Tooltip']['PoolName'] = "Type the Pool name";
$l['Tooltip']['IPAddress'] = "Type the IP address";
$l['Tooltip']['Filter'] = "Type a filter, can be any alpha numeric string. Leave empty to match anything. ";
$l['Tooltip']['Date'] = "Type the date <br/> example: 1982-06-04 (Y-M-D)";
$l['Tooltip']['RateName'] = "Type the Rate name";
$l['Tooltip']['OperatorName'] = "Type the Operator name";
$l['Tooltip']['BillingPlanName'] = "Type the Billing Plan name";

$l['Tooltip']['EditRate'] = "Edit Rate";
$l['Tooltip']['RemoveRate'] = "Remove Rate";

$l['Tooltip']['EditRate'] = "Edit Rate";
$l['Tooltip']['RemoveRate'] = "Remove Rate";

$l['Tooltip']['rateNameTooltip'] = "The rate friendly name,<br/>
                                    to describe the purpose of the rate";
$l['Tooltip']['rateTypeTooltip'] = "The rate type, to describe<br/>
                                the operation of the rate";
$l['Tooltip']['rateCostTooltip'] = "The rate cost amount";

$l['Tooltip']['planNameTooltip'] = "The Plan's name. This is<br/>
                                    a friendly name describing the
                                    characeristics of the plan";

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
$l['Tooltip']['planRecurringPeriodTooltip'] = "";
$l['Tooltip']['planCostTooltip'] = "";
$l['Tooltip']['planSetupCostTooltip'] = "";
$l['Tooltip']['planTaxTooltip'] = "";
$l['Tooltip']['planCurrencyTooltip'] = "";
$l['Tooltip']['planGroupTooltip'] = "";


$l['Tooltip']['EditIPPool'] = "IP-Pool szerkesztése";
$l['Tooltip']['RemoveIPPool'] = "IP-Pool eltávolítása";
$l['Tooltip']['EditIPAddress'] = "IP cím szerkesztése";
$l['Tooltip']['RemoveIPAddress'] = "IP cím eltávolítása";

$l['Tooltip']['BusinessNameTooltip'] = "Cég név kiegészítés";
$l['Tooltip']['BusinessPhoneTooltip'] = "Cég telefon kiegészítés";
$l['Tooltip']['BusinessAddressTooltip'] = "Cég cím kiegészítés";
$l['Tooltip']['BusinessWebsiteTooltip'] = "Cég honlap kiegészítés";
$l['Tooltip']['BusinessEmailTooltip'] = "Cég email kiegészítés";
$l['Tooltip']['BusinessContactPersonTooltip'] = "Cég kapcsolattartó kiegészítés";

$l['Tooltip']['proxyNameTooltip'] = "Proxy name";
$l['Tooltip']['proxyRetryDelayTooltip'] = "Várakozási idő másodpercben <br/>
                    a PROXY szerver válaszára.";
$l['Tooltip']['proxyRetryCountTooltip'] = "The number of retries to send <br/>
                    before giving up, and sending a <br/>
                    reject message to the NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "If the home server does not respond <br/>
                    to any of the multiple retries, <br/>
                    then FreeRADIUS will stop sending it <br/>
                    proxy requests, and mark it 'dead'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "If all exact matching realms <br/>
                        did not respond, we can try the <br/>
                        ";
$l['Tooltip']['realmNameTooltip'] = "Övezet neve";
$l['Tooltip']['realmTypeTooltip'] = "Beállítás mint Radius alapértelmezett";
$l['Tooltip']['realmSecretTooltip'] = "Radius övezet titok";
$l['Tooltip']['realmAuthhostTooltip'] = "Övezet authentikációs kliens";
$l['Tooltip']['realmAccthostTooltip'] = "Övezet azonosító kliens";
$l['Tooltip']['realmLdflagTooltip'] = "Allows for load balancing<br/>
                    Allowed values are 'fail_over' <br/>
                    and 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Whether to strip or not the <br/>
                    realm suffix";
$l['Tooltip']['realmHintsTooltip'] = "Találati segéd";
$l['Tooltip']['realmNotrealmTooltip'] = "Nem övezeti segéd";


$l['Tooltip']['vendorNameTooltip'] = "Example: Cisco<br/>&nbsp;&nbsp;&nbsp;
                                        The Vendor's name.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "Example: string<br/>&nbsp;&nbsp;&nbsp;
                                        The attributes variable type<br/>&nbsp;&nbsp;&nbsp;
                    (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Example: Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;
                                        The exact attribute name.<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "Example: :=<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended attribute's operator.<br/>&nbsp;&nbsp;&nbsp;
                    (one of: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "Example: check<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended target table.<br/>&nbsp;&nbsp;&nbsp;
                    (either check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Example: the ip address for the user<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended tooltip.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['RecommendedHelperTooltip'] = "The helper function which will be<br/>&nbsp;&nbsp;&nbsp;
                                        available when adding this attribute<br/>&nbsp;&nbsp;&nbsp;";



$l['Tooltip']['AttributeEdit'] = "Tulajdonságok szerkesztése";


$l['Tooltip']['UserEdit'] = "Felhasználó szerkesztése";
$l['Tooltip']['HotspotEdit'] = "Hotspot szerkesztése";
$l['Tooltip']['EditNAS'] = "NAS szerkesztése";
$l['Tooltip']['RemoveNAS'] = "NAS eltávolítása";

$l['Tooltip']['EditUserGroup'] = "Felhasználói csoport szerkesztése";
$l['Tooltip']['ListUserGroups'] = "Felhasználói csoport listázása";

$l['Tooltip']['EditProfile'] = "Profil szerkesztése";

$l['Tooltip']['EditRealm'] = "Övezet szerkesztése";
$l['Tooltip']['EditProxy'] = "Proxy szerkesztése";

$l['Tooltip']['EditGroup'] = "Csoport szerkesztése";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(descriptive name)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!";


$l['Tooltip']['usernameTooltip'] = "The exact username as the user<br/>&nbsp;&nbsp;&nbsp;
                    will use to connect to the system";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "Passwords are case sensetive in<br/>&nbsp;&nbsp;&nbsp;
                    certain systems so take extra care";
$l['Tooltip']['groupTooltip'] = "The user will be added to this group.<br/>&nbsp;&nbsp;&nbsp;
                    By assigning a user to a particular group<br/>&nbsp;&nbsp;&nbsp;
                    the user is subject to the group's attributes";
$l['Tooltip']['macaddressTooltip'] = "Example: 00:aa:bb:cc:dd:ee<br/>&nbsp;&nbsp;&nbsp;
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

$l['Tooltip']['hotspotMacaddressTooltip'] = "Example: 00aabbccddee<br/>&nbsp;&nbsp;&nbsp;
                    The MAC address of the NAS<br/>";

$l['Tooltip']['geocodeTooltip'] = "Example: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    This is the GooleMaps location code used<br/>&nbsp;&nbsp;&nbsp;
                    to pin the Hotspot/NAS on the map (see GIS).";


/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/
$l['button']['ClearSessions'] = "Munkaciklus törlése";

$l['button']['ListRates'] = "List Rates";
$l['button']['NewRate'] = "New Rate";
$l['button']['EditRate'] = "Edit Rate";
$l['button']['RemoveRate'] = "Remove Rate";

$l['button']['ListPlans'] = "List Plans";
$l['button']['NewPlan'] = "New Plan";
$l['button']['EditPlan'] = "Edit Plan";
$l['button']['RemovePlan'] = "Remove Plan";

$l['button']['ListRealms'] = "Övezetek listája";
$l['button']['NewRealm'] = "Új övezet";
$l['button']['EditRealm'] = "Övezet szekesztése";
$l['button']['RemoveRealm'] = "Övezet eltávolítása";

$l['button']['ListProxys'] = "Proxyk listája";
$l['button']['NewProxy'] = "Új Proxy";
$l['button']['EditProxy'] = "Proxy szerkesztése";
$l['button']['RemoveProxy'] = "Proxy eltávolítása";

$l['button']['ListAttributesforVendor'] = "Gyártók listája:";
$l['button']['NewVendorAttribute'] = "Új gyártó";
$l['button']['EditVendorAttribute'] = "Gyártó szerkesztése";
$l['button']['SearchVendorAttribute'] = "Keresési tulajdonságok";
$l['button']['RemoveVendorAttribute'] = "Gyártó tulajdonságok eltávolítása";

$l['button']['ImportVendorDictionary'] = "Import Vendor Dictionary";

$l['button']['BetweenDates'] = "Megadott időszak:";
$l['button']['Where'] = "Hol";
$l['button']['AccountingFieldsinQuery'] = "Nyilvántartási mezők a lekérdezésben:";
$l['button']['OrderBy'] = "Rendezés";
$l['button']['HotspotAccounting'] = "Hotspot nyilvántartás";
$l['button']['HotspotsComparison'] = "Hotspots Comparison";

$l['button']['CleanupStaleSessions'] = "Cleanup Stale Sessions";
$l['button']['DeleteAccountingRecords'] = "Nyilvántartási rekordok törlése";

$l['button']['ListUsers'] = "Felhasználók listázása";
$l['button']['NewUser'] = "Új felhasználó";
$l['button']['NewUserQuick'] = "Uj felhasználó - gyors";
$l['button']['BatchAddUsers'] = "Csoportos felhasználó hozzáadás";
$l['button']['EditUser'] = "Felhasználó szerkesztése";
$l['button']['SearchUsers'] = "Felhasználó keresése";
$l['button']['RemoveUsers'] = "Felhasználó törlése";
$l['button']['ListHotspots'] = "Hotspotok listája";
$l['button']['NewHotspot'] = "Új Hotspot";
$l['button']['EditHotspot'] = "Hotspot szerkesztése";
$l['button']['RemoveHotspot'] = "Hotspot eltávolítása";

$l['button']['ListIPPools'] = "IP-Pool-ok listája";
$l['button']['NewIPPool'] = "Új IP-Pool";
$l['button']['EditIPPool'] = "IP-Pool szerkesztése";
$l['button']['RemoveIPPool'] = "IP-Pool szerkesztése";

$l['button']['ListNAS'] = "NAS Lista";
$l['button']['NewNAS'] = "Új NAS";
$l['button']['EditNAS'] = "NAS szerkesztése";
$l['button']['RemoveNAS'] = "NAS eltávolítása";

$l['button']['ListUserGroup'] = "Felhasználó csoport térkép lista";
$l['button']['ListUsersGroup'] = "Felhasználó csoportok térkép lista";
$l['button']['NewUserGroup'] = "Új felhasználó csoport térkép";
$l['button']['EditUserGroup'] = "Felhasználó csoport térkép szerkesztés";
$l['button']['RemoveUserGroup'] = "Felhasználó csoport térkép eltávolítás";

$l['button']['ListProfiles'] = "Profilok litája";
$l['button']['NewProfile'] = "Új profil";
$l['button']['EditProfile'] = "Profil szerkesztése";
$l['button']['DuplicateProfile'] = "Profil másolása";
$l['button']['RemoveProfile'] = "Profil eltávolítása";

$l['button']['ListGroupReply'] = "Csoport válasz lista";
$l['button']['SearchGroupReply'] = "Csoport válasz lista keresése";
$l['button']['NewGroupReply'] = "Új csoport válasz";
$l['button']['EditGroupReply'] = "Csoport válasz szerkeszétse";
$l['button']['RemoveGroupReply'] = "Csoport válasz eltávolítása";

$l['button']['ListGroupCheck'] = "Csoport lekérdezések listája";
$l['button']['SearchGroupCheck'] = "Csoport lekérdezés keresése";
$l['button']['NewGroupCheck'] = "Új csoport lekérdezés";
$l['button']['EditGroupCheck'] = "Csoport lekérdezés szerkesztése";
$l['button']['RemoveGroupCheck'] = "Csoport lekérdezés eltávolítása";

$l['button']['UserAccounting'] = "Felhasználói fiók";
$l['button']['IPAccounting'] = "IP fiók";
$l['button']['NASIPAccounting'] = "NAS IP fiók";
$l['button']['DateAccounting'] = "Dátum fiók";
$l['button']['AllRecords'] = "Minden Record";
$l['button']['ActiveRecords'] = "Aktív Recordok";

$l['button']['OnlineUsers'] = "Online felhasználók";
$l['button']['LastConnectionAttempts'] = "Utolsó kapcsolódási kísérlet";
$l['button']['TopUser'] = "Leggyakoribb felhasználók";
$l['button']['History'] = "Esemény napló";

$l['button']['ServerStatus'] = "Szerver Státusz";
$l['button']['ServicesStatus'] = "Szervíz Státusz";

$l['button']['daloRADIUSLog'] = "RADIUS Napló";
$l['button']['RadiusLog'] = "Radius napló";
$l['button']['SystemLog'] = "Rendszer napló";
$l['button']['BootLog'] = "Boot napló";

$l['button']['UserLogins'] = "Felhasználói bejelentkezések";
$l['button']['UserDownloads'] = "Felhasználó letöltések";
$l['button']['UserUploads'] = "Felhasználó feltöltések";
$l['button']['TotalLogins'] = "Összes bejelentkezés";
$l['button']['TotalTraffic'] = "Összes forgalom";

$l['button']['ViewMAP'] = "Térkép megtekintése";
$l['button']['EditMAP'] = "Térkép szerkesztése";
$l['button']['RegisterGoogleMapsAPI'] = "GoogleMaps API regisztrálása";

$l['button']['DatabaseSettings'] = "Adatbázis beállítások";
$l['button']['LanguageSettings'] = "Nyelv beállítások";
$l['button']['LoggingSettings'] = "Eseménynaplo beállítások";
$l['button']['InterfaceSettings'] = "Eszköz beállítások";

$l['button']['TestUserConnectivity'] = "Felhasználó kapcsolatának tesztje";
$l['button']['DisconnectUser'] = "Felhasználó kapcsolatának megszakítása";

$l['button']['ManageBackups'] = "Manage Backups";
$l['button']['CreateBackups'] = "Create Backups";

$l['button']['ListOperators'] = "Kezelők listája";
$l['button']['NewOperator'] = "Új kezelő";
$l['button']['EditOperator'] = "Kezelő szerkesztése";
$l['button']['RemoveOperator'] = "Kezelő eltávolítása";

$l['button']['ProcessQuery'] = "Folyamat lekérdezés";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['RateInfo'] = "Rate Information";
$l['title']['PlanInfo'] = "Plan Information";
$l['title']['TimeSettings'] = "Time Settings";
$l['title']['BandwidthSettings'] = "Bandwidth Settings";
$l['title']['PlanRemoval'] = "Plan Removal";

$l['title']['Backups'] = "Backups";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS Tables";
$l['title']['daloRADIUSTables'] = "daloRADIUS Tables";


$l['title']['IPPoolInfo'] = "IP-Pool Információ";

$l['title']['BusinessInfo'] = "Üzleti Információ";

$l['title']['CleanupRecords'] = "Rekordok takarítása";
$l['title']['DeleteRecords'] = "Rekordok törlése";

$l['title']['RealmInfo'] = "Övezet információ";

$l['title']['ProxyInfo'] = "Proxy Információ";

$l['title']['VendorAttribute'] = "Gyártó tulajdonságai";

$l['title']['AccountRemoval'] = "Fiók eltávolítás";
$l['title']['AccountInfo'] = "Fiók információ";

$l['title']['Profiles'] = "Profilok";
$l['title']['ProfileInfo'] = "Profil információ";

$l['title']['GroupInfo'] = "Csoport információ";
$l['title']['GroupAttributes'] = "Csoport tulajdonságok";

$l['title']['NASInfo'] = "NAS Információ";
$l['title']['NASAdvanced'] = "NAS Speciális";

$l['title']['UserInfo'] = "Felhasználó információk";
$l['title']['BillingInfo'] = "Billing Info";

$l['title']['Attributes'] = "Tulajdonságok";
$l['title']['ProfileAttributes'] = "Profil tulajdonságok";

$l['title']['HotspotInfo'] = "Hotspot Információ";
$l['title']['HotspotRemoval'] = "Hotspot eltávolítás";

$l['title']['ContactInfo'] = "Kapcsolattartó információk";

$l['title']['Plan'] = "Plan";

$l['title']['Profile'] = "Profile";
$l['title']['Groups'] = "Csoportok";
$l['title']['RADIUSCheck'] = "RADIUS kérés tulajdonságai";
$l['title']['RADIUSReply'] = "RADIUS válaszok tulajdonságai";

$l['title']['Settings'] = "Beállítások";
$l['title']['DatabaseSettings'] = "Adatbázis beállítások";
$l['title']['DatabaseTables'] = "Adatbázis táblák";
$l['title']['AdvancedSettings'] = "Haladó beállítások";

$l['title']['Advanced'] = "Haladó";
$l['title']['Optional'] = "Opcionális";

/* ********************************************************************************** */


/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Bejelentlezés szükséges";
$l['text']['LoginPlease'] = "Kérlek jelentkezz be";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "Vezetéknév";
$l['ContactInfo']['LastName'] = "Keresztnév";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Cím";
$l['ContactInfo']['WorkPhone'] = "Munkahelyi telefon";
$l['ContactInfo']['HomePhone'] = "Otthoni telefon";
$l['ContactInfo']['Phone'] = "Phone";
$l['ContactInfo']['MobilePhone'] = "Mobil telefon";
$l['ContactInfo']['Notes'] = "Megjegyzés";
$l['ContactInfo']['EnableUserUpdate'] = "Enable User Update";

$l['ContactInfo']['OwnerName'] = "Tulajdonos neve";
$l['ContactInfo']['OwnerEmail'] = "Tulajdonos e-mail címe";
$l['ContactInfo']['ManagerName'] = "Manager neve";
$l['ContactInfo']['ManagerEmail'] = "Manager Email címe";
$l['ContactInfo']['Company'] = "Cég";
$l['ContactInfo']['Address'] = "Cím";
$l['ContactInfo']['City'] = "Város";
$l['ContactInfo']['State'] = "Megye";
$l['ContactInfo']['Zip'] = "Irsz.:";
$l['ContactInfo']['Phone1'] = "Telefon 1";
$l['ContactInfo']['Phone2'] = "Telefon 2";
$l['ContactInfo']['HotspotType'] = "Hotspot Típusa";
$l['ContactInfo']['CompanyWebsite'] = "Cég honlapja";
$l['ContactInfo']['CompanyPhone'] = "Cég telefonja";
$l['ContactInfo']['CompanyEmail'] = "Cég emailje";
$l['ContactInfo']['CompanyContact'] = "Cég kapcsolattartó";

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


$l['Intro']['billhistorymain.php'] = "Billing History";
$l['Intro']['msgerrorpermissions.php'] = "Hiba";

$l['Intro']['mngradproxys.php'] = "Proxys kezelése";
$l['Intro']['mngradproxysnew.php'] = "Új Proxy";
$l['Intro']['mngradproxyslist.php'] = "Proxy lista";
$l['Intro']['mngradproxysedit.php'] = "Proxy szerkesztése";
$l['Intro']['mngradproxysdel.php'] = "Proxy eltávolítása";

$l['Intro']['mngradrealms.php'] = "Övezet kezelés";
$l['Intro']['mngradrealmsnew.php'] = "Új övezet";
$l['Intro']['mngradrealmslist.php'] = "Övezetek listája";
$l['Intro']['mngradrealmsedit.php'] = "Övezetek szerkesztése";
$l['Intro']['mngradrealmsdel.php'] = "Övezet eltávolítása";

$l['Intro']['mngradattributes.php'] = "Gyártó tulajdonságok kezelése";
$l['Intro']['mngradattributeslist.php'] = "Gyártó tulajdonságok listája";
$l['Intro']['mngradattributesnew.php'] = "Új gyártó tulajdonság";
$l['Intro']['mngradattributesedit.php'] = "Gyártó tulajdonságok szerkesztése";
$l['Intro']['mngradattributessearch.php'] = "Keresési tulajdonságok";
$l['Intro']['mngradattributesdel.php'] = "Gyártó tulajdonság eltávolítása";
$l['Intro']['mngradattributesimport.php'] = "Import Vendor Dictionary";


$l['Intro']['acctactive.php'] = "Aktív rekortok elszámolása";
$l['Intro']['acctall.php'] = "Minden felhasználó elszámolása";
$l['Intro']['acctdate.php'] = "Dátum szerinti elszámolás";
$l['Intro']['accthotspot.php'] = "Hotspot elszámolás";
$l['Intro']['acctipaddress.php'] = "IP elszámolás";
$l['Intro']['accthotspotcompare.php'] = "Hotspot összehasonlítás";
$l['Intro']['acctmain.php'] = "Elszámoló oldal";
$l['Intro']['acctnasipaddress.php'] = "NAS IP elszámolás";
$l['Intro']['acctusername.php'] = "Felhasználó elszámolás";
$l['Intro']['acctcustom.php'] = "Egyéb elszámolások";
$l['Intro']['acctcustomquery.php'] = "Egyéb lekérdezés elszámolások";
$l['Intro']['acctmaintenance.php'] = "Elszámolási rekordok kezelése";
$l['Intro']['acctmaintenancecleanup.php'] = "Lejárt kapocsolatok eltávolítása";
$l['Intro']['acctmaintenancedelete.php'] = "Elszámóló rekordok törlése";

$l['Intro']['billmain.php'] = "Számlázás";
$l['Intro']['ratesmain.php'] = "Rates Billing Page";
$l['Intro']['billratesdate.php'] = "Rates Prepaid Accounting";
$l['Intro']['billratesdel.php'] = "Díjtétel törlése";
$l['Intro']['billratesedit.php'] = "Díjszabás részletek szerkesztése";
$l['Intro']['billrateslist.php'] = "Díjtáblázat";
$l['Intro']['billratesnew.php'] = "Új díjtétel";

$l['Intro']['paypalmain.php'] = "PayPal Transactions Page";
$l['Intro']['billpaypaltransactions.php'] = "PayPal Transactions Page";

$l['Intro']['billhistoryquery.php'] = "Billing History";

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

$l['Intro']['giseditmap.php'] = "Térkép mód szerkesztése";
$l['Intro']['gismain.php'] = "GIS Térkép";
$l['Intro']['gisviewmap.php'] = "Térkép mód nézet";

$l['Intro']['graphmain.php'] = "Használti grafikon";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Teljes forgalom használatának összehasonlítása";
$l['Intro']['graphsalltimelogins.php'] = "Összes bejelentkezés";
$l['Intro']['graphsoveralldownload.php'] = "Felhasználói letöltések";
$l['Intro']['graphsoveralllogins.php'] = "Felhasználói belépések";
$l['Intro']['graphsoverallupload.php'] = "Felhasználói feltöltések";

$l['Intro']['rephistory.php'] = "Esemény történet";
$l['Intro']['replastconnect.php'] = "Utolsó 50 kapcsolódási próbálkozás";
$l['Intro']['repstatradius.php'] = "Kiszolgálói információk";
$l['Intro']['repstatserver.php'] = "Szerver Státusz és információk";
$l['Intro']['reponline.php'] = "Online Felhasználók listája";
$l['Intro']['replogssystem.php'] = "Rendszernapló";
$l['Intro']['replogsradius.php'] = "RADIUS Szerver napló";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS napló";
$l['Intro']['replogsboot.php'] = "Boot napló";
$l['Intro']['replogs.php'] = "Naplók";

$l['Intro']['rephsall.php'] = "Hotspotok listája";
$l['Intro']['repmain.php'] = "Jelentések oldal";
$l['Intro']['repstatus.php'] = "Statusz oldal";
$l['Intro']['replogs.php'] = "Naplózás oldal";
$l['Intro']['reptopusers.php'] = "Csúcs felhasználók";
$l['Intro']['repusername.php'] = "Felhasználók listája";

$l['Intro']['mngbatch.php'] = "Több felhasználó hozzáadása";
$l['Intro']['mngdel.php'] = "Felhasználó eltávolítása";
$l['Intro']['mngedit.php'] = "Felhasználói részletek szerkesztése";
$l['Intro']['mnglistall.php'] = "Felhasználó lista";
$l['Intro']['mngmain.php'] = "Felhasználók és Hotspot-ok managelése";
$l['Intro']['mngnew.php'] = "Új felhasználó";
$l['Intro']['mngnewquick.php'] = "Felhasználó gyors hozzáadása";
$l['Intro']['mngsearch.php'] = "Felhasználó keresése";

$l['Intro']['mnghsdel.php'] = "Hotspot eltávolítása";
$l['Intro']['mnghsedit.php'] = "Hotspot részletek szerkesztése";
$l['Intro']['mnghslist.php'] = "Hotspotok listája";
$l['Intro']['mnghsnew.php'] = "Új Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Felhasználói csoport eltávolítása";
$l['Intro']['mngradusergroup.php'] = "Felhasználói csoport konfigurálása";
$l['Intro']['mngradusergroupnew.php'] = "Új felhasználói csoport";
$l['Intro']['mngradusergrouplist'] = "Felhasználói csoportok listája";
$l['Intro']['mngradusergrouplistuser'] = "Felhasználói csoportok részletes listája";
$l['Intro']['mngradusergroupedit'] = "Felhasználói csoport szerkesztése a köv. felhasználónak:";

$l['Intro']['mngradippool.php'] = "IP-Pool konfiguráció";
$l['Intro']['mngradippoolnew.php'] = "Új IP-Pool";
$l['Intro']['mngradippoollist.php'] = "IP-Pool-ok listája";
$l['Intro']['mngradippooledit.php'] = "IP-Pool szerkesztése";
$l['Intro']['mngradippooldel.php'] = "IP-Pool eltávolítása";

$l['Intro']['mngradnas.php'] = "NAS konfiguráció";
$l['Intro']['mngradnasnew.php'] = "Új NAS bejegyzés";
$l['Intro']['mngradnaslist.php'] = "NAS Listázása";
$l['Intro']['mngradnasedit.php'] = "NAS bejegyzés szerkesztése";
$l['Intro']['mngradnasdel.php'] = "NAS bejegyzés eltávolítása";

$l['Intro']['mngradprofiles.php'] = "Profil konfigurálása";
$l['Intro']['mngradprofilesedit.php'] = "Profilok szerkesztése";
$l['Intro']['mngradprofilesduplicate.php'] = "Profilok másolása";
$l['Intro']['mngradprofilesdel.php'] = "Profilok törlése";
$l['Intro']['mngradprofileslist.php'] = "Profilok listázása";
$l['Intro']['mngradprofilesnew.php'] = "Új profil";

$l['Intro']['mngradgroups.php'] = "Csoportok konfigurálása";

$l['Intro']['mngradgroupreplynew.php'] = "Új csoport válasz";
$l['Intro']['mngradgroupreplylist.php'] = "Csoport válaszok listája";
$l['Intro']['mngradgroupreplyedit.php'] = "Csoport válasz szerkesztése a köv. csoportnál:";
$l['Intro']['mngradgroupreplydel.php'] = "Csoport válasz eltávolítása";
$l['Intro']['mngradgroupreplysearch.php'] = "Csoport válasz keresése";

$l['Intro']['mngradgroupchecknew.php'] = "Új csoport ellenőrzés";
$l['Intro']['mngradgroupchecklist.php'] = "Csoport ellenőrzések listája";
$l['Intro']['mngradgroupcheckedit.php'] = "Csoport ellenőrzések szerkesztése:";
$l['Intro']['mngradgroupcheckdel.php'] = "Csoport ellenőrzés eltávolítása";
$l['Intro']['mngradgroupchecksearch.php'] = "Csoport ellenőrzés keresése";

$l['Intro']['configdb.php'] = "Adatbázis konfiguráció";
$l['Intro']['configlang.php'] = "Nyelv konfiguráció";
$l['Intro']['configlogging.php'] = "Bejegyzések konfigurálása";
$l['Intro']['configinterface.php'] = "Web felület konfigurálása";
$l['Intro']['configmainttestuser.php'] = "Felhasználó kapcsolatának tesztje";
$l['Intro']['configmain.php'] = "Adatbázis konfiguráció";
$l['Intro']['configmaint.php'] = "Karbantartás";
$l['Intro']['configmaintdisconnectuser.php'] = "Disconnect User";
$l['Intro']['configbusiness.php'] = "Üzleti részletek";
$l['Intro']['configbusinessinfo.php'] = "Üzleti információk";
$l['Intro']['configbackup.php'] = "Biztonsági mentés";
$l['Intro']['configbackupcreatebackups.php'] = "Create Backups";
$l['Intro']['configbackupmanagebackups.php'] = "Manage Backups";

$l['Intro']['configoperators.php'] = "Operátorok konfigurálása";
$l['Intro']['configoperatorsdel.php'] = "Operátor eltávolítása";
$l['Intro']['configoperatorsedit.php'] = "Operátor beállítások szerkesztése";
$l['Intro']['configoperatorsnew.php'] = "Új operátor";
$l['Intro']['configoperatorslist.php'] = "Operátorok listája";

$l['Intro']['login.php'] = "Bejelentkezés";

$l['captions']['providebillratetodel'] = "Provide the rate entry type which you would like to remove";
$l['captions']['detailsofnewrate'] = "You may fill below details for the new rate";
$l['captions']['filldetailsofnewrate'] = "Fill below the details for the new rate entry";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/


$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "List all PayPal transactions";
$l['helpPage']['billhistoryquery'] = "List all billing history for a user(s)";

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

$l['helpPage']['msgerrorpermissions'] = "You do not have permissions to access the page. <br/>
Please consult with your System Administrator. <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "To remove a user entry from the database you must provide the username of the account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Profiles Management</b> - Manage Profiles for Users by composing a set of Reply Attributes and Check Attributes <br/>
Profiles can be thought of as the composition of Group Reply and Group Check. <br/>
<h200><b>List Profiles </b></h200> - List Profiles <br/>
<h200><b>New Profile </b></h200> - Add a Profile <br/>
<h200><b>Edit Profile </b></h200> - Edit a Profile <br/>
<h200><b>Delete Profile </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>Edit Profile </b></h200> - Edit a Profile <br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>Delete Profile </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofilesduplicate'] = "
<h200><b>Duplicate Profile </b></h200> - Duplicate a Profile's set of attributes to a new one with a different profile name <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>List Profiles </b></h200> - List Profiles <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>New Profile </b></h200> - Add a Profile <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>Groups Management</b> - Manage Group Reply and Group Check mappings (radgroupreply/radgroupcheck tables).<br/>
<h200><b>List Group Reply/Check </b></h200> - List Group Reply/Check Mappings<br/>
<h200><b>Search Group Reply/Check </b></h200> - Search a Group Reply/Check Mapping (you may use wildcards) <br/>
<h200><b>New Group Reply/Check </b></h200> - Add a Group Reply/Check Mapping <br/>
<h200><b>Edit Group Reply/Check </b></h200> - Edit a Group Reply/Check Mapping <br/>
<h200><b>Delete Group Reply/Check </b></h200> - Delete a Group Reply/Check Mapping <br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>New Group Check </b></h200> - Add a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>Delete Group Check </b></h200> - Delete a Group Check Mapping <br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>List Group Check </b></h200> - List Group Check Mappings<br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>Edit Group Check </b></h200> - Edit a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>Search Group Check </b></h200> - Search a Group Check Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>New Group Reply </b></h200> - Add a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>Delete Group Reply </b></h200> - Delete a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>List Group Reply </b></h200> - List Group Reply Mappings<br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>Edit Group Reply </b></h200> - Edit a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>Search Group Reply </b></h200> - Search a Group Reply </ Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";


$l['helpPage']['mngradippool'] = "
<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>
<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>
<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>
<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "To remove a nas ip/host entry from the database you must provide the ip/host of the account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";



$l['helpPage']['mnghsdel'] = "To remove a hotspot from the database you must provide the hotspot's name<br/>";
$l['helpPage']['mnghsedit'] = "You may edit below details for hotspot<br/>";
$l['helpPage']['mnghsnew'] = "You may fill below details for new hotspot addition to database";
$l['helpPage']['mnghslist'] = "List of all hotspots in the database. You may use the quick links to edit or delete a hotspot from the database.";

$l['helpPage']['configdb'] = "
<b>Database Settings</b> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Global Settings</b></h200> - Database Storage Engine<br/>
<h200><b>Tables Settings</b></h200> - If not using the default FreeRADIUS schema you may change the names
of the table names<br/>
<h200><b>Advanced Settings</b></h200> - If you wish to store passwords for users in the database not in
plain text but rather have it encrypted somehow you may choose one of MD5 or Crypt<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
Please make sure that the filename that you specify has write permissions by the webserver<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>
";
$l['helpPage']['configmain'] = "
<b>Global Settings</b><br/>
<h200><b>Database Settings</b></h200> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>

<b>Sub-Category Configuration</b>
<h200><b>Maintenance </b></h200> - Maintenance options for Testing users connections or terminating their sessions <br/>
<h200><b>Operators</b></h200> - Configure Operators Access Control List (ACL) <br/>
";
$l['helpPage']['configbusiness'] = "
<b>Business Information</b><br/>
<h200><b>Business Contact</b></h200> - set the business contact information (owners, title, address, phone, etc)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b>Maintenance</b><br/>
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid<br/>
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server
to disconnect a user and terminate his/her session in a given NAS.<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid.<br/>
daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server to disconnect a user and terminate his/her session in a given NAS.<br/>
For terminating a user's session it is required that the NAS support the PoD or CoA packet types, please consult your NAS vendor or
documentation for this. Moreover, it is required to know the NAS ports for PoD or CoA packets, whereas newer NASs use port 3799
while other ones are configured to receive the packet on port 1700.

daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>


";
$l['helpPage']['configoperatorsdel'] = "To remove an operator from the database you must provide the username.";
$l['helpPage']['configoperatorsedit'] = "Edit the operator user details below";
$l['helpPage']['configoperatorsnew'] = "You may fill below details for a new operator user addition to database";
$l['helpPage']['configoperatorslist'] = "Listing all Operators in database";
$l['helpPage']['configoperators'] = "Operators Configuration";
$l['helpPage']['configbackup'] = "Perform Backup";
$l['helpPage']['configbackupcreatebackups'] = "Create Backups";
$l['helpPage']['configbackupmanagebackups'] = "Manage Backups";

$l['helpPage']['graphmain'] = "
<b>Graphs</b><br/>
<h200><b>Overall Logins/Hits</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of Logins (or 'hits' to the NAS) are displayed in a graph as well as accompanied by a table listing.<br/>
<h200><b>Overall Download Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Downloaded by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<h200><b>Overall Upload Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Upload by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<br/>
<h200><b>Alltime Logins/Hits</b></h200> - Plots a graphical chart of the Logins to the server for a given period of time.<br/>
<h200><b>Alltime Traffic Comparison</b></h200> - Plots a graphical chart of the Downloaded and Uploaded statisticse.
";
$l['helpPage']['graphsalltimelogins'] = "An All-Time statistics of Logins to the server based on a distribution over a period of time";
$l['helpPage']['graphsalltimetrafficcompare'] = "An All-Time statistics of Traffic through the server based on a distribution over a period of time.";
$l['helpPage']['graphsoveralldownload'] = "Plots a graphical chart of the Downloaded bytes to the server";
$l['helpPage']['graphsoverallupload'] = "Plots a graphical chart of the Uploaded bytes to the server";
$l['helpPage']['graphsoveralllogins'] = "Plots a graphical chart of the Login attempts to the server";



$l['helpPage']['rephistory'] = "Lists all activity performed on management items and provides information on <br/>
Creation Date, Creation By as well as Updated Date and Update By history fields";
$l['helpPage']['replastconnect'] = "Lists all login attempts to the RADIUS server, both successful and failed logins";
$l['helpPage']['replogsboot'] = "Monitor Operating System Boot log - equivalent to running the dmesg command.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS's Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS's Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Operating System Logfile.";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS Log</b></h200> - Monitor daloRADIUS's Logfile.<br/>
<h200><b>RADIUS Log</b></h200> - Monitor FreeRADIUS's Logfile - equivalent to /var/log/freeradius/radius.log or /usr/local/var/log/radius/radius.log.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>System Log</b></h200> - Monitor Operating System Logfile - equivalent to /var/log/syslog or /var/log/message on most platform.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>Boot Log</b></h200> - Monitor Operating System Boot log - equivalent to running the dmesg command.
";
$l['helpPage']['repmain'] = "
<b>General Reports</b><br/>
<h200><b>Online Users</b></h200> - Provides a listing of all users which are
found to be online through the accounting table in the database. The check which is being performed is for users
with no ending time (AcctStopTime) set. It is important to notice that these users may also be of stale sessions
which happens when NASs for some reason fail to send the accounting-stop packets.<br/>
<h200><b>Last Connection Attempts</b></h200> - Provides a listing of all Access-Accept and Access-Reject (accepted and failed) logins
for users. <br/> These are pulled from the database's postauth table which is required to be defined
in FreeRADIUS's config file to actually log these.<br/>
<h200><b>Top User</b></h200> - Provides a listing of the top N users for bandwidth consumption and session time used<br/><br/>
<b>Sub-Category Reports</b><br/>
<h200><b>Logs</b></h200> - Provides access to daloRADIUS logfile, FreeRADIUSs logfile, System's logfile and Boot logfile<br/>
<h200><b>Status</b></h200> - Provides information on server status and RADIUS Components status";
$l['helpPage']['repstatradius'] = "Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.
";
$l['helpPage']['repstatserver'] = "Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['repstatus'] = "<b>Status</b><br/>
<h200><b>Server Status</b></h200> - Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.<br/>
<h200><b>RADIUS Status</b></h200> - Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['reptopusers'] = "Records for top users, those which are listed below have gained the highest consumption of session
time or bandwidth usage. Listing users of category: ";
$l['helpPage']['repusername'] = "Records found for user:";
$l['helpPage']['reponline'] = "
The following table lists users who are currently connected to
the system. It is very much possible that there are stale connections,
meaning that users got disconnected but the NAS didn't send or wasn't
able to send a STOP accounting packet to the RADIUS server.
";


$l['helpPage']['mnglistall'] = "Listing users in database";
$l['helpPage']['mngsearch'] = "Felhasználó keresése: ";
$l['helpPage']['mngnew'] = "You may fill below details for new user addition to database<br/>";
$l['helpPage']['mngedit'] = "Edit the user details below.<br/>";
$l['helpPage']['mngdel'] = "To remove a user entry from the database you must provide the username of the account<br/>";
$l['helpPage']['mngbatch'] = "You may fill below details for new user addition to database.<br/>
Note that these settings will apply for all the users that you are creating.<br/>";
$l['helpPage']['mngnewquick'] = "The following user/card is of type prepaid.<br/>
The amount of time specified in Time Credit will be used as the Session-Timeout and Max-All-Session
radius attributes";

// accounting section
$l['helpPage']['acctactive'] = "
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>
";
$l['helpPage']['acctall'] = "
    Provides full accounting information for all sessions in the database.
<br/>
";
$l['helpPage']['acctdate'] = "
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
";
$l['helpPage']['acctipaddress'] = "
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
";
$l['helpPage']['acctmain'] = "
<b>General Accounting</b><br/>
<h200><b>User Accounting</b></h200> -
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
<h200><b>IP Accounting</b></h200> -
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
<h200><b>NAS Accounting</b></h200> -
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
<h200><b>Date Accounting</b></h200> -
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
<h200><b>All Accounting Records</b></h200> -
    Provides full accounting information for all sessions in the database.
<br/>
<h200><b>Active Records Accounting</b></h200> -
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>

<br/>
<b>Sub-Category Accounting</b><br/>
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.
<br/>
<h200><b>Hotspots</b></h200> -
    Provides information on the different managed hotspots, comparison, and other useful information.
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
";
$l['helpPage']['acctusername'] = "
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Provides full accounting information for all sessions which originated from this specific Hotspot.
    This list is computed by listing only those records in the radacct table with the CalledStationId
    field which match a Hotspot's MAC Address entry in the Hotspot's management database.
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Accounting information provided: <br/><br/>
    Hotspot Name - The Hotspot's name <br/>
    Unique Users - Users that have logined only through this hotspot <br/>
    Total Hits - The total logins that were performed from this hotspot (unique and non unique) <br/>
    Average Time - The average time a user spent in this hotspot <br/>
    Total Time - The accumolated spent time of all users in this hotspot <br/>

<br/>
    Provides a graph plot of different comparisons made <br/>
    Graphs: <br/><br/>
    Distribution of Unique users per hotspot <br/>
    Distribution of Hits per hotspot <br/>
    Distribution of Time usage per hotspot <br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    Provides full accounting information for all sessions which originated from this specific Hotspot.
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Provides a graph plot of different comparisons made.
<br/>
";
// accounting custom queries section
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.<br/>
    You may adjust the query to it's max by modifying the settings in the left sidebar.<br/>
<br/>
    <b> Between Dates </b> - Set the beginning and ending date.
<br/>
    <b> Where </b> - Set the field in the database you wish to match (like a key), choose if the value
    to match to should be Equal (=) or it Contains part of the value you search for (like a regex). If you
    choose to use the Contains operator you shouldn't add any wildcards of the common form '*' but rather
    the value you input will automatically be searched in this form: *value* (or in mysql style: %value%).
<br/>
    <b> Query Accounting Fields </b> - You may choose which fields you would like to present in the resulting
    list.
<br/>
    <b> Order By </b> - Choose by which field you would like to order the results and it's type (Ascending
    or descending)
<br/>
";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200><b>Cleanup stale-sessions</b></h200> -
    Stale-sesions may often exist because the NAS was unable to provide an accounting STOP record for the <br/>
    user session, resulting in a stale open session in the accounting records which simulates a fake logged-in user
    record (false positive).
<br/>
<h200><b>Delete accounting records</b></h200> -
    Deletion of accounting records in the database. It may not be wise to perform this or to allow other users
    except for a supervised administrator access to this page.
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
    Edit Map Mode - in this mode you are able to either Add or Delete Hotspots simply by clicking
    on a location of the map or by clicking on a hotspot (respectively).<br/><br/>
    <b> Adding Hotspot </b> - Simply click on a clear location of the map, you will be asked to provide
    the hotspot's name and it's MAC address. These are 2 crucial details later used to identify this hotspot
    in the accounting table. Always provide the correct MAC Address!
<br/><br/>
    <b> Deleting Hotspot </b> - Simply click on a hotspot's icon and you confirm the deletion of it from the
    database.
<br/>
";
$l['helpPage']['gisviewmap'] = "
    View Map Mode - in this mode you are able to browse the Hotspots as they are layed out
    in icons across the maps provided by GoogleMaps service.<br/><br/>

    <b> Clicking a Hotspot </b> -Will provide you with more in-depth detail on the hotspot.
    Such as the contact information for the hotspot, and statistics details.
<br/>
";
$l['helpPage']['gismain'] = "
<b> General Information </b>
GIS Mapping provides visual mappings of the hotspot location across the world's map using Google Maps API. <br/>
In the Management page you are able to add new hotspot entries to the database where there is also a field
called Geolocation, this is the numeric value that the Google Maps API uses in order to pin-point the exact
location of that hotspot on the map.<br/><br/>

<h200><b>2 Modes of Operation are provided:</b></h200>
One is the <b>View MAP</b> mode which enables 'surfing' through the world map
and view the current locations of the hotspots in the database and another one - <b>Edit MAP</b> - which is the mode
that one can use in order to create hotspot's visually by simply left-clicking on the map or removing
existing hotspot entries by left-clicking on existing hotspot flags.<br/><br/>

Another important issue is that each computer on the network requires a unique Registration code which you
can obtain from Google Maps API page by providing the complete web address to the hosted directory of
daloRADIUS application on your server. Once you have obtained that code from Google, simply paste it in the
Registration box and click the 'Register code' button to write it.
Then you may be able to use Google Maps services. <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "A felhasználónak nincs check attribútuma";
$l['messages']['noReplyAttributesForUser'] = "A felhasználónak nincs reply attribútuma";

$l['messages']['noCheckAttributesForGroup'] = "This group has no check attributes associated with it";
$l['messages']['noReplyAttributesForGroup'] = "This group has no reply attributes associated with it";

$l['messages']['nogroupdefinedforuser'] = "A felhasználó nem tartozik egyetlen csoporthoz sem";
$l['messages']['wouldyouliketocreategroup'] = "Létre kíván hozni egyet?";


$l['messages']['missingratetype'] = "Hiba:hiányzó sávszélesség nem törölhető";
$l['messages']['missingtype'] = "Hiba: hiányzó típus";
$l['messages']['missingcardbank'] = "Hiba: hiányzó kártya szolgáltató";
$l['messages']['missingrate'] = "Hiba: hiányzó sávszélesség";
$l['messages']['success'] = "Sikerült";
$l['messages']['gisedit1'] = "Üdvözöllek jelenleg szerkesztő módban vagy.";
$l['messages']['gisedit2'] = "Törli a jelölést a térképről és az adatbázisból?";
$l['messages']['gisedit3'] = "Kérem adjon nevet a HotSpotnak";
$l['messages']['gisedit4'] = "Cél megadása az adatbázisnak?";
$l['messages']['gisedit5'] = "Kérem adja meg a HotSpot nevét";
$l['messages']['gisedit6'] = "Kérem adja meg a MAC Addressét a Hotspotnak";

$l['messages']['gismain1'] = "Sikeresen frissítette a GoogleMaps API Registration codeot";
$l['messages']['gismain2'] = "Hiba: a file-t nem lehet megnyitni írásra:";
$l['messages']['gismain3'] = "Ellenőrizze a hozzáférési jogot. Adjon írási jogot a webserveren a user/group-nak";
$l['messages']['gisviewwelcome'] = "Üdvözli a  Enginx Visual Maps";

$l['messages']['loginerror'] = "<br/><br/>valamely a következőkből:<br/>
1. rossz felhasználónév/jelszó<br/>
2. administrator már bejelentkezett (egyidejüleg csak 1 engedélyezett) <br/>
3. ugy tűnik több mint 1 'administrator' felhasználó van az adatbázisban<br/>
";

$l['buttons']['savesettings'] = "Mentés";
$l['buttons']['apply'] = "Alkalmaz";

$l['menu']['Home'] = "Kezdőlap";
$l['menu']['Managment'] = "Management";
$l['menu']['Reports'] = "Jelentés";
$l['menu']['Accounting'] = "Nyilvántartás";
$l['menu']['Billing'] = "Számlázás";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Grafikonok";
$l['menu']['Config'] = "Beállítások";
$l['menu']['Help'] = "Segítség";

// TODO translation needed.
// once translated, delete these comment lines.
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
