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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    $log = "visited page: ";

    // validating values
    $valid_values = array(
                            "en" => "English", 
                            "ru" => "Russian", 
                            "hu" => "Hungarian", 
                            "it" => "Italian", 
                            "es_VE" => "Spanish - Venezuelan", 
                            "pt_br" => "Portuguese - Brazilian", 
                            "ja" => "Japanese"
                         );

    if (array_key_exists('submit', $_POST) && isset($_POST['submit'])) {
        if (array_key_exists('CONFIG_LANG', $_POST) &&
            isset($_POST['CONFIG_LANG']) &&
            in_array(strtolower($_POST['CONFIG_LANG']), array_keys($valid_values))) {
            
            $configValues['CONFIG_LANG'] = $_POST['CONFIG_LANG'];
            include("library/config_write.php");
        }
    }
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configlang.php');
    $help = t('helpPage','configlang');
    
    print_html_prologue($title, $langCode);

    include("menu-config.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
?>

<form name="langsettings" method="POST">
    <fieldset>
        <h302><?= t('title','Settings') ?></h302>
        <br>

        <ul>
<?php
            print_select_as_list_elem('CONFIG_LANG', t('all','PrimaryLanguage'), $valid_values, $configValues['CONFIG_LANG']);
?>
        
            <li class="fieldset">
                <br>
                <hr>
                <br>
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
