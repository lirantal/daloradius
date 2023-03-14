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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/buttons.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$username_enc = (isset($username) && !empty($username))
              ? urlencode(htmlspecialchars($username, ENT_QUOTES, 'UTF-8'))
              : "";

$user_password_enc = (isset($user_password) && !empty($user_password))
                   ? urlencode(htmlspecialchars($user_password, ENT_QUOTES, 'UTF-8'))
                   : "";

if (!empty($username_enc)) {

    $button_descriptors1 = array();

    if (!empty($user_password_enc)) {

        $button_descriptors1[] = array( "onclick" => sprintf("self.location='config-maint-test-user.php?username=%s&password=%s'", $username_enc, $user_password_enc),
                                        "type" => "button", "value" => 'Test Connectivity', "name" => "test-connectivity-button" );
    }
    
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='config-maint-disconnect-user.php?username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Disconnect User', "name" => "disconnect-user-button" );                                
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='acct-maintenance-cleanup.php?username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Cleanup Stale Sessions', "name" => "cleanup-stale-sessions-button" );
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='acct-username.php?username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Accounting', "name" => "accounting-button" );
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='graphs-overall_logins.php?type=monthly&username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Graphs - Logins', "name" => "graphs-logins-button" );
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='graphs-overall_download.php?type=monthly&username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Graphs - Downloads', "name" => "graphs-downloads-button" );
    $button_descriptors1[] = array( "onclick" => sprintf("self.location='graphs-overall_upload.php?type=monthly&username=%s'", $username_enc),
                                    "type" => "button", "value" => 'Graphs - Uploads', "name" => "graphs-uploads-button" );
                                    
    //~ foreach ($button_descriptors1 as $button_descriptor) {
        //~ print_form_component($button_descriptor);
    //~ }
}

?>
