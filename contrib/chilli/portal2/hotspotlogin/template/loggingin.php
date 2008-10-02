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
 * daloRADIUS edition - fixed up variable definition through-out the code
 * as well as parted the code for the sake of modularity and ability to
 * to support templates and languages easier.
 * Copyright (C) Enginx and Liran Tal 2007, 2008
 *
 *********************************************************************************************************
 */

echo <<<END

<div id="wrap">

		<div class="header"><p>Hotspot<span>Login</span><sup>
			By <a href="http://templatefusion.org">TemplateFusion.org</a></sup></p>
		</div>
		
	<div id="navigation">
		<ul class="glossymenu">
		</ul>
	</div>
		
	<div id="body">
		<h1>Logging in...</h1>
		<p>		
			<br/>
			Please wait while Enginx's Hotspot system authenticates your <br/>
			user credentials against our servers. <br/><br/>
			This may take up to 30 seconds approximately. <br/>
                </p>

			<br/><br/>
			<br/><br/>
        </div>


                
        <div id="footer">Enginx&copy;2008 All Rights Reserved &bull; Enginx and daloRADIUS Hotspot Systems <br/>
                Design by <a href="http://templatefusion.org">TemplateFusion</a>
        </div>


</div>

</body>
</html>


END;


?>
