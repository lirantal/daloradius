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
 * Description:
 *		displays a welcome page for the main index.php file
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *          Miguel García <miguelvisgarcia@gmail.com>
 *
 *********************************************************************************************************
 */

echo "
	<center>

		<h2> daloRADIUS Web Management Server </h2>
		<h3> ".t('all','daloRADIUSVersion')." / ".$configValues['DALORADIUS_DATE']." </h3>
		<h4> <a href=\"mailto:liran.tal@gmail.com\"> Liran Tal </a> </h4>
		<br/><br/><br/>
		<img src='images/daloradius_logo.jpg' border=0 />
	</center>
";

?>
