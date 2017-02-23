
                                <ul id="subnav">
                                                <li><a href="acct-main.php"><em>G</em>eneral</a></li>
                                                <li><a href="acct-plans.php"><em>P</em>lans</a></li>
                                                <li><a href="acct-custom.php"><em>C</em>ustom</a></li>
                                                <li><a href="acct-hotspot.php"><em>H</em>otspot</a></li>
                                                <li><a href="acct-maintenance.php"><em>M</em>aintenance</a></li>

<div id="logindiv" style="text-align: right;">

                                                <li>Location: <b><?php echo htmlspecialchars($_SESSION['location_name'], ENT_QUOTES) ?></b></li><br/>
                                                <li>Welcome, <?php echo htmlspecialchars($operator, ENT_QUOTES); ?></li>

                                                <li><a href="logout.php">[logout]</a></li>

                                </ul>
								
                </div>

