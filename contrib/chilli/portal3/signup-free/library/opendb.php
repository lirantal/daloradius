<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:	Liran Tal <liran@lirantal.com>
 *
 *********************************************************************************************************
 */

include __DIR__ . '/config_read.php';
require_once dirname(__DIR__, 3) . '/common/database.php';
try {
    // PDO-only; retain this wrapper's historical port policy and exported handle.
    $dbSocket = dalo_chilli_pdo_open(dalo_chilli_database_settings($configValues, false));
} catch (Throwable $error) {
    die('<b>Database connection error</b><br/>');
}
