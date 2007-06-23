
<body>

<div id="wrapper">
<div id="innerwrapper">

		<div id="header">
		
				<form action="">
				<input value="Search" />
				</form>
				
				<h1><a href="index.php">daloRADIUS</a></h1>
				
				<h2>
				
						Radius Reporting, Billing and Management by <a href="http://www.enginx.com">Enginx</a>
				
				</h2>
				
				<ul id="nav">
				
						<li><a href="index.php"><em>H</em>ome</a></li>
						
						<li><a href="mng-main.php" class="active"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php"><em>R</em>eports</a></li>
						
						<li><a href="acct-main.php"><em>A</em>ccounting</a></li>

						<li><a href="bill-main.php"><em>B</em>illing</a></li>
						<li><a href="gis-main.php"><em>GIS</em></a></li>
						<li><a href="graph-main.php"><em>G</em>raphs</a></li>

						<li><a href="help-main.php"><em>H</em>elp</a></li>
				
				</ul>
				<ul id="subnav">
						<li><a href="mng-rad-nas.php" class="active"><em>N</em>as</a></li>
						<li><a href="mng-rad-usergroup.php"><em>U</em>ser-group Mapping</a></li>
						<li><a href="mng-rad-groupreply.php"><em>G</em>roup-reply</a></li>
						<li><a href="mng-rad-groupcheck.php"><em>G</em>roup-check</a></li>
						<li><a href="mng-rad-postauth.php"><em>P</em>ostauth</a></li>
						<li><a href="mng-rad-operators.php"><em>O</em>perators</a></li>

						<li>Welcome, <?php echo $operator; ?></li>

						<li><a href="logout.php">[logout]</a></li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
	
                                <h2>Management</h2>

                                <h3>User-Group Management</h3>
                                <ul class="subnav">

                                                <li><a href="mng-rad-usergroup-list.php"><b>&raquo;</b>List User-Group Mappings</a></li>
                                                <li><a href="javascript:document.mngradusrgrplist.submit();""><b>&raquo;</b>List User's Group Mapping<a>
                                                        <form name="mngradusrgrplist" action="mng-rad-usergroup-list-user.php" method="get" class="sidebar">
                                                        <input name="username" type="text">
                                                        </form></li>

                                                <li><a href="mng-rad-usergroup-add-manual.php"><b>&raquo;</b>New User-Group Mapping</a></li>
                                                <li><a href="mng-rad-usergroup-add-multi.php"><b>&raquo;</b>New Multi User-Group Mapping</a></li>
                                                <li><a href="javascript:document.mngradusrgrpedit.submit();""><b>&raquo;</b>Edit User-Group Mapping<a>
                                                        <form name="mngradusrgrpedit" action="mng-rad-usergroup-edit.php" method="get" class="sidebar">
                                                        <input name="username" type="text" value="[username]">
                                                        <input name="group" type="text" value="[groupname]">
                                                        </form></li>


                                                <li><a href="mng-rad-usergroup-del.php"><b>&raquo;</b>Remove User-Group Mapping</a></li>
                                </ul>


	
		</div>

		