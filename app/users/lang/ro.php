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
 * Description:    Romanian language file for user portal application
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/ro.php') !== false) {
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

$l['all']['Amount'] = "Suma";
$l['all']['Balance'] = "Sold";
$l['all']['ClientName'] = "Nume Client";
$l['all']['Date'] = "Data";
$l['all']['Download'] = "Descarcă";
$l['all']['EndingDate'] = "Data Sfârșit";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "Factură";
$l['all']['InvoiceStatus'] = "Stare Factură";
$l['all']['InvoiceType'] = "Tip Factură";
$l['all']['IPAddress'] = "Adresă IP";
$l['all']['Language'] = "Limba";
$l['all']['NASIPAddress'] = "Adresă IP NAS";
$l['all']['NewPassword'] = "Parolă Nouă";
$l['all']['Password'] = "Parolă";
$l['all']['PaymentDate'] = "Data Plății";
$l['all']['StartingDate'] = "Data de Început";
$l['all']['StartTime'] = "Ora de Început";
$l['all']['Statistics'] = "Statistici";
$l['all']['Status'] = "Stare";
$l['all']['StopTime'] = "Ora de Sfârșit";
$l['all']['Termination'] = "Terminare";
$l['all']['TotalBilled'] = "Total Facturat";
$l['all']['TotalPaid'] = "Total Plătit";
$l['all']['TotalTime'] = "Timp Total";
$l['all']['Upload'] = "Încărcare";
$l['all']['Username'] = "Nume Utilizator";
$l['all']['CurrentPassword'] = "Parolă Curentă";
$l['all']['VerifyPassword'] = "Verifică Parola";

$l['all']['Global'] = "Global";
$l['all']['Daily'] = "Zilnic";
$l['all']['Weekly'] = "Săptămânal";
$l['all']['Monthly'] = "Lunar";
$l['all']['Yearly'] = "Anual";

$l['button']['Accounting'] = "Accounting";
$l['button']['ChangeAuthPassword'] = "Schimbă Parola de Autentificare";
$l['button']['ChangePortalPassword'] = "Schimbă Parola Portalului";
$l['button']['DateAccounting'] = "Accounting pe Dată";
$l['button']['EditUserInfo'] = "Editează Informații de Contact";
$l['button']['GenerateReport'] = "Generează Raport";
$l['button']['Graphs'] = "Grafice";
$l['button']['Preferences'] = "Preferințe";
$l['button']['ShowInvoice'] = "Afișează Factura";

$l['button']['UserDownloads'] = "Trafic Descărcat";
$l['button']['UserLogins'] = "Istoric Conectări";
$l['button']['UserUploads'] = "Trafic Încărcat";

