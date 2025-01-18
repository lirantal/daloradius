<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Description:    Russian language file
 *
 * Authors:        Evgeniy Kozhuhovskiy <Evgeniy Kozhuhovskiy>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/ru.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS Management, Reporting, Accounting and Billing by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . <<<EOF
 <span class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Follow @filippolauria on GitHub">
  <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>
</span>  and <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.
EOF;

$l['all']['ID'] = "ID";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['VendorID'] = "Vendor ID";
$l['all']['VendorName'] = "Vendor Name";
$l['all']['VendorAttribute'] = "Vendor Attribute";
$l['all']['RecommendedOP'] = "Recommended OP";
$l['all']['RecommendedTable'] = "Recommended Table";
$l['all']['RecommendedTooltip'] = "Recommended Tooltip";
/********************************************************************************/

$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "Имя NAS";
$l['all']['NasType'] = "Тип NAS";
$l['all']['NasPorts'] = "Порты NAS";
$l['all']['NasSecret'] = "NAS Secret";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "Описание NAS";
$l['all']['PacketType'] = "Тип пакета";
$l['all']['HotSpot'] = "Точка доступа";
$l['all']['HotSpots'] = "Точки доступа";
$l['all']['HotSpotName'] = "Имя точки доступа";
$l['all']['Username'] = "Имя пользователя";
$l['all']['Password'] = "Пароль";
$l['all']['PasswordType'] = "Тип пароля";
$l['all']['IPAddress'] = "IP-адрес";
$l['all']['Group'] = "Группа";
$l['all']['Groupname'] = "Имя группы";
$l['all']['GroupPriority'] = "Приоритет группы";
$l['all']['CurrentGroupname'] = "Текущая группа";
$l['all']['NewGroupname'] = "Новое имя группы";
$l['all']['Priority'] = "Приоритет";
$l['all']['Attribute'] = "Аттрибут";
$l['all']['Operator'] = "Оператор";
$l['all']['Value'] = "Значение";
$l['all']['NewValue'] = "Новое значение";
$l['all']['MaxTimeExpiration'] = "Максимальное время / Истечение";
$l['all']['UsedTime'] = "Использовано времени";
$l['all']['Status'] = "Статус";
$l['all']['Usage'] = "Использование";
$l['all']['StartTime'] = "Начало";
$l['all']['StopTime'] = "Окончание";
$l['all']['TotalTime'] = "Всего времени";
$l['all']['Bytes'] = "Байт";
$l['all']['Upload'] = "Передано";
$l['all']['Download'] = "Принято";
$l['all']['Termination'] = "Завершение";
$l['all']['NASIPAddress'] = "IP-адрес NAS'а";
$l['all']['NASShortName'] = "NAS Short Name";
$l['all']['Action'] = "Действие";
$l['all']['UniqueUsers'] = "Уникальных пользователей";
$l['all']['TotalHits'] = "Всего хитов";
$l['all']['AverageTime'] = "Среднее время";
$l['all']['Records'] = "Записи";
$l['all']['Summary'] = "Всего";
$l['all']['Statistics'] = "Статистика";
$l['all']['Credit'] = "Кредит";
$l['all']['Used'] = "Использовано";
$l['all']['LeftTime'] = "Осталось времени";
$l['all']['LeftPercent'] = "% времени осталось";
$l['all']['TotalSessions'] = "Всего сессий";
$l['all']['LastLoginTime'] = "Последнее время входа";
$l['all']['TotalSessionTime'] = "Общая продолжительность сессий";
$l['all']['Rate'] = "Rate";
$l['all']['Billed'] = "Billed";
$l['all']['TotalUsers'] = "Всего пользователей";
$l['all']['TotalBilled'] = "Всего начислено";
$l['all']['CardBank'] = "Card Bank";
$l['all']['Type'] = "Type";
$l['all']['CardBank'] = "CardBank";
$l['all']['MACAddress'] = "MAC-адрес";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN-код";
$l['all']['CreationDate'] = "Дата создания";

$l['all']['edit'] = "править";
$l['all']['del'] = "удалить";
$l['all']['groupslist'] = "список групп";
$l['all']['TestUser'] = "Проверить пользователя";
$l['all']['Accounting'] = "Аккаунтинг";
$l['all']['RADIUSReply'] = "Ответ RADIUS-сервера";

$l['all']['Debug'] = "Отладка";
$l['all']['Timeout'] = "Таймаут";
$l['all']['Retries'] = "Попыток";
$l['all']['Count'] = "Счет";
$l['all']['Requests'] = "Запросов";

$l['all']['DatabaseHostname'] = "Хост БД";
$l['all']['DatabaseUser'] = "Логин в БД";
$l['all']['DatabasePass'] = "Пароль БД";
$l['all']['DatabaseName'] = "Имя БД";

$l['all']['PrimaryLanguage'] = "Язык интерфейса";

$l['all']['PagesLogging'] = "Логгирование web-активности (посещение страниц)";
$l['all']['QueriesLogging'] = "Логгирование запросов (отчеты и графики)";
$l['all']['ActionsLogging'] = "Логгирование изменений (заполнение форм)";
$l['all']['FilenameLogging'] = "Лог-файл (полный путь)";
$l['all']['LoggingDebugOnPages'] = "Показывать отладочную информацию на странице";
$l['all']['LoggingDebugInfo'] = "Писать отладочную информацию в файл";

