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
$l['all']['VendorID'] = "ID Venditore";
$l['all']['VendorName'] = "Nome Venditore";
$l['all']['VendorAttribute'] = "Attributo Venditore";
$l['all']['RecommendedOP'] = "OP Suggerito";
$l['all']['RecommendedTable'] = "Tabella Suggerita";
$l['all']['RecommendedTooltip'] = "Suggerimento";
$l['all']['RecommendedHelper'] = "Helper Suggerito";

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
$l['all']['ProxySecret'] = "Secret Proxy";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";

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
$l['all']['Username'] = "Nome Utente";
$l['all']['Password'] = "Password";
$l['all']['PasswordType'] = "Tipo Password";
$l['all']['IPAddress'] = "Indirizzo IP";
$l['all']['Group'] = "Gruppo";
$l['all']['Groupname'] = "Nome Gruppo";
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
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Upload";
$l['all']['Download'] = "Download";
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
$l['all']['Rate'] = "Rate";
$l['all']['Billed'] = "Billed";
$l['all']['TotalUsers'] = "Totale Utenti";
$l['all']['TotalBilled'] = "Total Billed";
$l['all']['Type'] = "Tipo";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "Indirizzo MAC";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "Codice PIN";
$l['all']['CreationDate'] = "Data di Creazione";
$l['all']['CreationBy'] = "Creato da";
$l['all']['UpdateDate'] = "Data di Aggiornamento";
$l['all']['UpdateBy'] = "Aggiornato da";

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
 * Login page text
 ***********************************************************************************/

$l['text']['LoginRequired'] = "Effettuare il login";
$l['text']['LoginPlease'] = "Login";

/* **********************************************************************************
 * Tooltips and form-field hints
 ***********************************************************************************/

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

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(nome descrittivo)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "Se specifichi un gruppo allora verrà rimosso solo il singolo record che corrisponde contemporaneamente all'username e al gruppo. Se ometti il gruppo allora verranno rimossi tutti i record di questo utente.";

$l['Tooltip']['usernameTooltip'] = <<<EOF
Il nome utente esatto<br/>&nbsp;&nbsp;&nbsp;
                    così come l'utente userà connettersi al sistema
EOF;
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = <<<EOF
Le password sono sensibili alle maiuscole<br/>&nbsp;&nbsp;&nbsp;
                    in certi sistemi. Prestare attenzione
EOF;
$l['Tooltip']['groupTooltip'] = <<<EOF
L'utente verrà aggiunto a questo gruppo.<br/>&nbsp;&nbsp;&nbsp;
                    Assegnando un utente a un particolare gruppo<br/>&nbsp;&nbsp;&nbsp;
                    l'utente diventa soggetto agli attributi del gruppo
EOF;
$l['Tooltip']['macaddressTooltip'] = <<<EOF
Esempio: 00:aa:bb:cc:dd:ee<br/>&nbsp;&nbsp;&nbsp;
                    Il formato dell'indirizzo MAC dovrebbe essere lo stesso <br/>&nbsp;&nbsp;&nbsp;
                    come viene mandato dal NAS. La maggioranza delle volte è senza<br/>&nbsp;&nbsp;&nbsp;
                    altri caratteri.
EOF;
$l['Tooltip']['pincodeTooltip'] = <<<EOF
Esempio: khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    Questo è il codice pin esattamente come verrà inserito dall'utente.<br/>&nbsp;&nbsp;&nbsp;
                    Si possono usare caratteri alfanumerici, è sensibile alle maiuscole
EOF;
$l['Tooltip']['usernamePrefixTooltip'] = <<<EOF
Esempio: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    Questo prefisso dell'username verrà aggiunto a<br/>&nbsp;&nbsp;&nbsp;
                    l'username generato alla fine.
EOF;
$l['Tooltip']['instancesToCreateTooltip'] = <<<EOF
Esempio: 100<br/>&nbsp;&nbsp;&nbsp;
                    L'ammontare degli utenti random da creare<br/>&nbsp;&nbsp;&nbsp;
                    con il profilo specificato.
EOF;
$l['Tooltip']['lengthOfUsernameTooltip'] = <<<EOF
Esempio: 8<br/>&nbsp;&nbsp;&nbsp;
                    La lunghezza dei caratteri del nome utente<br/>&nbsp;&nbsp;&nbsp;
                    da creare. Si raccomandano 8-12 caratteri.
EOF;
$l['Tooltip']['lengthOfPasswordTooltip'] = <<<EOF
Esempio: 8<br/>&nbsp;&nbsp;&nbsp;
                    La lunghezza di caratteri delle password<br/>&nbsp;&nbsp;&nbsp;
                    da creare. Si raccomandano 8-12 caratteri.
EOF;

