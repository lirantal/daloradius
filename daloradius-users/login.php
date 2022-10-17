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

if (array_key_exists('logged_in', $_SESSION)
    && $_SESSION['logged_in'] !== false) {
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
        <link rel="stylesheet" href="../css/1.css" type="text/css"
            media="screen,projection" />
        <link rel="stylesheet" href="../css/style.css" type="text/css"
            media="screen,projection" />
    </head>
 
    <body onLoad="document.login.login_user.focus()">
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
                </div><!-- #header -->
                
                <div id="main">
                    <h2 class="form-header"><?= t('text','LoginRequired') ?></h2>
                    <form class="form-box" name="login" action="dologin.php" method="post">
                        <label for="login_user">Username</label>
                        <input class="form-input" id="login_user"
                            name="login_user" value=""
                            type="text" tabindex="1" />
                        
                        <label for="login_pass">Password</label>
                        <input class="form-input" id="login_pass"
                            name="login_pass" value=""
                            type="password" tabindex="2" />
                            
                        <input class="form-submit" type="submit"
                            value="<?= t('text','LoginPlease') ?>" tabindex="3" />
                    </form>
                    
                    <small class="form-caption"><?= t('all','daloRADIUS') ?></small>
                    
                    
                    <div id="inner-box">
                    <?php
                        if (array_key_exists('login_error', $_SESSION)
                            && $_SESSION['login_error'] !== false) {
                    ?>
                        <h3 class="text-title error-title">Error!</h3>
                        <?= t('messages','loginerror') ?>
                        <hr class="inner-separator">
                        
                    <?php
                        }
                    ?>
                        
                        <h3 class="text-title success-title">Welcome!</h3>
                        <?= t('helpPage','loginUsersPortal') ?>
                    
                    </div><!-- #inner-box -->
                    
                </div><!-- #main -->
                
                <div id="footer">
                    <?php include('page-footer.php'); ?>
                </div><!-- #footer -->
            </div><!-- #innerwrapper -->
        </div><!-- #wrapper -->
    </body>
</html>
