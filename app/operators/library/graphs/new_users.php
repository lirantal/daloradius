<?php
include('../checklogin.php'); include('../../lang/main.php'); include('../validation.php'); include('../../../common/includes/chart.php');
$startdate = isset($_GET['startdate']) && preg_match(DATE_REGEX, trim($_GET['startdate']), $m) && checkdate($m[2], $m[3], $m[1]) ? trim($_GET['startdate']) : '';
$enddate = isset($_GET['enddate']) && preg_match(DATE_REGEX, trim($_GET['enddate']), $m) && checkdate($m[2], $m[3], $m[1]) ? trim($_GET['enddate']) : '';
include('../../../common/includes/db_open.php'); $where = array(); if ($startdate !== '') $where[] = "CreationDate >= '" . $dbSocket->escapeSimple($startdate) . "'"; if ($enddate !== '') $where[] = "CreationDate <= '" . $dbSocket->escapeSimple($enddate) . "'";
$sql = sprintf("SELECT COUNT(*), CONCAT(YEAR(CreationDate), ' ', LEFT(MONTHNAME(CreationDate), 3)) FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO']) . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' GROUP BY YEAR(CreationDate), MONTH(CreationDate) ORDER BY YEAR(CreationDate), MONTH(CreationDate)';
$res = $dbSocket->query($sql); $labels = array(); $values = array(); while ($row = $res->fetchRow()) { $values[] = intval($row[0]); $labels[] = strval($row[1]); } include('../../../common/includes/db_close.php');
dalo_chart_response('bar', $labels, array(array_merge(array('label' => 'users', 'data' => $values), array('backgroundColor' => 'rgba(54, 162, 235, 0.55)', 'borderColor' => 'rgb(54, 162, 235)', 'borderWidth' => 1))), 'new users amount', 'per-month distribution', 'users');
