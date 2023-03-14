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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");

    include("../common/includes/layout.php");

    // print HTML prologue
    $title = "Help";
    $help = "";

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

?>

    <p>There are several communication media available at your disposal:</p>
    <ul>
        <li><p><strong>daloRADIUS website:</strong> <a class="text-decoration-none" href="http://www.daloradius.com" target="_blank">daloRADIUS blog</a>.</p></li>
        <li><p><strong>daloRADIUS Project on GitHub:</strong> <a class="text-decoration-none" href="https://github.com/lirantal/daloradius" target="_blank">GitHub project</a>.<br>
        At GitHub, you can find the trackers for submitting tickets for support, bugs, or features for the next release.<br>
        The official daloRADIUS package is also available on GitHub.</p></li>
        <li><p><strong>daloRADIUS Project on SourceForge:</strong> <a class="text-decoration-none" href="http://sourceforge.net/projects/daloradius/" target="_blank">SourceForge project</a>.<br>
        You can use forums and the mailing list archive to review and search for issues.<br>
        However, the daloRADIUS packages here are old, so it is better to use the ones on GitHub.</p></li>
        <li><p><strong>daloRADIUS Mailing List:</strong> you can send an email to <a class="text-decoration-none" href="mailto:daloradius-users@lists.sourceforge.net">daloradius-users@lists.sourceforge.net</a>,<br>
        but registration to the mailing list is required first. You can register <a class="text-decoration-none" href="https://lists.sourceforge.net/lists/listinfo/daloradius-users" target="_blank">here</a>.</p></li>
        <li><strong>daloRADIUS IRC:</strong> You can find us at <a class="text-decoration-none" href="irc://irc.freenode.net/daloradius" target="_blank">#daloradius</a> on irc.freenode.net.</li>
    </ul>

<?php

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
