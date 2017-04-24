# About

![daloradius_logo][daloRADIUS_Logo]

[daloRADIUS](http://www.daloradius.com) is an advanced RADIUS web management application aimed at managing hotspots and 
general-purpose ISP deployments. It features user management, graphical reporting, accounting, 
a billing engine and integrates with GoogleMaps for geo-locating.

daloRADIUS is written in PHP and JavaScript and utilizes a database abstraction
layer which means that it supports many database systems, among them the popular
MySQL, PostgreSQL, Sqlite, MsSQL, and many others.

It is based on a [FreeRADIUS](http://www.freeradius.org) deployment with a database server serving as the backend.
Among other features it implements ACLs, GoogleMaps integration for locating 
hotspots/access points visually and many more features.

# daloRADIUS Book

Liran Tal authored a book about working with daloRADIUS covering most aspects through the UI, including setting up a captive portal system.
## Amazon Paperback Book
The paperback book version is available through Amazon at http://www.amazon.com/daloRADIUS-User-Guide-Volume-1/dp/1463752199

![daloradius_book][daloRADIUS_Book]
## PDF Digital Book
There is also a digital version of the book via PDF, available at: https://lirantal.selz.com/






# Features

## Management
### User Management

    * List Users
    * Create New User
    * Create New User - Quick add
      easy to use for POS or HotSpot shops
    * Edit User
    * Search User
    * Delete User

![daloradius_logo][daloRADIUS_Feature_Management]

## HotSpot Management

    * List HotSpots
    * Create New HotSpot
    * Edit HotSpot
    * Delete HotSpot



## NAS Management

    * List NAS
    * Create New NAS
    * Edit NAS
    * Delete NAS



## Groups Management

    * List, Create New, Edit and Delete User-Groups Mapping
      usergroup table in radius database
    * List, Create New, Edit and Delete Group-Reply and Group-Check Settings
      radgroupreply and radgroupcheck tables in radius database for managing group-wide attributes




## Accounting
### Users Accounting By

    * Username
    * IP Address
    * NAS IP Address
    * Date (From/To)
    * Display of All Accounting records
      the entire content of the radacct table in the radius database
    * Display of Active Accounting records
      performed by an algorithm implemented by daloRADIUS itself to calculate if
      an account has expired or not based on it's Max-All-Session attribute or Expiration attribute
	* Custom Accounting Query 


### HotSpots Accounting

    * Comparison of Accounting for different HotSpots
      provides information on hotspot's unique users, total hits, average time and total time

![daloradius_logo][daloRADIUS_Feature_Accounting]



### GIS - Geographical Information System

	daloRADIUS comes with integrated support for GIS provided by GoogleMaps and thus
	provides the ability to visually locate deployed HotSpots across a map, see their status,
	and monitor them visually.
	
	* View Map
	  Provides functionality of monitoring deployed HotSpots

	* Edit Map
	  Provides functionality for adding or deleting HotSpots from within the map itself
	  (i.e: no need to go to HotSpots Management page and delete or create a new one there)



## Reporting


### Basic Reporting

    * Online Users
      View Online users, users that are connected to the system from all NASes at a current
      point in time.
    * Last Connection Attempts
      View last connection attempts and their status - whether they were rejected or successful
    * Search Users
      Search for Users - similar to the functionality in User Management page
    * Top Users
      View a report of the Top Users based on their Bandwidth consumption or Time usage



### Logs Reporting

    * daloRADIUS Log
      daloRADIUS keeps a log file for all the actions it performs itself (viewing pages,
      form actions like deleting users, creating new hotspots, queries submission as in
      performing user accounting and more)
    * RADIUS Server Log
      Provides monitoring of the freeradius server logfile
    * System Log
      Provides monitoring of the system log, being syslog or messages, depends.
    * Boot Log
      Provides monitoring of the boot/kernel log (dmesg)



### Status Reporting

    * Server Status
      Provides detailed information on the server daloRADIUS is deployed.
      Information such as CPU utilization, uptime, memory, disks information, and more.
    * RADIUS Status
      Provides information whether the freeradius server is running along with the database
      server (mysql, postgresql, or others)




## Graphs

### Users Graphs
Provides visual graphs and statistical listing per user connection's attributes, being:

    * Logins/Hits
    * Download
    * Upload


### Server-Wide Graphs
Provides visual graphs and statistical listing for the entire server, all-time information on:

    * Logins/Hits
    * Traffic Comparison




## Configuration

### Global Configuration

    * Database Settings
      Database connection information (storage: mysql, postgresql and others),
      credentials (username and password), radius database tables names (radcheck, radacct, etc),
      and database password encryption type (none, md5, sha1)
    * Language Settings
      daloRADIUS is multi-lingual and supports currently English and Russian language packs
    * Logging Settings and Debugging
      Logging of different actions, queries and page visiting performed on different pages.
      Also supports debugging of SQL queries executed.
    * Interface Settings
      Support for displaying password text in either clear-text or as asterisks to hide it.
      Table listing spanning across multiple pages is configurable on number of rows per page
      and addition of numbers links for quick-access to different pages.


### Maintenance

    * Test User Connectivity
      Provides the ability to check if a user's credentials (username and password) are valid by
      executing a radius query to a radius server (configurable for radius port, shared secret, etc)
	* Disconnect User
	  Supply a username and send a PoD (Packet of Disconnect) or CoA (Change of Authority) packet
	  to the NAS to disconnect the user.

### Operators

daloRADIUS supports Operators for complete management of the entire platform.
Different Operators can be added with their contact information and ACLs settings to
grant or revoke them of permissions to access different pages.

    * List Operators
    * Create New Operator
    * Edit Operator
    * Delete Operator








# Credits

 [daloRADIUS](http://www.daloradius.com) makes use of several third-party packages and I would like to thank these
 great tools and their authors for releasing such a good software to the community.

 * datepicker PHP class	- Stefan Gabos <ix@nivelzero.ro>
 * libchart PHP class	- Jean-Marc Trémeaux <jm.tremeaux@gmail.com>
 * icons collection - Mark James of famfamfam.com icons <mjames@gmail.com>
 * ajax auto complete - Batur Orkun <batur@bilkent.edu.tr>
 * dhtml-Suite - Magne Kalleland <post@dhtmlgoodies.com>



# Support

Helpful resources to find help and support with daloRADIUS:

 * *Official daloRADIUS Website*: http://www.daloradius.com
 * SourceForge hosted forums area: https://sourceforge.net/p/daloradius/discussion/
 * *Mailing List*: daloradius-users@lists.sourceforge.net and register here to post there: https://lists.sourceforge.net/lists/listinfo/daloradius-users
 * Facebook's daloRADIUS related group: https://www.facebook.com/groups/551404948256611/




# Copyright

Copyright Liran Tal 2007. All rights reserved.
For release information and license, read LICENSE.

[daloRADIUS](http://www.daloradius.com) version 0.9 stable release
by Liran Tal of Enginx <liran@enginx.com>


[daloRADIUS_Logo]: https://cloud.githubusercontent.com/assets/316371/7488472/87a11c08-f3d3-11e4-9a8e-96deafaf4d2f.png
[daloRADIUS_Feature_Management]: https://cloud.githubusercontent.com/assets/316371/7444436/48d887e4-f18b-11e4-855d-264dc6d881e1.jpg
[daloRADIUS_Feature_Accounting]: https://cloud.githubusercontent.com/assets/316371/7488564/9338bf0c-f3d4-11e4-977b-48227eb5c2b5.jpg
[daloRADIUS_Book]: https://cloud.githubusercontent.com/assets/316371/7488439/e3c9bd4c-f3d2-11e4-9d88-9f57098752e0.jpg
