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
 * Description:    German language file
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *                 Mike
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/de.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS Management, Reporting, Accounting and Billing by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . <<<EOF
 <span class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Follow @filippolauria on GitHub">
  <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>
</span>  and <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.
EOF;

$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Pool-Name";
$l['all']['CalledStationId'] = "CalledStationId";
$l['all']['CallingStationID'] = "CallingStationID";
$l['all']['ExpiryTime'] = "Ablaufzeit";
$l['all']['PoolKey'] = "Pool-Key";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Wörterbuch";
$l['all']['VendorID'] = "Hersteller-ID";
$l['all']['VendorName'] = "Hersteller-Name";
$l['all']['VendorAttribute'] = "Hersteller-Attribut";
$l['all']['RecommendedOP'] = "Empfohlener Operator";
$l['all']['RecommendedTable'] = "Empfohlene Tabelle";
$l['all']['RecommendedTooltip'] = "Empfohlener Tooltip";
$l['all']['RecommendedHelper'] = "Empfohlene Hilfsfunktion";
/********************************************************************************/

$l['all']['CSVData'] = "CSV-formatierte Daten";

$l['all']['CPU'] = "CPU";

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "RADIUS Wörterbuch-Pfad";


$l['all']['DashboardSecretKey'] = "Dashboard Secret-Key";
$l['all']['DashboardDebug'] = "Debug";
$l['all']['DashboardDelaySoft'] = "Zeit in Minuten ein 'weiches' Verzögerungslimit in Betracht zu ziehen";
$l['all']['DashboardDelayHard'] = "Zeit in Minuten ein 'hartes' Verzögerungslimit in Betracht zu ziehen";



$l['all']['SendWelcomeNotification'] = "Sende Willkommens-Benachrichtigung";
$l['all']['SMTPServerAddress'] = "SMTP Server-Adresse";
$l['all']['SMTPServerPort'] = "SMTP Server-Port";
$l['all']['SMTPServerFromEmail'] = "Absender Email-Adresse (from)";

$l['all']['customAttributes'] = "Benutzerdefinierte Attribute";

$l['all']['UserType'] = "Benutzertyp";

$l['all']['BatchName'] = "Batch-Name";
$l['all']['BatchStatus'] = "Batch-Status";

$l['all']['Users'] = "Benutzer";

$l['all']['Compare'] = "Vergleiche";
$l['all']['Never'] = "Nie";


$l['all']['Section'] = "Abschnitt";
$l['all']['Item'] = "Artikel";

$l['all']['Megabytes'] = "Megabytes";
$l['all']['Gigabytes'] = "Gigabytes";

$l['all']['Daily'] = "Täglich";
$l['all']['Weekly'] = "Wöchentlich";
$l['all']['Monthly'] = "Monatlich";
$l['all']['Yearly'] = "Jährlich";

$l['all']['Month'] = "Monat";

$l['all']['RemoveRadacctRecords'] = "Accounting-Datensätze löschen";

$l['all']['CleanupSessions'] = "Bereinige Sessions älter als";
$l['all']['DeleteSessions'] = "Lösche Sessions älter als";

$l['all']['StartingDate'] = "Startdatum";
$l['all']['EndingDate'] = "Enddatum";

$l['all']['Realm'] = "Realm";
$l['all']['RealmName'] = "Realm-Name";
$l['all']['RealmSecret'] = "Realm-Secret";
$l['all']['AuthHost'] = "Auth-Host";
$l['all']['AcctHost'] = "Acct-Host";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "Hinweise";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy-Name";
$l['all']['ProxySecret'] = "Proxy-Secret";
$l['all']['DeadTime'] = "Dead-Time";
$l['all']['RetryDelay'] = "Retry-Delay";
$l['all']['RetryCount'] = "Retry-Count";
$l['all']['DefaultFallback'] = "Default-Fallback";


$l['all']['Firmware'] = "Firmware";
$l['all']['NASMAC'] = "NAS-MAC";

$l['all']['WanIface'] = "WAN-Interface";
$l['all']['WanMAC'] = "WAN-MAC";
$l['all']['WanIP'] = "WAN-IP";
$l['all']['WanGateway'] = "WAN-Gateway";

$l['all']['LanIface'] = "LAN-Interface";
$l['all']['LanMAC'] = "LAN-MAC";
$l['all']['LanIP'] = "LAN-IP";

$l['all']['WifiIface'] = "WLAN-Interface";
$l['all']['WifiMAC'] = "WLAN-MAC";
$l['all']['WifiIP'] = "WLAN-IP";

$l['all']['WifiSSID'] = "WLAN-SSID";
$l['all']['WifiKey'] = "WLAN-Key";
$l['all']['WifiChannel'] = "WLAN-Kanal";

$l['all']['CheckinTime'] = "Zuletzt eingeloggt";

$l['all']['FramedIPAddress'] = "Framed-IP-Address";
$l['all']['SimultaneousUse'] = "Simultaneous-Use";
$l['all']['HgID'] = "HG ID";
$l['all']['Hg'] = "HG ";
$l['all']['HgIPHost'] = "HG IP/Host";
$l['all']['HgGroupName'] = "HG GroupName";
$l['all']['HgPortId'] = "HG Port-ID";
$l['all']['NasID'] = "NAS-ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS-IP/Host";
$l['all']['NasShortname'] = "NAS-Kurzname";
$l['all']['NasType'] = "NAS-Type";
$l['all']['NasPorts'] = "NAS-Ports";
$l['all']['NasSecret'] = "NAS-Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual-Server";
$l['all']['NasCommunity'] = "NAS-Community";
$l['all']['NasDescription'] = "NAS-Beschreibung";
$l['all']['PacketType'] = "Packet-Type";
$l['all']['HotSpot'] = "Hotspot";
$l['all']['HotSpots'] = "Hotspots";
$l['all']['HotSpotName'] = "Hotspot-Name";
$l['all']['Name'] = "Name";
$l['all']['Username'] = "Benutzername";
$l['all']['Password'] = "Passwort";
$l['all']['PasswordType'] = "Passworttyp";
$l['all']['IPAddress'] = "IP-Adresse";
$l['all']['Profile'] = "Profil";
$l['all']['Group'] = "Gruppe";
$l['all']['Groupname'] = "Gruppenname";
$l['all']['ProfilePriority'] = "Profil-Priorität";
$l['all']['GroupPriority'] = "Gruppen-Priorität";
$l['all']['CurrentGroupname'] = "Aktueller Gruppenname";
$l['all']['NewGroupname'] = "Neuer Gruppenname";
$l['all']['Priority'] = "Priorität";
$l['all']['Attribute'] = "Attribut";
$l['all']['Operator'] = "Operator";
$l['all']['Value'] = "Wert";
$l['all']['NewValue'] = "Neuer Wert";
$l['all']['MaxTimeExpiration'] = "Maximalzeit / Ablaufzeit";
$l['all']['UsedTime'] = "Verwendete Zeit";
$l['all']['Status'] = "Status";
$l['all']['Usage'] = "Nutzung";
$l['all']['StartTime'] = "Startzeit";
$l['all']['StopTime'] = "Stoppzeit";
$l['all']['TotalTime'] = "Gesamtzeit";
$l['all']['TotalTraffic'] = "Gesamt-Traffic";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Upload";
$l['all']['Download'] = "Download";
$l['all']['Rollback'] = "Rollback";
$l['all']['Termination'] = "Beendigung";
$l['all']['NASIPAddress'] = "NAS IP-Adresse";
$l['all']['NASShortName'] = "NAS Kurzname";
$l['all']['Action'] = "Aktion";
$l['all']['UniqueUsers'] = "Einzelne Nutzer";
$l['all']['TotalHits'] = "Gesamtanzahl der Anfragen";
$l['all']['AverageTime'] = "Durchschnittliche Zeit";
$l['all']['Records'] = "Datensätze";
$l['all']['Summary'] = "Zusammenfassung";
$l['all']['Statistics'] = "Statistiken";
$l['all']['Credit'] = "Guthaben";
$l['all']['Used'] = "Verbraucht";
$l['all']['LeftTime'] = "Verbleibende Zeit";
$l['all']['LeftPercent'] = "Verbleibende Zeit (%)";
$l['all']['TotalSessions'] = "Gesamte Sessions";
$l['all']['LastLoginTime'] = "Letzter Login";
$l['all']['TotalSessionTime'] = "Gesamt-Sessiondauer";
$l['all']['RateName'] = "Tarifsatz-Name";
$l['all']['RateType'] = "Tarifsatz-Art";
$l['all']['RateCost'] = "Tarifsatz-Kosten";
$l['all']['Billed'] = "Abrechnungsrelevant";
$l['all']['TotalUsers'] = "Gesamtbenutzer";
$l['all']['ActiveUsers'] = "Aktive Benutzer";
$l['all']['TotalBilled'] = "Gesamt abgerechnet";
$l['all']['TotalPayed'] = "Gesamt bezahlt";
$l['all']['Balance'] = "Kontostand";
$l['all']['CardBank'] = "Kartenbank";
$l['all']['Type'] = "Typ";
$l['all']['MACAddress'] = "MAC-Adresse";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN-Code";
$l['all']['CreationDate'] = "Erstellungsdatum";
$l['all']['CreationBy'] = "Erstellt von";
$l['all']['UpdateDate'] = "Änderungsdatum";
$l['all']['UpdateBy'] = "Geändert von";

$l['all']['Discount'] = "Rabatt";
$l['all']['BillAmount'] = "Abrechnungsbetrag";
$l['all']['BillAction'] = "Abrechnungsaktion";
$l['all']['BillPerformer'] = "Abrechnender";
$l['all']['BillReason'] = "Abrechnungsgrund";
$l['all']['Lead'] = "Lead";
$l['all']['Coupon'] = "Gutschein";
$l['all']['OrderTaker'] = "Auftragnehmer";
$l['all']['BillStatus'] = "Rechnungsstatus";
$l['all']['LastBill'] = "Letzte Rechnung";
$l['all']['NextBill'] = "Nächste Rechnung";
$l['all']['BillDue'] = "Fälligkeit der Rechnung";
$l['all']['NextInvoiceDue'] = "Nächste Rechnungsfälligkeit";
$l['all']['PostalInvoice'] = "Postrechnung";
$l['all']['FaxInvoice'] = "Faxrechnung";
$l['all']['EmailInvoice'] = "E-Mail-Rechnung";

$l['all']['ClientName'] = "Kundenname";
$l['all']['Date'] = "Datum";

$l['all']['edit'] = "Bearbeiten";
$l['all']['del'] = "Löschen";
$l['all']['groupslist'] = "Gruppenliste";
$l['all']['TestUser'] = "Benutzer testen";
$l['all']['Accounting'] = "Accounting";
$l['all']['RADIUSReply'] = "RADIUS-Reply";

$l['all']['Disconnect'] = "Trennen";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Wiederholungsversuche";
$l['all']['Count'] = "Anzahl";
$l['all']['Requests'] = "Anfragen";

$l['all']['DatabaseHostname'] = "Datenbank-Hostname";
$l['all']['DatabasePort'] = "Datenbank-Port";
$l['all']['DatabaseUser'] = "Datenbank-Benutzer";
$l['all']['DatabasePass'] = "Datenbank-Passwort";
$l['all']['DatabaseName'] = "Datenbank-Name";

$l['all']['PrimaryLanguage'] = "Primäre Sprache";

$l['all']['PagesLogging'] = "Seitenaufrufe loggen";
$l['all']['QueriesLogging'] = "Abfragen loggen (z. B. Berichte, Diagramme, etc.)";
$l['all']['ActionsLogging'] = "Aktionen loggen (z. B. Formularübermittlungen)";
$l['all']['FilenameLogging'] = "Log-Datei (absoluter Pfad)";
$l['all']['LoggingDebugOnPages'] = "Debug-Informationen loggen (auf Seiten)";
$l['all']['LoggingDebugInfo'] = "Debug-Informationen loggen";

$l['all']['PasswordHidden'] = "Passwörter verstecken (es werden Asterisks angezeigt)";
$l['all']['TablesListing'] = "Zeilen/Datensätze pro Tabellenübersichtsseite";
$l['all']['TablesListingNum'] = "Nummerierung der Tabellenübersicht aktivieren";
$l['all']['AjaxAutoComplete'] = "Ajax-Autovervollständigung aktivieren";

$l['all']['RadiusServer'] = "Radius-Server";
$l['all']['RadiusPort'] = "Radius-Port";

$l['all']['UsernamePrefix'] = "Benutzernamen-Präfix";

$l['all']['batchName'] = "Batch-Id/Name";
$l['all']['batchDescription'] = "Batch-Beschreibung";

$l['all']['NumberInstances'] = "Anzahl zu erstellender Instanzen";
$l['all']['UsernameLength'] = "Länge des Benutzernamens";
$l['all']['PasswordLength'] = "Länge des Passworts";

$l['all']['Expiration'] = "Expiration";
$l['all']['MaxAllSession'] = "Max-All-Session";
$l['all']['SessionTimeout'] = "Session Timeout";
$l['all']['IdleTimeout'] = "Idle Timeout";

$l['all']['DBEngine'] = "Datenbank-Engine";
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


$l['all']['CreateIncrementingUsers'] = "Benutzer mit fortlaufender Nummerierung erstellen";
$l['all']['CreateRandomUsers'] = "Zufällige Benutzer erstellen";
$l['all']['StartingIndex'] = "Startindex";
$l['all']['EndingIndex'] = "Endindex";
$l['all']['RandomChars'] = "Erlaubte Zufallszeichen";
$l['all']['Memfree'] = "Freier Speicher";
$l['all']['Uptime'] = "Betriebszeit";
$l['all']['BandwidthUp'] = "Upstream-Bandbreite";
$l['all']['BandwidthDown'] = "Downstream-Bandbreite";

$l['all']['BatchCost'] = "Batch-Gebühr";