$l['all']['PasswordHidden'] = "Прятать пароли (звёздочки вместо паролей)";
$l['all']['TablesListing'] = "Строк/записей на странице";
$l['all']['TablesListingNum'] = "Включить нумерацию";

$l['all']['RadiusServer'] = "Radius-сервер";
$l['all']['RadiusPort'] = "Порт Radius (UDP)";

$l['all']['UsernamePrefix'] = "Префикс имени пользователя";
$l['all']['NumberInstances'] = "Количество создаваемых элементов";
$l['all']['UsernameLength'] = "Длинна имени пользователя";
$l['all']['PasswordLength'] = "Длинна пароля";

$l['all']['Expiration'] = "Окончание";
$l['all']['MaxAllSession'] = "Max-All-Session";
$l['all']['SessionTimeout'] = "Таймаут сессии";
$l['all']['IdleTimeout'] = "Таймаут неактивности";

$l['all']['Month'] = "Месяц";

$l['all']['DBEngine'] = "Тип БД";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "usergroup";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "operators";
$l['all']['rates'] = "rates";
$l['all']['hotspots'] = "hotspots";

$l['all']['DBPasswordEncryption'] = "Тип шифрования паролей в БД";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['vendorNameTooltip'] = "Пример: Cisco<br/>&nbsp;&nbsp;&nbsp;
                                        Имя производителя.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "Пример: string<br/>&nbsp;&nbsp;&nbsp;
                                        Тип значения аттрибута<br/>&nbsp;&nbsp;&nbsp;
                    (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Например: Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;
                                        Имя аттрибута.<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "Example: :=<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended attribute's operator.<br/>&nbsp;&nbsp;&nbsp;
                    (one of: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "Example: check<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended target table.<br/>&nbsp;&nbsp;&nbsp;
                    (either check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Example: the ip address for the user<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended tooltip.<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['UserEdit'] = "Редактировать пользователя";
$l['Tooltip']['HotspotEdit'] = "Редактировать точку доступа";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "Если вы укажете значение, то будет удалена только запись, соответствующая значениям, которые вы ввели. Если вы ничего не укажете, будут удалены все записи из данной группы!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "Если вы укажете значение, то будет удалена только запись, соответствующая значениям, которые вы ввели. Если вы ничего не укажете, то будут удалены все записи из данной группы!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(название)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!";


$l['Tooltip']['usernameTooltip'] = "The exact username as the user<br/>&nbsp;&nbsp;&nbsp;
                    will use to connect to the system";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "Passwords are case sensetive in<br/>&nbsp;&nbsp;&nbsp;
                    certain systems so take extra care";
$l['Tooltip']['groupTooltip'] = "The user will be added to this group.<br/>&nbsp;&nbsp;&nbsp;
                    By assigning a user to a particular group<br/>&nbsp;&nbsp;&nbsp;
                    the user is subject to the group's attributes";
$l['Tooltip']['macaddressTooltip'] = "Example: 00aabbccddee<br/>&nbsp;&nbsp;&nbsp;
                    The MAC Address format should be the same<br/>&nbsp;&nbsp;&nbsp;
                    as the NAS sends it. Mostly this is without<br/>&nbsp;&nbsp;&nbsp;
                    any characters.";
$l['Tooltip']['pincodeTooltip'] = "Example: khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    This is the exact pincode as the user will enter it.<br/>&nbsp;&nbsp;&nbsp;
                    You may use alpha numeric characters, case is sensituve";
$l['Tooltip']['usernamePrefixTooltip'] = "Example: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    This username prefix will be added to<br/>&nbsp;&nbsp;&nbsp;
                    the generated username finally.";
$l['Tooltip']['instancesToCreateTooltip'] = "Example: 100<br/>&nbsp;&nbsp;&nbsp;
                    The amount of random users to create<br/>&nbsp;&nbsp;&nbsp;
                    with the specified profile.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Example: 8<br/>&nbsp;&nbsp;&nbsp;
                    The characters length of the username<br/>&nbsp;&nbsp;&nbsp;
                    to be created. Recommended 8-12 chars.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Example: 8<br/>&nbsp;&nbsp;&nbsp;
                    The characters length of the password<br/>&nbsp;&nbsp;&nbsp;
                    to be created. Recommended 8-12 chars.";


$l['Tooltip']['hotspotNameTooltip'] = "Example: Hotel Stratocaster<br/>&nbsp;&nbsp;&nbsp;
                    a friendly name of the hotspot<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "Example: 00aabbccddee<br/>&nbsp;&nbsp;&nbsp;
                    The MAC address of the NAS<br/>";

$l['Tooltip']['geocodeTooltip'] = "Example: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    This is the GooleMaps location code used<br/>&nbsp;&nbsp;&nbsp;
                    to pin the Hotspot/NAS on the map (see GIS).";


/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/
$l['button']['ListAttributesforVendor'] = "Список аттрибутов для Vendor'а:";
$l['button']['NewVendorAttribute'] = "Новый vendor-аттрибут";
$l['button']['EditVendorAttribute'] = "Редактировать vendor-аттрибут";
$l['button']['SearchVendorAttribute'] = "Найти аттрибут";
$l['button']['RemoveVendorAttribute'] = "Удалить vendor-аттрибут";


$l['button']['BetweenDates'] = "Между датами:";
$l['button']['Where'] = "Где";
$l['button']['AccountingFieldsinQuery'] = "Поля аккаунтинга в запросе:";
$l['button']['OrderBy'] = "Сортировать по";
$l['button']['HotspotAccounting'] = "Аккаунтинг точек доступа";
$l['button']['HotspotsComparison'] = "Сравнение точек доступа";

$l['button']['ListUsers'] = "Список пользователей";
$l['button']['NewUser'] = "Новый пользователь";
$l['button']['NewUserQuick'] = "Быстрое добавление пользователя";
$l['button']['BatchAddUsers'] = "Массовое создание пользователей";
$l['button']['EditUser'] = "Редактировать пользователя";
$l['button']['SearchUsers'] = "Найти пользователя";
$l['button']['RemoveUsers'] = "Удалить пользователя";
$l['button']['ListHotspots'] = "Список точек доступа";
$l['button']['NewHotspot'] = "Новая точка доступа";
$l['button']['EditHotspot'] = "Редактировать точку доступа";
$l['button']['RemoveHotspot'] = "Удалить точку доступа";

$l['button']['ListNAS'] = "Список NAS";
$l['button']['NewNAS'] = "Новый NAS";
$l['button']['EditNAS'] = "Править NAS";
$l['button']['RemoveNAS'] = "Удалить NAS";

$l['button']['ListUserGroup'] = "Список соответствий пользователь-группа";
$l['button']['ListUsersGroup'] = "Показать пользовательские вхождения в группы";
$l['button']['NewUserGroup'] = "Новое соотношение пользователь-группа";
$l['button']['EditUserGroup'] = "Править соотношения пользователь-группа";
$l['button']['RemoveUserGroup'] = "Удалить соотношение пользователь-группа";

$l['button']['ListProfiles'] = "Список профайлов";
$l['button']['NewProfile'] = "Новый профайл";
$l['button']['EditProfile'] = "Редактировать профайл";
$l['button']['RemoveProfile'] = "Удалить профайл";

$l['button']['ListGroupReply'] = "List Group Reply Mappings";
$l['button']['SearchGroupReply'] = "Search Group Reply";
$l['button']['NewGroupReply'] = "New Group Reply Mapping";
$l['button']['EditGroupReply'] = "Edit Group Reply Mapping";
$l['button']['RemoveGroupReply'] = "Remove Group Reply Mapping";

$l['button']['ListGroupCheck'] = "List Group Check Mappings";
$l['button']['SearchGroupCheck'] = "Search Group Check";
$l['button']['NewGroupCheck'] = "New Group Check Mapping";
$l['button']['EditGroupCheck'] = "Edit Group Check Mapping";
$l['button']['RemoveGroupCheck'] = "Remove Group Check Mapping";

$l['button']['UserAccounting'] = "Аккаунтинг пользователя";
$l['button']['IPAccounting'] = "IP-аккаунтинг";
$l['button']['NASIPAccounting'] = "NAS IP-аккаунтинг";
$l['button']['DateAccounting'] = "Аккаунтинг по дате";
$l['button']['AllRecords'] = "Все записи";
$l['button']['ActiveRecords'] = "Активные записи";

$l['button']['OnlineUsers'] = "Пользователи онлайн";
$l['button']['LastConnectionAttempts'] = "Последние попытки установления соединения";
$l['button']['TopUser'] = "Рейтинг пользователей";

$l['button']['ServerStatus'] = "Статус сервера";
$l['button']['ServicesStatus'] = "Статус сервера";

$l['button']['daloRADIUSLog'] = "Лог daloRADIUS";
$l['button']['RadiusLog'] = "Лог Radius";
$l['button']['SystemLog'] = "Системный лог (syslog)";
$l['button']['BootLog'] = "Лог загрузки (dmesg)";

$l['button']['UserLogins'] = "User Logins";
$l['button']['UserDownloads'] = "User Downloads";
$l['button']['UserUploads'] = "User Uploads";
$l['button']['TotalLogins'] = "Total Logins";
$l['button']['TotalTraffic'] = "Total Traffic";

$l['button']['ViewMAP'] = "Просмотреть карту";
$l['button']['EditMAP'] = "Редактировать карту";
$l['button']['RegisterGoogleMapsAPI'] = "RegisterGoogleMaps API";

$l['button']['DatabaseSettings'] = "Настройки БД";
$l['button']['LanguageSettings'] = "Языковые настройки";
$l['button']['LoggingSettings'] = "Настройки логгирования";
$l['button']['InterfaceSettings'] = "Настройки интерфейса";

$l['button']['TestUserConnectivity'] = "Тестовый вход";
$l['button']['DisconnectUser'] = "Сбросить пользователя с линии";

$l['button']['ListOperators'] = "Список операторов";
$l['button']['NewOperator'] = "Новый оператор";
$l['button']['EditOperator'] = "Редактировать оператора";
$l['button']['RemoveOperator'] = "Удалить оператора";

$l['button']['ProcessQuery'] = "Process Query";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['VendorAttribute'] = "Vendor Attribute";


$l['title']['AccountRemoval'] = "Account Removal";
$l['title']['AccountInfo'] = "Account Info";

$l['title']['ProfileInfo'] = "Profile Info";

$l['title']['GroupInfo'] = "Group Info";
$l['title']['GroupAttributes'] = "Group Attributes";

$l['title']['NASInfo'] = "NAS Info";
$l['title']['NASAdvanced'] = "NAS Advanced";

$l['title']['UserInfo'] = "User Info";

$l['title']['Attributes'] = "Attributes";
$l['title']['ProfileAttributes'] = "Profile Attributes";

$l['title']['HotspotInfo'] = "Hotspot Info";
$l['title']['HotspotRemoval'] = "Hotspot Removal";

$l['title']['ContactInfo'] = "Contact Info";

$l['title']['Groups'] = "Groups";
$l['title']['RADIUSCheck'] = "Check Attributes";
$l['title']['RADIUSReply'] = "Reply Attributes";

$l['title']['Settings'] = "Settings";
$l['title']['DatabaseSettings'] = "Database Settings";
$l['title']['DatabaseTables'] = "Database Tables";
$l['title']['AdvancedSettings'] = "Advanced Settings";


$l['title']['Advanced'] = "Advanced";
$l['title']['Optional'] = "Optional";

/* ********************************************************************************** */


/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Login Required";
$l['text']['LoginPlease'] = "Login Please";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "First Name";
$l['ContactInfo']['LastName'] = "Last Name";
$l['ContactInfo']['Email'] = "Email";
$l['ContactInfo']['Department'] = "Department";
$l['ContactInfo']['WorkPhone'] = "Work Phone";
$l['ContactInfo']['HomePhone'] = "Home Phone";
$l['ContactInfo']['MobilePhone'] = "Mobile Phone";
$l['ContactInfo']['Notes'] = "Notes";

$l['ContactInfo']['OwnerName'] = "Owner Name";
$l['ContactInfo']['OwnerEmail'] = "Owner Email";
$l['ContactInfo']['ManagerName'] = "Manager Name";
$l['ContactInfo']['ManagerEmail'] = "Manager Email";
$l['ContactInfo']['Company'] = "Company";
$l['ContactInfo']['Address'] = "Address";
$l['ContactInfo']['Phone1'] = "Phone 1";
$l['ContactInfo']['Phone2'] = "Phone 2";
$l['ContactInfo']['HotspotType'] = "Hotspot Type";
$l['ContactInfo']['Website'] = "Website";

/* ********************************************************************************** */



$l['Intro']['msgerrorpermissions.php'] = "Error";

$l['Intro']['mngradattributes.php'] = "Vendor's Attributes Management";
$l['Intro']['mngradattributeslist.php'] = "Vendor's Attributes List";
$l['Intro']['mngradattributesnew.php'] = "New Vendor Attributes";
$l['Intro']['mngradattributesedit.php'] = "Edit Vendor's Attributes";
$l['Intro']['mngradattributessearch.php'] = "Search Attributes";
$l['Intro']['mngradattributesdel.php'] = "Remove Vendor's Attributes";


$l['Intro']['acctactive.php'] = "Active Records Accounting";
$l['Intro']['acctall.php'] = "All Users Accounting";
$l['Intro']['acctdate.php'] = "Date Sort Accounting";
$l['Intro']['accthotspot.php'] = "Hotspot Accounting";
$l['Intro']['acctipaddress.php'] = "IP Accounting";
$l['Intro']['accthotspotcompare.php'] = "Hotspot Comparison";
$l['Intro']['acctmain.php'] = "Accounting Page";
$l['Intro']['acctnasipaddress.php'] = "NAS IP Accounting";
$l['Intro']['acctusername.php'] = "Users Accounting";
$l['Intro']['acctcustom.php'] = "Custom Accountings";
$l['Intro']['acctcustomquery.php'] = "Custom Query Accounting";

$l['Intro']['billmain.php'] = "Billing Page";
$l['Intro']['billpersecond.php'] = "Prepaid Accounting";
$l['Intro']['billprepaid.php'] = "Prepaid Accounting";
$l['Intro']['billratesdel.php'] = "Delete Rate entry";
$l['Intro']['billratesedit.php'] = "Edit Rate Details";
$l['Intro']['billrateslist.php'] = "Rates Table";
$l['Intro']['billratesnew.php'] = "New Rate entry";

$l['Intro']['giseditmap.php'] = "Edit MAP Mode";
$l['Intro']['gismain.php'] = "GIS Mapping";
$l['Intro']['gisviewmap.php'] = "View MAP Mode";

$l['Intro']['graphmain.php'] = "Usage Graphs";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Total Traffic Comparison Usage";
$l['Intro']['graphsalltimelogins.php'] = "Total Logins";
$l['Intro']['graphsoveralldownload.php'] = "User Downlads";
$l['Intro']['graphsoveralllogins.php'] = "User Logins";
$l['Intro']['graphsoverallupload.php'] = "User Uploads";

$l['Intro']['replastconnect.php'] = "Last 50 Connection Attempts";
$l['Intro']['repstatradius.php'] = "Daemons Information";
$l['Intro']['repstatserver.php'] = "Server Status and Information";
$l['Intro']['reponline.php'] = "Listing Online Users";
$l['Intro']['replogssystem.php'] = "System Logfile";
$l['Intro']['replogsradius.php'] = "RADIUS Server Logfile";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS Logfile";
$l['Intro']['replogsboot.php'] = "Boot Logfile";
$l['Intro']['replogs.php'] = "Logs";

$l['Intro']['rephsall.php'] = "Hotspots Listing";
$l['Intro']['repmain.php'] = "Reports Page";
$l['Intro']['repstatus.php'] = "Status Page";
$l['Intro']['replogs.php'] = "Logs Page";
$l['Intro']['reptopusers.php'] = "Top Users";
$l['Intro']['repusername.php'] = "Users Listing";

$l['Intro']['mngbatch.php'] = "Create batch users";
$l['Intro']['mngdel.php'] = "Remove User";
$l['Intro']['mngedit.php'] = "Edit User Details";
$l['Intro']['mnglistall.php'] = "Users Listing";
$l['Intro']['mngmain.php'] = "Users and Hotspots Management";
$l['Intro']['mngnew.php'] = "New User";
$l['Intro']['mngnewquick.php'] = "Quick User Add";
$l['Intro']['mngsearch.php'] = "User Search";

$l['Intro']['mnghsdel.php'] = "Remove Hotspots";
$l['Intro']['mnghsedit.php'] = "Edit Hotspots Details";
$l['Intro']['mnghslist.php'] = "List Hotspots";
$l['Intro']['mnghsnew.php'] = "New Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Remove User-Group Mapping";
$l['Intro']['mngradusergroup.php'] = "User-Group Configuration";
$l['Intro']['mngradusergroupnew.php'] = "New User-Group Mapping";
$l['Intro']['mngradusergrouplist'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergrouplistuser'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergroupedit'] = "Edit User-Group Mapping for User:";

$l['Intro']['mngradnas.php'] = "NAS Configuration";
$l['Intro']['mngradnasnew.php'] = "New NAS Record";
$l['Intro']['mngradnaslist.php'] = "NAS Listing in Database";
$l['Intro']['mngradnasedit.php'] = "Edit NAS Record";
$l['Intro']['mngradnasdel.php'] = "Remove NAS Record";


$l['Intro']['mngradprofiles.php'] = "Profiles Configuration";
$l['Intro']['mngradprofilesedit.php'] = "Edit Profiles";
$l['Intro']['mngradprofilesdel.php'] = "Delete Profiles";
$l['Intro']['mngradprofileslist.php'] = "List Profiles";
$l['Intro']['mngradprofilesnew.php'] = "New Profile";

$l['Intro']['mngradgroups.php'] = "Groups Configuration";

$l['Intro']['mngradgroupreplynew.php'] = "New Group Reply Mapping";
$l['Intro']['mngradgroupreplylist.php'] = "Group Reply Mapping in Database";
$l['Intro']['mngradgroupreplyedit.php'] = "Edit Group Reply Mapping for Group:";
$l['Intro']['mngradgroupreplydel.php'] = "Remove Group Reply Mapping";
$l['Intro']['mngradgroupreplysearch.php'] = "Search Group Reply Mapping";

$l['Intro']['mngradgroupchecknew.php'] = "New Group Check Mapping";
$l['Intro']['mngradgroupchecklist.php'] = "Group Check Mapping in Database";
$l['Intro']['mngradgroupcheckedit.php'] = "Edit Group Check Mapping for Group:";
$l['Intro']['mngradgroupcheckdel.php'] = "Remove Group Check Mapping";
$l['Intro']['mngradgroupchecksearch.php'] = "Search Group Check Mapping";

$l['Intro']['configdb.php'] = "Database Configuration";
$l['Intro']['configlang.php'] = "Language Configuration";
$l['Intro']['configlogging.php'] = "Logging Configuration";
$l['Intro']['configinterface.php'] = "Web Interface Configuration";
$l['Intro']['configmainttestuser.php'] = "Test User Connectivity";
$l['Intro']['configmain.php'] = "Database Configuration";
$l['Intro']['configmaint.php'] = "Maintenance";
$l['Intro']['configbackup.php'] = "Backup";
$l['Intro']['configbackupbackup.php'] = "Backup";
$l['Intro']['configmaintdisconnectuser.php'] = "Disconnect User";

$l['Intro']['configoperators.php'] = "Operators Configuration";
$l['Intro']['configoperatorsdel.php'] = "Remove Operator";
$l['Intro']['configoperatorsedit.php'] = "Edit Operator Settings";
$l['Intro']['configoperatorsnew.php'] = "New Operator";
$l['Intro']['configoperatorslist.php'] = "Operators Listing";

$l['Intro']['login.php'] = "Login";

$l['captions']['providebillratetodel'] = "Provide the rate entry type which you would like to remove";
$l['captions']['detailsofnewrate'] = "You may fill below details for the new rate";
$l['captions']['filldetailsofnewrate'] = "Fill below the details for the new rate entry";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/


$l['helpPage']['login'] = "";

$l['helpPage']['mngradattributes'] = "";
$l['helpPage']['mngradattributeslist'] = "";
$l['helpPage']['mngradattributesnew'] = "";
$l['helpPage']['mngradattributesedit'] = "";
$l['helpPage']['mngradattributessearch'] = "";
$l['helpPage']['mngradattributesdel'] = "";

$l['helpPage']['msgerrorpermissions'] = "You do not have permissions to access the page. <br/>
Please consult with your System Administrator. <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "To remove a user entry from the database you must provide the username of the account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Profiles Management</b> - Manage Profiles for Users by composing a set of Reply Attributes and Check Attributes <br/>
Profiles can be thought of as the composition of Group Reply and Group Check. <br/>
<h200><b>List Profiles </b></h200> - List Profiles <br/>
<h200><b>New Profile </b></h200> - Add a Profile <br/>
<h200><b>Edit Profile </b></h200> - Edit a Profile <br/>
<h200><b>Delete Profile </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>Edit Profile </b></h200> - Edit a Profile <br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>Delete Profile </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>List Profiles </b></h200> - List Profiles <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>New Profile </b></h200> - Add a Profile <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>Groups Management</b> - Manage Group Reply and Group Check mappings (radgroupreply/radgroupcheck tables).<br/>
<h200><b>List Group Reply/Check </b></h200> - List Group Reply/Check Mappings<br/>
<h200><b>Search Group Reply/Check </b></h200> - Search a Group Reply/Check Mapping (you may use wildcards) <br/>
<h200><b>New Group Reply/Check </b></h200> - Add a Group Reply/Check Mapping <br/>
<h200><b>Edit Group Reply/Check </b></h200> - Edit a Group Reply/Check Mapping <br/>
<h200><b>Delete Group Reply/Check </b></h200> - Delete a Group Reply/Check Mapping <br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>New Group Check </b></h200> - Add a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>Delete Group Check </b></h200> - Delete a Group Check Mapping <br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>List Group Check </b></h200> - List Group Check Mappings<br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>Edit Group Check </b></h200> - Edit a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>Search Group Check </b></h200> - Search a Group Check Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>New Group Reply </b></h200> - Add a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>Delete Group Reply </b></h200> - Delete a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>List Group Reply </b></h200> - List Group Reply Mappings<br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>Edit Group Reply </b></h200> - Edit a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>Search Group Reply </b></h200> - Search a Group Reply </ Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "To remove a nas ip/host entry from the database you must provide the ip/host of the account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";



$l['helpPage']['mnghsdel'] = "To remove a hotspot from the database you must provide the hotspot's name<br/>";
$l['helpPage']['mnghsedit'] = "You may edit below details for hotspot<br/>";
$l['helpPage']['mnghsnew'] = "You may fill below details for new hotspot addition to database";
$l['helpPage']['mnghslist'] = "List of all hotspots in the database. You may use the quick links to edit or delete a hotspot from the database.";

$l['helpPage']['configdb'] = "
<b>Database Settings</b> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Global Settings</b></h200> - Database Storage Engine<br/>
<h200><b>Tables Settings</b></h200> - If not using the default FreeRADIUS schema you may change the names
of the table names<br/>
<h200><b>Advanced Settings</b></h200> - If you wish to store passwords for users in the database not in
plain text but rather have it encrypted somehow you may choose one of MD5 or Crypt<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
Please make sure that the filename that you specify has write permissions by the webserver<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>
";
$l['helpPage']['configmain'] = "
<b>Global Settings</b><br/>
<h200><b>Database Settings</b></h200> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>

<b>Sub-Category Configuration</b>
<h200><b>Maintenance </b></h200> - Maintenance options for Testing users connections or terminating their sessions <br/>
<h200><b>Operators</b></h200> - Configure Operators Access Control List (ACL) <br/>
";
$l['helpPage']['configmaint'] = "
<b>Maintenance</b><br/>
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid<br/>
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server
to disconnect a user and terminate his/her session in a given NAS.<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid.<br/>
daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server to disconnect a user and terminate his/her session in a given NAS.<br/>
For terminating a user's session it is required that the NAS support the PoD or CoA packet types, please consult your NAS vendor or
documentation for this. Moreover, it is required to know the NAS ports for PoD or CoA packets, whereas newer NASs use port 3799
while other ones are configured to receive the packet on port 1700.

daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>


";
$l['helpPage']['configoperatorsdel'] = "To remove an operator from the database you must provide the username.";
$l['helpPage']['configoperatorsedit'] = "Edit the operator user details below";
$l['helpPage']['configoperatorsnew'] = "You may fill below details for a new operator user addition to database";
$l['helpPage']['configoperatorslist'] = "Listing all Operators in database";
$l['helpPage']['configoperators'] = "Operators Configuration";
$l['helpPage']['configbackup'] = "Perform Backup";
$l['helpPage']['configbackupbackup'] = "Perform Backup";


$l['helpPage']['graphmain'] = "
<b>Graphs</b><br/>
<h200><b>Overall Logins/Hits</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of Logins (or 'hits' to the NAS) are displayed in a graph as well as accompanied by a table listing.<br/>
<h200><b>Overall Download Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Downloaded by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<h200><b>Overall Upload Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Upload by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<br/>
<h200><b>Alltime Logins/Hits</b></h200> - Plots a graphical chart of the Logins to the server for a given period of time.<br/>
<h200><b>Alltime Traffic Comparison</b></h200> - Plots a graphical chart of the Downloaded and Uploaded statisticse.
";
$l['helpPage']['graphsalltimelogins'] = "An All-Time statistics of Logins to the server based on a distribution over a period of time";
$l['helpPage']['graphsalltimetrafficcompare'] = "An All-Time statistics of Traffic through the server based on a distribution over a period of time.";
$l['helpPage']['graphsoveralldownload'] = "Plots a graphical chart of the Downloaded bytes to the server";
$l['helpPage']['graphsoverallupload'] = "Plots a graphical chart of the Uploaded bytes to the server";
$l['helpPage']['graphsoveralllogins'] = "Plots a graphical chart of the Login attempts to the server";



$l['helpPage']['replastconnect'] = "Lists all login attempts to the RADIUS server, both successful and failed logins";
$l['helpPage']['replogsboot'] = "Monitor Operating System Boot log - equivalent to running the dmesg command.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS's Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS's Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Operating System Logfile.";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS Log</b></h200> - Monitor daloRADIUS's Logfile.<br/>
<h200><b>RADIUS Log</b></h200> - Monitor FreeRADIUS's Logfile - equivalent to /var/log/freeradius/radius.log or /usr/local/var/log/radius/radius.log.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>System Log</b></h200> - Monitor Operating System Logfile - equivalent to /var/log/syslog or /var/log/message on most platform.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>Boot Log</b></h200> - Monitor Operating System Boot log - equivalent to running the dmesg command.
";
$l['helpPage']['repmain'] = "
<b>General Reports</b><br/>
<h200><b>Online Users</b></h200> - Provides a listing of all users which are
found to be online through the accounting table in the database. The check which is being performed is for users
with no ending time (AcctStopTime) set. It is important to notice that these users may also be of stale sessions
which happens when NASs for some reason fail to send the accounting-stop packets.<br/>
<h200><b>Last Connection Attempts</b></h200> - Provides a listing of all Access-Accept and Access-Reject (accepted and failed) logins
for users. <br/> These are pulled from the database's postauth table which is required to be defined
in FreeRADIUS's config file to actually log these.<br/>
<h200><b>Top User</b></h200> - Provides a listing of the top N users for bandwidth consumption and session time used<br/><br/>
<b>Sub-Category Reports</b><br/>
<h200><b>Logs</b></h200> - Provides access to daloRADIUS logfile, FreeRADIUSs logfile, System's logfile and Boot logfile<br/>
<h200><b>Status</b></h200> - Provides information on server status and RADIUS Components status";
$l['helpPage']['repstatradius'] = "Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.
";
$l['helpPage']['repstatserver'] = "Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['repstatus'] = "<b>Status</b><br/>
<h200><b>Server Status</b></h200> - Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.<br/>
<h200><b>RADIUS Status</b></h200> - Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['reptopusers'] = "Records for top users, those which are listed below have gained the highest consumption of session
time or bandwidth usage. Listing users of category: ";
$l['helpPage']['repusername'] = "Records found for user:";
$l['helpPage']['reponline'] = "
The following table lists users who are currently connected to
the system. It is very much possible that there are stale connections,
meaning that users got disconnected but the NAS didn't send or wasn't
able to send a STOP accounting packet to the RADIUS server.
";


$l['helpPage']['mnglistall'] = "Listing users in database";
$l['helpPage']['mngsearch'] = "Searching for user: ";
$l['helpPage']['mngnew'] = "You may fill below details for new user addition to database<br/>";
$l['helpPage']['mngedit'] = "Edit the user details below.<br/>";
$l['helpPage']['mngdel'] = "To remove a user entry from the database you must provide the username of the account<br/>";
$l['helpPage']['mngbatch'] = "You may fill below details for new user addition to database.<br/>
Note that these settings will apply for all the users that you are creating.<br/>";
$l['helpPage']['mngnewquick'] = "The following user/card is of type prepaid.<br/>
The amount of time specified in Time Credit will be used as the Session-Timeout and Max-All-Session
radius attributes";

// accounting section
$l['helpPage']['acctactive'] = "
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>
";
$l['helpPage']['acctall'] = "
    Provides full accounting information for all sessions in the database.
<br/>
";
$l['helpPage']['acctdate'] = "
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
";
$l['helpPage']['acctipaddress'] = "
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
";
$l['helpPage']['acctmain'] = "
<b>General Accounting</b><br/>
<h200><b>User Accounting</b></h200> -
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
<h200><b>IP Accounting</b></h200> -
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
<h200><b>NAS Accounting</b></h200> -
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
<h200><b>Date Accounting</b></h200> -
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
<h200><b>All Accounting Records</b></h200> -
    Provides full accounting information for all sessions in the database.
<br/>
<h200><b>Active Records Accounting</b></h200> -
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>

<br/>
<b>Sub-Category Accounting</b><br/>
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.
<br/>
<h200><b>Hotspots</b></h200> -
    Provides information on the different managed hotspots, comparison, and other useful information.
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
";
$l['helpPage']['acctusername'] = "
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Provides full accounting information for all sessions which originated from this specific Hotspot.
    This list is computed by listing only those records in the radacct table with the CalledStationId
    field which match a Hotspot's MAC Address entry in the Hotspot's management database.
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Accounting information provided: <br/><br/>
    Hotspot Name - The Hotspot's name <br/>
    Unique Users - Users that have logined only through this hotspot <br/>
    Total Hits - The total logins that were performed from this hotspot (unique and non unique) <br/>
    Average Time - The average time a user spent in this hotspot <br/>
    Total Time - The accumolated spent time of all users in this hotspot <br/>

<br/>
    Provides a graph plot of different comparisons made <br/>
    Graphs: <br/><br/>
    Distribution of Unique users per hotspot <br/>
    Distribution of Hits per hotspot <br/>
    Distribution of Time usage per hotspot <br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    Provides full accounting information for all sessions which originated from this specific Hotspot.
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Provides a graph plot of different comparisons made.
<br/>
";
// accounting custom queries section
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.<br/>
    You may adjust the query to it's max by modifying the settings in the left sidebar.<br/>
<br/>
    <b> Between Dates </b> - Set the beginning and ending date.
<br/>
    <b> Where </b> - Set the field in the database you wish to match (like a key), choose if the value
    to match to should be Equal (=) or it Contains part of the value you search for (like a regex). If you
    choose to use the Contains operator you shouldn't add any wildcards of the common form '*' but rather
    the value you input will automatically be searched in this form: *value* (or in mysql style: %value%).
<br/>
    <b> Query Accounting Fields </b> - You may choose which fields you would like to present in the resulting
    list.
<br/>
    <b> Order By </b> - Choose by which field you would like to order the results and it's type (Ascending
    or descending)
<br/>
";
$l['helpPage']['acctcustomquery'] = "";


$l['helpPage']['giseditmap'] = "
    Edit Map Mode - in this mode you are able to either Add or Delete Hotspots simply by clicking
    on a location of the map or by clicking on a hotspot (respectively).<br/><br/>
    <b> Adding Hotspot </b> - Simply click on a clear location of the map, you will be asked to provide
    the hotspot's name and it's MAC address. These are 2 crucial details later used to identify this hotspot
    in the accounting table. Always provide the correct MAC Address!
<br/><br/>
    <b> Deleting Hotspot </b> - Simply click on a hotspot's icon and you confirm the deletion of it from the
    database.
<br/>
";
$l['helpPage']['gisviewmap'] = "
    View Map Mode - in this mode you are able to browse the Hotspots as they are layed out
    in icons across the maps provided by GoogleMaps service.<br/><br/>

    <b> Clicking a Hotspot </b> -Will provide you with more in-depth detail on the hotspot.
    Such as the contact information for the hotspot, and statistics details.
<br/>
";
$l['helpPage']['gismain'] = "
<b> General Information </b>
GIS Mapping provides visual mappings of the hotspot location across the world's map using Google Maps API. <br/>
In the Management page you are able to add new hotspot entries to the database where there is also a field
called Geolocation, this is the numeric value that the Google Maps API uses in order to pin-point the exact
location of that hotspot on the map.<br/><br/>

<h200><b>2 Modes of Operation are provided:</b></h200>
One is the <b>View MAP</b> mode which enables 'surfing' through the world map
and view the current locations of the hotspots in the database and another one - <b>Edit MAP</b> - which is the mode
that one can use in order to create hotspot's visually by simply left-clicking on the map or removing
existing hotspot entries by left-clicking on existing hotspot flags.<br/><br/>

Another important issue is that each computer on the network requires a unique Registration code which you
can obtain from Google Maps API page by providing the complete web address to the hosted directory of
daloRADIUS application on your server. Once you have obtained that code from Google, simply paste it in the
Registration box and click the 'Register code' button to write it.
Then you may be able to use Google Maps services. <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "This user has no check attributes associated with it";
$l['messages']['noReplyAttributesForUser'] = "This user has no reply attributes associated with it";

$l['messages']['noCheckAttributesForGroup'] = "This group has no check attributes associated with it";
$l['messages']['noReplyAttributesForGroup'] = "This group has no reply attributes associated with it";

$l['messages']['nogroupdefinedforuser'] = "This user has no Groups associated with it";
$l['messages']['wouldyouliketocreategroup'] = "Would you like to create one?";


$l['messages']['missingratetype'] = "error: missing rate type to delete";
$l['messages']['missingtype'] = "error: missing type";
$l['messages']['missingcardbank'] = "error: missing cardbank";
$l['messages']['missingrate'] = "error: missing rate";
$l['messages']['success'] = "success";
$l['messages']['gisedit1'] = "Welcome, you are currently in Edit mode";
$l['messages']['gisedit2'] = "Remove current marker from map and database?";
$l['messages']['gisedit3'] = "Please enter name of HotSpot";
$l['messages']['gisedit4'] = "Add current marker to database?";
$l['messages']['gisedit5'] = "Please enter name of HotSpot";
$l['messages']['gisedit6'] = "Please enter the MAC Address of the Hotspot";

$l['messages']['gismain1'] = "Successfully updated GoogleMaps API Registration code";
$l['messages']['gismain2'] = "error: could not open the file for writing:";
$l['messages']['gismain3'] = "Check file permissions. The file should be writable by the webserver's user/group";
$l['messages']['gisviewwelcome'] = "Welcome to Enginx Visual Maps";

$l['messages']['loginerror'] = "<br/><br/>either of the following:<br/>
1. bad username/password<br/>
2. an administrator is already logged-in (only one instance is allowed) <br/>
3. there appears to be more than one 'administrator' user in the database <br/>
";

$l['buttons']['savesettings'] = "Save Settings";
$l['buttons']['apply'] = "Apply";

$l['menu']['Home'] = "Home";
$l['menu']['Managment'] = "Management";
$l['menu']['Reports'] = "Reports";
$l['menu']['Accounting'] = "Accounting";
$l['menu']['Billing'] = "Billing";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Graphs";
$l['menu']['Config'] = "Config";
$l['menu']['Help'] = "Help";

// TODO translation needed.
// once translated, delete these comment lines.
$l['submenu']['General'] = "General";
$l['submenu']['Reporting'] = "Reporting";
$l['submenu']['Maintenance'] = "Maintenance";
$l['submenu']['Operators'] = "Operators";
$l['submenu']['Backup'] = "Backup";
$l['submenu']['Logs'] = "Logs";
$l['submenu']['Status'] = "Status";
$l['submenu']['Batch Users'] = "Batch Users";
$l['submenu']['Dashboard'] = "Dashboard";
$l['submenu']['Users'] = "Users";
$l['submenu']['Hotspots'] = "Hotspots";
$l['submenu']['Nas'] = "Nas";
$l['submenu']['User-Groups'] = "User-Groups";
$l['submenu']['Profiles'] = "Profiles";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "Attributes";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "POS";
$l['submenu']['Plans'] = "Plans";
$l['submenu']['Rates'] = "Rates";
$l['submenu']['Merchant-Transactions'] = "Merchant-Transactions";
$l['submenu']['Billing-History'] = "Billing-History";
$l['submenu']['Invoices'] = "Invoices";
$l['submenu']['Payments'] = "Payments";
$l['submenu']['Custom'] = "Custom";
$l['submenu']['Hotspot'] = "Hotspot";

?>
