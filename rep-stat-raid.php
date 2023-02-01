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
 * Authors:     Liran Tal <liran@enginx.com>
 *              Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');

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
    
    $title = "RAID Status";
    $help = "";
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("include/menu/sidebar.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    $failureMsg = "";
    $error = '<strong>Error</strong> accessing RAID device information';
    
    if (!file_exists('/proc/mdstat')) {
        $failureMsg = $error;
    } else {
        exec("cat /proc/mdstat | awk '/md/ {print $1}'", $mdstat, $retStatus);
        
        if ($retStatus !== 0) {
            $failureMsg = $error;
        } else {
            if (count($mdstat) > 0) {
                
                $navbuttons = array();
                foreach($mdstat as $mddevice) {
                    $key = sprintf("%s-tab", $mddevice);
                    $navbuttons[$key] = $mddevice;
                }
                
                print_tab_navbuttons($navbuttons);
                
                $counter = 0;
                foreach($mdstat as $mddevice) {
                    printf('<div class="tabcontent" id="%s-tab"', htmlspecialchars($mddevice, ENT_QUOTES, 'UTF-8'));
                    
                    if ($counter == 0) {
                        echo ' style="display: block"';
                    }
                    
                    echo '>';
                    
                    $dev = "/dev/$mddevice";
                    $cmd = sprintf("sudo /sbin/mdadm --detail %s", escapeshellarg($dev));
                    $output = "";
                    exec($cmd, $output);

                    echo '<table class="summarySection">';
                    foreach($output as $line) {
                        list($var, $val) = split(":", $line);
                        $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
                        $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                        
                        printf('<tr><td class="summaryKey">%s</td>' .
                               '<td class="summaryValue"><span class="sleft">%s</span></td></tr>', $var, $val); 
                    }
                    echo '</table>'
                       . '</div>';
                       
                    $counter++;

                }
                

            } else {
                $failureMsg = $error;
            }
        }
    }
    
    if (!empty($failureMsg)) {
        include_once('include/management/actionMessages.php');
    }

?>
            
        </div>
        
        <div id="footer">
<?php
    include('page-footer.php');
?>
        </div>
        
    </div>
</div>

</body>
</html>