$l['all']['PaymentDate'] = "Zahlungsdatum";
$l['all']['PaymentStatus'] = "Zahlungsstatus";
$l['all']['FirstName'] = "Vorname";
$l['all']['LastName'] = "Nachname";
$l['all']['VendorType'] = "Zahlungsanbieter";
$l['all']['PayerStatus'] = "Zahlerstatus";
$l['all']['PaymentAddressStatus'] = "Zahlungsadressen-Status";
$l['all']['PayerEmail'] = "Zahler-E-Mail";
$l['all']['TxnId'] = "Transaktions-ID";
$l['all']['PlanActive'] = "Tarif aktiv";
$l['all']['PlanTimeType'] = "Tarif-Zeittyp";
$l['all']['PlanTimeBank'] = "Tarif-Zeitguthaben";
$l['all']['PlanTimeRefillCost'] = "Tarif-Nachfüllkosten (Zeit)";
$l['all']['PlanTrafficRefillCost'] = "Tarif-Nachfüllkosten (Traffic)";
$l['all']['PlanBandwidthUp'] = "Tarif-Upstream-Bandbreite";
$l['all']['PlanBandwidthDown'] = "Tarif-Downstream-Bandbreite";
$l['all']['PlanTrafficTotal'] = "Tarif-Datentraffic";
$l['all']['PlanTrafficDown'] = "Tarif-Downstream-Traffic";
$l['all']['PlanTrafficUp'] = "Tarif-Upstream-Traffic";
$l['all']['PlanRecurring'] = "Wiederkehrender Tarif";
$l['all']['PlanRecurringPeriod'] = "Tarif-Abrechnungsintervall";
$l['all']['planRecurringBillingSchedule'] = "Tarif-Abrechnungsplan (wiederkehrend)";
$l['all']['PlanCost'] = "Tarifkosten";
$l['all']['PlanSetupCost'] = "Tarif-Einrichtungskosten";
$l['all']['PlanTax'] = "Tarif-Steuer";
$l['all']['PlanCurrency'] = "Tarif-Währung";
$l['all']['PlanGroup'] = "Tarifprofil (Gruppe)";
$l['all']['PlanType'] = "Tarifart";
$l['all']['PlanName'] = "Tarifname";
$l['all']['PlanId'] = "Tarif-ID";

$l['all']['UserId'] = "Benutzer-ID";

$l['all']['Invoice'] = "Rechnung";
$l['all']['InvoiceID'] = "Rechnungs-ID";
$l['all']['InvoiceItems'] = "Rechnungsposten";
$l['all']['InvoiceStatus'] = "Rechnungsstatus";

$l['all']['InvoiceType'] = "Rechnungstyp";
$l['all']['Amount'] = "Betrag";
$l['all']['Total'] = "Gesamtbetrag";
$l['all']['TotalInvoices'] = "Gesamtrechnungen";

$l['all']['PayTypeName'] = "Name der Zahlungsart";
$l['all']['PayTypeNotes'] = "Beschreibung der Zahlungsart";
$l['all']['payment_type'] = "Zahlungsarten";
$l['all']['payments'] = "Zahlungen";
$l['all']['PaymentId'] = "Zahlungs-ID";
$l['all']['PaymentInvoiceID'] = "Rechnungs-ID";
$l['all']['PaymentAmount'] = "Zahlungsbetrag";
$l['all']['PaymentDate'] = "Zahlungsdatum";
$l['all']['PaymentType'] = "Zahlungsart";
$l['all']['PaymentNotes'] = "Zahlungshinweise";


$l['all']['Quantity'] = "Menge";
$l['all']['ReceiverEmail'] = "Empfänger E-Mail";
$l['all']['Business'] = "Unternehmen";
$l['all']['Tax'] = "Steuer";
$l['all']['Cost'] = "Kosten";
$l['all']['TotalCost'] = "Gesamtkosten";
$l['all']['TransactionFee'] = "Transaktionsgebühr";
$l['all']['PaymentCurrency'] = "Zahlungswährung";
$l['all']['AddressRecipient'] = "Adressat";
$l['all']['Street'] = "Straße";
$l['all']['Country'] = "Land";
$l['all']['CountryCode'] = "Ländercode";
$l['all']['City'] = "Stadt";
$l['all']['State'] = "Bundesland";
$l['all']['Zip'] = "Postleitzahl";

$l['all']['BusinessName'] = "Firmenname";
$l['all']['BusinessPhone'] = "Geschäfts-Telefonnummer";
$l['all']['BusinessAddress'] = "Geschäftsadresse";
$l['all']['BusinessWebsite'] = "Firmenwebseite";
$l['all']['BusinessEmail'] = "Geschäfts-E-Mail";
$l['all']['BusinessContactPerson'] = "Ansprechpartner";
$l['all']['DBPasswordEncryption'] = "Datenbank-Passwortverschlüsselungstyp";

$l['all']['Calling Station ID'] = "Calling Station ID";
$l['all']['Framed IP Address'] = "Framed IP Address";

/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "Geben Sie einen Bezeichnernamen für diese Batch-Erstellung an";
$l['Tooltip']['batchDescriptionTooltip'] = "Geben Sie eine allgemeine Beschreibung für diese Batch-Erstellung an";

$l['Tooltip']['hotspotTooltip'] = "Wählen Sie den Hotspot aus, mit dem diese Batch-Instanz verknüpft werden soll";

$l['Tooltip']['startingIndexTooltip'] = "Geben Sie den Startindex an, ab dem die Benutzer erstellt werden sollen";
$l['Tooltip']['planTooltip'] = "Wählen Sie einen Tarif aus, der dem Benutzer zugewiesen werden soll";

$l['Tooltip']['InvoiceEdit'] = "Rechnung bearbeiten";
$l['Tooltip']['invoiceTypeTooltip'] = "";
$l['Tooltip']['invoiceStatusTooltip'] = "";
$l['Tooltip']['invoiceID'] = "Geben Sie die Rechnungs-ID ein";
$l['Tooltip']['user_idTooltip'] = "Benutzer-ID";

$l['Tooltip']['amountTooltip'] = "";
$l['Tooltip']['taxTooltip'] = "";

$l['Tooltip']['PayTypeName'] = "Geben Sie den Namen der Zahlungsart ein";
$l['Tooltip']['EditPayType'] = "Zahlungsart bearbeiten";
$l['Tooltip']['RemovePayType'] = "Zahlungsart entfernen";
$l['Tooltip']['paymentTypeTooltip'] = "Der benutzerfreundliche Name der Zahlungsart, <br/>um den Zweck der Zahlung zu beschreiben";
$l['Tooltip']['paymentTypeNotesTooltip'] = "Die Beschreibung der Zahlungsart, <br/>um die Funktionsweise zu erläutern";
$l['Tooltip']['EditPayment'] = "Zahlung bearbeiten";
$l['Tooltip']['PaymentId'] = "Die Zahlungs-ID";
$l['Tooltip']['RemovePayment'] = "Zahlung entfernen";
$l['Tooltip']['paymentInvoiceTooltip'] = "Die mit dieser Zahlung verknüpfte Rechnung";



$l['Tooltip']['Username'] = "Geben Sie den Benutzernamen ein";
$l['Tooltip']['BatchName'] = "Geben Sie den Batch-Namen ein";
$l['Tooltip']['UsernameWildcard'] = "Hinweis: Ein Platzhalter (Wildcard) wird automatisch an die eingegebene Zeichenfolge angehängt.";
$l['Tooltip']['HotspotName'] = "Geben Sie den Hotspot-Namen ein";
$l['Tooltip']['NasName'] = "Geben Sie den NAS-Namen ein";
$l['Tooltip']['GroupName'] = "Geben Sie den Gruppennamen ein";
$l['Tooltip']['AttributeName'] = "Geben Sie den Name des Attributs ein";
$l['Tooltip']['VendorName'] = "Wählen Sie den Hersteller-Namen aus";
$l['Tooltip']['PoolName'] = "Geben Sie den Pool-Namen ein";
$l['Tooltip']['IPAddress'] = "Geben Sie die IP-Adresse ein";
$l['Tooltip']['Filter'] = "Geben Sie eine alphanumerische Zeichenkette ein oder lassen Sie das Feld leer, um alle anzuzeigen";
$l['Tooltip']['Date'] = "Wählen Sie ein Datum aus";
$l['Tooltip']['RateName'] = "Geben Sie den Tarifsatz-Namen ein";
$l['Tooltip']['OperatorName'] = "Geben Sie den Operatornamen ein";
$l['Tooltip']['BillingPlanName'] = "Geben Sie den Abrechnungstarifnamen ein";
$l['Tooltip']['PlanName'] = "Geben Sie den Tarifnamen ein";

$l['Tooltip']['EditRate'] = "Tarifsatz bearbeiten";
$l['Tooltip']['RemoveRate'] = "Tarifsatz entfernen";

$l['Tooltip']['rateNameTooltip'] = "Der benutzerfreundliche Name des Tarifsatzes, <br/>um den Zweck des Tarifs zu beschreiben";
$l['Tooltip']['rateTypeTooltip'] = "Der Tarifsatz-Typ, um die Funktionsweise <br/>des Tarifs zu beschreiben";
$l['Tooltip']['rateCostTooltip'] = "Die Kosten des Tarifsatzes";

$l['Tooltip']['planNameTooltip'] = "Der Name des Tarifs. Dies ist ein benutzerfreundlicher Name, <br/>der die Eigenschaften des Tarifs beschreibt";
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

$l['Tooltip']['EditIPPool'] = "IP-Pool bearbeiten";
$l['Tooltip']['RemoveIPPool'] = "IP-Pool entfernen";
$l['Tooltip']['EditIPAddress'] = "IP-Adresse bearbeiten";
$l['Tooltip']['RemoveIPAddress'] = "IP-Adresse entfernen";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Name des Proxys";
$l['Tooltip']['proxyRetryDelayTooltip'] = "Die Wartezeit auf eine Antwort des Proxys (in Sekunden), bevor die weitergeleitete Anfrage erneut gesendet wird.";
$l['Tooltip']['proxyRetryCountTooltip'] = "Die Anzahl der Wiederholungsversuche, bevor die Anfrage abgebrochen und eine Ablehnungsmeldung an den NAS gesendet wird.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "Wenn der Proxy-Server auf keine der Wiederholungsanfragen antwortet, "
                                      . "stoppt FreeRADIUS das Senden von Proxy-Anfragen an diesen Server und markiert ihn als 'dead'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "Falls keine exakt passenden Realms antworten, kann ein Fallback versucht werden.";
$l['Tooltip']['realmNameTooltip'] = "Name des Realms";
$l['Tooltip']['realmTypeTooltip'] = "Auf 'radius' setzen für Standardkonfiguration";
$l['Tooltip']['realmSecretTooltip'] = "Gemeinsames Secret des Realms";
$l['Tooltip']['realmAuthhostTooltip'] = "Authentifizierungs-Host des Realms";
$l['Tooltip']['realmAccthostTooltip'] = "Accounting-Host des Realms";
$l['Tooltip']['realmLdflagTooltip'] = "Ermöglicht Loadbalancing. Zulässige Werte sind 'fail_over' und 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Legt fest, ob der Realm-Suffix entfernt wird oder nicht";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "Der Name des Herstellers (z. B. Cisco, MikroTik, etc.).";
$l['Tooltip']['typeTooltip'] = "Der Datentyp dieses Attributs (z. B. string, integer, date, ipaddr, etc.).";
$l['Tooltip']['attributeTooltip'] = "Der Name des Attributs (z. B. Framed-IP-Address, Expiration, etc.).";

$l['Tooltip']['RecommendedOPTooltip'] = "Der empfohlene Operator für dieses Attribut (z. B. :=, ==, !=, etc.).";
$l['Tooltip']['RecommendedTableTooltip'] = "Die empfohlene Ziel-Tabelle für dieses Attribut (z. B. check oder reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Der Text, der als Tooltip angezeigt wird, wenn dieses Attribut ausgewählt wird (z. B. die IP-Adresse des Benutzers).";
$l['Tooltip']['RecommendedHelperTooltip'] = "Die Hilfsfunktion, die bei der Auswahl dieses Attributs verfügbar ist.";



$l['Tooltip']['AttributeEdit'] = "Attribut bearbeiten";

$l['Tooltip']['BatchDetails'] = "Batch-Details";

$l['Tooltip']['UserEdit'] = "Benutzer bearbeiten";
$l['Tooltip']['HotspotEdit'] = "Hotspot bearbeiten";
$l['Tooltip']['EditNAS'] = "NAS bearbeiten";
$l['Tooltip']['RemoveNAS'] = "NAS entfernen";
$l['Tooltip']['EditHG'] = "HuntGroup bearbeiten";
$l['Tooltip']['RemoveHG'] = "HuntGroup entfernen";
$l['Tooltip']['hgNasIpAddress'] = "Geben Sie die Host- oder IP-Adresse ein";
$l['Tooltip']['hgGroupName'] = "Geben Sie den Gruppennamen für den NAS ein";
$l['Tooltip']['hgNasPortId'] = "Geben Sie die NAS-Port-ID ein";
$l['Tooltip']['EditUserGroup'] = "Benutzergruppe bearbeiten";
$l['Tooltip']['ListUserGroups'] = "Benutzergruppen auflisten";
$l['Tooltip']['DeleteUserGroup'] = "Benutzer aus der Gruppe entfernen";

$l['Tooltip']['EditProfile'] = "Profil bearbeiten";

$l['Tooltip']['EditRealm'] = "Realm bearbeiten";
$l['Tooltip']['EditProxy'] = "Proxy bearbeiten";

$l['Tooltip']['EditGroup'] = "Gruppe bearbeiten";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "Wenn Sie einen Wert angeben, wird nur der einzelne Datensatz entfernt, der sowohl zum Gruppennamen als auch zum angegebenen Wert passt. Wenn Sie den Wert weglassen, werden alle Datensätze für diesen Gruppennamen entfernt!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "Wenn Sie einen Wert angeben, wird nur der einzelne Datensatz entfernt, der sowohl zum Gruppennamen als auch zum angegebenen Wert passt. Wenn Sie den Wert weglassen, werden alle Datensätze für diesen Gruppennamen entfernt!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(beschreibender Name)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "Wenn Sie eine Gruppe angeben, wird nur der einzelne Datensatz entfernt, der sowohl zum Benutzernamen als auch zur angegebenen Gruppe passt. Wenn Sie die Gruppe weglassen, werden alle Datensätze für diesen Benutzernamen entfernt!";

