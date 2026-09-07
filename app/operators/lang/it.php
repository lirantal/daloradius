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
 * Description:    Italian language file
 *
 * Authors:        Alessandro Rendina <ale@seleneinformatica.it>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/it.php') !== false) {
    header("Location: ../index.php");
    exit;
}

/* **********************************************************************************
 * General strings
 ***********************************************************************************/

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'Rapporti, Fatturazione e Gestione RADIUS by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . <<<EOF
 <span class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Follow @filippolauria on GitHub">
  <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>
</span>  and <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.
EOF;

$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Nome Pool";
$l['all']['CalledStationId'] = "IdStazioneChiamata";
$l['all']['CallingStationID'] = "IDStazioneChiamata";
$l['all']['ExpiryTime'] = "Tempo di scadenza";
$l['all']['PoolKey'] = "Chiave Pool";

// Vendor Attributes related translation
$l['all']['Dictionary'] = "Dizionario";
$l['all']['VendorID'] = "ID Venditore";
$l['all']['VendorName'] = "Nome Venditore";
$l['all']['VendorAttribute'] = "Attributo Venditore";
$l['all']['RecommendedOP'] = "OP Suggerito";
$l['all']['RecommendedTable'] = "Tabella Suggerita";
$l['all']['RecommendedTooltip'] = "Suggerimento";
$l['all']['RecommendedHelper'] = "Helper Suggerito";

$l['all']['CSVData'] = "Dati in formato CSV";
$l['all']['GeneratePassword'] = "Genera Password";
$l['all']['GeneratedPasswords'] = "Password generate";
$l['all']['Yes'] = "sì";
$l['all']['No'] = "no";

$l['all']['CPU'] = "CPU";

// radius related text
$l['all']['RADIUSDictionaryPath'] = "Percorso Dizionario RADIUS";

$l['all']['DashboardSecretKey'] = "Chiave segreta Dashboard";
$l['all']['DashboardDebug'] = "Debug";
$l['all']['DashboardDelaySoft'] = "Tempo in minuti per considerare un limite di ritardo 'soft'";
$l['all']['DashboardDelayHard'] = "Tempo in minuti per considerare un limite di ritardo 'hard'";

$l['all']['SendWelcomeNotification'] = "Invia notifica di benvenuto";
$l['all']['SMTPServerAddress'] = "Indirizzo server SMTP";
$l['all']['SMTPServerPort'] = "Porta server SMTP";
$l['all']['SMTPServerFromEmail'] = "Indirizzo email mittente";

$l['all']['customAttributes'] = "Attributi personalizzati";

$l['all']['UserType'] = "Tipo utente";

$l['all']['BatchName'] = "Nome batch";
$l['all']['BatchStatus'] = "Stato batch";

$l['all']['Users'] = "Utenti";

$l['all']['Compare'] = "Confronta";
$l['all']['Never'] = "Mai";

$l['all']['Section'] = "Sezione";
$l['all']['Item'] = "Campo";

$l['all']['Megabytes'] = "Megabyte";
$l['all']['Gigabytes'] = "Gigabyte";

$l['all']['Daily'] = "Giornaliero";
$l['all']['Weekly'] = "Settimanale";
$l['all']['Monthly'] = "Mensile";
$l['all']['Yearly'] = "Annuale";

$l['all']['Month'] = "Mese";

$l['all']['RemoveRadacctRecords'] = "Cancella Registrazioni di Accounting";

$l['all']['CleanupSessions'] = "Pulisci sessioni più vecchie di";
$l['all']['DeleteSessions'] = "Cancella sessioni più vecchie di";

$l['all']['StartingDate'] = "Data di inizio";
$l['all']['EndingDate'] = "Data di fine";

$l['all']['Realm'] = "Realm";
$l['all']['RealmName'] = "Nome Realm";
$l['all']['RealmSecret'] = "Realm Secert";
$l['all']['AuthHost'] = "Auth Host";
$l['all']['AcctHost'] = "Acct Host";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "Consigli";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Nome Proxy";
$l['all']['ProxySecret'] = "Secret Proxy";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";

$l['all']['Firmware'] = "Firmware";
$l['all']['NASMAC'] = "MAC NAS";

$l['all']['WanIface'] = "Interfaccia WAN";
$l['all']['WanMAC'] = "MAC WAN";
$l['all']['WanIP'] = "IP WAN";
$l['all']['WanGateway'] = "Gateway WAN";

$l['all']['LanIface'] = "Interfaccia LAN";
$l['all']['LanMAC'] = "MAC LAN";
$l['all']['LanIP'] = "IP LAN";

$l['all']['WifiIface'] = "Interfaccia Wi-Fi";
$l['all']['WifiMAC'] = "MAC Wi-Fi";
$l['all']['WifiIP'] = "IP Wi-Fi";

$l['all']['WifiSSID'] = "SSID Wi-Fi";
$l['all']['WifiKey'] = "Chiave Wi-Fi";
$l['all']['WifiChannel'] = "Canale Wi-Fi";

$l['all']['CheckinTime'] = "Ultimo check-in";

$l['all']['FramedIPAddress'] = "Framed-IP-Address";
$l['all']['SimultaneousUse'] = "Simultaneous-Use";
$l['all']['HgID'] = "ID HG";
$l['all']['Hg'] = "HG ";
$l['all']['HgIPHost'] = "HG IP/Host";
$l['all']['HgGroupName'] = "HG Groupname";
$l['all']['HgPortId'] = "HG Port Id";
$l['all']['NasID'] = "ID NAS";
$l['all']['Nas'] = "NAS";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "Nome breve";
$l['all']['NasType'] = "Tipo NAS";
$l['all']['NasPorts'] = "Porte NAS";
$l['all']['NasSecret'] = "NAS Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "Descrizione NAS";
$l['all']['PacketType'] = "Tipo Pacchetto";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['HotSpots'] = "HotSpots";
$l['all']['HotSpotName'] = "Nome Hotspot";
$l['all']['Name'] = "Nome";
$l['all']['Username'] = "Nome Utente";
$l['all']['Password'] = "Password";
$l['all']['PasswordType'] = "Tipo Password";
$l['all']['IPAddress'] = "Indirizzo IP";
$l['all']['Profile'] = "Profilo";
$l['all']['Group'] = "Gruppo";
$l['all']['Groupname'] = "Nome Gruppo";
$l['all']['ProfilePriority'] = "Priorità Profilo";
$l['all']['GroupPriority'] = "Priorità Gruppo";
$l['all']['CurrentGroupname'] = "Nome Gruppo Corrente";
$l['all']['NewGroupname'] = "Nuovo Nome Gruppo";
$l['all']['Priority'] = "Priorità";
$l['all']['Attribute'] = "Attributo";
$l['all']['Operator'] = "Operatore";
$l['all']['Value'] = "Valore";
$l['all']['NewValue'] = "Nuovo Valore";
$l['all']['MaxTimeExpiration'] = "Tempo massimo / Scadenza";
$l['all']['UsedTime'] = "Tempo di utilizzo";
$l['all']['Status'] = "Stato";
$l['all']['Usage'] = "Utilizzo";
$l['all']['StartTime'] = "Inizio";
$l['all']['StopTime'] = "Fine";
$l['all']['TotalTime'] = "Tempo Totale";
$l['all']['TotalTraffic'] = "Traffico Totale";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Upload";
$l['all']['Download'] = "Download";
$l['all']['Rollback'] = "Ripristino";
$l['all']['Termination'] = "Termine";
$l['all']['NASIPAddress'] = "Indirizzo IP NAS";
$l['all']['NASShortName'] = "Nome breve NAS";
$l['all']['Action'] = "Azione";
$l['all']['UniqueUsers'] = "Utenti Unici";
$l['all']['TotalHits'] = "Hit Totali";
$l['all']['AverageTime'] = "Tempo medio";
$l['all']['Records'] = "Registrazioni";
$l['all']['Summary'] = "Riassunto";
$l['all']['Statistics'] = "Statistiche";
$l['all']['Credit'] = "Credito";
$l['all']['Used'] = "Usato";
$l['all']['LeftTime'] = "Tempo Rimanente";
$l['all']['LeftPercent'] = "% di tempo rimasto";
$l['all']['TotalSessions'] = "Totale Sessioni";
$l['all']['LastLoginTime'] = "Data e ora ultimo accesso";
$l['all']['TotalSessionTime'] = "Tempo totale di Sessione";
$l['all']['RateName'] = "Nome Tariffa";
$l['all']['RateType'] = "Tipo Tariffa";
$l['all']['RateCost'] = "Costo Tariffa";
$l['all']['Billed'] = "Billed";
$l['all']['TotalUsers'] = "Totale Utenti";
$l['all']['ActiveUsers'] = "Utenti Attivi";
$l['all']['TotalBilled'] = "Total Billed";
$l['all']['TotalPayed'] = "Totale Pagato";
$l['all']['Balance'] = "Saldo";
$l['all']['Type'] = "Tipo";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "Indirizzo MAC";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "Codice PIN";
$l['all']['CreationDate'] = "Data di Creazione";
$l['all']['CreationBy'] = "Creato da";
$l['all']['UpdateDate'] = "Data di Aggiornamento";
$l['all']['UpdateBy'] = "Aggiornato da";

$l['all']['Discount'] = "Sconto";
$l['all']['BillAmount'] = "Importo Fatturato";
$l['all']['BillAction'] = "Azione Fatturata";
$l['all']['BillPerformer'] = "Eseguito da";
$l['all']['BillReason'] = "Motivo Fatturazione";
$l['all']['Lead'] = "Lead";
$l['all']['Coupon'] = "Coupon";
$l['all']['OrderTaker'] = "Operatore Ordine";
$l['all']['BillStatus'] = "Stato Fatturazione";
$l['all']['LastBill'] = "Ultima Fattura";
$l['all']['NextBill'] = "Prossima Fattura";
$l['all']['BillDue'] = "Scadenza Fattura";
$l['all']['NextInvoiceDue'] = "Prossima Scadenza Fattura";
$l['all']['PostalInvoice'] = "Fattura Postale";
$l['all']['FaxInvoice'] = "Fattura via Fax";
$l['all']['EmailInvoice'] = "Fattura via Email";

$l['all']['ClientName'] = "Nome Cliente";
$l['all']['Date'] = "Data";

$l['all']['edit'] = "modifica";
$l['all']['del'] = "cancella";
$l['all']['groupslist'] = "lista gruppi";
$l['all']['TestUser'] = "Test Utente";
$l['all']['Accounting'] = "Accounting";
$l['all']['RADIUSReply'] = "Risposta RADIUS";

$l['all']['Disconnect'] = "Disconnetti";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Tentativi";
$l['all']['Count'] = "Conto";
$l['all']['Requests'] = "Richieste";

$l['all']['DatabaseHostname'] = "Hostname Database";
$l['all']['DatabasePort'] = "Numero Porta Database";
$l['all']['DatabaseUser'] = "Utente Database";
$l['all']['DatabasePass'] = "Pass Database";
$l['all']['DatabaseName'] = "Nome Database";

$l['all']['PrimaryLanguage'] = "Lingua Principale";

$l['all']['PagesLogging'] = "Logging Pagine (visite per pagina)";
$l['all']['QueriesLogging'] = "Logging Query (rapporti e grafici)";
$l['all']['ActionsLogging'] = "Logging Azioni (immissionne nei form)";
$l['all']['FilenameLogging'] = "Logging filename (percorso completo)";
$l['all']['LoggingDebugOnPages'] = "Logging di Debug info sulle pagine";
$l['all']['LoggingDebugInfo'] = "Logging di Debug Info";

$l['all']['PasswordHidden'] = "Nascondi le Password (verranno mostrati degli asterischi)";
$l['all']['TablesListing'] = "Rows/Records per Tables Listing page";
$l['all']['TablesListingNum'] = "Enable Tables Listing Numbering";
$l['all']['AjaxAutoComplete'] = "Abilita auto-completamento Ajax";

$l['all']['RadiusServer'] = "Server Radius";
$l['all']['RadiusPort'] = "Porta Radius";

$l['all']['UsernamePrefix'] = "Prefisso Nome Utente";

$l['all']['batchName'] = "ID/Nome batch";
$l['all']['batchDescription'] = "Descrizione batch";

$l['all']['NumberInstances'] = "Numero di istanze da creare";
$l['all']['UsernameLength'] = "Lunghezza stringa nome utente";
$l['all']['PasswordLength'] = "Lunghezza stringa password";

$l['all']['Expiration'] = "Scadenza";
$l['all']['MaxAllSession'] = "Massimo Sessione";
$l['all']['SessionTimeout'] = "Timeout Sessione";
$l['all']['IdleTimeout'] = "Timeout Idle";

$l['all']['DBEngine'] = "Motore DB";
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

$l['all']['CreateIncrementingUsers'] = "Crea utenti incrementali";
$l['all']['CreateRandomUsers'] = "Crea utenti casuali";
$l['all']['StartingIndex'] = "Indice iniziale";
$l['all']['EndingIndex'] = "Indice finale";
$l['all']['RandomChars'] = "Caratteri casuali consentiti";
$l['all']['Memfree'] = "Memoria libera";
$l['all']['Uptime'] = "Uptime";
$l['all']['BandwidthUp'] = "Banda in Upload";
$l['all']['BandwidthDown'] = "Banda in Download";

$l['all']['BatchCost'] = "Costo batch";

$l['all']['PaymentStatus'] = "Stato Pagamento";
$l['all']['FirstName'] = "Nome";
$l['all']['LastName'] = "Cognome";
$l['all']['VendorType'] = "Fornitore Merchant";
$l['all']['PayerStatus'] = "Stato Pagante";
$l['all']['PaymentAddressStatus'] = "Stato Indirizzo Pagamento";
$l['all']['PayerEmail'] = "Email Pagante";
$l['all']['TxnId'] = "ID Transazione";
$l['all']['PlanActive'] = "Piano Attivo";
$l['all']['PlanTimeType'] = "Tipo Tempo Piano";
$l['all']['PlanTimeBank'] = "Banca Tempo Piano";
$l['all']['PlanTimeRefillCost'] = "Costo Ricarica Piano";
$l['all']['PlanTrafficRefillCost'] = "Costo Ricarica Piano";
$l['all']['PlanBandwidthUp'] = "Banda Upload Piano";
$l['all']['PlanBandwidthDown'] = "Banda Download Piano";
$l['all']['PlanTrafficTotal'] = "Traffico Totale Piano";
$l['all']['PlanTrafficDown'] = "Traffico Download Piano";
$l['all']['PlanTrafficUp'] = "Traffico Upload Piano";
$l['all']['PlanRecurring'] = "Piano Ricorrente";
$l['all']['PlanRecurringPeriod'] = "Periodo Ricorrenza Piano";
$l['all']['planRecurringBillingSchedule'] = "Pianificazione Fatturazione Ricorrente";
$l['all']['PlanCost'] = "Costo Piano";
$l['all']['PlanSetupCost'] = "Costo Attivazione Piano";
$l['all']['PlanTax'] = "Imposta Piano";
$l['all']['PlanCurrency'] = "Valuta Piano";
$l['all']['PlanGroup'] = "Profilo Piano (Gruppo)";
$l['all']['PlanType'] = "Tipo Piano";
$l['all']['PlanName'] = "Nome Piano";
$l['all']['PlanId'] = "ID Piano";

