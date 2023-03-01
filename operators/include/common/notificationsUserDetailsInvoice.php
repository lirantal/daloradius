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

    // prevent this file to be directly accessed
    if (strpos($_SERVER['PHP_SELF'], '/include/common/notificationsUserDetailsInvoice.php') !== false) {
        header("Location: ../../index.php");
        exit;
    }

    require_once(dirname(__FILE__)."/../../notifications/processNotificationUserDetailsInvoice.php");
    //require_once(dirname(__FILE__)."/../../library/config_read.php");

    /*
    isset($_GET['batch_name']) ? $batch_name = $_GET['batch_name'] : $batch_name = "";
    isset($_GET['destination']) ? $destination = $_GET['destination'] : $destination = "download";
    
    //test
    $batch_name = "batch_test6"; 
    
    if ($batch_name != "") {
        $customerInfo = @getBatchDetails($batch_name);
        
        $smtpInfo['host'] = $configValues['CONFIG_MAIL_SMTPADDR'];
        $smtpInfo['port'] = $configValues['CONFIG_MAIL_SMTPPORT'];
        $smtpInfo['auth'] = $configValues['CONFIG_MAIL_SMTPAUTH'];
        $from = $configValues['CONFIG_MAIL_SMTPFROM'];
        
        $pdfDocument = @createBatchDetailsNotification($customerInfo);
        
        if ($destination == "download") {
            
            header("Content-type: application/pdf");
            header("Content-Disposition: attachment; filename=batch_notification_invoice_" . date("Ymd") . ".pdf; size=" . strlen($pdfDocument));
            print $pdfDocument;
            
        } else if ($destination == "email") {
            
            @emailNotification($pdfDocument, $customerInfo, $smtpInfo, $from);
            header("Location: ".$_SERVER['HTTP_REFERER']);
        }
        
    }
    */
    
    function getCustomerInfo($row) {
    
        //global $configValues;
        
        $customerInfo = array();
        

        getCustomerInfo_customer_info($row, $customerInfo);
        getCustomerInfo_service_plan($row, $customerInfo);
        
        return $customerInfo;
        
    }
    
    
    
    function getCustomerInfo_customer_info($row, &$customerInfo) {
                
        global $configValues;
        require(dirname(__FILE__)."/../../lang/main.php");
        
        $tableTags = "width='580px' ";
        $tableTrTags = "bgcolor='#ECE5B6'";
        
        
        if (!empty($row['email1']))
            $invoice_email = $row['email1'];
        else if (!empty($row['email2']))
            $invoice_email = $row['email2'];
        else if (!empty($row['email3']))
            $invoice_email = $row['email3'];
        else
            $invoice_email = "";
        
        if (!empty($row['mobilephone']))
            $invoice_phone = $row['mobilephone'];
        else if (!empty($row['workphone']))
            $invoice_phone = $row['mobilephone'];
        else if (!empty($row['homephone']))
            $invoice_phone = $row['homephone'];
        else
            $invoice_phone = "Unavailable";
            
        $invoice_address = "";
        if (!empty($row['address']))
            $invoice_address = $row['address'];
        
        if (!empty($row['city']))
            $invoice_address .= ", ".$row['city'];
        
        if (!empty($row['state']))
            $invoice_address .= "<br/>".$row['state'];
        
        if (!empty($row['zip']))
            $invoice_address .= " ".$row['zip'];
        
        if (empty($invoice_address))
            $invoice_address = "Unavailable";
        
        $customerInfo['business_name'] = $row['firstname']. " " .$row['lastname'];
        $customerInfo['business_address'] = $invoice_address;
        $customerInfo['business_phone'] = $invoice_phone;
        $customerInfo['business_email'] = $invoice_email;


    }
    
    
    function getCustomerInfo_service_plan($row, &$customerInfo) {
                
        global $configValues;
        require(dirname(__FILE__)."/../../lang/main.php");
        
        $tableTags = "width='580px' ";
        $tableTrTags = "bgcolor='#ECE5B6'";
        
        $service_plan_info = "";
        $service_plan_info = "<table $tableTags>";
        
        $service_plan_info .= "".

                    "<tr $tableTrTags'>
                    <td>".t('all','Username')."</td>
                    <td>".$row['username']."</td>
                    </tr>".
        
                    "<tr $tableTrTags'>
                    <td>".t('all','PlanName')."</td>
                    <td>".$row['planname']."</td>
                    </tr>".
                    "<tr $tableTrTags'>
                    <td>".t('all','PlanRecurring')."</td>
                    <td>".$row['planRecurring']."</td>
                    </tr>".
                    "<tr $tableTrTags'>
                    <td>".t('all','PlanRecurringPeriod')."</td>
                    <td>".$row['planRecurringPeriod']."</td>
                    </tr>".
                    "<tr $tableTrTags'>
                    <td>".t('all','PlanCost')."</td>
                    <td>".$row['planCost']."</td>
                    </tr>".
                    "<tr $tableTrTags'>
                    <td>".t('all','NextBill')."</td>
                    <td>".$row['nextbill']."</td>
                    </tr>".
                    "<tr $tableTrTags'>
                    <td>".t('all','BillDue')."</td>
                    <td>".$row['billdue']."</td>
                    </tr>".
                    "";
        
        $service_plan_info .= "</table>";
        $customerInfo['service_plan_info'] = $service_plan_info;    
        
    }
    
    
    /*
    function getBatchDetails($batch_name = NULL) {
        
        require(dirname(__FILE__)."/../../library/opendb.php");
        require_once(dirname(__FILE__)."/../../lang/main.php");
        
        global $configValues;
        
        if ($batch_name == NULL || empty($batch_name))
            exit;
            

            
        $tableTags = "width='580px' ";
        $tableTrTags = "bgcolor='#ECE5B6'";
        
        $customerInfo = array();
        
        $sql = "SELECT ".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_description,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_status,".
            
            "COUNT(DISTINCT(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id)) as total_users,".
            "COUNT(DISTINCT(".$configValues['CONFIG_DB_TBL_RADACCT'].".username)) as active_users,".
            $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname,".
            $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".plancost,".
            $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".plancurrency,".
            $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as HotspotName,".
            
            
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".creationdate,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".creationby,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".updatedate,".
            $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".updateby ".
            " FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
            " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
            " ON ".
            "(".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id = ".
            $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id) ".

            " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
            " ON ".
            "(".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname = ".
            $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname) ".

            " LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
            " ON ".
            "(".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".hotspot_id = ".
            $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".id) ".
            
            " LEFT JOIN ".$configValues['CONFIG_DB_TBL_RADACCT'].
            " ON ".
            "(".$configValues['CONFIG_DB_TBL_RADACCT'].".username = ".
            $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username) ".
            
            " WHERE ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name = '$batch_name' ".
            " GROUP by ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ";
            
        $res = $dbSocket->query($sql);

        $batch_details = "";
        
        $batch_details .= "<table $tableTags><tr $tableTrTags>
                    <td> 
            ".t('all','BatchName')."
            </td>
    
            <td> 
            ".t('all','HotSpot')."
            </td>
    
            <td> 
            ".t('all','BatchStatus')."
            </td>
            
            <td> 
            ".t('all','TotalUsers')."
            </td>
    
            <td> 
            ".t('all','ActiveUsers')."
            </td>
    
            <td> 
            ".t('all','PlanName')."
            </td>
    
            <td> 
            ".t('all','PlanCost')."
            </td>
    
            <td> 
            ".t('all','BatchCost')."
            </td>
    
            <td> 
            ".t('all','CreationDate')."
            </td>
    
            <td> 
            ".t('all','CreationBy')."
            </td>
    
            </tr>";
        
        $active_users_per = 0;
        $total_users = 0;
        $active_users = 0;
        $batch_cost = 0;
        
        $hotspot_name = "";
        $batch_id = "";
        $planname = "";
        
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            
            $batch_id = $row['id'];
            $hotspot_name = $row['HotspotName'];
            $batch_status = $row['batch_status'];
            $plancost = $row['plancost'];
            $total_users = $row['total_users'];
            $active_users = $row['active_users'];
            $batch_cost = ($active_users * $plancost);
            $plan_currency = $row['plancurrency'];
            $planname = $row['planname'];
            
    
            $batch_details .= "
                    <tr>
                    
                    <td>".$row['batch_name']."
                        
                    </td>
                    
                    <td>".$hotspot_name."
                        
                    </td>
            
                    <td>".$batch_status."
                        
                    </td>
                    
                    <td>".$total_users."
                        
                    </td>
    
                    <td>".$active_users."
                        
                    </td>
    
                    <td>".
                        $row['planname']."
                    </td>
    
                    <td>".$plancost."
                    </td>
    
                    <td>".$batch_cost."
                    </td>
                    
                    <td>".
                        $row['creationdate']."
                    </td>
    
                    <td>".
                        $row['creationby']."
                    </td>
    
                </tr>
            ";
            

            
        }
        
        $batch_details .= "</table>";
        
        $customerInfo['batch_details'] = $batch_details;
        
        
        
        
        
        
        
        
        
        
        $sql = "SELECT planId, planName, planRecurringPeriod, planCost, planSetupCost, planTax, planCurrency FROM ".
                $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                " WHERE planName = '".$planname."'";
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
        $service_plan_info = "";
        $service_plan_info = "<table $tableTags>";
        
        foreach($row as $rowName => $rowValue) {
        
            $service_plan_info .= "<tr $tableTrTags'>
                        <td>$rowName</td>
                        <td>$rowValue</td>
                        </tr>";
        
        }
        
        $service_plan_info .= "</table>";
        $customerInfo['service_plan_info'] = $service_plan_info;
        
        
        $sql = "SELECT id, name, owner, address, companyphone, companyemail, companywebsite FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                    " WHERE name='".$hotspot_name."'";
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
        $customerInfo['business_name'] = $row['name'];
        $customerInfo['business_owner_name'] = $row['owner'];
        $customerInfo['business_address'] = $row['address'];
        $customerInfo['business_phone'] = $row['companyphone'];
        $customerInfo['business_email'] = $row['companyemail'];
        $customerInfo['business_web'] = $row['companywebsite'];
                
        
        
        
        
        
        $batch_active_users = "";
        
        $sql = "SELECT ".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id,".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username,".
                $configValues['CONFIG_DB_TBL_RADACCT'].".acctstarttime,".
                $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ".
                
                " FROM ".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].",".
                $configValues['CONFIG_DB_TBL_RADACCT'].",".
                $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
                
                " WHERE ".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = ".
                $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id".
                " AND ".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = '$batch_id' ".
                " AND ".
                $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username = ".
                $configValues['CONFIG_DB_TBL_RADACCT'].".username".
    
                " GROUP by ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username ".
                " ORDER BY id ,".$configValues['CONFIG_DB_TBL_RADACCT'].".radacctid ASC ";
                
        $res = $dbSocket->query($sql);
        
        $batch_active_users = "<table $tableTags><tr $tableTrTags'>
            <td> 
            ".t('all','BatchName')."
            </td>
    
            <td> 
            ".t('all','Username')."
            </td>
    
            <td> 
            ".t('all','StartTime')."
            </td>
    
            </tr>";

            
        $active_users_per = 0;
        $total_users = 0;
        $active_users = 0;
        $batch_cost = 0;
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
    
            $username = $row['username'];
            $acctstarttime = $row['acctstarttime'];
            $batch_name = $row['batch_name'];
            
            $batch_active_users .= "
                    <tr>
                    
                    <td>".$batch_name."
                    </td>
    
                    <td>".$username."
                    </td>
    
                    <td>".$acctstarttime."
                    </td>
    
                </tr>
            ";

        }
        

        $batch_active_users .= "</table>";
        $customerInfo['batch_active_users'] = $batch_active_users;
        
        
        require(dirname(__FILE__)."/../../library/closedb.php");
        
        return $customerInfo;
        
        
    }
    */
?>
