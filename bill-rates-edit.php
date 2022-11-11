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


    include 'library/opendb.php';

        isset($_REQUEST['ratename']) ? $ratename = $_REQUEST['ratename'] : $ratename = "";
        isset($_REQUEST['ratecost']) ? $ratecost = $_REQUEST['ratecost'] : $ratecost = "";
        isset($_REQUEST['ratetypenum']) ? $ratetypenum = $_REQUEST['ratetypenum'] : $ratetypenum = "";
        isset($_REQUEST['ratetypetime']) ? $ratetypetime = $_REQUEST['ratetypetime'] : $ratetypetime = "";

    $edit_ratename = $ratename; //feed the sidebar variables

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {

                $ratename = $_POST['ratename'];
                $ratetypenum = $_POST['ratetypenum'];
                $ratetypetime = $_POST['ratetypetime'];
                $ratecost = $_POST['ratecost'];

        if (trim($ratename) != "") {

            $currDate = date('Y-m-d H:i:s');
            $currBy = $_SESSION['operator_user'];

            $ratetype = "$ratetypenum/$ratetypetime";

            $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." SET ".
            " rateName='".$dbSocket->escapeSimple($ratename)."', ".
            " rateType='".$dbSocket->escapeSimple($ratetype).    "', ".
            " rateCost='".$dbSocket->escapeSimple($ratecost)."', ".
            " updatedate='$currDate', updateby='$currBy' ".
            " WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
            $res = $dbSocket->query($sql);
            $logDebugSQL = "";
            $logDebugSQL .= $sql . "\n";

            $successMsg = "Updated rate: <b> $ratename </b>";
            $logAction .= "Successfully updated rate [$ratename] on page: ";

        } else {
            $failureMsg = "no rate name was entered, please specify a rate name to edit.";
            $logAction .= "Failed updating rate [$ratename] on page: ";
        }

    }


    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
    $res = $dbSocket->query($sql);
    $logDebugSQL .= $sql . "\n";

    $row = $res->fetchRow();
    $ratename = $row[1];
    list($ratetypenum, $ratetypetime) = explode("/",$row[2]);
    $ratecost = $row[3];
    $creationdate = $row[4];
    $creationby = $row[5];
    $updatedate = $row[6];
    $updateby = $row[7];

    include 'library/closedb.php';


    if (trim($ratename) == "") {
        $failureMsg = "no rate name was entered or found in database, please specify a rate name to edit";
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
    
    $title = t('Intro','billratesedit.php');
    $help = t('helpPage','billratesedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (isset($ratename)) {
        $title .= ":: $ratename";
    } 

    include("menu-bill-rates.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $input_descriptors2 = array();
    $input_descriptors2[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
    $input_descriptors2[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
    $input_descriptors2[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
    $input_descriptors2[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );
    
    // set navbar stuff
    $navbuttons = array(
                          'RateInfo-tab' => t('title','RateInfo'),
                          'Optional-tab' => t('title','Optional'),
                       );

    print_tab_navbuttons($navbuttons);

?>

<form method="post">
    <div class="tabcontent" id="RateInfo-tab" style="display: block">


    <fieldset>

        <h302> <?php echo t('title','RateInfo'); ?> </h302>
        <br/>

        <ul>

            <li class='fieldset'>
            <label for='ratename' class='form'><?php echo t('all','RateName') ?></label>
            <input disabled name='ratename' type='text' id='ratename' value='<?php echo $ratename ?>' tabindex=100 />
            </li>

            <li class='fieldset'>
            <label for='ratetype' class='form'><?php echo t('all','RateType') ?></label>

                    <input class='integer' name='ratetypenum' type='text' id='ratetypenum' value='<?php echo $ratetypenum ?>' tabindex=101 />
                    <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratetypenum','increment')" />
                    <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratetypenum','decrement')"/>

                    <select class='form' tabindex=102 name='ratetypetime' id='ratetypetime' >
                <option value='<?php echo $ratetypetime ?>'><?php echo $ratetypetime ?></option>
                <option value=''></option>
                            <option value='second'>second</option>
                             <option value='minute'>minute</option>
                <option value='hour'>hour</option>
                            <option value='day'>day</option>
                            <option value='week'>week</option>
                            <option value='month'>month</option>
                    </select>
            <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateTypeTooltip')" />

            <div id='rateTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                <img src='images/icons/comment.png' alt='Tip' border='0' />
                <?php echo t('Tooltip','rateTypeTooltip') ?>
            </div>
            </li>

            <li class='fieldset'>
            <label for='ratecost' class='form'><?php echo t('all','RateCost') ?></label>
            <input class='integer' name='ratecost' type='text' id='ratecost' value='<?php echo $ratecost ?>' tabindex=103 />
                    <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratecost','increment')" />
                    <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratecost','decrement')"/>
            <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateCostTooltip')" />

            <div id='rateCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                <img src='images/icons/comment.png' alt='Tip' border='0' />
                <?php echo t('Tooltip','rateCostTooltip') ?>
            </div>
            </li>



        </ul>

    </fieldset>

    <input type=hidden value="<?php echo $ratename ?>" name="ratename"/>

</div>


    <div class="tabcontent" id="Optional-tab">
        <fieldset>

            <h302> Optional </h302>
            <h301> Other </h301>
            
            <ul style="margin: 30px auto">

<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>
        </fieldset>
    </div>

    <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />

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
