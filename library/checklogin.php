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
 * 		verifies a user session, valid or invalid based on the random
 *      session_id generated on dologin.php
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

include('sessions.php');
dalo_session_start();

if (!array_key_exists('daloradius_logged_in', $_SESSION)
    || $_SESSION['daloradius_logged_in'] !== true) {
    $_SESSION['daloradius_logged_in'] = false;
    header('Location: login.php');
    exit;
}

?>