$l['ContactInfo']['Address'] = "Adresă";
$l['ContactInfo']['City'] = "Oraș";
$l['ContactInfo']['Company'] = "Organizație";
$l['ContactInfo']['Country'] = "Țară";
$l['ContactInfo']['Department'] = "Unitate Operatională";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['FirstName'] = "Prenume";
$l['ContactInfo']['HomePhone'] = "Telefon Acasă";
$l['ContactInfo']['LastName'] = "Nume de Familie";
$l['ContactInfo']['MobilePhone'] = "Telefon Mobil";
$l['ContactInfo']['Notes'] = "Note";
$l['ContactInfo']['State'] = "Stat/Regiune";
$l['ContactInfo']['WorkPhone'] = "Telefon Serviciu";
$l['ContactInfo']['Zip'] = "Cod Poștal";

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Accounting pe Dată</h2>
<p>Furnizează informații detaliate de contabilitate pentru toate sesiunile dintre două date specificate pentru un anumit utilizator.</p>
EOF;
$l['helpPage']['acctmain'] = '<h1 class="fs-5">Contabilitate Generală</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>Generează un grafic care arată cantitatea de date pe care ai descărcat-o într-un anumit interval de timp.<br>
Graficul este însoțit de o listă tabelară.</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>Generează un grafic care arată cantitatea de date pe care ai încărcat-o într-un anumit interval de timp.<br>
Graficul este însoțit de o listă tabelară.</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>Generează un grafic care arată activitatea de conectare pe care ai efectuat-o într-un anumit interval de timp.<br>
Graficul afișează numărul de conectări (sau "hits" la NAS) și este însoțit de o listă tabelară.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Grafice</h1>'
. $l['helpPage']['graphsoveralldownload'] . $l['helpPage']['graphsoveralllogins']
. $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>Dragă utilizator,</p>
<p>Bun venit în Portalul Utilizatorilor. Suntem bucuroși că te-ai alăturat nouă!</p>
<p>Prin autentificarea cu numele tău de utilizator și parola, vei avea acces la o gamă largă de funcționalități. De exemplu, poți modifica ușor setările de contact, actualiza informațiile personale și vizualiza unele date istorice prin grafice vizuale.</p>
<p>Ne luăm foarte în serios confidențialitatea și securitatea ta, așa că poți fi liniștit că toate datele tale sunt stocate în siguranță în baza noastră de date și sunt accesibile doar ție și personalului nostru autorizat.</p>
<p>Dacă ai nevoie de asistență sau ai întrebări, nu ezita să ne contactezi echipa noastră de suport. Suntem întotdeauna bucuroși să te ajutăm!</p>
<p>Cu drag,<br/>
Echipa FiloRADIUS.</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Imposibil de efectuat autentificarea</h1>
<p>Dacă întâmpini probleme la accesarea contului tău, este posibil să fi introdus un nume de utilizator și/sau o parolă incorectă. Asigură-te că ai introdus corect datele tale de autentificare și încearcă din nou.</p>
<p>Dacă încă întâmpini probleme la autentificare după ce ai verificat informațiile, te rugăm să nu eziti să ne contactezi echipa de suport pentru asistență. Suntem întotdeauna aici pentru a te ajuta să recuperezi accesul la contul tău și să revii la utilizarea serviciilor noastre cât mai curând posibil.</p>
EOF;

$l['helpPage']['prefmain'] = "În această secțiune poți gestiona <strong>informațiile tale de contact</strong>, precum și parolele de acces pentru portalul web și serviciile noastre.";
$l['helpPage']['prefpasswordedit'] = "Folosește formularul de mai jos pentru a-ți schimba parola. În scopuri de securitate, ți se va cere să introduci parola veche și să introduci de două ori noua parolă pentru a evita erorile.";
$l['helpPage']['prefuserinfoedit'] = "Folosește formularul de mai jos pentru a-ți actualiza informațiile de contact. Poți schimba numele, prenumele, adresa de email, numerele de telefon și alte detalii conform nevoilor tale. Asigură-te că revezi modificările înainte de a salva, pentru a te asigura de exactitatea informațiilor tale actualizate.";

$l['Intro']['acctdate.php'] = "Contabilitate pe Dată";
$l['Intro']['acctmain.php'] = "Pagina de Contabilitate";
$l['Intro']['billinvoiceedit.php'] = "Vizualizare Factură";
$l['Intro']['billinvoicereport.php'] = "Raport Facturi";
$l['Intro']['billmain.php'] = "Pagina de Facturare";
$l['Intro']['graphmain.php'] = "Grafice de Utilizare";
$l['Intro']['graphsoveralldownload.php'] = "Descărcări Utilizatori";
$l['Intro']['graphsoveralllogins.php'] = "Conectări Utilizatori";
$l['Intro']['graphsoverallupload.php'] = "Încărcări Utilizatori";
$l['Intro']['prefmain.php'] = "Pagina Preferințelor";
$l['Intro']['prefpasswordedit.php'] = "Schimbare Parolă";
$l['Intro']['prefuserinfoedit.php'] = "Editare Informații Utilizator";
$l['menu']['Accounting'] = "Contabilitate";
$l['menu']['Billing'] = "Facturare";
$l['menu']['Graphs'] = "Grafice";
$l['menu']['Home'] = "Acasă";
$l['menu']['Preferences'] = "Preferințe";
$l['menu']['Help'] = "Help";

$l['text']['LoginPlease'] = "Autentifică-te";
$l['text']['LoginRequired'] = "Autentificare Necesară";
$l['title']['ContactInfo'] = "Informații de Contact";
$l['title']['OtherInfo'] = "Alte Informații";
$l['title']['Invoice'] = "Factură";
$l['title']['Items'] = "Elemente";
$l['Tooltip']['invoiceID'] = "Introduceți ID-ul facturii";
