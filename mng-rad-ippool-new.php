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

    // declaring variables
    $poolname = "";
    $ipaddress = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {
    
        $poolname = $_POST['poolname'];
        $ipaddress = $_POST['ipaddress'];
        
        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADIPPOOL'].
            " WHERE pool_name='".$dbSocket->escapeSimple($poolname)."'".
            " AND framedipaddress='".$dbSocket->escapeSimple($ipaddress)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {

            if (trim($poolname) != "" and trim($ipaddress) != "") {

                // insert ippool name and ip address
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADIPPOOL'].
                    " (pool_name, framedipaddress) ".
                    " VALUES ('".$dbSocket->escapeSimple($poolname)."', '".
                    $dbSocket->escapeSimple($ipaddress)."')";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
            
                $successMsg = "Added to database new IP Address: <b>$ipaddress</b> for Pool Name: <b>$poolname</b>";
                $logAction .= "Successfully added IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
            } else {
                $failureMsg = "No IP Address or Pool Name was entered, it is required that you specify both";
                $logAction .= "Failed adding (missing ipaddress/poolname) IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
            }
        } else {
            $failureMsg = "The IP Address <b>$ipaddress</b> for Pool Name <b>$poolname</b> already exists in the database";
            $logAction .= "Failed adding already existing IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
        }

        include 'library/closedb.php';
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

    $title = t('Intro','mngradippoolnew.php');
    $help = t('helpPage','mngradippoolnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-rad-ippool.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
?>

            <form name="newippool" method="POST">

                <fieldset>

                    <h302> <?= t('title','IPPoolInfo') ?> </h302>
                    <br/>

                    <label for='poolname' class='form'><?= t('all','PoolName') ?></label>
                    <input name='poolname' type='text' id='poolname' value='<?= $poolname ?>' tabindex=100 />
                    <br />

                    <label for='ipaddress' class='form'><?= t('all','IPAddress') ?></label>
                    <input name='ipaddress' type='text' id='ipaddress' value='<?= $ipaddress ?>' tabindex=101 />
                    <br />

                </fieldset>

                <input type='submit' name='submit' value='<?= t('buttons','apply') ?>' class='button' />

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
