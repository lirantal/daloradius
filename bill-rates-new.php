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
 *
 *********************************************************************************************************
 */
 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    isset($_POST['ratename']) ? $ratename = $_POST['ratename'] : $ratename = "";
    isset($_POST['ratetypenum']) ? $ratetypenum = $_POST['ratetypenum'] : $ratetypenum = "";
    isset($_POST['ratetypetime']) ? $ratetypetime = $_POST['ratetypetime'] : $ratetypetime = "";
    isset($_POST['ratecost']) ? $ratecost = $_POST['ratecost'] : $ratecost = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST["submit"])) {
        $ratename = $_POST['ratename'];
        $ratetypenum = $_POST['ratetypenum'];
        $ratetypetime = $_POST['ratetypetime'];
        $ratecost = $_POST['ratecost'];
        
        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {
            if (trim($ratename) != "" and trim($ratetypenum) != "" and trim($ratetypetime) != "" and trim($ratecost) != "") {

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];
                
                $ratetype = "$ratetypenum/$ratetypetime";

                // insert rate info
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].
                    " (id, ratename, ratetype, ratecost, ".
                    "  creationdate, creationby, updatedate, updateby) ".
                    " VALUES (0, '".$dbSocket->escapeSimple($ratename)."', '".
                    $dbSocket->escapeSimple($ratetype)."',".$dbSocket->escapeSimple($ratecost).",".
                    " '$currDate', '$currBy', NULL, NULL)";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $successMsg = "Added to database new rate: <b>$ratename</b>";
                $logAction .= "Successfully added new rate [$ratename] on page: ";
            } else {
                $failureMsg = "you must provide a rate name, type and cost";    
                $logAction .= "Failed adding new rate [$ratename] on page: ";    
            }
        } else { 
            $failureMsg = "You have tried to add a rate that already exist in the database: $ratename";
            $logAction .= "Failed adding new rate already in database [$ratename] on page: ";        
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
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','billratesnew.php');
    $help = t('helpPage','billratesnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (isset($paymentname)) {
        $title .= ":: $paymentname";
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

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="tabcontent" id="RateInfo-tab" style="display: block">

        <fieldset>

        <h302> <?php echo t('title','RateInfo'); ?> </h302>
        <br/>

        <ul>

        <li class='fieldset'>
        <label for='name' class='form'><?php echo t('all','RateName') ?></label>
        <input name='ratename' type='text' id='ratename' value='' tabindex=100 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateNameTooltip')" /> 
        
        <div id='rateNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','rateNameTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='ratetype' class='form'><?php echo t('all','RateType') ?></label>

        <input class='integer' name='ratetypenum' type='text' id='ratetypenum' value='1' tabindex=101 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratetypenum','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratetypenum','decrement')"/>

                <select class='form' tabindex=102 name='ratetypetime' id='ratetypetime' >
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
        <input class='integer' name='ratecost' type='text' id='ratecost' value='1' tabindex=103 />
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

    
        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
                class='button' />

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