$l['Tooltip']['usernameTooltip'] = "Beispiel: max_mustermann. Der genaue Benutzername, den der Benutzer zur Anmeldung im System verwendet.";
$l['Tooltip']['passwordTypeTooltip'] = "Beispiel: Cleartext-Password, MD5-Password, SHA1-Password. Der Passworttyp, der zur Authentifizierung des Benutzers im RADIUS verwendet wird.";
$l['Tooltip']['passwordTooltip'] = "Beispiel: P@ssw0rt!. Das Passwort des Benutzers. Beachten Sie, dass manche Systeme zwischen Groß- und Kleinschreibung unterscheiden. Bitte achten Sie auf die genaue Eingabe.";
$l['Tooltip']['groupTooltip'] = "Beispiel: Premium_Benutzer. Die Gruppe, der der Benutzer hinzugefügt wird. Durch das Hinzufügen zu einer bestimmten Gruppe erbt der Benutzer die Attribute dieser Gruppe.";
$l['Tooltip']['macaddressTooltip'] = "Beispiel: 00:AA:BB:CC:DD:EE. Das MAC-Adressformat sollte dem Format entsprechen, das vom NAS gesendet wird. In der Regel ist das ohne Trennzeichen.";
$l['Tooltip']['pincodeTooltip'] = "Beispiel: khrivnxufi101. Geben Sie den genauen PIN-Code ein, den der Benutzer verwenden wird. Es können alphanumerische Zeichen verwendet werden. Der PIN-Code unterscheidet zwischen Groß- und Kleinschreibung!";
$l['Tooltip']['usernamePrefixTooltip'] = "Beispiel: TMP_, POP_, WIFI1_. Dieses Präfix wird dem generierten Benutzernamen vorangestellt.";
$l['Tooltip']['instancesToCreateTooltip'] = "Beispiel: 100. Die Anzahl der mit dem angegebenen Profil zufällig zu erstellenden Benutzer.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Beispiel: 8. Die Länge des zu erstellenden Benutzernamens. Empfohlener Bereich: 8–12 Zeichen.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Beispiel: 8. Die Länge des zu erstellenden Passworts. Empfohlener Bereich: 8–12 Zeichen.";
$l['Tooltip']['hotspotNameTooltip'] = "Beispiel: Hotel Adlon. Ein benutzerfreundlicher Name für den Hotspot.";
$l['Tooltip']['hotspotMacaddressTooltip'] = "Beispiel: 00-aa-bb-cc-dd-ee. Die MAC-Adresse des NAS.";
$l['Tooltip']['geocodeTooltip'] = "Beispiel: -1.002,-2.201. Dies sind die geographischen Koordinaten, um den Standort des Hotspots/NAS auf der Karte zu markieren (siehe GIS).";

$l['Tooltip']['reassignplanprofiles'] = "Wenn aktiviert, werden beim Anwenden der Benutzerinformationen <br/>
                    die im Tab 'Profile' aufgelisteten Profile ignoriert und <br/>
                    die Profile werden basierend auf der Tarif-Profil Zuordnung neu zugewiesen.";

/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/

$l['button']['DashboardSettings'] = "Dashboard-Einstellungen";


$l['button']['GenerateReport'] = "Bericht erstellen";

$l['button']['ListPayTypes'] = "Zahlungsarten auflisten";
$l['button']['NewPayType'] = "Neue Zahlungsart";
$l['button']['EditPayType'] = "Zahlungsart bearbeiten";
$l['button']['RemovePayType'] = "Zahlungsart entfernen";
$l['button']['ListPayments'] = "Zahlungen auflisten";
$l['button']['NewPayment'] = "Neue Zahlung";
$l['button']['EditPayment'] = "Zahlung bearbeiten";
$l['button']['RemovePayment'] = "Zahlung entfernen";

$l['button']['NewUsers'] = "Neuer Benutzer";

$l['button']['ClearSessions'] = "Sessions löschen";
$l['button']['Dashboard'] = "Dashboard";
$l['button']['MailSettings'] = "E-Mail-Einstellungen";

$l['button']['Batch'] = "Batch";
$l['button']['BatchHistory'] = "Batch-Verlauf";
$l['button']['BatchDetails'] = "Batch-Details";

$l['button']['ListRates'] = "Tarifsätze auflisten";
$l['button']['NewRate'] = "Neuer Tarifsatz";
$l['button']['EditRate'] = "Tarifsatz bearbeiten";
$l['button']['RemoveRate'] = "Tarifsatz entfernen";

$l['button']['ListPlans'] = "Tarife auflisten";
$l['button']['NewPlan'] = "Neuer Tarif";
$l['button']['EditPlan'] = "Tarif bearbeiten";
$l['button']['RemovePlan'] = "Tarif entfernen";

$l['button']['ListInvoices'] = "Rechnungen auflisten";
$l['button']['NewInvoice'] = "Neue Rechnung";
$l['button']['EditInvoice'] = "Rechnung bearbeiten";
$l['button']['RemoveInvoice'] = "Rechnung entfernen";

$l['button']['ListRealms'] = "Realms auflisten";
$l['button']['NewRealm'] = "Neuer Realm";
$l['button']['EditRealm'] = "Realm bearbeiten";
$l['button']['RemoveRealm'] = "Realm entfernen";

$l['button']['ListProxys'] = "Proxys auflisten";
$l['button']['NewProxy'] = "Neuer Proxy";
$l['button']['EditProxy'] = "Proxy bearbeiten";
$l['button']['RemoveProxy'] = "Proxy entfernen";

$l['button']['ListAttributesforVendor'] = "Attribute für Hersteller auflisten:";
$l['button']['NewVendorAttribute'] = "Neues Hersteller-Attribut";
$l['button']['EditVendorAttribute'] = "Hersteller-Attribut bearbeiten";
$l['button']['SearchVendorAttribute'] = "Attribut suchen";
$l['button']['RemoveVendorAttribute'] = "Hersteller-Attribut entfernen";
$l['button']['ImportVendorDictionary'] = "Herstellerwörterbuch importieren";


$l['button']['BetweenDates'] = "Zeitraum:";
$l['button']['Where'] = "Filter:";
$l['button']['AccountingFieldsinQuery'] = "Accounting-Felder in Abfrage:";
$l['button']['OrderBy'] = "Sortieren nach:";
$l['button']['HotspotAccounting'] = "Hotspot-Accounting";
$l['button']['HotspotsComparison'] = "Hotspot-Vergleich";

$l['button']['CleanupStaleSessions'] = "Stale-Sessions bereinigen";
$l['button']['DeleteAccountingRecords'] = "Accounting-Datensätze löschen";

$l['button']['ListUsers'] = "Benutzer auflisten";
$l['button']['ListBatches'] = "Batches auflisten";
$l['button']['RemoveBatch'] = "Batch entfernen";
$l['button']['ImportUsers'] = "Benutzer importieren";
$l['button']['NewUser'] = "Neuer Benutzer";
$l['button']['NewUserQuick'] = "Neuer Benutzer (schnell)";
$l['button']['BatchAddUsers'] = "Benutzer batchweise hinzufügen";
$l['button']['EditUser'] = "Benutzer bearbeiten";
$l['button']['SearchUsers'] = "Benutzer suchen";
$l['button']['RemoveUsers'] = "Benutzer entfernen";
$l['button']['ListHotspots'] = "Hotspots auflisten";
$l['button']['NewHotspot'] = "Neuer Hotspot";
$l['button']['EditHotspot'] = "Hotspot bearbeiten";
$l['button']['RemoveHotspot'] = "Hotspot entfernen";

$l['button']['ListIPPools'] = "IP-Pools auflisten";
$l['button']['NewIPPool'] = "Neuer IP-Pool";
$l['button']['EditIPPool'] = "IP-Pool bearbeiten";
$l['button']['RemoveIPPool'] = "IP-Pool entfernen";

$l['button']['ListNAS'] = "NAS auflisten";
$l['button']['NewNAS'] = "Neuer NAS";
$l['button']['EditNAS'] = "NAS bearbeiten";
$l['button']['RemoveNAS'] = "NAS entfernen";
$l['button']['ListHG'] = "HuntGroups auflisten";
$l['button']['NewHG'] = "Neue HuntGroup";
$l['button']['EditHG'] = "HuntGroup bearbeiten";
$l['button']['RemoveHG'] = "HuntGroup entfernen";
$l['button']['ListUserGroup'] = "Benutzergruppen-Mappings auflisten";
$l['button']['ListUsersGroup'] = "Benutzers Gruppen-Mappings auflisten";
$l['button']['NewUserGroup'] = "Benutzergruppen-Mapping erstellen";
$l['button']['EditUserGroup'] = "Benutzergruppen-Mapping bearbeiten";
$l['button']['RemoveUserGroup'] = "Benutzergruppen-Mapping entfernen";

$l['button']['ListProfiles'] = "Profile auflisten";
$l['button']['NewProfile'] = "Neues Profil";
$l['button']['EditProfile'] = "Profil bearbeiten";
$l['button']['DuplicateProfile'] = "Profil duplizieren";
$l['button']['RemoveProfile'] = "Profil entfernen";

$l['button']['ListGroupReply'] = "Group-Reply-Mappings auflisten";
$l['button']['SearchGroupReply'] = "Group-Reply-Mapping suchen";
$l['button']['NewGroupReply'] = "Neues Group-Reply-Mapping";
$l['button']['EditGroupReply'] = "Group-Reply-Mapping bearbeiten";
$l['button']['RemoveGroupReply'] = "Group-Reply-Mapping entfernen";

$l['button']['ListGroupCheck'] = "Group-Check-Mappings auflisten";
$l['button']['SearchGroupCheck'] = "Group-Check-Mapping suchen";
$l['button']['NewGroupCheck'] = "Neues Group-Check-Mapping";
$l['button']['EditGroupCheck'] = "Group-Check-Mapping bearbeiten";
$l['button']['RemoveGroupCheck'] = "Group-Check-Mapping entfernen";

$l['button']['UserAccounting'] = "Benutzer-Accounting";
$l['button']['IPAccounting'] = "IP-Accounting";
$l['button']['NASIPAccounting'] = "NAS-IP-Accounting";
$l['button']['NASIPAccountingOnlyActive'] = "Nur Aktive anzeigen";
$l['button']['DateAccounting'] = "Datum-Accounting";
$l['button']['AllRecords'] = "Alle Datensätze";
$l['button']['ActiveRecords'] = "Aktive Datensätze";

$l['button']['PlanUsage'] = "Tarifnutzung";

$l['button']['OnlineUsers'] = "Benutzer online";
$l['button']['LastConnectionAttempts'] = "Letzte Verbindungsversuche";
$l['button']['TopUser'] = "Top-Benutzer";
$l['button']['History'] = "Verlauf";

$l['button']['ServerStatus'] = "Server Status";
$l['button']['ServicesStatus'] = "Services Status";

$l['button']['daloRADIUSLog'] = "daloRADIUS-Log";
$l['button']['RadiusLog'] = "Radius-Log";
$l['button']['SystemLog'] = "System-Log";
$l['button']['BootLog'] = "Boot-Log";

$l['button']['UserLogins'] = "Benutzer-Logins";
$l['button']['UserDownloads'] = "Benutzer-Downloads";
$l['button']['UserUploads'] = "Benutzer-Uploads";
$l['button']['TotalLogins'] = "Gesamt-Logins";
$l['button']['TotalTraffic'] = "Gesamter Traffic";
$l['button']['Users'] = "Angemeldete Benutzer";

$l['button']['ViewMAP'] = "Karte anzeigen";
$l['button']['EditMAP'] = "Karte bearbeiten";
$l['button']['RegisterGoogleMapsAPI'] = "Google-Maps-API registrieren";

$l['button']['UserSettings'] = "Benutzereinstellungen";
$l['button']['DatabaseSettings'] = "Datenbankeinstellungen";
$l['button']['LanguageSettings'] = "Spracheinstellungen";
$l['button']['LoggingSettings'] = "Loggingeinstellungen";
$l['button']['InterfaceSettings'] = "Interfaceeinstellungen";

$l['button']['ReAssignPlanProfiles'] = "Tarifprofile neu zuweisen";

$l['button']['TestUserConnectivity'] = "Benutzerverbindung testen";
$l['button']['DisconnectUser'] = "Benutzer trennen";

$l['button']['ManageBackups'] = "Backups verwalten";
$l['button']['CreateBackups'] = "Backups erstellen";

$l['button']['ListOperators'] = "Operatoren auflisten";
$l['button']['NewOperator'] = "Neuer Operator";
$l['button']['EditOperator'] = "Operator bearbeiten";
$l['button']['RemoveOperator'] = "Operator entfernen";

$l['button']['ProcessQuery'] = "Abfrage verarbeiten";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['ImportUsers'] = "Benutzer importieren";


$l['title']['Dashboard'] = "Dashboard";
$l['title']['DashboardAlerts'] = "Benachrichtigungen";

$l['title']['Invoice'] = "Rechnung";
$l['title']['Invoices'] = "Rechnungen";
$l['title']['InvoiceRemoval'] = "Rechnung entfernen";
$l['title']['Payments'] = "Zahlungen";
$l['title']['Items'] = "Posten";

$l['title']['PayTypeInfo'] = "Informationen zur Zahlungsart";
$l['title']['PaymentInfo'] = "Zahlungsinformationen";


$l['title']['RateInfo'] = "Tarifsatz-Informationen";
$l['title']['PlanInfo'] = "Tarif-Informationen";
$l['title']['TimeSettings'] = "Zeiteinstellungen";
$l['title']['BandwidthSettings'] = "Bandbreiteneinstellungen";
$l['title']['PlanRemoval'] = "Tarif entfernen";

$l['title']['BatchRemoval'] = "Batch-Entfernung";

$l['title']['Backups'] = "Backups";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS-Tabellen";
$l['title']['daloRADIUSTables'] = "daloRADIUS-Tabellen";

$l['title']['IPPoolInfo'] = "IP-Pool Informationen";

