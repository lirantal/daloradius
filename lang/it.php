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

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'Rapporti, Fatturazione e Gestione RADIUS by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Nome Pool";
$l['all']['CalledStationId'] = "IdStazioneChiamata";
$l['all']['CallingStationID'] = "IDStazioneChiamata";
$l['all']['ExpiryTime'] = "Tempo di scadenza";
$l['all']['PoolKey'] = "Chiave Pool";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['VendorID'] = "ID Venditore";
$l['all']['VendorName'] = "Nome Venditore";
$l['all']['VendorAttribute'] = "Attributo Venditore";
$l['all']['RecommendedOP'] = "OP Raccomandato";
$l['all']['RecommendedTable'] = "Tabella Raccomandata";
$l['all']['RecommendedTooltip'] = "Consiglio Raccomandato";
$l['all']['RecommendedHelper'] = "Helper Raccomandato";
/********************************************************************************/

$l['all']['Compare'] = "Confronta";

$l['all']['Section'] = "Sezione";
$l['all']['Item'] = "Campo";

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
$l['all']['ProxySecret'] = "Secert Proxy";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";

$l['all']['NasID'] = "ID NAS";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "Diminutivo NAS";
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
$l['all']['Username'] = "Nome Utente";
$l['all']['Password'] = "Password";
$l['all']['PasswordType'] = "Tipo Password";
$l['all']['IPAddress'] = "Indirizzo IP";
$l['all']['Group'] = "Gruppo";
$l['all']['Groupname'] = "Nome Gruppo";
$l['all']['GroupPriority'] = "Priorità Gruppo";
$l['all']['CurrentGroupname'] = "Nome Gruppo Corrente";
$l['all']['NewGroupname'] = "Nuovo Nome Gruppo";
$l['all']['Priority'] = "Priorit&aacute;";
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
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Upload";
$l['all']['Download'] = "Download";
$l['all']['Termination'] = "Termine";
$l['all']['NASIPAddress'] = "Indirizzo IP NAS";
$l['all']['NASShortName'] = "NAS Short Name";
$l['all']['Action'] = "Azione";
$l['all']['UniqueUsers'] = "Utenti Unici";
$l['all']['TotalHits'] = "Total Hits";
$l['all']['AverageTime'] = "Tempo medio";
$l['all']['Records'] = "Registrazioni";
$l['all']['Summary'] = "Riassunto";
$l['all']['Statistics'] = "Statistiche";
$l['all']['Credit'] = "Credit";
$l['all']['Used'] = "Usato";
$l['all']['LeftTime'] = "Tempo Rimanente";
$l['all']['LeftPercent'] = "% di tempo rimasto";
$l['all']['TotalSessions'] = "Totale Sessioni";
$l['all']['LastLoginTime'] = "Tempo Ultimo Login";
$l['all']['TotalSessionTime'] = "Tempo totale di Sessione";
$l['all']['Rate'] = "Rate";
$l['all']['Billed'] = "Billed";
$l['all']['TotalUsers'] = "Totale Utenti";
$l['all']['TotalBilled'] = "Total Billed";
$l['all']['CardBank'] = "Card Bank";
$l['all']['Type'] = "Type";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "Indirizzo MAC";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN Code";
$l['all']['CreationDate'] = "Data di Creazione";
$l['all']['CreationBy'] = "Creato da";
$l['all']['UpdateDate'] = "Data di Aggiornamento";
$l['all']['UpdateBy'] = "Aggiornato da";

$l['all']['edit'] = "modifica";
$l['all']['del'] = "cancella";
$l['all']['groupslist'] = "lista gruppi";
$l['all']['TestUser'] = "Test Utente";
$l['all']['Accounting'] = "Contabilit&aacute;";
$l['all']['RADIUSReply'] = "Risposta RADIUS";

$l['all']['Disconnect'] = "Disconnetti";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Tentativi";
$l['all']['Count'] = "Conto";
$l['all']['Requests'] = "Richieste";

$l['all']['DatabaseHostname'] = "Hostname Database";
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
$l['all']['rates'] = "rates";
$l['all']['hotspots'] = "hotspots";

$l['all']['Month'] = "Mese";

$l['all']['BusinessName'] = "Nome Lavoro";
$l['all']['BusinessPhone'] = "Telefono Lavoro";
$l['all']['BusinessAddress'] = "Indirizzo Lavoro";
$l['all']['BusinessWebsite'] = "Sito Web Lavoro";
$l['all']['BusinessEmail'] = "Email Lavoro";
$l['all']['BusinessContactPerson'] = "Contatto Lavoro";

$l['all']['DBPasswordEncryption'] = "Tipo di criptazione Password DB";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

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
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "If all exact matching realms did not respond, we can try the";
$l['Tooltip']['realmNameTooltip'] = "Nome Realm";
$l['Tooltip']['realmTypeTooltip'] = "Imposta su radius per default";
$l['Tooltip']['realmSecretTooltip'] = "Realm RADIUS shared secret";
$l['Tooltip']['realmAuthhostTooltip'] = "Host autenticazione Realm";
$l['Tooltip']['realmAccthostTooltip'] = "Accounting host Realm";
$l['Tooltip']['realmLdflagTooltip'] = "Abilita per il load balancing. I valori abilitati sono 'fail_over' e 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Se togliere o no il suffisso realm";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "Esempio: Cisco<br/>" . "Il nome del Fornitore.";
$l['Tooltip']['typeTooltip'] = "Esempio: string<br/>" . "Il tipo variabile attributi (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Esempio: Framed-IPAddress<br/>" . "Il nome esatto dell'attributo.";

$l['Tooltip']['RecommendedOPTooltip'] = "Esempio: :=<br/>" . "L'operatore consigliato per quest'attributo. (uno tra: :=, ==, !=, ecc.)";
$l['Tooltip']['RecommendedTableTooltip'] = "Esempio: check<br/>" . "La tabella obiettivo consigliata. (uno tra: check, reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Esempio: L'indirizzo IP per l'utente";
$l['Tooltip']['RecommendedHelperTooltip'] = "La funzione di aiuto che sarà disponibile quando si aggiungerà questo attributo";



$l['Tooltip']['AttributeEdit'] = "Modifica Attributo";


