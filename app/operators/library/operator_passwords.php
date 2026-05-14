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
 *********************************************************************************************************
 */

function operator_password_hash($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function operator_password_is_hash($storedPassword)
{
    $info = password_get_info($storedPassword);
    return intval($info['algo']) !== 0;
}

function operator_password_verify($password, $storedPassword)
{
    if (operator_password_is_hash($storedPassword)) {
        return password_verify($password, $storedPassword);
    }

    return hash_equals($storedPassword, $password);
}

function operator_password_needs_rehash($storedPassword)
{
    return !operator_password_is_hash($storedPassword) || password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}

?>
