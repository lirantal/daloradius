<?php
#
# chilli - ChilliSpot.org. A Wireless LAN Access Point Controller
# Copyright (C) 2003, 2004 Mondru AB.
#
# The contents of this file may be used under the terms of the GNU
# General Public License Version 2, provided that the above copyright
# notice and this permission notice is included in all copies or
# substantial portions of the software.

# Redirects from ChilliSpot daemon:
#
# Redirection when not yet or already authenticated
#   notyet:  ChilliSpot daemon redirects to login page.
#  already: ChilliSpot daemon redirects to success status page.
#
# Response to login:
#   already: Attempt to login when already logged in.
#   failed:  Login failed
#   success: Login succeded
#
# logoff:  Response to a logout
#
#/*
# *********************************************************************************************************
# *
# * Authors:     Liran Tal <liran@lirantal.com>
# *
# * daloRADIUS edition - fixed up variable definition through-out the code
# * as well as parted the code for the sake of modularity and ability to
# * to support templates and languages easier.
# * Copyright (C) Enginx and Liran Tal 2007, 2008
# *
# *********************************************************************************************************
# */

# Shared secret used to encrypt challenge with. Prevents dictionary attacks.
# You should change this to your own shared secret.
$uamsecret = "enginx";

# Uncomment the following line if you want to use ordinary user-password
# for radius authentication. Must be used together with $uamsecret.
$userpassword=1;

# Our own path
$loginpath = $_SERVER['PHP_SELF'];