$l['Tooltip']['UserEdit'] = "Modifica Utente";
$l['Tooltip']['HotspotEdit'] = "Modifica Hotspot";
$l['Tooltip']['EditNAS'] = "Modifica NAS";
$l['Tooltip']['RemoveNAS'] = "Cancella NAS";

$l['Tooltip']['EditUserGroup'] = "Modifica Gruppo Utente";
$l['Tooltip']['ListUserGroups'] = "Mostra Gruppi Utente";

$l['Tooltip']['EditProfile'] = "Modifica Profilo";

$l['Tooltip']['EditRealm'] = "Modifica Realm";
$l['Tooltip']['EditProxy'] = "Modifica Proxy";

$l['Tooltip']['EditGroup'] = "Modifica Gruppo";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "Se specifichi un valore, solo il record singolo che corrisponde contemporaneamente al nome del gruppo e al valore che hai specificato verrà rimosso. Se ometti il valore allora tutti i record di questo Gruppo verranno rimossi!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "Se specifichi un valore, solo il record singolo che corrisponde contemporaneamente al nome del gruppo e al valore che hai specificato verrà rimosso. Se ometti il valore allora tutti i record di questo Gruppo verranno rimossi!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(nome decrittivo)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "Se specifichi un gruppo allora solo il singolo record che corrisponde contemporaneamente all'username e al grupppo verrà rimosso.Se ometti il ggruppo allora tutti i record di questo utente verranno rimossi.!";


$l['Tooltip']['usernameTooltip'] = "Il nome utente esatto<br/>&nbsp;&nbsp;&nbsp;
                    così come l'utente userà connettersi al sistema";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "Le password sono sensibili alle maiuscole<br/>&nbsp;&nbsp;&nbsp;
                    in certi sistemi. Prestare attenzione";
$l['Tooltip']['groupTooltip'] = "L'utente verrà aggiunto a questo gruppo.<br/>&nbsp;&nbsp;&nbsp;
                    Assegnando un utente a un particolare gruppo<br/>&nbsp;&nbsp;&nbsp;
                    l'utente diventa soggetto agli attributi del gruppo";
$l['Tooltip']['macaddressTooltip'] = "Esempio: 00:aa:bb:cc:dd:ee<br/>&nbsp;&nbsp;&nbsp;
                    Il formato dell'indirizzo MAC dovrebbe essere lo stesso <br/>&nbsp;&nbsp;&nbsp;
                    come viene mandato dal NAS. La maggioranza delle volte è senza<br/>&nbsp;&nbsp;&nbsp;
                    altri caratteri.";
$l['Tooltip']['pincodeTooltip'] = "Esempio: khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    Questo è il codice pin esattamente come verrà inserito dall'utente.<br/>&nbsp;&nbsp;&nbsp;
                    Si possono usare caratteri alfanumerici, è sensibile alle maiuscole";
$l['Tooltip']['usernamePrefixTooltip'] = "Esempio: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    Questo prefisso dell'username verrà aggiunto a<br/>&nbsp;&nbsp;&nbsp;
                    l'username generato alla fine.";
$l['Tooltip']['instancesToCreateTooltip'] = "Esempio: 100<br/>&nbsp;&nbsp;&nbsp;
                    L'ammontare degli utenti random da creare<br/>&nbsp;&nbsp;&nbsp;
                    con il profilo specificato.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Esempio: 8<br/>&nbsp;&nbsp;&nbsp;
                    La lunghezza dei caratteri del nome utente<br/>&nbsp;&nbsp;&nbsp;
                    da creare. Si raccomandano 8-12 caratteri.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Esempio: 8<br/>&nbsp;&nbsp;&nbsp;
                    La lunghezza di caratteri delle password<br/>&nbsp;&nbsp;&nbsp;
                    da creare. Si raccomandano 8-12 caratteri.";


$l['Tooltip']['hotspotNameTooltip'] = "Esempio: Hotel Stratocaster<br/>&nbsp;&nbsp;&nbsp;
                    un nome comprensibile dell'hotspot<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "Esempio: 00aabbccddee<br/>&nbsp;&nbsp;&nbsp;
                    L'indirizzo MAC del NAS<br/>";

$l['Tooltip']['geocodeTooltip'] = "Esempio: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    Questo è il codice del luogo GoogleMaps utilizzato<br/>&nbsp;&nbsp;&nbsp;
                    per segnalare l'HotSpot/NAS sulla mappa (guardare GIS).";


/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/
$l['button']['ClearSessions'] = "Pulisci Sessioni";

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


$l['button']['BetweenDates'] = "Tra le Date:";
$l['button']['Where'] = "Dove";
$l['button']['AccountingFieldsinQuery'] = "Campi Accounting nella Query:";
$l['button']['OrderBy'] = "Ordina Per";
$l['button']['HotspotAccounting'] = "Accounting Hotspot";
$l['button']['HotspotsComparison'] = "Confronti Hotspots";

$l['button']['CleanupStaleSessions'] = "Pulisci Sessioni Stantie";
$l['button']['DeleteAccountingRecords'] = "Cancella Registrazioni Contabilizzate";

$l['button']['ListUsers'] = "Mostra Utenti";
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

$l['button']['ListUserGroup'] = "Mostra Mappa Gruppo-Utente";
$l['button']['ListUsersGroup'] = "Mostra Mappa Gruppi-Utente";
$l['button']['NewUserGroup'] = "Nuova Mappa Gruppo-Utente";
$l['button']['EditUserGroup'] = "Modifica Mappa Gruppo-Utente";
$l['button']['RemoveUserGroup'] = "Cancella Mappa Gruppo-Utente";

$l['button']['ListProfiles'] = "Mostra Profili";
$l['button']['NewProfile'] = "Nuovo Profilo";
$l['button']['EditProfile'] = "Modifica Profilo";
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
$l['button']['DateAccounting'] = "Data Accounting";
$l['button']['AllRecords'] = "Tutti i Record";
$l['button']['ActiveRecords'] = "Record Attivi";

$l['button']['OnlineUsers'] = "Utenti Online";
$l['button']['LastConnectionAttempts'] = "Ultimi Tentativi di Connessione";
$l['button']['TopUser'] = "Top User";
$l['button']['History'] = "History";

