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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once("library/sessions.php");
dalo_session_start();

if (array_key_exists('daloradius_logged_in', $_SESSION)
    && $_SESSION['daloradius_logged_in'] !== false) {
    header('Location: index.php');
    exit;
}

// this include "exports" $langCode that can be used later in this script
include("lang/main.php");

// ~ used later for rendering location select element
$onlyDefaultLocation = !(array_key_exists('CONFIG_LOCATIONS', $configValues)
                        && is_array($configValues['CONFIG_LOCATIONS'])
                        && count($configValues['CONFIG_LOCATIONS']) > 0);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= $langCode ?>" xml:lang="<?= $langCode ?>">
    <head>
        <title>daloRADIUS :: Login</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <link rel="stylesheet" href="css/1.css" media="screen">
        <link rel="stylesheet" href="css/style.css" media="screen">
        
        <script src="library/javascript/pages_common.js"></script>
    </head>

    <body onload="document.login.operator_user.focus()">
        <div id="wrapper">
            <div id="innerwrapper">
                <div id="header">
                    <h1>
                        <a href="index.php">
                            <img alt="daloRADIUS logo" style="border: 0" src="images/daloradius_small.png">
                        </a>
                    </h1>
                    <h2><?= t('all','copyright1') ?></h2>
                    <br>
                </div><!-- #header -->
		
        
                <div id="main">
                    <h2 class="form-header"><?= t('text','LoginRequired') ?></h2>
                    
                     <form class="form-box" name="login" action="dologin.php" method="post">
                            
                        <label for="operator_user">Username</label>
                        <input class="form-input" id="operator_user" name="operator_user" value="" type="text" tabindex="1">
                        
                        <label for="operator_pass">Password</label>
                        <input class="form-input" id="operator_pass" name="operator_pass" value="" type="password" tabindex="2">
                        
                        <label for="location">Location</label>
                        <select id="location" name="location" tabindex="3"
                            class="form-input"<?= ($onlyDefaultLocation) ? " disabled" : "" ?>>
                            <?php
                                $defaultLocationFormat = '<option value="%s">%s</option>' . "\n";
                                if ($onlyDefaultLocation) {
                                    printf($defaultLocationFormat, "default", "default");
                                } else {
                                    $locations = array_keys($configValues['CONFIG_LOCATIONS']);
                                    foreach ($locations as $location) {
                                        $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
                                        printf($defaultLocationFormat, $location, $location);
                                    }
                                }
                            ?>
                        </select>
                        <input class="form-submit" type="submit" value="<?= t('text','LoginPlease') ?>" tabindex="4">

                        <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
                    </form>
                    
                    <small class="form-caption"><?= t('all','daloRADIUS') ?></small>
                    
                    <?php
                        if (array_key_exists('operator_login_error', $_SESSION)
                            && $_SESSION['operator_login_error'] !== false) {
                    ?>
                    
                    <div id="inner-box">
                        <h3 class="text-title error-title">Error!</h3>
                        <?= t('messages','loginerror') ?>
                    </div><!-- #inner-box -->
                    
                    <?php
                        }
                    ?>
                    
                </div><!-- #main -->
        
                <div id="footer">
                    <?php include('page-footer.php'); ?>
                </div><!-- #footer -->
                
            </div><!-- #innerwrapper -->
        </div><!-- #wrapper -->
    </body>
</html>
