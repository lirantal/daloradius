
                                <ul id="subnav">

                                                <li><a href="bill-pos.php"><em>P</em>OS</a></li>
                                                <li><a href="bill-plans.php"><em>P</em>lans</a></li>
                                                <li><a href="bill-rates.php"><em>R</em>ates</a></li>
                                                <li><a href="bill-merchant.php"><em>M</em>erchant-Transactions</a></li>
                                                <li><a href="bill-history.php"><em>B</em>illing-History</a></li>
                                                <li><a href="bill-invoice.php"><em>I</em>nvoices</a></li>
                                                <li><a href="bill-payments.php">Pa<em>y</em>ments</a></li>
						<div id="logindiv" style="text-align: right;">

                                                <li>Location: <b><?php echo htmlspecialchars($_SESSION['location_name'], ENT_QUOTES) ?></b></li><br/>
                                                <li>Welcome, <?php echo htmlspecialchars($operator, ENT_QUOTES); ?></li>

                                                <li><a href="logout.php">[logout]</a></li>

                                </ul>
								
                </div>

