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

    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");

    $valid_encrypt_methods = array(
                                    "cleartext" => "cleartext",
                                    "crypt" => "unix crypt",
                                    "md5" => "md5",
                                  );

    $allowed_random_chars_regex = "^[a-zA-Z0-9_]+$";

    if (array_key_exists('submit', $_POST) && isset($_POST['submit'])) {
        
        // this should probably move to some other page at some point
        if (array_key_exists('CONFIG_DB_PASSWORD_ENCRYPTION', $_POST) &&
            isset($_POST['CONFIG_DB_PASSWORD_ENCRYPTION']) &&
            in_array(strtolower($_POST['CONFIG_DB_PASSWORD_ENCRYPTION']), array_keys($valid_encrypt_methods))) {
            $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = strtolower($_POST['CONFIG_DB_PASSWORD_ENCRYPTION']);
        }
        
        

        if (array_key_exists('CONFIG_USER_ALLOWEDRANDOMCHARS', $_POST) && isset($_POST['CONFIG_USER_ALLOWEDRANDOMCHARS']) &&
            preg_match($allowed_random_chars_regex, $_POST['CONFIG_USER_ALLOWEDRANDOMCHARS']) !== false) {
            $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = $_POST['CONFIG_USER_ALLOWEDRANDOMCHARS'];
        }
        
        include("library/config_write.php");
    }    

    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configuser.php');
    $help = t('helpPage','configuser');
    
    print_html_prologue($title, $langCode);

    include ("menu-config.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
?>        
        
<form name="usersettings" method="POST">

    <fieldset>
        <h302><?= t('title','Settings'); ?></h302>
        
        <br>

        <ul>

<?php
        print_select_as_list_elem('CONFIG_DB_PASSWORD_ENCRYPTION',
                                  t('all','DBPasswordEncryption'),
                                  $valid_encrypt_methods,
                                  $configValues['CONFIG_DB_PASSWORD_ENCRYPTION']);
?>

            <li class="fieldset">
                <label for="CONFIG_USER_ALLOWEDRANDOMCHARS" class="form"><?= t('all','RandomChars') ?></label>
                <input type="text" name="CONFIG_USER_ALLOWEDRANDOMCHARS" id="CONFIG_USER_ALLOWEDRANDOMCHARS"
                    value="<?= htmlspecialchars($configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'], ENT_QUOTES, 'UTF-8') ?>"
                    pattern="<?= $allowed_random_chars_regex ?>">
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
