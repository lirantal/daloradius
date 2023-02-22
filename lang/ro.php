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
 * Description:    Romanian language file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/ro.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS Meneger, Reportarea, Contabilitate si Facturare de <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Pool Name";
$l['all']['CalledStationId'] = "CalledStationId";
$l['all']['CallingStationID'] = "CallingStationID";
$l['all']['ExpiryTime'] = "Data de expirare";
$l['all']['PoolKey'] = "Pool Key";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Dictionar";
$l['all']['VendorID'] = "Vanzator ID";
$l['all']['VendorName'] = "Vanzator Name";
$l['all']['VendorAttribute'] = "Vanzator Attribute";
$l['all']['RecommendedOP'] = "Recomandat OP";
$l['all']['RecommendedTable'] = "Recomandat Table";
$l['all']['RecommendedTooltip'] = "Recomandat Tooltip";
$l['all']['RecommendedHelper'] = "Recomandat Helper";
/********************************************************************************/

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "RADIUS Dictionar Path";


$l['all']['Compare'] = "Compare";

$l['all']['Section'] = "Sectiune";
$l['all']['Item'] = "Element";

$l['all']['RemoveRadacctRecords'] = "Eliminati Inregistrarile contabile";

$l['all']['CleanupSessions'] = "Cur??ire sesiuni ?n v?rst? de peste";
$l['all']['DeleteSessions'] = "?tergere sesiuni ?n v?rst? de peste";

$l['all']['StartingDate'] = "?ncep?nd cu data";
$l['all']['EndingDate'] = "Pina pe data";

$l['all']['Realm'] = "Realm";
$l['all']['RealmName'] = "Realm Nume";
$l['all']['RealmSecret'] = "Realm Secert";
$l['all']['AuthHost'] = "Auth Gazd?";
$l['all']['AcctHost'] = "Acct Host";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "hints";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy Nume";
$l['all']['ProxySecret'] = "Proxy Secert";
$l['all']['DeadTime'] = "Dead Timp";
$l['all']['RetryDelay'] = "Re?ncerca?i ?nt?rzierea";
$l['all']['RetryCount'] = "Re?ncerca?i Count";
$l['all']['DefaultFallback'] = "Implicit fallback";

$l['all']['SimultaneousUse'] = "Simultan-Utiliza?i
 ";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "NAS Shortname";
$l['all']['NasType'] = "NAS Tip";
$l['all']['NasPorts'] = "NAS Ports";
$l['all']['NasSecret'] = "NAS Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Comunitare";
$l['all']['NasDescription'] = "NAS Descriere";
$l['all']['PacketType'] = "Tip de pachete";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['HotSpots'] = "HotSpots";
$l['all']['HotSpotName'] = "Hotspot Nome";
$l['all']['Name'] = "Nume";
$l['all']['Username'] = "Nume de utilizator";
$l['all']['Password'] = "Parola";
$l['all']['PasswordType'] = "Parola Tip";
$l['all']['IPAddress'] = "Adresa IP";
$l['all']['Profile'] = "Profil";
$l['all']['Group'] = "Grupul";
$l['all']['Groupname'] = "Groupname";
$l['all']['ProfilePriority'] = "Profil de prioritate";
$l['all']['GroupPriority'] = "Grupul de prioritate";
$l['all']['CurrentGroupname'] = "Current Groupname";
$l['all']['NewGroupname'] = "New Groupname";
$l['all']['Priority'] = "Prioritate";
$l['all']['Attribute'] = "Atribut";
$l['all']['Operator'] = "Operator";
$l['all']['Value'] = "Valoare";
$l['all']['NewValue'] = "New Value";
$l['all']['MaxTimeExpiration'] = "Max Time / Expiration";
$l['all']['UsedTime'] = "Timpul utilizat";
$l['all']['Status'] = "Statut";
$l['all']['Usage'] = "Usage";
$l['all']['StartTime'] = "Ora de incepere";
$l['all']['StopTime'] = "Stop Time";
$l['all']['TotalTime'] = "Timp total";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "?nc?rcare";
$l['all']['Download'] = "Desc?rca";
$l['all']['Rollback'] = "Roll-back";
$l['all']['Termination'] = "?ncetarea";
$l['all']['NASIPAddress'] = "NAS IP Address";
$l['all']['Action'] = "Ac?iune";
$l['all']['UniqueUsers'] = "De utilizatori unici";
$l['all']['TotalHits'] = "Total Hits";
$l['all']['AverageTime'] = "Timpul mediu";
$l['all']['Records'] = "Inregistreaza";
$l['all']['Summary'] = "Summary";
$l['all']['Statistics'] = "Statistica";
$l['all']['Credit'] = "credit";
$l['all']['Used'] = "Uzad";
$l['all']['LeftTime'] = "Timp ?n continuare";
$l['all']['LeftPercent'] = "% Timp de st?nga";
$l['all']['TotalSessions'] = "Total Sessions";
$l['all']['LastLoginTime'] = "Login Ultima Ora";
$l['all']['TotalSessionTime'] = "Timpul total de ?edin??";
$l['all']['RateName'] = "Tarif Nume";
$l['all']['RateType'] = "Tarif Tip";
$l['all']['RateCost'] = "Pretul de cost";
$l['all']['Billed'] = "Facturat";
$l['all']['TotalUsers'] = "Total Utilizatori";
$l['all']['TotalBilled'] = "Total facturat";
$l['all']['CardBank'] = "Card bancar";
$l['all']['Type'] = "Tip";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "MAC Address";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "Codul PIN";
$l['all']['CreationDate'] = "Data cre?rii";
$l['all']['CreationBy'] = "Crearea de c?tre";
$l['all']['UpdateDate'] = "Data de actualizare";
$l['all']['UpdateBy'] = "Actualizare de ";

$l['all']['Discount'] = "Reducere";
$l['all']['BillAmount'] = "Suma facturat";
$l['all']['BillAction'] = "Suma facturat";
$l['all']['BillPerformer'] = "Document interpretat";
$l['all']['BillReason'] = "Motiv de facturare";
$l['all']['Lead'] = "Conduce";
$l['all']['Coupon'] = "Cupon";
$l['all']['OrderTaker'] = "Ordinul Taker";
$l['all']['BillStatus'] = "Bill Status";
$l['all']['LastBill'] = "Ultima Bill";
$l['all']['NextBill'] = "?nainte Bill";
$l['all']['PostalInvoice'] = "Factura po?tal";
$l['all']['FaxInvoice'] = "Fax facturii";
$l['all']['EmailInvoice'] = "Email facturii";

$l['all']['edit'] = "edita";
$l['all']['del'] = "del";
$l['all']['groupslist'] = "grupuri-list";
$l['all']['TestUser'] = "Test User";
$l['all']['Accounting'] = "Contabilitate";
$l['all']['RADIUSReply'] = "RADIUS R?spunde?i";

$l['all']['Disconnect'] = "Deconectare";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Re?ncearc?";
$l['all']['Count'] = "Count";
$l['all']['Requests'] = "Cereri";

$l['all']['DatabaseHostname'] = "Baza de date Hostname";
$l['all']['DatabaseUser'] = "Baza de date a utilizatorului";
$l['all']['DatabasePass'] = "Baza de date Pass";
$l['all']['DatabaseName'] = "Baza de date Nume";

$l['all']['PrimaryLanguage'] = "Limba principal?";

$l['all']['PagesLogging'] = "Conectarea de pagini (pagin? de vizite)";
$l['all']['QueriesLogging'] = "Jurnalizarea de Interog?ri (rapoarte ?i grafice)
)";
$l['all']['ActionsLogging'] = "Jurnalizarea de Ac?iuni (formularul sus?ine)";
$l['all']['FilenameLogging'] = "Jurnalizarea filename (complet calea)";
$l['all']['LoggingDebugOnPages'] = "Logging of Debug info on pages";
$l['all']['LoggingDebugInfo'] = "Logging of Debug Info";

$l['all']['PasswordHidden'] = "Activa?i Parola Ascunderea (asterisc va fi afi?at) ";
$l['all']['TablesListing'] = "R?nduri / ?nregistr?ri pe Tabele Afi?area paginii ";
$l['all']['TablesListingNum'] = "Activa?i Tabele Afi?area Numerotare";
$l['all']['AjaxAutoComplete'] = "Ajax permite completarea automat?";

$l['all']['RadiusServer'] = "Raza Server";
$l['all']['RadiusPort'] = "Radius Port";

$l['all']['UsernamePrefix'] = "Numele de utilizator Prefixul";
$l['all']['NumberInstances'] = "Numarul de cazuri, pentru a crea";
$l['all']['UsernameLength'] = "Lungimea de utilizator string";
$l['all']['PasswordLength'] = "Lungimea parola string";
$l['all']['Expiration'] = "expir?rii";
$l['all']['MaxAllSession'] = "Max-Toate-session";
$l['all']['SessionTimeout'] = "Session Timeout";
$l['all']['IdleTimeout'] = "ne?ntemeiat Timeout";
$l['all']['DBEngine'] = "DB Motor";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "Utilizatori";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "operatori";
$l['all']['billingrates'] = "tarife de facturare";
$l['all']['hotspots'] = "hotspots";

