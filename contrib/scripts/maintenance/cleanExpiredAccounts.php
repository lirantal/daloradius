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
 * Package:		   Clean Expired Accounts
 *
 * Description:    This script should be placed in cron to run scheduled every X minutes and clear
 *				   expired accounts from the database. It cleans Accumulative and Time-To-Finish accounts
 *				   and requires that all of these accounts are associated with the corrosponding billing plan
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// UNIT-042: this unsafe legacy cleanup is deliberately retired, not PDO-migrated.
// Refuse before configuration, argument/request processing or database access.
$message = 'Legacy expired-account cleanup is retired. No accounts were deleted.';

if (PHP_SAPI === 'cli') {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

http_response_code(410);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo $message;
