<?php
/*********************************************************************
* Name: saveRealmsProxys.php
* Author: Liran tal <liran.tal@gmail.com>
*********************************************************************/

/*******************************************************************/
if ($fileFlag == 1) {
	
        // write daloradius header
        $currDate = date('Y-m-d_H:i:s');
        
		// open the file for reading and writing
        $origFd = fopen($filenameRealmsProxys, "r");
        
        // check if the file has daloradius signature
        //$dalo_signature = fgets($realmsFd, 12);
        $dalo_signature = fread($origFd, 12);
        if ( strcmp($dalo_signature, "# daloradius") !== 0) {
        	// if it doesn't then it's someone else's file so we make a backup copy of it
        	$test = @copy($filenameRealmsProxys, $filenameRealmsProxys.'orig-'.$currDate);
        	// if we weren't able to write the original file as a copy to the relevant directory
        	// then we copy it to daloradius's variable directory
        	if (!$test) {
        		copy($filenameRealmsProxys, $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'].'/proxy.conf.orig-'.$currDate);
        	}
        }

        
	
		// open the file for reading and writing
        $realmsFd = fopen($filenameRealmsProxys, "w");
        
        if ($realmsFd) {
	
			fwrite($realmsFd, '# daloradius - ' . $currDate . "\n\n");

			
			/* enumerate from database all proxy entries */
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS'];
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
	
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
						if ($row['proxyname']) {
                                fwrite($realmsFd, "proxy ".$row['proxyname']. " { \n");

                                if ($row['retry_delay'])
                                        fwrite($realmsFd, "\tretry_delay = " .$row['retry_delay']. "\n");
                                if ($row['retry_count'])
                                        fwrite($realmsFd, "\tretry_count = " .$row['retry_count']. "\n");
                                if ($row['dead_time'])
                                        fwrite($realmsFd, "\tdead_time = " .$row['dead_time']. "\n");
                                if ($row['default_fallback'])
                                        fwrite($realmsFd, "\tdefault_fallback = " .$row['default_fallback']. "\n");
                                fwrite($realmsFd, "}\n\n");
                        }
            }
	
			// put some blank space between proxys and realms
			fwrite($realmsFd, "\n\n");
	
			/* enumerate from database all realm entries */
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS'];	
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
	
            while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                        if ($row['realmname']) {
                                fwrite($realmsFd, "realm ".$row['realmname']. " { \n");

                                if ($row['type'])
                                        fwrite($realmsFd, "\ttype = " .$row['type']. "\n");
                                if ($row['authhost'])
                                        fwrite($realmsFd, "\tauthhost = " .$row['authhost']. "\n");
                                if ($row['accthost'])
                                        fwrite($realmsFd, "\taccthost = " .$row['accthost']. "\n");
                                if ($row['secret'])
                                        fwrite($realmsFd, "\tsecret = " .$row['secret']. "\n");
                                if ($row['ldflag'])
                                        fwrite($realmsFd, "\tldflag = " .$row['ldflag']. "\n");
                                if ($row['nostrip'])
                                        fwrite($realmsFd, "\tnostrip\n");
                                if ($row['hints'])
                                        fwrite($realmsFd, "\thints = " .$row['hints']. "\n");
                                if ($row['notrealm'])
                                        fwrite($realmsFd, "\tnotrealm = " .$row['notrealm']. "\n");

                                fwrite($realmsFd, "}\n\n");
                        }
            }
	
	
	        fclose($realmsFd);
	}

}
/*******************************************************************/








?>