$l['all']['nas'] = "nas";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "radippool";
$l['all']['userinfo'] = "userinfo";
$l['all']['dictionary'] = "dic?ionar";
$l['all']['realms'] = "domenii";
$l['all']['proxys'] = "proxys";
$l['all']['billingpaypal'] = "facturare paypal";
$l['all']['billingplans'] = "planuri de facturare";
$l['all']['billinghistory'] = "de facturare de istorie";
$l['all']['billinginfo'] = "utilizator de facturare info";
$l['all']['PaymentDate'] = "Data de plat?";
$l['all']['PaymentStatus'] = "Payment Status";
$l['all']['FirstName'] = "Stare de plat?";
$l['all']['LastName'] = "Nume";
$l['all']['PayerStatus'] = "Nume";
$l['all']['PaymentAddressStatus'] = "Pl?titor Status";
$l['all']['PayerEmail'] = "adresa de plat? Status";
$l['all']['TxnId'] = "Pl?titor de email";
$l['all']['PlanTimeType'] = "Tranasction Id-ul";
$l['all']['PlanTimeBank'] = "Ora Tip Plan";
$l['all']['PlanTimeRefillCost'] = "Planul de Banca Timpul";
$l['all']['PlanTrafficRefillCost'] = "Planul Refill cost";
$l['all']['PlanBandwidthUp'] = "Plan Bandwidth Up";
$l['all']['PlanBandwidthDown'] = "Plan Bandwidth Down";
$l['all']['PlanTrafficTotal'] = "Planul de Trafic Total";
$l['all']['PlanTrafficDown'] = "Plan Traffic Down";
$l['all']['PlanTrafficUp'] = "Plan Traffic Up";
$l['all']['PlanRecurring'] = "Plan Recurring";
$l['all']['PlanRecurringPeriod'] = "Planul Recurring";
$l['all']['PlanCost'] = "Planul Recurring Perioada";
$l['all']['PlanSetupCost'] = "Planul de cost de instalare";
$l['all']['PlanTax'] = "Planul de taxe";
$l['all']['PlanCurrency'] = "Planul de Curs";
$l['all']['PlanGroup'] = 'Planul de profil ("Grupul")';
$l['all']['PlanType'] = "Planul de tip";
$l['all']['PlanName'] = "Planul Nume";
$l['all']['PlanId'] = "Planul de Id-ul";
$l['all']['Quantity'] = "Cantitate";
$l['all']['ReceiverEmail'] = "Receptor de email";
$l['all']['Business'] = "afaceri";
$l['all']['Tax'] = "taxe";
$l['all']['Cost'] = "Cost";
$l['all']['TransactionFee'] = "tranzac?ie";
$l['all']['PaymentCurrency'] = "Curs de plat?";
$l['all']['AddressRecipient'] = "Adresa Recipient";
$l['all']['Street'] = "Street";
$l['all']['Country'] = "Tara";

$l['all']['CountryCode'] = "Codu postal";
$l['all']['City'] = "Codul de ?ar?";
$l['all']['State'] = "Stat";

$l['all']['Month'] = "Lună";

$l['all']['Zip'] = "Zip";
$l['all']['BusinessName'] = "Nume de afacere";
$l['all']['BusinessPhone'] = "Telefon de afaceri";
$l['all']['BusinessAddress'] = "adresa de afaceri";
$l['all']['BusinessWebsite'] = "pagina de web a afacerii";
$l['all']['BusinessEmail'] = "email afaceti";
$l['all']['BusinessContactPerson'] = "Persoana de contact de afaceri";
$l['all']['DBPasswordEncryption'] = "DB Parola Criptare Tip";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['Username'] = "Tip de utilizator";

$l['Tooltip']['UsernameWildcard'] = "Sugestie: puteti folosi caracterul * sau% pentru a specifica un wildcard";

$l['Tooltip']['HotspotName'] = "Tip de Hotspot nume";

$l['Tooltip']['NasName'] = "Tip de SNC nume";

$l['Tooltip']['GroupName'] = "Tip Numele de grup";

$l['Tooltip']['AttributeName'] = "Tip de atribut nume";

$l['Tooltip']['VendorName'] = "Tip de v?nz?tor nume";

$l['Tooltip']['PoolName'] = "Tip de Biliard nume";

$l['Tooltip']['IPAddress'] = "Tip de adres? IP";

$l['Tooltip']['Filter'] = "Tip un filtru, poate fi orice sir de caractere alfa-numerice. L?sa?i gol pentru a se potrivi cu nimic.";

$l['Tooltip']['Date'] = "Tip de la data <br/> exemplu: 1982-06-04 (YMD)";

$l['Tooltip']['RateName'] = "Tip rata nume";

$l['Tooltip']['OperatorName'] = "Tip de Operator nume";

$l['Tooltip']['BillingPlanName'] = "Tip de facturare Planul de nume";


$l['Tooltip']['EditRate'] = "Edit Tarif";

$l['Tooltip']['RemoveRate'] = "Remove Tarif";


$l['Tooltip']['rateNameTooltip'] = "Rata nume prietenos, <br/>

                    pentru a descrie cu scopul de a rata ";

$l['Tooltip']['rateTypeTooltip'] = "Rata de tip, pentru a descrie <br/>

                    modul de func?ionare a rata ";

$l['Tooltip']['rateCostTooltip'] = "Rata de cost suma";


$l['Tooltip']['planNameTooltip'] = "Planul lui nume. Aceasta este <br/>
                    Un nume prietenos descrie
                    Characeristics a planului ";
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

$l['Tooltip']['EditIPPool'] = "Editare IP-Pool";
$l['Tooltip']['RemoveIPPool'] = "Elimina?i IP-Pool";
$l['Tooltip']['EditIPAddress'] = "Editare IP Address";
$l['Tooltip']['RemoveIPAddress'] = "Editare IP Address";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Proxy numele";
$l['Tooltip']['proxyRetryDelayTooltip'] = "De timp (?n secunde) pentru a a?tepta <br/>"
                                        . "pentru un r?spuns de la proxy, <br/>"
                                        . "?nainte de re-trimiterea proxied request.";
$l['Tooltip']['proxyRetryCountTooltip'] = "Num?rul de re?ncearc? pentru a trimite <br/>"
                                        . "?nainte de a ?n sus, ?i de a trimite un <br/>"
                                        . "Respinge mesaj la SNC.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "Dac? serverul de origine nu r?spunde <br/>"
                                      . "la oricare dintre multiple re?ncearc?, <br/>"
                                      . "Apoi FreeRADIUS va opri trimiterea <br/>"
                                      . 'proxy a cererilor, ?i marca?i-o "moarte".';
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "Dac? toate exact? domenii <br/>nu r?spunde, se poate ?ncerca<br/>";
$l['Tooltip']['realmNameTooltip'] = "Realm numele";
$l['Tooltip']['realmTypeTooltip'] = "Setare pentru a raz? de implicit";
$l['Tooltip']['realmSecretTooltip'] = "Realm RADIUS ?mp?rt??it secrete";
$l['Tooltip']['realmAuthhostTooltip'] = "Realm autentificare gazd?";
$l['Tooltip']['realmAccthostTooltip'] = "Realm contabile gazd?";
$l['Tooltip']['realmLdflagTooltip'] = 'Permite pentru echilibrarea ?nc?rc?rii <br/>'
                                    . 'Valorile permise sunt "fail_over" <br/>'
                                    . '?i "round_robin".';

$l['Tooltip']['realmNostripTooltip'] = "Fie pentru a benzii sau nu <br/>

                    t?r?m sufix";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "Example: Cisco<br/>&nbsp;&nbsp;&nbsp;
                                        Numele vanzatorului.<br/>&nbsp;&nbsp;&nbsp;";
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



$l['Tooltip']['AttributeEdit'] = "Editare atribut";

$l['Tooltip']['UserEdit'] = "Editare Utilizator";
$l['Tooltip']['HotspotEdit'] = "Editare Hotspot";
$l['Tooltip']['EditNAS'] = "Editare SNC";
$l['Tooltip']['RemoveNAS'] = "Elimina?i SNC";

$l['Tooltip']['EditUserGroup'] = "Editare grup de utilizatori";
$l['Tooltip']['ListUserGroups'] = "Lista de utilizatori Grupuri";

$l['Tooltip']['EditProfile'] = "Editare profil";

$l['Tooltip']['EditRealm'] = "Editare Realm";
$l['Tooltip']['EditProxy'] = "Editare Proxy";

$l['Tooltip']['EditGroup'] = "Editare grup";

$l['FormField']['mngradgroupcheck.php']['Tooltip']['value'] = "Dac? specifica?i valoarea apoi numai unice care s? se potriveasc? at?t groupname ?i specifice de valoare pe care a?i specificat va fi eliminat. Dac? omite?i valoarea apoi toate ?nregistr?rile pentru c? groupname special vor fi eliminate! ";

$l['FormField']['mngradgroupreplydel.php']['Tooltip']['value'] = "Dac? specifica?i valoarea apoi numai unice care s? se potriveasc? at?t groupname ?i specifice de valoare pe care a?i specificat va fi eliminat. Dac? omite?i valoarea apoi toate ?nregistr?rile pentru c? groupname special vor fi eliminate! ";

$l['FormField']['mngradnasnew.php']['Tooltip']['NasShortname'] = "(nume descriptiv)";

$l['FormField']['mngradusergroupdel.php']['Tooltip']['Groupname'] = "Dac? specifica?i grup atunci numai unic de ?nregistrare care se potrive?te at?t de nume de utilizator ?i de grup pe care a?i specificat vor fi eliminate . Dac? omite?i grupului apoi toate ?nregistr?rile pentru c? special vor fi eliminate de utilizator! ";


$l['Tooltip']['usernameTooltip'] = "exact ca numele de utilizator a utilizatorului <br/>"
                                 . "Se va folosi pentru a se conecta la sistem";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "Parolele sunt sensetive ?n <br/>"
                                 . "Anumitor sisteme a?a ia extra grija";
$l['Tooltip']['groupTooltip'] = "Utilizatorul va fi ad?ugat la acest grup. <br/>
Prin alocarea de un utilizator la un anumit grup <br/>
?n care utilizatorul este supus atribute ale grupului ";
$l['Tooltip']['macaddressTooltip'] = "Exemplu: 00-AA-BB-CC-DD-EE <br/>
Adresa MAC format ar trebui s? fie acela?i <br/>
Ca SNC trimite. De cele mai multe ori acest lucru este, f?r? <br/>
Orice caractere. ";
$l['Tooltip']['pincodeTooltip'] = "Exemplu: khrivnxufi101 <br/>
Acest lucru este exact pincode ca utilizatorul va intra ?ntr-o. <br/>
Puteti utiliza caractere alfa numeric, cazul este sensituve ";
$l['Tooltip']['usernamePrefixTooltip'] = "Exemplu: TMP_ POP_ WIFI1_ <br/>
Acest nume de utilizator prefix vor fi ad?ugate la <br/>
Generat de utilizator final. ";
$l['Tooltip']['instancesToCreateTooltip'] = "Exemplu: 100 <br/>
Valoarea aleatoare utilizatorii pentru a crea <br/>
Cu specificate profil. ";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Exemplu: 8 <br/>
De caractere lungime de numele de utilizator <br/>
De a fi creat. Recomandat 8-12 caractere. ";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Exemplu: 8 <br/>
De caractere de lungime parola <br/>
De a fi creat. Recomandat 8-12 caractere. ";