$l['title']['BusinessInfo'] = "Geschäftsinformationen";

$l['title']['CleanupRecordsByUsername'] = "Nach Benutzername";
$l['title']['CleanupRecordsByDate'] = "Nach Datum";
$l['title']['DeleteRecords'] = "Datensätze löschen";

$l['title']['RealmInfo'] = "Realm-Informationen";

$l['title']['ProxyInfo'] = "Proxy-Informationen";

$l['title']['VendorAttribute'] = "Hersteller-Attribut";

$l['title']['AccountRemoval'] = "Konto entfernen";
$l['title']['AccountInfo'] = "Kontoinformationen";

$l['title']['Profiles'] = "Profile";
$l['title']['ProfileInfo'] = "Profil-Informationen";

$l['title']['GroupInfo'] = "Gruppeninformationen";
$l['title']['GroupAttributes'] = "Gruppen-Attribute";

$l['title']['NASInfo'] = "NAS-Informationen";
$l['title']['NASAdvanced'] = "Erweiterte NAS-Einstellungen";
$l['title']['HGInfo'] = "HuntGroup-Informationen";
$l['title']['UserInfo'] = "Benutzerinformationen";
$l['title']['BillingInfo'] = "Abrechnungsinformationen";

$l['title']['Attributes'] = "Attribute";
$l['title']['ProfileAttributes'] = "Profil Attribute";

$l['title']['HotspotInfo'] = "Hotspot-Informationen";
$l['title']['HotspotRemoval'] = "Hotspot entfernen";

$l['title']['ContactInfo'] = "Kontaktinformationen";

$l['title']['Plan'] = "Tarif";

$l['title']['Profile'] = "Profil";
$l['title']['Groups'] = "Gruppen";
$l['title']['RADIUSCheck'] = "Check-Attribute";
$l['title']['RADIUSReply'] = "Reply-Attribute";

$l['title']['Settings'] = "Einstellungen";
$l['title']['DatabaseSettings'] = "Datenbankeinstellungen";
$l['title']['DatabaseTables'] = "Datenbank-Tabellen";
$l['title']['AdvancedSettings'] = "Erweiterte Einstellungen";

$l['title']['Advanced'] = "Erweitert";
$l['title']['Optional'] = "Optional";

/* ********************************************************************************** */

/* **********************************************************************************
 * Graphs
 * General graphing text
 ************************************************************************************/
$l['graphs']['Day'] = "Tag";
$l['graphs']['Month'] = "Monat";
$l['graphs']['Year'] = "Jahr";
$l['graphs']['Jan'] = "Januar";
$l['graphs']['Feb'] = "Februar";
$l['graphs']['Mar'] = "März";
$l['graphs']['Apr'] = "April";
$l['graphs']['May'] = "Mai";
$l['graphs']['Jun'] = "Juni";
$l['graphs']['Jul'] = "Juli";
$l['graphs']['Aug'] = "August";
$l['graphs']['Sep'] = "September";
$l['graphs']['Oct'] = "Oktober";
$l['graphs']['Nov'] = "November";
$l['graphs']['Dec'] = "Dezember";


/* ********************************************************************************** */

/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Login erforderlich";
$l['text']['LoginPlease'] = "Bitte anmelden";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "Vorname";
$l['ContactInfo']['LastName'] = "Nachname";
$l['ContactInfo']['Email'] = "E-Mail";
$l['ContactInfo']['Department'] = "Abteilung";
$l['ContactInfo']['WorkPhone'] = "Telefon (geschäftlich)";
$l['ContactInfo']['HomePhone'] = "Telefon (privat)";
$l['ContactInfo']['Phone'] = "Telefon";
$l['ContactInfo']['MobilePhone'] = "Telefon (mobil)";
$l['ContactInfo']['Notes'] = "Notizen";
$l['ContactInfo']['EnableUserUpdate'] = "Benutzeraktualisierung erlauben";
$l['ContactInfo']['EnablePortalLogin'] = "Portal-Login erlauben";
$l['ContactInfo']['PortalLoginPassword'] = "Portal-Login Passwort";

$l['ContactInfo']['OwnerName'] = "Inhabername";
$l['ContactInfo']['OwnerEmail'] = "E-Mail des Inhabers";
$l['ContactInfo']['ManagerName'] = "Name des Vorgesetzten";
$l['ContactInfo']['ManagerEmail'] = "E-Mail des Vorgesetzten";
$l['ContactInfo']['Company'] = "Firma";
$l['ContactInfo']['Address'] = "Adresse";
$l['ContactInfo']['City'] = "Stadt";
$l['ContactInfo']['State'] = "Bundesland";
$l['ContactInfo']['Country'] = "Land";
$l['ContactInfo']['Zip'] = "Postleitzahl";
$l['ContactInfo']['Phone1'] = "Telefon 1";
$l['ContactInfo']['Phone2'] = "Telefon 2";
$l['ContactInfo']['HotspotType'] = "Hotspot-Typ";
$l['ContactInfo']['CompanyWebsite'] = "Firmenwebseite";
$l['ContactInfo']['CompanyPhone'] = "Firmentelefon";
$l['ContactInfo']['CompanyEmail'] = "Firmen E-Mail";
$l['ContactInfo']['CompanyContact'] = "Firmenkontakt";

$l['ContactInfo']['PlanName'] = "Tarifname";
$l['ContactInfo']['ContactPerson'] = "Ansprechpartner";
$l['ContactInfo']['PaymentMethod'] = "Zahlungsart";
$l['ContactInfo']['Cash'] = "Barzahlung";
$l['ContactInfo']['CreditCardNumber'] = "Kreditkartennummer";
$l['ContactInfo']['CreditCardName'] = "Name auf der Kreditkarte";
$l['ContactInfo']['CreditCardVerificationNumber'] = "Prüfnummer der Kreditkarte";
$l['ContactInfo']['CreditCardType'] = "Kreditkartenart";
$l['ContactInfo']['CreditCardExpiration'] = "Ablaufdatum der Kreditkarte";

/* ********************************************************************************** */

$l['Intro']['configdashboard.php'] = "Dashboard-Einstellungen";



$l['Intro']['paymenttypesmain.php'] = "Zahlungsarten-Seite";
$l['Intro']['paymenttypesdel.php'] = "Zahlungsart-Eintrag löschen";
$l['Intro']['paymenttypesedit.php'] = "Zahlungsart-Details bearbeiten";
$l['Intro']['paymenttypeslist.php'] = "Zahlungsarten-Tabelle";
$l['Intro']['paymenttypesnew.php'] = "Neuer Zahlungsart-Eintrag";
$l['Intro']['paymentslist.php'] = "Zahlungen-Tabelle";
$l['Intro']['paymentsmain.php'] = "Zahlungen-Seite";
$l['Intro']['paymentsdel.php'] = "Zahlungseintrag löschen";
$l['Intro']['paymentsedit.php'] = "Zahlungsdetails bearbeiten";
$l['Intro']['paymentsnew.php'] = "Neuer Zahlungseintrag";


$l['Intro']['billhistorymain.php'] = "Abrechnungsverlauf";
$l['Intro']['msgerrorpermissions.php'] = "Fehler";

$l['Intro']['repnewusers.php'] = "Neue Benutzer auflisten";

$l['Intro']['mngradproxys.php'] = "Proxy-Management";
$l['Intro']['mngradproxysnew.php'] = "Neuer Proxy";
$l['Intro']['mngradproxyslist.php'] = "Proxy auflisten";
$l['Intro']['mngradproxysedit.php'] = "Proxy bearbeiten";
$l['Intro']['mngradproxysdel.php'] = "Proxy entfernen";

$l['Intro']['mngradrealms.php'] = "Realm-Management";
$l['Intro']['mngradrealmsnew.php'] = "Neuer Realm";
$l['Intro']['mngradrealmslist.php'] = "Realm auflisten";
$l['Intro']['mngradrealmsedit.php'] = "Realm bearbeiten";
$l['Intro']['mngradrealmsdel.php'] = "Realm entfernen";

$l['Intro']['mngradattributes.php'] = "Hersteller-Attribute-Management";
$l['Intro']['mngradattributeslist.php'] = "Hersteller-Attribute auflisten";
$l['Intro']['mngradattributesnew.php'] = "Neue Hersteller-Attribute";
$l['Intro']['mngradattributesedit.php'] = "Hersteller-Attribute bearbeiten";
$l['Intro']['mngradattributessearch.php'] = "Attribute suchen";
$l['Intro']['mngradattributesdel.php'] = "Hersteller-Attribute entfernen";
$l['Intro']['mngradattributesimport.php'] = "Herstellerwörterbuch importieren";
$l['Intro']['mngimportusers.php'] = "Benutzer importieren";


$l['Intro']['acctactive.php'] = "Accounting: Aktive Datensätze";
$l['Intro']['acctall.php'] = "Accounting: Alle Benutzer";
$l['Intro']['acctdate.php'] = "Accounting: Nach Datum sortiert";
$l['Intro']['accthotspot.php'] = "Hotspot-Accounting";
$l['Intro']['acctipaddress.php'] = "IP-Accounting";
$l['Intro']['accthotspotcompare.php'] = "Hotspot-Vergleich";
$l['Intro']['acctmain.php'] = "Accounting-Seite";
$l['Intro']['acctplans.php'] = "Accounting: Tarife";
$l['Intro']['acctnasipaddress.php'] = "NAS-IP-Accounting";
$l['Intro']['acctusername.php'] = "Benutzer-Accounting";
$l['Intro']['acctcustom.php'] = "Benutzerdefiniertes Accounting";
$l['Intro']['acctcustomquery.php'] = "Benutzerdefinierte Abfrage für Accounting";
$l['Intro']['acctmaintenance.php'] = "Wartung der Accounting-Datensätze";
$l['Intro']['acctmaintenancecleanup.php'] = "Stale-Connections bereinigen";
$l['Intro']['acctmaintenancedelete.php'] = "Accounting-Datensätze löschen";

$l['Intro']['billmain.php'] = "Abrechnungsseite";
$l['Intro']['ratesmain.php'] = "Tarifsatz-Abrechnungsseite";
$l['Intro']['billratesdate.php'] = "Prepaid-Tarifsatz-Accounting";
$l['Intro']['billratesdel.php'] = "Tarifsatz-Eintrag löschen";
$l['Intro']['billratesedit.php'] = "Tarifsatz-Details bearbeiten";
$l['Intro']['billrateslist.php'] = "Tarifsatz-Tabelle";
$l['Intro']['billratesnew.php'] = "Neuer Tarifsatz-Eintrag";

$l['Intro']['paypalmain.php'] = "PayPal-Transaktionsseite";
$l['Intro']['billpaypaltransactions.php'] = "PayPal-Transaktionsseite";

$l['Intro']['billhistoryquery.php'] = "Abrechnungsverlauf";

$l['Intro']['billinvoice.php'] = "Rechnungsstellung";
$l['Intro']['billinvoicedel.php'] = "Rechnungseintrag löschen";
$l['Intro']['billinvoiceedit.php'] = "Rechnung bearbeiten";
$l['Intro']['billinvoicelist.php'] = "Rechnungen auflisten";
$l['Intro']['billinvoicereport.php'] = "Rechnungsbericht";
$l['Intro']['billinvoicenew.php'] = "Neue Rechnung";

$l['Intro']['billplans.php'] = "Abrechnungstarife-Seite";
$l['Intro']['billplansdel.php'] = "Tarif-Eintrag löschen";
$l['Intro']['billplansedit.php'] = "Tarif-Details bearbeiten";
$l['Intro']['billplanslist.php'] = "Tarif-Tabelle";
$l['Intro']['billplansnew.php'] = "Neuer Tarif-Eintrag";

$l['Intro']['billpos.php'] = "Abrechnung: Point of Sales Seite";
$l['Intro']['billposdel.php'] = "Benutzer löschen";
$l['Intro']['billposedit.php'] = "Benutzer bearbeiten";
$l['Intro']['billposlist.php'] = "Benutzer auflisten";
$l['Intro']['billposnew.php'] = "Neuer Benutzer";

$l['Intro']['giseditmap.php'] = "Modus: Karte bearbeiten";
$l['Intro']['gismain.php'] = "GIS-Kartierung";
$l['Intro']['gisviewmap.php'] = "Modus: Karte anzeigen";

$l['Intro']['graphmain.php'] = "Nutzungsdiagramme";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Gesamter Datenverkehrsvergleich";
$l['Intro']['graphsalltimelogins.php'] = "Gesamt-Logins";
$l['Intro']['graphsloggedusers.php'] = "Angemeldete Benutzer";
$l['Intro']['graphsoveralldownload.php'] = "Benutzer-Downloads";
$l['Intro']['graphsoveralllogins.php'] = "Benutzer-Logins";
$l['Intro']['graphsoverallupload.php'] = "Benutzer-Uploads";

$l['Intro']['rephistory.php'] = "Aktionsverlauf";
$l['Intro']['replastconnect.php'] = "Letzte Verbindungsversuche";
$l['Intro']['repstatradius.php'] = "Daemon-Informationen";
$l['Intro']['repstatserver.php'] = "Serverstatus und -informationen";
$l['Intro']['reponline.php'] = "Online-Benutzer auflisten";
$l['Intro']['replogssystem.php'] = "System-Log Anzeige";
$l['Intro']['replogsradius.php'] = "freeRADIUS-Log-Anzeige";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS-Log Anzeige";
$l['Intro']['replogsboot.php'] = "Boot-Log Anzeige";
$l['Intro']['replogs.php'] = "Logs";
$l['Intro']['rephb.php'] = "Heartbeat";
$l['Intro']['rephbdashboard.php'] = "daloRADIUS-NAS-Dashboard";
$l['Intro']['repbatch.php'] = "Batch";
$l['Intro']['mngbatchlist.php'] = "Batch-Sessions auflisten";
$l['Intro']['repbatchlist.php'] = "Batch-Benutzer auflisten";
$l['Intro']['repbatchdetails.php'] = "Batch-Details";