$l['all']['UserId'] = "ID Utente";

$l['all']['Invoice'] = "Fattura";
$l['all']['InvoiceID'] = "ID Fattura";
$l['all']['InvoiceItems'] = "Voci Fattura";
$l['all']['InvoiceStatus'] = "Stato Fattura";

$l['all']['InvoiceType'] = "Tipo Fattura";
$l['all']['Amount'] = "Importo";
$l['all']['Total'] = "Totale";
$l['all']['TotalInvoices'] = "Totale Fatture";

$l['all']['PayTypeName'] = "Nome Tipo Pagamento";
$l['all']['PayTypeNotes'] = "Descrizione Tipo Pagamento";
$l['all']['payment_type'] = "payment types";
$l['all']['payments'] = "payments";
$l['all']['PaymentId'] = "ID Pagamento";
$l['all']['PaymentInvoiceID'] = "ID Fattura";
$l['all']['PaymentAmount'] = "Importo";
$l['all']['PaymentDate'] = "Data";
$l['all']['PaymentType'] = "Tipo Pagamento";
$l['all']['PaymentNotes'] = "Note Pagamento";

$l['all']['Quantity'] = "Quantità";
$l['all']['ReceiverEmail'] = "Email Destinatario";
$l['all']['Business'] = "Azienda";
$l['all']['Tax'] = "Imposta";
$l['all']['Cost'] = "Costo";
$l['all']['TotalCost'] = "Costo Totale";
$l['all']['TransactionFee'] = "Commissione Transazione";
$l['all']['PaymentCurrency'] = "Valuta Pagamento";
$l['all']['AddressRecipient'] = "Destinatario Indirizzo";
$l['all']['Street'] = "Via";
$l['all']['Country'] = "Paese";
$l['all']['CountryCode'] = "Codice Paese";
$l['all']['City'] = "Città";
$l['all']['State'] = "Provincia";
$l['all']['Zip'] = "CAP";

$l['all']['BusinessName'] = "Nome Lavoro";
$l['all']['BusinessPhone'] = "Telefono Lavoro";
$l['all']['BusinessAddress'] = "Indirizzo Lavoro";
$l['all']['BusinessWebsite'] = "Sito Web Lavoro";
$l['all']['BusinessEmail'] = "Email Lavoro";
$l['all']['BusinessContactPerson'] = "Contatto Lavoro";
$l['all']['DBPasswordEncryption'] = "Tipo di criptazione Password DB";

$l['all']['Calling Station ID'] = "Calling Station ID";
$l['all']['Framed IP Address'] = "Framed IP Address";

$l['all']['Rate'] = "Rate";

$l['all']['rates'] = "rates";

/* **********************************************************************************
 * Login page text
 ***********************************************************************************/

$l['text']['LoginRequired'] = "Effettuare il login";
$l['text']['LoginPlease'] = "Login";

/* **********************************************************************************
 * Tooltips and form-field hints
 ***********************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "Fornire un nome identificativo per questa creazione batch";
$l['Tooltip']['batchDescriptionTooltip'] = "Fornire una descrizione generale di questa creazione batch";

$l['Tooltip']['hotspotTooltip'] = "Scegliere il nome dell'hotspot a cui questa istanza batch è associata";

$l['Tooltip']['startingIndexTooltip'] = "Fornire l'indice iniziale da cui creare gli utenti";
$l['Tooltip']['planTooltip'] = "Selezionare un piano a cui associare l'utente";

$l['Tooltip']['InvoiceEdit'] = "Modifica Fattura";
$l['Tooltip']['invoiceTypeTooltip'] = "";
$l['Tooltip']['invoiceStatusTooltip'] = "";
$l['Tooltip']['invoiceID'] = "Digitare l'id della fattura";
$l['Tooltip']['user_idTooltip'] = "ID utente";

$l['Tooltip']['amountTooltip'] = "";
$l['Tooltip']['taxTooltip'] = "";

$l['Tooltip']['PayTypeName'] = "Digitare il nome del Tipo di Pagamento";
$l['Tooltip']['EditPayType'] = "Modifica Tipo Pagamento";
$l['Tooltip']['RemovePayType'] = "Cancella Tipo Pagamento";
$l['Tooltip']['paymentTypeTooltip'] = "Il nome descrittivo del tipo di pagamento,<br>per descriverne lo scopo";
$l['Tooltip']['paymentTypeNotesTooltip'] = "La descrizione del tipo di pagamento, per descrivere<br>il funzionamento del tipo di pagamento";
$l['Tooltip']['generatePasswordTooltip'] = "Se impostato su 'sì', viene generata una password casuale di 8 caratteri quando il campo password del CSV è vuoto.";
$l['Tooltip']['CSVDataGeneratePasswordHint'] = "Lasciare vuoto il campo password per generarne una quando Genera Password è impostato su sì.";
$l['Tooltip']['EditPayment'] = "Modifica Pagamento";
$l['Tooltip']['PaymentId'] = "L'ID del Pagamento";
$l['Tooltip']['RemovePayment'] = "Cancella Pagamento";
$l['Tooltip']['paymentInvoiceTooltip'] = "La fattura collegata a questo pagamento";

$l['Tooltip']['Username'] = "Digitare l'username";
$l['Tooltip']['BatchName'] = "Digitare il nome del batch";
$l['Tooltip']['UsernameWildcard'] = "Nota: un carattere jolly verrà aggiunto automaticamente alla stringa digitata.";
$l['Tooltip']['HotspotName'] = "Digitare il nome dell'hotspot";
$l['Tooltip']['NasName'] = "Digitare il nome del NAS";
$l['Tooltip']['GroupName'] = "Digitare il nome del gruppo";
$l['Tooltip']['AttributeName'] = "Digitare il nome dell'attributo";
$l['Tooltip']['VendorName'] = "Selezionare il nome del fornitore";
$l['Tooltip']['PoolName'] = "Digitare il nome del pool";
$l['Tooltip']['IPAddress'] = "Digitare l'indirizzo IP";
$l['Tooltip']['Filter'] = "Digitare una stringa alfanumerica oppure lasciare vuoto per trovare tutto";
$l['Tooltip']['Date'] = "Selezionare una data";
$l['Tooltip']['RateName'] = "Digitare il nome della tariffa";
$l['Tooltip']['OperatorName'] = "Digitare il nome dell'operatore";
$l['Tooltip']['BillingPlanName'] = "Digitare il nome del piano di fatturazione";
$l['Tooltip']['PlanName'] = "Digitare il nome del piano";

$l['Tooltip']['EditRate'] = "Modifica Tariffa";
$l['Tooltip']['RemoveRate'] = "Cancella Tariffa";

$l['Tooltip']['rateNameTooltip'] = "Il nome descrittivo della tariffa,<br>per descriverne lo scopo";
$l['Tooltip']['rateTypeTooltip'] = "Il tipo di tariffa, per descrivere<br>il funzionamento della tariffa";
$l['Tooltip']['rateCostTooltip'] = "L'importo del costo della tariffa";

$l['Tooltip']['planNameTooltip'] = "Il nome del piano. È un nome descrittivo che indica le caratteristiche del piano";
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

$l['Tooltip']['EditIPPool'] = "Modifica IP-Pool";
$l['Tooltip']['RemoveIPPool'] = "Cancella IP-Pool";
$l['Tooltip']['EditIPAddress'] = "Modifica IP Address";
$l['Tooltip']['RemoveIPAddress'] = "Cancella IP Address";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Nome Proxy";
$l['Tooltip']['proxyRetryDelayTooltip'] = "Il tempo di attesa (in secondi) per una risposta dal proxy, prima di rispedire la rischiesta al proxy.";
$l['Tooltip']['proxyRetryCountTooltip'] = "Numero di tentativi di invio prima di rinunciare, e mandare un messaggio di reject al NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "Se l'home server non risponde a nessuna delle prove multiple, "
                                      . "allora FreeRADIUS smetterà di mandare richieste proxy, e lo marcherà come 'dead'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "Se nessun realm con corrispondenza esatta ha risposto, si può provare il";
$l['Tooltip']['realmNameTooltip'] = "Nome Realm";
$l['Tooltip']['realmTypeTooltip'] = "Imposta su radius per default";
$l['Tooltip']['realmSecretTooltip'] = "Segreto condiviso RADIUS del realm";
$l['Tooltip']['realmAuthhostTooltip'] = "Host autenticazione Realm";
$l['Tooltip']['realmAccthostTooltip'] = "Accounting host Realm";
$l['Tooltip']['realmLdflagTooltip'] = "Abilita per il load balancing. I valori abilitati sono 'fail_over' e 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Se togliere o no il suffisso realm";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";

$l['Tooltip']['vendorNameTooltip'] = "Esempio: Cisco. Il nome del fornitore.";
$l['Tooltip']['typeTooltip'] = "Esempio: string. Il tipo di dato dell'attributo (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Esempio: Framed-IPAddress. Il nome esatto dell'attributo.";

$l['Tooltip']['RecommendedOPTooltip'] = "Esempio: :=. L'operatore consigliato per questo attributo (uno tra: :=, ==, !=, ecc.).";
$l['Tooltip']['RecommendedTableTooltip'] = "Esempio: check. La tabella di destinazione consigliata (una tra: check, reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Esempio: l'indirizzo IP dell'utente.";
$l['Tooltip']['RecommendedHelperTooltip'] = "La funzione di aiuto che sarà disponibile quando si aggiungerà questo attributo";

$l['Tooltip']['AttributeEdit'] = "Modifica Attributo";

$l['Tooltip']['BatchDetails'] = "Dettagli Batch";

$l['Tooltip']['UserEdit'] = "Modifica Utente";
$l['Tooltip']['HotspotEdit'] = "Modifica Hotspot";
$l['Tooltip']['EditNAS'] = "Modifica NAS";
$l['Tooltip']['RemoveNAS'] = "Cancella NAS";
$l['Tooltip']['EditHG'] = "Modifica HuntGroup";
$l['Tooltip']['RemoveHG'] = "Cancella HuntGroup";
$l['Tooltip']['hgNasIpAddress'] = "Digitare l'indirizzo Host/IP";
$l['Tooltip']['hgGroupName'] = "Digitare il Groupname per il NAS";
$l['Tooltip']['hgNasPortId'] = "Digitare il Nas Port Id";
$l['Tooltip']['EditUserGroup'] = "Modifica Gruppo Utente";
$l['Tooltip']['ListUserGroups'] = "Mostra Gruppi Utente";
$l['Tooltip']['DeleteUserGroup'] = "Cancella Associazione Gruppo Utente";

$l['Tooltip']['EditProfile'] = "Modifica Profilo";

$l['Tooltip']['EditRealm'] = "Modifica Realm";
$l['Tooltip']['EditProxy'] = "Modifica Proxy";

$l['Tooltip']['EditGroup'] = "Modifica Gruppo";

$l['Tooltip']['usernameTooltip'] = "Il nome utente esatto, così come l'utente lo userà per connettersi al sistema.";
$l['Tooltip']['passwordTypeTooltip'] = "Il tipo di password usato per autenticare l'utente in RADIUS.";
$l['Tooltip']['passwordTooltip'] = "In alcuni sistemi le password sono sensibili alle maiuscole: prestare attenzione.";
$l['Tooltip']['groupTooltip'] = "L'utente verrà aggiunto a questo gruppo. Assegnando un utente a un particolare gruppo, l'utente diventa soggetto agli attributi del gruppo.";
$l['Tooltip']['macaddressTooltip'] = "Esempio: 00:aa:bb:cc:dd:ee. Il formato dell'indirizzo MAC dovrebbe essere lo stesso con cui viene inviato dal NAS. Nella maggior parte dei casi è senza altri caratteri.";
$l['Tooltip']['pincodeTooltip'] = "Esempio: khrivnxufi101. Questo è il codice PIN esattamente come verrà inserito dall'utente. Si possono usare caratteri alfanumerici ed è sensibile alle maiuscole.";
$l['Tooltip']['usernamePrefixTooltip'] = "Esempio: TMP_ POP_ WIFI1_. Questo prefisso verrà aggiunto all'inizio dell'username generato.";
$l['Tooltip']['instancesToCreateTooltip'] = "Esempio: 100. Il numero di utenti casuali da creare con il profilo specificato.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Esempio: 8. La lunghezza dell'username da creare. Valori consigliati: 8-12 caratteri.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Esempio: 8. La lunghezza della password da creare. Valori consigliati: 8-12 caratteri.";
$l['Tooltip']['hotspotNameTooltip'] = "Esempio: Hotel Stratocaster. Un nome comprensibile dell'hotspot.";
$l['Tooltip']['hotspotMacaddressTooltip'] = "Esempio: 00aabbccddee. L'indirizzo MAC del NAS.";
$l['Tooltip']['geocodeTooltip'] = "Esempio: -1.002,-2.201. È il codice del luogo GoogleMaps utilizzato per segnalare l'hotspot/NAS sulla mappa (vedi GIS).";

$l['Tooltip']['reassignplanprofiles'] = <<<EOF
Se attivato, quando si applicano le informazioni dell'utente<br>
                    i Profili elencati nella scheda Profili verranno ignorati e<br>
                    i profili verranno riassegnati in base all'associazione dei profili del Piano
EOF;

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "Se specifichi un valore, solo il record singolo che corrisponde contemporaneamente al nome del gruppo e al valore che hai specificato verrà rimosso. Se ometti il valore allora tutti i record di questo Gruppo verranno rimossi!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "Se specifichi un valore, solo il record singolo che corrisponde contemporaneamente al nome del gruppo e al valore che hai specificato verrà rimosso. Se ometti il valore allora tutti i record di questo Gruppo verranno rimossi!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(nome descrittivo)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "Se specifichi un gruppo allora verrà rimosso solo il singolo record che corrisponde contemporaneamente all'username e al gruppo. Se ometti il gruppo allora verranno rimossi tutti i record di questo utente.";

/* **********************************************************************************
 * Links and buttons
 ***********************************************************************************/