$l['Tooltip']['hotspotNameTooltip'] = <<<EOF
Esempio: Hotel Stratocaster<br/>&nbsp;&nbsp;&nbsp;
                    un nome comprensibile dell'hotspot<br/>
EOF;

$l['Tooltip']['hotspotMacaddressTooltip'] = <<<EOF
Esempio: 00aabbccddee<br/>&nbsp;&nbsp;&nbsp;
                    L'indirizzo MAC del NAS<br/>
EOF;

$l['Tooltip']['geocodeTooltip'] = <<<EOF
Esempio: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    Questo è il codice del luogo GoogleMaps utilizzato<br/>&nbsp;&nbsp;&nbsp;
                    per segnalare l'HotSpot/NAS sulla mappa (guardare GIS).
EOF;

/* **********************************************************************************
 * Links and buttons
 ***********************************************************************************/

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

$l['button']['ViewMAP'] = "Vedi Mappa";
$l['button']['EditMAP'] = "Modifica Mappa";
$l['button']['RegisterGoogleMapsAPI'] = "Registra API GoogleMap";

$l['button']['DatabaseSettings'] = "Impostazioni Database";
$l['button']['LanguageSettings'] = "Impostazioni Lingua";
$l['button']['LoggingSettings'] = "Impostazioni Logging";
$l['button']['InterfaceSettings'] = "Impostazioni Interfaccia";

$l['button']['TestUserConnectivity'] = "Test connettività";
$l['button']['DisconnectUser'] = "Disconnetti Utente";

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

/* **********************************************************************************
 * Titles (fieldsets, tables, tabs)
 ***********************************************************************************/

$l['title']['IPPoolInfo'] = "Info IP-Pool";

$l['title']['BusinessInfo'] = "Info Commerciali";

$l['title']['CleanupRecords'] = "Pulisci Records";
$l['title']['DeleteRecords'] = "Cancella Records";

$l['title']['RealmInfo'] = "Info Realm";

$l['title']['ProxyInfo'] = "Info Proxy";

$l['title']['VendorAttribute'] = "Attributo Venditore";

$l['title']['AccountRemoval'] = "Cancellazione Account";
$l['title']['AccountInfo'] = "Info account";

$l['title']['ProfileInfo'] = "Info Profilo";

$l['title']['GroupInfo'] = "Info Gruppo";
$l['title']['GroupAttributes'] = "Attributi Gruppo";

$l['title']['NASInfo'] = "Info NAS";
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

/* **********************************************************************************
 * Contact info
 ***********************************************************************************/

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

/* **********************************************************************************
 * Messages and alerts
 ***********************************************************************************/

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

/* **********************************************************************************
 * Help-page headers
 ***********************************************************************************/

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

$l['Intro']['rephsall.php'] = "Mostra Hotspot";
$l['Intro']['repmain.php'] = "Report";
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

$l['Intro']['configoperators.php'] = "Configurazione Operatori";
$l['Intro']['configoperatorsdel.php'] = "Cancella Operatore";
$l['Intro']['configoperatorsedit.php'] = "Impostazioni Modifica Operatore";
$l['Intro']['configoperatorsnew.php'] = "Nuovo Operatore";
$l['Intro']['configoperatorslist.php'] = "Mostra Operatori";

$l['Intro']['login.php'] = "Login";

/* **********************************************************************************
 * Help-page content
 ***********************************************************************************/

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

$l['helpPage']['msgerrorpermissions'] = <<<EOF
Non si hanno i permessi per accedere alla pagina. <br/>
Consultare il proprio amministratore di sistema. <br/>
EOF;

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Per rimuovere un utente dal database si deve fornire il nome utente dell'account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";

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
$l['helpPage']['mngradprofilesdel'] = <<<EOF
<h2 class="fs-6">Cancella Profilo</h2>
<p>Cancella un Profilo</p>
EOF;
$l['helpPage']['mngradprofileslist'] = <<<EOF
<h2 class="fs-6">Mostra Profili</h2>
<p>Mostra Profili</p>
EOF;
$l['helpPage']['mngradprofilesnew'] = <<<EOF
<h2 class="fs-6">Nuovo Profilo</h2>
<p>Aggiungi un Profilo</p>
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

$l['helpPage']['mngradgroupchecknew'] = <<<EOF
<h2 class="fs-6">Nuovo Gruppo Verifica</h2>
<p>Aggiunge una Mappa Group-Check</p>
EOF;
$l['helpPage']['mngradgroupcheckdel'] = <<<EOF
<h2 class="fs-6">Cancella Group-Check</h2>
<p>Cancella una Mappa Group-Check</p>
EOF;

