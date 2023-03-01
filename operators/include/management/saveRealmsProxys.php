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
if (strpos($_SERVER['PHP_SELF'], '/include/management/saveRealmsProxy.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

if ($fileFlag == 1) {

    // write daloradius header
    $currDate = date('Y-m-d_H:i:s');

    // open the file for reading and writing
    $origFd = fopen($filenameRealmsProxys, "r");

    // check if the file has daloradius signature
    // $dalo_signature = fgets($realmsFd, 12);
    $dalo_signature = fread($origFd, 12);

    if (strcmp($dalo_signature, "# daloradius") !== 0) {
        
        // if it doesn't then it's someone else's file so we make a backup copy of it
        $backupFilename = sprintf("%s.orig-%s", $filenameRealmsProxys, $currDate);
        $test = @copy($filenameRealmsProxys, $backupFilename);
        
        // if we weren't able to write the original file as a copy to the relevant directory
        // then we copy it to daloradius's variable directory
        if (!$test) {
            $backupFilename = sprintf("%s/proxy.conf.orig-%s", $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'], $currDate);
            copy($filenameRealmsProxys, $backupFilename);
        }
    }

    // open the file for reading and writing
    $realmsFd = fopen($filenameRealmsProxys, "w");
        
    if ($realmsFd) {
        fwrite($realmsFd, sprintf("# daloradius - %s\n\n", $currDate));
            
        /* enumerate from database all proxy entries */
        $sql = sprintf("SELECT proxyname, retry_delay, retry_count, dead_time, default_fallback
                          FROM %s", $configValues['CONFIG_DB_TBL_DALOPROXYS']);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    
        $params = array('retry_delay', 'retry_count', 'dead_time', 'default_fallback');
    
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($row['proxyname']) {
                $output = sprintf("proxy %s { \n", $row['proxyname']);
            
                foreach ($params as $param) {
                    if ($row[$param]) {
                        $output .= sprintf("\t" . "%s = %s" . "\n", $param, $row[$param]);
                    }
                }
            
                $output .= "}\n\n";
                
                fwrite($realmsFd, $output);
            }
        }
    
        // put some blank space between proxys and realms
        fwrite($realmsFd, "\n\n");
    
        /* enumerate from database all realm entries */
        $sql = sprintf("SELECT realmname, type, authhost, accthost, secret, ldflag, nostrip, hints, notrealm
                          FROM %s", $configValues['CONFIG_DB_TBL_DALOREALMS']);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    
        $params = array(
                         'type', 'authhost', 'accthost', 'secret',
                         'ldflag', 'hints', 'notrealm'
                       );
    
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($row['realmname']) {
                $output = sprintf("realm %s { \n", $row['realmname']);
                
                foreach ($params as $param) {
                    if ($row[$param]) {
                        $output .= sprintf("\t" . "%s = %s" . "\n", $param, $row[$param]);
                    }
                }
                
                if ($row['nostrip']) {
                    $output .= "\t" . "nostrip" . "\n";
                }
                
                $output .= "}\n\n";
                
                fwrite($realmsFd, $output);
            }
        }
    
    fclose($realmsFd);
    }

}

?>
