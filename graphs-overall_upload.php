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

    // validate (or pre-validate) parameters
    $goto_stats = (array_key_exists('goto_stats', $_GET) && isset($_GET['goto_stats']));
    
    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    $size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
             in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
          ? strtolower($_GET['size']) : "megabytes";

    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace('%', '', $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $overall_upload_username = $username_enc;
    $overall_upload_type = $type;
    $overall_upload_size = $size;

    include_once('library/config_read.php');

    // init logging variables
    $log = "visited page: ";
    if (!empty($username)) {
        $logQuery = "performed query for user [$username] of type [$type] on page: ";
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
    
    $title = t('Intro','graphsoverallupload.php');
    $help = t('helpPage','graphsoverallupload');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-graphs.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    if (!empty($username)) {
        $src = sprintf("library/graphs-overall-users-data.php?category=upload&type=%s&size=%s&user=%s", $type, $size, $username_enc);
        $alt = sprintf("traffic uploaded by user %s", $username_enc);
        
        // set navbar stuff
        $navbuttons = array(
                              'Graph-tab' => "Graph",
                              'Statistics-tab' => "Statistics",
                           );

        print_tab_navbuttons($navbuttons);
?>

            <div class="tabcontent" id="Graph-tab" style="display: block">
                <div style="text-align: center; margin-top: 50px">
                    <img alt="<?= $alt ?>" src="<?= $src ?>">
                </div>
            </div>

            <div class="tabcontent" id="Statistics-tab">
                <div style="margin-top: 50px">
<?php
        include("library/tables-overall-users-upload.php");
        
        if ($goto_stats) {
?>
                    <script>
                        window.addEventListener('load', function() {
                            var stats_tab = document.getElementById('Statistics-tab'),
                                stats_btn = document.getElementById(stats_tab.id + '-button');
                            stats_btn.click();
                        });
                    </script>
<?php  
        }
?>
                </div>
            </div>

<?php
    } else {
        $failureMsg = "You must provide a valid username";
        include_once("include/management/actionMessages.php");
    }
?>
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