$l['button']['ServerStatus'] = "Stato Server";
$l['button']['ServicesStatus'] = "Stato Servizi";

$l['button']['daloRADIUSLog'] = "daloRADIUS Log";
$l['button']['RadiusLog'] = "Radius Log";
$l['button']['SystemLog'] = "System Log";
$l['button']['BootLog'] = "Boot Log";

$l['button']['UserLogins'] = "Login Utenti";
$l['button']['UserDownloads'] = "Download Utenti";
$l['button']['UserUploads'] = "Upload Utenti";
$l['button']['TotalLogins'] = "Totale Logins";
$l['button']['TotalTraffic'] = "Traffico Totale";

$l['button']['ViewMAP'] = "Vedi Mappa";
$l['button']['EditMAP'] = "Modifica Mappa";
$l['button']['RegisterGoogleMapsAPI'] = "Registra API GoogleMap";

$l['button']['DatabaseSettings'] = "Impostazioni Database";
$l['button']['LanguageSettings'] = "Impostazioni Lingua";
$l['button']['LoggingSettings'] = "Impostazioni Logging";
$l['button']['InterfaceSettings'] = "Impostazioni Interfaccia";

$l['button']['TestUserConnectivity'] = "Test Connettivit&aacute;";
$l['button']['DisconnectUser'] = "Disconnetti Utente";

$l['button']['ListOperators'] = "Mostra Operatori";
$l['button']['NewOperator'] = "Nuovo Operatore";
$l['button']['EditOperator'] = "Modifica Operatore";
$l['button']['RemoveOperator'] = "Cancella Operatore";

$l['button']['ProcessQuery'] = "Elabora Query";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['IPPoolInfo'] = "Info IP-Pool";

$l['title']['BusinessInfo'] = "Business Info";

$l['title']['CleanupRecords'] = "Pulisci Records";
$l['title']['DeleteRecords'] = "Cancella Records";

$l['title']['RealmInfo'] = "Realm Info";

$l['title']['ProxyInfo'] = "Proxy Info";

$l['title']['VendorAttribute'] = "Vendor Attribute";

$l['title']['AccountRemoval'] = "Cancellazione Account";
$l['title']['AccountInfo'] = "Info account";

$l['title']['ProfileInfo'] = "Info Profilo";

$l['title']['GroupInfo'] = "Info Gruppo";
$l['title']['GroupAttributes'] = "Attributi Gruppo";

$l['title']['NASInfo'] = "Info NAS ";
$l['title']['NASAdvanced'] = "NAS Avanzato";

$l['title']['UserInfo'] = "Info Utente";

$l['title']['Attributes'] = "Attributi";
$l['title']['ProfileAttributes'] = "Attributi Profilo";

$l['title']['HotspotInfo'] = "Info Hotspot";
$l['title']['HotspotRemoval'] = "Rimozione Hotspot";

$l['title']['ContactInfo'] = "Info Contatti";

$l['title']['Groups'] = "Gruppi";
$l['title']['RADIUSCheck'] = "Verifica Attributi";
$l['title']['RADIUSReply'] = "Risposta Attributi";

$l['title']['Settings'] = "Impostazioni";
$l['title']['DatabaseSettings'] = "Impostazioni Database";
$l['title']['DatabaseTables'] = "Tabelle Database";
$l['title']['AdvancedSettings'] = "Impostazioni Avanzate";

$l['title']['Advanced'] = "Avanzate";
$l['title']['Optional'] = "Opzionale";

/* ********************************************************************************** */


/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Effettuare il login";
$l['text']['LoginPlease'] = "Login";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "Nome";
$l['ContactInfo']['LastName'] = "Cognome";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Dipartimento";
$l['ContactInfo']['WorkPhone'] = "Telefono Lavoro";
$l['ContactInfo']['HomePhone'] = "Telefono Casa";
$l['ContactInfo']['MobilePhone'] = "Telefono Mobile";
$l['ContactInfo']['Notes'] = "Note";

$l['ContactInfo']['OwnerName'] = "Nome Proprietario";
$l['ContactInfo']['OwnerEmail'] = "Email Proprietario";
$l['ContactInfo']['ManagerName'] = "Nome Gestore";
$l['ContactInfo']['ManagerEmail'] = "Email Gestore";
$l['ContactInfo']['Company'] = "Azienda";
$l['ContactInfo']['Address'] = "Indirizzo";
$l['ContactInfo']['Phone1'] = "Telefono 1";
$l['ContactInfo']['Phone2'] = "Telefono 2";
$l['ContactInfo']['HotspotType'] = "Tipo Hotspot";
$l['ContactInfo']['CompanyWebsite'] = "Sito Web Azienda";
$l['ContactInfo']['CompanyPhone'] = "Telefono Azienda";
$l['ContactInfo']['CompanyEmail'] = "Email Azienda";
$l['ContactInfo']['CompanyContact'] = "Contatto Azienda";

/* ********************************************************************************** */



$l['Intro']['msgerrorpermissions.php'] = "Errore";

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


$l['Intro']['acctactive.php'] = "Accounting Record Attivi";
$l['Intro']['acctall.php'] = "Accounting di Tutti gli Utenti";
$l['Intro']['acctdate.php'] = "Ordina gli Accounting per Data";
$l['Intro']['accthotspot.php'] = "Accounting Hotspot";
$l['Intro']['acctipaddress.php'] = "Accounting IP";
$l['Intro']['accthotspotcompare.php'] = "Confronta Hotspot";
$l['Intro']['acctmain.php'] = "Pagina Accounting";
$l['Intro']['acctnasipaddress.php'] = "Accounting IP NAS";
$l['Intro']['acctusername.php'] = "Accounting Utenti";
$l['Intro']['acctcustom.php'] = "Accounting personalizzati";
$l['Intro']['acctcustomquery.php'] = "Query Accounting Personalizzate";
$l['Intro']['acctmaintenance.php'] = "Manutenzione Record Accounting";
$l['Intro']['acctmaintenancecleanup.php'] = "Pulisci Connessioni Stantie";
$l['Intro']['acctmaintenancedelete.php'] = "Cancella Record Accounting";