$l['helpPage']['mngradgroupchecklist'] = <<<EOF
<h2 class="fs-6">Mostra Group-Check</h2>
<p>Mostra Mappe Group-Check</p>
EOF;
$l['helpPage']['mngradgroupcheckedit'] = <<<EOF
<h2 class="fs-6">Modifica Group-Check</h2>
<p>Modifica una Mappe Group-Check</p>
EOF;
$l['helpPage']['mngradgroupchecksearch'] = <<<EOF
<h2 class="fs-6">Cerca Group-Check</h2>
<p>Cerca una Mappa Group-Check</p>
<p>Per usare caratteri jolly si deve scrivere il carattere % o si può utilizzare il più comune * per ragioni di convenienza daloRADIUS lo tradurrà in %</p>
EOF;

$l['helpPage']['mngradgroupreplynew'] = <<<EOF
<h2 class="fs-6">Nuovo Group Reply</h2>
<p>Aggiungi una Mappa Group-Reply</p>
EOF;
$l['helpPage']['mngradgroupreplydel'] = <<<EOF
<h2 class="fs-6">Cancella Group Reply</h2>
<p>Cancella una Mappa Group Reply</p>
EOF;
$l['helpPage']['mngradgroupreplylist'] = <<<EOF
<h2 class="fs-6">Mostra Group Reply</h2>
<p>Mostra Mappa Group Reply</p>
EOF;
$l['helpPage']['mngradgroupreplyedit'] = <<<EOF
<h2 class="fs-6">Modifica Group Reply</h2>
<p>Modifica una Mappa Group Reply</p>
EOF;
$l['helpPage']['mngradgroupreplysearch'] = <<<EOF
<h2 class="fs-6">Cerca Group Reply</h2>
<p>Cerca una Mappa Group Reply</p>
<p>Per usare un carattere jolly è possibile scrivere il carattere % che è familiare in SQL o si può utilizzare il più comune * per ragioni di convenienza e daloRADIUS lo tradurrà in %</p>
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
$l['helpPage']['mngradippoollist'] = <<<EOF
<h2 class="fs-6">Mostra IP Pool</h2>
<p>Mostra un Pool IP Configurato e i suoi Indirizzi IP</p>
EOF;
$l['helpPage']['mngradippoolnew'] = <<<EOF
<h2 class="fs-6">Nuovo Pool IP</h2>
<p>Aggiungi un nuovo Indirizzo IP ad un Pool IP già configurato</p>
EOF;
$l['helpPage']['mngradippooledit'] = <<<EOF
<h2 class="fs-6">Modifica Pool IP</h2>
<p>Modifica un Indirizzo IP per un Pool IP già configurato</p>
EOF;
$l['helpPage']['mngradippooldel'] = <<<EOF
<h2 class="fs-6">Cancella Pool IP</h2>
<p>Cancella un Indirizzo IP per un Pool IP configurato</p>
EOF;

$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "Per cancellare una voce nas ip/host dal database si deve fornire l'ip/host dell'account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

$l['helpPage']['mnghsdel'] = "Per cancellare unn hotspot dal database si deve fornire il nome dell'hotspot<br/>";
$l['helpPage']['mnghsedit'] = "Si possono modificare sotto i dettagli per l'hotspot<br/>";
$l['helpPage']['mnghsnew'] = "Si possono riempire sotto i dettagli per il nuovo hotspot aggiunto dal database";
$l['helpPage']['mnghslist'] = "Lista di tutti gli hotspots nel database. Si possono utilizzare i links veloci per modificare o cancellare un hotspot dal database.";

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
$l['helpPage']['configlogging'] = <<<EOF
<h2 class="fs-6">Impostazioni Logging</h2>
<p>Configura le regole di logging e le facilitazioni</p>
<p>Assicurarsi che il nome del file che si specifica ha i permessi di scrittura del webserver</p>
EOF;
$l['helpPage']['configinterface'] = <<<EOF
<h2 class="fs-6">Impostazioni Interfaccia</h2>
<p>Configura l'impaginazine dell'interfaccia e il comportamento</p>
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
$l['helpPage']['configbusiness'] = <<<EOF
<h1 class="fs-5">Informazioni Commerciali</h1>
<h2 class="fs-6">Contatti Commerciali</h2>
<p>Imposta il le informazioni del contatto commerciale (proprietari, titolo, indirizzo, telefono, etc)</p>
EOF;
$l['helpPage']['configbusinessinfo'] = "";
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
$l['helpPage']['configoperatorsdel'] = "Per cancellare un operatore dal database si deve fornire il suo username.";
$l['helpPage']['configoperatorsedit'] = "Modifica i dettagli utente dell'operatore sotto";
$l['helpPage']['configoperatorsnew'] = "Si possono inserire sotto i dettagli per un utente operatore aggiunto al database";
$l['helpPage']['configoperatorslist'] = "Mostra tutti gli Operatori nel database";
$l['helpPage']['configoperators'] = "Configurazione Operatori";
$l['helpPage']['configbackup'] = "Esegui Backup";
$l['helpPage']['configbackupbackup'] = "Esegui Backup";

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
$l['helpPage']['graphsalltimelogins'] = "Statistiche All-Time dei Login al server basate su una distribuzione su dato un periodo di tempo";
$l['helpPage']['graphsalltimetrafficcompare'] = "Statistiche All-Time di Traffico attraverso il server basate su una distribuzione su un dato periodo di tempo.";
$l['helpPage']['graphsoveralldownload'] = "Disegna un grafico dei byte scaricati (download) verso il server";
$l['helpPage']['graphsoverallupload'] = "Disegna un grafico dei byte caricati (upload) verso il server";
$l['helpPage']['graphsoveralllogins'] = "Disegna un grafico dei tentativi di Login al server";

