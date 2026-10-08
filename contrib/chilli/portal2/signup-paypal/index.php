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
 *currency_code
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
require_once dirname(__DIR__,2).'/common/portal2Paypal.php';
header('Content-Type: text/html; charset=UTF-8');header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
ini_set('session.use_strict_mode','1');ini_set('session.cookie_httponly','1');ini_set('session.cookie_samesite','Lax');
session_cache_limiter('');session_start();
if (!is_string($_SESSION['portal2_paypal_csrf'] ?? null)) {$_SESSION['portal2_paypal_csrf']=bin2hex(random_bytes(32));}
function portal2_paypal_html($value) {return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
$plans=array();$registered=null;$failureMsg='';$pdo=null;$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if (!in_array($method,array('GET','POST'),true)) {http_response_code(405);throw new DomainException('Invalid method');}
    if ($method==='POST') {
        $csrf=$_POST['csrf_token'] ?? null;
        if (!is_string($_POST['submit'] ?? null) || !is_string($csrf) || !hash_equals($_SESSION['portal2_paypal_csrf'],$csrf)) {
            http_response_code(403);throw new DomainException('Invalid request');
        }
        $_SESSION['portal2_paypal_csrf']=bin2hex(random_bytes(32));
    }
    $pdo=dalo_chilli_pdo_open($configValues);
    $pending=$_SESSION['portal2_paypal_pending'] ?? null;
    if (is_array($pending) && is_string($pending['correlation'] ?? null) && ($pending['expires'] ?? 0)>time()) {
        $registered=dalo_portal2_paypal_resume($pdo,$configValues,$pending['correlation']);
    }
    if ($registered===null) {
        unset($_SESSION['portal2_paypal_pending']);
        $plans=dalo_portal2_paypal_plans($pdo,$configValues);
        if ($method==='POST') {
            $registered=dalo_portal2_paypal_register($pdo,$configValues,$_POST);
            $_SESSION['portal2_paypal_pending']=array('correlation'=>$registered['correlation'],'expires'=>time()+1800);
        }
    }
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
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<script src="library/javascript/common.js" type="text/javascript"></script>
<body>

<div id="wrap">

                <div class="header"><p>Hotspot<span>Login</span><sup>
                        By <a href="http://templatefusion.org">TemplateFusion.org</a></sup></p>
                </div>

        <div id="navigation">
                <ul class="glossymenu">
                        <li><a href="index.php" class="current"><b>Home</b></a></li>
                        <li><a href="#"><b>Services</b></a></li>
                        <li><a href="#"><b>About Us</b></a></li>
                        <li><a href="#"><b>Contact</b></a></li>
                </ul>
        </div>

        <div id="body">
                <h1>Sign-Up</h1>
                <p>
                        <center>

<?php if ($failureMsg!==''): ?><p role="alert"><?= portal2_paypal_html($failureMsg) ?></p><?php endif; ?>
<?php if ($registered===null): ?>
We allow our customers to sign-up for Internet access plans using their PayPal accounts.<br/>
Complete the form and click the Apply button to register in our database.<br/>
<form name="newuser" action="index.php" method="post">
<input type="hidden" name="csrf_token" value="<?= portal2_paypal_html($_SESSION['portal2_paypal_csrf']) ?>">
<table><tr><td>Select your plan:</td><td><select id="planId" name="planId">
<?php foreach ($plans as $plan): ?><option value="<?= portal2_paypal_html($plan['planId']) ?>"><?= portal2_paypal_html($plan['planName']) ?> - Cost <?= portal2_paypal_html($plan['planCost']) ?> <?= portal2_paypal_html($plan['planCurrency']) ?> </option><?php endforeach; ?>
</select></td></tr>
<?php foreach (array('firstName'=>'First name','lastName'=>'Last name','address'=>'Address','city'=>'City','state'=>'State') as $field=>$label): ?>
<tr><td><?= $label ?>:</td><td><input name="<?= $field ?>" value="<?= portal2_paypal_html(is_string($_POST[$field] ?? null)?$_POST[$field]:'') ?>"></td></tr>
<?php endforeach; ?></table><br><input type="submit" value="Submit" name="submit"></form>
<?php else: ?>
<font color="blue"><b>Thank you...</b></font><br><br>
Your PIN code has been created but it will only be activated after you complete and confirm<br>
your payment through PayPal. Following is your PIN code, which you will need in-order to access<br>
our Hotspot services.<br><br>
<table><tr><td>PIN Code:</td><td><b><?= portal2_paypal_html($registered['pin']) ?></b></td></tr></table>
<form action="<?= portal2_paypal_html($registered['checkout']['action']) ?>" method="post">
<?php foreach (dalo_portal2_paypal_fields($registered) as $name=>$value): ?>
<input type="hidden" id="<?= $name ?>" name="<?= $name ?>" value="<?= portal2_paypal_html($value) ?>">
<?php endforeach; ?><button type="submit"><?= $registered['plan']['planRecurring']==='Yes'?'Subscribe':'Buy Now' ?></button></form>
<br><b>It is recommended that you will write it down now in-case of a failure.</b><br><br>
<?php endif; ?>


                        </center>
                </p>


                <h1>Hotspot References</h1>
                <a href="#"><img src="images/portfolio1.jpg" alt="portfolio1" /></a>
                <a href="#"><img src="images/portfolio2.jpg" alt="portfolio2" /></a>
                <a href="#"><img src="images/portfolio3.jpg" alt="portfolio3" /></a>
                <a href="#"><img src="images/portfolio4.jpg" alt="portfolio4" /></a>
        </div>



        <div id="footer">Enginx&copy;2008 All Rights Reserved &bull; Enginx and daloRADIUS Hotspot Systems <br/>
                Design by <a href="http://templatefusion.org">TemplateFusion</a>
        </div>


</div>

</body>
</html>