$l['Intro']['rephsall.php'] = "Hotspots auflisten";
$l['Intro']['repmain.php'] = "Berichteseite";
$l['Intro']['repstatus.php'] = "Statusseite";
$l['Intro']['reptopusers.php'] = "Top-Benutzer";
$l['Intro']['repusername.php'] = "Benutzer auflisten";

$l['Intro']['mngbatch.php'] = "Batch-Benutzer erstellen";
$l['Intro']['mngbatchdel.php'] = "Batch-Sessions löschen";

$l['Intro']['mngdel.php'] = "Benutzer entfernen";
$l['Intro']['mngedit.php'] = "Benutzerdetails bearbeiten";
$l['Intro']['mnglistall.php'] = "Benutzer auflisten";
$l['Intro']['mngmain.php'] = "Benutzer- und Hotspot-Management";
$l['Intro']['mngbatch.php'] = "Batch-Benutzer-Management";
$l['Intro']['mngnew.php'] = "Neuer Benutzer";
$l['Intro']['mngnewquick.php'] = "Benutzer schnell anlegen";
$l['Intro']['mngsearch.php'] = "Benutzer suchen";

$l['Intro']['mnghsdel.php'] = "Hotspots entfernen";
$l['Intro']['mnghsedit.php'] = "Hotspot-Details bearbeiten";
$l['Intro']['mnghslist.php'] = "Hotspots auflisten";
$l['Intro']['mnghsnew.php'] = "Neuer Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Benutzergruppen-Mapping entfernen";
$l['Intro']['mngradusergroup.php'] = "Benutzergruppen-Management";
$l['Intro']['mngradusergroupnew.php'] = "Neues Benutzergruppen-Mapping";
$l['Intro']['mngradusergrouplist'] = "Benutzergruppen-Mappings in der Datenbank";
$l['Intro']['mngradusergrouplistuser'] = "Benutzergruppen-Mappings in der Datenbank";
$l['Intro']['mngradusergroupedit'] = "Bearbeite Benutzergruppen-Mapping für Benutzer:";

$l['Intro']['mngradippool.php'] = "IP-Pool-Management";
$l['Intro']['mngradippoolnew.php'] = "Neuer IP-Pool";
$l['Intro']['mngradippoollist.php'] = "IP-Pools auflisten";
$l['Intro']['mngradippooledit.php'] = "IP-Pool bearbeiten";
$l['Intro']['mngradippooldel.php'] = "IP-Pool entfernen";

$l['Intro']['mngradnas.php'] = "NAS-Management";
$l['Intro']['mngradnasnew.php'] = "Neuer NAS-Eintrag";
$l['Intro']['mngradnaslist.php'] = "NAS-Einträge in der Datenbank";
$l['Intro']['mngradnasedit.php'] = "NAS-Eintrag bearbeiten";
$l['Intro']['mngradnasdel.php'] = "NAS-Eintrag entfernen";

$l['Intro']['mngradhunt.php'] = "HuntGroup-Management";
$l['Intro']['mngradhuntnew.php'] = "Neuer HuntGroup-Eintrag";
$l['Intro']['mngradhuntlist.php'] = "HuntGroup-Einträge in der Datenbank";
$l['Intro']['mngradhuntedit.php'] = "HuntGroup-Eintrag bearbeiten";
$l['Intro']['mngradhuntdel.php'] = "HuntGroup-Eintrag entfernen";

$l['Intro']['mngradprofiles.php'] = "Profil-Management";
$l['Intro']['mngradprofilesedit.php'] = "Profile bearbeiten";
$l['Intro']['mngradprofilesduplicate.php'] = "Profile duplizieren";
$l['Intro']['mngradprofilesdel.php'] = "Profile löschen";
$l['Intro']['mngradprofileslist.php'] = "Profile auflisten";
$l['Intro']['mngradprofilesnew.php'] = "Neues Profil";

$l['Intro']['mngradgroups.php'] = "Gruppen-Management";

$l['Intro']['mngradgroupreplynew.php'] = "Neues Gruppen-Reply-Mapping";
$l['Intro']['mngradgroupreplylist.php'] = "Gruppen-Reply-Mappings in der Datenbank";
$l['Intro']['mngradgroupreplyedit.php'] = "Bearbeite Gruppen-Reply-Mapping für Gruppe:";
$l['Intro']['mngradgroupreplydel.php'] = "Gruppen-Reply-Mapping entfernen";
$l['Intro']['mngradgroupreplysearch.php'] = "Gruppen-Reply-Mapping suchen";

$l['Intro']['mngradgroupchecknew.php'] = "Neues Gruppen-Check-Mapping";
$l['Intro']['mngradgroupchecklist.php'] = "Gruppen-Check-Mappings in der Datenbank";
$l['Intro']['mngradgroupcheckedit.php'] = "Gruppen-Check-Mapping für Gruppe bearbeiten:";
$l['Intro']['mngradgroupcheckdel.php'] = "Gruppen-Check-Mapping entfernen";
$l['Intro']['mngradgroupchecksearch.php'] = "Gruppen-Check-Mapping suchen";

$l['Intro']['configuser.php'] = "Benutzer-Konfiguration";
$l['Intro']['configmail.php'] = "E-Mail-Konfiguration";
$l['Intro']['configcrontab.php'] = "Wiederkehrende Aufgaben-Konfiguration";
$l['Intro']['configdb.php'] = "Datenbank-Konfiguration";
$l['Intro']['configlang.php'] = "Sprach-Konfiguration";
$l['Intro']['configlogging.php'] = "Logging-Konfiguration";
$l['Intro']['configinterface.php'] = "Interface-Konfiguration";
$l['Intro']['configmainttestuser.php'] = "Benutzerverbindung testen";
$l['Intro']['configmain.php'] = "Systemkonfiguration";
$l['Intro']['configmaint.php'] = "Wartung";
$l['Intro']['configmaintdisconnectuser.php'] = "Benutzer trennen";
$l['Intro']['configbusiness.php'] = "Geschäftsdetails";
$l['Intro']['configbusinessinfo.php'] = "Geschäftsinformationen";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupcreatebackups.php'] = "Backups erstellen";
$l['Intro']['configbackupmanagebackups.php'] = "Backups verwalten";

$l['Intro']['configoperators.php'] = "Operatoren-Konfiguration";
$l['Intro']['configoperatorsdel.php'] = "Operator entfernen";
$l['Intro']['configoperatorsedit.php'] = "Operator-Einstellungen bearbeiten";
$l['Intro']['configoperatorsnew.php'] = "Neuer Operator";
$l['Intro']['configoperatorslist.php'] = "Operatoren auflisten";

$l['Intro']['login.php'] = "Login";

$l['captions']['providebillratetodel'] = "Geben Sie den Tarifsatz-Eintrag an, den Sie löschen möchten";
$l['captions']['detailsofnewrate'] = "Sie können unten die Details für den neuen Tarifsatz ausfüllen";
$l['captions']['filldetailsofnewrate'] = "Füllen Sie unten die Details für den neuen Tarifsatz-Eintrag aus";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "Dashboard-Einstellungen";


$l['helpPage']['repnewusers'] = "Die folgende Tabelle listet die neu erstellten Benutzer pro Monat auf.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "Alle PayPal-Transaktionen auflisten";
$l['helpPage']['billhistoryquery'] = "Verlauf aller Rechnungsdaten für Benutzer auflisten";

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

$l['helpPage']['msgerrorpermissions'] = "Leider haben Sie nicht die erforderlichen Berechtigungen, um auf diesen Bereich zuzugreifen. <br>Bitte kontaktieren Sie den System-Administrator.";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Um einen Benutzereintrag aus der Datenbank zu entfernen, müssen Sie den Benutzernamen des Kontos angeben.";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";

// profiles help pages
$l['helpPage']['mngradprofilesnew'] = <<<EOF
<h2 class="fs-6">Neues Profil</h2>
<p>Verwenden Sie diese Funktion, um ein neues Profil zu erstellen. Sie müssen die Reply- und Check-Attribute angeben, die dem Profil zugeordnet werden sollen. Sobald Sie das Profil erstellt haben, steht es den Benutzern im System zur Verfügung.</p>
EOF;
$l['helpPage']['mngradprofileslist'] = <<<EOF
<h2 class="fs-6">Profile auflisten</h2>
<p>Mit dieser Funktion können Sie eine Liste aller verfügbaren Profile im System anzeigen. Sie können ein Profil auswählen, um dessen Details anzuzeigen oder es zu bearbeiten oder zu löschen.</p>
EOF;
$l['helpPage']['mngradprofiles'] = <<<EOF
<h1 class="fs-5">Profil-Management</h1>
<p>Profile werden verwendet, um Sätze von Reply-Attributen und Check-Attributen für Benutzer zu verwalten.<br>
Im Wesentlichen ist ein Profil eine Kombination aus einem Group-Reply und einem Group-Check.</p>
EOF;
$l['helpPage']['mngradprofilesedit'] = <<<EOF
<h2 class="fs-6">Profil bearbeiten</h2>
<p>Wenn Sie Änderungen an einem bestehenden Profil vornehmen müssen, können Sie diese Funktion verwenden. Sie können die Reply- und Check-Attribute bearbeiten, die dem Profil zugeordnet sind.</p>
EOF;
$l['helpPage']['mngradprofilesduplicate'] = <<<EOF
<h2 class="fs-6">Profil duplizieren</h2>
<p>Mit dieser Funktion können Sie schnell ein neues Profil basierend auf einem Bestehenden erstellen.
   Wählen Sie einfach das Profil aus, das Sie duplizieren möchten, geben Sie einen neuen Namen für das duplizierte Profil ein und klicken Sie auf "Duplizieren".
   Das neue Profil wird dieselben Reply-Attribute und Check-Attribute wie das ursprüngliche Profil haben, sodass Sie einfach die gewünschten Anpassungen vornehmen können.</p>
EOF;
$l['helpPage']['mngradprofilesdel'] = <<<EOF
<h2 class="fs-6">Profil löschen</h2>
<p>Wenn Sie ein Profil nicht mehr benötigen, können Sie es mit dieser Funktion löschen. Beachten Sie, dass durch das Löschen eines Profils auch alle Zuordnungen zwischen dem Profil und den Benutzern im System entfernt werden.</p>
EOF;

$l['helpPage']['mngradprofiles'] .= $l['helpPage']['mngradprofilesnew'] . $l['helpPage']['mngradprofileslist']
                                  . $l['helpPage']['mngradprofilesedit'] . $l['helpPage']['mngradprofilesduplicate']
                                  . $l['helpPage']['mngradprofilesdel'];

// group check/reply help pages
$l['helpPage']['mngradgroupchecknew'] = <<<EOF
<h2 class="fs-6">Neues Group-Reply/Check-Mapping hinzufügen</h2>
<p>Erstellen Sie mit der intuitiven Benutzeroberfläche ganz einfach ein neues Group-Reply/Check-Mapping.</p>
EOF;
$l['helpPage']['mngradgroupreplynew'] = $l['helpPage']['mngradgroupchecknew'];

$l['helpPage']['mngradgroupchecklist'] = <<<EOF
<h2 class="fs-6">Group-Reply/Check-Mappings auflisten</h2>
<p>Zeigen Sie schnell eine Liste aller vorhandenen Group-Reply/Check-Mappings.</p>
EOF;
$l['helpPage']['mngradgroupreplylist'] = $l['helpPage']['mngradgroupchecklist'];

$l['helpPage']['mngradgroupchecksearch'] = <<<EOF
<h2 class="fs-6">Group-Reply/Check-Mappings suchen</h2>
<p>Suchen Sie nach bestimmten Group-Reply/Check-Mappings mithilfe des Namens, Attributs oder Werts. Ein Platzhalterzeichen (Wildcard) wird automatisch als Suffix an den Suchtext angehängt, um die Suchergebnisse zu verfeinern.</p>
EOF;
$l['helpPage']['mngradgroupreplysearch'] = $l['helpPage']['mngradgroupchecksearch'];

$l['helpPage']['mngradgroupcheckedit'] = <<<EOF
<h2 class="fs-6">Group-Reply/Check-Mappings bearbeiten</h2>
<p>Nehmen Sie Änderungen an bestehenden Group-Reply/Check-Mappings vor, um sicherzustellen, dass Ihr Netzwerk effizient arbeitet.</p>
EOF;
$l['helpPage']['mngradgroupreplyedit'] = $l['helpPage']['mngradgroupcheckedit'];

$l['helpPage']['mngradgroupcheckdel'] = <<<EOF
<h2 class="fs-6">Group-Reply/Check-Mappings löschen</h2>
<p>Entfernen Sie unnötige Group-Reply/Check-Mappings, um Ihre Datenbank aktuell und organisiert zu halten.</p>
EOF;
$l['helpPage']['mngradgroupreplydel'] = $l['helpPage']['mngradgroupcheckdel'];

$l['helpPage']['mngradgroups'] = <<<EOF
<h1 class="fs-5">Gruppen-Management</h1>
<p>Verwalten Sie effizient Group-Reply- und Group-Check-Mappings in den Tabellen radgroupreply und radgroupcheck.</p>
EOF;
$l['helpPage']['mngradgroups'] .= $l['helpPage']['mngradgroupreplynew'] . $l['helpPage']['mngradgroupreplylist']
                                . $l['helpPage']['mngradgroupchecksearch'] . $l['helpPage']['mngradgroupcheckedit']
                                . $l['helpPage']['mngradgroupcheckdel'];

// ip pool help pages
$l['helpPage']['mngradippoolnew'] = <<<EOF
<h2 class="fs-6">Neuer IP-Pool</h2>
<p>Fügen Sie eine neue IP-Adresse zu einem bereits konfigurierten IP-Pool hinzu.</p>
EOF;
$l['helpPage']['mngradippoollist'] = <<<EOF
<h2 class="fs-6">IP-Pools auflisten</h2>
<p>Listen Sie alle konfigurierten IP-Pools und deren zugewiesene IP-Adressen auf.</p>
EOF;
$l['helpPage']['mngradippooledit'] = <<<EOF
<h2 class="fs-6">IP-Pool bearbeiten</h2>
<p>Bearbeiten Sie eine IP-Adresse in einem bereits konfigurierten IP-Pool.</p>
EOF;
$l['helpPage']['mngradippooldel'] = <<<EOF
<h2 class="fs-6">IP-Pool entfernen</h2>
<p>Entfernen Sie eine IP-Adresse aus einem bereits konfigurierten IP-Pool.</p>
EOF;

