                <div id="header">
				
								<span id="login_data">
									Welcome, <b><?php echo $operator; ?></b>. <a href="logout.php" title="Logout">&#x274E;</a>
									<br>
									Location: <b><?php echo $_SESSION['location_name'] ?></b>.
								</span>
								
								<span id="sep">
									&nbsp;
								</span>

                                <form action="mng-search.php">
									<input name="username"
										value=""
										placeholder="<?php echo t('button','SearchUsers') ?>"
										title="<?php echo t('Tooltip','Username') . '. ' . t('Tooltip','UsernameWildcard'); ?>"
									/>
                                </form>
																
								<span id="sep">
									&nbsp;
								</span>

                                <h1><a href="index.php"> <img src="images/daloradius_small.png" border=0/></a></h1>

                                <h2>
									<?php echo t('all','copyright1'); ?>
				                </h2>

                                <ul id="nav">
				<a name='top'></a>

				<li><a href="index.php" <?php echo ($m_active == "Home") ? "class=\"active\"" : ""?>><?php echo t('menu','Home'); ?></a></li>
				<li><a href="mng-main.php" <?php echo ($m_active == "Management") ? "class=\"active\"" : "" ?>><?php echo t('menu','Managment'); ?></a></li>
				<li><a href="rep-main.php" <?php echo ($m_active == "Reports") ? "class=\"active\"" : "" ?>><?php echo t('menu','Reports'); ?></a></li>
				<li><a href="acct-main.php" <?php echo ($m_active == "Accounting") ? "class=\"active\"" : "" ?>><?php echo t('menu','Accounting'); ?></a></li>
				<li><a href="bill-main.php" <?php echo ($m_active == "Billing") ? "class=\"active\"" : "" ?>><?php echo t('menu','Billing'); ?></a></li>
				<li><a href="gis-main.php" <?php echo ($m_active == "Gis") ? "class=\"active\"" : ""?>><?php echo t('menu','Gis'); ?></a></li>
				<li><a href="graph-main.php" <?php echo ($m_active == "Graphs") ? "class=\"active\"" : ""?>><?php echo t('menu','Graphs'); ?></li>
				<li><a href="config-main.php" <?php echo ($m_active == "Config") ? "class=\"active\"" : ""?>><?php echo t('menu','Config'); ?></li>
				<li><a href="help-main.php" <?php echo ($m_active == "Help") ? "class=\"active\"" : ""?>><?php echo t('menu','Help'); ?></a></li>

                                </ul>