$l['Tooltip']['hotspotNameTooltip'] = "Exemplu: Hotel Stratocaster <br/>
Un nume prietenos din hotspot <br/> ";

$l['Tooltip']['hotspotMacaddressTooltip'] = "Exemplu: 00: aa: bb: cc: dd: ee <br/>
Adresa MAC a SNC <br/> ";

$l['Tooltip']['geocodeTooltip'] = "Exemplu: -1.002, -2.201 <br/>
Aceasta este loca?ia GooleMaps codul folosit <br/>
PIN-ul de la Hotspot / SNC de pe hart? (a se vedea GIS). ";


/************************************************* ********************************** * /

/ * ************************************************ **********************************
* Link-uri ?i butoane
************************************************** **********************************/
$l['button']['ClearSessions'] = "Clear Sessions";

$l['button']['ListRates'] = "Lista Preturi ";
$l['button']['NewRate'] = "Tarif nou ";
$l['button']['EditRate'] = "Editare Rate ";
$l['button']['RemoveRate'] = "Elimina?i Rate ";

$l['button']['ListPlans'] = "Lista Planuri ";
$l['button']['NewPlan'] = "Nou plan ";
$l['button']['EditPlan'] = "Editare Planul ";
$l['button']['RemovePlan'] = "Elimina?i Planul ";

$l['button']['ListRealms'] = "Lista domenii ";
$l['button']['NewRealm'] = "Noul Realm";
$l['button']['EditRealm'] = "editare Realm";
$l['button']['RemoveRealm'] = "Sterge Realm";

$l['button']['ListProxys'] = "Lista Proxys";
$l['button']['NewProxy'] = "New Proxy";
$l['button']['EditProxy'] = "editare Proxy";
$l['button']['RemoveProxy'] = "Sterge Proxy";

$l['button']['ListAttributesforVendor'] = "Lista de atribute pentru Vendor:";
$l['button']['NewVendorAttribute'] = "Nou distribuitorul de atribut";
$l['button']['EditVendorAttribute'] = "Editare furnizor de atribut ";
$l['button']['SearchVendorAttribute'] = "Cautati atribut ";
$l['button']['RemoveVendorAttribute'] = "Elimina?i furnizor de atribut ";
$l['button']['ImportVendorDictionary'] = "Import Vendor Dictionar ";


$l['button']['BetweenDates'] = "intre Datele: ";
$l['button']['Where'] = "Unde ";
$l['button']['AccountingFieldsinQuery'] = "Domenii de Contabilitate ?n Interogare: ";
$l['button']['OrderBy'] = "Prin Ordinul ";
$l['button']['HotspotAccounting'] = "Hotspot de contabilitate ";
$l['button']['HotspotsComparison'] = "Compara?ie hotspots ";

$l['button']['CleanupStaleSessions'] = "Cur??ire surmenat Sessions ";
$l['button']['DeleteAccountingRecords'] = "Delete ?nregistr?ri contabile";

$l['button']['Listusers'] =" Lista utilizatorilor ";
$l['button']['NewUser'] = "utilizator nou";
$l['button']['NewUserQuick'] = "utilizator nou - Ad?ugare rapid?";
$l['button']['BatchAddUsers'] = "Serie ad?uga utilizatori";
$l['button']['EditUser'] = "Editare utilizator";
$l['button']['SearchUsers'] = "Caut? utilizatori";
$l['button']['RemoveUsers'] = "?terge Utilizatorii";
$l['button']['ListHotspots'] = "Lista hotspots";
$l['button']['NewHotspot'] = "New Hotspot";
$l['button']['EditHotspot'] = "Editare Hotspot";
$l['button']['RemoveHotspot'] = "?terge Hotspot";

$l['button']['ListIPPools'] = "Lista de IP-Bazine";
$l['button']['NewIPPool'] = "Nou IP-Pool";
$l['button']['EditIPPool'] = "Editare IP-Pool";
$l['button']['RemoveIPPool'] = "?terge IP-Pool";

$l['button']['ListNAS'] = "Lista NAS";
$l['button']['NewNAS'] = "New NAS";
$l['button']['EditNAS'] = "Editare NAS";
$l['button']['RemoveNAS'] = "?terge NAS";

$l['button']['ListUserGroup'] = "Lista de utilizatori map?rile-Group";
$l['button']['ListUsersGroup'] = "Lista de utilizare al Grupului map?rile";
$l['button']['NewUserGroup'] = "utilizator nou-map?rile Group";
$l['button']['EditUserGroup'] =" Editare User-map?rile Group ";
$l['button']['RemoveUserGroup'] = "?terge-User Group map?rile";

$l['button']['ListProfiles'] = "Lista Profile";
$l['button']['NewProfile'] = "Profil nou";
$l['button']['EditProfile'] = "Editare Profil";
$l['button']['DuplicateProfile'] = "Duplicat Profil";
$l['button']['RemoveProfile'] = "?terge Profil";

$l['button']['ListGroupReply'] = "Lista de grup R?spunde?i map?rile";
$l['button']['SearchGroupReply'] = "Caut? Grupului R?spunde?i";
$l['button']['NewGroupReply'] = "New Group R?spunde?i Mapare";
$l['button']['EditGroupReply'] = "Edit Group R?spunde?i Mapare";
$l['button']['RemoveGroupReply'] = "?terge Grupului R?spunde?i Mapare";

$l['button']['ListGroupCheck'] = "Lista de grup Verifica?i map?rile";
$l['button']['SearchGroupCheck'] = "Caut? Grupului Check";
$l['button']['NewGroupCheck'] = "New Group Verifica?i Mapare";
$l['button']['EditGroupCheck'] = "Edit Group Verifica?i Mapare";
$l['button']['RemoveGroupCheck'] = "?terge Grupului Verifica?i Mapare";

$l['button']['UserAccounting'] = "utilizator de contabilitate";
$l['button']['IPAccounting'] = "IP Contabilitate";
$l['button']['NASIPAccounting'] = "SNC IP Contabilitate";
$l['button']['DateAccounting'] = "Data de contabilitate";
$l['button']['AllRecords'] = "Toate Records";
$l['button']['ActiveRecords'] = "active Records";

$l['button']['OnlineUsers'] = "Online Users";
$l['button']['LastConnectionAttempts'] = "Ultima conexiune ?ncerc?rile de";
$l['button']['TopUser'] = "Top utilizatorului";
$l['button']['istoric'] = "Istorie";
$l['button']['ServerStatus'] = "Server Status";
$l['button']['ServicesStatus'] = "Servicii de stare";

$l['button']['daloRADIUSLog'] = "daloRADIUS Jurnal";
$l['button']['RadiusLog'] = "Raza Jurnal";
$l['button']['SystemLog'] = "System Log";
$l['button']['bootlog'] = "boot Jurnal";

$l['button']['UserLogins'] = "User Conect?ri";
$l['button']['UserDownloads'] = "utilizator Download-uri";
$l['button']['UserUploads'] = "Utilizatorul ?ncarc?";
$l['button']['TotalLogins'] = "Total Conect?ri";
$l['button']['TotalTraffic'] = "Total de trafic";

$l['button']['ViewMAP'] = "Vizualizare hart?";
$l['button']['EditMAP'] = "Editare MAP";
$l['button']['RegisterGoogleMapsAPI'] = "RegisterGoogleMaps API";

$l['button']['DatabaseSettings'] = "Baza de date Set?ri";
$l['button']['LanguageSettings'] = "Language Settings";
$l['button']['LoggingSettings'] = "Jurnalizarea Set?ri";
$l['button']['InterfaceSettings'] = "Interface Settings";

$l['button']['TestUserConnectivity'] = "Test de utilizator Conectivitate";
$l['button']['DisconnectUser'] = "Deconectare utilizatorului";

$l['button']['ManageBackups'] = "Gestionare backup";
$l['button']['CreateBackups'] = "Crea?i copii de rezerv?";

$l['button']['ListOperators'] = "Lista Operatorii";
$l['button']['NewOperator'] = "nou operator";
$l['button']['EditOperator'] = "Editare Operator";
$l['button']['RemoveOperator'] = "?terge Operator";

$l['button']['ProcessQuery'] = "Procesul de Interogare";



/************************************************************************************/


/***********************************************************************************
* Titlurile
* Textul referitor la toate titlu anteturile ?n legende, tabele ?i layout cu tab-text
************************************************************************************
*/

$l['title']['RateInfo'] = "Rata de informa?ii";
$l['title']['PlanInfo'] = "Planul de informare";
$l['title']['TimeSettings'] = "Ora Set?ri";
$l['title']['BandwidthSettings'] = "Bandwidth Settings";
$l['title']['PlanRemoval'] = "Planul de eliminare";

$l['title']['backups'] = "backup";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS Tabele";
$l['title']['daloRADIUSTables'] = "daloRADIUS Tabele";

$l['title']['IPPoolInfo'] = "IP-Pool Info";

$l['title']['BusinessInfo'] = "Business Info";

$l['title']['CleanupRecords'] = "Cur??ire Records";
$l['title']['DeleteRecords'] = "Delete Records";

$l['title']['RealmInfo'] = "Realm Info";

$l['title']['ProxyInfo'] = "proxy Info";

$l['title']['VendorAttribute'] = "Vendor atribut";

$l['title']['AccountRemoval'] = "Contul de eliminare";
$l['title']['AccountInfo'] = "Informa?ii despre cont";

$l['title']['Profile'] = "Profiluri";
$l['title']['ProfileInfo'] = "Profil Info";

$l['title']['GroupInfo'] = "informa?ii despre grup";
$l['title']['GroupAttributes'] = "Grupul Atribute";

$l['title']['NASInfo'] = "SNC Info";
$l['title']['NASAdvanced'] = "SNC avansat?";

$l['title']['UserInfo'] = "User Info";
$l['title']['BillingInfo'] = "Facturare Info";

$l['title']['Atribute'] = "Atribute";
$l['title']['ProfileAttributes'] = "Profil Atribute";

$l['title']['HotspotInfo'] = "Hotspot Info";
$l['title']['HotspotRemoval'] = "Hotspot Eliminarea";

$l['title']['ContactInfo'] = "Contact";