$l['helpPage']['mngradippool'] = <<<EOF
<h1 class="fs-5">IP-Pool-Management</h1>
<p>IP-Pools sind Gruppen von IP-Adressen, die verschiedenen Geräten, virtuellen Maschinen oder Anwendungen innerhalb eines Netzwerks zugewiesen werden können. Das Management von IP-Pools ist wichtig, um sicherzustellen, dass ausreichend IP-Adressen für alle Geräte verfügbar sind, die diese benötigen, und gleichzeitig die Verwendung von doppelten oder ungültigen IP-Adressen zu vermeiden.</p>
EOF;

$l['helpPage']['mngradippool'] .= $l['helpPage']['mngradippoolnew'] . $l['helpPage']['mngradippoollist']
                                . $l['helpPage']['mngradippooledit'] . $l['helpPage']['mngradippooldel'];

// nas help pages
$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "Um einen NAS-IP- oder Host-Eintrag aus der Datenbank zu entfernen, müssen Sie die IP-Adresse/den Host des Kontos angeben.";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

// huntgroup help pages
$l['helpPage']['mngradhunt'] = <<<EOF
<p>Bevor Sie mit HuntGroup arbeiten, lesen Sie bitte das <a href="https://wiki.freeradius.org/guide/SQL-Huntgroup-HOWTO" target="_blank">SQL_Huntgroup_HOWTO</a> im FreeRADIUS-Wiki.</p>
<p>Insbesondere:</p>
<p><i>Suchen Sie den Abschnitt "authorize" in Ihrer radiusd.conf oder in der Datei sites-enabled/default und bearbeiten Sie diesen. Fügen Sie am Anfang des authorize-Abschnitts nach dem preprocess-Modul folgende Zeilen ein:</i></p>
<pre>
update request {
    Huntgroup-Name := "%{sql:select groupname from radhuntgroup where nasipaddress=\"%{NAS-IP-Address}\"}"
}
</pre>
<p><i>Diese Anweisung führt eine Abfrage in der radhuntgroup Tabelle durch, wobei die IP-Adresse als Key verwendet wird, um den HuntGroup-Namen zurückzugeben. Dadurch wird ein Attribut/Wert-Paar zur Anfrage hinzugefügt, wobei der Name des Attributs "HuntGroup-Name" ist und dessen Wert das Ergebnis der SQL-Abfrage. Falls die Abfrage kein Ergebnis liefert, ist der Wert eine leere Zeichenkette.</i></p>
EOF;


$l['helpPage']['mngradhuntdel'] = "Um einen HuntGroup-Eintrag aus der Datenbank zu entfernen, müssen Sie die IP-Adresse/den Host und die Port-ID der HuntGroup angeben.";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

// hotspots help pages
$l['helpPage']['mnghsdel'] = "Um einen Hotspot aus der Datenbank zu entfernen, müssen Sie den Namen des Hotspots angeben";
$l['helpPage']['mnghsedit'] = "Sie können die folgenden Details für den Hotspot bearbeiten:";
$l['helpPage']['mnghsnew'] = "Sie können untenstehende Angaben für die Aufnahme eines neuen Hotspots in die Datenbank ausfüllen";
$l['helpPage']['mnghslist'] = "Liste aller Hotspots in der Datenbank. Sie können die Schnellzugriff-Links verwenden, um einen Hotspot in der Datenbank zu bearbeiten oder zu löschen.";


$l['helpPage']['configuser'] = <<<EOF
<h2 class="fs-6">Benutzer-Einstellungen</h2>
<p>Legen Sie fest, ob Klartext-Passwörter in der Datenbank erlaubt sind und welche Zeichen für die zufällige Erstellung von Passwörtern und/oder Benutzernamen zugelassen sind.</p>
EOF;

$l['helpPage']['configdb_short'] = <<<EOF
<h2 class="fs-6">Datenbank-Einstellungen</h2>
<p>Konfigurieren Sie die Datenbank-Engine, Verbindungsparameter und Tabellennamen, falls nicht die Standardwerte verwendet werden.</p>
EOF;

$l['helpPage']['configdb'] = $l['helpPage']['configdb_short'];
$l['helpPage']['configdb'] .= <<<EOF
<h3 class="fs-6">Globale Einstellungen</h3>
<p>Wählen Sie die Datenbank Storage-Engine</p>
<h3 class="fs-6">Tabellen-Einstellungen</h3>
<p>Falls nicht das Standard-FreeRADIUS-Schema verwendet wird, können Sie die Tabellennamen ändern</p>
EOF;

$l['helpPage']['configlang'] = <<<EOF
<h2 class="fs-6">Sprach-Einstellungen</h2>
<p>Konfigurieren Sie die Sprache der Benutzeroberfläche.</p>
EOF;

$l['helpPage']['configcrontab'] = <<<EOF
<p>In diesem Abschnitt können Sie verschiedene Überwachungs- und Benachrichtigungsfunktionen in Bezug auf Sessions und Traffic im System konfigurieren.<br>
Hier können Sie Parameter wie Intervalle für die Erkennung Stale-Sessions, Einstellungen für die Node-Überwachung, Schwellenwerte für den Benutzer-Traffic
und E-Mail-Benachrichtigungskonfigurationen anpassen. Der Abschnitt ist in Registerkarten unterteilt, die sich jeweils auf einen bestimmten Aspekt der Überwachungs-
und Benachrichtigungsfunktionen des Systems konzentrieren. Insbesondere können Sie Prüfungen aktivieren oder deaktivieren, Schwellenwerte festlegen und E-Mail Empfänger für Benachrichtigungen konfigurieren.
Zusätzlich gibt es eine Registerkarte zur Anzeige der Ausgabe der Crontab-Konfiguration des Systems,
die Einblicke in geplante Aufgaben im Zusammenhang mit Überwachung und Wartung bietet.</p>

<h3 class="fs-6">Stale-Sessions</h3>
<p>Intervall und Grace-Periode werden verwendet, um den Zeit-Schwellwert zu berechnen. Es ist wichtig sicherzustellen, dass der 
Zeit-Schwellwert entsprechend dem <strong>Acct-Interim-Intervall</strong> eingestellt wird, insbesondere muss er größer als dieses 
sein, um eine vorzeitige Beendigung der Session zu vermeiden.</p>
EOF;

$l['helpPage']['configlogging'] = <<<EOF
<h2 class="fs-6">daloRADIUS-Konfigurationsformular</h2>
<p>In diesem Abschnitt können Sie die Logging-Einstellungen für daloRADIUS und andere Logging-Optionen konfigurieren.</p>
<h2 class="fs-6">daloRADIUS Logging-Einstellungen</h2>
<ul>
    <li><strong>{$l['all']['PagesLogging']}:</strong> Aktivieren oder Deaktivieren des Loggings von Seitenaufrufen.</li>
    <li><strong>{$l['all']['QueriesLogging']}:</strong> Aktivieren oder Deaktivieren des Loggings von Queries (Abfragen).</li>
    <li><strong>{$l['all']['ActionsLogging']}:</strong> Aktivieren oder Deaktivieren des Loggings von Aktionen, wie z. B. Formularübermittlungen.</li>
    <li><strong>{$l['all']['LoggingDebugInfo']}:</strong> Aktivieren oder Deaktivieren des Loggings von Debug-Informationen.</li>
    <li><strong>{$l['all']['LoggingDebugOnPages']}:</strong> Aktivieren oder Deaktivieren des Loggings von Debug-Informationen speziell auf Seiten.</li>
    <li><strong>daloRADIUS {$l['all']['FilenameLogging']}:</strong> Geben Sie den absoluten Pfad zur Log-Datei an.<br>
        Beispiel: <code>/var/www/daloradius/var/log/daloradius.log</code></li>
</ul>
<h2 class="fs-6">Weitere Logging-Einstellungen</h2>
<ul>
    <li><strong>SYSLOG {$l['all']['FilenameLogging']}:</strong> Geben Sie den absoluten Pfad zur SYSLOG Datei an.<br>
        Beispiel: <code>/var/log/syslog</code></li>
    <li><strong>RADIUSLOG {$l['all']['FilenameLogging']}:</strong> Geben Sie den absoluten Pfad zur RADIUSLOG Datei an.
        Beispiel: <code>/var/log/freeradius/radius.log</code></li>
    <li><strong>BOOTLOG {$l['all']['FilenameLogging']}:</strong> Geben Sie den absoluten Pfad zur BOOTLOG Datei an.
        Beispiel: <code>/var/log/boot.log</code></li>
</ul>
EOF;

$l['helpPage']['configinterface'] = <<<EOF
<h2 class="fs-6">Interface-Einstellungen</h2>
<p>Konfigurieren Sie das Layout und das Verhalten der Benutzeroberfläche.</p>
EOF;

$l['helpPage']['configmail'] = <<<EOF
<h2 class="fs-6">E-Mail Einstellungen</h2>
<div id="help-text">
  <div class="help-item">
    <strong>Aktiviert:</strong>
    <p>Wählen Sie, ob der SMTP-Client für das Senden von E-Mails aktiviert oder deaktiviert sein soll.</p>
  </div>

  <div class="help-item">
    <strong>SMTP-Server Adresse:</strong>
    <p>Geben Sie die Adresse Ihres SMTP-Servers ein. <br>Dies ist der Server der für das Senden Ihrer E-Mails zuständig ist.</p>
  </div>

  <div class="help-item">
    <strong>SMTP-Server Port:</strong>
    <p>Geben Sie die Port-Nummer an die vom SMTP-Server verwendet wird. Standardmäßig ist das Port 25.</p>
  </div>

  <div class="help-item">
    <strong>SMTP-Sicherheit:</strong>
    <p>Wählen Sie das Sicherheitsprotokoll für die SMTP-Verbindung. <br>Wählen Sie 'none' für keine Sicherheit oder 'tls' für TLS-Verschlüsselung.</p>
  </div>

  <div class="help-item">
    <strong>Absender Email-Adresse (from):</strong>
    <p>Geben Sie die E-Mail-Adresse an die als Absender der E-Mails verwendet wird.</p>
  </div>

  <div class="help-item">
    <strong>Name des Absenders:</strong>
    <p>Geben Sie den Namen ein der mit der Absender-E-Mail-Adresse verknüpft ist. <br>Verwenden Sie nur Buchstaben, Zahlen und Leerzeichen.</p>
  </div>

  <div class="help-item">
    <strong>Betreff-Präfix:</strong>
    <p>Legen Sie ein Präfix für die Betreffzeilen der E-Mails fest. <br>Erlaubte Zeichen sind Buchstaben, Zahlen, Leerzeichen und eckige Klammern.</p>
  </div>

  <div class="help-item">
    <strong>SMTP-Benutzername und Passwort:</strong>
    <p>Geben Sie den Benutzernamen und das Passwort für die SMTP-Authentifizierung ein, falls erforderlich.<br>Lassen Sie beide Felder leer, um die Authentifizierung zu überspringen.</p>
  </div>

  <small><strong>Hinweis:</strong> Klicken Sie nach dem Vornehmen von Änderungen auf "Übernehmen", um Ihre Konfiguration zu speichern.</small>
</div>
EOF;

$l['helpPage']['configmaint'] = <<<EOF
<h1 class="fs-5">Wartung</h1>
<h6>Benutzerverbindung testen</h6>
<p>Senden Sie einen Access-Request an den RADIUS-Server, um zu überprüfen, ob die Anmeldedaten eines Benutzers gültig sind.</p>
<h6>Benutzer trennen</h6>
<p>Senden Sie ein PoD (Packet of Disconnect) oder CoA (Change of Authority) an den NAS-Server, um einen Benutzer zu trennen und dessen Session auf einem bestimmten NAS zu beenden.</p>
EOF;

$l['helpPage']['configmainttestuser'] = <<<EOF
<h1 class="fs-5">Benutzerverbindung testen</h1>
<p>Senden Sie einen Access-Request an den RADIUS-Server, um zu überprüfen, ob die Anmeldedaten eines Benutzers gültig sind.</p>
<p>daloRADIUS verwendet radclient um den Test durchzuführen und gibt die Ergebnisse des Befehls zurück nachdem dieser abgeschlossen wurde.</p>
<p>daloRADIUS setzt voraus, dass radclient in Ihrer <code>\$PATH</code>-Umgebungsvariable verfügbar ist. Falls dies nicht der Fall ist, nehmen Sie bitte die notwendigen Korrekturen in der Datei <code>library/extensions/maintenance_radclient.php</code> vor.</p>
<p>Bitte beachten Sie, dass der Test aufgrund von Fehlern und erneuten Übertragungen durch radclient einige Zeit in Anspruch nehmen kann (bis zu 10-20 Sekunden).</p>
<p>Im Reiter "Erweitert" können Sie die Optionen für den Test feinabstimmen:</p>
<ul>
<li>Timeout - Warte 't' Sekunden, bevor ein erneuter Versuch gestartet wird (kann eine Fließkommazahl sein)</li>
<li>Wiederholungsversuche - Falls ein Timeout auftritt, sende das Paket 'x'-mal erneut</li>
<li>Anzahl - Sende jedes Paket 'y'-mal</li>
<li>Anfragen - Sende 'z' Pakete parallel aus einer Datei</li>
</ul>
EOF;

