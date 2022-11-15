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
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/validation.php') !== false) {
    header('Location: ../index.php');
    exit;
}

// some parameters can be validated using a whitelist.
// here we collect some useful whitelist.
// this lists can be also used for presentation purpose.
// whitelists naming convention:
// $valid_ [param_name] s
$valid_authTypes = array( "userAuth" => "Username/password based",
                          "macAuth" => "MAC Address base",
                          "pincodeAuth" => "PIN code based" );

$valid_passwordTypes = array(
                                "Cleartext-Password",
                                "NT-Password",
                                "MD5-Password",
                                "SHA1-Password",
                                "User-Password",
                                "Crypt-Password",
                                "CHAP-Password"
                             );

$valid_ops = array(
                    "=", ":=", ":=", "==", "+=", "!=", ">",
                    ">=", "<", "<=", "=~", "!~", "=*", "!*"
                  );

$valid_db_engines = array(
                            "mysql" => "MySQL",
                            "pgsql" => "PostgreSQL",
                            "odbc" => "ODBC",
                            "mssql" => "MsSQL",
                            "mysqli" => "MySQLi",
                            "msql" => "MsQL",
                            "sybase" => "Sybase",
                            "sqlite" => "Sqlite",
                            "oci8" => "Oci8 ",
                            "ibase" => "ibase",
                            "fbsql" => "fbsql",
                            "informix" => "informix"
                         );

$valid_nastypes = array(
                            "other", "cisco", "livingston", "computon", "max40xx",
                            "multitech", "natserver", "pathras",
                            "patton", "portslave", "tc", "usrhiper"
                       );

?>