$l['Intro']['billmain.php'] = "Pagina Fatturazione";
$l['Intro']['billpersecond.php'] = "Account Prepagati";
$l['Intro']['billprepaid.php'] = "Account Prepagati";
$l['Intro']['billratesdel.php'] = "Cancella voce Tariffa";
$l['Intro']['billratesedit.php'] = "Modifica Dettagli Tariffa";
$l['Intro']['billrateslist.php'] = "Tabella Tariffe";
$l['Intro']['billratesnew.php'] = "Nuova voce Tariffa";

$l['Intro']['giseditmap.php'] = "Modifica Modalità Mappa";
$l['Intro']['gismain.php'] = "Mappa GIS";
$l['Intro']['gisviewmap.php'] = "Vedi Modalità Mappa";

$l['Intro']['graphmain.php'] = "Grafici di utilizzo";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Confronto Utilizzo Traffico Totale";
$l['Intro']['graphsalltimelogins.php'] = "Totale Login";
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
$l['Intro']['replogs.php'] = "Logs";

$l['Intro']['rephsall.php'] = "Mostra Hotspot";
$l['Intro']['repmain.php'] = "Reports Page";
$l['Intro']['repstatus.php'] = "Stato Pagina";
$l['Intro']['replogs.php'] = "Log Pagina";
$l['Intro']['reptopusers.php'] = "Top Utenti";
$l['Intro']['repusername.php'] = "Lista Utenti";

$l['Intro']['mngbatch.php'] = "Crea Utenti batch";
$l['Intro']['mngdel.php'] = "Cancella Utente";
$l['Intro']['mngedit.php'] = "Modifica Dettagli Utente";
$l['Intro']['mnglistall.php'] = "Mostra Utenti";
$l['Intro']['mngmain.php'] = "Gestione Utenti e Hotspot";
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

$l['Intro']['mngradprofiles.php'] = "Configurazione Profili";
$l['Intro']['mngradprofilesedit.php'] = "Modifica Profili";
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

$l['Intro']['configdb.php'] = "Configurazione Database";
$l['Intro']['configlang.php'] = "Configurazione Lingua";
$l['Intro']['configlogging.php'] = "Configurazione Logging";
$l['Intro']['configinterface.php'] = "Configurazione Interfaccia Web";
$l['Intro']['configmainttestuser.php'] = "Test Connettività Utente";
$l['Intro']['configmain.php'] = "Configurazione Database";
$l['Intro']['configmaint.php'] = "Manutenzione";
$l['Intro']['configbusiness.php'] = "Dettagli Commerciali";
$l['Intro']['configbusinessinfo.php'] = "Informazioni Commerciali";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupbackup.php'] = "Backup";
$l['Intro']['configmaintdisconnectuser.php'] = "Disconnetti Utente";
$l['button']['BusinessInformation'] = "Informazioni Commerciali";

$l['Intro']['configoperators.php'] = "Configurazione Operatori";
$l['Intro']['configoperatorsdel.php'] = "Cancella Operatore";
$l['Intro']['configoperatorsedit.php'] = "Impostazioni Modifica Operatore";
$l['Intro']['configoperatorsnew.php'] = "Nuovo Operatore";
$l['Intro']['configoperatorslist.php'] = "Mostra Operatori";

$l['Intro']['login.php'] = "Login";

$l['captions']['providebillratetodel'] = "Fornisce il tipo di tariffa che potresti voler rimuovere";
$l['captions']['detailsofnewrate'] = "E' possibile riempire di sotto i dettagli per la nuova tariffa";
$l['captions']['filldetailsofnewrate'] = "Riempire sotto i dettagli per la nuova tariffa";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/


$l['helpPage']['login'] = "";

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

$l['helpPage']['msgerrorpermissions'] = "Non si hanno i permessi per accedere alla pagina. <br/>
Consultare il proprio amministratore di sistema. <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Per rimuovere un utente dal database si deve fornire il nome utente dell'account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Gestione Profili</b> - Gestisce i Profili Utente componendo un insieme di Attributi Risposta e Attributi Verifica<br/>
I Profili possono essere pensati come ta composizione di Gruppi Risposta e Gruppi Verifica. <br/>
<h200><b>Mostra Profili</b></h200> - Mostra Profili <br/>
<h200><b>Nuovo Profilo</b></h200> - Aggiungi un Profilo<br/>
<h200><b>Modifica Profilo</b></h200> - Modifica un Profilo<br/>
<h200><b>Cancella Profilo </b></h200> - Cancella un Profilo <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>Modifica Profilo </b></h200> - Modifica un Profilo<br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>Cancella Profilo </b></h200> - Cancella un Profilo <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>Mostra Profili </b></h200> - Mostra Profili <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>Nuovo Profilo </b></h200> - Aggiungi un Profilo <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>Gestione Gruppi</b> - Gestisce mappature Gruppi Risposta e Gruppi Verifica (radgroupreply/radgroupcheck tables).<br/>
<h200><b>Mostra Gruppi Risposta/Verifica</b></h200> - Mostra Mappe Gruppi Risposta/Verifica<br/>
<h200><b>Cerca Gruppi Risposta/Verifica</b></h200> - Cerca Mappe Gruppi Risposta/Verifica (è possibile usare caratteri jolly) <br/>
<h200><b>Nuovo Gruppo Risposta/Verifica</b></h200> - Aggiunge una Mappa Gruppo Risposta/Verifica<br/>
<h200><b>Modifica Gruppo Risposta/Verifica</b></h200> - Modifica una Mappa di un Gruppo Risposta/Verifica<br/>
<h200><b>Cancella Gruppo Risposta/Verifica</b></h200> - Cancella una Mappa di un Gruppo Risposta/Verifica<br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>Nuovo Gruppo Verifica</b></h200> - Aggiunge una Mappa Group-Check<br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>Cancella Group-Check</b></h200> - Cancella una Mappa Group-Check<br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>Mostra Group-Check</b></h200> - Mostra Mappe Group-Check<br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>Modifica Group-Check</b></h200> - Modifica una Mappe Group-Check<br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>Cerca Group-Check</b></h200> - Cerca una Mappa Group-Check<br/>
Per usare caratteri jolly si deve scrivere il carattere % o si può utilizzare il più comune *
per ragioni di convenienza daloRADIUS lo tradurrà in %
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>Nuovo Group Reply </b></h200> - Aggiungi una Mappa Group-Reply<br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>Cancella Group Reply </b></h200> - Cancella una Mappa Group Reply <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>Mostra Group Reply </b></h200> - Mostra Mappa Group Reply <br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>Modifica Group Reply </b></h200> - Modifica una Mappa Group Reply<br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>Cerca Group Reply </b></h200> - Cerca una Mappa Group Reply <br/>
Per usare un carattere jolly è possibile scrivere il carattere % che è familiare in SQL o si può utilizzare il più comune *
per ragioni di convenienza e daloRADIUS lo tradurrà in %
";