$l['helpPage']['configmaintdisconnectuser'] = <<<EOF
<h1 class="fs-5">Benutzer trennen</h1>
<p>Senden Sie ein PoD (Packet of Disconnect) oder CoA (Change of Authority) an den NAS-Server, um einen Benutzer zu trennen und dessen Session auf einem bestimmten NAS zu beenden.</p>
<p>Um die Session eines Benutzers zu beenden, muss der NAS PoD- oder CoA-Pakettypen unterstützen. Bitte konsultieren Sie hierzu Ihren NAS-Hersteller oder die Dokumentation. Zudem müssen die NAS-Ports für PoD- oder CoA-Pakete bekannt sein. Neuere NAS-Geräte verwenden in der Regel Port 3799, während ältere Geräte oft auf Port 1700 konfiguriert sind.</p>
<p>daloRADIUS verwendet radclient, um den Vorgang durchzuführen und gibt die Ergebnisse des Befehls zurück, nachdem dieser abgeschlossen wurde.</p>
<p>daloRADIUS setzt voraus, dass radclient in Ihrer <code>\$PATH</code>-Umgebungsvariable verfügbar ist. Falls dies nicht der Fall ist, nehmen Sie bitte die notwendigen Korrekturen in der Datei <code>library/extensions/maintenance_radclient.php</code> vor.</p>
<p>Bitte beachten Sie, dass der Vorgang aufgrund von Fehlern und erneuten Übertragungen durch radclient einige Zeit in Anspruch nehmen kann (bis zu 10-20 Sekunden).</p>
<p>Im Reiter "Erweitert" können Sie die Optionen für den Vorgang feinabstimmen:</p>
<ul>
<li>Timeout - Warte 't' Sekunden, bevor ein erneuter Versuch gestartet wird (kann eine Fließkommazahl sein)</li>
<li>Wiederholungsversuche - Falls ein Timeout auftritt, sende das Paket 'x'-mal erneut</li>
<li>Anzahl - Sende jedes Paket 'y'-mal</li>
<li>Anfragen - Sende 'z' Pakete parallel aus einer Datei</li>
</ul>
EOF;

$l['helpPage']['configoperators'] = <<<EOF
<h1 class="fs-5">Operatoren</h1>
<p>Konfigurieren Sie die Einstellungen und das Verhalten von Operatoren.</p>
EOF;

$l['helpPage']['configoperatorsdel'] = "Um einen Operator aus der Datenbank zu entfernen, müssen Sie den Benutzernamen angeben.";
$l['helpPage']['configoperatorsedit'] = "Bearbeiten Sie die Benutzerdetails des Operators unten:";
$l['helpPage']['configoperatorsnew'] = "Geben Sie unten die Details für einen neuen Operator ein, der zur Datenbank hinzugefügt werden soll:";
$l['helpPage']['configoperatorslist'] = "Liste aller Operatoren in der Datenbank";

$l['helpPage']['configbackup'] = <<<EOF
<h1 class="fs-5">Backup</h1>
<p>Datenbank-Backups verwalten</p>
EOF;
$l['helpPage']['configbackupcreatebackups'] = "Backups erstellen";
$l['helpPage']['configbackupmanagebackups'] = "Backups verwalten";

$l['helpPage']['configmain'] = <<<EOF
<h1 class="fs-5">Globale Einstellungen</h1>
EOF;
$l['helpPage']['configmain'] .= $l['helpPage']['configuser'] . $l['helpPage']['configdb_short']
                              . $l['helpPage']['configlang'] . $l['helpPage']['configlogging']
                              . $l['helpPage']['configinterface'] . $l['helpPage']['configmail']
                              . $l['helpPage']['configmaint'] . $l['helpPage']['configoperators']
                              . $l['helpPage']['configbackup'];

// graphs help pages
$l['helpPage']['graphsalltimelogins'] = <<<EOF
<h2 class="fs-6">Gesamt-Logins/Aufrufe</h2>
<p>Erstellt ein Diagramm, das die Anzahl der Logins auf dem Server über einen bestimmten Zeitraum anzeigt.</p>
EOF;
$l['helpPage']['graphsoveralldownload'] = <<<EOF
<h2 class="fs-6">Gesamt-Download-Statistiken</h2>
<p>Erstellt ein Diagramm, das die Menge der von einem bestimmten Benutzer über einen bestimmten Zeitraum heruntergeladenen Daten anzeigt. Das Diagramm wird von einer tabellarischen Auflistung begleitet.</p>
EOF;
$l['helpPage']['graphsoverallupload'] = <<<EOF
<h2 class="fs-6">Gesamt-Upload-Statistiken</h2>
<p>Erstellt ein Diagramm, das die Menge der von einem bestimmten Benutzer über einen bestimmten Zeitraum hochgeladenen Daten anzeigt. Das Diagramm wird von einer tabellarischen Auflistung begleitet.</p>
EOF;
$l['helpPage']['graphsoveralllogins'] = <<<EOF
<h2 class="fs-6">Gesamt-Logins/Aufrufe</h2>
<p>Erstellt ein Diagramm, das die Nutzung eines bestimmten Benutzers über einen bestimmten Zeitraum anzeigt. Das Diagramm zeigt die Anzahl der Logins (oder 'Aufrufe' des NAS) und wird von einer tabellarischen Auflistung begleitet.</p>
EOF;
$l['helpPage']['graphsalltimetrafficcompare'] = <<<EOF
<h2 class="fs-6">Gesamt-Datenverkehrsvergleich</h2>
<p>Erstellt ein Diagramm, das die Menge der heruntergeladenen und hochgeladenen Daten über einen bestimmten Zeitraum vergleicht.</p>
EOF;
$l['helpPage']['graphsloggedusers'] = <<<EOF
<h2 class="fs-6">Angemeldete Benutzer</h2>
<p>Erstellt ein Diagramm, das die Anzahl der angemeldeten Benutzer über einen bestimmten Zeitraum anzeigt. Benutzer können nach Tag, Monat und Jahr gefiltert werden, um ein stundenweises Diagramm zu erstellen, oder nur nach Monat und Jahr (wählen Sie "―" im Tagesfeld), um die minimale und maximale Anzahl der angemeldeten Benutzer über den ausgewählten Monat grafisch darzustellen.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Graphs</h1>'
                            . $l['helpPage']['graphsoveralllogins'] . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'] . $l['helpPage']['graphsoveralllogins']
                            . $l['helpPage']['graphsalltimetrafficcompare'] . $l['helpPage']['graphsloggedusers'];


$l['helpPage']['rephistory'] = "Listet alle Aktivitäten auf, die an Management-Elementen durchgeführt wurden, und bietet Informationen <br>
zu den Feldern Erstellt von, Erstellungsdatum sowie Aktualisierungsdatum und Aktualisiert von";
$l['helpPage']['replastconnect'] = "Listet alle Anmeldeversuche am RADIUS-Server auf, sowohl Erfolgreiche als auch Fehlgeschlagene";
$l['helpPage']['replogsboot'] = <<<EOF
Der <strong>{$l['Intro']['replogsboot.php']}</strong> ermöglicht die Überwachung des Betriebssystem-Boot-Logs, was dem Ausführen des <kbd>dmesg</kbd>-Befehls entspricht.
Sie können die Ansicht anpassen, indem Sie die Anzahl der Zeilen angeben und Filter anwenden, um die Ergebnisse zu verfeinern.
EOF;
$l['helpPage']['replogsdaloradius'] = <<<EOF
Der <strong>{$l['Intro']['replogsdaloradius.php']}</strong> ermöglicht die Überwachung der Logs von daloRADIUS.
Passen Sie die Zeilenanzahl an und filtern Sie die Logs nach Queries, Notices, Inserts, oder Selects.
EOF;
$l['helpPage']['replogsradius'] = <<<EOF
Der <strong>{$l['Intro']['replogsradius.php']}</strong> ermöglicht die Überwachung der Event-Logs von freeRADIUS,
mit anpassbarer Zeilenanzahl und verfügbaren Filtern für Nachrichtentypen wie 'Auth', 'Info' oder 'Error'.
EOF;
$l['helpPage']['replogssystem'] = <<<EOF
Der <strong>{$l['Intro']['replogssystem.php']}</strong> ermöglicht die Überwachung von System-Logs, wie syslog und messages,
mit anpassbarer Zeilenanzahl und optionalen Filtern zur Verfeinerung Ihrer Ansicht.
EOF;
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "Zeigt Details zu einem bestimmten Batch an";

$l['helpPage']['replogs'] = <<<EOF
<h1 class="fs-5">Logs</h1>
<p>{$l['helpPage']['replogsdaloradius']}</p>
<p>{$l['helpPage']['replogsradius']}</p>
<p>{$l['helpPage']['replogssystem']}</p>
<p>{$l['helpPage']['replogsboot']}</p>
EOF;

$l['helpPage']['repmain'] = <<<EOF
<h1 class="fs-5">Allgemeine Berichte</h1>
<h2 class="fs-6">Online-Benutzer</h2>
<p>Zeigt eine Liste aller sich derzeit online befindlichen Benutzer an, indem die Accounting-Tabelle in der Datenbank überprüft wird. Die Überprüfung erfolgt für Benutzer, bei denen keine Endzeit (AcctStopTime) gesetzt ist. Es ist wichtig zu beachten, dass einige dieser Benutzer Stale-Sessions haben könnten, da NAS-Fehler beim Senden von Accounting-Stop-Paketen aufgetreten sind. Beachten Sie, dass dieser Reiter nur sichtbar ist, wenn es Benutzer gibt die online sind.</p>
<h2 class="fs-6">Letzte Verbindungsversuche</h2>
<p>Zeigt eine Liste aller Access-Accept- und Access-Reject-Logins (Erfolgreiche und Fehlgeschlagene) für Benutzer an. Diese werden aus der postauth-Tabelle der Datenbank abgerufen, die in der Konfigurationsdatei von FreeRADIUS definiert sein muss, um das Logging zu aktivieren.</p>
<h2 class="fs-6">Top-Benutzer</h2>
<p>Zeigt eine Liste der Top-N-Benutzer nach Bandbreitenverbrauch und genutzter Session-Zeit an.</p>
<h1 class="fs-5">Unterkategorien-Berichte</h1>
<h2 class="fs-6">Logs</h2>
<p>Bietet Zugriff auf die Logs von daloRADIUS, FreeRADIUS, des Systems und der Boot-Logs.</p>
<h2 class="fs-6">Status</h2>
<p>Bietet Informationen zum Serverstatus und zum Status der RADIUS-Komponenten.</p>
EOF;
$l['helpPage']['repstatradius'] = "Bietet allgemeine Informationen über den FreeRADIUS-Daemon und den MySQL/MariaDB-Datenbankserver";
$l['helpPage']['repstatserver'] = "Bietet allgemeine Informationen über den Server selbst: CPU-Auslastung, Prozesse, Betriebszeit, Speichernutzung usw.";
$l['helpPage']['repstatus'] = <<<EOF
<h1 class="fs-5">Status</h1>
<h2 class="fs-6">Serverstatus</h2>
<p>Zeigt allgemeine Informationen über den Server an, einschließlich CPU-Auslastung, Anzahl der laufenden Prozesse, Betriebszeit, Speichernutzung und mehr.</p>
<h2 class="fs-6">RADIUS-Status</h2>
<p>Zeigt allgemeine Informationen über den FreeRADIUS-Daemon und den MySQL-Datenbankserver an.</p>
EOF;
$l['helpPage']['reptopusers'] = "Datensätze der Top-Benutzer, die unten aufgelistet sind, haben den höchsten Verbrauch an Session-Zeit oder Bandbreitennutzung. Auflistung der Benutzer der Kategorie: ";
$l['helpPage']['repusername'] = "Gefundene Datensätze für Benutzer:";
$l['helpPage']['reponline'] = "Die folgende Tabelle listet Benutzer auf, die derzeit mit dem System verbunden sind. Es ist durchaus möglich, dass es Stale-Sessions gibt, was bedeutet, dass Benutzer getrennt wurden, der NAS jedoch kein STOP-Accounting-Paket an den RADIUS-Server gesendet hat oder senden konnte.";


$l['helpPage']['mnglistall'] = "Liste der Benutzer in der Datenbank";
$l['helpPage']['mngsearch'] = "Suche nach Benutzer: ";
$l['helpPage']['mngnew'] = "Geben Sie unten die Details für einen neuen Benutzer ein, der zur Datenbank hinzugefügt werden soll:<br/>";
$l['helpPage']['mngedit'] = "Bearbeiten Sie die Benutzerdetails unten:<br/>";
$l['helpPage']['mngdel'] = "Um einen Benutzereintrag aus der Datenbank zu entfernen, müssen Sie den Benutzernamen des Kontos angeben:<br/>";
$l['helpPage']['mngbatch'] = "Geben Sie unten die Details für neue Benutzer ein, die zur Datenbank hinzugefügt werden sollen.<br/>
Beachten Sie, dass diese Einstellungen für alle Benutzer gelten, die Sie erstellen.<br/>";
$l['helpPage']['mngnewquick'] = "Der folgende Benutzer/die folgende Karte ist vom Typ Prepaid.<br/>
Die in der Zeitgutschrift angegebene Zeit wird als Session-Timeout und Max-All-Session 
RADIUS-Attribute verwendet.";

// accounting section
$l['helpPage']['acctusername'] = <<<EOF
<h2 class="fs-6">Benutzer-Accounting</h2>
<p>Bietet detaillierte Accounting-Informationen für alle Sessions in der Datenbank, die einem bestimmten Benutzer zugeordnet sind.</p>
EOF;

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Datum-Accounting</h2>
<p>Bietet detaillierte Accounting-Informationen für alle Sessions zwischen zwei angegebenen Daten für einen bestimmten Benutzer.</p>
EOF;

$l['helpPage']['acctipaddress'] = <<<EOF
<h2 class="fs-6">IP-Accounting</h2>
<p>Bietet detaillierte Accounting-Informationen für alle Sessions, die von einer bestimmten IP-Adresse stammen.</p>
EOF;

$l['helpPage']['acctnasipaddress'] = <<<EOF
<h2 class="fs-6">NAS-Accounting</h2>
<p>Bietet detaillierte Accounting-Informationen für alle Sessions, die von einer bestimmten NAS-IP-Adresse bearbeitet wurden.</p>
EOF;

$l['helpPage']['acctactive'] = <<<EOF
<h2 class="fs-6">Aktive Accounting-Datensätze</h2>
<p>Bietet Informationen, die für die Nachverfolgung aktiver oder abgelaufener Benutzer in der Datenbank nützlich sind, wie z. B. Benutzer mit einem Expiration- oder einem Max-All-Session-Attribut.</p>
EOF;

