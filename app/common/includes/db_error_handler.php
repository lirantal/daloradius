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
 * Description:    global error handling for all PEAR packages, it isn't too wise to do
 *                 that as I've no idea what other php applications are running on this machine
 *                 even though this is really just about handling the error but still.
 *                 So instead we're using the object's error handling method (see library/opendb.php)
 *                 PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/db_error_handler.php') !== false) {
    http_response_code(404);
    exit;
}

function errorHandler($err) {
    echo '<div class="failure"><strong>Database error</strong><br>'
       . "Error message: <strong>" . $err->getMessage() . "</strong><br>"
       . "Debug info: <br>" . $err->getDebugInfo()
       . "</div>";
}

?>