$l['helpPage']['rephistory'] = <<<EOF
Mostra tutte le attività eseguite sui campi gestione e fornisce informazioni su di loro<br/>
Data di Creazione, Creazione Da, Aggiornamento Data e Aggiornamento dei campi con lo storico
EOF;
$l['helpPage']['replastconnect'] = "Mostra tutti tentativi di login al server RADIUS, sia quelli avvenuti con successo sia quelli falliti";
$l['helpPage']['replogsboot'] = "Controlla il log di Boot del Sistema Operativo - equivalente al lancio del comando dmesg.";
$l['helpPage']['replogsdaloradius'] = "Controlla il file di log di daloRADIUS.";
$l['helpPage']['replogsradius'] = "Controlla il file di log di FreeRADIUS.";
$l['helpPage']['replogssystem'] = "Controlla il file di log del Sistema Operativo.";
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
$l['helpPage']['mngnew'] = "E' possibile riempire i dettagli di sotto per l'aggiunta di un nuovo utente al database<br/>";
$l['helpPage']['mngedit'] = "Modifica dettagli utente sotto.<br/>";
$l['helpPage']['mngdel'] = "Per cancellare una voce utente dal database si deve fornire l'utente dell'account<br/>";
$l['helpPage']['mngbatch'] = <<<EOF
E' possibile riempire sotto i dettagli per il nuovo utente aggiunto al database.<br/>
Si noti che queste impostazioni si applicheranno a tutti gli utenti che si stanno creando.<br/>
EOF;
$l['helpPage']['mngnewquick'] = <<<EOF
Il seguente utente/scheda è di tipo prepagato.<br/>
L'ammontare del tempo specificato in Time Credit (Credito di tempo) verrà usato come gli attributi radius Session-Timeout e Max-All-Session
EOF;

// accounting section
$l['helpPage']['acctactive'] = <<<EOF
    Fornisce informazioni che potrebbero essere funzionali per tracciare utenti attivi o scaduti nel database
    in termini di utenti che hanno un attributo di Scadenza (Expiration) o un attributo di Massimo-numero-sessioni (Max-All-Session).
<br/>
EOF;
$l['helpPage']['acctall'] = <<<EOF
    Fornisce informazioni complete sull'accounting per tutte le sessioni nel database.
<br/>
EOF;
$l['helpPage']['acctdate'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni tra due date per un particolare utente.
<br/>
EOF;
$l['helpPage']['acctipaddress'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni che sono state avviate da un particolare indirizzo IP.
<br/>
EOF;
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
$l['helpPage']['acctnasipaddress'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni che l'indirizzo NAS specifico ha gestito.
<br/>
EOF;
$l['helpPage']['acctusername'] = <<<EOF
    Fornisce informazioni complete per tutte le sessioni nel database per un particolare utente.
<br/>
EOF;

// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = <<<EOF
    Fornisce informazioni per tutte le sessioni che sono state avviate da uno specifico hotspot.
    Questa lista viene calcolata mostrando solo quei record che si trovano nella tabella radacct con il campo CalledStationId corrispondente all'indirizzo MAC nel database di gestione Hotspot.
<br/>
EOF;
$l['helpPage']['accthotspotcompare'] = <<<EOF
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
EOF;
$l['helpPage']['accthotspot'] = <<<EOF
<h2 class="fs-6">Accounting Hotspot</h2>
<p>Fornisce informazioni complete per tutte le sessioni che sono state avviate da uno specifico Hotspot.</p>
<h2 class="fs-6">Confronto Hotspot</h2>
<p>Fornisce informazioni di base sull'accounting per confronto tra gli hotspot attivi nel database. Fornisce un grafico dei differenti confronti possibili.</p>
EOF;

// accounting custom queries section
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