$l['helpPage']['acctall'] = <<<EOF
<h2 class="fs-6">Alle Accounting-Datensätze</h2>
<p>Bietet detaillierte Accounting-Informationen für alle Sessions in der Datenbank.</p>
EOF;

$l['helpPage']['acctcustom_short'] = <<<EOF
<h1 class="fs-5">Benutzerdefinierte Abfrage</h1>
<p>Bietet die flexibelste benutzerdefinierte Abfrage zur Ausführung auf der Datenbank. Sie können die Abfrageeinstellungen in der linken Seitenleiste nach Bedarf anpassen.</p>
EOF;


$l['helpPage']['acctcustom'] = <<<EOF
<h2 class="fs-6">Benutzerdefinierte Abfrage</h2>
<p>Diese Funktion ermöglicht hochgradig anpassbare Abfragen auf der Datenbank und gibt Operatoren die Möglichkeit, die Datenabfrage präzise an ihre Bedürfnisse anzupassen. Sie können die Abfrageeinstellungen in der linken Seitenleiste optimieren, um die Datenextraktion zu verbessern.</p>
<h2 class="fs-6">Start- und Enddatum</h2>
<p>Geben Sie das Start- und Enddatum an, um Daten innerhalb eines bestimmten Zeitraums abzurufen.</p>
<h2 class="fs-6">Bedingung</h2>
<p>Definieren Sie das Datenbankfeld für Abgleichszwecke, das als Schlüssel fungiert. Wählen Sie zwischen exaktem Abgleich ("equals") oder teilweisem Abgleich ("contains") mithilfe des jeweiligen Operators. Vermeiden Sie die Verwendung von Platzhaltern wie "*", da der eingegebene Wert automatisch für die Suche formatiert wird (z. B. *Wert* oder %Wert%).</p>
<h2 class="fs-6">Abfrage-Accounting-Felder</h2>
<p>Wählen Sie aus, welche Felder in der resultierenden Liste enthalten sein sollen.</p>
<h2 class="fs-6">Sortieren nach und Sortierart</h2>
<p>Geben Sie das Feld an, nach dem Sie die Ergebnisse sortieren möchten, und wählen Sie zwischen aufsteigender oder absteigender Reihenfolge.</p>
EOF;

$l['helpPage']['acctcustomquery'] = $l['helpPage']['acctcustom'];


$l['helpPage']['acctplans'] = "";
$l['helpPage']['acctmain'] = '<h1 class="fs-5">Allgemeines Accounting</h1>'
                           . $l['helpPage']['acctusername'] . $l['helpPage']['acctdate']
                           . $l['helpPage']['acctipaddress'] . $l['helpPage']['acctnasipaddress']
                           . $l['helpPage']['acctall'] . $l['helpPage']['acctactive']
                           . $l['helpPage']['acctcustom_short'] . <<<EOF
<h1 class="fs-5">Hotspots</h1>
<p>Bietet Informationen zu verschiedenen verwalteten Hotspots, Vergleichen und anderen nützlichen Informationen.</p>
EOF;



// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Bietet vollständige Accounting-Informationen für alle Sessions, die von diesem spezifischen Hotspot stammen.
    Diese Liste wird erstellt, indem nur die Datensätze in der radacct-Tabelle mit dem Feld CalledStationId aufgelistet werden,
    das mit einem MAC-Adress-Eintrag eines Hotspots in der Hotspot-Management Datenbank übereinstimmt.
<br/>
";
$l['helpPage']['accthotspotcompare'] = <<<EOF
<h1 class="fs-5">Hotspot-Vergleich</h1>
<h2 class="fs-6">Grundlegende Informationen</h2>
<p>Dieser Abschnitt bietet grundlegende Accounting-Informationen zum Vergleich aller aktiven Hotspots, die in der Datenbank gefunden wurden. Folgende Accounting-Informationen sind enthalten:
<ul>
<li>Hotspot-Name: Der Name des Hotspots</li>
<li>Einzigartige Benutzer: Die Anzahl der Benutzer, die sich nur über diesen Hotspot angemeldet haben</li>
<li>Gesamtaufrufe: Die Gesamtzahl der Anmeldungen, die von diesem Hotspot aus durchgeführt wurden (Einzigartige und nicht-Einzigartige)</li>
<li>Durchschnittliche Zeit: Die durchschnittliche Zeit, die ein Benutzer an diesem Hotspot verbracht hat</li>
<li>Gesamtzeit: Die akkumulierte Zeit, die alle Benutzer an diesem Hotspot verbracht haben</li>
</ul>
</p>
<h2 class="fs-6">Grafiken</h2>
<p>
Dieser Abschnitt bietet grafische Vergleiche für die verschiedenen Hotspots. Folgende Grafiken sind verfügbar:
<ul>
<li>Verteilung der einzigartigen Benutzer pro Hotspot</li>
<li>Verteilung der Aufrufe pro Hotspot</li>
<li>Verteilung der Nutzungszeit pro Hotspot</li>
</ul>
</p>
EOF;
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot-Accounting</b></h200> –
    Bietet vollständige Accounting-Informationen für alle Sessions, die von diesem spezifischen Hotspot stammen.
<br/>
<h200><b>Hotspot-Vergleich</b></h200> –
    Bietet grundlegende Accounting-Informationen zum Vergleich aller aktiven Hotspots, die in der Datenbank gefunden wurden.
    Zeigt grafische Darstellungen verschiedener Vergleiche an.
<br/>
";

$l['helpPage']['acctmaintenance'] = <<<EOF
<h2 class="fs-6">Stale-Sessions bereinigen</h2>
<p>Stale-Sessions entstehen oft, wenn der NAS kein Accounting-STOP-Protokoll für die Benutzer-Session liefern kann. Dies führt zu einer offenen Stale-Session in den Accounting-Datensätzen, die einen falschen Eintrag eines angemeldeten Benutzers vortäuscht und zu falsch-positiven Ergebnissen führt.</p>
<h2 class="fs-6">Accounting-Datensätze löschen</h2>
<p>Auf dieser Seite können Accounting-Datensätze aus der Datenbank gelöscht werden. Es wird empfohlen, nur überwachten Administratoren den Zugriff auf diese Seite zu gestatten, da diese Aktion ohne sorgfältige Überlegung unklug sein könnte.</p>
EOF;
$l['helpPage']['acctmaintenancecleanup'] = <<<EOF
<h2 class="fs-6">Stale-Sessions bereinigen</h2>
<p>Diese Funktion dient dazu, Stale-Sessions zu bereinigen, die in FreeRADIUS (und somit auch in daloRADIUS) weiterhin als aktiv markiert sind, obwohl der Benutzer nicht mehr mit dem NAS verbunden ist. Stale-Sessions können auftreten, wenn der NAS kein Accounting-STOP-Protokoll liefert, was zu falsch-positiven Einträgen angemeldeter Benutzer führt.</p>
<p>Es gibt zwei Möglichkeiten, Stale-Sessions zu bereinigen:
<ul>
<li>Bereinigung nach Benutzername: Diese Option <b>schließt</b> alle offenen Sessions für einen bestimmten Benutzernamen in der FreeRADIUS-Datenbank. Verwenden Sie diese Option mit Vorsicht!</li>
<li>Bereinigung nach Datum: Diese Option <b>löscht</b> alle offenen Sessions, die älter als ein bestimmtes Datum in der FreeRADIUS-Datenbank sind. Verwenden Sie auch diese Option mit Vorsicht!</li>
</ul>
</p>
EOF;
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = <<<EOF
<h1 class="fs-5">Karte bearbeiten</h1>
<p>In diesem Modus können Sie Hotspots durch Klicken auf die Karte hinzufügen oder durch Klicken auf ein Hotspot-Symbol entfernen.</p>
<h2 class="fs-6">Hotspot hinzufügen</h2>
<p>Um einen Hotspot hinzuzufügen, klicken Sie auf eine freie Stelle auf der Karte. Sie werden aufgefordert, den Namen und die MAC-Adresse des Hotspots anzugeben. Diese Angaben sind entscheidend, um den Hotspot in der Accounting-Tabelle zu identifizieren. Stellen Sie sicher, dass Sie die korrekte MAC-Adresse angeben!</p>
<h2 class="fs-6">Hotspot löschen</h2>
<p>Um einen Hotspot zu löschen, klicken Sie einfach auf das Hotspot-Symbol und bestätigen Sie das Löschen aus der Datenbank.</p>
EOF;
$l['helpPage']['gisviewmap'] = <<<EOF
<h1 class="fs-5">Karte anzeigen</h1>
<p>In diesem Modus können Sie Hotspots anzeigen, die als Symbole auf der Karte dargestellt sind.</p>
<p>Durch Klicken auf einen Hotspot können Sie detailliertere Informationen darüber abrufen, einschließlich Kontaktdaten und anderer relevanter Details.</p>
EOF;

$l['helpPage']['gismain'] = <<<EOF
<p>Das <strong>GIS-Feature</strong> bietet visuelle Kartendarstellungen von Hotspot-Standorten weltweit.</p>
<p>Beim Hinzufügen eines neuen Hotspots können Sie dessen geographische Position durch Angabe der Breiten- und Längengrade festlegen, die zur genauen Lokalisierung auf der Karte verwendet werden.</p>
<p>Das GIS-Feature bietet zwei verschiedene Betriebsmodi:</p>
<ul>
    <li>Im Modus <strong>Karte anzeigen</strong> können Sie die Weltkarte erkunden und die aktuellen Standorte aller Hotspots in der Datenbank durch einfaches Klicken auf deren Symbole anzeigen;</li>
    <li>Im Modus <strong>Karte bearbeiten</strong> können Sie neue Hotspots durch Linksklick auf eine freie Stelle auf der Karte hinzufügen oder bestehende Hotspots durch Linksklick auf deren Symbole entfernen.</li>
</ul>
EOF;

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "Dieser Benutzer hat keine Check-Attribute zugeordnet";
$l['messages']['noReplyAttributesForUser'] = "Dieser Benutzer hat keine Reply-Attribute zugeordnet";

$l['messages']['noCheckAttributesForGroup'] = "Diese Gruppe hat keine Check-Attribute zugeordnet";
$l['messages']['noReplyAttributesForGroup'] = "Diese Gruppe hat keine Reply-Attribute zugeordnet";

$l['messages']['nogroupdefinedforuser'] = "Dieser Benutzer hat keine Gruppen zugeordnet";
$l['messages']['wouldyouliketocreategroup'] = "Möchten Sie eine erstellen?";

$l['messages']['missingratetype'] = "Fehler: Fehlender Tariftyp zum Löschen";
$l['messages']['missingtype'] = "Fehler: Fehlender Typ";
$l['messages']['missingcardbank'] = "Fehler: Fehlende Kartenbank";
$l['messages']['missingrate'] = "Fehler: Fehlender Tarif";
$l['messages']['success'] = "Erfolgreich";

$l['messages']['gisedit1'] = "Willkommen, Sie befinden sich derzeit im Bearbeitungsmodus";
$l['messages']['gisedit2'] = "Aktuellen Marker von der Karte und der Datenbank entfernen?";
$l['messages']['gisedit3'] = "Bitte geben Sie den Namen des Hotspots ein";
$l['messages']['gisedit4'] = "Aktuellen Marker zur Datenbank hinzufügen?";
$l['messages']['gisedit5'] = "Bitte geben Sie den Namen des Hotspots ein";
$l['messages']['gisedit6'] = "Bitte geben Sie die MAC-Adresse des Hotspots ein";

$l['messages']['gismain1'] = "GoogleMaps-API-Registrierungscode erfolgreich aktualisiert";
$l['messages']['gismain2'] = "Fehler: Die Datei konnte nicht zum Schreiben geöffnet werden:";
$l['messages']['gismain3'] = "Überprüfen Sie die Dateiberechtigungen. Die Datei sollte vom Webserver-Benutzer/-Gruppe beschreibbar sein";
$l['messages']['gisviewwelcome'] = "Willkommen bei Enginx Visual Maps";

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Login nicht möglich.</h1>
<p>Dies geschieht normalerweise aus einem der folgenden Gründe:
    <ul>
        <li>Falscher Benutzername und/oder falsches Passwort;</li>
        <li>Ein Administrator ist bereits angemeldet<br>(nur eine Instanz ist erlaubt);</li>
        <li>Wenn es mehr als einen 'administrator'-Benutzer in der Datenbank zu geben scheint.</li>
    </ul>
</p>
EOF;

$l['buttons']['savesettings'] = "Einstellungen speichern";
$l['buttons']['apply'] = "Übernehmen";

$l['menu']['Home'] = "Startseite";
$l['menu']['Managment'] = "Management";
$l['menu']['Reports'] = "Berichte";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Abrechnung";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Diagramme";
$l['menu']['Config'] = "Konfiguration";
$l['menu']['Help'] = "Hilfe";

$l['submenu']['General'] = "Allgemein";
$l['submenu']['Reporting'] = "Berichterstellung";
$l['submenu']['Maintenance'] = "Wartung";
$l['submenu']['Operators'] = "Operatoren";
$l['submenu']['Backup'] = "Backup";
$l['submenu']['Logs'] = "Logs";
$l['submenu']['Status'] = "Status";
$l['submenu']['Batch Users'] = "Batch-Benutzer";
$l['submenu']['Dashboard'] = "Dashboard";
$l['submenu']['Users'] = "Benutzer";
$l['submenu']['Hotspots'] = "Hotspots";
$l['submenu']['Nas'] = "NAS";
$l['submenu']['User-Groups'] = "Benutzergruppen";
$l['submenu']['Profiles'] = "Profile";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "Attribute";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "POS";
$l['submenu']['Plans'] = "Tarife";
$l['submenu']['Rates'] = "Tarifsätze";
$l['submenu']['Merchant-Transactions'] = "Anbieter-Transaktionen";
$l['submenu']['Billing-History'] = "Abrechnungsverlauf";
$l['submenu']['Invoices'] = "Rechnungen";
$l['submenu']['Payments'] = "Zahlungen";
$l['submenu']['Custom'] = "Benutzerdefiniert";
$l['submenu']['Hotspot'] = "Hotspot";
$l['submenu']['Mail'] = "E-Mail";
