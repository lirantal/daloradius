<?php
include_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_LIBRARY'], 'checklogin.php'));
include_once implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_LANG'], 'main.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['COMMON_INCLUDES'], 'chart.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['COMMON_INCLUDES'], 'db_open.php'));
$values = array(count_users($dbSocket)); include implode(DIRECTORY_SEPARATOR, array($configValues['COMMON_INCLUDES'], 'db_close.php'));
dalo_chart_response('bar', array(''), array(array_merge(array('label' => strtolower(t('all', 'Users')), 'data' => $values), array('backgroundColor' => 'rgba(54, 162, 235, 0.55)', 'borderColor' => 'rgb(54, 162, 235)', 'borderWidth' => 1))), strtolower(t('all', 'TotalUsers')), '', strtolower(t('all', 'Users')));