$l['button']['DashboardSettings'] = "Impostazioni Dashboard";

$l['button']['GenerateReport'] = "Genera Report";

$l['button']['ListPayTypes'] = "Mostra Tipi Pagamento";
$l['button']['NewPayType'] = "Nuovo Tipo Pagamento";
$l['button']['EditPayType'] = "Modifica Tipo Pagamento";
$l['button']['RemovePayType'] = "Cancella Tipo Pagamento";
$l['button']['ListPayments'] = "Mostra Pagamenti";
$l['button']['NewPayment'] = "Nuovo Pagamento";
$l['button']['EditPayment'] = "Modifica Pagamento";
$l['button']['RemovePayment'] = "Cancella Pagamento";

$l['button']['NewUsers'] = "Nuovi Utenti";

$l['button']['ClearSessions'] = "Pulisci Sessioni";
$l['button']['Dashboard'] = "Dashboard";
$l['button']['MailSettings'] = "Impostazioni Mail";

$l['button']['Batch'] = "Batch";
$l['button']['BatchHistory'] = "Storico Batch";
$l['button']['BatchDetails'] = "Dettagli Batch";

$l['button']['ListRates'] = "Mostra Tariffe";
$l['button']['NewRate'] = "Nuova Tariffa";
$l['button']['EditRate'] = "Modifica Tariffa";
$l['button']['RemoveRate'] = "Cancella Tariffa";

$l['button']['ListPlans'] = "Mostra Piani";
$l['button']['NewPlan'] = "Nuovo Piano";
$l['button']['EditPlan'] = "Modifica Piano";
$l['button']['RemovePlan'] = "Cancella Piano";

$l['button']['ListInvoices'] = "Mostra Fatture";
$l['button']['NewInvoice'] = "Nuova Fattura";
$l['button']['EditInvoice'] = "Modifica Fattura";
$l['button']['RemoveInvoice'] = "Cancella Fattura";

$l['button']['ListRealms'] = "Mostra Realms";
$l['button']['NewRealm'] = "Nuovo Realm";
$l['button']['EditRealm'] = "Modifica Realm";
$l['button']['RemoveRealm'] = "Cancella Realm";

$l['button']['ListProxys'] = "Mostra Proxys";
$l['button']['NewProxy'] = "Nuovo Proxy";
$l['button']['EditProxy'] = "Modifica Proxy";
$l['button']['RemoveProxy'] = "Cancella Proxy";

$l['button']['ListAttributesforVendor'] = "Mostra Attributi per Vendor:";
$l['button']['NewVendorAttribute'] = "Nuovo Attributo Fornitore";
$l['button']['EditVendorAttribute'] = "Modifica Attributo Fornitore";
$l['button']['SearchVendorAttribute'] = "Cerca Attributo";
$l['button']['RemoveVendorAttribute'] = "Cancella Attributo Fornitore";
$l['button']['ImportVendorDictionary'] = "Importa Dizionario Fornitore";

$l['button']['BetweenDates'] = "Tra le Date:";
$l['button']['Where'] = "Dove";
$l['button']['AccountingFieldsinQuery'] = "Campi Accounting nella Query:";
$l['button']['OrderBy'] = "Ordina Per";
$l['button']['HotspotAccounting'] = "Accounting Hotspot";
$l['button']['HotspotsComparison'] = "Confronti Hotspots";

$l['button']['CleanupStaleSessions'] = "Pulisci Sessioni Stantie";
$l['button']['DeleteAccountingRecords'] = "Cancella Registrazioni Contabilizzate";

$l['button']['ListUsers'] = "Mostra Utenti";
$l['button']['ListBatches'] = "Mostra Batch";
$l['button']['RemoveBatch'] = "Cancella Batch";
$l['button']['ImportUsers'] = "Importa Utenti";
$l['button']['NewUser'] = "Nuovo Utente";
$l['button']['NewUserQuick'] = "Nuovo Utente - Agg. Veloce";
$l['button']['BatchAddUsers'] = "Batch Aggiungi Utenti";
$l['button']['EditUser'] = "Modifica Utente";
$l['button']['SearchUsers'] = "Cerca Utenti";
$l['button']['RemoveUsers'] = "Cancella Utenti";
$l['button']['ListHotspots'] = "Mostra Hotspots";
$l['button']['NewHotspot'] = "Nuovo Hotspot";
$l['button']['EditHotspot'] = "Modifica Hotspot";
$l['button']['RemoveHotspot'] = "Cancella Hotspot";

$l['button']['ListIPPools'] = "Mostra IP-Pools";
$l['button']['NewIPPool'] = "Nuovo IP-Pool";
$l['button']['EditIPPool'] = "Modifica IP-Pool";
$l['button']['RemoveIPPool'] = "Cancella IP-Pool";

$l['button']['ListNAS'] = "Mostra NAS";
$l['button']['NewNAS'] = "Nuovo NAS";
$l['button']['EditNAS'] = "Modifica NAS";
$l['button']['RemoveNAS'] = "Cancella NAS";
$l['button']['ListHG'] = "Mostra HuntGroup";
$l['button']['NewHG'] = "Nuovo HuntGroup";
$l['button']['EditHG'] = "Modifica HuntGroup";
$l['button']['RemoveHG'] = "Cancella HuntGroup";
$l['button']['ListUserGroup'] = "Mostra Mappa Gruppo-Utente";
$l['button']['ListUsersGroup'] = "Mostra Mappa Gruppi-Utente";
$l['button']['NewUserGroup'] = "Nuova Mappa Gruppo-Utente";
$l['button']['EditUserGroup'] = "Modifica Mappa Gruppo-Utente";
$l['button']['RemoveUserGroup'] = "Cancella Mappa Gruppo-Utente";

$l['button']['ListProfiles'] = "Mostra Profili";
$l['button']['NewProfile'] = "Nuovo Profilo";
$l['button']['EditProfile'] = "Modifica Profilo";
$l['button']['DuplicateProfile'] = "Duplica Profilo";
$l['button']['RemoveProfile'] = "Cancella Profilo";

$l['button']['ListGroupReply'] = "Mostra Mappe Group-Reply";
$l['button']['SearchGroupReply'] = "Cerca Group-Reply";
$l['button']['NewGroupReply'] = "Nuova Mappa Group-Reply";
$l['button']['EditGroupReply'] = "Modifica Mappa Group-Reply";
$l['button']['RemoveGroupReply'] = "Cancella Mappa Group-Reply";

$l['button']['ListGroupCheck'] = "Mostra Mappe Group-Check";
$l['button']['SearchGroupCheck'] = "Cerca Group-Check";
$l['button']['NewGroupCheck'] = "Nuova Mappa Group-Check";
$l['button']['EditGroupCheck'] = "Modifica Mappa Group-Check";
$l['button']['RemoveGroupCheck'] = "Cancella Mappa Group-Check";

$l['button']['UserAccounting'] = "Accounting Utente";
$l['button']['IPAccounting'] = "Accounting IP";
$l['button']['NASIPAccounting'] = "Accounting IP NAS";
$l['button']['NASIPAccountingOnlyActive'] = "Mostra solo attivi";
$l['button']['DateAccounting'] = "Data Accounting";
$l['button']['AllRecords'] = "Tutti i Record";
$l['button']['ActiveRecords'] = "Record Attivi";

$l['button']['PlanUsage'] = "Utilizzo Piano";

$l['button']['OnlineUsers'] = "Utenti Online";
$l['button']['LastConnectionAttempts'] = "Ultimi Tentativi di Connessione";
$l['button']['TopUser'] = "Top Utenti";
$l['button']['History'] = "Storico";

$l['button']['ServerStatus'] = "Stato Server";
$l['button']['ServicesStatus'] = "Stato Servizi";

$l['button']['daloRADIUSLog'] = "Log daloRADIUS";
$l['button']['RadiusLog'] = "Log RADIUS";
$l['button']['SystemLog'] = "Log di Sistema";
$l['button']['BootLog'] = "Log di Boot";

$l['button']['UserLogins'] = "Login Utenti";
$l['button']['UserDownloads'] = "Download Utenti";
$l['button']['UserUploads'] = "Upload Utenti";
$l['button']['TotalLogins'] = "Totale Logins";
$l['button']['TotalTraffic'] = "Traffico Totale";
$l['button']['LoggedUsers'] = "Utenti Connessi";

$l['button']['ViewMAP'] = "Vedi Mappa";
$l['button']['EditMAP'] = "Modifica Mappa";
$l['button']['RegisterGoogleMapsAPI'] = "Registra API GoogleMap";

$l['button']['UserSettings'] = "Impostazioni Utente";
$l['button']['DatabaseSettings'] = "Impostazioni Database";
$l['button']['LanguageSettings'] = "Impostazioni Lingua";
$l['button']['LoggingSettings'] = "Impostazioni Logging";
$l['button']['InterfaceSettings'] = "Impostazioni Interfaccia";

$l['button']['ReAssignPlanProfiles'] = "Riassegna Profili Piano";

$l['button']['TestUserConnectivity'] = "Test connettività";
$l['button']['DisconnectUser'] = "Disconnetti Utente";

$l['button']['ManageBackups'] = "Gestisci Backup";
$l['button']['CreateBackups'] = "Crea Backup";

$l['button']['ListOperators'] = "Mostra Operatori";
$l['button']['NewOperator'] = "Nuovo Operatore";
$l['button']['EditOperator'] = "Modifica Operatore";
$l['button']['RemoveOperator'] = "Cancella Operatore";

$l['button']['ProcessQuery'] = "Elabora Query";

$l['button']['BusinessInformation'] = "Informazioni Commerciali";

/* **********************************************************************************
 * Form action buttons
 ***********************************************************************************/

$l['buttons']['savesettings'] = "Salva Impostazioni";
$l['buttons']['apply'] = "Applica";
$l['buttons']['downloadGeneratedPasswordsCSV'] = "Scarica CSV Password Generate";

/* **********************************************************************************
 * Titles (fieldsets, tables, tabs)
 ***********************************************************************************/

$l['title']['ImportUsers'] = "Importa Utenti";

$l['title']['Dashboard'] = "Dashboard";
$l['title']['DashboardAlerts'] = "Avvisi";

$l['title']['Invoice'] = "Fattura";
$l['title']['Invoices'] = "Fatture";
$l['title']['InvoiceRemoval'] = "Rimozione Fattura";
$l['title']['Payments'] = "Pagamenti";
$l['title']['Items'] = "Voci";

$l['title']['PayTypeInfo'] = "Informazioni Tipo Pagamento";
$l['title']['PaymentInfo'] = "Informazioni Pagamento";

$l['title']['RateInfo'] = "Informazioni Tariffa";
$l['title']['PlanInfo'] = "Informazioni Piano";
$l['title']['TimeSettings'] = "Impostazioni Tempo";
$l['title']['BandwidthSettings'] = "Impostazioni Banda";
$l['title']['PlanRemoval'] = "Rimozione Piano";

$l['title']['BatchRemoval'] = "Rimozione Batch";

$l['title']['Backups'] = "Backup";
$l['title']['FreeRADIUSTables'] = "Tabelle FreeRADIUS";
$l['title']['daloRADIUSTables'] = "Tabelle daloRADIUS";

$l['title']['IPPoolInfo'] = "Info IP-Pool";

$l['title']['BusinessInfo'] = "Info Commerciali";

$l['title']['CleanupRecordsByUsername'] = "Per Username";
$l['title']['CleanupRecordsByDate'] = "Per Data";
$l['title']['DeleteRecords'] = "Cancella Records";

$l['title']['RealmInfo'] = "Info Realm";

$l['title']['ProxyInfo'] = "Info Proxy";

$l['title']['VendorAttribute'] = "Attributo Venditore";

$l['title']['AccountRemoval'] = "Cancellazione Account";
$l['title']['AccountInfo'] = "Info account";

$l['title']['Profiles'] = "Profili";
$l['title']['ProfileInfo'] = "Info Profilo";

$l['title']['GroupInfo'] = "Info Gruppo";
$l['title']['GroupAttributes'] = "Attributi Gruppo";

$l['title']['NASInfo'] = "Info NAS";
$l['title']['NASAdvanced'] = "NAS Avanzato";
$l['title']['HGInfo'] = "Info HG";
$l['title']['UserInfo'] = "Info Utente";
$l['title']['BillingInfo'] = "Info Fatturazione";

$l['title']['Attributes'] = "Attributi";
$l['title']['ProfileAttributes'] = "Attributi Profilo";

$l['title']['HotspotInfo'] = "Info Hotspot";
$l['title']['HotspotRemoval'] = "Rimozione Hotspot";

$l['title']['ContactInfo'] = "Info Contatti";

$l['title']['Plan'] = "Piano";

$l['title']['Profile'] = "Profilo";
$l['title']['Groups'] = "Gruppi";
$l['title']['RADIUSCheck'] = "Verifica Attributi";
$l['title']['RADIUSReply'] = "Risposta Attributi";

$l['title']['Settings'] = "Impostazioni";
$l['title']['DatabaseSettings'] = "Impostazioni Database";
$l['title']['DatabaseTables'] = "Tabelle Database";
$l['title']['AdvancedSettings'] = "Impostazioni Avanzate";

$l['title']['Advanced'] = "Avanzate";
$l['title']['Optional'] = "Opzionale";

$l['title']['CleanupRecords'] = "Pulisci Records";

/* **********************************************************************************
 * Captions
 ***********************************************************************************/

$l['captions']['providebillratetodel'] = "Fornisce il tipo di tariffa che potresti voler rimuovere";
$l['captions']['detailsofnewrate'] = "E' possibile riempire di sotto i dettagli per la nuova tariffa";
$l['captions']['filldetailsofnewrate'] = "Riempire sotto i dettagli per la nuova tariffa";

/* **********************************************************************************
 * Top navigation
 ***********************************************************************************/

$l['menu']['Home'] = "Home";
$l['menu']['Managment'] = "Gestione";
$l['menu']['Reports'] = "Rapporti";
$l['menu']['Accounting'] = "Contabilità";
$l['menu']['Billing'] = "Fatture";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Grafici";
$l['menu']['Config'] = "Config";
$l['menu']['Help'] = "Aiuto";

