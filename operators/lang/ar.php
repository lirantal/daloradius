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
 * Description:    Arabic language file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *                 Muhammed AL-Qadhy <witradius@gmail.com>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/ar.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'راديوس الإدارة وإعداد التقارير والمحاسبة والفواتير <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'حقوق نشر دالوراديوس 2007-2023 بواسطة <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'تم تطوير دالوراديوس يواسطة <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "مسلسل";
$l['all']['PoolName'] = "IP حوض";
$l['all']['CalledStationId'] = "ماك نقطة الوصول";
$l['all']['CallingStationID'] = "ماك العميل";
$l['all']['ExpiryTime'] = "وقت انتهاء الصلاحية";
$l['all']['PoolKey'] = "IP مفتاح حوض";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "القاموس";
$l['all']['VendorID'] = "معرف الصانع";
$l['all']['VendorName'] = "اسم الصانع";
$l['all']['VendorAttribute'] = "صفات الصانع";
$l['all']['RecommendedOP'] = "Recommended OP";
$l['all']['RecommendedTable'] = "Recommended Table";
$l['all']['RecommendedTooltip'] = "Recommended Tooltip";
$l['all']['RecommendedHelper'] = "Recommended Helper";
/********************************************************************************/

$l['all']['CSVData'] = "CSV بيانات بصيغة";

$l['all']['CPU'] = "المعالج";

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "مسار قاموس راديوس";


$l['all']['DashboardSecretKey'] = "المفتاح السري للوحة التحكم";
$l['all']['DashboardDebug'] = "التصحيح";
$l['all']['DashboardDelaySoft'] = "Time in minutes to consider a 'soft' delay limit";
$l['all']['DashboardDelayHard'] = "Time in minutes to consider a 'hard' delay limit";



$l['all']['SendWelcomeNotification'] = "إرسال إشعار ترحيبي";
$l['all']['SMTPServerAddress'] = "SMTP Server Address";
$l['all']['SMTPServerPort'] = "SMTP Server Port";
$l['all']['SMTPServerFromEmail'] = "From Email Address";

$l['all']['customAttributes'] = "سمة-صفة مخصصة";

$l['all']['UserType'] = "نوع المستخدم";

$l['all']['BatchName'] = "اسم الدفعة";
$l['all']['BatchStatus'] = "حالة الدفعة";

$l['all']['Users'] = "المستخدمين";

$l['all']['Compare'] = "مقارنة";
$l['all']['Never'] = "أبداً";


$l['all']['Section'] = "قسم";
$l['all']['Item'] = "غرض";

$l['all']['Megabytes'] = "ميجا بايت";
$l['all']['Gigabytes'] = "جيجا بايت";

$l['all']['Daily'] = "يومي";
$l['all']['Weekly'] = "اسبوعي";
$l['all']['Monthly'] = "شهري";
$l['all']['Yearly'] = "سنوي";

$l['all']['Month'] = "شهر";

$l['all']['RemoveRadacctRecords'] = "إزالة السجلات المحاسبية";

$l['all']['CleanupSessions'] = "تنظيف الجلسات الأقدم من";
$l['all']['DeleteSessions'] = "إزالة الجلسات الأقدم من";

$l['all']['StartingDate'] = "تاريخ البدء";
$l['all']['EndingDate'] = "تاريخ الانتهاء";

$l['all']['Realm'] = "عالم";
$l['all']['RealmName'] = "اسم العالم";
$l['all']['RealmSecret'] = "كلمة سر العالم";
$l['all']['AuthHost'] = "مصدق المضيف";
$l['all']['AcctHost'] = "محاسب المضيف";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "hints";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy Name";
$l['all']['ProxySecret'] = "Proxy Secret";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";


$l['all']['Firmware'] = "نظام التشغيل";
$l['all']['NASMAC'] = "ماك خادم الوصول";

$l['all']['WanIface'] = "بورت الإنترنت";
$l['all']['WanMAC'] = "ماك بورت الإنترنت";
$l['all']['WanIP'] = "آيبي بورت الإنترنت";
$l['all']['WanGateway'] = "آيبي الراوتر";

$l['all']['LanIface'] = "بورت اللان";
$l['all']['LanMAC'] = "ماك اللان";
$l['all']['LanIP'] = "آيبي اللان";

$l['all']['WifiIface'] = "كارت الوايفاي";
$l['all']['WifiMAC'] = "ماك الوايفاي";
$l['all']['WifiIP'] = "آيبي الوايفاي";

$l['all']['WifiSSID'] = "اسم شبكة الوايفاي";
$l['all']['WifiKey'] = "باسورد الوايفاي";
$l['all']['WifiChannel'] = "قناة الوايفي";

$l['all']['CheckinTime'] = "آخر إتصال";

$l['all']['FramedIPAddress'] = "آيبي العميل";
$l['all']['SimultaneousUse'] = "عدد الأجهزة المسموح لها بالاتصال";
$l['all']['HgID'] = "HG ID";
$l['all']['Hg'] = "HG ";
$l['all']['HgIPHost'] = "HG IP/Host";
$l['all']['HgGroupName'] = "HG GroupName";
$l['all']['HgPortId'] = "HG Port Id";
$l['all']['NasID'] = "معرف خادم الشبكة";
$l['all']['Nas'] = "خادم الشبكة ";
$l['all']['NasIPHost'] = "عنوان آيبي خادم الشبكة";
$l['all']['NasShortname'] = "الاسم المختصر لخادم الشبكة";
$l['all']['NasType'] = "نوع خادم الشبكة";
$l['all']['NasPorts'] = "بورت خادم الشبكة";
$l['all']['NasSecret'] = "كلمة سر خادم الوصول للشبكة";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "وصف خادم الوصول للشبكة";
$l['all']['PacketType'] = "نوع الحزمة";
$l['all']['HotSpot'] = "هوتسبوت";
$l['all']['HotSpots'] = "هوتسبوت";
$l['all']['HotSpotName'] = "اسم الهوتسبوت";
$l['all']['Name'] = "الاسم";
$l['all']['Username'] = "اسم المستخدم";
$l['all']['Password'] = "كلمة السر";
$l['all']['PasswordType'] = "نوع كلمة السر";
$l['all']['IPAddress'] = "عنوان آي بي";
$l['all']['Profile'] = "بروفايل";
$l['all']['Group'] = "مجموعة";
$l['all']['Groupname'] = "اسم المجموعة";
$l['all']['ProfilePriority'] = "أولوية البروفايل";
$l['all']['GroupPriority'] = "أولوية المجموعة";
$l['all']['CurrentGroupname'] = "اسم المجموعة الحالية";
$l['all']['NewGroupname'] = "اسم مجموعة جديدة";
$l['all']['Priority'] = "أولوية";
$l['all']['Attribute'] = "سمة - صفة";
$l['all']['Operator'] = "مدير مسئول";
$l['all']['Value'] = "القيمة";
$l['all']['NewValue'] = "قيمة جديدة";
$l['all']['MaxTimeExpiration'] = "الوقت الاقصى / انتهاء الصلاحية";
$l['all']['UsedTime'] = "الوقت المستخدم";
$l['all']['Status'] = "الحالة";
$l['all']['Usage'] = "الاستهلاك";
$l['all']['StartTime'] = "وقت البدء";
$l['all']['StopTime'] = "وقت التوقف";
$l['all']['TotalTime'] = "الوقت الكلي";
$l['all']['TotalTraffic'] = "حجم البيانات الكلي";
$l['all']['Bytes'] = "بايت";
$l['all']['Upload'] = "رفع";
$l['all']['Download'] = "تحميل";
$l['all']['Rollback'] = "التراجع";
$l['all']['Termination'] = "سبب الفصل";
$l['all']['NASIPAddress'] = "عنوان أي بي خادم الوصول للشبكة";
$l['all']['NASShortName'] = "الاسم المختصر لخادم الوصول للشبكة";
$l['all']['Action'] = "عمل";
$l['all']['UniqueUsers'] = "المستخدمون الفريدون";
$l['all']['TotalHits'] = "اجمالي الظهور";
$l['all']['AverageTime'] = "متوسط الوقت";
$l['all']['Records'] = "السجلات";
$l['all']['Summary'] = "ملخص";
$l['all']['Statistics'] = "إحصائيات";
$l['all']['Credit'] = "الائتمان";
$l['all']['Used'] = "ماتم استخدامه";
$l['all']['LeftTime'] = "الوقت المتبقي";
$l['all']['LeftPercent'] = "النسبة المئوية للوقت المتبقي";
$l['all']['TotalSessions'] = "إجمالي الجلسات";
$l['all']['LastLoginTime'] = "أخر وقت لتسجيل الدخول";
$l['all']['TotalSessionTime'] = "إجمالي وقت الجلسات";
$l['all']['RateName'] = "اسم التسعير";
$l['all']['RateType'] = "نوع التسعير";
$l['all']['RateCost'] = "تكلفة التسعير";
$l['all']['Billed'] = "فاتورة";
$l['all']['TotalUsers'] = "جميع المستخدمين";
$l['all']['ActiveUsers'] = "المستخدمين النشطين";
$l['all']['TotalBilled'] = "إجمالي المفوتر";
$l['all']['TotalPayed'] = "إجمالي المدفوع";
$l['all']['Balance'] = "الرصيد";
$l['all']['CardBank'] = "كارت البنك";
$l['all']['Type'] = "النوع";
$l['all']['CardBank'] = "كارت البنك";
$l['all']['MACAddress'] = "عنوان ماك";
$l['all']['Geocode'] = "تكويد جغرافي";
$l['all']['PINCode'] = "كود PIN";
$l['all']['CreationDate'] = "تاريخ الاصدار";
$l['all']['CreationBy'] = "مصدر بواسطة";
$l['all']['UpdateDate'] = "تاريخ التحديث";
$l['all']['UpdateBy'] = "محدث بواسطة";