$l['title']['Plan'] = "Planul";

$l['title']['Profile'] = "Profil";
$l['title']['Groups'] = "Grupuri";
$l['title']['RADIUSCheck'] = "Check Atribute";
$l['title']['RADIUSReply'] = "R?spunde?i Atribute";

$l['title']['Settings'] = "Set?ri";
$l['title']['DatabaseSettings'] = "Baza de date Set?ri";
$l['title']['DatabaseTables'] = "Baza de date Tabele";
$l['title']['AdvancedSettings'] = "Set?ri avansate";

$l['title']['Advanced'] = "Avansat";
$l['title']['Optional'] = "Optional";

/************************************************************************************/


/***********************************************************************************
* Text
* General de informa?ii de tip text, care este folosit, prin-afar? de pagini
************************************************************************************
*/

$l['text']['LoginRequired'] =" Necesit? autentificare ";
$l['text']['LoginPlease'] =" V? rug?m s? Login ";

/************************************************************************************/



/************************************************* **********************************
* Contact
* Servicii de la toate informa?iile de contact de tip text, utilizatorul info,
* hotspot proprietar informatii de contact, etc
************************************************** **********************************
*/

$l['ContactInfo']['FirstName'] = "Nume";
$l['ContactInfo']['LastName'] = "Nume";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Departament";
$l['ContactInfo']['WorkPhone'] = "telefon";
$l['ContactInfo']['HomePhone'] = "telefon acas?";
$l['ContactInfo']['Phone'] = "Telefon";
$l['ContactInfo']['MobilePhone'] = "Telefon mobil";
$l['ContactInfo']['Note'] = "Note";
$l['ContactInfo']['EnableUserUpdate'] = "Enable utilizatorului Update";

$l['ContactInfo']['OwnerName'] = "Proprietar Nume";
$l['ContactInfo']['OwnerEmail'] = "proprietar de email";
$l['ContactInfo']['ManagerName'] = "Nume Manager";
$l['ContactInfo']['ManagerEmail'] = "Email Manager";
$l['ContactInfo']['Company'] = "Compania";
$l['ContactInfo']['Address'] = "Adresa";
$l['ContactInfo']['City'] = "City";
$l['ContactInfo']['State'] = "State";
$l['ContactInfo']['Country'] = "Country";
$l['ContactInfo']['Zip'] = "Zip";
$l['ContactInfo']['Phone1'] = "Telefon 1";
$l['ContactInfo']['Phone2'] = "Telefon 2";
$l['ContactInfo']['HotspotType'] = "Hotspot de tip";
$l['ContactInfo']['CompanyWebsite'] = "site de companie";
$l['ContactInfo']['CompanyPhone'] = "Firma Telefon";
$l['ContactInfo']['CompanyEmail'] = "Compania de email";
$l['ContactInfo']['CompanyContact'] = "Firma Contact";

$l['ContactInfo']['PlanName'] = "Planul Nume";
$l['ContactInfo']['ContactPerson'] = "Persoana de contact";
$l['ContactInfo']['PaymentMethod'] = "Metoda de plat?";
$l['ContactInfo']['Cash'] = "cash";
$l['ContactInfo']['CreditCardNumber'] = "num?r de card de credit";
$l['ContactInfo']['CreditCardName'] = "Cardul de credit Nume";
$l['ContactInfo']['CreditCardVerificationNumber'] = "Cardul de credit num?rul de verificare";
$l['ContactInfo']['CreditCardType'] = "Tipul de card de credit";
$l['ContactInfo']['CreditCardExpiration'] = "expir?rii cardului de credit";

/************************************************************************************/


$l['Intro']['billhistorymain.php'] = "Facturare Istorie";
$l['Intro']['msgerrorpermissions.php'] = "Error";

$l['Intro']['mngradproxys.php'] = "Proxys Management";
$l['Intro']['mngradproxysnew.php'] = "New Proxy";
$l['Intro']['mngradproxyslist.php'] = "Lista Proxy";
$l['Intro']['mngradproxysedit.php'] = "Editare Proxy";
$l['Intro']['mngradproxysdel.php'] = "?terge Proxy";

$l['Intro']['mngradrealms.php'] = "domenii de Management";
$l['Intro']['mngradrealmsnew.php'] = "New Realm";
$l['Intro']['mngradrealmslist.php'] = "Lista Realm";
$l['Intro']['mngradrealmsedit.php'] = "Editare Realm";
$l['Intro']['mngradrealmsdel.php'] = "?terge Realm";

$l['Intro']['mngradattributes.php'] = "furnizor de Atribute Management";
$l['Intro']['mngradattributeslist.php'] = "Vendor's lista Atribute";
$l['Intro']['mngradattributesnew.php'] = "New Vendor Atribute";
$l['Intro']['mngradattributesedit.php'] = "Editare Vendor Atributele lui";
$l['Intro']['mngradattributessearch.php'] = "Caut? Atribute";
$l['Intro']['mngradattributesdel.php'] = "?terge Vendor Atributele lui";
$l['Intro']['mngradattributesimport.php'] = "Import Vendor Dictionary";


$l['Intro']['acctactive.php'] = "active Records Contabilitate";
$l['Intro']['acctall.php'] = "To?i utilizatorii de contabilitate";
$l['Intro']['acctdate.php'] = "Data Sorteaz? Contabilitate";
$l['Intro']['accthotspot.php'] = "Hotspot de contabilitate";
$l['Intro']['acctipaddress.php'] = "IP Contabilitate";
$l['Intro']['accthotspotcompare.php'] = "Hotspot de comparare";
$l['Intro']['acctmain.php'] = "Pagina de contabilitate";
$l['Intro']['acctnasipaddress.php'] = "SNC IP Contabilitate";
$l['Intro']['acctusername.php'] = "Utilizatorii de contabilitate";
$l['Intro']['acctcustom.php'] = "Custom Accountings";
$l['Intro']['acctcustomquery.php'] = "Custom Interogare de contabilitate";
$l['Intro']['acctmaintenance.php'] = "?inerea eviden?ei contabile";
$l['Intro']['acctmaintenancecleanup.php'] = "Cur??ire st?tut-conexiuni";
$l['Intro']['acctmaintenancedelete.php'] = "Delete ?nregistr?ri contabile";

$l['Intro']['billmain.php'] = "Page Facturare";
$l['Intro']['ratesmain.php'] = "Tarife de facturare Page";
$l['Intro']['billratesdate.php'] = "Tarife preplatite de contabilitate";
$l['Intro']['billratesdel.php'] = "Delete Tarif intrare";
$l['Intro']['billratesedit.php'] = "Editare Evalueaz? Detalii";
$l['Intro']['billrateslist.php'] = "Tarife Tabelul";
$l['Intro']['billratesnew.php'] = "New Tarif intrare";

$l['Intro']['paypalmain.php'] = "PayPal Tranzac?ii Page";
$l['Intro']['billpaypaltransactions.php'] = "PayPal Tranzac?ii Page";

$l['Intro']['billhistoryquery.php'] = "Facturare Istorie";

$l['Intro']['billplans.php'] = "Facturare Planuri Page";
$l['Intro']['billplansdel.php'] = "Delete Planul de intrare";
$l['Intro']['billplansedit.php'] = "Editare Planul Detalii";
$l['Intro']['billplanslist.php'] = "Planurile Tabelul";
$l['Intro']['billplansnew.php'] = "nou plan de intrare";

$l['Intro']['billpos.php'] = "Facturare Point of Sales Page";
$l['Intro']['billposdel.php'] = "?terge utilizator";
$l['Intro']['billposedit.php'] = "Editare utilizator";
$l['Intro']['billposlist.php'] = "Lista utilizatorilor";
$l['Intro']['billposnew.php'] = "utilizator nou";

$l['Intro']['giseditmap.php'] = "Editare MAP Mode";
$l['Intro']['gismain.php'] = "GIS Mapping";
$l['Intro']['gisviewmap.php'] = "Vizualiza?i harta Mode";

$l['Intro']['graphmain.php'] = "Usage Grafuri";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Total de trafic de comparare Utilizare";
$l['Intro']['graphsalltimelogins.php'] = "Total Conect?ri";
$l['Intro']['graphsoveralldownload.php'] = "User Downlads";
$l['Intro']['graphsoveralllogins.php'] = "User Conect?ri";
$l['Intro']['graphsoverallupload.php'] = "Utilizatorul ?ncarc?";

$l['Intro']['rephistory.php'] = "Ac?iunea de Istorie";
$l['Intro']['replastconnect.php'] = "Ultimele 50 de conexiune Tentativele";
$l['Intro']['repstatradius.php'] = "daemons informatiei";
$l['Intro']['repstatserver.php'] = "Server Status ?i informa?ii";
$l['Intro']['reponline.php'] = "Afi?area Online Users";
$l['Intro']['replogssystem.php'] = "System Logfile";
$l['Intro']['replogsradius.php'] = "RADIUS server Logfile";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS Logfile";
$l['Intro']['replogsboot.php'] = "boot Logfile";
$l['Intro']['replogs.php'] = "Rapoarte";

$l['Intro']['rephsall.php'] = "hotspots Listing ";
$l['Intro']['repmain.php'] = "Rapoarte Page";
$l['Intro']['repstatus.php'] = "Status Page";
$l['Intro']['replogs.php'] = "Rapoarte Page";
$l['Intro']['reptopusers.php'] = "Top Utilizatori";
$l['Intro']['repusername.php'] = "Utilizatorii Listing";

$l['Intro']['mngbatch.php'] = "Create lot utilizatori";
$l['Intro']['mngdel.php'] = "?terge utilizator";
$l['Intro']['mngedit.php'] = "Editare utilizator Detalii";
$l['Intro']['mnglistall.php'] = "Utilizatorii Listing";
$l['Intro']['mngmain.php'] = "Utilizatorii ?i hotspots Management";
$l['Intro']['mngnew.php'] = "utilizator nou";
$l['Intro']['mngnewquick.php'] = "Utilizatorul Ad?ugare rapid?";
$l['Intro']['mngsearch.php'] = "Caut? utilizator";

