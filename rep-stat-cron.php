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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');
    
    $cronUser = get_current_user();
    
    // validating params
    $cmd = (array_key_exists('cmd', $_GET) && isset($_GET['cmd']) &&
            in_array(strtolower($_GET['cmd']), array( "enable", "disable" )))
         ? strtolower($_GET['cmd']) : "";

    $dalo_crontab_file = dirname(__FILE__) . '/contrib/scripts/dalo-crontab';
    
    $exec = "";
    
    switch ($cmd) {
        case "disable":
        $exec = sprintf("$(which crontab || command -v crontab) -u %s -r", escapeshellarg($cronUser));
        break;
        
        case "enable":
        $exec = sprintf("$(which crontab || command -v crontab) -u %s %s", escapeshellarg($cronUser), $dalo_crontab_file);
        break;
    }
    
    if (!empty($exec)) {
        exec($exec);
    }
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = "CRON Status";
    $help = "";
    
    print_html_prologue($title, $langCode);
    
    include("include/menu/sidebar.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
?>    

        <div style="margin: 20px auto">
            <h3>CRON Entries</h3>
            <a title="Enable CRON" href="?cmd=enable">Enable CRON</a>
            &nbsp;
            <a title="Disable CRON" href="?cmd=disable">Disable CRON</a>
        </div>

<?php
    $failureMsg = "";

    $exec = sprintf("$(which crontab || command -v crontab) -u %s -l", escapeshellarg($cronUser));
    exec($exec, $output, $retStatus);

    if ($retStatus !== 0) {
        $failureMsg = '<strong>Error</strong> no crontab is configured for this user or user does not exist<br><br>';
    } else {
        $i = 1;
        foreach($output as $text) {
            printf('<strong>#%d</strong>: %s<br>', $i, htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
            $i++;
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
