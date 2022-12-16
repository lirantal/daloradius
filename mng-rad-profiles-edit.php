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
    include_once('library/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $profile_name = (array_key_exists('profile_name', $_POST) && !empty($_POST['profile_name']) &&
                         str_replace("%", "", trim($_POST['profile_name'])))
                      ? str_replace("%", "", trim($_POST['profile_name'])) : "";
    } else {
        $profile_name = (array_key_exists('profile_name', $_REQUEST) && isset($_REQUEST['profile_name']) &&
                         str_replace("%", "", trim($_REQUEST['profile_name'])))
                      ? str_replace("%", "", trim($_REQUEST['profile_name'])) : "";
    }
    
    $profile_name_enc = (!empty($profile_name)) ? htmlspecialchars($profile_name, ENT_QUOTES, 'UTF-8') : "";


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
    
    $title = t('Intro','mngradprofilesedit.php');
    $help = t('helpPage','mngradprofilesedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (!empty($profile_name_enc)) {
        $title .= " :: $profile_name_enc";
    } 

    include_once('include/management/populate_selectbox.php');
    include("menu-mng-rad-profiles.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        
        $groups = array_keys(get_groups());
        
        // check if it exists
        if (!in_array($profile_name, $groups)) {
            // we empty the profile if the hs does not exist
            $profile_name = "";
        } else {
            include('library/opendb.php');
            include("library/attributes.php");
            $skipList = array( "profile_name", "submit", "csrf_token" );
            $count = handleAttributes($dbSocket, $profile_name, $skipList, false, 'group');
            include('library/closedb.php');
            
            $successMsg = "Updated attributes for: <b> $profile_name_enc </b>";
            $logAction .= "Successfully updates attributes for profile [$profile_name] on page:";
        }
    }
    
    if (empty($profile_name)) {
        $failureMsg = "Profile name is invalid or empty";
        $logAction .= "Failed updating (possible empty or invalid profile name) profile on page: ";
    }
    
    include_once('include/management/actionMessages.php');
    
    if (!empty($profile_name)) { 
        
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );
        $input_descriptors1[] = array(
                                        "name" => "profile_name",
                                        "type" => "hidden",
                                        "value" => $profile_name,
                                     );
        
        $input_descriptors2 = array();
        $input_descriptors2[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
        $input_descriptors2[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
        $input_descriptors2[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
        $input_descriptors2[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                       'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );
        
        $submit_descriptor = array(
                                    "type" => "submit",
                                    "name" => "submit",
                                    "value" => t('buttons','apply')
                                  );
        
        // set navbar stuff
        $navbuttons = array(
                              'RADIUSCheck-tab' => t('title','RADIUSCheck'),
                              'RADIUSReply-tab' => t('title','RADIUSReply'),
                              'Attributes-tab' => t('title','Attributes'),
                           );

        print_tab_navbuttons($navbuttons);
    
    
        include('library/opendb.php');
        include_once('include/management/pages_common.php');
        
        $hashing_algorithm_notice = '<small style="font-size: 10px; color: black">'
                                  . 'Notice that for supported password-like attributes, you can just specify a plaintext value. '
                                  . 'The system will take care of correctly hashing it.'
                                  . '</small>';
                                  
    
        $fieldset1_descriptor = array(
                                    "title" => t('title','RADIUSCheck'),
                                 );
    
        open_form();
    
        
?>

        <div id="RADIUSCheck-tab" class="tabcontent" style="display: block">
    
<?php
        open_fieldset($fieldset1_descriptor);

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }


        $sql = sprintf("SELECT rc.attribute, rc.op, rc.value, dd.type, dd.recommendedTooltip, rc.id
                          FROM %s AS rc LEFT JOIN %s AS dd ON rc.attribute = dd.attribute AND dd.value IS NULL
                         WHERE rc.groupname='%s'", $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                                                   $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                                   $dbSocket->escapeSimple($profile_name));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";

    if ($res->numRows() == 0) {
        echo '<div style="text-align: center">'
           . t('messages','noCheckAttributesForGroup')
           . '</div>';
    } else {
        
        echo '<ul>';
        
        $editCounter = 0;
        $table = 'radgroupcheck';
        while ($row = $res->fetchRow()) {
            
            foreach ($row as $i => $v) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            $id = intval($row[5]);
            $id__attribute = sprintf('%d__%s', $id, $row[0]);
            $name = sprintf('editValues%s[]', $editCounter);
            $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";
            
            echo '<li>';
            printf('<a class="tablenovisit" href="mng-rad-profiles-del.php?profile_name=%s&id=%d&tablename=%s">',
                   urlencode($profile_name_enc), $id, $table);
            echo '<img src="images/icons/delete.png" border="0" alt="Remove"></a>';
            
            printf('<label for="attribute" class="attributes">%s</label>', $row[0]);

            printf('<input type="hidden" name="%s" value="%s">', $name, $id__attribute);            
            printf('<input type="%s" value="%s" name="%s">', $type, $row[2], $name);
            
            printf('<select name="%s" class="form">', $name);
            printf('<option value="%s">%s</option>', $row[1], $row[1]);
            drawOptions();
            echo '</select>';

            printf('<input type="hidden" name="%s" value="%s">', $name, $table);


            if (!empty($row[3]) || !empty($row[4])) {
                $divId = sprintf("%s-Tooltip-%d-%s", $row[0], $editCounter, $table);
                $onclick = sprintf("toggleShowDiv('%s')", $divId);
                printf('<img src="images/icons/comment.png" alt="Tip" border="0" onClick="%s">', $onclick);
                printf('<div id="%s" style="display:none;visibility:visible" class="ToolTip2">', $divId);
                
                if (!empty($row[3])) {
                    echo '<br>';
                    printf('<i><b>Type:</b> %s</i>', $row[3]);
                }
                
                if (!empty($row[4])) {
                    echo '<br>';
                    printf('<i><b>Tooltip Description:</b> %s</i>', $row[4]);
                }
                echo '</div>';
            }
            
            echo '</li>';
            
            // we increment the counter for the html elements of the edit attributes
            $editCounter++;
        }
        
        echo '</ul>';
        
        echo $hashing_algorithm_notice;
    }

?>
            </ul>

<?php
            print_form_component($submit_descriptor);
?>

        </fieldset>
    </div>

    <div class="tabcontent" id="RADIUSReply-tab">

<?php

    $fieldset2_descriptor = array(
                                    "title" => t('title','RADIUSReply'),
                                 );

    open_fieldset($fieldset2_descriptor);

    $sql = sprintf("SELECT rc.attribute, rc.op, rc.value, dd.type, dd.recommendedTooltip, rc.id
                      FROM %s AS rc LEFT JOIN %s AS dd ON rc.attribute = dd.attribute AND dd.value IS NULL
                     WHERE rc.groupname='%s'", $configValues['CONFIG_DB_TBL_RADGROUPREPLY'],
                                               $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                               $dbSocket->escapeSimple($profile_name));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    if ($res->numRows() == 0) {
        echo '<div style="text-align: center">'
           . t('messages','noReplyAttributesForGroup')
           . '</div>';
    } else {
        
        echo '<ul>';
        
        $editCounter = 0;
        $table = 'radgroupreply';
        while ($row = $res->fetchRow()) {
            
            foreach ($row as $i => $v) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }

            $id__attribute = sprintf('%s__%s', $row[5], $row[0]);
            $name = sprintf('editValues%s[]', $editCounter);
            $type = (preg_match("/-Password$/", $row[0])) ? $hiddenPassword : "text";
            
            echo '<li>';
            printf('<a class="tablenovisit" href="mng-rad-profiles-del.php?profile_name=%s&id=%d&tablename=%s">',
                   urlencode($profile_name_enc), $id, $table);
            echo '<img src="images/icons/delete.png" border="0" alt="Remove"></a>';
            
            printf('<label for="attribute" class="attributes">%s</label>', $row[0]);

            printf('<input type="hidden" name="%s" value="%s">', $name, $id__attribute);            
            printf('<input type="%s" value="%s" name="%s">', $type, $row[2], $name);
            
            printf('<select name="%s" class="form">', $name);
            printf('<option value="%s">%s</option>', $row[1], $row[1]);
            drawOptions();
            echo '</select>';

            printf('<input type="hidden" name="%s" value="%s">', $name, $table);


            if (!empty($row[3]) || !empty($row[4])) {
                $divId = sprintf("%s-Tooltip-%d-%s", $row[0], $editCounter, $table);
                $onclick = sprintf("toggleShowDiv('%s')", $divId);
                printf('<img src="images/icons/comment.png" alt="Tip" border="0" onClick="%s">', $onclick);
                printf('<div id="%s" style="display:none;visibility:visible" class="ToolTip2">', $divId);
                
                if (!empty($row[3])) {
                    echo '<br>';
                    printf('<i><b>Type:</b> %s</i>', $row[3]);
                }
                
                if (!empty($row[4])) {
                    echo '<br>';
                    printf('<i><b>Tooltip Description:</b> %s</i>', $row[4]);
                }
                echo '</div>';
            }
            
            echo '</li>';
            
            // we increment the counter for the html elements of the edit attributes
            $editCounter++;
        }
        
        echo '</ul>';
        
        echo $hashing_algorithm_notice;
    }
    
?>
            </ul>
            
<?php
            print_form_component($submit_descriptor);
?>
            
        </fieldset>
    </div>

<?php
        include('library/closedb.php');
?>
    
    <div class="tabcontent" id="Attributes-tab">
<?php
        include_once('include/management/attributes.php');
?>
    </div>     
</form>

<?php
    }
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