$l['Intro']['mnghsdel.php'] = "?terge hotspots ";
$l['Intro']['mnghsedit.php'] = "Editare hotspots Detalii";
$l['Intro']['mnghslist.php'] = "Lista hotspots ";
$l['Intro']['mnghsnew.php'] = "New Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "?terge-User Group Mapping";
$l['Intro']['mngradusergroup.php'] = "User-Grupului de configurare";
$l['Intro']['mngradusergroupnew.php'] = "utilizator nou-Mapare Group";
$l['Intro']['mngradusergrouplist'] = "User-Group Mapare ?n baza de date";
$l['Intro']['mngradusergrouplistuser'] = "User-Group Mapare ?n baza de date";
$l['Intro']['mngradusergroupedit'] = "Edit User-Group Mapare de utilizator:";

$l['Intro']['mngradippool.php'] = "IP-Biliard de configurare";
$l['Intro']['mngradippoolnew.php'] = "Nou IP-Pool";
$l['Intro']['mngradippoollist.php'] = "Lista de IP-Bazine";
$l['Intro']['mngradippooledit.php'] = "Editare IP-Pool";
$l['Intro']['mngradippooldel.php'] = "?terge IP-Pool";

$l['Intro']['mngradnas.php'] = "SNC Configurare";
$l['Intro']['mngradnasnew.php'] = "Noua SNC Record";
$l['Intro']['mngradnaslist.php'] = "SNC Afi?area ?n baza de date";
$l['Intro']['mngradnasedit.php'] = "Editare SNC Record";
$l['Intro']['mngradnasdel.php'] = "?terge SNC Record";

$l['Intro']['mngradprofiles.php'] = "Profiluri de configurare";
$l['Intro']['mngradprofilesedit.php'] = "Edit Profile";
$l['Intro']['mngradprofilesduplicate.php'] = "Duplicat Profile";
$l['Intro']['mngradprofilesdel.php'] = "Delete Profile";
$l['Intro']['mngradprofileslist.php'] = "Lista Profile";
$l['Intro']['mngradprofilesnew.php'] = "Profil nou";

$l['Intro']['mngradgroups.php'] = "Grupuri de configurare";

$l['Intro']['mngradgroupreplynew.php'] = "New Group R?spunde?i Mapare";
$l['Intro']['mngradgroupreplylist.php'] = "Grupul R?spunde?i Mapare ?n baza de date";
$l['Intro']['mngradgroupreplyedit.php'] = "Edit Group R?spunde?i Mapping pentru Grupa:";
$l['Intro']['mngradgroupreplydel.php'] = "?terge Grupului R?spunde?i Mapare";
$l['Intro']['mngradgroupreplysearch.php'] = "Caut? Grupului R?spunde?i Mapare";

$l['Intro']['mngradgroupchecknew.php'] = "New Group Verifica?i Mapare";
$l['Intro']['mngradgroupchecklist.php'] = "Grupul Verifica?i Mapare ?n baza de date";
$l['Intro']['mngradgroupcheckedit.php'] = "Edit Group Verifica?i Mapping pentru Grupa:";
$l['Intro']['mngradgroupcheckdel.php'] = "?terge Grupului Verifica?i Mapare";
$l['Intro']['mngradgroupchecksearch.php'] = "Caut? Grupului Verifica?i Mapare";

$l['Intro']['configdb.php'] = "Baza de date de configurare";
$l['Intro']['configlang.php'] = "Limba de configurare";
$l['Intro']['configlogging.php'] = "Jurnalizarea Configurare";
$l['Intro']['configinterface.php'] = "interfata web de configurare";
$l['Intro']['configmainttestuser.php'] = "Test de utilizator Conectivitate";
$l['Intro']['configmain.php'] = "Baza de date de configurare";
$l['Intro']['configmaint.php'] = "?ntre?inere";
$l['Intro']['configmaintdisconnectuser.php'] = "Deconectare utilizatorului";
$l['Intro']['configbusiness.php'] = "Detalii de afaceri";
$l['Intro']['configbusinessinfo.php'] = "Business Information";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupcreatebackups.php'] = "Crea?i copii de rezerv?";
$l['Intro']['configbackupmanagebackups.php'] = "Gestionare backup";

$l['Intro']['configoperators.php'] = "Operatorii de configurare";
$l['Intro']['configoperatorsdel.php'] = "?terge Operator";
$l['Intro']['configoperatorsedit.php'] = "Editare Operator Set?ri";
$l['Intro']['configoperatorsnew.php'] = "nou operator";
$l['Intro']['configoperatorslist.php'] = "Operatorii Listing";

$l['Intro']['login.php'] = "Login";

$l['legende']['providebillratetodel'] = "Furniza?i rata tip de intrare pe care dori?i s? ?nl?tura?i";
$l['legende']['detailsofnewrate'] = "Este posibil s? completa?i detaliile de mai jos pentru noul curs";
$l['legende']['filldetailsofnewrate'] = "Completati mai jos detaliile pentru noul curs de intrare";

/* ************************************************ **********************************
* Ajutor Pagini Info
* Fiecare pagin? are un antet care este Intro clasa, atunci c?nd face?i clic pe antet
* Se va revela / div helpPage ascunde un con?inut care este o descriere a unei anumite
* Pagina, practic v? extins instrument-tip.
************************************************** **********************************/


$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "Lista tuturor tranzac?iilor PayPal";
$l['helpPage']['billhistoryquery'] = "Lista tuturor istoricul facturilor pentru un utilizator (i)";

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

$l['helpPage']['msgerrorpermissions'] = "Nu ave?i permisiunile de acces la pagin?. <br/>
V? rug?m s? consulta?i-v? cu administratorul de sistem. <br/> ";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Pentru a elimina un utilizator intrare din baza de date trebuie s? furniza?i numele de utilizator de cont";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
Profiluri <b> Management </ b> - Gestionare Profile pentru Utilizatorii de compune?i un set de atribute ?i R?spunde?i Verifica?i Atribute <br/>
Profiluri poate fi crezut ca de la componen?a Grupului R?spunde?i Verifica?i ?i de grup. <br/>
<h200> <b> Lista Profile </ b> </ h200> - Lista Profile <br/>
<h200> <b> Profil nou </ b> </ h200> - Adauga un profil <br/>
<h200> <b> Editare profil </ b> </ h200> - Edita?i un profil <br/>
<h200> <b> Profil ?tergere </ b> </ h200> - ?terge?i un profil <br/> ";
$l['helpPage']['mngradprofilesedit'] = "
<h200> <b> Editare profil </ b> </ h200> - Edita?i un profil <br/> ";
$l['helpPage']['mngradprofilesdel'] = "
<h200> <b> Profil ?tergere </ b> </ h200> - ?terge?i un profil <br/> ";
$l['helpPage']['mngradprofilesduplicate'] = "
<h200> <b> Duplicat Profil </ b> </ h200> - DUPLICATE un profil al set de atribute la unul nou, cu un alt nume de profil <br/> ";
$l['helpPage']['mngradprofileslist'] = "
<h200> <b> Lista Profile </ b> </ h200> - Lista Profile <br/> ";
$l['helpPage']['mngradprofilesnew'] = "
<h200> <b> Profil nou </ b> </ h200> - Adauga un profil <br/> ";

$l['helpPage']['mngradgroups'] = "
<b> grupuri de Management </ b> - Admin R?spunde?i Group ?i Grupul Verifica?i map?rile (radgroupreply / radgroupcheck tabele). <br/>
<h200> <b> Lista Grupului R?spunde?i / Verifica?i </ b> </ h200> - Lista Grupului R?spunde?i / Check map?rile <br/>
<h200> <b> C?uta?i Grupului R?spunde?i / Verifica?i </ b> </ h200> - Cauta un grup R?spunde?i / Check Mapare (pute?i utiliza metacaractere) <br/>
<h200> <b> New Group R?spunde?i / Verifica?i </ b> </ h200> - ad?uga un grup R?spunde?i / Check Mapare <br/>
<h200> <b> Editare grup de R?spunde?i / Verifica?i </ b> </ h200> - Edita?i un grup R?spunde?i / Check Mapare <br/>
<h200> <b> ?tergere grup de R?spunde?i / Verifica?i </ b> </ h200> - ?terge?i un grup de R?spunde?i / Check Mapare <br/> ";


$l['helpPage']['mngradgroupchecknew'] = "
<h200> <b> New Group Verifica?i </ b> </ h200> - ad?uga un grup Verifica?i Mapare <br/> ";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200> <b> ?tergere grup de Verifica?i </ b> </ h200> - ?terge?i un grup de Verifica?i Mapare <br/> ";

$l['helpPage']['mngradgroupchecklist'] = "
<h200> <b> Lista Grupului Verifica?i </ b> </ h200> - Lista Grupului Verifica?i map?rile <br/> ";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200> <b> Editare grup de Verifica?i </ b> </ h200> - Edita?i un grup Verifica?i Mapare <br/> ";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200> <b> C?uta?i Grupului Verifica?i </ b> </ h200> - Cauta un grup Verifica?i Mapare <br/>
pentru a folosi un wildcard-ar putea s? fie de tip% de caractere, care este cunoscut ?n SQL sau puteti folosi mai multe comune *
pentru comoditate ?i motive daloRADIUS va traduce ?n% ";

$l['helpPage']['mngradgroupreplynew'] = "
<h200> <b> New Group R?spunde?i </ b> </ h200> - ad?uga un grup R?spunde?i Mapare <br/> ";
$l['helpPage']['mngradgroupreplydel'] = "
<h200> <b> Grupului R?spunde?i ?tergere </ b> </ h200> - ?terge?i un grup de R?spunde?i Mapare <br/> ";
$l['helpPage']['mngradgroupreplylist'] = "
<h200> <b> Lista R?spunde?i Group </ b> </ h200> - Lista Grupului R?spunde?i map?rile <br/> ";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200> <b> Editare grup de R?spunde?i </ b> </ h200> - Edita?i un grup R?spunde?i Mapare <br/> ";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200> <b> C?uta?i Grupului R?spunde?i </ b> </ h200> - Cauta un grup R?spunde?i </ Mapare <br/>
pentru a folosi un wildcard-ar putea s? fie de tip% de caractere, care este cunoscut ?n SQL sau puteti folosi mai multe comune *
pentru comoditate ?i motive daloRADIUS va traduce ?n% ";


