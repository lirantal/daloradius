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

    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = "Help";
    $help = "";
    
    print_html_prologue($title, $langCode);

    include("menu-help.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

?>

            <p>
                One of several communication mediums available at your disposal:<br/><br/>

                <b>daloRADIUS website</b>: <a href="http://www.daloradius.com">daloRADIUS blog</a><br/><br/>
                <b>daloRADIUS Project at GitHub</b>: <a href="https://github.com/lirantal/daloradius">GitHub project</a><br/>
                At GitHub you may find the trackers for submitting tickets for support, bugs or features for next releases.<br/>
                The official daloRADIUS package is available at
                GitHub as well.<br/><br/>
                <b>daloRADIUS Project at SourceForge</b>: <a href="http://sourceforge.net/projects/daloradius/">SourceForge project</a><br/>
                Forums and the mailing list archive to review and search for issues.<br/>
                The daloRADIUS packages here are old, use the GitHub ones instead.<br/><br/>
                <b>daloRADIUS Mailing List</b>: Email to daloradius-users@lists.sourceforge.net, though registration to the mailing list<br/>
                is required first <a href="https://lists.sourceforge.net/lists/listinfo/daloradius-users">here</a><br/><br/>

                <b>daloRADIUS IRC</b>: you can find us at #daloradius on irc.freenode.net
            </p>

        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>


<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

?>

