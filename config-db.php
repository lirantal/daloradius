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
    
    // whitelists used for validation/presentation purposes
    $valid_db_engines = array(
                                "mysql" => "MySQL",
                                "pgsql" => "PostgreSQL",
                                "odbc" => "ODBC",
                                "mssql" => "MsSQL",
                                "mysqli" => "MySQLi",
                                "msql" => "MsQL",
                                "sybase" => "Sybase",
                                "sqlite" => "Sqlite",
                                "oci8" => "Oci8 ",
                                "ibase" => "ibase",
                                "fbsql" => "fbsql",
                                "informix" => "informix"
                             );
    
    $db_tbl_param_label = array(
                                'CONFIG_DB_TBL_RADCHECK' => t('all','radcheck'), 
                                'CONFIG_DB_TBL_RADREPLY' => t('all','radreply'),
                                'CONFIG_DB_TBL_RADGROUPREPLY' => t('all','radgroupreply'), 
                                'CONFIG_DB_TBL_RADGROUPCHECK' => t('all','radgroupcheck'), 
                                'CONFIG_DB_TBL_RADUSERGROUP' => t('all','usergroup'), 
                                'CONFIG_DB_TBL_RADACCT' => t('all','radacct'), 
                                'CONFIG_DB_TBL_RADNAS' => t('all','nas'),
                                'CONFIG_DB_TBL_RADHG' => t('all','hunt'), 
                                'CONFIG_DB_TBL_RADPOSTAUTH' => t('all','radpostauth'), 
                                'CONFIG_DB_TBL_RADIPPOOL' => t('all','radippool'), 
                                'CONFIG_DB_TBL_DALOUSERINFO' => t('all','userinfo'), 
                                'CONFIG_DB_TBL_DALODICTIONARY' => t('all','dictionary'), 
                                'CONFIG_DB_TBL_DALOREALMS' => t('all','realms'), 
                                'CONFIG_DB_TBL_DALOPROXYS' => t('all','proxys'), 
                                'CONFIG_DB_TBL_DALOBILLINGMERCHANT' => t('all','billingmerchant'), 
                                'CONFIG_DB_TBL_DALOBILLINGPAYPAL' => t('all','billingpaypal'), 
                                'CONFIG_DB_TBL_DALOBILLINGPLANS' => t('all','billingplans'), 
                                'CONFIG_DB_TBL_DALOBILLINGRATES' => t('all','billingrates'), 
                                'CONFIG_DB_TBL_DALOBILLINGHISTORY' => t('all','billinghistory'), 
                                'CONFIG_DB_TBL_DALOUSERBILLINFO' => t('all','billinginfo'), 
                                'CONFIG_DB_TBL_DALOBILLINGINVOICE' => t('all','Invoice'), 
                                'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS' => t('all','InvoiceItems'), 
                                'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS' => t('all','InvoiceStatus'), 
                                'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE' => t('all','InvoiceType'), 
                                'CONFIG_DB_TBL_DALOPAYMENTTYPES' => t('all','payment_type'), 
                                'CONFIG_DB_TBL_DALOPAYMENTS' => t('all','payments'), 
                                'CONFIG_DB_TBL_DALOOPERATORS' => t('all','operators'), 
                                'CONFIG_DB_TBL_DALOOPERATORS_ACL' => t('all','operators_acl'), 
                                'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES' => t('all','operators_acl_files'), 
                                'CONFIG_DB_TBL_DALOHOTSPOTS' => t('all','hotspots'), 
                                'CONFIG_DB_TBL_DALONODE' => t('all','node'), 
                            );
    
    $generic_db_conf_params = array(
                                    'CONFIG_DB_HOST' => t('all','DatabaseHostname'),
                                    'CONFIG_DB_USER' => t('all','DatabaseUser'),
                                    'CONFIG_DB_PASS' => t('all','DatabasePass'),
                                    'CONFIG_DB_NAME' => t('all','DatabaseName'),
                               );
    
    $tbl_name_regex = '^[a-zA-Z0-9_]+$';
    
    // if the form has been submitted we validate and store the configuration
    if (array_key_exists('submit', $_POST) && isset($_POST['submit'])) {
        
        if (array_key_exists('CONFIG_DB_ENGINE', $_POST) && isset($_POST['CONFIG_DB_ENGINE']) &&
            in_array(strtolower($_POST['CONFIG_DB_ENGINE']), array_keys($valid_db_engines))) {
            $configValues['CONFIG_DB_ENGINE'] = $_POST['CONFIG_DB_ENGINE']; 
        }
    
        if (array_key_exists('CONFIG_DB_PORT', $_POST) && isset($_POST['CONFIG_DB_PORT']) &&
            intval($_POST['CONFIG_DB_PORT']) >= 0 && intval($_POST['CONFIG_DB_PORT']) <= 65535) {
            $configValues['CONFIG_DB_PORT'] = intval($_POST['CONFIG_DB_PORT']);
        }

        foreach ($generic_db_conf_params as $param => $caption) {
            if (array_key_exists($param, $_POST) && isset($_POST[$param])) {
                $configValues[$param] = $_POST[$param];
            }
        }

        // validate table name
        foreach ($db_tbl_param_label as $param => $label) {
            if (array_key_exists($param, $_POST) && isset($_POST[$param]) && preg_match($tbl_name_regex, $_POST[$param]) !== false) {
                $configValues[$param] = $_POST[$param];
            }
        }

        include("library/config_write.php");
    }    

    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configmain.php');
    $help = t('helpPage','configmain');
    
    print_html_prologue($title, $langCode);

    include("menu-config.php");
    
    include_once ("library/tabber/tab-layout.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
?>

<form name="dbsettings" method="POST">
    <div class="tabber">
        
        <div class="tabbertab" title="<?= t('title','Settings') ?>">
            <fieldset>
                <h302><?= t('title','Settings') ?></h302>
                
                <br/>

                <ul>

                    <li class="fieldset">
                        <label for="CONFIG_DB_ENGINE" class="form"><?= t('all','DBEngine')?></label>
                        <select class="form" name="CONFIG_DB_ENGINE" id="CONFIG_DB_ENGINE">
<?php
        foreach ($valid_db_engines as $value => $caption) {
            $selected = (strtolower($configValues['CONFIG_DB_ENGINE']) === $value) ? " selected" : "";
            printf('<option value="%s"%s>%s</option>', $value, $selected, $caption);
        }
?>
                        </select>
                    </li>

                    <li class="fieldset">
                        <label for="CONFIG_DB_PORT" class="form"><?= t('all','DatabasePort') ?></label>
                        <input type="number" min="0" max="65535" value="<?= $configValues['CONFIG_DB_PORT'] ?>"
                            name="CONFIG_DB_PORT" id="CONFIG_DB_PORT">
                    </li>

<?php
            foreach ($generic_db_conf_params as $param => $label) {
                $value = htmlspecialchars($configValues[$param], ENT_QUOTES, 'UTF-8');
                
                echo '<li class="fieldset">';
                printf('<label for="%s" class="form">%s</label>', $param, $label);
                printf('<input type="text" value="%s" name="%s" id="%s">', $value, $param, $param);
                echo '</li>';
            }
?>
                    <li class="fieldset">
                        <br><hr><br>
                        <input type="submit" name="submit" value="<?= t('buttons','apply') ?>" class="button">
                    </li>

                </ul>
    
            </fieldset>

        </div><!-- .tabbertab -->

         <div class="tabbertab" title="<?= t('title','DatabaseTables') ?>">
            <fieldset>
                <h302><?= t('title','DatabaseTables') ?></h302>
        
                <br>

                <ul>

<?php
            foreach ($db_tbl_param_label as $param => $label) {
                $value = htmlspecialchars($configValues[$param], ENT_QUOTES, 'UTF-8');
                
                echo '<li class="fieldset">';
                printf('<label for="%s" class="form">%s</label>', $param, $label);
                printf('<input type="text" value="%s" name="%s" id="%s" pattern="%s">', $value, $param, $param, $tbl_name_regex);
                echo '</li>';
            }
?>
                    <li class="fieldset">
                        <br><hr><br>
                        <input type="submit" name="submit" value="<?= t('buttons','apply') ?>" class="button">
                    </li>
                    
                </ul>
            </fieldset>
        </div><!-- .tabbertab -->
    </div>
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
