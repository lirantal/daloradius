<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

include_once("library/sessions.php");
dalo_session_start();

if (array_key_exists('daloradius_logged_in', $_SESSION)
    && $_SESSION['daloradius_logged_in'] !== false) {
    header('Location: index.php');
    exit;
}

include("lang/main.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?= $langCode ?>" xml:lang="<?= $langCode ?>"
    xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <script src="library/javascript/pages_common.js"
            type="text/javascript"></script>
        <title>daloRADIUS</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="css/1.css" type="text/css"
            media="screen,projection" />
        <style type="text/css">
            #helpPage {
                display: none;
                visibility: visible;
            }
        </style>
    </head>

    <body onLoad="document.login.operator_user.focus()">
        <div id="wrapper">
            <div id="innerwrapper">
                <div id="header">
                    <h1>
                        <a href="index.php">
                            <img src="images/daloradius_small.png" border="0" />
                        </a>
                    </h1>
                    <h2><?= t('all','copyright1') ?></h2>
                    <br/>
                    <ul id="subnav">
                        <li><?= t('all','daloRADIUS') ?></li>
                    </ul>
                </div>
		
                <div id="sidebar">
                    <h2><?= t('text','LoginRequired') ?></h2>
                    <h3><?= t('text','LoginPlease') ?></h3>
                    
                    <form name="login" action="dologin.php" class="sidebar"
                        method="post">
                        
                        <label for="operator_user">Username</label>
                        <input id="operator_user" name="operator_user"
                            value="administrator" type="text" tabindex="1" />
                        
                        <label for="operator_pass">Password</label>
                        <input id="operator_pass" name="operator_pass"
                            value="" type="password" tabindex="2" />
                            
                        <label for="location">Location</label>
                        <?php
                            $onlyDefaultLocation = !(array_key_exists('CONFIG_LOCATIONS', $configValues)
                                && is_array($configValues['CONFIG_LOCATIONS'])
                                && count($configValues['CONFIG_LOCATIONS']) > 0);
                        ?>
                        <select id="location" name="location" tabindex="3"
                            class="generic"<?= ($onlyDefaultLocation) ? " disabled" : "" ?>>
                            <?php
                                if ($onlyDefaultLocation) {
                                    echo '<option value="default">default</option>';
                                } else {
                                    $locations = array_keys($configValues['CONFIG_LOCATIONS']);
                                    foreach ($locations as $l) {
                                        echo "<option value=\"$l\">$l</option>";
                                    }
                                }
                            ?>
                        </select>
                        <input class="sidebutton" type="submit"
                            value="Login" tabindex="4" />
                    </form>
                </div>
                
                <div id="contentnorightbar">
                    <h2 id="Intro">
                        <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                            <?= t('Intro','login.php') ?></a>
                    </h2>
                    
                    <div id="helpPage">
                        <?= t('helpPage','login') ?>
                    </div>
                    
                    <?php
                        if (array_key_exists('operator_login_error', $_SESSION)
                            && $_SESSION['operator_login_error'] !== false) {
                            echo t('messages','loginerror');
                        }
                    ?>
                </div>
		
                <div id="footer">
                    <?php include('page-footer.php'); ?>
                </div>
                
            </div>
        </div>
    </body>
</html>
