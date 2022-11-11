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
    $nashost = "";
    $nassecret = "";
    $nasname = "";
    $nasports = "";
    $nastype = "";
    $nasdescription = "";
    $nascommunity = "";
    $nasvirtualserver = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {
    
        $nashost = $_POST['nashost'];
        $nassecret = $_POST['nassecret'];
        $nasname = $_POST['nasname'];
        $nasports = $_POST['nasports'];
        $nastype = $_POST['nastype'];
        $nasdescription = $_POST['nasdescription'];
        $nascommunity = $_POST['nascommunity'];
        $nasvirtualserver = $_POST['nasvirtualserver'];

        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].
                " WHERE nasname='".$dbSocket->escapeSimple($nashost)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {

            if (trim($nashost) != "" and trim($nassecret) != "") {

                if (!$nasports) {
                    $nasports = 0;
                }
                
                if (!$nasvirtualserver) {
                      $nasvirtualserver = '';
               }

                // insert nas details
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADNAS'].
                    " (id,nasname,shortname,type,ports,secret,server,community,description) ".
                    " values (0, '".$dbSocket->escapeSimple($nashost)."', '".$dbSocket->escapeSimple($nasname).
                    "', '".$dbSocket->escapeSimple($nastype)."', '".$dbSocket->escapeSimple($nasports).
                    "', '".$dbSocket->escapeSimple($nassecret)."', '".$dbSocket->escapeSimple($nasvirtualserver).
                    "', '".$dbSocket->escapeSimple($nascommunity)."', '".$dbSocket->escapeSimple($nasdescription)."')";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
            
                $successMsg = "Added new NAS to database: <b> $nashost </b>  ";
                $logAction .= "Successfully added nas [$nashost] on page: ";
            } else {
                $failureMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
                $logAction .= "Failed adding (missing nas/secret) nas [$nashost] on page: ";
            }
        } else {
            $failureMsg = "The NAS IP/Host $nashost already exists in the database";    
            $logAction .= "Failed adding already existing nas [$nashost] on page: ";
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

    $title = t('Intro','mngradnasnew.php');
    $help = t('helpPage','mngradnasnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-rad-nas.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

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

        <h302> <?= t('title','NASInfo') ?> </h302>
        <br/>

                <label for='nashost' class='form'><?= t('all','NasIPHost') ?></label>
                <input name='nashost' type='text' id='nashost' value='' tabindex=100 />
                <br />


                <label for='nassecret' class='form'><?= t('all','NasSecret') ?></label>
                <input name='nassecret' type='text' id='nassecret' value='' tabindex=101 />
                <br />


                <label for='nastype' class='form'><?= t('all','NasType') ?></label>
                <input name='nastype' type='text' id='nastype' value='' tabindex=102 />
                <select onChange="javascript:setStringText(this.id,'nastype')" id="optionSele" tabindex=103 class='form'>
                    <option value="">Select Type...</option>
                    <option value="other">other</option>
                    <option value="cisco">cisco</option>
                    <option value="livingston">livingston</option>
                    <option value="computon">computon</option>
                    <option value="max40xx">max40xx</option>
                    <option value="multitech">multitech</option>
                    <option value="natserver">natserver</option>
                    <option value="pathras">pathras</option>
                    <option value="patton">patton</option>
                    <option value="portslave">portslave</option>
                    <option value="tc">tc</option>
                    <option value="usrhiper">usrhiper</option>
                   </select>
                <br />
        

                <label for='nasname' class='form'><?= t('all','NasShortname') ?></label>
                <input name='nasname' type='text' id='nasname' value='' tabindex=104 />
                <br />

        </fieldset>


     </div>
    
    <div class="tabcontent" id="NASAdvanced-tab">
        <fieldset>

        <h302> <?= t('title','NASAdvanced') ?> </h302>
        <br/>

                <label for='nasports' class='form'><?= t('all','NasPorts') ?></label>
                <input name='nasports' type='text' id='nasports' value='0' tabindex=105 />
                <br />

                <label for='nascommunity' class='form'><?= t('all','NasCommunity') ?></label>
                <input name='nascommunity' type='text' id='nascommunity' value='' tabindex=106 />
                <br />

                <label for='nasvirtualserver' class='form'><?= t('all','NasVirtualServer') ?></label>
                <input name='nasvirtualserver' type= 'text' id='nasvirtualserver' value='' tabindex=107 >
                <br />

                <label for='nasdescription' class='form'><?= t('all','NasDescription') ?></label>
                <textarea class='form' name='nasdescription' id='nasdescription' value='' tabindex=108 ></textarea>
                <br />
                
        </fieldset>

    </div>
    
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