$l['all']['Discount'] = "خصم";
$l['all']['BillAmount'] = "مبلغ الفاتورة";
$l['all']['BillAction'] = "عمل مفوتر";
$l['all']['BillPerformer'] = "Bill Performer";
$l['all']['BillReason'] = "سبب الفواتير";
$l['all']['Lead'] = "قيادة";
$l['all']['Coupon'] = "كوبون";
$l['all']['OrderTaker'] = "متلقي الطلبات";
$l['all']['BillStatus'] = "حالة الفاتورة";
$l['all']['LastBill'] = "أخر فاتورة";
$l['all']['NextBill'] = "الفاتورة التالية";
$l['all']['BillDue'] = "الفاتورة المستحقة";
$l['all']['NextInvoiceDue'] = "الفاتورة التالية المستحقة";
$l['all']['PostalInvoice'] = "فاتورة بالبريد";
$l['all']['FaxInvoice'] = "فاتورة بالفاكس";
$l['all']['EmailInvoice'] = "فاتورة بالبريد الإلكتروني";

$l['all']['ClientName'] = "اسم العميل";
$l['all']['Date'] = "تاريخ";

$l['all']['edit'] = "تحرير";
$l['all']['del'] = "حذف";
$l['all']['groupslist'] = "قائمة المجموعات";
$l['all']['TestUser'] = "اختبار مستخدم";
$l['all']['Accounting'] = "محاسبة";
$l['all']['RADIUSReply'] = "رد الراديوس";

$l['all']['Disconnect'] = "قطع الاتصال";

$l['all']['Debug'] = "تصحيح";
$l['all']['Timeout'] = "نفاذ الوقت";
$l['all']['Retries'] = "اعادة المحاولات";
$l['all']['Count'] = "عددها";
$l['all']['Requests'] = "الطلبات";

$l['all']['DatabaseHostname'] = "اسم مستضيف قاعدة البيانات";
$l['all']['DatabasePort'] = "رقم البورت لقاعدة البيانات";
$l['all']['DatabaseUser'] = "اسم مستخدم قاعدة البيانات";
$l['all']['DatabasePass'] = "كلمة سر قاعدة البيانات";
$l['all']['DatabaseName'] = "اسم قاعدة البيانات";

$l['all']['PrimaryLanguage'] = "اللغة الرئيسية";

$l['all']['PagesLogging'] = "تسجيل الصفحات - زيارة الصفحات";
$l['all']['QueriesLogging'] = "تسجيل الاستعلامات - التقارير والرسوم البيانية";
$l['all']['ActionsLogging'] = "تسجيل الاجراءات - عمليات ارسال النموذج";
$l['all']['FilenameLogging'] = "اسم ملف التسجل - المسار الكامل";
$l['all']['LoggingDebugOnPages'] = "تسجيل معلومات التصحيح على الصفحة";
$l['all']['LoggingDebugInfo'] = "تسجيل معلومات التصحيح";

$l['all']['PasswordHidden'] = "تفعيل إخفاء كلمة السر - سيتم اظهار * بدل منها";
$l['all']['TablesListing'] = "عدد الصفوف المعروضة في الجدول لكل صفحة";
$l['all']['TablesListingNum'] = "تمكين ترقيم الجداول في الصفحات المعروضة";
$l['all']['AjaxAutoComplete'] = "تمكين التكملة التلقائية";

$l['all']['RadiusServer'] = "خادم الراديوس";
$l['all']['RadiusPort'] = "بورت الراديوس";

$l['all']['UsernamePrefix'] = "بادئة اسم المستخدم";

$l['all']['batchName'] = "معرف\اسم الدفعة";
$l['all']['batchDescription'] = "وصف الدفعة";

$l['all']['NumberInstances'] = "عدد المستخدمين المراد توليدهم";
$l['all']['UsernameLength'] = "عدد حروف اسم المستخدم";
$l['all']['PasswordLength'] = "عدد حروف كلمة السر";

$l['all']['Expiration'] = "انتهاء الصلاحية";
$l['all']['MaxAllSession'] = "الحد الأقصى للجلسات";
$l['all']['SessionTimeout'] = "مهلة الجلسة";
$l['all']['IdleTimeout'] = "مهلة الخمول";

$l['all']['DBEngine'] = "DBEngine";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "usergroup";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "المديرين";
$l['all']['operators_acl'] = "صلاحيات المدير";
$l['all']['operators_acl_files'] = "operators_acl_files";
$l['all']['billingrates'] = "أسعار الخدمة";
$l['all']['hotspots'] = "هوت سبوت";
$l['all']['node'] = "node";
$l['all']['nas'] = "خادم الشبكة";
$l['all']['hunt'] = "radhuntgroup";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "حزمة IP خادم الشبكة";
$l['all']['userinfo'] = "معلومات المستخدم";
$l['all']['dictionary'] = "القاموس";
$l['all']['realms'] = "realms";
$l['all']['proxys'] = "بروكسي";
$l['all']['billingpaypal'] = "باي بال";
$l['all']['billingmerchant'] = "تاجر";
$l['all']['billingplans'] = "فواتير الخطط";
$l['all']['billinghistory'] = "سجلات الفواتير";
$l['all']['billinginfo'] = "معلومات الفواتير";


$l['all']['CreateIncrementingUsers'] = "إنشاء مستخدمين متزايدين";
$l['all']['CreateRandomUsers'] = "إنشاء مستخدمين بشكل عشوائي";
$l['all']['StartingIndex'] = "مؤشر البداية";
$l['all']['EndingIndex'] = "مؤشر النهاية";
$l['all']['RandomChars'] = "الأحرف العشوائية المسموح بها";
$l['all']['Memfree'] = "الذاكرة الخالية";
$l['all']['Uptime'] = "مدة التشغيل";
$l['all']['BandwidthUp'] = "حجم الرفع";
$l['all']['BandwidthDown'] = "حجم التحميل";

$l['all']['BatchCost'] = "تكلفة الدفعة";

$l['all']['PaymentDate'] = "تاريخ الدفع";
$l['all']['PaymentStatus'] = "حالة الدفع";
$l['all']['FirstName'] = "الاسم الاول";
$l['all']['LastName'] = "الاسم الاخير";
$l['all']['VendorType'] = "التاجر";
$l['all']['PayerStatus'] = "حالة الدافع";
$l['all']['PaymentAddressStatus'] = "حالة عنوان الدفع";
$l['all']['PayerEmail'] = "البريد الإلكتروني للدافع";
$l['all']['TxnId'] = "معرف التحويل";
$l['all']['PlanActive'] = "خطة نشطة";
$l['all']['PlanTimeType'] = "نوع وقت الخطة";
$l['all']['PlanTimeBank'] = "وقت الخطة بالثانية";
$l['all']['PlanTimeRefillCost'] = "تكلفة إعادة تعبئة الخطة";
$l['all']['PlanTrafficRefillCost'] = "تكلفة إعادة تعبئة حجم الخطة";
$l['all']['PlanBandwidthUp'] = "سرعة رفع الخطة";
$l['all']['PlanBandwidthDown'] = "سرعة تنزيل الخطة";
$l['all']['PlanTrafficTotal'] = "إجمالي حجم الخطة";
$l['all']['PlanTrafficDown'] = "كمية التحميل للخطة";
$l['all']['PlanTrafficUp'] = "حجم الرفع للخطة";
$l['all']['PlanRecurring'] = "خطة متكررة";
$l['all']['PlanRecurringPeriod'] = "فترة الخطط المتكررة";
$l['all']['planRecurringBillingSchedule'] = "خطة جدولة الفواتير المتكررة";
$l['all']['PlanCost'] = "تكلفة الخطة";
$l['all']['PlanSetupCost'] = "تكلفة إعداد الخطة";
$l['all']['PlanTax'] = "ضريبة الخطة";
$l['all']['PlanCurrency'] = "عملة الخطة";
$l['all']['PlanGroup'] = "مجموعة الخطة";
$l['all']['PlanType'] = "نوع الخطة";
$l['all']['PlanName'] = "اسم الخطة";
$l['all']['PlanId'] = "معرف الخطة";

$l['all']['UserId'] = "معرف المستخدم";

$l['all']['Invoice'] = "فاتورة";
$l['all']['InvoiceID'] = "معرف الفاتورة";
$l['all']['InvoiceItems'] = "عناصر الفاتورة";
$l['all']['InvoiceStatus'] = "حالة لافاتورة";

$l['all']['InvoiceType'] = "نوع الفاتورة";
$l['all']['Amount'] = "المقدار";
$l['all']['Total'] = "الإجمالي";
$l['all']['TotalInvoices'] = "إجمالي الفواتير";

$l['all']['PayTypeName'] = "اسم نوع الدفع";
$l['all']['PayTypeNotes'] = "وصف نوع الدفع";
$l['all']['payment_type'] = "أنواع الدفع";
$l['all']['payments'] = "المدفوعات";
$l['all']['PaymentId'] = "معرف الدفع";
$l['all']['PaymentInvoiceID'] = "معرف الفاتورة";
$l['all']['PaymentAmount'] = "المقدار";
$l['all']['PaymentDate'] = "التاريخ";
$l['all']['PaymentType'] = "نوع الدفع";
$l['all']['PaymentNotes'] = "ملاحظات الدفع";




$l['all']['Quantity'] = "الكمية";
$l['all']['ReceiverEmail'] = "عنوان البريد الإلكتروني للمتلقي";
$l['all']['Business'] = "العمل";
$l['all']['Tax'] = "الضريبة";
$l['all']['Cost'] = "التكلفة";
$l['all']['TotalCost'] = "التكلفة الاجمالية";
$l['all']['TransactionFee'] = "رسوم التحويل";
$l['all']['PaymentCurrency'] = "عملة الدفع";
$l['all']['AddressRecipient'] = "عنوان المستلم";
$l['all']['Street'] = "الشارع";
$l['all']['Country'] = "الدولة";
$l['all']['CountryCode'] = "رمز الدولة";
$l['all']['City'] = "المدينة";
$l['all']['State'] = "المحافظة";
$l['all']['Zip'] = "الرمز البريدي";

$l['all']['BusinessName'] = "اسم العمل";
$l['all']['BusinessPhone'] = "تليفون العمل";
$l['all']['BusinessAddress'] = "عنوان العمل";
$l['all']['BusinessWebsite'] = "عنوان ويب العمل";
$l['all']['BusinessEmail'] = "البريد الاكتروني للعمل";
$l['all']['BusinessContactPerson'] = "فرد الاتصال للعمل";
$l['all']['DBPasswordEncryption'] = "نظام تشفير كلمة سر قاعدة البيانات";

