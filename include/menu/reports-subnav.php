
                                <ul id="subnav">

                                                <li><a href="rep-main.php"><em>G</em>eneral</a></li>
                                                <li><a href="rep-logs.php"><em>L</em>ogs</a></li>
                                                <li><a href="rep-status.php"><em>S</em>tatus</a></li>
												<li><a href="rep-batch.php"><em>B</em>atch Users</a></li>
												<li><a href="rep-hb.php"><em>D</em>ashboard</a></li>

<div id="logindiv" style="text-align: right;">
                                                <li>Location: <b><?php echo htmlspecialchars($_SESSION['location_name'], ENT_QUOTES) ?></b></li><br/>
                                                <li>Welcome, <?php echo htmlspecialchars($operator, ENT_QUOTES); ?></li>

                                                <li><a href="logout.php">[logout]</a></li>

                                </ul>
								
                </div>

