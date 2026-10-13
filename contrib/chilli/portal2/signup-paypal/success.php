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
require_once dirname(__DIR__,2).'/common/portal2Paypal.php';
header('Content-Type: text/html; charset=UTF-8');header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
$successMsg=$configValues['CONFIG_MERCHANT_SUCCESS_MSG_PRE'] ?? 'Waiting for payment confirmation';$refresh=true;$pdo=null;
try {
    if (array_key_exists('txnId',$_GET)) {
        dalo_paypal_text($_GET['txnId'],200,true);$pdo=dalo_chilli_pdo_open($configValues);
        $receipt=dalo_portal2_paypal_receipt($pdo,$configValues,$_GET['txnId']);
        if ($receipt!==null && $receipt['payment_status']==='Completed') {
            $successMsg='We have successfully validated your payment<br/>';
            $successMsg.='Your user PIN is:<br/><b>'.htmlspecialchars((string)$receipt['username'],ENT_QUOTES,'UTF-8').'</b>';
            $successMsg.='<br/><br/>'.($configValues['CONFIG_MERCHANT_SUCCESS_MSG_POST'] ?? 'Payment confirmed').'<br/><br/>';
            $successMsg.="Click <a href='http://192.168.182.1:3990/prelogin'>here</a> to return to the Login page";
            $refresh=false;
        }
    }
} catch (InvalidArgumentException $error) {http_response_code(400);$refresh=false;$successMsg='Payment receipt unavailable';}
catch (Throwable $error) {http_response_code(503);$refresh=false;$successMsg='Payment receipt unavailable';}
finally {if ($pdo instanceof PDO) {dalo_chilli_database_close($pdo);}}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
        if ($refresh == true)
                echo '<meta http-equiv="refresh" content="5">';
?>
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

        <?php
                echo "<font color='blue'><b>".($configValues['CONFIG_MERCHANT_SUCCESS_MSG_HEADER'] ?? '')."</b></font>";
                echo $successMsg;
        ?>

			<br/><br/>
			<br/><br/>
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