$l['helpPage']['mngradippool'] = "
<h200> <b> Lista Bazine de IP </ b> </ h200> - Lista Configured IP Bazine ?i atribuite lor Adrese IP <br/>
<h200> <b> New IP Pool </ b> </ h200> - Ad?uga?i un nou Adresa IP la un configurat IP Pool <br/>
<h200> <b> Editare IP Pool </ b> </ h200> - Edita?i o adres? IP pentru o configurat IP Pool <br/>
<h200> <b> Elimina?i IP Pool </ b> </ h200> - Elimina?i o adres? IP de la un configurat IP Pool <br/> ";
$l['helpPage']['mngradippoollist'] = "<h200> <b> Lista Bazine de IP </ b> </ h200> - Lista Configured IP Bazine ?i atribuite lor Adrese IP <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200> <b> nou IP Pool </ b> </ h200> - Ad?uga?i un nou Adresa IP la un configurat IP Pool <br/>";
$l['helpPage']['mngradippooledit'] = "<h200> <b> Editare IP Pool </ b> </ h200> - Edita?i o adres? IP pentru o configurat IP Pool <br/>";
$l['helpPage']['mngradippooldel'] = "<h200> <b> Elimina?i IP Pool </ b> </ h200> - Elimina?i o adres? IP de la un configurat IP Pool <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "Pentru a elimina un ip-ne / gazd? intrare din baza de date trebuie s? furniza?i IP / gazd? de cont";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";



$l['helpPage']['mnghsdel'] = "Pentru a elimina un hotspot din baza de date trebuie s? furniza?i hotspot numele <br/>";
$l['helpPage']['mnghsedit'] = "A?i putea edita detaliile de mai jos pentru hotspot <br/>";
$l['helpPage']['mnghsnew'] = "Este posibil s? completa?i detaliile de mai jos pentru noi hotspot l?ng? baza de date";
$l['helpPage']['mnghslist'] = "Lista de toate hotspots in baza de date. Pute?i utiliza rapid link-uri pentru a modifica sau ?terge un hotspot din baza de date.";

$l['helpPage']['configdb'] = "
Baza de date <b> Set?ri </ b> - Configura?i motorul de baze de date, set?rile conexiunii, ?n cazul ?n care numele de tabele
implicit nu sunt utilizate, precum ?i a parolei de criptare de tip ?n baza de date. <br/>
<h200> <b> Global Set?ri </ b> </ h200> - Baza de date Motor de stocare <br/>
<h200> <b> Tabele Set?ri </ b> </ h200> - Dac? nu utilizeaz? implicit FreeRADIUS schem? ce se pot schimba numele
din tabelul de nume <br/>
<h200> <b> Set?ri avansate </ b> </ h200> - Dac? dori?i s? v? stoca parole pentru utilizatori ?n baza de date nu ?n
text simplu, ci mai degrab? s?-l criptate cumva s-ar putea s? aleag? una dintre MD5 sau crypt <br/> ";
$l['helpPage']['configlang'] = "
<h200> <b> Limba Set?ri </ b> </ h200> - Configura?i limba interfe?ei. <br/> ";
$l['helpPage']['configlogging'] = "
<h200> <b> Jurnalizarea Set?ri </ b> </ h200> - Configura?i reguli ?i facilit??i de logare <br/>
V? rug?m s? v? asigura?i c? numele de fi?ier pe care le specifica?i are permisiuni de scriere de c?tre server <br/> ";
$l['helpPage']['configinterface'] = "
<h200> <b> Interface Set?ri </ b> </ h200> - Configura?i set?rile de aspect ?i interfa?? behvaiour <br/> ";
$l['helpPage']['configmain'] = "
<b> Global Set?ri </ b> <br/>
<h200> <b> Baza de date Set?ri </ b> </ h200> - Configura?i motorul de baze de date, set?rile conexiunii, ?n cazul ?n care numele de tabele
implicit nu sunt utilizate, precum ?i a parolei de criptare de tip ?n baza de date. <br/>
<h200> <b> Limba Set?ri </ b> </ h200> - Configura?i limba interfe?ei. <br/>
<h200> <b> Jurnalizarea Set?ri </ b> </ h200> - Configura?i reguli ?i facilit??i de logare <br/>
<h200> <b> Interface Set?ri </ b> </ h200> - Configura?i set?rile de aspect ?i interfa?? behvaiour <br/>

<b> sub-categorii Configuration </ b>
<h200> <b> Intretinere </ b> </ h200> - ?ntre?inere op?iuni pentru Testarea utilizatorii de conexiuni sau se termin? lor sesiuni <br/>
<h200> <b> Operatorii </ b> </ h200> - Configura?i Operatorii Lista de control al accesului (ACL) <br/> ";
$l['helpPage']['configbusiness'] = "
<b> Business Information </ b> <br/>
<h200> <b> Business Contact </ b> </ h200> - set de informa?ii de contact de afaceri (proprietari, titlul, adresa, telefon, etc) <br/> ";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b> Intretinere </ b> <br/>
<h200> <b> Test Conectivitate utilizatorului </ b> </ h200> - Trimite-o Cerere de acces la RADIUS server pentru a verifica dac? un utilizator de acreditare sunt valide <br/>
<h200> <b> Deconecta?i utilizatorului </ b> </ h200> - Trimite un Pod (pachete de Deconecta?i) sau CoA (Schimbarea Authority) pachetele de la server SNC
Pentru a deconecta un utilizator ?i ?ncetarea lui / ei sesiune, ?ntr-un anumit SNC. <br/> ";
$l['helpPage']['configmainttestuser'] = '
<h200> <b> Test Conectivitate utilizatorului </ b> </ h200> - Trimite-o Cerere de acces la RADIUS server pentru a verifica dac? un utilizator de acreditare sunt valide. <br/>
daloRADIUS utilizeaz? radclient binare de utilitate pentru a efectua, de test ?i returneaz? rezultate de comanda dupa ce se termina. <br/>
daloRADIUS conteaz? pe radclient binar fiind disponibile ?n \ $ PATH variabil? de mediu, ?n cazul ?n care acesta nu este, v? rug?m s? face?i
corec?ii la library/extensions/maintenance_radclient.php fi?ier. <br/> <br/>

V? rug?m s? re?ine?i c? poate dura un timp pentru a testa pentru a termina (p?n? la c?teva secunde [10-20 secunde sau a?a]), din cauza e?ecurilor ?i
radclient va retransmite pachetele.

?n fila Complex, este posibil s? se ajusteze de op?iuni pentru test: <br/>
Timeout - Stai "Timeout" secund? ?nainte de a re?ncerca (poate fi un punct de flotant num?r) <br/>
Re?ncearc? - Dac? timeout, re?ncerca?i trimiterea de pachete de "re?ncearc?" ori. <br/>
Count - Trimite fiecare pachet "Count" ori <br/>
Cereri - Trimite NUM pachetele de la un fi?ier ?n paralel <br/> ';
$l['helpPage']['configmaintdisconnectuser'] = '
<h200> <b> Deconecta?i utilizatorului </ b> </ h200> - Trimite un Pod (pachete de Deconecta?i) sau CoA (Schimbare de Autoritatea) pentru a pachetelor de NAS server pentru a deconecta un utilizator ?i ?ncetarea lui / ei sesiune, ?ntr-o anumit? SNC. <br/>
Pentru ?ncheierea unui utilizator sesiune, este necesar ca SNC sprijini Pod sau CoA tipuri de pachete, v? rug?m s? consulta?i-v? SNC v?nz?torului sau
documenta?ia pentru acest lucru. ?n plus, este necesar s? se cunoasc? SNC porturile pentru Pod sau CoA pachete, ?ntruc?t, mai nou NASs utiliza portul 3799
?n timp ce altele sunt configurate pentru a primi pachete de pe portul 1700.

daloRADIUS utilizeaz? radclient binare de utilitate pentru a efectua, de test ?i returneaz? rezultate de comanda dupa ce se termina. <br/>
daloRADIUS conteaz? pe radclient binar fiind disponibile ?n \ $ PATH variabil? de mediu, ?n cazul ?n care acesta nu este, v? rug?m s? face?i
corec?ii la library/extensions/maintenance_radclient.php fi?ier. <br/> <br/>

V? rug?m s? re?ine?i c? poate dura un timp pentru a testa pentru a termina (p?n? la c?teva secunde [10-20 secunde sau a?a]), din cauza e?ecurilor ?i
radclient va retransmite pachetele.

?n fila Complex, este posibil s? se ajusteze de op?iuni pentru test: <br/>
Timeout - Stai "Timeout" secund? ?nainte de a re?ncerca (poate fi un punct de flotant num?r) <br/>
Re?ncearc? - Dac? timeout, re?ncerca?i trimiterea de pachete de "re?ncearc?" ori. <br/>
Count - Trimite fiecare pachet "Count" ori <br/>
Cereri - Trimite NUM "pachetele de la un fi?ier ?n paralel <br/>';
$l['helpPage']['configoperatorsdel'] = "Pentru a elimina un operator de la baza de date trebuie s? furniza?i numele de utilizator.";
$l['helpPage']['configoperatorsedit'] = "Editare operatorul utilizator detaliile de mai jos";
$l['helpPage']['configoperatorsnew'] = "Este posibil s? completa?i detaliile de mai jos pentru un nou operator de utilizator plus la baza de date";
$l['helpPage']['configoperatorslist'] = "Afi?area tuturor operatorilor ?n baza de date";
$l['helpPage']['configoperators'] = "Operatorii de configurare";
$l['helpPage']['configbackup'] = "Realiza?i backup";
$l['helpPage']['configbackupcreatebackups'] = "Crea?i copii de rezerv?";
$l['helpPage']['configbackupmanagebackups'] = "Gestionare backup";


