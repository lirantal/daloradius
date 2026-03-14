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

$year = date('Y');
if ($year > 2023) {
    $year = "2023-$year";
}
$l['all']['copyright2'] = <<<EOF
<a target="_blank" href="https://github.com/filippolauria/daloradius">daloRADIUS</a><br>
Copyright &copy; 2007-2022 Liran Tal, Filippo Lauria $year.
EOF;

$l['all']['Amount'] = "Betrag";
$l['all']['Balance'] = "Kontostand";
$l['all']['ClientName'] = "Kundenname";
$l['all']['Date'] = "Datum";
$l['all']['Download'] = "Download";
$l['all']['EndingDate'] = "Enddatum";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "Rechnung";
$l['all']['InvoiceStatus'] = "Rechnungsstatus";
$l['all']['InvoiceType'] = "Rechnungstyp";
$l['all']['IPAddress'] = "IP-Adresse";
$l['all']['Language'] = "Sprache";
$l['all']['NASIPAddress'] = "NAS IP-Adresse";
$l['all']['NewPassword'] = "Neues Passwort";
$l['all']['Password'] = "Passwort";
$l['all']['PaymentDate'] = "Zahlungsdatum";
$l['all']['StartingDate'] = "Startdatum";
$l['all']['StartTime'] = "Startzeit";
$l['all']['Statistics'] = "Statistiken";
$l['all']['Status'] = "Status";
$l['all']['StopTime'] = "Endzeit";
$l['all']['Termination'] = "Beendigung";
$l['all']['TotalBilled'] = "Gesamtbetrag";
$l['all']['TotalPayed'] = "Bezahlt";
$l['all']['TotalTime'] = "Gesamtzeit";
$l['all']['Upload'] = "Upload";
$l['all']['Username'] = "Benutzername";
$l['all']['CurrentPassword'] = "Aktuelles Passwort";
$l['all']['VerifyPassword'] = "Passwort bestätigen";

$l['all']['Global'] = "Gesamt";
$l['all']['Daily'] = "Täglich";
$l['all']['Weekly'] = "Wöchentlich";
$l['all']['Monthly'] = "Monatlich";
$l['all']['Yearly'] = "Jährlich";

$l['button']['Accounting'] = "Abrechnung";
$l['button']['ChangeAuthPassword'] = "Authentifizierungspasswort ändern";
$l['button']['ChangePortalPassword'] = "Portalpasswort ändern";
$l['button']['DateAccounting'] = "Nutzung nach Datum";
$l['button']['EditUserInfo'] = "Kontaktinformationen bearbeiten";
$l['button']['GenerateReport'] = "Bericht erstellen";
$l['button']['Graphs'] = "Diagramme";
$l['button']['Preferences'] = "Einstellungen";
$l['button']['ShowInvoice'] = "Rechnung anzeigen";

$l['button']['UserDownloads'] = "Download-Traffic";
$l['button']['UserLogins'] = "Anmeldeverlauf";
$l['button']['UserUploads'] = "Upload-Traffic";

$l['ContactInfo']['Address'] = "Adresse";
$l['ContactInfo']['City'] = "Stadt";
$l['ContactInfo']['Company'] = "Firma";
$l['ContactInfo']['Country'] = "Land";
$l['ContactInfo']['Department'] = "Abteilung";
$l['ContactInfo']['Email'] = "E-Mail";
$l['ContactInfo']['FirstName'] = "Vorname";
$l['ContactInfo']['HomePhone'] = "Telefon (privat)";
$l['ContactInfo']['LastName'] = "Nachname";
$l['ContactInfo']['MobilePhone'] = "Telefon (mobil)";
$l['ContactInfo']['Notes'] = "Notizen";
$l['ContactInfo']['State'] = "Bundesland/Region";
$l['ContactInfo']['WorkPhone'] = "Telefon (geschäftlich)";
$l['ContactInfo']['Zip'] = "Postleitzahl";

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Nutzung nach Datum</h2>
<p>Zeigt detaillierte Nutzungs-Informationen für alle Sitzungen eines Benutzers zwischen zwei ausgewählten Daten.</p>
EOF;
$l['helpPage']['acctmain'] = '<h1 class="fs-5">Allgemeine Nutzungsprotokollierung</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>Erstellt ein Diagramm über die Menge der heruntergeladenen Daten innerhalb eines bestimmten Zeitraums.<br>
Zusätzlich wird eine Tabelle mit den entsprechenden Werten angezeigt.</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>Erstellt ein Diagramm über die Menge der hochgeladenen Daten innerhalb eines bestimmten Zeitraums.<br>
Zusätzlich wird eine Tabelle mit den entsprechenden Werten angezeigt.</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>Erzeugt ein Diagramm über Ihre Anmeldeaktivität innerhalb eines bestimmten Zeitraums.<br>
Das Diagramm zeigt die Anzahl der Anmeldungen (oder Zugriffe auf das NAS) und wird durch eine Tabelle ergänzt.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Diagramme</h1>'
                            . $l['helpPage']['graphsoveralllogins']
                            . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>Sehr geehrte Benutzerin, sehr geehrter Benutzer,</p>
