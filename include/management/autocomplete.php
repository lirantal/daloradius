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
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


    include_once("library/config_read.php");

    /**
     * Checking if the AJAX functionality should be loaded
     */
    if( (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE'])) &&
                (strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") )
    {
        $autoComplete = true; # Set boolean for throughout the page
        echo "
		<script type=\"text/javascript\" src=\"library/javascript/ajax.js\"></script>
		<script type=\"text/javascript\" src=\"library/javascript/dhtmlSuite-common.js\"></script>
		<script type=\"text/javascript\" src=\"library/javascript/auto-complete.js\"></script>
		<script>
			var DHTML_SUITE_THEME_FOLDER = './';
			var DHTML_SUITE_JS_FOLDER = 'library/javascript/';
			var DHTML_SUITE_THEME = '.';
		</script>
        ";
    }


?>
