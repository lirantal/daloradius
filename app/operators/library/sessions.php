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
 * Description:    a bit of custom session management to prevent some XSS stuff
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/sessions.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// this function ("installs" and) returns a csrf token
function dalo_csrf_token() {
    
    $random = (function_exists('random_bytes'))
            ? random_bytes(32)
            : ((function_exists('mcrypt_create_iv')) ? mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)
                                                     : openssl_random_pseudo_bytes(32));
    $_SESSION['csrf_token'] = bin2hex($random);
    
    return $_SESSION['csrf_token'];
}

// this function can be used for verifying if the csrf token is valid
function dalo_check_csrf_token($token) {
    if (empty($token)) {
        return false;
    }

    // this should provide backward compatibility with PHP < 5.6.0
    $result = (function_exists('hash_equals'))
            ? hash_equals($_SESSION['csrf_token'], $token)
            : $_SESSION['csrf_token'] === $token;

    return $result;
}

// daloRADIUS session start function support timestamp management
function dalo_session_start() {
    $session_max_lifetime = 3600;
    
    // set's the session max lifetime
    ini_set('session.gc_maxlifetime', $session_max_lifetime);
    ini_set('session.use_strict_mode', 1);
    
    // Change PHPSESSID for better security, remove this if set in php.ini
    session_name('daloradius_operator_sid');

    // Secure session_set_cookie_params
    session_set_cookie_params(0, '/', null, null, true);
    
    session_start();
    
    $now = time();
    
    if (array_key_exists('time', $_SESSION) && isset($_SESSION['time'])) {
        // if too old, destroy and restart
        if ($_SESSION['time'] < $now-$session_max_lifetime) {
            dalo_session_destroy();
            session_start();
            dalo_session_regenerate_id();
        }
    } else {
        $_SESSION['time'] = $now;
    }
    
}

// daloRADIUS session regenerate id function
// should be used at least on login and logout
function dalo_session_regenerate_id() {
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $id = 'daloRADIUS-operator';
    $session_id = (function_exists('session_create_id'))
        ? session_create_id($id) : uniqid($id);
    
    $_SESSION['time'] = time();
    
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