$l['helpPage']['mngradippool'] = "
<h200><b>Mostra Pool IP </b></h200> - Mostra i Pools IP Configurati e gli Indirizzi IP Assegnati<br/>
<h200><b>Nuovo Pool IP </b></h200> - Aggiungi un nuovo indirizzo IP ad un Pool IP già configurato<br/>
<h200><b>Modifica Pool IP </b></h200> - Modifica un indirizzo IP per un Pool IP già configurato<br/>
<h200><b>Cancella Pool IP</b></h200> - Cancella un Indirizzo IP per un Pool IP già configurato<br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>Mostra IP Pool</b></h200> - Mostra un Pool IP Configurato e i suoi Indirizzi IP<br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>Nuovo Pool IP</b></h200> - Aggiungi un nuovo Indirizzo IP ad un Pool IP già configurato<br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>Modifica Pool IP</b></h200> - Modifica un Indirizzo IP per un Pool IP già configurato<br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>Cancella Pool IP</b></h200> - Cancella un Indirizzo IP per un Pool IP configurato<br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "Per cancellare una voce nas ip/host dal database si deve fornire l'ip/host dell'account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";



$l['helpPage']['mnghsdel'] = "Per cancellare unn hotspot dal database si deve fornire il nome dell'hotspot<br/>";
$l['helpPage']['mnghsedit'] = "Si possono modificare sotto i dettagli per l'hotspot<br/>";
$l['helpPage']['mnghsnew'] = "Si possono riempire sotto i dettagli per il nuovo hotspot aggiunto dal database";
$l['helpPage']['mnghslist'] = "Lista di tutti gli hotspots nel database. Si possono utilizzare i links veloci per modificare o cancellare un hotspot dal database.";

$l['helpPage']['configdb'] = "
<b>Impostazioni Database</b> - Configura il motore del database, le impostazioni di connessione, i nomi delle tabelle se quelle di
default non vengono usate, e il tipo di criptazione delle passwords nel database.<br/>
<h200><b>Impostazioni Globali</b></h200> - Motore Storage Database <br/>
<h200><b>Impostazioni Tabelle</b></h200> - Se non usi lo schema di default di FreeRADIUS potresti voler cambiare i nomi
delle tabelle<br/>
<h200><b>Impostazioni Avanzate</b></h200> - Se intendi conservare le passwords degli utenti del database non in
chiaro ma in modo criptato puoi scegliere tra MD5 o Crypt<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>Impostazioni Lingua</b></h200> - Configura la lingua dell'interfaccia.<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>Impostazioni Logging</b></h200> - Configura le regole di logging e le facilitazioni<br/>
Assicurarsi che il nome del file che si specifica ha i permessi di scrittura del webserver<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>Impostazioni Interfaccia</b></h200> - Configura l'impaginazine dell'interfaccia e il comportamento<br/>
";
$l['helpPage']['configmain'] = "
<b>Impostazioni Globali</b><br/>
<h200><b>Impostazioni Database</b></h200> - Configura il motore del database, impostazioni di connessione, nomi di tabelle se quelle di default non sono usate,
e il tipo di criptazione per le password nel database.<br/>
<h200><b>Impostazioni Lingua</b></h200> - Configura il linguaggio dell'interfaccia.<br/>
<h200><b>Impostazioni Logging</b></h200> - Configura le regole di logging e facilitazioni<br/>
<h200><b>Impostazioni Interfaccia</b></h200> - Configura l'impaginazione e il comportamento dell'interfaccia<br/>

<b>Configurazione Sotto-Categorie</b>
<h200><b>Manutenzione </b></h200> - Manutenzione opzioni per il Test delle connessioni degli utenti o terminare le loro sessioni<br/>
<h200><b>Operatori</b></h200> - Configura le Access Control List degli operatori (ACL)<br/>
";
$l['helpPage']['configbusiness'] = "
<b>Informazioni Commerciali</b><br/>
<h200><b>Contatti Commerciali</b></h200> - Imposta il le informazioni del contatto commerciale (proprietari, titolo, indirizzo, telefono, etc)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b>Manutenzione</b><br/>
<h200><b>Test Connettività Utente</b></h200> - Manda una Access-Request al Server RADIUS per verificare se i dati dell'utente sono corretti<br/>
<h200><b>Disconnetti Utente</b></h200> - Manda un PoD (Packet of Disconnect) o un pacchetto CoA (Change of Authority) al NAS server
per disconnettere un utente e terminare la sua sessione nel NAS dato.<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>Test Connettività Utente</b></h200> - Manda un Access-Request al server RADIUS per verificare se le credenziali di un utente sono valide.<br/>
daloRADIUS usa l'utilità radclient per fare test e ritorna i risultati del comando dopo che ha finito. <br/>
daloRADIUS conta sul fatto che il binario radclient sia disponibile nella variabile d'ambiente \$PATH variable, se così non è si devono effettuare delle modifiche al file library/extensions/maintenance_radclient.php.<br/><br/>

Tenere presente che il test potrebbe impiegare un pò di tempo per finire (diversi secondi [10-20 secondi or più]) perchè nel caso di errori radclient potrebbe ritrasmettere i pacchetti.

Nella zona Avanzate è possibile regolare con precisione le opsioni per il test:<br/>
Timeout - Aspetta 'timeout' secondi prima di riprovare (può essere un numero reale) <br/>
Retries - Dopo il timeout, riprova a mandare il pacchetto 'Retries' volte. <br/>
Count - Manda ogni pacchetto 'count' volte<br/>
Requests -  Manda 'num' pacchetti da un file in parallelo<br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>Disconnetti Utente</b></h200> - Manda un PoD (Packet of Disconnect) o un pacchetto CoA (Change of Authority) al server NAS per disconnettere un utente e terminare la sessione di un dato NAS.<br/>
Per terminare una sessione utente è richiesto che il NAS supporti il PoD o i tipi di pacchetti CoA, consultare il fornitore NAS o
la documentazione. Inoltre, si richiede la conoscenza delle porte NAS per PoD o pacchetti CoA, i NAS più nuovi usano la porta 3799
mentre gli altri sono configurati sulla porta 1700.