<p>Willkommen im Benutzerportal. Vielen Dank, dass Sie unseren Service nutzen!</p>

<p>Wenn Sie sich mit Ihrem Benutzernamen und Passwort anmelden, erhalten Sie Zugriff auf verschiedene Funktionen. Sie können beispielsweise Ihre Kontaktinformationen bearbeiten, persönliche Daten aktualisieren und Nutzungsstatistiken über grafische Auswertungen einsehen.</p>

<p>Der Schutz Ihrer Daten ist uns sehr wichtig. Alle Informationen werden sicher in unserer Datenbank gespeichert und sind nur für Sie und autorisierte Mitarbeiter zugänglich.</p>

<p>Wenn Sie Unterstützung benötigen oder Fragen haben, zögern Sie nicht, sich an unser Support-Team zu wenden. Wir helfen Ihnen jederzeit gerne weiter!</p>

<p>Mit freundlichen Grüßen<br/>
Ihr FiloRADIUS-Team</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Anmeldung fehlgeschlagen</h1>
<p>Wenn Sie Probleme haben, sich in Ihrem Benutzerkonto anzumelden, haben Sie möglicherweise einen falschen Benutzernamen und/oder ein falsches Passwort eingegeben. Bitte stellen Sie sicher, dass Sie Ihre Anmeldeinformationen richtig eingegeben haben und versuchen Sie es erneut.</p>
<p>Sollte das Problem weiterhin bestehen nachdem Sie Ihre Anmeldedaten überprüft haben, zögern Sie bitte nicht unser Support-Team zur Unterstützung zu kontaktieren. Wir sind immer für Sie da um Ihnen zu helfen den Zugang zu Ihrem Benutzerkonto wiederzuerlangen damit Sie unseren Service so schnell wie möglich wieder nutzen können</p>
EOF;

$l['helpPage']['prefmain'] = "In diesem Bereich können Sie Ihre <strong>Kontaktinformationen</strong> verwalten sowie die Passwörter für das Webportal und unsere Dienste ändern.";
$l['helpPage']['prefpasswordedit'] = "Verwenden Sie das folgende Formular, um Ihr Passwort zu ändern. Aus Sicherheitsgründen müssen Sie Ihr aktuelles Passwort eingeben und das neue Passwort zweimal bestätigen um Fehleingaben zu vermeiden.";
$l['helpPage']['prefuserinfoedit'] = "Mit dem folgenden Formular können Sie Ihre Kontaktinformationen aktualisieren. Sie können Ihren Vornamen und Nachnamen, Ihre E-Mail-Adresse, Telefonnummern und weitere Angaben ändern. Überprüfen Sie Ihre Änderungen um die Richtigkeit Ihrer aktualisierten Daten sicherzustellen.";

$l['Intro']['acctdate.php'] = "Nutzung nach Datum";
$l['Intro']['acctmain.php'] = "Nutzungsprotokollierung";
$l['Intro']['billinvoiceedit.php'] = "Rechnung anzeigen";
$l['Intro']['billinvoicereport.php'] = "Rechnungsbericht";
$l['Intro']['billmain.php'] = "Abrechnungsseite";
$l['Intro']['graphmain.php'] = "Nutzungsdiagramme";
$l['Intro']['graphsoveralldownload.php'] = "Downloads";
$l['Intro']['graphsoveralllogins.php'] = "Anmeldungen";
$l['Intro']['graphsoverallupload.php'] = "Uploads";
$l['Intro']['prefmain.php'] = "Einstellungen";
$l['Intro']['prefpasswordedit.php'] = "Passwort ändern";
$l['Intro']['prefuserinfoedit.php'] = "Benutzerdaten ändern";
$l['menu']['Accounting'] = "Nutzungsprotokollierung";
$l['menu']['Billing'] = "Abrechnung";
$l['menu']['Graphs'] = "Diagramme";
$l['menu']['Home'] = "Startseite";
$l['menu']['Preferences'] = "Einstellungen";
$l['menu']['Help'] = "Hilfe";


$l['text']['LoginPlease'] = "Bitte anmelden";
$l['text']['LoginRequired'] = "Login erforderlich";
$l['title']['ContactInfo'] = "Kontaktinformationen";
$l['title']['BusinessInfo'] = "Geschäftsinformationen";
$l['title']['Invoice'] = "Rechnung";
$l['title']['Items'] = "Positionen";
$l['Tooltip']['invoiceID'] = "Rechnungs-ID eingeben";
