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

    // declaring variables
    $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
              ? trim(str_replace("%", "", $_POST['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $groupname = (array_key_exists('group', $_POST) && isset($_POST['group']))
               ? trim(str_replace("%", "", $_POST['group'])) : "";
    $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";
    
    $priority = (array_key_exists('priority', $_POST) && isset($_POST['priority']) &&
                 intval(trim($_POST['priority'])) >= 0) ? intval(trim($_POST['priority'])) : 1;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (empty($username) || empty($groupname)) {
            // username and groupname are required
            $failureMsg = "Username and groupname are required.";
            $logAction .= "Failed adding user-group mapping (username and/or groupname missing): ";
        } else {
            include('library/opendb.php');
        
            // check if this mapping is already in place
            $sql = sprintf("SELECT * FROM %s WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                           $dbSocket->escapeSimple($username),
                           $dbSocket->escapeSimple($groupname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            $exists = $res->numRows() > 0;
            
            if ($exists) {
                // this user mapping is already in place
                $failureMsg = "The chose user mapping ($username_enc - $groupname_enc) is already in place.";
                $logAction .= "Failed adding user-group mapping [$username_enc - $groupname_enc already in place]: ";
            } else {
                // insert usergroup details
                $sql = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ('%s', '%s', %s)",
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($groupname), $dbSocket->escapeSimple($priority));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = "Added new user-group mapping [$username_enc - $groupname_enc]";
                    $logAction .= "Added new user-group mapping [$username - $groupname]: ";
                } else {
                    $failureMsg = "DB Error when adding the chosen user mapping ($username_enc - $groupname_enc)";
                    $logAction .= "Failed adding user-group mapping [$username - $groupname, db error]: ";
                }
            }
            
            include('library/closedb.php');
        }
    }

    include_once("lang/main.php");
    
    include("library/layout.php");

    $title = t('Intro','mngradusergroupnew.php');
    $help = t('helpPage','mngradusergroupnew');
    
    print_html_prologue($title, $langCode);
    
    include("menu-mng-rad-usergroup.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    include_once('include/management/populate_selectbox.php');

    $input_descriptors1 = array();
    
    $options = get_users();
    array_unshift($options , '');
    
    $input_descriptors1[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "select",
                                    "selected_value" => ((isset($failureMsg)) ? $username : ""),
                                    "tooltipText" => t('Tooltip','usernameTooltip'),
                                    "options" => $options,
                                 );

    $options = get_groups();
    array_unshift($options , '');
    $input_descriptors1[] = array(
                                    "id" => "group",
                                    "name" => "group",
                                    "caption" => t('all','Groupname'),
                                    "type" => "select",
                                    "options" => $options,
                                    "selected_value" => ((isset($failureMsg)) ? $groupname : ""),
                                    "tooltipText" => t('Tooltip','groupTooltip')
                                 );
                                 
    $input_descriptors1[] = array(
                                    "id" => "priority",
                                    "name" => "priority",
                                    "caption" => t('all','Priority'),
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => ((isset($failureMsg)) ? $priority : "1"),
                                 );

    $input_descriptors1[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

?>

            <form name="newusergroup" method="POST">
                <fieldset>
                    <h302><?= t('title','GroupInfo') ?></h302>

                    <ul>
                    
<?php
                        foreach ($input_descriptors1 as $input_descriptor) {
                            print_form_component($input_descriptor);
                        }
?>

                    </ul>
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
