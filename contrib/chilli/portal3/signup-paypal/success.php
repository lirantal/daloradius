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
$successMsg=$configValues['CONFIG_PAYPAL_SUCCESS_MSG_PRE'] ?? 'Waiting for payment confirmation';$refresh=true;$pdo=null;
try {
    if (array_key_exists('txnId',$_GET)) {
        dalo_paypal_text($_GET['txnId'],200,true);$pdo=dalo_chilli_pdo_open($configValues);
        $receipt=dalo_portal3_paypal_receipt($pdo,$configValues,$_GET['txnId']);
        if ($receipt!==null && $receipt['payment_status']==='Completed') {
            $successMsg='Your user PIN is: <b>'.htmlspecialchars((string)$receipt['username'],ENT_QUOTES,'UTF-8').
                '</b> <br/><br/>'.($configValues['CONFIG_PAYPAL_SUCCESS_MSG_POST'] ?? 'Payment confirmed');
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

	<?php
		echo "<font color='blue'>".($configValues['CONFIG_PAYPAL_SUCCESS_MSG_HEADER'] ?? '')."</font>";
	        echo $successMsg;
	?>

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
