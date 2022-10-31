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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");

    if (isset($_REQUEST['submit'])) {

        if (isset($_REQUEST['config_mail_smtpaddr']))
            $configValues['CONFIG_MAIL_SMTPADDR'] = $_REQUEST['config_mail_smtpaddr'];
        
        if (isset($_REQUEST['config_mail_smtpport']))
            $configValues['CONFIG_MAIL_SMTPPORT'] = $_REQUEST['config_mail_smtpport'];
            
        if (isset($_REQUEST['config_mail_smtp_fromemail']))
            $configValues['CONFIG_MAIL_SMTPFROM'] = $_REQUEST['config_mail_smtp_fromemail'];
        
        include ("library/config_write.php");
    }    

    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configmail.php');
    $help = t('helpPage','configmail');
    
    print_html_prologue($title, $langCode);

    include("menu-config.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

?>

<form name="mailsettings" method="POST">
    <fieldset>
        <h302><?= t('title','Settings'); ?></h302>
        
        <br/>

        <ul>

            <li class="fieldset">
                <label for="config_mail_smtpaddr" class="form"><?= t('all','SMTPServerAddress') ?></label>
                <input type="text" value="<?= $configValues['CONFIG_MAIL_SMTPADDR'] ?>" name="config_mail_smtpaddr">
            </li>

            <li class="fieldset">
                <label for="config_mail_smtpport" class="form"><?= t('all','SMTPServerPort') ?></label>
                <input type="number" min="0" max="65535" value="<?= $configValues['CONFIG_MAIL_SMTPPORT'] ?>" name="config_mail_smtpport">
            </li>

            <li class="fieldset">
                <label for="config_mail_smtp_fromemail" class="form"><?= t('all','SMTPServerFromEmail') ?></label>
                <input type="text" value="<?= $configValues['CONFIG_MAIL_SMTPFROM'] ?>" name="config_mail_smtp_fromemail">
            </li>


            <li class="fieldset">
                <br/><hr><br/>
                <input type="submit" name="submit" value="<?= t('buttons','apply') ?>" class="button">
            </li>
        </ul>
    </fieldset>
</form>

        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
