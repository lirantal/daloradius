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
 * Description:    Italian language file for user portal application
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/it.php') !== false) {
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

$l['all']['Amount'] = "Importo";
$l['all']['Balance'] = "Saldo";
$l['all']['ClientName'] = "Nome Cliente";
$l['all']['Date'] = "Data";
$l['all']['Download'] = "Scarica";
$l['all']['EndingDate'] = "Data di Fine";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "Fattura";
$l['all']['InvoiceStatus'] = "Stato Fattura";
$l['all']['InvoiceType'] = "Tipo Fattura";
$l['all']['IPAddress'] = "Indirizzo IP";
$l['all']['Language'] = "Lingua";
$l['all']['NASIPAddress'] = "Indirizzo IP NAS";
$l['all']['NewPassword'] = "Nuova Password";
$l['all']['Password'] = "Password";
$l['all']['PaymentDate'] = "Data Pagamento";
$l['all']['StartingDate'] = "Data di Inizio";
$l['all']['StartTime'] = "Orario di Inizio";
$l['all']['Statistics'] = "Statistiche";
$l['all']['Status'] = "Stato";
$l['all']['StopTime'] = "Orario di Fine";
$l['all']['Termination'] = "Terminazione";
$l['all']['TotalBilled'] = "Totale Fatturato";
$l['all']['TotalPaid'] = "Totale Pagato";
$l['all']['TotalTime'] = "Tempo Totale";
$l['all']['Upload'] = "Carica";
$l['all']['Username'] = "Nome Utente";
$l['all']['CurrentPassword'] = "Password Attuale";
$l['all']['VerifyPassword'] = "Conferma Password";

$l['all']['Global'] = "Globale";
$l['all']['Daily'] = "Quotidianamente";
$l['all']['Weekly'] = "Settimanalmente";
$l['all']['Monthly'] = "Mensilmente";
$l['all']['Yearly'] = "Annualmente";
                         
$l['button']['Accounting'] = "Accounting";
$l['button']['ChangeAuthPassword'] = "Cambia Password di Autenticazione";
$l['button']['ChangePortalPassword'] = "Cambia Password del Portale";
$l['button']['DateAccounting'] = "Accounting per Data";
$l['button']['EditUserInfo'] = "Modifica Informazioni di Contatto";
$l['button']['GenerateReport'] = "Genera Report";
$l['button']['Graphs'] = "Grafici";
$l['button']['Preferences'] = "Preferenze";
$l['button']['ShowInvoice'] = "Mostra Fattura";

$l['button']['UserDownloads'] = "Traffico in Download";
$l['button']['UserLogins'] = "Storico Accessi";
$l['button']['UserUploads'] = "Traffico in Upload";

$l['ContactInfo']['Address'] = "Indirizzo";
$l['ContactInfo']['City'] = "Città";
$l['ContactInfo']['Company'] = "Organizzazione";
$l['ContactInfo']['Country'] = "Paese";
$l['ContactInfo']['Department'] = "Unità Operativa";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['FirstName'] = "Nome";
$l['ContactInfo']['HomePhone'] = "Telefono Casa";
$l['ContactInfo']['LastName'] = "Cognome";
$l['ContactInfo']['MobilePhone'] = "Telefono Cellulare";
$l['ContactInfo']['Notes'] = "Note";
$l['ContactInfo']['State'] = "Stato/Regione";
$l['ContactInfo']['WorkPhone'] = "Telefono Lavoro";
$l['ContactInfo']['Zip'] = "CAP";


$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Accounting per Data</h2>
<p>Fornisce informazioni dettagliate sull'accounting per tutte le sessioni tra due date specifiche per un utente particolare.</p>
EOF;
$l['helpPage']['acctmain'] = '<h1 class="fs-5">Accounting Generale</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>Genera un grafico mostrando la quantità di dati che hai scaricato in un determinato periodo di tempo.<br>
Il grafico è accompagnato da un elenco tabellare.</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>Genera un grafico mostrando la quantità di dati che hai caricato in un determinato periodo di tempo.<br>
Il grafico è accompagnato da un elenco tabellare.</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>Genera un grafico mostrando l'attività di login che hai effettuato in un determinato periodo di tempo.<br>
Il grafico mostra il numero di login (o 'hits' al NAS) ed è accompagnato da un elenco tabellare.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Grafici</h1>'
                            . $l['helpPage']['graphsoveralldownload'] . $l['helpPage']['graphsoveralllogins']
                            . $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>Gentile utente,</p>
