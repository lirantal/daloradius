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
 * Authors:     Liran Tal <liran@lirantal.com>
 *
 * Credits to the implementation of captcha are due to G.Sujith Kumar of codewalkers
 *
 *********************************************************************************************************
 */

require_once __DIR__.'/library/config_read.php';
require_once dirname(__DIR__,2).'/common/portal3Paypal.php';
header('Content-Type: text/html; charset=UTF-8');header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
ini_set('session.use_strict_mode','1');ini_set('session.cookie_httponly','1');ini_set('session.cookie_samesite','Lax');
session_cache_limiter('');session_start();
if (!is_string($_SESSION['portal3_paypal_csrf'] ?? null)) {$_SESSION['portal3_paypal_csrf']=bin2hex(random_bytes(32));}
function portal3_paypal_html($value) {return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
$plans=array();$registered=null;$failureMsg='';$pdo=null;$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if (!in_array($method,array('GET','POST'),true)) {http_response_code(405);throw new DomainException('Invalid method');}
    if ($method==='POST') {
        $csrf=$_POST['csrf_token'] ?? null;
        if (!is_string($_POST['submit'] ?? null) || !is_string($csrf) || !hash_equals($_SESSION['portal3_paypal_csrf'],$csrf)) {
            http_response_code(403);throw new DomainException('Invalid request');
        }
        $_SESSION['portal3_paypal_csrf']=bin2hex(random_bytes(32));
    }
    $pdo=dalo_chilli_pdo_open($configValues);
    $plans=dalo_portal3_paypal_plans($pdo,$configValues);
    if ($method==='POST') {$registered=dalo_portal3_paypal_register($pdo,$configValues,$_POST);}
} catch (DomainException $error) {$failureMsg='Registration request rejected';}
catch (InvalidArgumentException $error) {http_response_code(400);$failureMsg='Missing or invalid registration fields';}
catch (Throwable $error) {http_response_code(503);$failureMsg='Registration could not be completed';}
finally {if ($pdo instanceof PDO) {dalo_chilli_database_close($pdo);}}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>User Sign-Up</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body>
<div id="wrapper">
  <div id="header">
    <div id="nav">	<a href="index.php">Sign-Up</a> &nbsp;|&nbsp;
			<a href="#">Terms Of Service</a> &nbsp;|&nbsp; 
			<a href="#">About us</a> &nbsp;|&nbsp; 
			<a href="#">Contact us</a> &nbsp;|&nbsp; 
     </div>
    <div id="bg"></div>
  </div>
  <div id="main-content">
    <div id="left-column">
      <div id="logo"><img src="images/big-paw.gif" alt="Pet Logo" width="42" height="45" align="left" />
		<span class="logotxt1">daloRADIUS</span>
		<span class="logotxt2">user Sign-Up</span><br />
      		<span style="margin-left:15px;">daloRADIUS, driving smart hotspots to the limit</span></div>
      <div class="box">

        <h1>Sign-Up</h1>
	<p>

<?php if ($failureMsg!==''): ?><p role="alert"><?= portal3_paypal_html($failureMsg) ?></p><?php endif; ?>
<?php if ($registered===null): ?>
We allow our customers to sign-up for Internet access plans using their PayPal accounts.
Complete the form and click the Apply button to register in our database.<br><br>
<form name="newuser" action="index.php" method="post">
<input type="hidden" name="csrf_token" value="<?= portal3_paypal_html($_SESSION['portal3_paypal_csrf']) ?>">
Select your plan:<br><select id="planId" name="planId">
<?php foreach ($plans as $plan): ?><option value="<?= portal3_paypal_html($plan['planId']) ?>"><?= portal3_paypal_html($plan['planName']) ?> - Cost <?= portal3_paypal_html($plan['planCost']) ?> <?= portal3_paypal_html($plan['planCurrency']) ?> </option><?php endforeach; ?>
</select><br><br><ul>
<?php foreach (array('firstName'=>'First name','lastName'=>'Last name','address'=>'Address','city'=>'City','state'=>'State') as $field=>$label): ?>
<?= $label ?>:<li><input name="<?= $field ?>" value="<?= portal3_paypal_html(is_string($_POST[$field] ?? null)?$_POST[$field]:'') ?>"></li>
<?php endforeach; ?><br><input type="submit" value="Submit" name="submit"></ul></form>
<?php else: ?>
<font color="blue">Thank you...</font><br>
Your PIN code has been created but it will only be activated after you complete and confirm
your payment through PayPal. Following is your PIN code, which you will need in-order to access
our Hotspot services.<br><br>
<ul><li>PIN Code: <b><?= portal3_paypal_html($registered['pin']) ?></b></li></ul>
It is recommended that you will write it down now in-case of a failure.<br><br>
<form action="<?= portal3_paypal_html($registered['checkout']['action']) ?>" method="post">
<?php foreach (dalo_portal3_paypal_fields($registered) as $name=>$value): ?>
<input type="hidden" id="<?= $name ?>" name="<?= $name ?>" value="<?= portal3_paypal_html($value) ?>">
<?php endforeach; ?><button type="submit">Buy Now</button></form>
<?php endif; ?>



	</p>
      </div>

    </div>
    <div id="right-column">
      <div id="main-image"><img src="images/lady.jpg" alt="I love Pets" width="153" height="222" /></div>
      <div class="sidebar">

        <h3>About daloRADIUS</h3>
	<p>
		daloRADIUS is an advanced RADIUS web management application aimed at managing hotspots and
		general-purpose ISP deployments. It features user management, graphical reporting, accounting,
		a billing engine and integrates with GoogleMaps for geo-locating.		
	</p>
        <h3>Resources</h3>
        <div class="box">
          <ul>
            <li><a href="http://www.daloradius.com" target="_blank">daloRADIUS Official homepage</a></li>
            <li><a href="http://daloradius.wiki.sourceforge.net/" target="_blank">daloRADIUS Wiki</a></li>
          </ul>
        </div><a href="http://www.web-designers-directory.org/"></a><a href="http://www.medicine-pet.com/"></a>
      </div>
    </div>
  </div>
  <div id="footer">Copyright &copy; 2008 Liran Tal and daloRADIUS Project, All rights reserved.<br />
    <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a>  |  <a href="http://jigsaw.w3.org/css-validator/check/referer?warning=no&amp;profile=css2" target="_blank">CSS</a>  - Thanks to: <a href="http://www.medicine-pet.com/" target="_blank">Pet Medicine</a> | <span class="crd"><a href="http://www.web-designers-directory.org/">Web site Design</a></span> by : <a href="http://www.web-designers-directory.org/" target="_blank">WDD</a></div>
</div>

</body>
</html>
