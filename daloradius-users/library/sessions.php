<?php

/*
 *******************************************************************************
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
 *******************************************************************************
 * Description:
 *      a bit of custom session management to prevent some XSS stuff
 *
 * Author:
 *      Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *******************************************************************************
 */

// daloRADIUS session start function support timestamp management
function dalo_session_start() {
    ini_set('session.use_strict_mode', 0);
    session_start();
    
    if (array_key_exists('deleted_time', $_SESSION)) {
        $t = $_SESSION['deleted_time'];
    
        // if too old, destroy and restart
        if (!empty($t) && $t < time()-900) {
            session_destroy();
            session_start();
        }
    }
}

// daloRADIUS session regenerate id function
// should be used at least on login and logout
function dalo_session_regenerate_id() {
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $session_id = (function_exists('session_create_id'))
        ? session_create_id('daloRADIUS-') : uniqid('daloRADIUS-');
    
    $_SESSION['deleted_time'] = time();
    session_commit();
    
    ini_set('session.use_strict_mode', 0);
    session_id($session_id);
    
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// daloRADIUS session destroy and clean all session variables
function dalo_session_destroy() {
    // unset all of the session variables.
    $_SESSION = array();

    // completely destory the session and all it's variables
    session_destroy();
}