$l['all']['Calling Station ID'] = "ماك العميل";
$l['all']['Framed IP Address'] = "آيبي العميل";

/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "قم بتوفير اسم معرف لإنشاء هذه الدُفعة";
$l['Tooltip']['batchDescriptionTooltip'] = "قدم وصفًا عامًا بخصوص إنشاء هذه الدُفعة";

$l['Tooltip']['hotspotTooltip'] = "اختر اسم نقطة الاتصال التي ترتبط بها هذه الدفعة";

$l['Tooltip']['startingIndexTooltip'] = "قم بتوفير فهرس البداية الذي سيتم إنشاء المستخدم منه";
$l['Tooltip']['planTooltip'] = "حدد خطة لربط المستخدم بها";

$l['Tooltip']['InvoiceEdit'] = "تحرير الفاتورة";
$l['Tooltip']['invoiceTypeTooltip'] = "نوع الفاتورة";
$l['Tooltip']['invoiceStatusTooltip'] = "حالة الفاتورة";
$l['Tooltip']['invoiceID'] = "اكتب معرف الفاتورة";
$l['Tooltip']['user_idTooltip'] = "معرف المستخدم";

$l['Tooltip']['amountTooltip'] = "القيمة";
$l['Tooltip']['taxTooltip'] = "الضريبة";

$l['Tooltip']['PayTypeName'] = "اكتب اسم نوع الدفع";
$l['Tooltip']['EditPayType'] = "تحرير نوع الدفع";
$l['Tooltip']['RemovePayType'] = "حذف نوع الدفع";
$l['Tooltip']['paymentTypeTooltip'] = "نوع طريقة الدفع,<br/>
                                        لوصف الغرض من الدفع";
$l['Tooltip']['paymentTypeNotesTooltip'] = "وصف نوع الدفع<br/>
                                        لنوع عملية الدفع";
$l['Tooltip']['EditPayment'] = "تحرير الدفع";
$l['Tooltip']['PaymentId'] = "معرف الدفع";
$l['Tooltip']['RemovePayment'] = "حذف الدفع";
$l['Tooltip']['paymentInvoiceTooltip'] = "الفاتورة الخاصة بعملية الدفع";



$l['Tooltip']['Username'] = "اكتب اسم المستخدم";
$l['Tooltip']['BatchName'] = "اكتب اسم الدفعة";
$l['Tooltip']['UsernameWildcard'] = "Hint: you may use the char * or % to specify a wildcard";
$l['Tooltip']['HotspotName'] = "اكتب اسم نقطة الوصول";
$l['Tooltip']['NasName'] = "اكتب اسم خادم الوصول للشبكة";
$l['Tooltip']['GroupName'] = "اكتب اسم المجموعة";
$l['Tooltip']['AttributeName'] = "اكتب اسم السمة";
$l['Tooltip']['VendorName'] = "اكتب اسم مزود السمة";
$l['Tooltip']['PoolName'] = "Type the Pool name";
$l['Tooltip']['IPAddress'] = "Type the IP address";
$l['Tooltip']['Filter'] = "Type a filter, can be any alpha numeric string. Leave empty to match anything. ";
$l['Tooltip']['Date'] = "Type the date <br/> example: 1982-06-04 (Y-M-D)";
$l['Tooltip']['RateName'] = "Type the Rate name";
$l['Tooltip']['OperatorName'] = "اكتب اسم المدير المسئول";
$l['Tooltip']['BillingPlanName'] = "اكتب اسم خطة الفواتير";
$l['Tooltip']['PlanName'] = "اكتب اسم الخطة";

$l['Tooltip']['EditRate'] = "تحرير التسعير";
$l['Tooltip']['RemoveRate'] = "حذف التسعير";

$l['Tooltip']['rateNameTooltip'] = "اسم التسعير,<br/>
                    لوصف الغرض من التسعير";
$l['Tooltip']['rateTypeTooltip'] = "نوع التسعير<br/>
                    لوصف عملية التسعير";
$l['Tooltip']['rateCostTooltip'] = "تكلفة التسعير";

$l['Tooltip']['planNameTooltip'] = "اسم الخطة<br/>
                    لوصف خصائص الخطة";
$l['Tooltip']['planIdTooltip'] = "";
$l['Tooltip']['planTimeTypeTooltip'] = "";
$l['Tooltip']['planTimeBankTooltip'] = "";
$l['Tooltip']['planTimeRefillCostTooltip'] = "";
$l['Tooltip']['planTrafficRefillCostTooltip'] = "";
$l['Tooltip']['planBandwidthUpTooltip'] = "";
$l['Tooltip']['planBandwidthDownTooltip'] = "";
$l['Tooltip']['planTrafficTotalTooltip'] = "";
$l['Tooltip']['planTrafficDownTooltip'] = "";
$l['Tooltip']['planTrafficUpTooltip'] = "";

$l['Tooltip']['planRecurringTooltip'] = "";
$l['Tooltip']['planRecurringBillingScheduleTooltip'] = "";
$l['Tooltip']['planRecurringPeriodTooltip'] = "";
$l['Tooltip']['planCostTooltip'] = "";
$l['Tooltip']['planSetupCostTooltip'] = "";
$l['Tooltip']['planTaxTooltip'] = "";
$l['Tooltip']['planCurrencyTooltip'] = "";
$l['Tooltip']['planGroupTooltip'] = "";

$l['Tooltip']['EditIPPool'] = "Edit IP-Pool";
$l['Tooltip']['RemoveIPPool'] = "Remove IP-Pool";
$l['Tooltip']['EditIPAddress'] = "Edit IP Address";
$l['Tooltip']['RemoveIPAddress'] = "Remove IP Address";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Proxy name";
$l['Tooltip']['proxyRetryDelayTooltip'] = "The time (in seconds) to wait <br/>
                    for a response from the proxy, <br/>
                    before re-sending the proxied request.";
$l['Tooltip']['proxyRetryCountTooltip'] = "The number of retries to send <br/>
                    before giving up, and sending a <br/>
                    reject message to the NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "If the home server does not respond <br/>
                    to any of the multiple retries, <br/>
                    then FreeRADIUS will stop sending it <br/>
                    proxy requests, and mark it 'dead'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "If all exact matching realms <br/>
                        did not respond, we can try the <br/>
                        ";
