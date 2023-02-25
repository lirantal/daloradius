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
 * Description:    returns user status (active, expired, disabled)
 *                 as well as performs different user operations
 *                 (e.g. disable user, enable user, etc.) via ajax
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once('../checklogin.php');

// name of the group of disabled users
$disabled_groupname = 'daloRADIUS-Disabled-Users';

// username and divContainer are required
if (array_key_exists('username', $_GET) && isset($_GET['username']) &&
    array_key_exists('divContainer', $_GET) && isset($_GET['divContainer'])) {
    
    // divContainer id must begin with a letter ([A-Za-z]) and may be followed by any number of letters,
    // digits ([0-9]), hyphens ("-"), underscores ("_").
    if (!preg_match('/[A-Za-z][A-Za-z0-9_-]+/', $_GET['divContainer'])) {
        exit;
    }
    
    $divContainer = $_GET['divContainer'];
    
    // username could contain a list of usernames
    $tmp_usernames = (!is_array($_GET['username'])) ? array( $_GET['username'] ) : $_GET['username'];
    
    $usernames = array();
    
    // we escape username(s)
    foreach ($tmp_usernames as $tmp_username) {
        $tmp_username = trim(str_replace("%", "", $tmp_username));
        
        if (!empty($tmp_username) && !in_array($tmp_username, $usernames)) {
            $usernames[] = $tmp_username;
        }
    }
    
    if (count($usernames) == 0) {
        exit;
    }
    
    // we can handle these actions
    $action = "";
    if (isset($_GET['userDisable'])) {
        $action = 'userDisable';
    } else if (isset($_GET['refillSessionTime'])) {
        $action = 'refillSessionTime';
    } else if (isset($_GET['refillSessionTraffic'])) {
        $action = 'refillSessionTraffic';
    } else if (isset($_GET['checkDisabled'])) {
        $action = 'checkDisabled';
    } else {
        // this represents the default action
        $action = 'userEnable';
    }
    
    include('../../../common/includes/db_open.php');
    include_once('../../include/management/pages_common.php');
    
    // further escape usernames for safe db queries
    foreach ($usernames as $i => $username) {
        $usernames[$i] = $dbSocket->escapeSimple($username);
    }
    
    // commonly used in the following lines of code
    $username_list = "'" . implode("', '", $usernames) . "'";
    
    // used in presentation
    $username_list_enc = htmlspecialchars($username_list, ENT_QUOTES, 'UTF-8');
    $label = (count($usernames) > 1 || count($usernames) == 0) ? "users" : "user";

    switch ($action) {
    
        default:
        case 'userEnable':
            // delete from radusergroup
            $sql = sprintf("DELETE FROM %s WHERE username IN (%s) AND groupname='%s'", 
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username_list, $disabled_groupname);
            $res = $dbSocket->query($sql);
            
            // return message
            if (DB::isError($res)) {
                $class = "danger";
                $message = sprintf('Failed to enable %s <strong>%s</strong>.', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Enabled %s <strong>%s</strong>.', $label, $username_list_enc);
            }
                
            break;
    
        case 'userDisable':
            // get the list of users already disabled
            $sql = sprintf("SELECT DISTINCT(username)
                              FROM %s
                             WHERE username IN (%s)
                               AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $username_list, $disabled_groupname);
            $res = $dbSocket->query($sql);
            
            $already_disabled = array();
            while ($row = $res->fetchRow()) {
                $already_disabled[] = $row[0];
            }
        
            // no need to disable already disabled users
            $to_disable = array();
            foreach ($usernames as $username) {
                if (in_array($username, $already_disabled)) {
                    continue;
                }
                
                $to_disable[] = $username;
            }
        
            if (count($to_disable) > 0) {
        
                // this left piece of the query is the same for all 
                $sql0 = sprintf("INSERT INTO %s (username, groupname, priority) VALUES ",
                                $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
                                
                $sql_piece_format = "('%s', '%s', 0)";
                $sql_pieces = array();
                
                foreach ($to_disable as $username) {
                    $sql_pieces[] = sprintf($sql_piece_format, $username, $disabled_groupname);
                }
                
                // actually execute the query for disabling users
                $sql = $sql0 . implode(", ", $sql_pieces);
                $res = $dbSocket->query($sql);
                
                $to_disable_list = implode(", ", $to_disable);
                $to_disable_list_enc = htmlspecialchars($to_disable_list, ENT_QUOTES, 'UTF-8');
            
                if (DB::isError($res)) {
                    $class = "danger";
                    $message = sprintf('Failed to disable %s <strong>%s</strong>.', $label, $to_disable_list_enc);
                } else {
                    $class = "success";
                    $message = sprintf('Disabled %s <strong>%s</strong>.', $label, $to_disable_list_enc);
                }
            } else {
                $already_disabled_enc = htmlspecialchars(implode(", ", $already_disabled), ENT_QUOTES, 'UTF-8');
                $already_disabled_label = ((count($to_disable) > 1 || count($to_disable) == 0)) ? "users" : "user";
                
                $class = "danger";
                $message = sprintf('%s <strong>%s</strong> already disabled.', $already_disabled_label, $already_disabled_enc);
            }

            break;
        
        case 'checkDisabled':
            $username = $usernames[0];
        
            $sql = sprintf("SELECT username FROM %s WHERE username='%s' AND groupname='%s'",
                           $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                           $dbSocket->escapeSimple($username), $disabled_groupname);
            $res = $dbSocket->query($sql);
            $numrows = $res->numRows();
            
            if ($numrows > 0) {
                $class = "danger";
                $message = sprintf('Please note that user <strong>%s</strong> is currently disabled.',
                                   htmlspecialchars($username, ENT_QUOTES, 'UTF-8'))
                         . '<br>'
                         . sprintf('To enable this user, remove it from the <em>%s</em> profile.', $disabled_groupname);
            }
            break;
        
        case 'userRefillSessionTime':
            // we update the sessiontime value to be 0 - this will only work though
            // for accumulative type accounts. For TTF accounts we need to completely
            // delete the record.
            // to handle this - as a work-around I've modified the accessperiod sql
            // counter definition in radiusd.conf to check for records with AcctSessionTime>=1
            $sql = sprintf("UPDATE %s SET AcctSessionTime=0 WHERE Username IN (%s)",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $username_list);
            
            $res = $dbSocket->query($sql);

            $isErr = DB::isError($res);
            
            if (!$isErr) {

                // take care of recording the billing action in billing_history table
                foreach ($usernames as $username) {
                    $sql = sprintf("SELECT ubi.id, ubi.username, ubi.planName, bp.id as PlanID, bp.planTimeRefillCost, 
                                           bp.planTax, ubi.paymentmethod, ubi.cash, ubi.creditcardname, ubi.creditcardnumber,
                                           ubi.creditcardverification, ubi.creditcardtype, ubi.creditcardexp
                                      FROM %s AS ubi, %s AS bp
                                     WHERE ubi.planname = bp.planname AND ubi.username = '%s'",
                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $username);
                    $res = $dbSocket->query($sql);
                    $numrows = $res->numRows();
                    
                    if ($numrows == 0) {
                        continue;
                    }
                    
                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                    
                    $id = $row['id'];
                    $refillCost = $row['planTimeRefillCost'];

                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];
                    
                    $sql = sprintf("INSERT INTO %s (id, username, planId, billAmount, billAction, billPerformer, billReason,
                                                    paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification,
                                                    creditcardtype, creditcardexp, creationdate, creationby)
                                          VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                  '%s', '%s',  '%s')",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'], $username, $row['planName'], $row['planTimeRefillCost'],
                                   'Refill Session Time', 'daloRADIUS Web Interface', 'Refill Session Time', $row['paymentmethod'],
                                   $row['cash'], $row['creditcardname'], $row['creditcardnumber'], $row['creditcardverification'],
                                   $row['creditcardtype'], $row['creditcardexp'], $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    

                    // if the refill cost is anything beyond the amount 0, we create an invoice for it.
                    if ($refillCost > 0 && !empty($id)) {
            
                        // if the user id indeed set in the userbillinfo table
                        include_once('../../include/management/userBilling.php');
                
                        $invoiceInfo['notes'] = 'refill user account';
                        
                        // calculate tax (planTax is the numerical percentage amount) 
                        $planTax = floatval($row['planTax'] / 100);
                        
                        $invoiceItems[0]['plan_id'] = $row['PlanID'];
                        $invoiceItems[0]['amount'] = $row['planTimeRefillCost'];
                        $invoiceItems[0]['tax'] = floatval($row['planTimeRefillCost'] * $planTax);
                        $invoiceItems[0]['notes'] = 'refill user session time';
                                            
                        userInvoiceAdd($id, $invoiceInfo, $invoiceItems);
                    }
                }
            }

            // return message
            if ($isErr) {
                $class = "danger";
                $message = sprintf('Cannot refill session time for %s <strong>%s</strong>', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Session time for %s <strong>%s</strong> has been successfully refilled (and billed).',
                                   $label, $username_list_enc);
            }

            break;
    
         case 'userRefillSessionTraffic':
            $sql = sprintf("UPDATE %s SET AcctInputOctets=0, AcctOutputOctets=0 WHERE Username IN (%s)",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $username_list);
            
            $res = $dbSocket->query($sql);

            $isErr = DB::isError($res);
            
            if (!$isErr) {

                // take care of recording the billing action in billing_history table
                foreach ($usernames as $username) {
                
                    $sql = sprintf("SELECT ubi.id, ubi.username, ubi.planName, bp.id as PlanID, bp.planTax,
                                           bp.planTrafficRefillCost, ubi.paymentmethod, ubi.cash, ubi.creditcardname,
                                           ubi.creditcardnumber, ubi.creditcardverification, ubi.creditcardtype, ubi.creditcardexp
                                      FROM %s AS ubi, %s AS bp
                                     WHERE ubi.planname = bp.planname AND ubi.username = '%s'",
                                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $username);
                    $res = $dbSocket->query($sql);
                    $numrows = $res->numRows();
                    
                    if ($numrows == 0) {
                        continue;
                    }

                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                    
                    $id = $row['id'];
                    $refillCost = $row['planTrafficRefillCost'];

                    $currDate = date('Y-m-d H:i:s');
                    $currBy = $_SESSION['operator_user'];
                    
                    $sql = sprintf("INSERT INTO %s (id, username, planId, billAmount, billAction, billPerformer, billReason,
                                                    paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification,
                                                    creditcardtype, creditcardexp, creationdate, creationby)
                                          VALUES (0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                                                  '%s', '%s',  '%s')",
                                   $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'], $username, $row['planName'], $row['planTimeRefillCost'],
                                   'Refill Session Traffic', 'daloRADIUS Web Interface', 'Refill Session Traffic', $row['paymentmethod'],
                                   $row['cash'], $row['creditcardname'], $row['creditcardnumber'], $row['creditcardverification'],
                                   $row['creditcardtype'], $row['creditcardexp'], $currDate, $currBy);
                    $res = $dbSocket->query($sql);
                    
                    // if the refill cost is anything beyond the amount 0, we create an invoice for it.
                    if ($refillCost > 0 && !empty($id)) {
                        // if the user id indeed set in the userbillinfo table
                        include_once('../../include/management/userBilling.php');
                
                        $invoiceInfo['notes'] = 'refill user account';
                        
                        // calculate tax (planTax is the numerical percentage amount) 
                        $planTax = floatval($row['planTax'] / 100);
                        $invoiceItems[0]['plan_id'] = $row['PlanID'];
                        $invoiceItems[0]['amount'] = $row['planTrafficRefillCost'];
                        $invoiceItems[0]['tax'] = floatval($row['planTrafficRefillCost'] * $planTax);
                        $invoiceItems[0]['notes'] = 'refill user session traffic';
                                            
                        userInvoiceAdd($id, $invoiceInfo, $invoiceItems);

                    }
                }
            }
            
            // return message
            if ($isErr) {
                $class = "danger";
                $message = sprintf('Cannot refill session traffic for %s <strong>%s</strong>', $label, $username_list_enc);
            } else {
                $class = "success";
                $message = sprintf('Session traffic for %s <strong>%s</strong> has been successfully refilled (and billed).',
                                   $label, $username_list_enc);
            }
            
            break;
    
    }

    include('../../../common/includes/db_close.php');
    
    // output message
    if (isset($message) && isset($class)) {
        $div = sprintf('<div class="alert alert-%s" role="alert">%s</div>', $class, $message);
        printf("document.getElementById('%s').innerHTML = '%s';", $divContainer, $div);
    }
}
