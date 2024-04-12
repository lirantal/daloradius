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
 * Description:    This script retrieves and loads configuration settings stored in the
 *                 daloradius.conf.php file, populating the $configValues associative array
 *                 with the retrieved data.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/config_read.php') !== false) {
    http_response_code(404);
    exit;
}

$_configFile = __DIR__ . '/daloradius.conf.php';
clearstatcache(true, $_configFile);
unset($configValues);
include($_configFile);

// strip slashes (if any)
foreach ($configValues as $_configOption => $_configElem) {
    if (!is_array($_configElem)) {
        $configValues[$_configOption] = stripslashes($_configElem);
    }
}

// inject useful paths in the $configValues
$configValues['COMMON_INCLUDES'] = __DIR__;
$configValues['COMMON_ROOT'] = realpath(implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], '..' ]));
$configValues['COMMON_LIBRARY'] = implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_ROOT'], 'library' ]);

$configValues['APP_ROOT'] = realpath(implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_ROOT'], '..' ]));

$configValues['OPERATORS_ROOT'] = implode(DIRECTORY_SEPARATOR, [ $configValues['APP_ROOT'], 'operators' ]);
$configValues['OPERATORS_LANG'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_ROOT'], 'lang' ]);
$configValues['OPERATORS_INCLUDE'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_ROOT'], 'include' ]);
$configValues['OPERATORS_INCLUDE_MANAGEMENT'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE'], 'management' ]);
$configValues['OPERATORS_INCLUDE_CONFIG'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE'], 'config' ]);
$configValues['OPERATORS_LIBRARY'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_ROOT'], 'library' ]);
$configValues['OPERATORS_LIBRARY_EXTENSIONS'] = implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'extensions' ]);

$configValues['USERS_ROOT'] = implode(DIRECTORY_SEPARATOR, [ $configValues['APP_ROOT'], 'users' ]);