$l['Tooltip']['realmNameTooltip'] = "Realm name";
$l['Tooltip']['realmTypeTooltip'] = "Set to radius for default";
$l['Tooltip']['realmSecretTooltip'] = "Realm RADIUS shared secret";
$l['Tooltip']['realmAuthhostTooltip'] = "Realm authentication host";
$l['Tooltip']['realmAccthostTooltip'] = "Realm accounting host";
$l['Tooltip']['realmLdflagTooltip'] = "Allows for load balancing<br/>
                    Allowed values are 'fail_over' <br/>
                    and 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Whether to strip or not the <br/>
                    realm suffix";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "Example: Cisco<br/>&nbsp;&nbsp;&nbsp;
                                        The Vendor's name.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "Example: string<br/>&nbsp;&nbsp;&nbsp;
                                        The attributes variable type<br/>&nbsp;&nbsp;&nbsp;
                    (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Example: Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;
                                        The exact attribute name.<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "Example: :=<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended attribute's operator.<br/>&nbsp;&nbsp;&nbsp;
                    (one of: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "Example: check<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended target table.<br/>&nbsp;&nbsp;&nbsp;
                    (either check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Example: the ip address for the user<br/>&nbsp;&nbsp;&nbsp;
                                        The recommended tooltip.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['RecommendedHelperTooltip'] = "The helper function which will be<br/>&nbsp;&nbsp;&nbsp;
                                        available when adding this attribute<br/>&nbsp;&nbsp;&nbsp;";



$l['Tooltip']['AttributeEdit'] = "تحرير السمة";

$l['Tooltip']['BatchDetails'] = "تفاصيل الدفعة";

$l['Tooltip']['UserEdit'] = "تحرير مستخدم";
$l['Tooltip']['HotspotEdit'] = "تحرير نقطة وصول";
$l['Tooltip']['EditNAS'] = "تحرير خادم الوصول للشبكة";
$l['Tooltip']['RemoveNAS'] = "حذف خادم الوصول للشبكة";
$l['Tooltip']['EditHG'] = "Edit HuntGroup";
$l['Tooltip']['RemoveHG'] = "Remove HuntGroup";
$l['Tooltip']['hgNasIpAddress'] = "Type the Host/Ip address";
$l['Tooltip']['hgGroupName'] = "Type the Groupname for the NAS";
$l['Tooltip']['hgNasPortId'] = "Type the Nas Port Id";
$l['Tooltip']['EditUserGroup'] = "تحرير مجموعات المستخدم";
$l['Tooltip']['ListUserGroups'] = "عرض مجموعات المستخدم";
$l['Tooltip']['DeleteUserGroup'] = "حذف ارتباط المستخدم بالمجموعة";

$l['Tooltip']['EditProfile'] = "تحرير ملف شخصي";

$l['Tooltip']['EditRealm'] = "تحرير مملكة";
$l['Tooltip']['EditProxy'] = "تحرير بروكسي";

$l['Tooltip']['EditGroup'] = "تحرير مجموعة";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(descriptive name)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!";


$l['Tooltip']['usernameTooltip'] = "اسم المستخدم الذي سيقوم بالاتصال بالنظام";
$l['Tooltip']['passwordTypeTooltip'] = "نوع كلمة السر المستخدمة في عملية المصادقة";
$l['Tooltip']['passwordTooltip'] = "كلمة السر حساسة للحروف الكبيرة والصغيرة في بعض الأنظمة لذا يرجى توخي الحذر";
$l['Tooltip']['groupTooltip'] = "سيتم إضافة المستخدم إلى هذه المجموعة بتعيين مستخدم إلى مجموعة محددة سيكون المستخدم خاضع لسمات المجموعة";
$l['Tooltip']['macaddressTooltip'] = "مثال- 00:AA:BB:CC:DD:EE<br/>&nbsp;&nbsp;&nbsp;
                    طريقة كتابة الماك يجب أن تكون بهذه الطريقة<br/>&nbsp;&nbsp;&nbsp;
                    كما يقوم خادم شبكة الاتصال بارساله<br/>&nbsp;&nbsp;&nbsp;
                    بدون زيادات في الحروف.";
$l['Tooltip']['pincodeTooltip'] = "مثال : khrivnxufi101<br/>; هذا هو الرمز السري الذي سيقوم المستخدم بكتابته للدخول<br/>; يمكنك استخدام حروف وأرقام بدون الخضوع للحروف الكبيرة والصغيرة";
$l['Tooltip']['usernamePrefixTooltip'] = "مثال: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    سيتم إضافة بادئة اسم المستخدم هذاإلى<br/>&nbsp;&nbsp;&nbsp;
                    ما سيتم توليده من أسماء مستخدمين";
$l['Tooltip']['instancesToCreateTooltip'] = "مثال: 100<br/>&nbsp;&nbsp;&nbsp;
                    عدد المستخدمين المطلوب انشائهم<br/>&nbsp;&nbsp;&nbsp;
                    مع ملف شخصي محدد";
$l['Tooltip']['lengthOfUsernameTooltip'] = "مثال: 8<br/>&nbsp;&nbsp;&nbsp;
                    طول أحرف اسم المستخدم<br/>&nbsp;&nbsp;&nbsp;
                    المراد انشائها - الأفضل من 8 إلى 12 حرف";
$l['Tooltip']['lengthOfPasswordTooltip'] = "مثال: 8<br/>&nbsp;&nbsp;&nbsp;
                    طول أحرف كلمة السر<br/>&nbsp;&nbsp;&nbsp;
                    المراد انشائها - الأفضل من 8 إلى 12 حرف";


$l['Tooltip']['hotspotNameTooltip'] = "مثال: Hotspot-1<br/>&nbsp;&nbsp;&nbsp;
                    اسم نقطة الوصول<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "مثال: 00-aa-bb-cc-dd-ee<br/>&nbsp;&nbsp;&nbsp;
                    الماك الخاص بسيرفر الوصول للشبكة<br/>";

$l['Tooltip']['geocodeTooltip'] = "Example: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    This is the GooleMaps location code used<br/>&nbsp;&nbsp;&nbsp;
                    to pin the Hotspot/NAS on the map (see GIS).";

$l['Tooltip']['reassignplanprofiles'] = "If toggled on, when applying user information <br/>
                    the Profiles listed in the Profiles tab will be ignored and <br/>
                    profiles will be re-assigned based on the Plans profile association";

/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/

$l['button']['DashboardSettings'] = "اعدادات لوحدة التحكم";


$l['button']['GenerateReport'] = "توليد تقارير";

$l['button']['ListPayTypes'] = "عرض طرق الدفع";
$l['button']['NewPayType'] = "طريقة دفع جديدة";
$l['button']['EditPayType'] = "تحرير طريقة دفع";
$l['button']['RemovePayType'] = "حذف طريقة دفع";
$l['button']['ListPayments'] = "عرض المدفوعات";
$l['button']['NewPayment'] = "دفع جديد";
$l['button']['EditPayment'] = "تحرير دفع";
$l['button']['RemovePayment'] = "حذف دفع";

$l['button']['NewUsers'] = "مستخدمين جدد";

$l['button']['ClearSessions'] = "ازالة الجلسات";
$l['button']['Dashboard'] = "لوحة التحكم";
$l['button']['MailSettings'] = "اعدادات البريد الإلكتروني";

$l['button']['Batch'] = "دفعة";
$l['button']['BatchHistory'] = "قائمة حزم الكروت";
$l['button']['BatchDetails'] = "تفاصيل حزمة كروت";

$l['button']['ListRates'] = "قائمة الأسعار";
$l['button']['NewRate'] = "تسعير جديد";
$l['button']['EditRate'] = "تعديل التسعير";
$l['button']['RemoveRate'] = "ازالة التسعير";

$l['button']['ListPlans'] = "قائمة الخطط";
$l['button']['NewPlan'] = "خطة جديدة";
$l['button']['EditPlan'] = "تعديل الخطة";
$l['button']['RemovePlan'] = "إزالة الخطة";

$l['button']['ListInvoices'] = "قائمة الفواتير";
$l['button']['NewInvoice'] = "فاتورة جديدة";
$l['button']['EditInvoice'] = "تعديل الفاتورة";
$l['button']['RemoveInvoice'] = "إزالة الفاتورة";

$l['button']['ListRealms'] = "List Realms";
$l['button']['NewRealm'] = "New Realm";
$l['button']['EditRealm'] = "Edit Realm";
$l['button']['RemoveRealm'] = "Remove Realm";

$l['button']['ListProxys'] = "List Proxys";
$l['button']['NewProxy'] = "New Proxy";
$l['button']['EditProxy'] = "Edit Proxy";
$l['button']['RemoveProxy'] = "Remove Proxy";

$l['button']['ListAttributesforVendor'] = "عرض السمات الخاصة بالمزود:";
$l['button']['NewVendorAttribute'] = "سمة جديدة للمزود";
$l['button']['EditVendorAttribute'] = "تعديل سمة المزود";
$l['button']['SearchVendorAttribute'] = "بحث السمات";
$l['button']['RemoveVendorAttribute'] = "ازالة سمة مزود";
$l['button']['ImportVendorDictionary'] = "استيراد قاموس مزود";


$l['button']['BetweenDates'] = "بين التواريخ";
$l['button']['Where'] = "Where";
$l['button']['AccountingFieldsinQuery'] = "Accounting Fields in Query:";
$l['button']['OrderBy'] = "ترتيب حسب";
$l['button']['HotspotAccounting'] = "محاسبة نقطة الوصول";
$l['button']['HotspotsComparison'] = "مقارنة نقاط الوصول";

$l['button']['CleanupStaleSessions'] = "تنظيف الجلسات العالقة";
$l['button']['DeleteAccountingRecords'] = "حذف السجلات المحاسبية";

$l['button']['ListUsers'] = "قائمة المستخدمين";
$l['button']['ListBatches'] = "قائمة حزم الكروت";
$l['button']['RemoveBatch'] = "حذف حزمة كروت";
$l['button']['ImportUsers'] = "استيراد مستخدمين";
$l['button']['NewUser'] = "مستخدم جديد";
$l['button']['NewUserQuick'] = "مستخدم جديد - إضافة سريعة";
$l['button']['BatchAddUsers'] = "إنشاء حزمة كروت جديدة";
$l['button']['EditUser'] = "تعديل مستخدم";
$l['button']['SearchUsers'] = "بحث في المستخدمين";
$l['button']['RemoveUsers'] = "إزالة مستخدمين";
$l['button']['ListHotspots'] = "عرض نقاط الوصول";
$l['button']['NewHotspot'] = "نقطة وصول جديدة";
$l['button']['EditHotspot'] = "تعديل نقطة وصول";
$l['button']['RemoveHotspot'] = "حذف نقطة وصول";

$l['button']['ListIPPools'] = "عرض نطاق الأي بي";
$l['button']['NewIPPool'] = "نطاق أي بي جديد";
$l['button']['EditIPPool'] = "تعديل نطاق أي بي";
$l['button']['RemoveIPPool'] = "حذف نطاق أي بي";

$l['button']['ListNAS'] = "عرض خوادم الوصول للشبكة";
$l['button']['NewNAS'] = "خادم وصول للشبكة جديد";
$l['button']['EditNAS'] = "تحرير خادم وصول للشبكة";
$l['button']['RemoveNAS'] = "حذف خادم وصول للشبكة";
$l['button']['ListHG'] = "List HuntGroup";
$l['button']['NewHG'] = "New HuntGroup";
$l['button']['EditHG'] = "Edit HuntGroup";
$l['button']['RemoveHG'] = "Remove HuntGroup";
$l['button']['ListUserGroup'] = "عرض تعيينات المستخدم للمجموعة";
$l['button']['ListUsersGroup'] = "عرض تعيينات المستخدمين للمجموعات";
$l['button']['NewUserGroup'] = "تعيين جديد مستخدم لمجموعة";
$l['button']['EditUserGroup'] = "تحرير تعيين مستخدم لمجموعة";
$l['button']['RemoveUserGroup'] = "حذف تعيين مستخدم لمجموعة";

$l['button']['ListProfiles'] = "عرض البروفايلات";
$l['button']['NewProfile'] = "بروفايل جديد";
$l['button']['EditProfile'] = "تعديل بروفايل";
$l['button']['DuplicateProfile'] = "استنساخ بروفايل";
$l['button']['RemoveProfile'] = "حذف بروفايل";

$l['button']['ListGroupReply'] = "عرض تعيينات مجوعة الرد";
$l['button']['SearchGroupReply'] = "بحث في مجموعة الرد";
$l['button']['NewGroupReply'] = "تعيين مجموعة رد جديدة";
$l['button']['EditGroupReply'] = "تحرير مجموعة رد";
$l['button']['RemoveGroupReply'] = "حذف تعيين مجموعة رد";

$l['button']['ListGroupCheck'] = "عرض تعيينات مجموعة الفحص";
$l['button']['SearchGroupCheck'] = "بحث في مجموعة الفحص";
$l['button']['NewGroupCheck'] = "تعيين مجموعة فحص جديدة";
$l['button']['EditGroupCheck'] = "تحرير مجموعة فحص";
$l['button']['RemoveGroupCheck'] = "حذف تعيين مجموعة فحص";

$l['button']['UserAccounting'] = "محاسبة المستخدم";
$l['button']['IPAccounting'] = "محاسبة IP";
$l['button']['NASIPAccounting'] = "محاسبة خادم الوصول للشبكة IP";
$l['button']['NASIPAccountingOnlyActive'] = "إظهار النشط فقط";
$l['button']['DateAccounting'] = "تاريخ المحاسبة";
$l['button']['AllRecords'] = "كل السجلات";
$l['button']['ActiveRecords'] = "السجلات النشطة";

$l['button']['PlanUsage'] = "استهلاك الخطة";

$l['button']['OnlineUsers'] = "المستخدمين النشطين";
$l['button']['LastConnectionAttempts'] = "آخر محاولات الاتصال";
$l['button']['TopUser'] = "أعلى مستخدم";
$l['button']['History'] = "تاريخي";

$l['button']['ServerStatus'] = "حالة الخادم";
$l['button']['ServicesStatus'] = "حالة الخدمات";

$l['button']['daloRADIUSLog'] = "سجل دالوراديوس";
$l['button']['RadiusLog'] = "سجل الراديوس";
$l['button']['SystemLog'] = "سجل النظام";
$l['button']['BootLog'] = "سجل بدء التشغيل";

$l['button']['UserLogins'] = "تسجيلات دخول المستخدم";
$l['button']['UserDownloads'] = "تحميلات مستخدم";
$l['button']['UserUploads'] = "رفع مستخدم";
$l['button']['TotalLogins'] = "إجمالي عمليات تسجيل الدخول";
$l['button']['TotalTraffic'] = "إجمالي حجم البيانات";
$l['button']['LoggedUsers'] = "المستخدمون المسجلون";

$l['button']['ViewMAP'] = "عرض خريطة";
$l['button']['EditMAP'] = "تحرير خريطة";
$l['button']['RegisterGoogleMapsAPI'] = "RegisterGoogleMaps API";

$l['button']['UserSettings'] = "إعدادات المستخدم";
$l['button']['DatabaseSettings'] = "إعدادات قاعدة البيانات";
$l['button']['LanguageSettings'] = "اعدادات اللغة";
$l['button']['LoggingSettings'] = "أعدادات التسجيل";
$l['button']['InterfaceSettings'] = "إعدادات الواجهة";

$l['button']['ReAssignPlanProfiles'] = "إعادة تعيين ملف شخصي للخطة";

$l['button']['TestUserConnectivity'] = "إختبار اتصال مستخدم";
$l['button']['DisconnectUser'] = "فصل مستخدم";

$l['button']['ManageBackups'] = "إدارة النسخ الاحتياطية";
$l['button']['CreateBackups'] = "إنشاء نسخة احتياطية";

$l['button']['ListOperators'] = "عرض المديرين";
$l['button']['NewOperator'] = "مدير جديد";
$l['button']['EditOperator'] = "تحرير مدير";
$l['button']['RemoveOperator'] = "حذف مدير";

$l['button']['ProcessQuery'] = "استعلام عن عملية";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['ImportUsers'] = "استيراد مستخدمين";


$l['title']['Dashboard'] = "لوحة التحكم";
$l['title']['DashboardAlerts'] = "تنبيهات";

$l['title']['Invoice'] = "فاتورة";
$l['title']['Invoices'] = "فواتير";
$l['title']['InvoiceRemoval'] = "حذف فاتورة";
$l['title']['Payments'] = "المدفوعات";
$l['title']['Items'] = "العناصر";

$l['title']['PayTypeInfo'] = "معلومات نوع الدفع";
$l['title']['PaymentInfo'] = "معلومات الدفع";


$l['title']['RateInfo'] = "معلومات تسعير";
$l['title']['PlanInfo'] = "معلومات الخطة";
$l['title']['TimeSettings'] = "اعدادات الوقت";
$l['title']['BandwidthSettings'] = "اعدادات الحصة";
$l['title']['PlanRemoval'] = "حذف خطة";

$l['title']['BatchRemoval'] = "حذف دفعة";

$l['title']['Backups'] = "النسخ الاحتياطية";
$l['title']['FreeRADIUSTables'] = "جداول الراديوس";
$l['title']['daloRADIUSTables'] = "جداول دالوراديوس";

$l['title']['IPPoolInfo'] = "IP-Pool معلومات";

$l['title']['BusinessInfo'] = "Business معلومات";

$l['title']['CleanupRecordsByUsername'] = "حسب اسم المستخدم";
$l['title']['CleanupRecordsByDate'] = "حسب التاريخ";
$l['title']['DeleteRecords'] = "حذف السجلات";

$l['title']['RealmInfo'] = "معلومات العالم";

$l['title']['ProxyInfo'] = "معلومات البروكسي";

$l['title']['VendorAttribute'] = "سمات المزود";

$l['title']['AccountRemoval'] = "حذف الحساب";
$l['title']['AccountInfo'] = "معلومات الحساب";

$l['title']['Profiles'] = "Profiles";
$l['title']['ProfileInfo'] = "Profile Info";

$l['title']['GroupInfo'] = "معلومات المجموعة";
$l['title']['GroupAttributes'] = "سمات المجموعة";

$l['title']['NASInfo'] = "NAS Info";
$l['title']['NASAdvanced'] = "NAS Advanced";
$l['title']['HGInfo'] = "HG Info";
$l['title']['UserInfo'] = "User Info";
$l['title']['BillingInfo'] = "Billing Info";

$l['title']['Attributes'] = "Attributes";
$l['title']['ProfileAttributes'] = "Profile Attributes";

$l['title']['HotspotInfo'] = "معلومات نقطة الوصول";
$l['title']['HotspotRemoval'] = "حذف نقطة الوصول";

$l['title']['ContactInfo'] = "معلومات الاتصال";

$l['title']['Plan'] = "Plan";

$l['title']['Profile'] = "Profile";
$l['title']['Groups'] = "المجموعات";
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
 * Graphs
 * General graphing text
 ************************************************************************************/
$l['graphs']['Day'] = "يوم";
$l['graphs']['Month'] = "شهر";
$l['graphs']['Year'] = "سنة";
$l['graphs']['Jan'] = "يناير";
$l['graphs']['Feb'] = "فبراير";
$l['graphs']['Mar'] = "مارس";
$l['graphs']['Apr'] = "أبريل";
$l['graphs']['May'] = "مايو";
$l['graphs']['Jun'] = "يونيو";
$l['graphs']['Jul'] = "يوليو";
$l['graphs']['Aug'] = "أغسطس";
$l['graphs']['Sep'] = "سبتمبر";
$l['graphs']['Oct'] = "أكتوبر";
$l['graphs']['Nov'] = "نوفمبر";
$l['graphs']['Dec'] = "ديسمبر";


/* ********************************************************************************** */

/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "أدخل اسم المستخدم وكلمة المرور";
$l['text']['LoginPlease'] = "التسجيل";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "الاسم الاول";
$l['ContactInfo']['LastName'] = "الاسم الاخير";
$l['ContactInfo']['Email'] = "البريد الإلكتروني";
$l['ContactInfo']['Department'] = "القسم";
$l['ContactInfo']['WorkPhone'] = "هاتف العمل";
$l['ContactInfo']['HomePhone'] = "هاتف المنزل";
$l['ContactInfo']['Phone'] = "التليفون";
$l['ContactInfo']['MobilePhone'] = "المحمول";
$l['ContactInfo']['Notes'] = "ملاحظات";
$l['ContactInfo']['EnableUserUpdate'] = "تمكين تحديث المستخدم";
$l['ContactInfo']['EnablePortalLogin'] = "تمكين تسجيل الدخول لبوابة المستخدم";
$l['ContactInfo']['PortalLoginPassword'] = "كلمة سر بوابة تسجيل الدخول";

$l['ContactInfo']['OwnerName'] = "اسم المالك";
$l['ContactInfo']['OwnerEmail'] = "البريد الإلكتروني للمالك";
$l['ContactInfo']['ManagerName'] = "اسم المدير";
$l['ContactInfo']['ManagerEmail'] = "البريد الإلكتروني للمدير";
$l['ContactInfo']['Company'] = "الشركة";
$l['ContactInfo']['Address'] = "العنوان";
$l['ContactInfo']['City'] = "المدينة";
$l['ContactInfo']['State'] = "المحافظة";
$l['ContactInfo']['Country'] = "الدولة";
$l['ContactInfo']['Zip'] = "الرمز البريدي";
$l['ContactInfo']['Phone1'] = "هاتف1";
$l['ContactInfo']['Phone2'] = "هاتف2";
$l['ContactInfo']['HotspotType'] = "نوع نقطة الوصول";
$l['ContactInfo']['CompanyWebsite'] = "موقع الشركة";
$l['ContactInfo']['CompanyPhone'] = "تليفون الشركة";
$l['ContactInfo']['CompanyEmail'] = "البريد الإلكتروني للشركة";
$l['ContactInfo']['CompanyContact'] = "فرد الاتصال للشركة";

$l['ContactInfo']['PlanName'] = "اسم الخطة";
$l['ContactInfo']['ContactPerson'] = "فرد الاتصال";
$l['ContactInfo']['PaymentMethod'] = "طريقة الدفع";
$l['ContactInfo']['Cash'] = "نقدا";
$l['ContactInfo']['CreditCardNumber'] = "رقم كارت الدفع";
$l['ContactInfo']['CreditCardName'] = "اسم كارت الدفع";
$l['ContactInfo']['CreditCardVerificationNumber'] = "كود أمان كارت البنك";
$l['ContactInfo']['CreditCardType'] = "نوع كارت الدفع";
$l['ContactInfo']['CreditCardExpiration'] = "تاريخ انتهاء كارت الدفع";

/* ********************************************************************************** */

$l['Intro']['configdashboard.php'] = "اعدادات لوحة التحكم";



$l['Intro']['paymenttypesmain.php'] = "صفحة أصناف الدفع";
$l['Intro']['paymenttypesdel.php'] = "حذف صنف الدفع";
$l['Intro']['paymenttypesedit.php'] = "تعديل صنف الدفع";
$l['Intro']['paymenttypeslist.php'] = "جدول أصناف المدفوعات";
$l['Intro']['paymenttypesnew.php'] = "انشاء صنف دفع جديد";
$l['Intro']['paymenttypeslist.php'] = "جدول أصناف المدفوعات";
$l['Intro']['paymentslist.php'] = "جدول المدفوعات";
$l['Intro']['paymentsmain.php'] = "صفحة المدفوعات";
$l['Intro']['paymentsdel.php'] = "حذف مدخل مدفوع";
$l['Intro']['paymentsedit.php'] = "تعديل تفاصيل الدفع";
$l['Intro']['paymentsnew.php'] = "مدفوع جديد";

$l['Intro']['billhistorymain.php'] = "تاريخ الفواتير";
$l['Intro']['msgerrorpermissions.php'] = "ليس لديك الصلاحية لعرض الصفحة";

$l['Intro']['repnewusers.php'] = "عرض المستخدمين الجدد";

$l['Intro']['mngradproxys.php'] = "Proxys Management";
$l['Intro']['mngradproxysnew.php'] = "New Proxy";
$l['Intro']['mngradproxyslist.php'] = "List Proxy";
$l['Intro']['mngradproxysedit.php'] = "Edit Proxy";
$l['Intro']['mngradproxysdel.php'] = "Remove Proxy";

$l['Intro']['mngradrealms.php'] = "Realms Management";
$l['Intro']['mngradrealmsnew.php'] = "New Realm";
$l['Intro']['mngradrealmslist.php'] = "List Realm";
$l['Intro']['mngradrealmsedit.php'] = "Edit Realm";
$l['Intro']['mngradrealmsdel.php'] = "Remove Realm";

$l['Intro']['mngradattributes.php'] = "إدارة سمات \ صفات المزود";
$l['Intro']['mngradattributeslist.php'] = "قائمة سمات\صفات المزود";
$l['Intro']['mngradattributesnew.php'] = "إنشاء سمة\صفة جديدة للمزود";
$l['Intro']['mngradattributesedit.php'] = "تعديل سمة\صفة لمزود";
$l['Intro']['mngradattributessearch.php'] = "البحث في السمات\الصفات";
$l['Intro']['mngradattributesdel.php'] = "حذف سمة\صفة مزود";
$l['Intro']['mngradattributesimport.php'] = "استيراد قاموس مزود";
$l['Intro']['mngimportusers.php'] = "استيراد مستخدمين";


$l['Intro']['acctactive.php'] = "محاسبات المستخدمين النشطين";
$l['Intro']['acctall.php'] = "محاسبات جميع المستخدمين";
$l['Intro']['acctdate.php'] = "محاسبات مرتبة بالتاريخ";
$l['Intro']['accthotspot.php'] = "محاسبات الهوتسبوت";
$l['Intro']['acctipaddress.php'] = "محاسبات الآي بي";
$l['Intro']['accthotspotcompare.php'] = "مقارنة الهوتسبوت";
$l['Intro']['acctmain.php'] = "صفحة المحاسبات";
$l['Intro']['acctplans.php'] = "صفحة محاسبات الخطط";
$l['Intro']['acctnasipaddress.php'] = "محاسبات آي بي خادم الشبكة";
$l['Intro']['acctusername.php'] = "محاسبات المستخدمين";
$l['Intro']['acctcustom.php'] = "محاسبات مخصصة";
$l['Intro']['acctcustomquery.php'] = "محاسبة مخصصة من قاعدة البيانات";
$l['Intro']['acctmaintenance.php'] = "صيانة سجلات المحاسبات";
$l['Intro']['acctmaintenancecleanup.php'] = "حذف السجلات الوهمية";
$l['Intro']['acctmaintenancedelete.php'] = "حذف سجلات المحاسبات";

$l['Intro']['billmain.php'] = "صفحة الفواتير";
$l['Intro']['ratesmain.php'] = "قائمة الأسعار";
$l['Intro']['billratesdate.php'] = "حسابات أسعار الدفع المقدم";
$l['Intro']['billratesdel.php'] = "حذف تسعير";
$l['Intro']['billratesedit.php'] = "تعديل بيانات التسعير";
$l['Intro']['billrateslist.php'] = "جدول التسعير";
$l['Intro']['billratesnew.php'] = "اضافة تسعير جديد";

$l['Intro']['paypalmain.php'] = "PayPal Transactions Page";
$l['Intro']['billpaypaltransactions.php'] = "PayPal Transactions Page";

$l['Intro']['billhistoryquery.php'] = "سجل المستحقات";

$l['Intro']['billinvoice.php'] = "فواتير مستحقة";
$l['Intro']['billinvoicedel.php'] = "حذف فاتورة";
$l['Intro']['billinvoiceedit.php'] = "تعديل فاتورة";
$l['Intro']['billinvoicelist.php'] = "عرض الفواتير";
$l['Intro']['billinvoicereport.php'] = "تقارير الفواتير";
$l['Intro']['billinvoicenew.php'] = "فاتورة جديدة";

$l['Intro']['billplans.php'] = "صفحة فواتير الخطط";
$l['Intro']['billplansdel.php'] = "حذف خطة";
$l['Intro']['billplansedit.php'] = "تعديل خطة";
$l['Intro']['billplanslist.php'] = "جدول الخطط";
$l['Intro']['billplansnew.php'] = "خطة جديدة";

$l['Intro']['billpos.php'] = "نقطة البيع";
$l['Intro']['billposdel.php'] = "حذف مستخدم";
$l['Intro']['billposedit.php'] = "تعديل مستخدم";
$l['Intro']['billposlist.php'] = "عرض المستخدمين";
$l['Intro']['billposnew.php'] = "مستخدم جديد";

$l['Intro']['giseditmap.php'] = "Edit MAP Mode";
$l['Intro']['gismain.php'] = "GIS Mapping";
$l['Intro']['gisviewmap.php'] = "View MAP Mode";

$l['Intro']['graphmain.php'] = "بيانات الاستهلاك";
$l['Intro']['graphsalltimetrafficcompare.php'] = "مقارنة كلية بكميات الاستهلاك";
$l['Intro']['graphsalltimelogins.php'] = "عمليات الدخول";
$l['Intro']['graphsloggedusers.php'] = "المستخدمين الأونلاين";
$l['Intro']['graphsoveralldownload.php'] = "تحميلات المستخدم";
$l['Intro']['graphsoveralllogins.php'] = "تسجيل دخول المستخدم";
$l['Intro']['graphsoverallupload.php'] = "رفع المستخدم";

$l['Intro']['rephistory.php'] = "Action History";
$l['Intro']['replastconnect.php'] = "أخر محاولات الدخول";
$l['Intro']['repstatradius.php'] = "حالة البرامج";
$l['Intro']['repstatserver.php'] = "حالة ومعلومات الخادم";
$l['Intro']['reponline.php'] = "المستخدمين المتصلين حاليا";
$l['Intro']['replogssystem.php'] = "سجل نظام التشغيل";
$l['Intro']['replogsradius.php'] = "RADIUS Server Logfile";
$l['Intro']['replogsdaloradius.php'] = "سجل ويت راديوس";
$l['Intro']['replogsboot.php'] = "سجل بدء التشغيل";
$l['Intro']['replogs.php'] = "سجلات";
$l['Intro']['rephb.php'] = "Heartbeat";
$l['Intro']['rephbdashboard.php'] = "لوحة تحكم دالوراديوس";
$l['Intro']['repbatch.php'] = "حزمة";
$l['Intro']['mngbatchlist.php'] = "إدارة الحزم";
$l['Intro']['repbatchlist.php'] = "قائمة حزم الكروت";
$l['Intro']['repbatchdetails.php'] = "تفاصيل الحزمة";

$l['Intro']['rephsall.php'] = "قائمة الهوت سبوت";
$l['Intro']['repmain.php'] = "صفحة التقارير";
$l['Intro']['repstatus.php'] = "صفحة الحالة";
$l['Intro']['reptopusers.php'] = "أعلى المستخدمين";
$l['Intro']['repusername.php'] = "عرض المستخدمين";


$l['Intro']['mngbatch.php'] = "إنشاء حزمة كروت";
$l['Intro']['mngbatchdel.php'] = "حذف جلسات دفعة";

$l['Intro']['mngdel.php'] = "حذف مستخدم";
$l['Intro']['mngedit.php'] = "تحرير تفاصيل مستخدم";
$l['Intro']['mnglistall.php'] = "قائمة المستخدمين";
$l['Intro']['mngmain.php'] = "إدارة الهوت سبوت";
$l['Intro']['mngbatch.php'] = "إدارة حزم الكروت";
$l['Intro']['mngnew.php'] = "مستخدم جديد";
$l['Intro']['mngnewquick.php'] = "مستخدم جديد سريع";
$l['Intro']['mngsearch.php'] = "البحث عن مستخدم";

$l['Intro']['mnghsdel.php'] = "حذف هوت سبوت";
$l['Intro']['mnghsedit.php'] = "تعديل هوتسبوت";
$l['Intro']['mnghslist.php'] = "قائمة هوتسبوت";
$l['Intro']['mnghsnew.php'] = "إضافة هوتسبوت";

$l['Intro']['mngradusergroupdel.php'] = "حذف مستخدم من الارتباط مع مجموعة";
$l['Intro']['mngradusergroup.php'] = "تغيير البروفايل";
$l['Intro']['mngradusergroupnew.php'] = "إضافة بروفايل لمستخدم";
$l['Intro']['mngradusergrouplist'] = "بروفايلات المستخدمين";
$l['Intro']['mngradusergrouplistuser'] = "بروفايلات المستخدمين";
$l['Intro']['mngradusergroupedit'] = "تعديل بروفايل المستخدم";

$l['Intro']['mngradippool.php'] = "إعدادات نطاق أي بي";
$l['Intro']['mngradippoolnew.php'] = "نطاق أي بي جديد";
$l['Intro']['mngradippoollist.php'] = "عرض نطاق أي بي";
$l['Intro']['mngradippooledit.php'] = "تعديل نطاق أي بي";
$l['Intro']['mngradippooldel.php'] = "حذف نطاق أي بي";

$l['Intro']['mngradnas.php'] = "إعدادات خادم الوصول للشبكة";
$l['Intro']['mngradnasnew.php'] = "إضافة خادم وصول جديد";
$l['Intro']['mngradnaslist.php'] = "قائمة الخوادم المسجلة";
$l['Intro']['mngradnasedit.php'] = "تعديل الخادم";
$l['Intro']['mngradnasdel.php'] = "حذف لخادم";

$l['Intro']['mngradhunt.php'] = "HuntGroup Configuration";
$l['Intro']['mngradhuntnew.php'] = "New HuntGroup Record";
$l['Intro']['mngradhuntlist.php'] = "HuntGroup Listing in Database";
$l['Intro']['mngradhuntedit.php'] = "Edit HuntGroup Record";
$l['Intro']['mngradhuntdel.php'] = "Remove HuntGroup Record";

$l['Intro']['mngradprofiles.php'] = "إعدادات البروفايل";
$l['Intro']['mngradprofilesedit.php'] = "تعديل بروفايل";
$l['Intro']['mngradprofilesduplicate.php'] = "نسخ بروفايل";
$l['Intro']['mngradprofilesdel.php'] = "حذف بروفايل";
$l['Intro']['mngradprofileslist.php'] = "عرض بروفايل";
$l['Intro']['mngradprofilesnew.php'] = "بروفايل جديد";

$l['Intro']['mngradgroups.php'] = "إعدادات البروفايل";

$l['Intro']['mngradgroupreplynew.php'] = "مجموعة رد جديدة";
$l['Intro']['mngradgroupreplylist.php'] = "مجموعات الرد المخزنة";
$l['Intro']['mngradgroupreplyedit.php'] = "تعديل مجموعات الرد لمجموعة";
$l['Intro']['mngradgroupreplydel.php'] = "حذف مجموعة الرد";
$l['Intro']['mngradgroupreplysearch.php'] = "ابحث عن مجموعة رد";

$l['Intro']['mngradgroupchecknew.php'] = "مجموعة فحص جديدة";
$l['Intro']['mngradgroupchecklist.php'] = "مجموعات الفحص المخزنة";
$l['Intro']['mngradgroupcheckedit.php'] = "تعديل مجموعات الفحص لمجموعة";
$l['Intro']['mngradgroupcheckdel.php'] = "حذف مجموعة الفحص";
$l['Intro']['mngradgroupchecksearch.php'] = "ابحث عن مجموعة فحص";

$l['Intro']['configuser.php'] = "إعدادات المستخدم";
$l['Intro']['configmail.php'] = "إعدادات البريد الإلكتروني";

$l['Intro']['configdb.php'] = "إعدادات قواعد البيانات";
$l['Intro']['configlang.php'] = "إعدادات اللغة";
$l['Intro']['configlogging.php'] = "إعدادات السجلات";
$l['Intro']['configinterface.php'] = "إعدادات الواجهة";
$l['Intro']['configmainttestuser.php'] = "إختبار اتصال مستخدم";
$l['Intro']['configmain.php'] = "إعدادات قاعدة البيانات";
$l['Intro']['configmaint.php'] = "الصيانة";
$l['Intro']['configmaintdisconnectuser.php'] = "قطع اتصال مستخدم";
$l['Intro']['configbusiness.php'] = "تفاصيل العمل";
$l['Intro']['configbusinessinfo.php'] = "معلومات العمل";
$l['Intro']['configbackup.php'] = "نسخ احتياطي";
$l['Intro']['configbackupcreatebackups.php'] = "انشاء نسخ احتياطية";
$l['Intro']['configbackupmanagebackups.php'] = "إدارة نسخ احتياطية";

$l['Intro']['configoperators.php'] = "Operators Configuration";
$l['Intro']['configoperatorsdel.php'] = "Remove Operator";
$l['Intro']['configoperatorsedit.php'] = "Edit Operator Settings";
$l['Intro']['configoperatorsnew.php'] = "New Operator";
$l['Intro']['configoperatorslist.php'] = "Operators Listing";

$l['Intro']['login.php'] = "الدخول";

$l['captions']['providebillratetodel'] = "التسعير المراد حذفه";
$l['captions']['detailsofnewrate'] = "إملاء بيانات التسعير";
$l['captions']['filldetailsofnewrate'] = "Fill below the details for the new rate entry";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "إعدادات لوحة التحكم";


$l['helpPage']['repnewusers'] = "المستخدمين الجدد الذين تمت إضافتهم على مدار شهر";

$l['helpPage']['login'] = "الدخول";

$l['helpPage']['billpaypaltransactions'] = "List all PayPal transactions";
$l['helpPage']['billhistoryquery'] = "List all billing history for a user(s)";

$l['helpPage']['billinvoicereport'] = "";

$l['helpPage']['billinvoicelist'] = "قائمة الفواتير";
$l['helpPage']['billinvoicenew'] = "فاتورة جديدة";
$l['helpPage']['billinvoiceedit'] = "تعديل فاتورة";
$l['helpPage']['billinvoicedel'] = "حذف فاتورة";

$l['helpPage']['paymenttypeslist'] = "طرق الدفع";
$l['helpPage']['paymenttypesnew'] = "طريقة دفع جديدة";
$l['helpPage']['paymenttypesedit'] = "تعديل طرق الدفع";
$l['helpPage']['paymenttypesdel'] = "حذف طرق الدفع";
$l['helpPage']['paymenttypesdate'] = "تاريخ طرق الدفع";

$l['helpPage']['paymentslist'] = "عمليات الدفع";
$l['helpPage']['paymentsnew'] = "عملية دفع جديدة";
$l['helpPage']['paymentsedit'] = "تعديل عملية دفع";
$l['helpPage']['paymentsdel'] = "حذف عملية دفع";
$l['helpPage']['paymentsdate'] = "تاريخ عملية دفع";

$l['helpPage']['billplanslist'] = "قائمة الخطط";
$l['helpPage']['billplansnew'] = "خطة جديدة";
$l['helpPage']['billplansedit'] = "تعديل خطة";
$l['helpPage']['billplansdel'] = "حذف خطة";

$l['helpPage']['billposlist'] = "قائمة نقاط البيع";
$l['helpPage']['billposnew'] = "نقطة بيع جديدة";
$l['helpPage']['billposedit'] = "تعديل نقطة بيع";
$l['helpPage']['billposdel'] = "حذف نقطة بيع";

$l['helpPage']['billrateslist'] = "قائمة التسعير";
$l['helpPage']['billratesnew'] = "تسعير جديد";
$l['helpPage']['billratesedit'] = "تعديل تسعير";
$l['helpPage']['billratesdel'] = "حذف تسعير";
$l['helpPage']['billratesdate'] = "تاريخ تسعير";

$l['helpPage']['mngradproxys'] = "";
$l['helpPage']['mngradproxyslist'] = "";
$l['helpPage']['mngradproxysnew'] = "";
$l['helpPage']['mngradproxysedit'] = "";
$l['helpPage']['mngradproxysdel'] = "";

$l['helpPage']['mngradrealms'] = "";
$l['helpPage']['mngradrealmslist'] = "";
$l['helpPage']['mngradrealmsnew'] = "";
$l['helpPage']['mngradrealmsedit'] = "";
$l['helpPage']['mngradrealmsdel'] = "";

$l['helpPage']['mngradattributes'] = "إدارة السمات-الصفات";
$l['helpPage']['mngradattributeslist'] = "قائمة السمات-الصفات";
$l['helpPage']['mngradattributesnew'] = "سمة-صفة جديدة";
$l['helpPage']['mngradattributesedit'] = "تعديل سمة-صفة";
$l['helpPage']['mngradattributessearch'] = "البحث عن سمة-صفة";
$l['helpPage']['mngradattributesdel'] = "حذف سمة-صفة";
$l['helpPage']['mngradattributesimport'] = "إستيراد سمات مزود";
$l['helpPage']['mngimportusers'] = "استيراد مستخدمين";


$l['helpPage']['msgerrorpermissions'] = "ليس لديك الصلاحية لعرض هذه الصفحة";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "To remove a user entry from the database you must provide the username of the account";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "تعيين بروفايل للمستخدمين لتحديد السرعة والوقت وكمية الاستهلاك";
$l['helpPage']['mngradprofilesedit'] = "تعديل بروفايل";
$l['helpPage']['mngradprofilesdel'] = "حذف بروفايل";
$l['helpPage']['mngradprofilesduplicate'] = "نصخ بروفايل";
$l['helpPage']['mngradprofileslist'] = "قائمة البروفايلات";
$l['helpPage']['mngradprofilesnew'] = "بروفايل جديد";

$l['helpPage']['mngradgroups'] = "إدارة المجموعات - مجموعات الفحص ومجموعات الرد في البروفايل";


$l['helpPage']['mngradgroupchecknew'] = "تعيين مجموعة فحص جديدة";
$l['helpPage']['mngradgroupcheckdel'] = "حذف مجموعة فحص";

$l['helpPage']['mngradgroupchecklist'] = "قائمة مجموعات الفحص";
$l['helpPage']['mngradgroupcheckedit'] = "تعديل مجموعة فحص";
$l['helpPage']['mngradgroupchecksearch'] = "البحث عن مجموعة فحص";

$l['helpPage']['mngradgroupreplynew'] = "تعيين مجموعة رد جديدة";
$l['helpPage']['mngradgroupreplydel'] = "حذف مجموعة رد";
$l['helpPage']['mngradgroupreplylist'] = "قائمة مجموعة رد";
$l['helpPage']['mngradgroupreplyedit'] = "تعديل مجموعة رد";
$l['helpPage']['mngradgroupreplysearch'] = "البحث عن مجموعات الرد";


$l['helpPage']['mngradippool'] = "إدارة الأيبيهات";
$l['helpPage']['mngradippoollist'] = "قائمة نطاقات الأي بي";
$l['helpPage']['mngradippoolnew'] = "نطاق أي بي جديد";
$l['helpPage']['mngradippooledit'] = "تعديل نطاق أي بي";
$l['helpPage']['mngradippooldel'] = "حذف نطاق أي بي";


$l['helpPage']['mngradnas'] = "إدارة خادم الوصول للشبكة";
$l['helpPage']['mngradnasdel'] = "حذف خادم وصول للشبكة";
$l['helpPage']['mngradnasnew'] = "إضافة خادم وصول جديد";
$l['helpPage']['mngradnaslist'] = "قائمة الخوادم";
$l['helpPage']['mngradnasedit'] = "تعديل خادم";

$l['helpPage']['mngradhunt'] = "Before starting work with HuntGroup, please read <a href='http://wiki.freeradius.org/SQL_Huntgroup_HOWTO' target='_blank'>http://wiki.freeradius.org/SQL_Huntgroup_HOWTO</a>.
<br/>
In particular:
...
<i>Locate the authorize section in your radiusd.conf or sites-enabled/defaut configuration file and edit it. At the top of the authorize section after the preprocess module insert these lines:</i>
<br/>
<pre>
update request {
    Huntgroup-Name := \"%{sql:select groupname from radhuntgroup where nasipaddress=\\\"%{NAS-IP-Address}\\\"}\"
}
</pre>
<i> What this does is perform a lookup in the radhuntgroup table using the ip-address as a key to return the huntgroup name. It then adds an attribute/value pair to the request where the name of the attribute is Huntgroup-Name and it's value is whatever was returned from the SQL query. If the query did not find anything then the value is the empty string. </i>";


$l['helpPage']['mngradhuntdel'] = "To remove a huntgroup entry from the database you must provide the ip/host and port id of the huntgroup";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

$l['helpPage']['mnghsdel'] = "يجب كتابة اسم الهوت سبوت المراد حذفها";
$l['helpPage']['mnghsedit'] = "يمكنك تعجيل بيانات الهوت سبوت من هنا";
$l['helpPage']['mnghsnew'] = "إملاء بيانات الهوت سبوت المراد اضافتها";
$l['helpPage']['mnghslist'] = "عرض قائمة الهوت سبوت في قاعدة البيانات";

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
$l['helpPage']['configuser'] = "
<h200><b>User Settings</b></h200> - Configure user management behavior.<br/>
";
$l['helpPage']['configmail'] = "
<h200><b>User Settings</b></h200> - Configure mailing settings.<br/>
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
$l['helpPage']['configbusiness'] = "
<b>Business Information</b><br/>
<h200><b>Business Contact</b></h200> - set the business contact information (owners, title, address, phone, etc)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
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
$l['helpPage']['configoperatorsdel'] = "لحذف مدير من قواعد البيانات يجب كتابة اسم المستخدم الخاص به";
$l['helpPage']['configoperatorsedit'] = "تحرير المدير المكتوب اسم المسخدم الخاص به هنا";
$l['helpPage']['configoperatorsnew'] = "قم بكتابة بيانات المدير المطلوب إضافته لقواعد البيانات";
$l['helpPage']['configoperatorslist'] = "عرض جميع المديرين في قواعد البيانات";
$l['helpPage']['configoperators'] = "أعدادات المديرين";
$l['helpPage']['configbackup'] = "قم بإجراء نسخ احتياطي";
$l['helpPage']['configbackupcreatebackups'] = "إنشاء نسخ احتياطية";
$l['helpPage']['configbackupmanagebackups'] = "إدارة نسخ احتياطية";


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
<h200><b>Alltime Traffic Comparison</b></h200> - Plots a graphical chart of the Downloaded and Uploaded statisticse.</br>
<h200><b>Logged Users</b></h200> - Plots a graphical chart of the logged in users in the specified period.
Filter by day, month and year to graph a hourly graph or filter only by month and year (select \"---\" on the day field) to graph the minimum and maximum logged in users over the selected month.
";
$l['helpPage']['graphsalltimelogins'] = "An All-Time statistics of Logins to the server based on a distribution over a period of time";
$l['helpPage']['graphsalltimetrafficcompare'] = "An All-Time statistics of Traffic through the server based on a distribution over a period of time.";
$l['helpPage']['graphsloggedusers'] = "Plots a graphical chart of the total logged in users";
$l['helpPage']['graphsoveralldownload'] = "Plots a graphical chart of the Downloaded bytes to the server";
$l['helpPage']['graphsoverallupload'] = "Plots a graphical chart of the Uploaded bytes to the server";
$l['helpPage']['graphsoveralllogins'] = "Plots a graphical chart of the Login attempts to the server";



$l['helpPage']['rephistory'] = "Lists all activity performed on management items and provides information on <br/>
Creation Date, Creation By as well as Updated Date and Update By history fields";
$l['helpPage']['replastconnect'] = "Lists all login attempts to the RADIUS server, both successful and failed logins";
$l['helpPage']['replogsboot'] = "Monitor Operating System Boot log - equivalent to running the dmesg command.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS's Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS's Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Operating System Logfile.";
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "معلومات عن الحزم المنتجة من الكروت";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS Log</b></h200> - Monitor daloRADIUS's Logfile.<br/>
<h200><b>RADIUS Log</b></h200> - Monitor FreeRADIUS's Logfile - equivalent to /var/log/freeradius/radius.log or /usr/local/var/log/radius/radius.log.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>System Log</b></h200> - Monitor Operating System Logfile - equivalent to /var/log/syslog or /var/log/message on most platform.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>Boot Log</b></h200> - Monitor Operating System Boot log - equivalent to running the dmesg command.
";
$l['helpPage']['repmain'] = "صفحة التقارير - المستخدمين النشطين المتصلين حاليا - آخر عمليات محاولة الإتصال - قائمة المستخدمين الجدد والأعلى استخداما";
$l['helpPage']['repstatradius'] = "حالة الراديوس وقواعد البيانات";
$l['helpPage']['repstatserver'] = "حالة الخادم من زمن العمل والحمل على المعالج واستخدام الذاكرة والمساحة المتاحة للتخزين وعنوان الأي بي";
$l['helpPage']['repstatus'] = "حالة الخادم من زمن العمل والحمل على المعالج واستخدام الذاكرة والمساحة المتاحة للتخزين وعنوان الأي بي";
$l['helpPage']['reptopusers'] = "قائمة أعلى المستخدمين في الاستهلاك";
$l['helpPage']['repusername'] = "المستخدمين الذين تم العثور عليهم";
$l['helpPage']['reponline'] = "المستخدمين المتصلين حاليا";


$l['helpPage']['mnglistall'] = "عرض المستخدمين في قاعدة البيانات";
$l['helpPage']['mngsearch'] = "البحث عن مستخدم";
$l['helpPage']['mngnew'] = "إملاء ببيانات المستخدم المراد اضافته لقاعدة البيانات";
$l['helpPage']['mngedit'] = "تعديل بيانات المستخدم";
$l['helpPage']['mngdel'] = "لحذف مستخدم من قاعدة البيانات يجب كتابة اسم المستخدم أولا";
$l['helpPage']['mngbatch'] = "إملاء بيانات حزمة الكروت المراد إضافتها لقاعدة البيانات";
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

$l['helpPage']['acctplans'] = "";
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
$l['helpPage']['acctmaintenance'] = "
<h200><b>Cleanup stale-sessions</b></h200> -
    Stale-sesions may often exist because the NAS was unable to provide an accounting STOP record for the <br/>
    user session, resulting in a stale open session in the accounting records which simulates a fake logged-in user
    record (false positive).
<br/>
<h200><b>Delete accounting records</b></h200> -
    Deletion of accounting records in the database. It may not be wise to perform this or to allow other users
    except for a supervised administrator access to this page.
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "
<h200><b>Cleanup Stale Sessions</b></h200> - Cleanup Stale Sessions by username or date.<br/><br/>
    A stale session occurs when a user connection remains as active in FreeRADIUS (so, in daloRADIUS), but it does not exists in the NAS.
    This is normally caused by a lost disconnect message from the NAS to FreeRADIUS.</br></br>
    You have two choices to cleanup stale sessions, use them with caution:<br/>
    &nbsp;&bullet; By Username: This option will <b>CLOSE</b> all opened sessions for a username in the FreeRADIUS database.<br/>
    &nbsp;&bullet; By Date: This option will <b>DELETE</b> all opened sessions older than a date in the FreeRADIUS database.<br/>
";
$l['helpPage']['acctmaintenancedelete'] = "";



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


$l['messages']['missingratetype'] = "خطأ: لم يتم اختيار تسعير لحذفه";
$l['messages']['missingtype'] = "خطأ: لا يوجد نوع";
$l['messages']['missingcardbank'] = "error: missing cardbank";
$l['messages']['missingrate'] = "خطأ: لايوجد تسعير";
$l['messages']['success'] = "نجاح";
$l['messages']['gisedit1'] = "Welcome, you are currently in Edit mode";
$l['messages']['gisedit2'] = "Remove current marker from map and database?";
$l['messages']['gisedit3'] = "من فضلك أدخل اسم نقطة الوصول";
$l['messages']['gisedit4'] = "Add current marker to database?";
$l['messages']['gisedit5'] = "أدخل اسم الهوتسبوت";
$l['messages']['gisedit6'] = "أدخل ماك الهوتسبوت";

$l['messages']['gismain1'] = "Successfully updated GoogleMaps API Registration code";
$l['messages']['gismain2'] = "error: could not open the file for writing:";
$l['messages']['gismain3'] = "Check file permissions. The file should be writable by the webserver's user/group";
$l['messages']['gisviewwelcome'] = "مرحبا بكم في الخرائط المرئية";

$l['messages']['loginerror'] = "<br/><br/>أي مما يلي:<br/>
1. اسم مستخدم \ كلمة سر- خطأ<br/>
2. قام مدير بتسجيل الدخول - يسمح بمدير واحد فقط<br/>
3. يبدو أن هناك أكثر من مدير مسئول في قاعدة البيانات <br/>
";

$l['buttons']['savesettings'] = "حفظ الاعدادات";
$l['buttons']['apply'] = "تطبيق";

$l['menu']['Home'] = "البداية";
$l['menu']['Managment'] = "الإدارة";
$l['menu']['Reports'] = "التقارير";
$l['menu']['Accounting'] = "المحاسبة";
$l['menu']['Billing'] = "الفواتير";
$l['menu']['Graphs'] = "الرسوم البيانية";
$l['menu']['Config'] = "الإعدادات";
$l['menu']['Help'] = "المساعدة";

$l['submenu']['General'] = "عام";
$l['submenu']['Reporting'] = "التقارير";
$l['submenu']['Maintenance'] = "الصيانة";
$l['submenu']['Operators'] = "المديرين";
$l['submenu']['Backup'] = "النسخ الاحتياطي";
$l['submenu']['Logs'] = "السجلات";
$l['submenu']['Status'] = "الحالة";
$l['submenu']['Batch Users'] = "إنشاء كروت";
$l['submenu']['Dashboard'] = "لوحة التحكم";
$l['submenu']['Users'] = "المستخدمين";
$l['submenu']['Hotspots'] = "هوتسبوت";
$l['submenu']['Nas'] = "خادم الشبكة";
$l['submenu']['User-Groups'] = "مجموعات المستخدمين";
$l['submenu']['Profiles'] = "البروفايلات";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "السمات-الصفات";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "نقطة البيع";
$l['submenu']['Plans'] = "الخطط";
$l['submenu']['Rates'] = "الأسعار";
$l['submenu']['Merchant-Transactions'] = "تحويلات التاجر";
$l['submenu']['Billing-History'] = "تاريخ الفوترة";
$l['submenu']['Invoices'] = "الفواتير";
$l['submenu']['Payments'] = "المدفوعات";
$l['submenu']['Custom'] = "مخصص";
$l['submenu']['Hotspot'] = "هوتسبوت";
?>