/* **********************************************************************************
 * Sub navigation
 ***********************************************************************************/

$l['submenu']['General'] = "Generale";
$l['submenu']['Reporting'] = "Reporting";
$l['submenu']['Maintenance'] = "Maintenance";
$l['submenu']['Operators'] = "Operatori";
$l['submenu']['Backup'] = "Backup";
$l['submenu']['Logs'] = "Logs";
$l['submenu']['Status'] = "Status";
$l['submenu']['Batch Users'] = "Batch Users";
$l['submenu']['Dashboard'] = "Dashboard";
$l['submenu']['Users'] = "Utenti";
$l['submenu']['Hotspots'] = "Hotspots";
$l['submenu']['Nas'] = "Nas";
$l['submenu']['User-Groups'] = "User-Groups";
$l['submenu']['Profiles'] = "Profili";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "Attributi";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "POS";
$l['submenu']['Plans'] = "Piani";
$l['submenu']['Rates'] = "Rates";
$l['submenu']['Merchant-Transactions'] = "Merchant-Transactions";
$l['submenu']['Billing-History'] = "Billing-History";
$l['submenu']['Invoices'] = "Invoices";
$l['submenu']['Payments'] = "Payments";
$l['submenu']['Custom'] = "Custom";
$l['submenu']['Hotspot'] = "Hotspot";
$l['submenu']['Mail'] = "Mail";

/* **********************************************************************************
 * Sidebar
 ***********************************************************************************/

// sidebar menu titles, section headings, link labels and form captions
// (see app/operators/include/menu/sidebar/)
$l['sidebar']['Accounting'] = "Accounting";
$l['sidebar']['AttributesManagement'] = "Gestione Attributi";
$l['sidebar']['BackupSettings'] = "Impostazioni Backup";
$l['sidebar']['BatchManagement'] = "Gestione Batch";
$l['sidebar']['BatchUsers'] = "Utenti Batch";
$l['sidebar']['Billing'] = "Fatturazione";
$l['sidebar']['CRONStatus'] = "Stato CRON";
$l['sidebar']['Charts'] = "Grafici";
$l['sidebar']['Configuration'] = "Configurazione";
$l['sidebar']['CustomQuery'] = "Query Personalizzata";
$l['sidebar']['ExtendedCapabilities'] = "Funzionalità Estese";
$l['sidebar']['ExtendedPeripherals'] = "Periferiche Estese";
$l['sidebar']['Filter'] = "Filtro";
$l['sidebar']['FilterRADIUSReply'] = "Filtra i record con il RADIUS Reply selezionato";
$l['sidebar']['GIS'] = "GIS";
$l['sidebar']['GISMapping'] = "Mappatura GIS";
$l['sidebar']['GlobalSettings'] = "Impostazioni Globali";
$l['sidebar']['GroupCheckManagement'] = "Gestione Group Check";
$l['sidebar']['GroupReplyManagement'] = "Gestione Group Reply";
$l['sidebar']['Heartbeat'] = "Heartbeat";
$l['sidebar']['Help'] = "Aiuto";
$l['sidebar']['Home'] = "Home";
$l['sidebar']['HotspotsAccounting'] = "Accounting Hotspot";
$l['sidebar']['HotspotsManagement'] = "Gestione Hotspot";
$l['sidebar']['Huntgroup'] = "Huntgroup";
$l['sidebar']['HuntgroupsManagement'] = "Gestione Huntgroup";
$l['sidebar']['IPPoolsManagement'] = "Gestione IP-Pool";
$l['sidebar']['InvoiceManagement'] = "Gestione Fatture";
$l['sidebar']['InvoiceReport'] = "Report Fatture";
$l['sidebar']['LinesCount'] = "Numero di righe";
$l['sidebar']['List'] = "Elenco";
$l['sidebar']['LogFiles'] = "File di Log";
$l['sidebar']['Logs'] = "Log";
$l['sidebar']['Mail'] = "Mail";
$l['sidebar']['Maintenance'] = "Manutenzione";
$l['sidebar']['Management'] = "Gestione";
$l['sidebar']['MessageSettings'] = "Impostazioni Messaggi";
$l['sidebar']['NASManagement'] = "Gestione NAS";
$l['sidebar']['OperatorsManagement'] = "Gestione Operatori";
$l['sidebar']['OrderResultsBy'] = "È possibile ordinare i risultati per: %s";
$l['sidebar']['OrderType'] = "Tipo Ordinamento";
$l['sidebar']['OtherReports'] = "Altri Report";
$l['sidebar']['PaymentsManagement'] = "Gestione Pagamenti";
$l['sidebar']['PaymentsTypesManagement'] = "Gestione Tipi di Pagamento";
$l['sidebar']['PlanAccounting'] = "Accounting Piani";
$l['sidebar']['PlansManagement'] = "Gestione Piani";
$l['sidebar']['PleaseInsertAValid'] = "Inserire un %s valido";
$l['sidebar']['PleaseSelectA'] = "Selezionare un %s";
$l['sidebar']['PleaseSelectOneOrMultiple'] = "Selezionare uno o più %s";
$l['sidebar']['PointOfSalesManagement'] = "Gestione Punti Vendita";
$l['sidebar']['ProfilesManagement'] = "Gestione Profili";
$l['sidebar']['ProxiesManagement'] = "Gestione Proxy";
$l['sidebar']['RAIDStatus'] = "Stato RAID";
$l['sidebar']['RatesManagement'] = "Gestione Tariffe";
$l['sidebar']['ReadMore'] = "Leggi di più";
$l['sidebar']['RealmsManagement'] = "Gestione Realm";
$l['sidebar']['RecurringTasksSettings'] = "Impostazioni Attività Ricorrenti";
$l['sidebar']['ReportingSettings'] = "Impostazioni Reportistica";
$l['sidebar']['Reports'] = "Report";
$l['sidebar']['ShowOnlySelectedLines'] = "Mostra solo il numero di righe selezionato";
$l['sidebar']['Status'] = "Stato";
$l['sidebar']['Support'] = "Supporto";
$l['sidebar']['TestEmail'] = "Email di prova";
$l['sidebar']['TrackBillingHistory'] = "Traccia Storico Fatturazione";
$l['sidebar']['TrackMerchantTransactions'] = "Traccia Transazioni Merchant";
$l['sidebar']['TrackRates'] = "Traccia Tariffe";
$l['sidebar']['TwoFactorAuthentication'] = "Autenticazione a due fattori";
$l['sidebar']['UPSStatus'] = "Stato UPS";
$l['sidebar']['UserCharts'] = "Grafici Utente";
$l['sidebar']['UserGroupManagement'] = "Gestione Gruppi Utente";
$l['sidebar']['UserReports'] = "Report Utente";
$l['sidebar']['UsersAccounting'] = "Accounting Utenti";
$l['sidebar']['UsersManagement'] = "Gestione Utenti";

/* **********************************************************************************
 * Operator dashboard
 ***********************************************************************************/

// operator dashboard strings (see app/operators/home-main.php)
$l['dashboard']['CurrentlyOnline'] = "Attualmente online";
$l['dashboard']['GoToHotspotsList'] = "Vai all'elenco hotspot";
$l['dashboard']['GoToNASList'] = "Vai all'elenco NAS";
$l['dashboard']['GoToUsersList'] = "Vai all'elenco utenti";
$l['dashboard']['LastMonthTopUsers'] = "Utenti top del mese scorso";
$l['dashboard']['OnlineSince'] = "Online da";

/* **********************************************************************************
 * Graphs
 ***********************************************************************************/

$l['graphs']['Day'] = "Giorno";
$l['graphs']['Month'] = "Mese";
$l['graphs']['Year'] = "Anno";
$l['graphs']['Jan'] = "Gennaio";
$l['graphs']['Feb'] = "Febbraio";
$l['graphs']['Mar'] = "Marzo";
$l['graphs']['Apr'] = "Aprile";
$l['graphs']['May'] = "Maggio";
$l['graphs']['Jun'] = "Giugno";
$l['graphs']['Jul'] = "Luglio";
$l['graphs']['Aug'] = "Agosto";
$l['graphs']['Sep'] = "Settembre";
$l['graphs']['Oct'] = "Ottobre";
$l['graphs']['Nov'] = "Novembre";
$l['graphs']['Dec'] = "Dicembre";

/* **********************************************************************************
 * Contact info
 ***********************************************************************************/

$l['ContactInfo']['FirstName'] = "Nome";
$l['ContactInfo']['LastName'] = "Cognome";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Dipartimento";
$l['ContactInfo']['WorkPhone'] = "Telefono Lavoro";
$l['ContactInfo']['HomePhone'] = "Telefono Casa";
$l['ContactInfo']['Phone'] = "Telefono";
$l['ContactInfo']['MobilePhone'] = "Telefono Mobile";
$l['ContactInfo']['Notes'] = "Note";
$l['ContactInfo']['EnableUserUpdate'] = "Abilita aggiornamento utente";
$l['ContactInfo']['EnablePortalLogin'] = "Abilita login al portale utente";
$l['ContactInfo']['PortalLoginPassword'] = "Password login portale utente";

$l['ContactInfo']['OwnerName'] = "Nome Proprietario";
$l['ContactInfo']['OwnerEmail'] = "Email Proprietario";
$l['ContactInfo']['ManagerName'] = "Nome Gestore";
$l['ContactInfo']['ManagerEmail'] = "Email Gestore";
$l['ContactInfo']['Company'] = "Azienda";
$l['ContactInfo']['Address'] = "Indirizzo";
$l['ContactInfo']['City'] = "Città";
$l['ContactInfo']['State'] = "Provincia";
$l['ContactInfo']['Country'] = "Paese";
$l['ContactInfo']['Zip'] = "CAP";
$l['ContactInfo']['Phone1'] = "Telefono 1";
$l['ContactInfo']['Phone2'] = "Telefono 2";
$l['ContactInfo']['HotspotType'] = "Tipo Hotspot";
$l['ContactInfo']['CompanyWebsite'] = "Sito Web Azienda";
$l['ContactInfo']['CompanyPhone'] = "Telefono Azienda";
$l['ContactInfo']['CompanyEmail'] = "Email Azienda";
$l['ContactInfo']['CompanyContact'] = "Contatto Azienda";

$l['ContactInfo']['PlanName'] = "Nome Piano";
$l['ContactInfo']['ContactPerson'] = "Persona di contatto";
$l['ContactInfo']['PaymentMethod'] = "Metodo di pagamento";
$l['ContactInfo']['Cash'] = "Contanti";
$l['ContactInfo']['CreditCardNumber'] = "Numero carta di credito";
$l['ContactInfo']['CreditCardName'] = "Intestatario carta di credito";
$l['ContactInfo']['CreditCardVerificationNumber'] = "Codice di verifica carta di credito";
$l['ContactInfo']['CreditCardType'] = "Tipo carta di credito";
$l['ContactInfo']['CreditCardExpiration'] = "Scadenza carta di credito";

/* **********************************************************************************
 * Messages and alerts
 ***********************************************************************************/

$l['messages']['generatedPasswordsExportNotice'] = "Scaricare ora le credenziali generate. Questo download CSV monouso scade dopo %d minuti e contiene solo le password generate durante questa importazione.";
$l['messages']['noCheckAttributesForUser'] = "Non ci sono attributi di verifica (check) associati con questo utente";
$l['messages']['noReplyAttributesForUser'] = "Non ci sono attributi di risposta (reply) associati con questo utente";

$l['messages']['noCheckAttributesForGroup'] = "Non ci sono attributi di verifica (check) associati con questo gruppo";
$l['messages']['noReplyAttributesForGroup'] = "Non ci sono attributi di risposta (reply) associati con questo gruppo";

$l['messages']['nogroupdefinedforuser'] = "Non ci sono gruppi associati con questo utente";
$l['messages']['wouldyouliketocreategroup'] = "Si desidera crearne uno?";

$l['messages']['missingratetype'] = "errore: tipo tariffa da cancellare non trovato";
$l['messages']['missingtype'] = "errore: tipo non trovato";
$l['messages']['missingcardbank'] = "errore: cardbank non trovata";
$l['messages']['missingrate'] = "errore: tariffa non trovata";
$l['messages']['success'] = "successo";
$l['messages']['gisedit1'] = "Benvenuto, sei in modalità di Modifica";
$l['messages']['gisedit2'] = "Cancellare il marcatore corrente dalla mappa del database?";
$l['messages']['gisedit3'] = "Inserire il nome dell'HotSpot";
$l['messages']['gisedit4'] = "Aggiungere il marcatore corrente al database?";
$l['messages']['gisedit5'] = "Inserire il nome dell'Hotspot";
$l['messages']['gisedit6'] = "Inserire l'indirizzo MAC dell'Hotspot";

$l['messages']['gismain1'] = "Codice di registrazione GoogleMaps aggiornato correttamente";
$l['messages']['gismain2'] = "errore: impossibile aprire il file in scrittura:";
$l['messages']['gismain3'] = "Verificare i permessi sui file. Il file dovrebbe essere scrivibile dall'utente/gruppo del webserver";
$l['messages']['gisviewwelcome'] = "Benvenuto nelle mappe visuali Enginx";

