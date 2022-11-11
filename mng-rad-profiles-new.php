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
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    // we import validation facilities
    include_once("library/validation.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        $profile = (array_key_exists('profile', $_POST) && isset($_POST['profile']))
                 ? trim(str_replace("%", "", $_POST['profile'])) : "";
        $profile_enc = (!empty($profile)) ? htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') : "";
    
        if (empty($profile)) {
            // profile required
            $failureMsg = "The specified profile name is empty or invalid";
            $logAction .= "Failed creating profile [empty or invalid profile name] on page: ";
        } else {

            include('library/opendb.php');
            //~ include_once('include/management/populate_selectbox.php');

            //~ $profiles = get_groups();
            $profiles = array();
            if (in_array($profile, $profiles)) {
                // invalid profile name
                $failureMsg = "This profile name [<strong>$profile_enc</strong>] is already in use";
                $logAction .= "Failed creating profile [$profile, name already in use] on page: ";
            } else {
    
                $skipList = array( "profile", "submit", "csrf_token" );

                $count = 0;

                foreach ($_POST as $element => $field) {

                    // we skip several attributes (contained in the $skipList array)
                    // which we do not wish to process (ie: do any sql related stuff in the db)
                    if (in_array($element, $skipList)) {
                        continue;
                    }
                    
                    // we need $field to be exactly an array with 4 fields:
                    // $attribute, $value, $op, $table
                    if (!is_array($field) || count($field) != 4) {
                        continue;
                    }
                    
                    // we trim all array values
                    foreach ($field as $i => $v) {
                        $field[$i] = trim($v);
                    }
                    
                    list($attribute, $value, $op, $table) = $field;
                    
                    // value and attribute are required
                    if (empty($value) || empty($attribute)) {
                            continue;
                    }

                    // we only accept valid ops
                    if (!in_array($op, $valid_ops)) {
                        continue;
                    }

                    // $table value can be only '(rad)reply' or '(rad)check'
                    $table = strtolower($table);
                    if (in_array($table, array('reply', 'radreply', 'radgroupreply'))) {
                        $table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
                    } else if (in_array($table, array('check', 'radcheck', 'radgroupcheck'))) {
                        $table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
                    } else {
                        continue;
                    }

                    // if all checks are passed, we insert the new attribute
                    $sql = sprintf("INSERT INTO %s (id, groupname, attribute, op, value) VALUES (0, '%s', '%s', '%s', '%s')",
                                   $table, $dbSocket->escapeSimple($profile), $dbSocket->escapeSimple($attribute),
                                   $dbSocket->escapeSimple($op), $dbSocket->escapeSimple($value));
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= "$sql;\n";

                    if (!DB::isError($res)) {
                        $count += 1;
                    }

                } // foreach
                
                
                if ($count > 0) {
                    $successMsg = "Added new profile: <b> $profile_enc </b>";
                    $logAction .= "Successfully added a new profile [$profile] on page: ";
                } else {
                    $failureMsg = "Failed creating profile [$profile_enc], invalid or empty attributes list";
                    $logAction .= "Failed creating profile [$profile_enc, invalid or empty attributes list] on page: ";
                }

            } // profile non-existent
            
            include('library/closedb.php');
            
        } // profile name not empty    
    }
    
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
        "library/javascript/productive_funcs.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngradprofilesnew.php');
    $help = t('helpPage','mngradprofilesnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-mng-rad-profiles.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
?>
                
<form name="newusergroup" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>

            <h302> <?php echo t('title','ProfileInfo') ?> </h302>
            <br/>

            <label for='profile' class='form'>Profile Name</label>
            <input name='profile' type='text' id='profile' value='' tabindex=100 />
            <br />

            <br/><br/>
            <hr><br/>

            <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

    </fieldset>


    <br/>


    <?php
        include_once('include/management/attributes.php');
    ?>
        
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
