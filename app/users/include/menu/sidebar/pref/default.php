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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar/pref/default.php') !== false) {
    header("Location: ../../../../index.php");
    exit;
}


include_once('../common/includes/config_read.php');

// define descriptors
$descriptors1 = array();
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ChangePortalPassword'), 'href' => 'pref-portal-password-edit.php', 'icon' => 'house-lock-fill', );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','ChangeAuthPassword'), 'href' => 'pref-auth-password-edit.php', 'icon' => 'person-fill-lock', );
$descriptors1[] = array( 'type' => 'link', 'label' => t('button','EditUserInfo'), 'href' => 'pref-userinfo-edit.php', 'icon' => 'person-fill-gear', );

$sections = array();
$sections[] = array( 'title' => 'Settings', 'descriptors' => $descriptors1 );

// add sections to menu
$menu = array(
                'title' => 'User Preferences',
                'sections' => $sections,
             );