daloRADIUS utilizza l'utilità radclient per effettuare test e ritorna i risultati del comando dopo che questo ha finito. <br/>
daloRADIUS conta sul fato che il binaario radclient sia disponibile nella variabile \$PATH, se così non è, si devono
effettuare correzioni al file library/extensions/maintenance_radclient.php.<br/><br/>

Si tenga presente che potrebbe metterci un pò (10 - 20 secondi o più) perchè nel caso di errori
radclient ritrasmetterà i pacchetti.

Nella finestra Avanzate è possibile regolare con precisione le opzioni per i test:<br/>
Timeout - Aspetta 'timeout' secondi prima di riprovare (può essere un numero reale) <br/>
Retries - Se timeout, tenta di rimandare il pacchetto 'retries' volte. <br/>
Count - Manda ogni pacchetto 'count' volte <br/>
Requests -  Manda 'num' pacchetti da un file in parallelo <br/>


";
$l['helpPage']['configoperatorsdel'] = "Per cancellare un operatore dal database si deve fornire il suo username.";
$l['helpPage']['configoperatorsedit'] = "Modifica i dettagli utente dell'operatore sotto";
$l['helpPage']['configoperatorsnew'] = "Si possono inserire sotto i dettagli per un utente operatore aggiunto al database";
$l['helpPage']['configoperatorslist'] = "Mostra tutti gli Operatori nel database";
$l['helpPage']['configoperators'] = "Configurazione Operatori";
$l['helpPage']['configbackup'] = "Esegui Backup";
$l['helpPage']['configbackupbackup'] = "Esegui Backup";


$l['helpPage']['graphmain'] = "
<b>Grafici</b><br/>
<h200><b>Riassunto Login/Hits</b></h200> - Disegna un grafico dell'utilizzo per un utente specifico per un periodo dato.
L'ammontare di logins (o 'hits' sul ) vengono mostrati in un grafico accompagnati da una lista.<br/>
<h200><b>Riassunto Statistiche Download</b></h200> - Disegna un grafico con l'utilizzo di uno specifico utente per un dato periodo di tempo.
L'ammontare dei dati scaricati dal client è il valore che viene calcolato. Il grafico è accompagnato da una lista<br/>
<h200><b>Riassunto Statistiche Upload</b></h200> - Disegna un grafico con l'utilizzo di uno specifico utente per un dato periodo di tempo.
L'ammontare di dati in Upload dal client è il valore che viene calcolato. Il grafico è accompagnato da una lista<br/>
<br/>
<h200><b>Logins/Hits All time</b></h200> - Disegna un grafico dei Login al server per un dato periodo di tempo.<br/>
<h200><b>Confronto Traffico All time</b></h200> - Disegna un grafico delle statistiche di Download/Upload.
";
$l['helpPage']['graphsalltimelogins'] = "Statistiche All-Time dei Login al server basate su una distribuzione su dato un periodo di tempo";
$l['helpPage']['graphsalltimetrafficcompare'] = "Statistiche All-Time di Traffico attraverso il server basate su una distribuzione su un dato periodo di tempo.";
$l['helpPage']['graphsoveralldownload'] = "Disegna un grafico dei byte scaricati (download) verso il server";
$l['helpPage']['graphsoverallupload'] = "Disegna un grafico dei byte caricati (upload) verso il server";
$l['helpPage']['graphsoveralllogins'] = "Disegna un grafico dei tentativi di Login al server";

$l['helpPage']['rephistory'] = "Mostra tutte le attività eseguite sui campi gestione e fornisce informazioni su di loro<br/>
Data di Creazione, Creazione Da, Aggiornamento Data e Aggiornamento dei campi con lo storico";
$l['helpPage']['replastconnect'] = "Mostra tutti tentativi di login al server RADIUS, sia quelli avvenuti con successo sia quelli falliti";
$l['helpPage']['replogsboot'] = "Controlla il log di Boot del Sistema Operativo - equivalente al lancio del comando dmesg.";
$l['helpPage']['replogsdaloradius'] = "Controlla il file di log di daloRADIUS.";
$l['helpPage']['replogsradius'] = "Controlla il file di log di FreeRADIUS.";
$l['helpPage']['replogssystem'] = "Controlla il file di log del Sistema Operativo.";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS Log</b></h200> - Controlla il file di log di daloRADIUS's.<br/>
<h200><b>RADIUS Log</b></h200> - Controlla il file di log di FreeRADIUS - equivalente a /var/log/freeradius/radius.log o /usr/local/var/log/radius/radius.log.
Potrebbero esserci altri possibili posti per i file di configurazione, se questo è il caso modificare le impostazioni.<br/>
<h200><b>Log di Sistema</b></h200> - Controlla il file di log del Sistema Operativo - equivalente a /var/log/syslog or /var/log/message nella maggioranza delle piattaforme.
Potrebbero esistere altri posti per i file di log, se questo è il caso modificare le impostazioni di configurazione.<br/>
<h200><b>Log di Boot</b></h200> - Controlla il log del Boot del Sistema Operativo - equivalente a lanciare il comando dmesg.
";
$l['helpPage']['repmain'] = "
<b>Rapporti Generali</b><br/>
<h200><b>Utenti Online</b></h200> - Fornisce una lista di tutti gli utenti che risultano
online secondo la tabella di accounting del database. Il controllo che viene eseguito è per utenti dove non è stata impostata la voce di fine connessione (AcctStopTime). E' importante tenere presente che questi utenti potrebbero anche essere delle sessioni stantie (stale sessions)
che succede quando il NASs per qualche ragione non riesce a mandare i pacchetti di accounting-stop, i quali comunicano la fine della sessione.<br/>
<h200><b>Ultimi Tentativi di Connessione</b></h200> - Fornisce una lista di tutti i login con 'Access-Accept' (accesso accettato) e 'Access-Reject' (accesso rifiutato)
per gli utenti. <br/> Questi sono presi dalla tabella postauth del database che si richiede di definire nel file di configurazione di FreeRADIUS.<br/>
<h200><b>Top Utenti</b></h200> - Fornisce un lista della Top N Utenti per consumo di banda e tempo di connessione<br/><br/>
<b>Rapporti Sotto-Categoria</b><br/>
<h200><b>Logs</b></h200> - Fornisce accesso ai file di log di daloRADIUS, FreeRADIUS, di Sistema e di Boot<br/>
<h200><b>Stato</b></h200> - Fornisce informazioni sullo stato del server e sullo stato dei componenti RADIUS";
$l['helpPage']['repstatradius'] = "Fornisce informazioni generali sul server stesso: Utilizzo CPU, Processi, Uptime, utilizzo Memora, etc...
";
$l['helpPage']['repstatserver'] = "Fornisce informazioni generali sul daemon FreeRadius e il Database server MySQL";
$l['helpPage']['repstatus'] = "<b>Stato</b><br/>
<h200><b>Stato Server</b></h200> - Fornisce informazioni generali sul server stesso: Utilizzo CPU, Processi, Uptime, Utilizzo Memoria, etc...<br/>
<h200><b>Stato RADIUS</b></h200> - Fornisce informazioni generali sul daemon FreeRADIUS e sul daemon del Database server MySQL";
$l['helpPage']['reptopusers'] = "Records per top utenti, che hanno guadagnato il più alto consumo di tempo di sessione o utilizzo di banda. Mostra utenti di categoria:";
$l['helpPage']['repusername'] = "Records trovati per l'utente:";
$l['helpPage']['reponline'] = "
La seguente tabella mostra gli utenti che sono connessi in questo momento al sistema. E' possibile che ci siano connessioni stantie (stale connections),
che vuol dire che gli utenti si sono disconnessi ma il NAS non ha mandato o non è stato in grado di mandare un pacchetto di disconnessione (STOP accounting packet) al server RADIUS.
";


