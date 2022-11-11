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



    include 'library/opendb.php';

    $nasipaddress = "";
    $nasportid = "";
    $groupname = "";

    isset($_REQUEST['nasipaddress']) ? $nasipaddress = $_REQUEST['nasipaddress'] : $nasipaddress = "";
    isset($_REQUEST['groupname']) ? $groupname = $_REQUEST['groupname'] : $groupname = "";

    $logAction = "";
    $logDebugSQL = "";

    // fill-in nashost details in html textboxes
    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADHG']." WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddress).
            "' AND groupname='".$dbSocket->escapeSimple($groupname)."'";
    $res = $dbSocket->query($sql);
    $logDebugSQL = "";
    $logDebugSQL .= $sql . "\n";

    $row = $res->fetchRow();        // array fetched with values from $sql query

    // assignment of values from query to local variables
    // to be later used in html to display on textboxes (input)
    $groupname = $row[1];
    $nasipaddress = $row[2];
    $nasportid = $row[3];
    

    if (isset($_POST['submit'])) {
    
        $nasipaddressold = $_REQUEST['nasipaddressold'];
        $nasipaddress = $_REQUEST['nasipaddress'];
        $nasportid = $_REQUEST['nasportid'];
        $nasportidold = $_REQUEST['nasportidold'];
        $groupname = $_REQUEST['groupname'];

            
        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADHG'].
        " WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddressold).
        "' AND nasportid='". $dbSocket->escapeSimple($nasportidold)."' ";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 1) {

            if (trim($nasipaddress) != "" and trim($groupname) != "") {

                if (!$nasportid) {
                    $nasportid = 0;
                }

                // insert nas details
                $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADHG'].
                    " SET nasipaddress='".$dbSocket->escapeSimple($nasipaddress)."', ".
                    " groupname='".$dbSocket->escapeSimple($groupname)."', ".
                    " nasportid=".$dbSocket->escapeSimple($nasportid)." ".
                    " WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddressold)."'".
                    " AND nasportid='". $dbSocket->escapeSimple($nasportidold)."' ";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $successMsg = "Updated HG settings in database: <b> $nasipaddress - $nasportid</b>  ";
                $logAction .= "Successfully updated attributes for hg [$nasipaddress - $nasportid] on page: ";
            } else {
                $failureMsg = "no HG Host or HG GroupName was entered, it is required that you specify both hg Host and HG GroupName ";
                $logAction .= "Failed updating attributes for hg [$nasipaddress - $nasportid] on page: ";
            }
            
        } elseif ($res->numRows() > 1) {
            $failureMsg = "The HG IP/Host - Port <b> $nasipaddress  - $nasportid</b> already exists in the database
            <br/> Please check that there are no duplicate entries in the database";
            $logAction .= "Failed updating attributes for already existing hg [$nasipaddress - $nasportid] on page: ";
        } else {
            $failureMsg = "The HG IP/Host - Port <b> $nasipaddress  - $nasportid</b> doesn't exist at all in the database.
            <br/>Please re-check the nashost ou specified.";
            $logAction .= "Failed updating empty nas on page: ";
        }

        include 'library/closedb.php';
    }

    if (isset($_REQUEST['nasipaddress']))
        $nasipaddress = $_REQUEST['nasipaddress'];
    else
        $nasipaddress = "";

    if (trim($nasipaddress) == "") {
        $failureMsg = "no HG Host or HG GroupName was entered, it is required that you specify both HG Host and HG GroupName";
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
    
    $title = t('Intro','mngradhuntedit.php');
    $help = t('helpPage','mngradhuntedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (isset($nasipaddress)) {
        $title .= ":: $nasipaddress";
    } 

    include("menu-mng-rad-hunt.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
?>

<form name="newhg" method="post">
            

        <input type="hidden" value="<?php echo $nasipaddress ?>" name="nasipaddressold" />
        <input type="hidden" value="<?php echo $nasportid ?>" name="nasportidold" />


        <fieldset>

                <h302> <?php echo t('title','HGInfo') ?> </h302>
                <br/>

                <label for='nasipaddress' class='form'><?php echo t('all','HgIPHost') ?></label>
                <input name='nasipaddress' type='text' id='nasipaddress' value='<?php echo $nasipaddress ?>' tabindex=100 />
                <br />

                <label for='groupname' class='form'><?php echo t('all','HgGroupName') ?></label>
                <input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=101 />
                <br />


                <label for='nasportid' class='form'><?php echo t('all','HgPortId') ?></label>
                <input name='nasportid' type='text' id='nasportid' value='<?php echo $nasportid ?>' tabindex=104 />
                <br />



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
