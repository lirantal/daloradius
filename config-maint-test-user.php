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

    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_REQUEST['password']) ? $password = $_REQUEST['password'] : $password = "";
    isset($_REQUEST['radius']) ? $radius = $_REQUEST['radius'] : $radius = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'];
    isset($_REQUEST['radiusport']) ? $radiusport = $_REQUEST['radiusport'] : $radiusport = $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'];
    isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = $configValues['CONFIG_MAINT_TEST_USER_NASPORT'];
    isset($_REQUEST['secret']) ? $secret = $_REQUEST['secret'] : $secret = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'];
    isset($_REQUEST['dictionaryPath']) ? $dictionaryPath = $_REQUEST['dictionaryPath'] : $dictionaryPath = $configValues['CONFIG_PATH_RADIUS_DICT'];
        
    if (isset($_REQUEST['submit'])) {

        include_once('library/exten-maint-radclient.php');
        
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        // process advanced options to pass to radclient
        isset($_REQUEST['debug']) ? $debug = $_REQUEST['debug'] : $debug = "no";
        isset($_REQUEST['timeout']) ? $timeout = $_REQUEST['timeout'] : $timeout = 3;
        isset($_REQUEST['retries']) ? $retries = $_REQUEST['retries'] : $retries = 3;
        isset($_REQUEST['count']) ? $count = $_REQUEST['count'] : $count = 1;
        isset($_REQUEST['retries']) ? $requests = $_REQUEST['requests'] : $requests = 3;

        // create the optional arguments variable

        // convert the debug = yes to the actual debug option which is "-x" to pass to radclient
        if ($debug == "yes")
            $debug = "-x";
        else
            $debug = "";

        $options = array("count" => $count, "requests" => $requests,
                    "retries" => $retries, "timeout" => $timeout,
                    "debug" => $debug, "dictionary" => $dictionaryPath
                    );

        $successMsg = user_auth($options, $username, $password, $radius, $radiusport, $secret);
        $logAction = "Informative action performed on user [$username] on page: ";    
    }

    
    include_once('library/config_read.php');
    $log = "visited page: ";

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

    $title = t('Intro','configmainttestuser.php');
    $help = t('helpPage','configmainttestuser');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-config-maint.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $input_descriptors1 = array();
    $input_descriptors1[] = array( "name" => "username", "caption" => t('all','Username'),
                                   "type" => "text", "value" => ((isset($username)) ? $username : ""), );
    $input_descriptors1[] = array( "name" => "password", "caption" => t('all','Password'),
                                   "type" => "text", "value" => ((isset($password)) ? $password : ""), );
    $input_descriptors1[] = array( "name" => "radius", "caption" => t('all','RadiusServer'),
                                   "type" => "text", "value" => ((isset($radius)) ? $radius : ""), );
    $input_descriptors1[] = array( "name" => "radiusport", "caption" => t('all','RadiusPort'),
                                   "type" => "text", "value" => ((isset($radiusport)) ? $radiusport : ""), );
    $input_descriptors1[] = array( "name" => "nasport", "caption" => t('all','NasPorts'),
                                   "type" => "text", "value" => ((isset($nasport)) ? $nasport : ""), );
    $input_descriptors1[] = array( "name" => "secret", "caption" => t('all','NasSecret'),
                                   "type" => "text", "value" => ((isset($secret)) ? $secret : ""), );

    $input_descriptors2 = array();
    $input_descriptors2[] = array( "name" => "debug", "caption" => t('all','Debug'), "type" => "select", "options" => array("yes", "no"), );
    $input_descriptors2[] = array( "name" => "timeout", "caption" => t('all','Timeout'), "type" => "number", "value" => "3", "min" => "1", );
    $input_descriptors2[] = array( "name" => "retries", "caption" => t('all','Retries'), "type" => "number", "value" => "3", "min" => "0", );
    $input_descriptors2[] = array( "name" => "count", "caption" => t('all','Count'), "type" => "number", "value" => "1", "min" => "1", );
    $input_descriptors2[] = array( "name" => "requests", "caption" => t('all','Requests'), "type" => "number", "value" => "3", "min" => "1", );
    $input_descriptors2[] = array( "name" => "dictionaryPath", "caption" => t('all','RADIUSDictionaryPath'), "type" => "text",
                                   "value" => ((isset($dictionaryPath)) ? $dictionaryPath : ""), );

    $button_descriptor = array(
                                "type" => "submit",
                                "name" => "submit",
                                "value" => "Test"
                              );

    // set navbar stuff
    $navbuttons = array(
                          'Settings-tab' => t('title','Settings'),
                          'Advanced-tab' => t('title','Advanced'),
                       );

    print_tab_navbuttons($navbuttons);
?>

<form name="mainttestuser" method="POST">
    
    <div id="Settings-tab" class="tabcontent" title="<?= t('title','Settings') ?>" style="display: block">
        <fieldset>

            <h302> Test User Connectivity </h302>
            
            <ul style="margin: 30px auto">
<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>

        </fieldset>
        
<?php
        print_form_component($button_descriptor);
?>
            
    </div><!-- #Settings-tab -->
        
        <div id="Advanced-tab" class="tabcontent" title="<?= t('title','Advanced') ?>">
            <fieldset>

                <h302><?= t('title','Advanced') ?></h302>

            <ul style="margin: 30px auto">
<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>

            </fieldset>

<?php
        print_form_component($button_descriptor);
?>
            
    </div><!-- #Advanced-tab -->
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
