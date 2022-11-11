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


    // declaring variables
    $nasipaddress = "";
    $groupname = "";
    $nasportid = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {
    
        $nasipaddress = $_POST['nasipaddress'];
        $groupname = $_POST['groupname'];
        $nasportid = $_POST['nasportid'];

        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADHG'].
                " WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddress)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {

            if (trim($nasipaddress) != "" and trim($groupname) != "") {

                if (!$nasportid) {
                    $nasportid = 0;
                }
                
                // insert nas details
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADHG'].
                    " (id,nasipaddress,groupname,nasportid) ".
                    " values (0, '".$dbSocket->escapeSimple($nasipaddress)."', '".$dbSocket->escapeSimple($groupname).
                    "', '".$dbSocket->escapeSimple($nasportid)."')";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
            
                $successMsg = "Added new HG to database: <b> $nasipaddress </b>  ";
                $logAction .= "Successfully added hg [$nasipaddress] on page: ";
            } else {
                $failureMsg = "no HG Host or HG GroupName was entered, it is required that you specify both HG Host and HG GroupName";
                $logAction .= "Failed adding (missing ip/groupname) hg [$nasipaddress] on page: ";
            }
        } else {
            $failureMsg = "The HG IP/Host $nasipaddress already exists in the database";    
            $logAction .= "Failed adding already existing hg [$nasipaddress] on page: ";
        }

        include 'library/closedb.php';
    }
    

    include_once('library/config_read.php');
    $log = "visited page: ";

    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue    
    $title = t('Intro','mngradhuntnew.php');
    $help = t('helpPage','mngradhuntnew');
    
    print_html_prologue($title, $langCode);

    if (isset($ratename)) {
        $title .= ":: $ratename";
    } 

    include("menu-mng-rad-hunt.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

?>

<form name="newhg" method="post">


    <fieldset>

        <h302> <?php echo t('title','HGInfo') ?> </h302>
        <br/>

                <label for='nasipaddress' class='form'><?php echo t('all','HgIPHost') ?></label>
                <input name='nasipaddress' type='text' id='nasipaddress' value='' tabindex=100 />
                <br />


                <label for='groupname' class='form'><?php echo t('all','HgGroupName') ?></label>
                <input name='groupname' type='text' id='groupname' value='' tabindex=101 />
                <br />

                <label for='nasportid' class='form'><?php echo t('all','HgPortId') ?></label>
                <input name='nasportid' type='text' id='nasportid' value='0' tabindex=105 />
                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

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