$l['helpPage']['graphmain'] = '
<b> grafice </ b> <br/>
<h200> <b> general Conect?ri / Hits </ b> </ h200> - parcele de un grafic diagram? de utilizare pentru un anume utilizator pe o anumit? perioad? de timp.
Valoarea Conect?ri (sau "hit-uri" la SNC) sunt afi?ate ?ntr-un grafic, precum ?i ?nso?ite de un tabel. <br/>
<h200> <b> ansamblu Download Statistici </ b> </ h200> - parcele de un grafic diagram? de utilizare pentru un anume utilizator pe o anumit? perioad? de timp.
Cantitatea de date desc?rcate de c?tre client este de valoare, care este calculat. Graful este ?nso?it de un tabel <br/>
<h200> <b> general ?nc?rcare Statistici </ b> </ h200> - parcele de un grafic diagram? de utilizare pentru un anume utilizator pe o anumit? perioad? de timp.
Cantitatea de date ?nc?rca?i de client este de valoare, care este calculat. Graful este ?nso?it de un tabel <br/>
<br/>
<h200> <b> Alltime Conect?ri / Hits </ b> </ h200> - parcele de un grafic diagram? de Conect?ri la server pentru o anumit? perioad? de timp. <br/>
<h200> <b> Alltime Trafic de comparare </ b> </ h200> - parcele de un grafic diagram? de descarcat si Uploaded statisticse.';
$l['helpPage']['graphsalltimelogins'] = "O-Time Toate statisticile de Conect?ri la server bazat pe o distribu?ie pe o perioad? de timp";
$l['helpPage']['graphsalltimetrafficcompare'] = "O-Time Toate statisticile de trafic prin intermediul server bazat pe o distribu?ie pe o perioad? de timp.";
$l['helpPage']['graphsoveralldownload'] = "parcele de un grafic plan de Downloaded octe?i la server";
$l['helpPage']['graphsoverallupload'] = "parcele de un grafic diagram? de Uploaded octe?i la server";
$l['helpPage']['graphsoveralllogins'] = "parcele de un grafic plan de ?ncerc?ri de conectare la server";



$l['helpPage']['rephistory'] = "Liste de activitate face pe toate posturile de management ?i ofer? informa?ii cu privire la <br/>
Data cre?rii, crearea de c?tre precum ?i Actualizat Data ?i Update Prin istoria domeniile ";
$l['helpPage']['replastconnect'] = "Listeaz? toate tentativele de conectare la server RADIUS, ambele de succes ?i e?ec login";
$l['helpPage']['replogsboot'] = "Monitor Sistem de operare Boot jurnal - echivalent cu rularea de comanda dmesg.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS lui Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS lui Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Sistem de operare Logfile.";
$l['helpPage']['replogs'] = "
<b> Rapoarte </ b> <br/>
<h200> <b> daloRADIUS Jurnal </ b> </ h200> - Monitorul daloRADIUS lui Logfile. <br/>
<h200> <b> RADIUS Jurnal </ b> </ h200> - Monitorul FreeRADIUS lui Logfile - echivalent cu / var / log / freeradius / radius.log sau / usr / local / var / log / raz? / radius.log.
Alte posibile loca?ii pentru LogFile poate avea loc, dac? este cazul, v? rug?m s? ajusteze de configurare corespunz?tor. <br/>
<h200> <b> Sistemul de Log </ b> </ h200> - Monitor Sistem de operare Logfile - echivalent cu / var / log / syslog sau / var / log / mesaj pe cele mai multe platforme.
Alte posibile loca?ii pentru LogFile poate avea loc, dac? este cazul, v? rug?m s? ajusteze de configurare corespunz?tor. <br/>
<h200> <b> Jurnal de Boot </ b> </ h200> - Monitor Sistem de operare Boot jurnal - echivalent cu rularea de comanda dmesg. ";
$l['helpPage']['repmain'] = "
<b> General Rapoarte </ b> <br/>
<h200> <b> Online Users </ b> </ h200> - Ofer? o list? a tuturor utilizatorilor, care sunt
dovedit a fi on-line prin intermediul contabile tabel in baza de date. A verifica care este realizat este pentru utilizatori
nu se ?ncheie cu timpul (AcctStopTime) set. Este important s? observa?i c? aceste utilizatori pot fi, de asemenea, sesiuni de st?tut
care se ?nt?mpl? atunci c?nd NASs dintr-un motiv pentru a nu trimite contabilului-stop de pachete. <br/>
<h200> <b> Ultima conexiune ?ncerc?rile de </ b> </ h200> - Ofer? o list? cu toate Acces-Accept si acces-Respingere (a acceptat ?i nu a reu?it) autentific?rile
pentru utilizatori. <br/> Acestea sunt extrase din baza de date a postauth tabel care este necesar pentru a fi definite
?n FreeRADIUS de fi?ier de configurare pentru a jurnalului de fapt, acestea. <br/>
<h200> <b> Top utilizatorului </ b> </ h200> - Ofer? o list? a ?nceput N utilizatori pentru a consumului de l??ime de band? ?i timp sesiune folosit <br/> <br/>
<b> sub-categorii Rapoarte </ b> <br/>
<h200> <b> Rapoarte </ b> </ h200> - ofer? acces la daloRADIUS LogFile, FreeRADIUSs LogFile, sistem de LogFile ?i Boot LogFile <br/>
<h200> <b> Stare </ b> </ h200> - ofer? informa?ii cu privire la statutul ?i server RADIUS Componente statut ";
$l['helpPage']['repstatradius'] = "ofer? informa?ii generale cu privire la server sine: utilizarea procesorului, a proceselor, uptime, memorie, etc ";
$l['helpPage']['repstatserver'] = "ofer? informa?ii generale cu privire la FreeRADIUS daemon ?i baze de date MySQL server";
$l['helpPage']['repstatus'] = "<b> Stare </ b> <br/>
<h200> <b> Server Stare </ b> </ h200> - Ofer? informa?ii generale despre server sine: utilizarea procesorului, a proceselor, uptime, memorie, etc <br/>
<h200> <b> RADIUS Stare </ b> </ h200> - Ofer? informa?ii generale despre FreeRADIUS daemon ?i baze de date MySQL server ";
$l['helpPage']['reptopusers'] = "Inregistreaza top pentru utilizatori, cei care sunt enumerate mai jos au c??tigat cel mai mare consum de sesiune
timp sau de utilizare a l??imii de band?. Afi?area utilizatorilor din categoria: ";
$l['helpPage']['repusername'] = "Recorduri g?sit de utilizator:";
$l['helpPage']['reponline'] = "
Urm?torul tabel afi?eaz? utilizatorii care ?n prezent sunt conectate la
sistem. Este foarte posibil ca acolo sunt surmenat de conexiuni,
sensul pe care utilizatorii le-am deconectat de la SNC, dar nu a trimite sau nu a fost
posibilitatea de a trimite o STOP contabile de pachete de RADIUS server. ";


$l['helpPage']['mnglistall'] = "Lista utilizatorilor din baza de date";
$l['helpPage']['mngsearch'] = "C?utare de utilizator:";
$l['helpPage']['mngnew'] = "Este posibil s? completa?i detaliile de mai jos pentru utilizator nou l?ng? baza de date <br/>";
$l['helpPage']['mngedit'] = "Editare utilizator detalii de mai jos. <br/>";
$l['helpPage']['mngdel'] = "Pentru a elimina un utilizator intrare din baza de date trebuie s? furniza?i numele de utilizator al contului <br/>";
$l['helpPage']['mngbatch'] = "Este posibil s? completa?i detaliile de mai jos pentru utilizator nou l?ng? baza de date. <br/>
Re?ine?i c? aceste set?ri se aplic? pentru to?i utilizatorii care ?l crea?i. <br/> ";
$l['helpPage']['mngnewquick'] = "Urm?toarele utilizator / carte este de tip prepaid. <br/>
Durata de timp specificat? ?n Timpul de credit va fi utilizat ca Session-Timeout ?i Max-Toate-Session
raz? atribute ";

// Contabilitate pct.
$l['helpPage']['acctactive'] = "
Ofer? informa?ii c? s-ar dovedi util? pentru urm?rirea active sau a expirat utilizatori ?n baza de date
din punct de vedere al utilizatorilor, care au un atribut expir?rii sau un Max-Toate-Sesiunea atribut.
<br/> ";
$l['helpPage']['acctall'] = "
Ofer? complet de contabilitate pentru toate sesiunile de informa?ii ?n baza de date.
<br/> ";
$l['helpPage']['acctdate'] = "
Ofer? completa informa?iile contabile pentru toate sesiunile ?ntre 2 dat datele pentru un anumit utilizator.
<br/> ";
$l['helpPage']['acctipaddress'] = "
Ofer? completa informa?iile contabile pentru toate sesiunile, care au provenit cu o anumit? adres? IP.
<br/> ";
$l['helpPage']['acctmain'] = "
<b> General Accounting </ b> <br/>
<h200> <b> utilizatorului contabilitate </ b> </ h200> --
Ofer? complet de contabilitate pentru toate sesiunile de informa?ii ?n baza de date pentru un anumit utilizator.
<br/>
<h200> <b> contabilitate </ b> </ h200> --
Ofer? completa informa?iile contabile pentru toate sesiunile, care au provenit cu o anumit? adres? IP.
<br/>
<h200> <b> SNC Contabilitate </ b> </ h200> --
Ofer? completa informa?iile contabile pentru toate sesiunile specifice SNC c? adresa IP a manipulat.
<br/>
<h200> <b> Data de contabilitate </ b> </ h200> --
Ofer? completa informa?iile contabile pentru toate sesiunile ?ntre 2 dat datele pentru un anumit utilizator.
<br/>
<h200> <b> Toate ?nregistr?rile contabile </ b> </ h200> --
Ofer? complet de contabilitate pentru toate sesiunile de informa?ii ?n baza de date.
<br/>
<h200> <b> active Records contabilitate </ b> </ h200> --
Ofer? informa?ii c? s-ar dovedi util? pentru urm?rirea active sau a expirat utilizatori ?n baza de date
din punct de vedere al utilizatorilor, care au un atribut expir?rii sau un Max-Toate-Sesiunea atribut.
<br/>