$l['helpPage']['mnglistall'] = "Mostra utenti nel database";
$l['helpPage']['mngsearch'] = "Cerca utente: ";
$l['helpPage']['mngnew'] = "E' possibile riempire i dettagli di sotto per l'aggiunta di un nuovo utente al database<br/>";
$l['helpPage']['mngedit'] = "Modifica dettagli utente sotto.<br/>";
$l['helpPage']['mngdel'] = "Per cancellare una voce utente dal database si deve fornire l'utente dell'account<br/>";
$l['helpPage']['mngbatch'] = "E' possibile riempire sotto i dettagli per il nuovo utente aggiunto al database.<br/>
Si noti che queste impostazioni si applicheranno a tutti gli utenti che si stanno creando.<br/>";
$l['helpPage']['mngnewquick'] = "Il seguente utente/scheda è di tipo prepagato.<br/>
L'ammontare del tempo specificato in Time Credit (Credito di tempo) verrà usato come gli attributi radius Session-Timeout e Max-All-Session";

// accounting section
$l['helpPage']['acctactive'] = "
    Fornisce informazioni che potrebbero essere funzionali per tracciare utenti attivi o scaduti nel database
    in termini di utenti che hanno un attributo di Scadenza (Expiration) o un attributo di Massimo-numero-sessioni (Max-All-Session).
<br/>
";
$l['helpPage']['acctall'] = "
    Fornisce informazioni complete sull'accounting per tutte le sessioni nel database.
<br/>
";
$l['helpPage']['acctdate'] = "
    Fornisce informazioni complete per tutte le sessioni tra due date per un particolare utente.
<br/>
";
$l['helpPage']['acctipaddress'] = "
    Fornisce informazioni complete per tutte le sessioni che sono state avviate da un particolare indirizzo IP.
<br/>
";
$l['helpPage']['acctmain'] = "
<b>Accounting Generale</b><br/>
<h200><b>Accounting Utente</b></h200> -
    Fornisce informazioni complete per tutte le sessioni nel database per un particolare utente.
<br/>
<h200><b>Accounting IP</b></h200> -
    Fornisce informazioni complete di accounting per tutte le sessioni che sono state avviate da un particolare indirizzo IP.
<br/>
<h200><b>Accounting NAS</b></h200> -
    Fornisce informazioni complete per tutte le sessioni che uno specifico indirizzo NAS ha gestito.
<br/>
<h200><b>Accounting Date</b></h200> -
    Fornisce informazioni complete di accounting per tutte le sessioni tra due date di un paricolare utente.
<br/>
<h200><b>Tutti i Records di Accounting</b></h200> -
    Fornisce informazioni complete per tutte le sessioni di accounting nel database.
<br/>
<h200><b>Records di Accounting Attivi</b></h200> -
    Fornisce informazioni che potrebbero essere comode per tracciare utenti attivi o scaduti nel database
    in termini di utenti che hanno un attributo di scadenza (Expiration) o un attributo Max-All-session.
<br/>

<br/>
<b>Sottocategoria Accounting</b><br/>
<h200><b>Personalizzazioni</b></h200> -
    Fornisce la query personalizzata più flessibile che si possa lanciare nel database.
<br/>
<h200><b>Hotspots</b></h200> -
    Fornisce informazioni sui differenti hotspot gestiti, confronti, e altre comode informazioni.
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    Fornisce informazioni complete per tutte le sessioni che l'indirizzo NAS specifico ha gestito.
<br/>
";
$l['helpPage']['acctusername'] = "
    Fornisce informazioni complete per tutte le sessioni nel database per un particolare utente.
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Fornisce informazioni per tutte le sessioni che sono state avviate da uno specifico hotspot.
    Questa lista viene calcolata mostrando solo quei record che si trovano nella tabella radacct con il campo CalledStationId corrispondente all'indirizzo MAC nel databasee di gestione Hotspot.
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    Fornisce informazioni di base sull'accounting per confronto tra tutti gli hotspot attivi trovati nel database.
    Informazioni di Accounting fornite: <br/><br/>
    Nome Hotspot - Il nome dell'Hotspot<br/>
    Utenti Unici - Utenti che hanno effettuato il login solamente attraverso questo hotspot<br/>
    Hits Totali - Il totale dei login che sono stati eseguiti da questo hotspot (unici e non unici) <br/>
    Tempo Medio - Il tempo medio che l'utente ha speso in questo hotspot <br/>
    Tempo Totale - Il tempo speso da tutti gli utenti (cumulato) in questo hotspot<br/>

