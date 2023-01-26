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

    $logAction = "";

    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_REQUEST['nasaddr']) ? $nasaddr = $_REQUEST['nasaddr'] : $nasaddr = "";
    isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = "";
    isset($_REQUEST['nassecret']) ? $nassecret = $_REQUEST['nassecret'] : $nassecret = "";
    isset($_REQUEST['packettype']) ? $packettype = $_REQUEST['packettype'] : $packettype = "";
    isset($_REQUEST['customattributes']) ? $customAttributes = $_REQUEST['customattributes'] : $customAttributes = "";
    

    if (isset($_REQUEST['submit'])) {

    if ( ($nasaddr == "") || ($nasport == "") || ($nassecret == "") ) {

        $failureMsg = "One of NAS Address, NAS Port or NAS Secret fields were left empty";
        $logAction .= "Failed performing disconnect on user [$username] because of missing NAS fields on page: ";

    } else if ($username == "") {

        $failureMsg = "The User-Name to disconnect was not provided";
        $logAction .= "Failed performing disconnect on user [$username] because of missing User-Name on page: ";

    } else {

        include_once('library/extensions/maintenance_radclient.php');
        
        $username = $_REQUEST['username'];

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
                                        "debug" => $debug,
                                        );

        $successMsg = user_disconnect($options,$username,$nasaddr,$nasport,$nassecret,$packettype,$customAttributes);
        $logAction .= "Informative action performed on user [$username] on page: ";

    } 

    } //if submit


    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "static/css/tabs.css"
    );
    
    $extra_js = array(
        // js tabs stuff
        "static/js/tabs.js"
    );
    
    $title = t('Intro','configmaintdisconnectuser.php');
    $help = t('helpPage','configmaintdisconnectuser');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-config-maint.php");
    

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    include('library/opendb.php');

    // get the list of online users
    $sql = sprintf("SELECT DISTINCT(username)
                    FROM %s
                    WHERE AcctStopTime IS NULL
                       OR AcctStopTime = '0000-00-00 00:00:00'", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);

    $options = array();
    while ($row = $res->fetchRow()) {
        $options[] = $row[0];
    }

    $input_descriptors1 = array();
    $tmp = array(
                    "name" => "username",
                    "caption" => t('all','Username'),
                    "type" => "text",
                );
    
    if (count($options) > 0) {
        $tmp['datalist'] = $options;
        if (isset($username) && in_array($username, $options)) {
            $tmp['value'] = $username;
        }
    } else {
        $tmp['disabled'] = true;
    }
    
    $input_descriptors1[] = $tmp;
    
    $input_descriptors1[] = array( "name" => "packettype", "caption" => t('all','PacketType'), "type" => "select",
                                   "options" => array(
                                                        "disconnect" => 'PoD - Packet of Disconnect',
                                                        "coa" => 'CoA - Change of Authorization'
                                                     ),
                                 );

    $input_descriptors1[] = array( "name" => "nasaddr", "type" => "hidden", "value" => $nasaddr );

    // Grabing the group lists from usergroup table
    $sql = sprintf("SELECT DISTINCT(nasname), shortname, secret FROM %s", $configValues['CONFIG_DB_TBL_RADNAS']);
    $res = $dbSocket->query($sql);

    $options = array();
    while ($row = $res->fetchRow()) {
        $value = sprintf("%s||%s", $row[0], $row[2]);
        $label = sprintf("%s - %s", $row[1], $row[0]);
        $options[$value] = $label;
    }

    include('library/closedb.php');

    $tmp = array( "name" => "nasaddrlist", "caption" => t('all','NasIPHost'), "type" => "select",
                  "onchange" => "setStringTextMulti(this.id, 'nasaddr', 'nassecret')",
                );

    if (count($options) > 0) {
        $tmp['options'] = $options;
    } else {
        $tmp['disabled'] = true;
    }

    $input_descriptors1[] = $tmp;

    $input_descriptors1[] = array( "name" => "nassecret", "type" => "hidden", "value" => "" );
    $input_descriptors1[] = array( "name" => "nasport", "type" => "hidden", "value" => "" );
    
    $input_descriptors1[] = array( "name" => "nasportlist", "caption" => t('all','NasPorts'), "type" => "select",
                                   "onchange" => "setStringText(this.id, 'nasport')", "options" => array( "3799", "1700" ),
                                 );

    $input_descriptors1[] = array( "name" => "customattributes", "caption" => t('all','customAttributes'),
                                   "type" => "textarea", "content" => $customAttributes
                                 );

    $input_descriptors2 = array();
    $input_descriptors2[] = array( "name" => "debug", "caption" => t('all','Debug'), "type" => "select", "options" => array("yes", "no"), );
    $input_descriptors2[] = array( "name" => "timeout", "caption" => t('all','Timeout'), "type" => "number", "value" => "3", "min" => "1", );
    $input_descriptors2[] = array( "name" => "retries", "caption" => t('all','Retries'), "type" => "number", "value" => "3", "min" => "0", );
    $input_descriptors2[] = array( "name" => "count", "caption" => t('all','Count'), "type" => "number", "value" => "1", "min" => "1", );
    $input_descriptors2[] = array( "name" => "requests", "caption" => t('all','Requests'), "type" => "number", "value" => "3", "min" => "1", );
    
    $button_descriptor = array(
                                "type" => "submit",
                                "name" => "submit",
                                "value" => t('button','DisconnectUser')
                              );

    // set navbar stuff
    $navbuttons = array(
                          'Settings-tab' => t('title','Settings'),
                          'Advanced-tab' => t('title','Advanced'),
                       );

    print_tab_navbuttons($navbuttons);
?>

<form name="maintdisconnectuser" method="POST">
    <div id="Settings-tab" class="tabcontent" style="display: block">
        <fieldset>

            <h302><?= t('title','Settings') ?></h302>
            
            <ul style="margin: 30px auto">
<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>

        </fieldset>
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
    </div><!-- #Advanced-tab -->

<?php
        print_form_component($button_descriptor);
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
