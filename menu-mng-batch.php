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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-batch.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");
?>
<script src="library/javascript/rounded-corners.js"></script>
<script src="library/javascript/form-field-tooltip.js"></script>
<link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
    $m_active = "Management";
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");

$subnav_elements = array(
                            "mng-batch-list.php" => array(t('button','ListBatches'), "images/icons/userList.gif"),
                            "mng-batch-add.php" => array(t('button','BatchAddUsers'), "images/icons/userNew.gif"),
                            "mng-batch-del.php" => array(t('button','RemoveBatch'), "images/icons/userRemove.gif")
                        );
?>

            <div id="sidebar">
                <h2>Management</h2>
	
                <h3>Batch Management</h3>
                <ul class="subnav">
<?php
foreach ($subnav_elements as $href => $items) {
    list($label, $src) = $items;
    printf('<li><a href="%s" title="%s"><b>&raquo;</b><img style="border: 0; margin-right: 5px" src="%s">%s</a></li>',
           $href, strip_tags($label), $src, $label);
}
?>

                </ul><!-- .subnav -->
                
                <br><br>
            </div>

<?php
    if ($autoComplete) {
?>
<script>
    /** Making dictAttributesCustom interactive **/
    var autoComEdit = new DHTMLSuite.autoComplete();
</script>
<?php
	} 
?>
