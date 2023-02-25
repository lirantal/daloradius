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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("../../library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    $batch_name = (array_key_exists('batch_name', $_GET) && !empty(str_replace("%", "", trim($_GET['batch_name']))))
                ? str_replace("%", "", trim($_GET['batch_name'])) : "";

    $destination = (array_key_exists('destination', $_GET) && !empty(trim($_GET['destination'])) &&
                    in_array(strtolower(trim($_GET['destination'])), array( "download", "email" )))
                 ? strtolower($_GET['destination']) : "download";

    if (empty($batch_name)) {
        die("you should provide a valid batch name");
    }
    
    include_once("../../notifications/processNotificationBatchDetails.php");
    include_once("../../../common/includes/config_read.php");
    
    
    function getBatchDetails($batch_name = NULL) {
        global $configValues;

        if ($batch_name == NULL || empty(trim($batch_name))) {
            return false;
        }

        include('../../../common/includes/db_open.php');
        include_once("../../lang/main.php");

        $tableTags = 'style="width: 580px"';
        $tableTrTags = 'style="background-color: #ECE5B6"';

        $customerInfo = array();

        $ths = array(
                        t('all','BatchName'),
                        t('all','HotSpot'),
                        t('all','BatchStatus'),
                        t('all','TotalUsers'),
                        t('all','ActiveUsers'),
                        t('all','PlanName'),
                        t('all','PlanCost'),
                        t('all','BatchCost'),
                        t('all','CreationDate'),
                        t('all','CreationBy'),
                    );

        // start filling in batch details
        $batch_details = "<table $tableTags><tr $tableTrTags>";
        
        foreach ($ths as $th) {
            $batch_details .= sprintf("<th>%s</th>", $th);
        }
        
        $batch_details .= "</tr>";

        $sql = sprintf("SELECT dbh.id AS batch_id, dbh.batch_name, dbh.batch_description, dbh.batch_status,
                               COUNT(DISTINCT(ubi.id)) AS total_users, COUNT(DISTINCT(ra.username)) AS active_users,
                               ubi.planname, dbp.plancost, dbp.plancurrency, dhs.name AS hotspot_name,
                               dbh.creationdate, dbh.creationby, dbh.updatedate, dbh.updateby
                          FROM %s AS dbh LEFT JOIN %s AS ubi ON dbh.id=ubi.batch_id
                                        LEFT JOIN %s AS dbp ON dbp.planname=ubi.planname
                                        LEFT JOIN %s AS dhs ON dbh.hotspot_id=dhs.id
                                        LEFT JOIN %s AS ra ON ra.username=ubi.username
                         WHERE dbh.batch_name='%s'
                         GROUP BY dbh.batch_name", $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                   $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                                                   $configValues['CONFIG_DB_TBL_RADACCT'],
                                                   $dbSocket->escapeSimple($batch_name));
        $res = $dbSocket->query($sql);

        
        $active_users_per = 0;
        $total_users = 0;
        $active_users = 0;
        $batch_cost = 0;

        $hotspot_name = "";
        $batch_id = "";
        $planname = "";

        while($row = $res->fetchRow()) {

            foreach ($row as $i => $value) {
                $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            list(
                    $batch_id, $batch_name, $batch_description, $batch_status, $total_users, $active_users, $planname,
                    $plancost, $plancurrency, $hotspot_name, $creationdate, $creationby, $updatedate, $updateby
                ) = $row;

            
            $batch_cost = (intval($active_users) * intval($plancost));

            $tds = array(
                            $batch_name,
                            $hotspot_name,
                            $batch_status,
                            $total_users,
                            $active_users,
                            $planname,
                            $plancost,
                            $batch_cost,
                            $creationdate,
                            $creationby
                        );
                        
            $batch_details .= "<tr>";
            foreach ($tds as $td) {
                $batch_details .= sprintf("<td>%s</td>", $td);
            }
            $batch_details .= "</tr>";

        }

        $batch_details .= "</table>";

        $customerInfo['batch_details'] = $batch_details;


        // filling in plan info
        if (!empty($planname)) {
        
            $sql = sprintf("SELECT planId, planName, planRecurringPeriod, planCost, planSetupCost, planTax, planCurrency
                              FROM %s WHERE planName='%s'", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'],
                                                            $dbSocket->escapeSimple($planname));
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
            
            echo $sql;
            echo $row;
            exit;

            $service_plan_info = "<table $tableTags>";

            foreach ($row as $rowName => $rowValue) {
                $rowName = htmlspecialchars($rowName, ENT_QUOTES, 'UTF-8');
                $rowValue = htmlspecialchars($rowValue, ENT_QUOTES, 'UTF-8');

                $service_plan_info .= "<tr $tableTrTags>"
                                    . sprintf("<th>%s</th>", $rowName)
                                    . sprintf("<td>%s</td>", $rowValue)
                                    . "</tr>";
            }

            $service_plan_info .= "</table>";
            $customerInfo['service_plan_info'] = $service_plan_info;
        }

        // filling in business info
        if (!empty($hotspot_name)) {
            $sql = sprintf("SELECT id, name, owner, address, companyphone, companyemail, companywebsite
                              FROM %s WHERE name='%s'", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                                                        $dbSocket->escapeSimple($hotspot_name));
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

            $customerInfo['business_name'] = $row['name'];
            $customerInfo['business_owner_name'] = $row['owner'];
            $customerInfo['business_address'] = $row['address'];
            $customerInfo['business_phone'] = $row['companyphone'];
            $customerInfo['business_email'] = $row['companyemail'];
            $customerInfo['business_web'] = $row['companywebsite'];
        }

        // active users details
        $sql = sprintf("SELECT ubi.id, ubi.username, ra.acctstarttime, dbh.batch_name
                          FROM %s AS ubi, %s AS ra, %s AS dbh
                         WHERE ubi.batch_id=dbh.id
                           AND ubi.batch_id='%s'
                           AND ubi.username=ra.username
                         GROUP BY ubi.username
                         ORDER BY id, ra.radacctid ASC", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                                         $configValues['CONFIG_DB_TBL_RADACCT'],
                                                         $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                                                         $dbSocket->escapeSimple($batch_id));
        $res = $dbSocket->query($sql);

        $ths = array(
                        t('all','BatchName'),
                        t('all','Username'),
                        t('all','StartTime'),
                    );

        $batch_active_users = "<table $tableTags><tr $tableTrTags>";
        foreach ($ths as $th) {
            $batch_active_users .= sprintf("<th>%s</th>", $th);
        }
        $batch_active_users .= "</tr>";
        
        $active_users_per = 0;
        $total_users = 0;
        $active_users = 0;
        $batch_cost = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            foreach ($row as $i => $value) {
                $row[$i] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            list($id, $username, $acctstarttime, $batch_name) = $row;

            $tds = array( $username, $acctstarttime, $batch_name );

            $batch_active_users .= "<tr>";
            foreach ($tds as $td) {
                $batch_active_users .= sprintf("<td>%s</td>", $td);
            }
            $batch_active_users .= "</tr>";
        }
        
        $batch_active_users .= "</table>";
        $customerInfo['batch_active_users'] = $batch_active_users;


        include('../../../common/includes/db_close.php');

        return $customerInfo;
    }
    
    $customerInfo = getBatchDetails($batch_name);
    
    if ($customerInfo === false) {
        die("error when loading batch details");
    }

    $smtpInfo['host'] = $configValues['CONFIG_MAIL_SMTPADDR'];
    $smtpInfo['port'] = $configValues['CONFIG_MAIL_SMTPPORT'];
    $smtpInfo['auth'] = $configValues['CONFIG_MAIL_SMTPAUTH'];
    $from = $configValues['CONFIG_MAIL_SMTPFROM'];

    $pdfDocument = @createBatchDetailsNotification($customerInfo);

    if ($destination == "download") {
        $filename = sprintf('batch_notification_invoice_%s.pdf', date("Ymd"));
        $size = strlen($pdfDocument);
        
        header("Content-type: application/pdf");
        header(sprintf("Content-Disposition: attachment; filename=%s; size=%d", $filename, $size));
        print $pdfDocument;
        
    } else if ($destination == "email") {
        
        @emailNotification($pdfDocument, $customerInfo, $smtpInfo, $from);
        
        $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
                  ? trim($_SESSION['PREV_LIST_PAGE']) : "/mng-batch.php";
        header("Location: " . $redirect);
        
    }
    

?>
