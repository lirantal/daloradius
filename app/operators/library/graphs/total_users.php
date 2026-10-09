<?php
include_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_LIBRARY'], 'checklogin.php'));
include_once implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_LANG'], 'main.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php'));
include implode(DIRECTORY_SEPARATOR, array($configValues['COMMON_INCLUDES'], 'chart.php'));
require_once __DIR__.'/../widget_reads_pdo.php';
dalo_widget_inputs(true);
try {
$widgetPDO=dalo_widget_open();
// Landing pages have no ACL entries; use the permission for listing users.
dalo_widget_authorize($widgetPDO,array('mng-list-all'));

$checkTable=dalo_widget_table($configValues,'CONFIG_DB_TBL_RADCHECK');
$infoTable=dalo_widget_table($configValues,'CONFIG_DB_TBL_DALOUSERINFO');
$countRows=dalo_widget_rows($widgetPDO,"SELECT COUNT(DISTINCT ui.username) FROM $checkTable AS rc,$infoTable AS ui WHERE ui.username=rc.username AND (rc.attribute='Auth-Type' OR rc.attribute LIKE '%-Password')");
$values=array((int)$countRows[0][0]);
unset($widgetPDO);
} catch (Throwable $exception) {dalo_widget_failure($exception,true);}

$dataset = array(
    'label' => strtolower(t('all', 'Users')),
    'data' => $values,
    'backgroundColor' => 'rgba(54, 162, 235, 0.55)',
    'borderColor' => 'rgb(54, 162, 235)',
    'borderWidth' => 1,
);
dalo_chart_response('bar', array(''), array($dataset), strtolower(t('all', 'TotalUsers')), '', strtolower(t('all', 'Users')));
