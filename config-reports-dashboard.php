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
    
    if (isset($_POST['submit'])) {
        
        if (array_key_exists('config_dashboard_dalo_secretkey', $_POST) && isset($_POST['config_dashboard_dalo_secretkey'])) {
            $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] = $_POST['config_dashboard_dalo_secretkey'];
        }
        
        if (array_key_exists('config_dashboard_dalo_debug', $_POST) &&  isset($_POST['config_dashboard_dalo_debug']) &&
            in_array($_POST['config_dashboard_dalo_debug'], array( "0", "1" ))) {
            $configValues['CONFIG_DASHBOARD_DALO_DEBUG'] = $_POST['config_dashboard_dalo_debug'];
        }
            
        include ("library/config_write.php");
    }    

    include_once("lang/main.php");
    
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

    $title = t('Intro','configdashboard.php');
    $help = t('helpPage','configdashboard');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-config-reports.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    // set navbar stuff
    $navbuttons = array(
                          'Dashboard-tab' => t('title','Dashboard'),
                          'Settings-tab' => t('title','Settings'),
                       );

    print_tab_navbuttons($navbuttons);
    
    
    
    $input_descriptors1 = array();
    $input_descriptors1[] = array( "name" => "config_dashboard_dalo_secretkey", "caption" => t('all','DashboardSecretKey'),
                                   "type" => "text", "value" => $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] );
    
    $input_descriptors1[] = array( "name" => "config_dashboard_dalo_debug", "caption" => t('all','DashboardDebug'),
                                   "type" => "select", "selected_value" => $configValues['CONFIG_DASHBOARD_DALO_DEBUG'],
                                   "options" => array("0", "1")
                                 );
    
    $input_descriptors2 = array();
    
    $input_descriptors2[] = array( "name" => "config_dashboard_dalo_delay_soft", "caption" => t('all','DashboardDelaySoft'),
                                   "type" => "text", "value" => $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] );

    $input_descriptors2[] = array( "name" => "config_dashboard_dalo_delay_hard", "caption" => t('all','DashboardDelayHard'),
                                   "type" => "text", "value" => $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] );


    $submit_descriptor = array(
                                "type" => "submit",
                                "name" => "submit",
                                "value" => t('buttons','apply')
                              );
?>

<form name="dashboardsettings" method="POST">

    <div id="Dashboard-tab" class="tabcontent" title="<?= t('title','Dashboard') ?>" style="display: block">

        <fieldset>
            <h302><?= t('title','Dashboard') ?></h302>

            <ul style="margin: 30px auto">
<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>
        </fieldset>

    </div><!-- #Dashboard-tab -->
                    
    <div id="Settings-tab" class="tabcontent" title="<?= t('title','Settings') ?>">
    
        <fieldset>
            <h302><?= t('title','Settings') ?></h302>

            <ul style="margin: 30px auto">
<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>
        </fieldset>

    </div><!-- #Settings-tab -->

<?php
    print_form_component($submit_descriptor);
?>

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