# Escape values reflected into HTML or inline JavaScript. Chilli supplies
# these on the query string of the pre-auth login page, so they are not trusted.
if (!function_exists('dalo_chilli_text')) {
    function dalo_chilli_text($value) {
        $text = (string) $value;
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }
        $clean = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        return $clean === false ? '' : $clean;
    }
}
if (!function_exists('dalo_chilli_html')) {
    function dalo_chilli_html($value) {
        return htmlspecialchars(dalo_chilli_text($value), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('dalo_chilli_js')) {
    function dalo_chilli_js($value) {
        $json = json_encode(dalo_chilli_text($value), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        return $json === false ? "''" : $json;
    }
}
if (!function_exists('dalo_chilli_http_url')) {
    function dalo_chilli_http_url($value) {
        $url = dalo_chilli_text($value);
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }
        if (!in_array(strtolower($parts['scheme']), array('http', 'https'), true)) {
            return '';
        }
        if (strpbrk($url, "\r\n\t") !== false) {
            return '';
        }
        return $url;
    }
}


include('lang/main.php');

/* if SSL was not used show an error */
if (!($_SERVER['HTTPS'] == 'on')) {
	include('hotspotlogin-nonssl.php');
	exit(0);
}

# Read form parameters which we care about
if (isset($_POST['UserName']))    
	$username    = $_POST['UserName'];
elseif (isset($_GET['UserName']))
	$username    = $_GET['UserName'];
else
	$username    = "";


if (isset($_POST['Password']))    
	$password    = $_POST['Password'];
elseif (isset($_GET['Password']))
	$password    = $_GET['Password'];
else
	$password    = "";


if (isset($_POST['challenge']))    
	$challenge    = $_POST['challenge'];
elseif (isset($_GET['challenge']))    
	$challenge    = $_GET['challenge'];
else
	$challenge    = "";


if (isset($_POST['button']))
    $button        = $_POST['button'];
elseif (isset($_GET['button']))
    $button        = $_GET['button'];
else
    $button        = "";


if (isset($_POST['logout']))
    $logout        = $_POST['logout'];
elseif (isset($_GET['logout']))
    $logout        = $_GET['logout'];
else
    $logout        = "";


if (isset($_POST['prelogin']))    
	$prelogin    = $_POST['prelogin'];
elseif (isset($_GET['prelogin']))    
	$prelogin    = $_GET['prelogin'];
else
	$prelogin    = "";


if (isset($_POST['res']))    
	$res        = $_POST['res'];
elseif (isset($_GET['res']))    
	$res        = $_GET['res'];
else
	$res        = "";


if (isset($_POST['uamip']))
    $uamip        = $_POST['uamip'];
elseif (isset($_GET['uamip']))
    $uamip        = $_GET['uamip'];
else
    $uamip        = "";


if (isset($_POST['uamport']))
    $uamport    = $_POST['uamport'];
elseif (isset($_GET['uamport']))
    $uamport    = $_GET['uamport'];
else
    $uamport    = "";


if (isset($_POST['userurl']))
    $userurl    = $_POST['userurl'];
elseif (isset($_GET['userurl']))
    $userurl    = $_GET['userurl'];
else
    $userurl    = "";


if (isset($_POST['timeleft']))
    $timeleft    = $_POST['timeleft'];
elseif (isset($_GET['timeleft']))
    $timeleft    = $_GET['timeleft'];
else
    $timeleft    = "";


if (isset($_POST['redirurl']))
    $redirurl    = $_POST['redirurl'];
elseif (isset($_GET['redirurl']))
    $redirurl    = $_GET['redirurl'];
else
    $redirurl    = "";


(isset($_GET['reply']))      ? $reply        = $_GET['reply']       : $reply = "";


$userurldecode = $userurl;
$redirurldecode = $redirurl;

# If attempt to login
if ($button == 'Login') {
  $hexchal = pack ("H32", $challenge);
  if ($uamsecret) {
    $newchal = pack ("H*", md5($hexchal . $uamsecret));
  } else {
    $newchal = $hexchal;
  }
  $response = md5("\0" . $password . $newchal);
  $newpwd = pack("a32", $password);
  $pappassword = implode ("", unpack("H32", ($newpwd ^ $newchal)));
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
  <title>$title</title>
  ";
  	include('template/loginform-metatags.php');

  $uam_host = dalo_chilli_html($uamip);
  $uam_port = dalo_chilli_html($uamport);
  $uam_user = dalo_chilli_html($username);
  $uam_pass = dalo_chilli_html($pappassword);
  $uam_userurl = dalo_chilli_html($userurl);
  if (isset($uamsecret) && isset($userpassword)) {
    echo "  <meta http-equiv=\"refresh\" content=\"0;url=http://$uam_host:$uam_port/logon?username=$uam_user&password=$uam_pass\">";
  } else {
    echo "  <meta http-equiv=\"refresh\" content=\"0;url=http://$uam_host:$uam_port/logon?username=$uam_user&response=$response&userurl=$uam_userurl\">";
  }

	include('template/loggingin.php');

echo "
<!--
<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<WISPAccessGatewayParam 
  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
  xsi:noNamespaceSchemaLocation=\"http://www.acmewisp.com/WISPAccessGatewayParam.xsd\">
<AuthenticationReply>
<MessageType>120</MessageType>
<ResponseCode>201</ResponseCode>
";
  if (isset($uamsecret) && isset($userpassword)) {
    echo "<LoginResultsURL>http://$uam_host:$uam_port/logon?username=$uam_user&password=$uam_pass</LoginResultsURL>";
  } else {
    echo "<LoginResultsURL>http://$uam_host:$uam_port/logon?username=$uam_user&response=$response&userurl=$uam_userurl</LoginResultsURL>";
  }
  echo "</AuthenticationReply> 
</WISPAccessGatewayParam>
-->
</html>
";
    exit(0);
}

switch($res) {
  case 'success':     $result =  1; break; // If login successful
  case 'failed':      $result =  2; break; // If login failed
  case 'logoff':      $result =  3; break; // If logout successful
  case 'already':     $result =  4; break; // If tried to login while already logged in
  case 'notyet':      $result =  5; break; // If not logged in yet
  case 'smartclient': $result =  6; break; // If login from smart client
  case 'popup1':      $result = 11; break; // If requested a logging in pop up window
  case 'popup2':      $result = 12; break; // If requested a success pop up window
  case 'popup3':      $result = 13; break; // If requested a logout pop up window
  default: $result = 0; // Default: It was not a form request
}

/* Otherwise it was not a form request
 * Send out an error message
 */
if ($result == 0) {
	include('hotspotlogin-nonchilli.php');
	exit(0);
}

# Generate the output
#echo "Content-type: text/html\n\n";
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
  <title>$title</title>
  ";  
  include('template/loginform-metatags.php');
  echo "
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
  <SCRIPT LANGUAGE=\"JavaScript\">
	";
	include('js/hotspotlogin.js');
$js_result = (int) $result;
$js_popup = dalo_chilli_js($loginpath . '?res=popup2&uamip=' . $uamip . '&uamport=' . $uamport . '&userurl=' . $userurl . '&redirurl=' . $redirurl . '&timeleft=' . $timeleft);
$js_userurl = dalo_chilli_js($userurldecode);
$js_redirurl = dalo_chilli_js(dalo_chilli_http_url($redirurldecode));
$js_timeleft = dalo_chilli_js($timeleft);
echo "
  </script>
</head>
<body onLoad='javascript:doOnLoad($js_result, $js_popup, $js_userurl, $js_redirurl, $js_timeleft)' onBlur = 'javascript:doOnBlur($js_result)' bgColor = '#c0d8f4'>";

if ($result == 2) {
    echo "
  <h1 style=\"text-align: center;\">$h1Failed</h1>";
    if ($reply) {
    echo "<center> " . dalo_chilli_html($reply) . " </BR></BR></center>";
    }
}

if ($result == 5) {
//	chillispot header - login form
//	echo "<h1 style=\"text-align: center;\">$h1Login</h1>";
}

if ($result == 2 || $result == 5) {
	include('template/loginform-header.php');
	include('template/loginform-login.php');
	include('template/loginform-footer.php');
}

if ($result == 1) {
  echo "
  <h1 style=\"text-align: center;\">$h1Loggedin</h1>";

  if ($reply) { 
      echo "<center> " . dalo_chilli_html($reply) . " </br></br></center>";
  }

  $logoff = dalo_chilli_html("http://$uamip:$uamport/logoff");
  echo "
  <center>
    <a href=\"$logoff\">Logout</a>
  </center>
</body>
</html>";
}

if (($result == 4) || ($result == 12)) {
  $logoff = dalo_chilli_html("http://$uamip:$uamport/logoff");
  echo "
  <h1 style=\"text-align: center;\">$h1Loggedin</h1>
  <center>
    <a href=\"$logoff\">$centerLogout</a>
  </center>
  </body>
</html>";
}


if ($result == 11) {
        include('template/loggingin-popup.php');
}


if (($result == 3) || ($result == 13)) {
  $prelogin_url = dalo_chilli_html("http://$uamip:$uamport/prelogin");
  echo "
  <h1 style=\"text-align: center;\">$h1Loggedout</h1>
  <center>
    <a href=\"$prelogin_url\">$centerLogin</a>
  </center>
</body>
</html>";
}

exit(0);
?>