$l['messages']['loginerror'] = <<<EOF
<h5>Impossibile accedere.</h5>
<p>Di solito questo accade per uno dei seguenti motivi:
    <ul>
        <li>username e/o password errati;</li>
        <li>un amministratore è già loggato<br>(è consentita solo un'istanza per volta);</li>
        <li>sembra che ci sia più di un utente 'administrator' nel database.</li>
    </ul>
</p>
EOF;
$l['messages']['noDataToShow'] = "nessun dato da mostrare";

/* **********************************************************************************
 * Help-page headers
 ***********************************************************************************/

$l['Intro']['configdashboard.php'] = "Impostazioni Dashboard";

$l['Intro']['paymenttypesmain.php'] = "Tipi di Pagamento";
$l['Intro']['paymenttypesdel.php'] = "Cancella Tipo di Pagamento";
$l['Intro']['paymenttypesedit.php'] = "Modifica Dettagli Tipo di Pagamento";
$l['Intro']['paymenttypesnew.php'] = "Nuovo Tipo di Pagamento";
$l['Intro']['paymenttypeslist.php'] = "Elenco Tipi di Pagamento";
$l['Intro']['paymentslist.php'] = "Elenco Pagamenti";
$l['Intro']['paymentsmain.php'] = "Pagamenti";
$l['Intro']['paymentsdel.php'] = "Cancella Pagamento";
$l['Intro']['paymentsedit.php'] = "Modifica Dettagli Pagamento";
$l['Intro']['paymentsnew.php'] = "Nuovo Pagamento";

$l['Intro']['billhistorymain.php'] = "Storico Fatturazione";
$l['Intro']['msgerrorpermissions.php'] = "Errore";

$l['Intro']['repnewusers.php'] = "Elenco Nuovi Utenti";

$l['Intro']['mngradproxys.php'] = "Gestione Proxy";
$l['Intro']['mngradproxysnew.php'] = "Nuovo Proxy";
$l['Intro']['mngradproxyslist.php'] = "Mostra Proxy";
$l['Intro']['mngradproxysedit.php'] = "Modifica Proxy";
$l['Intro']['mngradproxysdel.php'] = "Cancella Proxy";

$l['Intro']['mngradrealms.php'] = "Gestione Realms";
$l['Intro']['mngradrealmsnew.php'] = "Nuovo Realm";
$l['Intro']['mngradrealmslist.php'] = "Mostra Realm";
$l['Intro']['mngradrealmsedit.php'] = "Modifica Realm";
$l['Intro']['mngradrealmsdel.php'] = "Cancella Realm";

$l['Intro']['mngradattributes.php'] = "Gestione Attributi Fornitore";
$l['Intro']['mngradattributeslist.php'] = "Mostra Attributi Fornitore";
$l['Intro']['mngradattributesnew.php'] = "Nuovo Attributo Fornitore";
$l['Intro']['mngradattributesedit.php'] = "Modifica Attributi Fornitore";
$l['Intro']['mngradattributessearch.php'] = "Cerca Attributi";
$l['Intro']['mngradattributesdel.php'] = "Cancella Attributi Fornitore";
$l['Intro']['mngradattributesimport.php'] = "Importa Dizionario Fornitore";
$l['Intro']['mngimportusers.php'] = "Importa Utenti";

$l['Intro']['acctactive.php'] = "Accounting Record Attivi";
$l['Intro']['acctall.php'] = "Accounting di Tutti gli Utenti";
$l['Intro']['acctdate.php'] = "Ordina gli Accounting per Data";
$l['Intro']['accthotspot.php'] = "Accounting Hotspot";
$l['Intro']['acctipaddress.php'] = "Accounting IP";
$l['Intro']['accthotspotcompare.php'] = "Confronta Hotspot";
$l['Intro']['acctmain.php'] = "Pagina Accounting";
$l['Intro']['acctplans.php'] = "Accounting Piani";
$l['Intro']['acctnasipaddress.php'] = "Accounting IP NAS";
$l['Intro']['acctusername.php'] = "Accounting Utenti";
$l['Intro']['acctcustom.php'] = "Accounting personalizzati";
$l['Intro']['acctcustomquery.php'] = "Query Accounting Personalizzate";
$l['Intro']['acctmaintenance.php'] = "Manutenzione Record Accounting";
$l['Intro']['acctmaintenancecleanup.php'] = "Pulisci Connessioni Stantie";
$l['Intro']['acctmaintenancedelete.php'] = "Cancella Record Accounting";

$l['Intro']['billmain.php'] = "Pagina Fatturazione";
$l['Intro']['ratesmain.php'] = "Tariffe di Fatturazione";
$l['Intro']['billratesdate.php'] = "Accounting Prepagato Tariffe";
$l['Intro']['billratesdel.php'] = "Cancella voce Tariffa";
$l['Intro']['billratesedit.php'] = "Modifica Dettagli Tariffa";
$l['Intro']['billrateslist.php'] = "Tabella Tariffe";
$l['Intro']['billratesnew.php'] = "Nuova voce Tariffa";

$l['Intro']['paypalmain.php'] = "Transazioni PayPal";
$l['Intro']['billpaypaltransactions.php'] = "Transazioni PayPal";

$l['Intro']['billhistoryquery.php'] = "Storico Fatturazione";

$l['Intro']['billinvoice.php'] = "Fatture";
$l['Intro']['billinvoicedel.php'] = "Cancella Fattura";
$l['Intro']['billinvoiceedit.php'] = "Modifica Fattura";
$l['Intro']['billinvoicelist.php'] = "Elenco Fatture";
$l['Intro']['billinvoicereport.php'] = "Report Fatture";
$l['Intro']['billinvoicenew.php'] = "Nuova Fattura";

$l['Intro']['billplans.php'] = "Piani di Fatturazione";
$l['Intro']['billplansdel.php'] = "Cancella Piano";
$l['Intro']['billplansedit.php'] = "Modifica Dettagli Piano";
$l['Intro']['billplanslist.php'] = "Elenco Piani";
$l['Intro']['billplansnew.php'] = "Nuovo Piano";

$l['Intro']['billpos.php'] = "Punto Vendita";
$l['Intro']['billposdel.php'] = "Cancella Utente";
$l['Intro']['billposedit.php'] = "Modifica Utente";
$l['Intro']['billposlist.php'] = "Elenco Utenti";
$l['Intro']['billposnew.php'] = "Nuovo Utente";

$l['Intro']['giseditmap.php'] = "Modifica Modalità Mappa";
$l['Intro']['gismain.php'] = "Mappa GIS";
$l['Intro']['gisviewmap.php'] = "Vedi Modalità Mappa";

$l['Intro']['graphmain.php'] = "Grafici di utilizzo";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Confronto Utilizzo Traffico Totale";
$l['Intro']['graphsalltimelogins.php'] = "Totale Login";
$l['Intro']['graphsloggedusers.php'] = "Utenti Connessi";
$l['Intro']['graphsoveralldownload.php'] = "Download Utente";
$l['Intro']['graphsoveralllogins.php'] = "Login Utente";
$l['Intro']['graphsoverallupload.php'] = "Upload Utente";

$l['Intro']['rephistory.php'] = "Storico delle Azioni";
$l['Intro']['replastconnect.php'] = "Ultimo 50 tentativi di Connessione";
$l['Intro']['repstatradius.php'] = "Informazioni Daemons";
$l['Intro']['repstatserver.php'] = "Informazioni e Stato Server";
$l['Intro']['reponline.php'] = "Mostra Utenti Online";
$l['Intro']['replogssystem.php'] = "System Logfile";
$l['Intro']['replogsradius.php'] = "RADIUS Server Logfile";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS Logfile";
$l['Intro']['replogsboot.php'] = "Boot Logfile";
$l['Intro']['replogs.php'] = "Log Pagina";
$l['Intro']['rephb.php'] = "Heartbeat";
$l['Intro']['rephbdashboard.php'] = "Dashboard NAS daloRADIUS";
$l['Intro']['repbatch.php'] = "Batch";
$l['Intro']['mngbatchlist.php'] = "Elenco Sessioni Batch";
$l['Intro']['repbatchlist.php'] = "Elenco Utenti Batch";
$l['Intro']['repbatchdetails.php'] = "Dettagli Batch";

$l['Intro']['rephsall.php'] = "Mostra Hotspot";
$l['Intro']['repmain.php'] = "Report";
$l['Intro']['repstatus.php'] = "Stato Pagina";
$l['Intro']['reptopusers.php'] = "Top Utenti";
$l['Intro']['repusername.php'] = "Lista Utenti";

$l['Intro']['mngbatchdel.php'] = "Cancella sessioni batch";

$l['Intro']['mngdel.php'] = "Cancella Utente";
$l['Intro']['mngedit.php'] = "Modifica Dettagli Utente";
$l['Intro']['mnglistall.php'] = "Mostra Utenti";
$l['Intro']['mngmain.php'] = "Gestione Utenti e Hotspot";
$l['Intro']['mngbatch.php'] = "Crea Utenti batch";
$l['Intro']['mngnew.php'] = "Nuovo Utente";
$l['Intro']['mngnewquick.php'] = "Aggiungi Utente Velocemente";
$l['Intro']['mngsearch.php'] = "Cerca Utente";

$l['Intro']['mnghsdel.php'] = "Cancella Hotspot";
$l['Intro']['mnghsedit.php'] = "Modifica Dettagli Hotspot";
$l['Intro']['mnghslist.php'] = "Mostra Hotspot";
$l['Intro']['mnghsnew.php'] = "Nuovo Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Cancella Mappa Gruppo-Utente";
$l['Intro']['mngradusergroup.php'] = "Configurazione Gruppo-Utente";
$l['Intro']['mngradusergroupnew.php'] = "Nuova Mappa Gruppo-Utente";
$l['Intro']['mngradusergrouplist'] = "Mappa Gruppo-Utente nel Database";
$l['Intro']['mngradusergrouplistuser'] = "Mappa Gruppo-Utente nel Database";
$l['Intro']['mngradusergroupedit'] = "Modifica Mappa Gruppo-Utente per l'Utente:";

$l['Intro']['mngradippool.php'] = "Configurazione IP-Pool";
$l['Intro']['mngradippoolnew.php'] = "Nuovo IP-Pool";
$l['Intro']['mngradippoollist.php'] = "Mostra IP-Pool";
$l['Intro']['mngradippooledit.php'] = "Modifica IP-Pool";
$l['Intro']['mngradippooldel.php'] = "Cancella IP-Pool";

$l['Intro']['mngradnas.php'] = "Configurazione NAS";
$l['Intro']['mngradnasnew.php'] = "Nuovo Record NAS";
$l['Intro']['mngradnaslist.php'] = "Mostra NAS nel Database";
$l['Intro']['mngradnasedit.php'] = "MOdifica Record NAS";
$l['Intro']['mngradnasdel.php'] = "Cancella Record NAS";

$l['Intro']['mngradhunt.php'] = "Configurazione HuntGroup";
$l['Intro']['mngradhuntnew.php'] = "Nuovo Record HuntGroup";
$l['Intro']['mngradhuntlist.php'] = "Elenco HuntGroup nel Database";
$l['Intro']['mngradhuntedit.php'] = "Modifica Record HuntGroup";
$l['Intro']['mngradhuntdel.php'] = "Rimuovi Record HuntGroup";

$l['Intro']['mngradprofiles.php'] = "Configurazione Profili";
$l['Intro']['mngradprofilesedit.php'] = "Modifica Profili";
$l['Intro']['mngradprofilesduplicate.php'] = "Duplica Profilo";
$l['Intro']['mngradprofilesdel.php'] = "Cancella Profili";
$l['Intro']['mngradprofileslist.php'] = "Mostra Profili";
$l['Intro']['mngradprofilesnew.php'] = "Nuovo Profilo";

$l['Intro']['mngradgroups.php'] = "Configurazione Gruppi";

$l['Intro']['mngradgroupreplynew.php'] = "Nuova Mappa Group-Reply";
$l['Intro']['mngradgroupreplylist.php'] = "Nuova Mappa Group-Reply nel Database";
$l['Intro']['mngradgroupreplyedit.php'] = "Modifica Mappa Risposta per il Gruppo:";
$l['Intro']['mngradgroupreplydel.php'] = "Cancella Mappa Group-Reply";
$l['Intro']['mngradgroupreplysearch.php'] = "Cerca Group-Reply";

$l['Intro']['mngradgroupchecknew.php'] = "Nuova Mappa Group-Check";
$l['Intro']['mngradgroupchecklist.php'] = "Mappa Group-Check nel Database";
$l['Intro']['mngradgroupcheckedit.php'] = "Modifica Mappa Group-Check per il gruppo:";
$l['Intro']['mngradgroupcheckdel.php'] = "Cancella Mappa Group-Check";
$l['Intro']['mngradgroupchecksearch.php'] = "Cerca Mappa Group-Check";

$l['Intro']['configuser.php'] = "Configurazione Utente";
$l['Intro']['configmail.php'] = "Configurazione Mail";
$l['Intro']['configcrontab.php'] = "Configurazione attività ricorrenti";
$l['Intro']['configdb.php'] = "Configurazione Database";
$l['Intro']['configlang.php'] = "Configurazione Lingua";
$l['Intro']['configlogging.php'] = "Configurazione Logging";
$l['Intro']['configinterface.php'] = "Configurazione Interfaccia Web";
$l['Intro']['configmainttestuser.php'] = "Test Connettività Utente";
$l['Intro']['configmain.php'] = "Configurazione Database";
$l['Intro']['configmaint.php'] = "Manutenzione";
$l['Intro']['configmaintdisconnectuser.php'] = "Disconnetti Utente";
$l['Intro']['configbusiness.php'] = "Dettagli Commerciali";
$l['Intro']['configbusinessinfo.php'] = "Informazioni Commerciali";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupcreatebackups.php'] = "Crea Backup";
$l['Intro']['configbackupmanagebackups.php'] = "Gestisci Backup";

$l['Intro']['configoperators.php'] = "Configurazione Operatori";
$l['Intro']['configoperatorsdel.php'] = "Cancella Operatore";
$l['Intro']['configoperatorsedit.php'] = "Impostazioni Modifica Operatore";
$l['Intro']['configoperatorsnew.php'] = "Nuovo Operatore";
$l['Intro']['configoperatorslist.php'] = "Mostra Operatori";

$l['Intro']['login.php'] = "Login";

$l['Intro']['billpersecond.php'] = "Account Prepagati";

$l['Intro']['billprepaid.php'] = "Account Prepagati";

$l['Intro']['configbackupbackup.php'] = "Backup";

/* **********************************************************************************
 * Help-page content
 ***********************************************************************************/

$l['helpPage']['configdashboard'] = "Impostazioni Dashboard";

$l['helpPage']['repnewusers'] = "La seguente tabella elenca i nuovi utenti creati ogni mese.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "Elenca tutte le transazioni PayPal";
$l['helpPage']['billhistoryquery'] = "Elenca tutto lo storico di fatturazione per uno o più utenti";

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
$l['helpPage']['mngimportusers'] = <<<EOF
<h1 class="fs-5">Importa Utenti</h1>
<p>Usa questa pagina per creare più utenti RADIUS a partire da dati in formato CSV. Puoi selezionare il tipo di autenticazione, assegnare gli utenti importati a dei gruppi e, facoltativamente, associarli a un piano di fatturazione.</p>

<h2 class="fs-6">Importazione con username e password</h2>
<p>Per <strong>In base a username e password</strong>, ogni riga del CSV deve contenere almeno questi cinque campi:</p>
<pre><code>username,password,email,firstname,lastname</code></pre>
<p>È possibile aggiungere altri campi facoltativi dopo i primi cinque, come descritto nel campo Dati CSV.</p>

<h2 class="fs-6">Genera Password</h2>
<p>Imposta <strong>Genera Password</strong> su <strong>sì</strong> per generare una password casuale di 8 caratteri quando il campo password di una riga del CSV è vuoto. Se una password è già indicata, viene mantenuta. Quando questa opzione è impostata su <strong>no</strong>, le righe con password vuota vengono rifiutate.</p>
<p>Per richiedere una password generata, lasciare vuoto il secondo campo del CSV:</p>
<pre><code>user001,,user001@example.com,John,Doe</code></pre>
<p>Dopo un'importazione riuscita, usa <strong>Scarica CSV Password Generate</strong> per recuperare le credenziali generate durante quell'importazione. Le password fornite nel CSV originale non sono incluse. Il download è disponibile una sola volta e scade dopo cinque minuti.</p>
<p>Il valore generato viene memorizzato usando il Tipo di Password selezionato. Con un tipo di password con hash a senso unico, l'originale non può essere recuperato in seguito dall'attributo RADIUS memorizzato. Quando il login al portale è disabilitato, l'originale non viene copiato nel record delle informazioni utente; quando il login al portale è abilitato, viene memorizzato anche come password di login al portale.</p>
EOF;

$l['helpPage']['msgerrorpermissions'] = "Non si hanno i permessi per accedere a questa pagina.<br>Consultare il proprio amministratore di sistema.";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Per rimuovere un utente dal database si deve fornire il nome utente dell'account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";

// profiles help pages
$l['helpPage']['mngradprofilesnew'] = <<<EOF
<h2 class="fs-6">Nuovo Profilo</h2>
<p>Aggiungi un Profilo</p>
EOF;
$l['helpPage']['mngradprofileslist'] = <<<EOF
<h2 class="fs-6">Mostra Profili</h2>
<p>Mostra Profili</p>
EOF;
$l['helpPage']['mngradprofiles'] = <<<EOF
<h1 class="fs-5">Gestione Profili</h1>
<p>Gestisce i Profili Utente componendo un insieme di Attributi Risposta e Attributi Verifica</p>
<p>I Profili possono essere pensati come ta composizione di Gruppi Risposta e Gruppi Verifica.</p>
<h2 class="fs-6">Mostra Profili</h2>
<p>Mostra Profili</p>
<h2 class="fs-6">Nuovo Profilo</h2>
<p>Aggiungi un Profilo</p>
<h2 class="fs-6">Modifica Profilo</h2>
<p>Modifica un Profilo</p>
<h2 class="fs-6">Cancella Profilo</h2>
<p>Cancella un Profilo</p>
EOF;
$l['helpPage']['mngradprofilesedit'] = <<<EOF
<h2 class="fs-6">Modifica Profilo</h2>
<p>Modifica un Profilo</p>
EOF;
$l['helpPage']['mngradprofilesduplicate'] = <<<EOF
<h2 class="fs-6">Duplica Profilo</h2>
<p>Questa funzione permette di creare rapidamente un nuovo profilo a partire da uno esistente.
   È sufficiente selezionare il profilo da duplicare, indicare un nuovo nome per il profilo duplicato e fare clic sul pulsante "Duplica".
   Il nuovo profilo avrà gli stessi Attributi Reply e Attributi Check del profilo originale, così da poterlo modificare facilmente secondo le necessità.</p>
EOF;
$l['helpPage']['mngradprofilesdel'] = <<<EOF
<h2 class="fs-6">Cancella Profilo</h2>
<p>Cancella un Profilo</p>
EOF;
$l['helpPage']['mngradprofiles'] = <<<EOF
<h1 class="fs-5">Gestione Profili</h1>
<p>Gestisce i Profili Utente componendo un insieme di Attributi Risposta e Attributi Verifica</p>
<p>I Profili possono essere pensati come ta composizione di Gruppi Risposta e Gruppi Verifica.</p>
<h2 class="fs-6">Mostra Profili</h2>
<p>Mostra Profili</p>
<h2 class="fs-6">Nuovo Profilo</h2>
<p>Aggiungi un Profilo</p>
<h2 class="fs-6">Modifica Profilo</h2>
<p>Modifica un Profilo</p>
<h2 class="fs-6">Cancella Profilo</h2>
<p>Cancella un Profilo</p>
EOF;

// group check/reply help pages
$l['helpPage']['mngradgroupchecknew'] = <<<EOF
<h2 class="fs-6">Nuovo Gruppo Verifica</h2>
<p>Aggiunge una Mappa Group-Check</p>
EOF;
$l['helpPage']['mngradgroupreplynew'] = <<<EOF
<h2 class="fs-6">Nuovo Group Reply</h2>
<p>Aggiungi una Mappa Group-Reply</p>
EOF;

$l['helpPage']['mngradgroupchecklist'] = <<<EOF
<h2 class="fs-6">Mostra Group-Check</h2>
<p>Mostra Mappe Group-Check</p>
EOF;
$l['helpPage']['mngradgroupreplylist'] = <<<EOF
<h2 class="fs-6">Mostra Group Reply</h2>
<p>Mostra Mappa Group Reply</p>
EOF;

$l['helpPage']['mngradgroupchecksearch'] = <<<EOF
<h2 class="fs-6">Cerca Group-Check</h2>
<p>Cerca una Mappa Group-Check</p>
<p>Per usare caratteri jolly si deve scrivere il carattere % o si può utilizzare il più comune * per ragioni di convenienza daloRADIUS lo tradurrà in %</p>
EOF;
$l['helpPage']['mngradgroupreplysearch'] = <<<EOF
<h2 class="fs-6">Cerca Group Reply</h2>
<p>Cerca una Mappa Group Reply</p>
<p>Per usare un carattere jolly è possibile scrivere il carattere % che è familiare in SQL o si può utilizzare il più comune * per ragioni di convenienza e daloRADIUS lo tradurrà in %</p>
EOF;

$l['helpPage']['mngradgroupcheckedit'] = <<<EOF
<h2 class="fs-6">Modifica Group-Check</h2>
<p>Modifica una Mappe Group-Check</p>
EOF;
$l['helpPage']['mngradgroupreplyedit'] = <<<EOF
<h2 class="fs-6">Modifica Group Reply</h2>
<p>Modifica una Mappa Group Reply</p>
EOF;

$l['helpPage']['mngradgroupcheckdel'] = <<<EOF
<h2 class="fs-6">Cancella Group-Check</h2>
<p>Cancella una Mappa Group-Check</p>
EOF;
$l['helpPage']['mngradgroupreplydel'] = <<<EOF
<h2 class="fs-6">Cancella Group Reply</h2>
<p>Cancella una Mappa Group Reply</p>
EOF;

$l['helpPage']['mngradgroups'] = <<<EOF
<h1 class="fs-5">Gestione Gruppi</h1>
<p>Gestisce mappature Gruppi Risposta e Gruppi Verifica (radgroupreply/radgroupcheck tables).</p>
<h2 class="fs-6">Mostra Gruppi Risposta/Verifica</h2>
<p>Mostra Mappe Gruppi Risposta/Verifica</p>
<h2 class="fs-6">Cerca Gruppi Risposta/Verifica</h2>
<p>Cerca Mappe Gruppi Risposta/Verifica (è possibile usare caratteri jolly)</p>
<h2 class="fs-6">Nuovo Gruppo Risposta/Verifica</h2>
<p>Aggiunge una Mappa Gruppo Risposta/Verifica</p>
<h2 class="fs-6">Modifica Gruppo Risposta/Verifica</h2>
<p>Modifica una Mappa di un Gruppo Risposta/Verifica</p>
<h2 class="fs-6">Cancella Gruppo Risposta/Verifica</h2>
<p>Cancella una Mappa di un Gruppo Risposta/Verifica</p>
EOF;

$l['helpPage']['mngradgroups'] = <<<EOF
<h1 class="fs-5">Gestione Gruppi</h1>
<p>Gestisce mappature Gruppi Risposta e Gruppi Verifica (radgroupreply/radgroupcheck tables).</p>
<h2 class="fs-6">Mostra Gruppi Risposta/Verifica</h2>
<p>Mostra Mappe Gruppi Risposta/Verifica</p>
<h2 class="fs-6">Cerca Gruppi Risposta/Verifica</h2>
<p>Cerca Mappe Gruppi Risposta/Verifica (è possibile usare caratteri jolly)</p>
<h2 class="fs-6">Nuovo Gruppo Risposta/Verifica</h2>
<p>Aggiunge una Mappa Gruppo Risposta/Verifica</p>
<h2 class="fs-6">Modifica Gruppo Risposta/Verifica</h2>
<p>Modifica una Mappa di un Gruppo Risposta/Verifica</p>
<h2 class="fs-6">Cancella Gruppo Risposta/Verifica</h2>
<p>Cancella una Mappa di un Gruppo Risposta/Verifica</p>
EOF;

// ip pool help pages
$l['helpPage']['mngradippoolnew'] = <<<EOF
<h2 class="fs-6">Nuovo Pool IP</h2>
<p>Aggiungi un nuovo Indirizzo IP ad un Pool IP già configurato</p>
EOF;
$l['helpPage']['mngradippoollist'] = <<<EOF
<h2 class="fs-6">Mostra IP Pool</h2>
<p>Mostra un Pool IP Configurato e i suoi Indirizzi IP</p>
EOF;
$l['helpPage']['mngradippooledit'] = <<<EOF
<h2 class="fs-6">Modifica Pool IP</h2>
<p>Modifica un Indirizzo IP per un Pool IP già configurato</p>
EOF;
$l['helpPage']['mngradippooldel'] = <<<EOF
<h2 class="fs-6">Cancella Pool IP</h2>
<p>Cancella un Indirizzo IP per un Pool IP configurato</p>
EOF;

$l['helpPage']['mngradippool'] = <<<EOF
<h2 class="fs-6">Mostra Pool IP</h2>
<p>Mostra i Pools IP Configurati e gli Indirizzi IP Assegnati</p>
<h2 class="fs-6">Nuovo Pool IP</h2>
<p>Aggiungi un nuovo indirizzo IP ad un Pool IP già configurato</p>
<h2 class="fs-6">Modifica Pool IP</h2>
<p>Modifica un indirizzo IP per un Pool IP già configurato</p>
<h2 class="fs-6">Cancella Pool IP</h2>
<p>Cancella un Indirizzo IP per un Pool IP già configurato</p>
EOF;

$l['helpPage']['mngradippool'] = <<<EOF
<h2 class="fs-6">Mostra Pool IP</h2>
<p>Mostra i Pools IP Configurati e gli Indirizzi IP Assegnati</p>
<h2 class="fs-6">Nuovo Pool IP</h2>
<p>Aggiungi un nuovo indirizzo IP ad un Pool IP già configurato</p>
<h2 class="fs-6">Modifica Pool IP</h2>
<p>Modifica un indirizzo IP per un Pool IP già configurato</p>
<h2 class="fs-6">Cancella Pool IP</h2>
<p>Cancella un Indirizzo IP per un Pool IP già configurato</p>
EOF;

// nas help pages
$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "Per cancellare una voce nas ip/host dal database si deve fornire l'ip/host dell'account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

// huntgroup help pages
$l['helpPage']['mngradhunt'] = <<<EOF
<p>Prima di iniziare a lavorare con gli HuntGroup, leggere il <a href="https://wiki.freeradius.org/guide/SQL-Huntgroup-HOWTO" target="_blank">SQL_Huntgroup_HOWTO</a> sulla wiki di FreeRADIUS.</p>
<p>In particolare:</p>
<p><i>Individuare la sezione authorize nel file di configurazione radiusd.conf o sites-enabled/default e modificarla. All'inizio della sezione authorize, dopo il modulo preprocess, inserire queste righe:</i></p>
<pre>
update request {
    Huntgroup-Name := "%{sql:select groupname from radhuntgroup where nasipaddress=\"%{NAS-IP-Address}\"}"
}
</pre>
<p><i>Questo esegue una ricerca nella tabella radhuntgroup usando l'indirizzo IP come chiave per ottenere il nome del huntgroup. Aggiunge poi alla richiesta una coppia attributo/valore in cui il nome dell'attributo è Huntgroup-Name e il valore è quello restituito dalla query SQL. Se la query non trova nulla, il valore è la stringa vuota.</i></p>
EOF;

$l['helpPage']['mngradhuntdel'] = "Per rimuovere una voce huntgroup dal database si deve fornire l'ip/host e il port id del huntgroup";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

// hotspots help pages
$l['helpPage']['mnghsdel'] = "Per cancellare unn hotspot dal database si deve fornire il nome dell'hotspot<br>";
$l['helpPage']['mnghsedit'] = "Si possono modificare sotto i dettagli per l'hotspot<br>";
$l['helpPage']['mnghsnew'] = "Si possono riempire sotto i dettagli per il nuovo hotspot aggiunto dal database";
$l['helpPage']['mnghslist'] = "Lista di tutti gli hotspots nel database. Si possono utilizzare i links veloci per modificare o cancellare un hotspot dal database.";

$l['helpPage']['configuser'] = <<<EOF
<h2 class="fs-6">Impostazioni Utente</h2>
<p>Scegliere se consentire le password in chiaro nel database e quali caratteri sono consentiti per la creazione casuale di password e/o username.</p>
EOF;

$l['helpPage']['configdb_short'] = <<<EOF
<h2 class="fs-6">Impostazioni Database</h2>
<p>Configura il motore del database, le impostazioni di connessione e i nomi delle tabelle se non si usano quelli predefiniti.</p>
EOF;

$l['helpPage']['configdb'] = <<<EOF
<h1 class="fs-5">Impostazioni Database</h1>
<p>Configura il motore del database, le impostazioni di connessione, i nomi delle tabelle se quelle di default non vengono usate, e il tipo di criptazione delle passwords nel database.</p>
<h2 class="fs-6">Impostazioni Globali</h2>
<p>Motore Storage Database</p>
<h2 class="fs-6">Impostazioni Tabelle</h2>
<p>Se non usi lo schema di default di FreeRADIUS potresti voler cambiare i nomi delle tabelle</p>
<h2 class="fs-6">Impostazioni Avanzate</h2>
<p>Se intendi conservare le passwords degli utenti del database non in chiaro ma in modo criptato puoi scegliere tra MD5 o Crypt</p>
EOF;

$l['helpPage']['configdb'] = <<<EOF
<h1 class="fs-5">Impostazioni Database</h1>
<p>Configura il motore del database, le impostazioni di connessione, i nomi delle tabelle se quelle di default non vengono usate, e il tipo di criptazione delle passwords nel database.</p>
<h2 class="fs-6">Impostazioni Globali</h2>
<p>Motore Storage Database</p>
<h2 class="fs-6">Impostazioni Tabelle</h2>
<p>Se non usi lo schema di default di FreeRADIUS potresti voler cambiare i nomi delle tabelle</p>
<h2 class="fs-6">Impostazioni Avanzate</h2>
<p>Se intendi conservare le passwords degli utenti del database non in chiaro ma in modo criptato puoi scegliere tra MD5 o Crypt</p>
EOF;

$l['helpPage']['configlang'] = <<<EOF
<h2 class="fs-6">Impostazioni Lingua</h2>
<p>Configura la lingua dell'interfaccia.</p>
EOF;

$l['helpPage']['configcrontab'] = <<<EOF
<p>Questa sezione permette di configurare varie funzionalità di monitoraggio e avviso relative a sessioni e traffico del sistema.<br>
Permette di regolare parametri come gli intervalli di rilevamento delle sessioni stantie, le impostazioni di monitoraggio dei nodi, le soglie di monitoraggio del traffico utente
e le configurazioni degli avvisi via email. La sezione è organizzata in schede, ciascuna dedicata a un aspetto specifico delle capacità di monitoraggio e
avviso del sistema. In particolare, è possibile abilitare o disabilitare i controlli, impostare le soglie e configurare i destinatari email degli avvisi.
Inoltre è presente una scheda per visualizzare l'output della configurazione crontab del sistema,
che offre una panoramica delle attività pianificate relative a monitoraggio e manutenzione.</p>

<h3 class="fs-6">Sessioni stantie</h3>
<p>Interval e Grace sono usati per calcolare la soglia temporale. È importante assicurarsi che la soglia temporale sia impostata coerentemente
con l'<strong>Acct-Interim-Interval</strong>, in particolare che sia maggiore dell'Acct-Interim-Interval per evitare
la terminazione prematura delle sessioni.</p>
EOF;

$l['helpPage']['configlogging'] = <<<EOF
<h2 class="fs-6">Impostazioni Logging</h2>
<p>Configura le regole di logging e le facilitazioni</p>
<p>Assicurarsi che il nome del file che si specifica ha i permessi di scrittura del webserver</p>
EOF;

$l['helpPage']['configinterface'] = <<<EOF
<h2 class="fs-6">Impostazioni Interfaccia</h2>
<p>Configura l'impaginazine dell'interfaccia e il comportamento</p>
EOF;

$l['helpPage']['configmail'] = <<<EOF
<h2 class="fs-6">Configurazione Impostazioni Mail</h2>
<div id="help-text">
  <div class="help-item">
    <strong>Abilitato:</strong>
    <p>Scegliere se il client SMTP è abilitato o disabilitato per l'invio delle email.</p>
  </div>

  <div class="help-item">
    <strong>Indirizzo server SMTP:</strong>
    <p>Inserire l'indirizzo del proprio server SMTP.<br>È il server responsabile dell'invio delle email.</p>
  </div>

  <div class="help-item">
    <strong>Porta server SMTP:</strong>
    <p>Specificare il numero di porta usato dal server SMTP. Il valore predefinito è 25.</p>
  </div>

  <div class="help-item">
    <strong>Sicurezza SMTP:</strong>
    <p>Selezionare il protocollo di sicurezza per la connessione SMTP.<br>Scegliere 'none' per nessuna sicurezza o 'tls' per la cifratura TLS.</p>
  </div>

  <div class="help-item">
    <strong>Indirizzo email mittente:</strong>
    <p>Indicare l'indirizzo email che sarà usato come mittente delle email.</p>
  </div>

  <div class="help-item">
    <strong>Nome del mittente:</strong>
    <p>Inserire il nome associato all'indirizzo email del mittente.<br>Usare solo lettere, numeri e spazi.</p>
  </div>

  <div class="help-item">
    <strong>Prefisso oggetto:</strong>
    <p>Impostare un prefisso per l'oggetto delle email.<br>I caratteri consentiti includono lettere, numeri, spazi e parentesi quadre.</p>
  </div>

  <div class="help-item">
    <strong>Username e password SMTP:</strong>
    <p>Indicare username e password per l'autenticazione SMTP se richiesta.<br>Lasciare entrambi i campi vuoti per saltare l'autenticazione.</p>
  </div>

  <small><strong>Nota:</strong> ricordarsi di fare clic su "Applica" dopo le modifiche per salvare la configurazione.</small>
</div>
EOF;

$l['helpPage']['configmaint'] = <<<EOF
<h1 class="fs-5">Manutenzione</h1>
<h2 class="fs-6">Test Connettività Utente</h2>
<p>Manda una Access-Request al Server RADIUS per verificare se i dati dell'utente sono corretti</p>
<h2 class="fs-6">Disconnetti Utente</h2>
<p>Manda un PoD (Packet of Disconnect) o un pacchetto CoA (Change of Authority) al NAS server per disconnettere un utente e terminare la sua sessione nel NAS dato.</p>
EOF;

$l['helpPage']['configmainttestuser'] = <<<EOF
<h1 class="fs-5">Test Connettività Utente</h1>
<p>Manda un Access-Request al server RADIUS per verificare se le credenziali di un utente sono valide.</p>
<p>daloRADIUS usa l'utilità radclient per fare i test e ritorna i risultati del comando dopo che ha finito.</p>
<p>daloRADIUS conta sul fatto che il binario radclient sia disponibile nella variabile d'ambiente <code>\$PATH</code>. Se così non è, si devono effettuare delle modifiche al file <code>library/extensions/maintenance_radclient.php</code>.</p>
<p>Tenere presente che il test potrebbe impiegare un po' di tempo per finire (diversi secondi [10-20 secondi o più]) perché nel caso di errori radclient potrebbe ritrasmettere i pacchetti.</p>
<p>Nella scheda Avanzate è possibile regolare con precisione le opzioni per il test:</p>
<ul>
<li>Timeout - Aspetta 'timeout' secondi prima di riprovare (può essere un numero reale)</li>
<li>Retries - Dopo il timeout, riprova a mandare il pacchetto 'Retries' volte</li>
<li>Count - Manda ogni pacchetto 'count' volte</li>
<li>Requests - Manda 'num' pacchetti da un file in parallelo</li>
</ul>
EOF;

$l['helpPage']['configmaintdisconnectuser'] = <<<EOF
<h1 class="fs-5">Disconnetti Utente</h1>
<p>Manda un PoD (Packet of Disconnect) o un pacchetto CoA (Change of Authority) al server NAS per disconnettere un utente e terminare la sua sessione in un dato NAS.</p>
<p>Per terminare una sessione utente è richiesto che il NAS supporti il PoD o i tipi di pacchetti CoA, consultare il fornitore NAS o la documentazione. Inoltre, si richiede la conoscenza delle porte NAS per PoD o pacchetti CoA: i NAS più nuovi usano la porta 3799 mentre gli altri sono configurati sulla porta 1700.</p>
<p>daloRADIUS utilizza l'utilità radclient per effettuare i test e ritorna i risultati del comando dopo che questo ha finito.</p>
<p>daloRADIUS conta sul fatto che il binario radclient sia disponibile nella variabile d'ambiente <code>\$PATH</code>. Se così non è, si devono effettuare delle correzioni al file <code>library/extensions/maintenance_radclient.php</code>.</p>
<p>Si tenga presente che potrebbe metterci un po' (10-20 secondi o più) perché nel caso di errori radclient ritrasmetterà i pacchetti.</p>
<p>Nella scheda Avanzate è possibile regolare con precisione le opzioni per il test:</p>
<ul>
<li>Timeout - Aspetta 'timeout' secondi prima di riprovare (può essere un numero reale)</li>
<li>Retries - Se scade il timeout, riprova a mandare il pacchetto 'retries' volte</li>
<li>Count - Manda ogni pacchetto 'count' volte</li>
<li>Requests - Manda 'num' pacchetti da un file in parallelo</li>
</ul>
EOF;

$l['helpPage']['configoperators'] = "Configurazione Operatori";

$l['helpPage']['configoperatorsdel'] = "Per cancellare un operatore dal database si deve fornire il suo username.";
$l['helpPage']['configoperatorsedit'] = "Modifica i dettagli utente dell'operatore sotto";
$l['helpPage']['configoperatorsnew'] = "Si possono inserire sotto i dettagli per un utente operatore aggiunto al database";
$l['helpPage']['configoperatorslist'] = "Mostra tutti gli Operatori nel database";

$l['helpPage']['configbackup'] = "Esegui Backup";
$l['helpPage']['configbackupcreatebackups'] = "Crea Backup";
$l['helpPage']['configbackupmanagebackups'] = "Gestisci Backup";

$l['helpPage']['configmain'] = <<<EOF
<h1 class="fs-5">Impostazioni Globali</h1>
<h2 class="fs-6">Impostazioni Database</h2>
<p>Configura il motore del database, impostazioni di connessione, nomi di tabelle se quelle di default non sono usate, e il tipo di criptazione per le password nel database.</p>
<h2 class="fs-6">Impostazioni Lingua</h2>
<p>Configura il linguaggio dell'interfaccia.</p>
<h2 class="fs-6">Impostazioni Logging</h2>
<p>Configura le regole di logging e facilitazioni</p>
<h2 class="fs-6">Impostazioni Interfaccia</h2>
<p>Configura l'impaginazione e il comportamento dell'interfaccia</p>
<h1 class="fs-5">Configurazione Sotto-Categorie</h1>
<h2 class="fs-6">Manutenzione</h2>
<p>Manutenzione opzioni per il Test delle connessioni degli utenti o terminare le loro sessioni</p>
<h2 class="fs-6">Operatori</h2>
<p>Configura le Access Control List degli operatori (ACL)</p>
EOF;

$l['helpPage']['configmain'] = <<<EOF
<h1 class="fs-5">Impostazioni Globali</h1>
<h2 class="fs-6">Impostazioni Database</h2>
<p>Configura il motore del database, impostazioni di connessione, nomi di tabelle se quelle di default non sono usate, e il tipo di criptazione per le password nel database.</p>
<h2 class="fs-6">Impostazioni Lingua</h2>
<p>Configura il linguaggio dell'interfaccia.</p>
<h2 class="fs-6">Impostazioni Logging</h2>
<p>Configura le regole di logging e facilitazioni</p>
<h2 class="fs-6">Impostazioni Interfaccia</h2>
<p>Configura l'impaginazione e il comportamento dell'interfaccia</p>
<h1 class="fs-5">Configurazione Sotto-Categorie</h1>
<h2 class="fs-6">Manutenzione</h2>
<p>Manutenzione opzioni per il Test delle connessioni degli utenti o terminare le loro sessioni</p>
<h2 class="fs-6">Operatori</h2>
<p>Configura le Access Control List degli operatori (ACL)</p>
EOF;

// graphs help pages
$l['helpPage']['graphsalltimelogins'] = "Statistiche All-Time dei Login al server basate su una distribuzione su dato un periodo di tempo";
$l['helpPage']['graphsoveralldownload'] = "Disegna un grafico dei byte scaricati (download) verso il server";
$l['helpPage']['graphsoverallupload'] = "Disegna un grafico dei byte caricati (upload) verso il server";
$l['helpPage']['graphsoveralllogins'] = "Disegna un grafico dei tentativi di Login al server";
$l['helpPage']['graphsalltimetrafficcompare'] = "Statistiche All-Time di Traffico attraverso il server basate su una distribuzione su un dato periodo di tempo.";
$l['helpPage']['graphsloggedusers'] = <<<EOF
<h2 class="fs-6">Utenti Connessi</h2>
<p>Genera un grafico che mostra il numero di utenti connessi in un periodo specificato. Gli utenti possono essere filtrati per giorno, mese e anno per creare un grafico orario, oppure filtrati solo per mese e anno (selezionare "―" nel campo giorno) per rappresentare il minimo e il massimo di utenti connessi nel mese selezionato.</p>
EOF;

$l['helpPage']['graphmain'] = <<<EOF
<h1 class="fs-5">Grafici</h1>
<h2 class="fs-6">Riassunto Login/Hits</h2>
<p>Disegna un grafico dell'utilizzo per un utente specifico per un periodo dato. L'ammontare di logins (o 'hits' sul ) vengono mostrati in un grafico accompagnati da una lista.</p>
<h2 class="fs-6">Riassunto Statistiche Download</h2>
<p>Disegna un grafico con l'utilizzo di uno specifico utente per un dato periodo di tempo. L'ammontare dei dati scaricati dal client è il valore che viene calcolato. Il grafico è accompagnato da una lista</p>
<h2 class="fs-6">Riassunto Statistiche Upload</h2>
<p>Disegna un grafico con l'utilizzo di uno specifico utente per un dato periodo di tempo. L'ammontare di dati in Upload dal client è il valore che viene calcolato. Il grafico è accompagnato da una lista</p>
<h2 class="fs-6">Logins/Hits All time</h2>
<p>Disegna un grafico dei Login al server per un dato periodo di tempo.</p>
<h2 class="fs-6">Confronto Traffico All time</h2>
<p>Disegna un grafico delle statistiche di Download/Upload.</p>
EOF;

$l['helpPage']['rephistory'] = <<<EOF
Mostra tutte le attività eseguite sui campi gestione e fornisce informazioni su di loro<br>
Data di Creazione, Creazione Da, Aggiornamento Data e Aggiornamento dei campi con lo storico
EOF;
$l['helpPage']['replastconnect'] = "Mostra tutti tentativi di login al server RADIUS, sia quelli avvenuti con successo sia quelli falliti";
$l['helpPage']['replogsboot'] = "Controlla il log di Boot del Sistema Operativo - equivalente al lancio del comando dmesg.";
$l['helpPage']['replogsdaloradius'] = "Controlla il file di log di daloRADIUS.";
$l['helpPage']['replogsradius'] = "Controlla il file di log di FreeRADIUS.";
$l['helpPage']['replogssystem'] = "Controlla il file di log del Sistema Operativo.";
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "Fornisce i dettagli di uno specifico batch";

$l['helpPage']['replogs'] = <<<EOF
<h1 class="fs-5">Logs</h1>
<h2 class="fs-6">daloRADIUS Log</h2>
<p>Controlla il file di log di daloRADIUS.</p>
<h2 class="fs-6">RADIUS Log</h2>
<p>Controlla il file di log di FreeRADIUS - equivalente a /var/log/freeradius/radius.log o /usr/local/var/log/radius/radius.log. Potrebbero esserci altri possibili posti per i file di configurazione, se questo è il caso modificare le impostazioni.</p>
<h2 class="fs-6">Log di Sistema</h2>
<p>Controlla il file di log del Sistema Operativo - equivalente a /var/log/syslog or /var/log/message nella maggioranza delle piattaforme. Potrebbero esistere altri posti per i file di log, se questo è il caso modificare le impostazioni di configurazione.</p>
<h2 class="fs-6">Log di Boot</h2>
<p>Controlla il log del Boot del Sistema Operativo - equivalente a lanciare il comando dmesg.</p>
EOF;

$l['helpPage']['repmain'] = <<<EOF
<h1 class="fs-5">Rapporti Generali</h1>
<h2 class="fs-6">Utenti Online</h2>
<p>Fornisce una lista di tutti gli utenti che risultano online secondo la tabella di accounting del database. Il controllo che viene eseguito è per utenti dove non è stata impostata la voce di fine connessione (AcctStopTime). E' importante tenere presente che questi utenti potrebbero anche essere delle sessioni stantie (stale sessions) che succede quando il NASs per qualche ragione non riesce a mandare i pacchetti di accounting-stop, i quali comunicano la fine della sessione.</p>
<h2 class="fs-6">Ultimi Tentativi di Connessione</h2>
<p>Fornisce una lista di tutti i login con 'Access-Accept' (accesso accettato) e 'Access-Reject' (accesso rifiutato) per gli utenti.</p>
<p>Questi sono presi dalla tabella postauth del database che si richiede di definire nel file di configurazione di FreeRADIUS.</p>
<h2 class="fs-6">Top Utenti</h2>
<p>Fornisce un lista della Top N Utenti per consumo di banda e tempo di connessione</p>
<h1 class="fs-5">Rapporti Sotto-Categoria</h1>
<h2 class="fs-6">Logs</h2>
<p>Fornisce accesso ai file di log di daloRADIUS, FreeRADIUS, di Sistema e di Boot</p>
<h2 class="fs-6">Stato</h2>
<p>Fornisce informazioni sullo stato del server e sullo stato dei componenti RADIUS</p>
EOF;
$l['helpPage']['repstatradius'] = <<<EOF
Fornisce informazioni generali sul server stesso: Utilizzo CPU, Processi, Uptime, utilizzo Memoria, etc...
EOF;
$l['helpPage']['repstatserver'] = "Fornisce informazioni generali sul daemon FreeRadius e il Database server MySQL";
$l['helpPage']['repstatus'] = <<<EOF
<h1 class="fs-5">Stato</h1>
<h2 class="fs-6">Stato Server</h2>
<p>Fornisce informazioni generali sul server stesso: Utilizzo CPU, Processi, Uptime, Utilizzo Memoria, etc...</p>
<h2 class="fs-6">Stato RADIUS</h2>
<p>Fornisce informazioni generali sul daemon FreeRADIUS e sul daemon del Database server MySQL</p>
EOF;
$l['helpPage']['reptopusers'] = "Records per top utenti, che hanno guadagnato il più alto consumo di tempo di sessione o utilizzo di banda. Mostra utenti di categoria:";
$l['helpPage']['repusername'] = "Records trovati per l'utente:";
$l['helpPage']['reponline'] = <<<EOF
La seguente tabella mostra gli utenti che sono connessi in questo momento al sistema. E' possibile che ci siano connessioni stantie (stale connections),
che vuol dire che gli utenti si sono disconnessi ma il NAS non ha mandato o non è stato in grado di mandare un pacchetto di disconnessione (STOP accounting packet) al server RADIUS.
EOF;

$l['helpPage']['mnglistall'] = "Mostra utenti nel database";
$l['helpPage']['mngsearch'] = "Cerca utente: ";
$l['helpPage']['mngnew'] = "E' possibile riempire i dettagli di sotto per l'aggiunta di un nuovo utente al database<br>";
$l['helpPage']['mngedit'] = "Modifica dettagli utente sotto.<br>";
$l['helpPage']['mngdel'] = "Per cancellare una voce utente dal database si deve fornire l'utente dell'account<br>";
$l['helpPage']['mngbatch'] = <<<EOF
E' possibile riempire sotto i dettagli per il nuovo utente aggiunto al database.<br>
Si noti che queste impostazioni si applicheranno a tutti gli utenti che si stanno creando.<br>
EOF;
$l['helpPage']['mngnewquick'] = <<<EOF
Il seguente utente/scheda è di tipo prepagato.<br>
L'ammontare del tempo specificato in Time Credit (Credito di tempo) verrà usato come gli attributi radius Session-Timeout e Max-All-Session
EOF;

// accounting section
$l['helpPage']['acctusername'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni nel database per un particolare utente.
EOF;

$l['helpPage']['acctdate'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni tra due date per un particolare utente.
EOF;

$l['helpPage']['acctipaddress'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni che sono state avviate da un particolare indirizzo IP.
EOF;

$l['helpPage']['acctnasipaddress'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni che l'indirizzo NAS specifico ha gestito.
EOF;

$l['helpPage']['acctactive'] = <<<EOF
    Fornisce informazioni che potrebbero essere funzionali per tracciare utenti attivi o scaduti nel database
    in termini di utenti che hanno un attributo di Scadenza (Expiration) o un attributo di Massimo-numero-sessioni (Max-All-Session).
EOF;

$l['helpPage']['acctall'] = <<<EOF
    Fornisce informazioni complete sull'accounting per tutte le sessioni nel database.
EOF;

$l['helpPage']['acctcustom_short'] = <<<EOF
<h1 class="fs-5">Query Personalizzata</h1>
<p>Fornisce la query personalizzata più flessibile da eseguire sul database. È possibile regolare le impostazioni della query nella barra laterale sinistra a proprio vantaggio.</p>
EOF;

$l['helpPage']['acctcustom'] = <<<EOF
<h2 class="fs-6">Custom</h2>
<p>Fornisce la più flessibile query personalizzata da lanciare sul database.</p>
<p>E' possibile regolare la query modificando le impostazioni sulla barra a sinistra.</p>
<h2 class="fs-6">Tra le Date</h2>
<p>Imposta la data di inizio e di fine.</p>
<h2 class="fs-6">Dove</h2>
<p>Imposta il campo nel database che si desidera far corrispondere (come una chiave), scegliere se il valore da far corrispondere deve essere Uguale (=) o deve Contenere parte del valore che si cerca (come una regex). Se si sceglie di usare l'operatore Contiene non si devono aggiungere caratteri jolly della comune forma '*' il valore che si inserisce verrà automaticamente cercato in questa forma: *value* (o in stile mysql: %valore%).</p>
<h2 class="fs-6">Query Campi Accounting</h2>
<p>E' possibile scegliere quali campi si vogliono mostrare nella lista risultante.</p>
<h2 class="fs-6">Ordina per</h2>
<p>Scegliere per quale campo si desidera ordinare i risultati e il loro tipo (Ascendente o Discendente)</p>
EOF;

$l['helpPage']['acctcustomquery'] = "";

$l['helpPage']['acctplans'] = "";
$l['helpPage']['acctmain'] = <<<EOF
<h1 class="fs-5">Accounting Generale</h1>
<h2 class="fs-6">Accounting Utente</h2>
<p>Fornisce informazioni complete per tutte le sessioni nel database per un particolare utente.</p>
<h2 class="fs-6">Accounting IP</h2>
<p>Fornisce informazioni complete di accounting per tutte le sessioni che sono state avviate da un particolare indirizzo IP.</p>
<h2 class="fs-6">Accounting NAS</h2>
<p>Fornisce informazioni complete per tutte le sessioni che uno specifico indirizzo NAS ha gestito.</p>
<h2 class="fs-6">Accounting Date</h2>
<p>Fornisce informazioni complete di accounting per tutte le sessioni tra due date di un particolare utente.</p>
<h2 class="fs-6">Tutti i Records di Accounting</h2>
<p>Fornisce informazioni complete per tutte le sessioni di accounting nel database.</p>
<h2 class="fs-6">Records di Accounting Attivi</h2>
<p>Fornisce informazioni che potrebbero essere comode per tracciare utenti attivi o scaduti nel database in termini di utenti che hanno un attributo di scadenza (Expiration) o un attributo Max-All-session.</p>
<h1 class="fs-5">Sottocategoria Accounting</h1>
<h2 class="fs-6">Personalizzazioni</h2>
<p>Fornisce la query personalizzata più flessibile che si possa lanciare nel database.</p>
<h2 class="fs-6">Hotspots</h2>
<p>Fornisce informazioni sui differenti hotspot gestiti, confronti, e altre comode informazioni.</p>
EOF;

// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = <<<EOF
    Fornisce informazioni per tutte le sessioni che sono state avviate da uno specifico hotspot.
    Questa lista viene calcolata mostrando solo quei record che si trovano nella tabella radacct con il campo CalledStationId corrispondente all'indirizzo MAC nel database di gestione Hotspot.
EOF;
$l['helpPage']['accthotspotcompare'] = <<<EOF
<h1 class="fs-5">Confronto Hotspot</h1>
<h2 class="fs-6">Informazioni di base</h2>
<p>Fornisce informazioni di base sull'accounting per il confronto tra tutti gli hotspot attivi presenti nel database. Le informazioni fornite sono:</p>
<ul>
<li>Nome Hotspot - il nome dell'hotspot</li>
<li>Utenti Unici - utenti che hanno effettuato il login solo attraverso questo hotspot</li>
<li>Hit Totali - il totale dei login effettuati da questo hotspot (unici e non unici)</li>
<li>Tempo Medio - il tempo medio che l'utente ha trascorso in questo hotspot</li>
<li>Tempo Totale - il tempo trascorso da tutti gli utenti (cumulato) in questo hotspot</li>
</ul>
<h2 class="fs-6">Grafici</h2>
<p>Fornisce un grafico dei diversi confronti possibili:</p>
<ul>
<li>distribuzione degli utenti unici per hotspot</li>
<li>distribuzione delle hit per hotspot</li>
<li>distribuzione del tempo di utilizzo per hotspot</li>
</ul>
EOF;
$l['helpPage']['accthotspot'] = <<<EOF
<h2 class="fs-6">Accounting Hotspot</h2>
<p>Fornisce informazioni complete per tutte le sessioni che sono state avviate da uno specifico Hotspot.</p>
<h2 class="fs-6">Confronto Hotspot</h2>
<p>Fornisce informazioni di base sull'accounting per confronto tra gli hotspot attivi nel database. Fornisce un grafico dei differenti confronti possibili.</p>
EOF;

$l['helpPage']['acctmaintenance'] = <<<EOF
<h2 class="fs-6">Pulisci sessioni stantie (stale-sessions)</h2>
<p>Le sessioni stantie si formano quando il NAS non è capace di fornire un Accounting-STOP per la sessione utente, risultante in un record di sessione aperta che simula un utente connesso in un record utente (falso positivo).</p>
<h2 class="fs-6">Cancella Record accounting</h2>
<p>Cancellazione di un Record di accounting nel database. Potrebbe non essere saggio farlo o permettere di farlo ad altri utenti eccetto l'amministratore o un gruppo controllato.</p>
EOF;
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";

$l['helpPage']['giseditmap'] = <<<EOF
<h1 class="fs-5">Modifica Modalità Mappa</h1>
<p>In questa modalità si possono aggiungere o cancellare hotspot semplicemente cliccando su un luogo della mappa o su un hotspot (rispettivamente).</p>
<h2 class="fs-6">Aggiungere Hotspot</h2>
<p>Si deve semplicemente cliccare su un luogo vuoto della mappa: verrà chiesto di fornire il nome dell'hotspot e il suo indirizzo MAC. Questi sono i due elementi cruciali per identificare l'hotspot nella tabella di accounting. Fare attenzione a fornire l'indirizzo MAC corretto!</p>
<h2 class="fs-6">Cancellare Hotspot</h2>
<p>Cliccare semplicemente sull'icona di un hotspot e confermare la cancellazione dal database.</p>
EOF;
$l['helpPage']['gisviewmap'] = <<<EOF
<h1 class="fs-5">Vedi Modalità Mappa</h1>
<p>In questa modalità è possibile sfogliare gli hotspot così come sono visualizzati come icone nelle mappe fornite dal servizio GoogleMap.</p>
<p>Cliccando su un hotspot vengono forniti maggiori dettagli su di esso, come le informazioni di contatto e altri dettagli rilevanti.</p>
EOF;

$l['helpPage']['gismain'] = <<<EOF
<h1 class="fs-5">Informazioni Generali</h1>
<p>Le Mappature GIS forniscono mappature visuali del luogo dell'hotspot attraverso la mappa mondiale utilizzando la API di Google Maps.</p>
<p>Nella pagina Gestione è possibile aggiungere nuove voci hotspot al database dove c'è anche un campo chiamato Geolocation, questo è un valore numerico che la API di Google Maps usa per segnare (pin-point) il luogo esatto di un hotspot nella mappa.</p>
<h2 class="fs-6">Vengono fornite 2 Modalità Operative:</h2>
<p>Una è la modalità <b>Vedi MAPPA</b> che abilita il 'surfing' attraverso la mappa mondiale e vede i luoghi degli hotspots nel database, un'altra è - <b>Modifica MAPPA</b> - che è la modalità che si può utilizzare per creare hotspot in modo visuale semplicemente con un click sinistro sulla mappa o cancellando hotspot esistenti sempre con un click sinistro su una bandierina di un hotspot esistente.</p>
<p>Un'altra importante questione è che ogni computer sul network richiede un codice di registrazione unico che può essere ottenuto dalla pagina API di Google Maps fornendo l'indirizzo completo della directory ospitata dall'applicazione daloRADIUS sul server. Una volta ottenuto il codice da Google, si deve incollarlo nel campo di Registrazione (Registration box) e cliccare il bottone 'Register code' per salvarlo. A questo punto dovresti essere in grado di utilizzare i servizi di Google Maps.</p>
EOF;

$l['helpPage']['configbusiness'] = <<<EOF
<h1 class="fs-5">Informazioni Commerciali</h1>
<h2 class="fs-6">Contatti Commerciali</h2>
<p>Imposta il le informazioni del contatto commerciale (proprietari, titolo, indirizzo, telefono, etc)</p>
EOF;

$l['helpPage']['configbusinessinfo'] = "";

$l['helpPage']['configbackupbackup'] = "Esegui Backup";