<br/>
    Fornisce un grafico dei differenti confronti che si possono fare<br/>
    Grafici: <br/><br/>
    Distribuzione di utenti Unici per hotspot<br/>
    Distribuzione delle Hits per hotspot<br/>
    Distribuzione del tempo di utilizzo per hotspot<br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Accounting Hotspot</b></h200> -
    Fornisce informazioni complete per tutte le sessioni che sono state avviate da uno specifico Hotspot.
<br/>
<h200><b>Confronto Hotspot</b></h200> -
    Fornisce informazioni di base sull'accounting per confronto tra gli hotspot attivi nel database.
    Fornisce un grafico dei differenti confronti possibili.
<br/>
";
// accounting custom queries section
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    Fornisce la più flessibile query personalizzata da lanciare sul database.<br/>
    E' possibile regolare la query modificando le impostazioni sulla barra a sinistra.<br/>
<br/>
    <b> Tra le Date </b> - Imposta la data di inizio e di fine.
<br/>
    <b> Dove </b> - Imposta il campo nel database che si desidera far corrispondere (come una chiave), scegliere se il valore
    da far corrispondere deve essere Uguale (=) o deve Contenere parte del valore che si cerca (come una regex). Se si sceglie
    di usare l'operatore Contiene non si devono aggiungere caratteri jolly della comune forma '*'
    il valore che si inserisce verrà automaticamente cercato in questa forma: *value* (o in stile mysql: %valore%).
<br/>
    <b> Query Campi Accounting </b> - E' possibile scegliere quali campi si vogliono mostrare nella lista risultante.
<br/>
    <b> Ordina per </b> - Scegliere per quale campo si desidera ordinare i risultati e il loro tipo (Ascendente o Discendente)
<br/>
";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200><b>Pulisci sessioni stantie (stale-sessions)</b></h200> -
    Le sessioni stantie si formano quando il NAS non è capace di forntire un Accounting-STOP per la sessione utente <br/>
    risultante in un record di sessione aperta che simula un utente connesso in un record utente (falso positivo).
<br/>
<h200><b>Cancella Record accounting</b></h200> -
    Cancellazione di un Record di accounting nel database. Potrebbe non essere saggio farlo o permettere di farlo ad altri utenti eccetto l'amministratore o un gruppo controllato.
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
    Modifica Modalità Mappa - in questa modalità si può Aggiungere o Cancellare Hotspots semplicemente cliccando
    su un luogo della mappa o cliccando su un hotspot (rispettivamente).<br/><br/>
    <b> Aggiungere Hotspot </b> - Si deve semplicemente cliccare su un luogo vuoto della mappa, verrà chiesto di fornire
    il nome dell'hotspot e il suo indirizzo MAC. Questi sono i due elementi cruciali per identificare l'hotspot nella tabella di accounting. Fare attenzione a fornire l'indirizzo MAC corretto!
<br/><br/>
    <b> Cancellare Hotspot </b> - Cliccare semplicemente su una icona di un hotspot e confermare la cancellazione dal database.
<br/>
";
$l['helpPage']['gisviewmap'] = "
    Vedi Modalità Mappa - in questa modalità è possibile sfogliare gli Hotspot così come sono visualizzati come icone nelle mappe fornite dal servizio GoogleMap.<br/><br/>

    <b> Cliccare su un Hotspot </b> - Fornirà maggiore dettaglio sull'hotspot.
    Come le informazioni sui contatti per l'hotspot, e dettagli statistiche.
<br/>
";
$l['helpPage']['gismain'] = "
<b> Informazioni Generali</b>
Le Mappature GIS forniscono mappature visuali del luogo dell'hotspot attraverso la mappa mondiale utilizzando la API di Google Maps. <br/>
Nella pagina Gestione è possibile aggiungere nuove voci hotspot al database dove c'è anche un campo chiamato Geolocation, questo è un valore numerico che la API di Google Maps usa per segnare (pin-point) il luogo esatto di un hotspot nella mappa.<br/><br/>

<h200><b>Vengono fornite 2 Modalità Operative:</b></h200>
Una è la modalità <b>Vedi MAPPA</b> che abilita il 'surfing' attraverso la mappa mondiale
e vede i luoghi degli hotspots nel database, un'altra è - <b>Modifica MAPPA</b> - che è la modalità
che si può utilizzare per creare hotspot in modo visuale semplicemente con un click sinistro sulla mappa o cancellando
hotspot esistenti sempre con un click sinistro su una bandierina di un hotspot esistente.<br/><br/>

Un'altra importante questione è che ogni computer sul network richiede un codice di registrazione unico
che può essere ottenuto dalla pagina API di Google Maps fornendo l'indirizzo completo della directory ospitata
dall'applicazione daloRADIUS sul server. Una volta ottenuto il codice da Google, si deve incollarlo nel campo di Registrazione (Registration box) e cliccare il bottone 'Register code' per salvarlo.
A questo punto dovresti essere in grado di utilizzare i servizi di Google Maps. <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "Non ci sono attributi di verifica (check) associati con questo utente";
$l['messages']['noReplyAttributesForUser'] = "Non ci sono attributi di risposta (reply) associati con questo utente";

$l['messages']['noCheckAttributesForGroup'] = "Non ci sono attributi di verifica (check) associati con questo gruppo";
$l['messages']['noReplyAttributesForGroup'] = "Non ci sono attributi di risposta (reply) associati con questo gruppo ";

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
$l['messages']['gismain2'] = "errore: non si pu&oacute aprire il file in scrittura:";
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

$l['buttons']['savesettings'] = "Salva Impostazioni";
$l['buttons']['apply'] = "Applica";

$l['menu']['Home'] = "Home";
$l['menu']['Managment'] = "Gestione";
$l['menu']['Reports'] = "Rapporti";
$l['menu']['Accounting'] = "Contabilità";
$l['menu']['Billing'] = "Fatture";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Grafici";
$l['menu']['Config'] = "Config";
$l['menu']['Help'] = "Aiuto";

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

?>