<br/>
<b> sub-categorii de contabilitate </ b> <br/>
<h200> <b> Custom </ b> </ h200> --
Ofer? cele mai flexibile personalizat interogare pentru a se executa pe baza de date.
<br/>
<h200> <b> hotspots </ b> </ h200> --
Ofer? informa?ii cu privire la diferite gestionate hotspots, compara?ie, ?i alte informa?ii utile.
<br/> ";
$l['helpPage']['acctnasipaddress'] = "
Ofer? completa informa?iile contabile pentru toate sesiunile specifice SNC c? adresa IP a manipulat.
<br/> ";
$l['helpPage']['acctusername'] = "
Ofer? complet de contabilitate pentru toate sesiunile de informa?ii ?n baza de date pentru un anumit utilizator.
<br/> ";
// Contabilitate hotspot pct.
$l['helpPage']['accthotspotaccounting'] = "
Ofer? completa informa?iile contabile pentru toate sesiunile, care au provenit din acest Hotspot.
Aceast? list? este calculat? de c?tre cota numai acele ?nregistr?ri ?n radacct tabel cu CalledStationId
domeniu, care se potrivesc cu un Hotspot de adresa MAC intrarea ?n Hotspot de gestionare a bazelor de date.
<br/> ";
$l['helpPage']['accthotspotcompare'] = "
Ofer? informa?ii contabile de baz? pentru compara?ie ?ntre toate activ? hotspots g?sit ?n baza de date.
Informa?iilor contabile furnizate: <br/> <br/>
Hotspot Nume - numele de Hotspot <br/>
Utilizatori unici - Utilizatorii care au logined numai prin acest hotspot <br/>
Total Hits - totalul autentific?rile c? s-au efectuat la acest hotspot (unic ?i nu unic) <br/>
Timpul mediu - Timpul mediu petrecut un utilizator ?n acest hotspot <br/>
Timp total - The accumolated petrecut timp de to?i utilizatorii din acest hotspot <br/>

<br/>
Ofer? un grafic de complot diferite compara?iile f?cute <br/>
Grafice: <br/> <br/>
Distribuirea de utilizatori unici pe hotspot <br/>
Distribu?ia pe Hits hotspot <br/>
Distribu?ie de timp de utilizare pe hotspot <br/>
<br/> ";
$l['helpPage']['accthotspot'] = "
<h200> <b> Hotspot de contabilitate </ b> </ h200> --
Ofer? completa informa?iile contabile pentru toate sesiunile, care au provenit din acest Hotspot.
<br/>
<h200> <b> Hotspot de comparare </ b> </ h200> --
Ofer? informa?ii contabile de baz? pentru compara?ie ?ntre toate activ? hotspots g?sit ?n baza de date.
Ofer? un grafic de complot diferite compara?iile f?cute.
<br/> ";

//Contabilitate personalizat interog?ri pct.
$l['helpPage']['acctcustom'] = "
<h200> <b> Custom </ b> </ h200> --
Ofer? cele mai flexibile personalizat interogare pentru a se executa pe baza de date. <br/>
Este posibil s? se adapteze la interogare este max prin modificarea set?rilor ?n bara lateral? st?nga. <br/>
<br/>
<b> ?ntre datele </ b> - Seta?i care ?ncepe ?i se termin? data.
<br/>
?n cazul ?n care <b> </ b> - Seta?i domeniul in baza de date pe care dori?i, pentru a se potrivi (la fel ca ?i un element-cheie), ales ?n cazul ?n care valoarea
care s? se potriveasc? pentru a ar trebui s? fie egal (=) sau acesta con?ine o parte din valoarea de c?utare pentru tine (cum ar fi o regex). Dac?
alege s? utilizeze Contine operatorul nu trebuie s? ad?uga?i orice metacaractere din comuna forma "*", ci mai degrab?
valoarea pe care vor fi c?utate ?n mod automat ?n acest formular: * valoare * (sau ?n stil mysql:% valoarea%).
<br/>
Interogare de Contabilitate <b> Domenii </ b> - Pute?i alege domenii care v-ar pl?cea s? prezinte, ?n rezultate
List?.
<br/>
Prin Ordinul <b> </ b> - Alege domeniu de care v-ar pl?cea s? ordinea rezultatelor ?i este de tip (Crescator
Sau descresc?toare)
<br/> ";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200> <b> Cur??ire surmenat-sesiuni de </ b> </ h200> --
St?tut-sesions mai multe ori exist? pentru c? SNC nu a fost ?n m?sur? s? ofere un contabil STOP record pentru <br/>
sesiune de utilizator, care rezult? ?ntr-o sesiune surmenat deschis ?n eviden?ele contabile care simuleaz? un fals autentifica?i ?n utilizator
Record (fals pozitive).
<br/>
<h200> <b> ?terge?i ?nregistr?rile contabile </ b> </ h200> --
?tergerea de ?nregistr?ri contabile ?n baza de date. Ea nu poate fi ?n?elept pentru a efectua acest lucru sau pentru a permite altor utilizatori
cu excep?ia unui supravegheat de acces de administrator la aceast? pagin?.
<br/> ";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
Editare Harta Mode - ?n acest mod, pute?i ad?uga sau ?terge fie hotspots f?c?nd clic pur ?i simplu
pe-o loca?ie de pe hart? sau printr-un clic pe un hotspot (respectiv). <br/> <br/>
Ad?ugarea <b> Hotspot </ b> - Pur ?i simplu face?i clic pe o loca?ie de pe hart?, vi se va cere s? furnizeze
Hotspot de numele ?i adresa de e MAC. Acestea sunt 2 crucial detalii mai t?rziu, utilizat pentru a identifica acest hotspot
?n tabelul de contabilitate. Ofer? ?ntotdeauna corect adresa MAC!
<br/> <br/>
?tergerea <b> Hotspot </ b> - Pur ?i simplu face?i clic pe un hotspot de pictograma ?i v? confirma?i ?tergerea de acesta de la
Baza de date.
<br/> ";
$l['helpPage']['gisviewmap'] = "
Vizualiza?i harta Mode - ?n acest mod, ve?i putea naviga pe hotspots ?n care sunt prev?zute
?n icoane peste h?r?i furnizate de GoogleMaps serviciu. <br/> <br/>

F?c?nd clic pe un <b> Hotspot </ b>-v? va oferi mai multe detalii ?n detaliu cu privire la hotspot.
Cum ar fi informa?ii de contact pentru a hotspot, statistici ?i detalii.
<br/> ";
$l['helpPage']['gismain'] = '
<b> Informa?ii generale </ b>
GIS ofer? Mapare vizual map?rile de hotspot loca?ie din ?ntreaga lume, harta, folosind Google Maps API. <br/>
?n pagina de management ce au posibilitatea de a ad?uga noi hotspot, referitoare la baza de date, ?n cazul ?n care exist?, de asemenea, un domeniu
numita Geolocation, aceasta este valoarea numeric? c? Google Maps API folose?te pentru a PIN-punct exacta
Hotspot de faptul c? loca?ia pe hart?. <br/> <br/>

<h200> <b> 2 Moduri de func?ionare sunt prev?zute: </ b> </ h200>
Unul este <b> Vizualiza?i harta </ b> modul care permite "surf", prin harta lumii
?i a vedea loca?iile curente de hotspots in baza de date ?i un altul - <b> Edita?i MAP </ b> -, care este modul de
pe care o pot folosi pentru a crea hotspot al vizual de la st?nga, pur ?i simplu clic pe hart? sau de a scoate
existente hotspot intr?rile de st?nga-clic pe existente hotspot steaguri. <br/> <br/>

Un alt aspect important este c? fiecare computer din re?ea necesit? un cod unic de ?nregistrare pe care le
poate ob?ine de la Google Maps API pagin? prin furnizarea completa la adresa de web g?zduit de director
daloRADIUS aplicarea pe server. Odat? ce a?i ob?inut codul de la Google, pur ?i simplu s?-l insera?i ?n
De ?nregistrare caset? ?i face?i clic pe "Registrul de cod" buton pentru a scrie o.
Apoi, s-ar putea s? fie capabil de a utiliza Google Maps servicii. <br/> <br/> ';

/* ************************************************ ********************************** */



$l['messages']['noCheckAttributesForUser'] = "Acest utilizator nu are nici un control atribute asociate cu acesta";
$l['messages']['noReplyAttributesForUser'] = "Acest utilizator nu are nici un r?spuns atribute asociate cu acesta";

$l['messages']['noCheckAttributesForGroup'] = "Acest grup nu are nici un control atribute asociate cu acesta";
$l['messages']['noReplyAttributesForGroup'] = "Acest grup nu are nici un r?spuns atribute asociate cu acesta";

$l['messages']['nogroupdefinedforuser'] = "Acest utilizator nu are nicio grupuri asociate cu acesta";
$l['messages']['wouldyouliketocreategroup'] = "Dori?i s? crea?i o?";


$l['messages']['missingratetype'] = "Eroare: lipsesc rata de tip pentru a ?terge";
$l['messages']['missingtype'] = "Eroare: lips? de tip";
$l['messages']['missingcardbank'] = "Eroare: lipsesc cardbank";
$l['messages']['missingrate'] = "Eroare: lipsesc rata";
$l['messages']['succes'] = "de succes";
$l['messages']['gisedit1'] = "Bine ai venit, pe care ?n mod Editare";
$l['messages']['gisedit2'] = "?terge curent marker de la harta si baza de date?";
$l['messages']['gisedit3'] = "V? rug?m s? introduce?i numele de Hotspot";
$l['messages']['gisedit4'] = "Adauga curent marker la baza de date?";
$l['messages']['gisedit5'] = "V? rug?m s? introduce?i numele de Hotspot";
$l['messages']['gisedit6'] = "V? rug?m s? introduce?i adresa de MAC a Hotspot";

$l['messages']['gismain1'] = "au fost actualizate cu succes GoogleMaps API codul de inregistrare";
$l['messages']['gismain2'] = "Eroare: nu sa putut deschide fi?ierul de scris:";
$l['messages']['gismain3'] = "Check fi?ier permissions. Fi?ierul ar trebui s? fie de scriere de c?tre server de utilizator / grup";
$l['messages']['gisviewwelcome'] = "Bine ati venit la Enginx Visual Maps";

$l['messages']['Loginerror'] = '<br/> <br/> una din urm?toarele: <br/>
1. rea de utilizator / parola <br/>
2. un administrator este deja conectat-in (doar un exemplu, este permis) <br/>
3. se pare c? exist? mai mult de un "administrator" utilizator ?n baza de date <br/> ';

$l['buttons']['savesettings'] = "Save Settings";
$l['buttons']['apply'] = "Aplica";

$l['menu']['Home'] = "Home";
$l['menu']['Managment'] = "Management";
$l['menu']['Reports'] = "Reports";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Billing";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Graphs";
$l['menu']['Config'] = "Config";
$l['menu']['Help'] = "Help";

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

