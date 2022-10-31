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

    $param_label = array(
                            'CONFIG_IFACE_PASSWORD_HIDDEN' => t('all','PasswordHidden'),
                            'CONFIG_IFACE_TABLES_LISTING_NUM' => t('all','TablesListingNum'),
                            'CONFIG_IFACE_AUTO_COMPLETE' => t('all','AjaxAutoComplete')
                        );
                  
    // if the form has been submitted we validate and store the configuration
    if (array_key_exists('submit', $_POST) && isset($_POST['submit'])) {
        
        foreach ($param_label as $param => $label) {
            if (array_key_exists($param, $_POST) && isset($_POST[$param]) &&
                in_array(strtolower($_POST[$param]), array("yes", "no"))) {
                $configValues[$param] = $_POST[$param];
            }
        }
        
        if (array_key_exists('CONFIG_IFACE_TABLES_LISTING_NUM', $_POST) && isset($_POST['CONFIG_IFACE_TABLES_LISTING_NUM']) &&
            intval($_POST['CONFIG_IFACE_TABLES_LISTING_NUM']) > 0 && intval($_POST['CONFIG_IFACE_TABLES_LISTING_NUM']) <= 100) {
            $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = intval($_POST['CONFIG_IFACE_TABLES_LISTING_NUM']);
        }
        
        include("library/config_write.php");
    }
                        

    if (isset($_REQUEST['submit'])) {

        if (isset($_REQUEST['config_iface_pass_hidden']))
            $configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = $_REQUEST['config_iface_pass_hidden'];
        if (isset($_REQUEST['config_iface_tableslisting']))
            $configValues['CONFIG_IFACE_TABLES_LISTING'] = $_REQUEST['config_iface_tableslisting'];
        if (isset($_REQUEST['config_iface_tableslisting_num']))
            $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = $_REQUEST['config_iface_tableslisting_num'];
        if (isset($_REQUEST['config_iface_auto_complete']))
            $configValues['CONFIG_IFACE_AUTO_COMPLETE'] = $_REQUEST['config_iface_auto_complete'];
            
            include ("library/config_write.php");
    }
    

    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configinterface.php');
    $help = t('helpPage','configinterface');
    
    print_html_prologue($title, $langCode);

    include ("menu-config.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

<form name="interfacesettings" method="POST">
    <fieldset>
        <h302><?= t('title','Settings') ?></h302>
        <br>
        
        <ul>
<?php
    foreach ($param_label as $name => $label) {
        print_select_as_list_elem($name, $label, array("no", "yes"), $configValues[$name]);
    }
?>
            <li class="fieldset">
                <label for="CONFIG_IFACE_TABLES_LISTING" class="form"><?= t('all','TablesListing') ?></label>
                <input type="number" min="1" max="100" value="<?= $configValues['CONFIG_IFACE_TABLES_LISTING'] ?>"
                    name="CONFIG_IFACE_TABLES_LISTING" id="CONFIG_IFACE_TABLES_LISTING">
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
