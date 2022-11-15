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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once('library/config_read.php');
    
    include_once("lang/main.php");
    
    // we import validation facilities
    include_once("library/validation.php");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nasname = (array_key_exists('nasname', $_POST) && isset($_POST['nasname'])) ? trim(str_replace("%", "", $_POST['nasname'])) : "";
        $nassecret = (array_key_exists('nassecret', $_POST) && isset($_POST['nassecret'])) ? trim(str_replace("%", "", $_POST['nassecret'])) : "";
        
        $nasname_enc = (!empty($nasname)) ? htmlspecialchars($nasname, ENT_QUOTES, 'UTF-8') : "";
        
        if (empty($nasname) || empty($nassecret)) {
            // required
            $failureMsg = sprintf("%s and/or %s are empty or invalid", t('all','NasIPHost'), t('all','NasSecret'));
            $logAction .= "Failed adding (possible empty user/pass) new operator on page: ";
        } else {
            include('library/opendb.php');
            
            $sql = sprintf("SELECT COUNT(id) FROM %s WHERE nasname='%s'", $configValues['CONFIG_DB_TBL_RADNAS'],
                                                                          $dbSocket->escapeSimple($nasname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            $exists = $res->fetchrow()[0] > 0;
            
            if ($exists) {
                // name already taken
                $failureMsg = sprintf("This %s already exists: <b>%s</b>", t('all','NasIPHost'), $nasname_enc);
                $logAction .= "Failed adding a new NAS [$nasname already exists] on page: ";
            } else {
                $shortname = (array_key_exists('shortname', $_POST) && isset($_POST['shortname'])) ? trim(str_replace("%", "", $_POST['shortname'])) : "";
                $nasports = (array_key_exists('nasports', $_POST) && isset($_POST['nasports'])) ? trim(str_replace("%", "", $_POST['nasports'])) : "";
                $nastype = (array_key_exists('nastype', $_POST) && isset($_POST['nastype']) &&
                            in_array($_POST['nastype'], $valid_nastypes)) ? $_POST['nastype'] : $valid_nastypes[0];
                $nasdescription = (array_key_exists('nasdescription', $_POST) && isset($_POST['nasdescription'])) ? trim(str_replace("%", "", $_POST['nasdescription'])) : "";
                $nascommunity = (array_key_exists('nascommunity', $_POST) && isset($_POST['nascommunity'])) ? trim(str_replace("%", "", $_POST['nascommunity'])) : "";
                $nasvirtualserver = (array_key_exists('nasvirtualserver', $_POST) && isset($_POST['nasvirtualserver'])) ? trim(str_replace("%", "", $_POST['nasvirtualserver'])) : "";
                
                $sql = sprintf("INSERT INTO %s (id, nasname, shortname, type, ports, secret, server, community, description)
                                        VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $configValues['CONFIG_DB_TBL_RADNAS'],
                               $dbSocket->escapeSimple($nasname), $dbSocket->escapeSimple($shortname), $dbSocket->escapeSimple($nastype),
                               $dbSocket->escapeSimple($nasports), $dbSocket->escapeSimple($nassecret), $dbSocket->escapeSimple($nasvirtualserver),
                               $dbSocket->escapeSimple($nascommunity), $dbSocket->escapeSimple($nasdescription));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    // it seems that operator could not be added
                    $f = "Failed to add this new NAS [%s] to database";
                    $failureMsg = sprintf($f, $nasname_enc);
                    $logAction .= sprintf($f, $nasname_enc);
                } else {
                    $successMsg = sprintf("Added to database new NAS: <strong>%s</strong>", $nasname_enc);
                    $logAction .= sprintf("Successfully added new NAS [%s] on page: ", $nasname);
                }
            }
            
            include('library/closedb.php');
        }

    }

    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        // js tabs stuff
        "library/javascript/tabs.js"
    );

    $title = t('Intro','mngradnasnew.php');
    $help = t('helpPage','mngradnasnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-rad-nas.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "nasname",
                                        "caption" => t('all','NasIPHost'),
                                        "type" => "text",
                                        "value" => ((isset($nasname)) ? $nasname : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "nassecret",
                                        "caption" => t('all','NasSecret'),
                                        "type" => "text",
                                        "value" => ((isset($nassecret)) ? $nassecret : "")
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "nastype",
                                        "caption" => t('all','NasType'),
                                        "type" => "text",
                                        "datalist" => $valid_nastypes,
                                        "value" => ((isset($nastype)) ? $nastype : $valid_nastypes[0])
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "shortname",
                                        "caption" => t('all','NasShortname'),
                                        "type" => "text",
                                        "value" => ((isset($shortname)) ? $shortname : "")
                                     );

        
        $input_descriptors2 = array();
        
        $input_descriptors2[] = array(
                                        "name" => "nasports",
                                        "caption" => t('all','NasPorts'),
                                        "type" => "number",
                                        "min" => "0",
                                        "max" => "65535",
                                        "value" => ((isset($nasports)) ? $nasports : "")
                                     );
                                     
        $input_descriptors2[] = array(
                                        "name" => "nascommunity",
                                        "caption" => t('all','NasCommunity'),
                                        "type" => "text",
                                        "value" => ((isset($nascommunity)) ? $nascommunity : "")
                                     );
                                     
        $input_descriptors2[] = array(
                                        "name" => "nasvirtualserver",
                                        "caption" => t('all','NasVirtualServer'),
                                        "type" => "text",
                                        "value" => ((isset($nasvirtualserver)) ? $nasvirtualserver : "")
                                     );
                                     
        $input_descriptors2[] = array(
                                        "name" => "nasdescription",
                                        "caption" => t('all','NasDescription'),
                                        "type" => "textarea",
                                        "content" => ((isset($nasdescription)) ? $nasdescription : "")
                                     );

        $submit_descriptor = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                  );

        // set navbar stuff
        $navbuttons = array(
                              'NASInfo-tab' => t('title','NASInfo'),
                              'NASAdvanced-tab' => t('title','NASAdvanced'),
                           );

        print_tab_navbuttons($navbuttons);

?>

<form name="newnas" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="tabcontent" id="NASInfo-tab" style="display: block">
        <fieldset>
            <h302><?= t('title','NASInfo') ?></h302>
        
            <ul style="margin: 30px auto">

<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>

            </ul>
        </fieldset>
     </div><!-- #NASInfo-tab -->
    
    <div class="tabcontent" id="NASAdvanced-tab">
        <fieldset>
            <h302><?= t('title','NASAdvanced') ?></h302>
            <ul style="margin: 30px auto">

<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>

            </ul>
        </fieldset>
    </div><!-- #NASAdvanced-tab -->
    
<?php
        print_form_component($submit_descriptor);
?>

</form>

<?php
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