<p>Benvenuto nel Portale Utenti. Siamo felici che tu ti sia unito a noi!</p>

<p>Accedendo con il tuo nome utente e password, potrai accedere a una vasta gamma di funzionalità. Ad esempio, puoi facilmente modificare le impostazioni dei tuoi contatti, aggiornare le tue informazioni personali e visualizzare alcuni dati storici tramite grafici visivi.</p>

<p>Prendiamo seriamente la tua privacy e la tua sicurezza, quindi puoi stare tranquillo che tutti i tuoi dati sono archiviati in modo sicuro nel nostro database e sono accessibili solo a te e al nostro personale autorizzato.</p>

<p>Se hai bisogno di assistenza o hai domande, non esitare a contattare il nostro team di supporto. Siamo sempre felici di aiutarti!</p>

<p>Cordiali saluti,<br/>
Lo Staff di FiloRADIUS.</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Impossibile Effettuare il Login</h1>
<p>Se stai avendo problemi ad accedere al tuo account, è probabile che tu abbia inserito un nome utente e/o password errati. Assicurati di aver inserito correttamente le tue credenziali di accesso e riprova.</p>
<p>Se continui ad avere difficoltà ad accedere anche dopo aver verificato le tue informazioni, non esitare a contattare il nostro team di supporto per assistenza. Siamo sempre qui per aiutarti a recuperare l'accesso al tuo account e tornare a utilizzare i nostri servizi il prima possibile.</p>
EOF;


$l['helpPage']['prefmain'] = "In questa sezione puoi gestire le tue <strong>informazioni di contatto</strong>, nonché le password di accesso al portale web e al nostro servizio.";
$l['helpPage']['prefpasswordedit'] = "Utilizza il modulo sottostante per cambiare la tua password. Per motivi di sicurezza, ti verrà richiesto di inserire la tua vecchia password e, per evitare errori, di inserire due volte la nuova.";
$l['helpPage']['prefuserinfoedit'] = "Utilizza il modulo sottostante per aggiornare le tue informazioni di contatto. Puoi modificare il tuo nome, cognome, indirizzo email, numeri di telefono e altri dettagli secondo le tue necessità. Assicurati di rivedere le modifiche prima di salvare per garantire l'accuratezza delle tue informazioni aggiornate.";

$l['Intro']['acctdate.php'] = "Accounting per Data";
$l['Intro']['acctmain.php'] = "Pagina di Accounting";
$l['Intro']['billinvoiceedit.php'] = "Visualizzazione Fattura";
$l['Intro']['billinvoicereport.php'] = "Report delle Fatture";
$l['Intro']['billmain.php'] = "Pagina di Fatturazione";
$l['Intro']['graphmain.php'] = "Grafici di Utilizzo";
$l['Intro']['graphsoveralldownload.php'] = "Download Utenti";
$l['Intro']['graphsoveralllogins.php'] = "Accessi Utenti";
$l['Intro']['graphsoverallupload.php'] = "Upload Utenti";
$l['Intro']['prefmain.php'] = "Pagina delle Preferenze";
$l['Intro']['prefpasswordedit.php'] = "Cambio Password";
$l['Intro']['prefuserinfoedit.php'] = "Modifica Informazioni Utente";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Fatturazione";
$l['menu']['Graphs'] = "Grafici";
$l['menu']['Home'] = "Home";
$l['menu']['Preferences'] = "Preferenze";
$l['menu']['Help'] = "Aiuto";

$l['text']['LoginPlease'] = "Effettua il Login";
$l['text']['LoginRequired'] = "Accesso Richiesto";
$l['title']['ContactInfo'] = "Informazioni di Contatto";
$l['title']['OtherInfo'] = "Altre Informazioni";
$l['title']['Invoice'] = "Fattura";
$l['title']['Items'] = "Elementi";
$l['Tooltip']['invoiceID'] = "Inserisci l'ID della fattura";
