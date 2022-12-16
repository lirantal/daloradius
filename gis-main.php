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
        
    include_once('library/config_read.php');
    $log = "visited page: ";
    
    if (array_key_exists('submit', $_POST) && isset($_POST['submit'])) {
        $myfile = "library/googlemaps.php";
        
        $default_failureMsg = sprintf("Error: could not open the file for reading or writing: <strong>%s</strong>", $myfile)
                            . "<br>Check file permissions. The file should be readable and writable by the webserver's user/group";
        
        
        if (array_key_exists('code', $_POST) && isset($_POST['code']) &&
            preg_match('/[a-zA-Z0-9_-]+/', $_POST['code']) !== false) {
        
            
            
            if (is_readable($myfile) && is_writable($myfile)) {
                $old_contents = file_get_contents($myfile);
                $replacement = sprintf('<script src="//maps.google.com/maps?file=api&v=3&key=%s"></script>', $_POST['code']);
                $new_contents = preg_replace('/<script.*<\/script>/si', $replacement, $old_contents);
                
                if ($new_contents !== $old_contents) {
                    if (file_put_contents($myfile, $new_contents) !== false) {
                        $successMsg = "Successfully updated GoogleMaps API Registration code";
                    } else {
                        $failureMsg = $default_failureMsg;
                    }
                }

            } else {
                $failureMsg = $default_failureMsg;
            }
        }
    }
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','gismain.php');
    $help = t('helpPage','gismain');

    print_html_prologue($title, $langCode);
    
    include ("menu-gis.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
