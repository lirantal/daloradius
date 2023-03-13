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
 * Description:    Turkish language file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Huseyin Yildirim <huseyinyildirim@hotmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/tr.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS yönetimi, raporlama, muhasebe ve faturalama <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a> tarafından geliştirilmiştir.';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';
$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Pool Name";
$l['all']['CalledStationId'] = "CalledStationId";
$l['all']['CallingStationID'] = "CallingStationID";
$l['all']['ExpiryTime'] = "Son Kullanma Tarihi";
$l['all']['PoolKey'] = "Pool Key";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Dictionary";
$l['all']['VendorID'] = "Satıcı No";
$l['all']['VendorName'] = "Satıcı Adı";
$l['all']['VendorAttribute'] = "Satıcı Özellikleri";
$l['all']['RecommendedOP'] = "Önerilen OP";
$l['all']['RecommendedTable'] = "Önerilen Tablo";
$l['all']['RecommendedTooltip'] = "Önerilen İpucu";
$l['all']['RecommendedHelper'] = "Önerilen Yardımcı";
/********************************************************************************/

$l['all']['CSVData'] = "CSV-formatted data";

$l['all']['CPU'] = "CPU";

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "RADIUS Dictionary Path";


$l['all']['DashboardSecretKey'] = "Kontrol Paneli Gizli Anahtarı";
$l['all']['DashboardDebug'] = "Debug";
$l['all']['DashboardDelaySoft'] = "Time in minutes to consider a 'soft' delay limit";
$l['all']['DashboardDelayHard'] = "Time in minutes to consider a 'hard' delay limit";
$l['all']['DashboardDebug'] = "Hata Ayıklama";
$l['all']['DashboardDelaySoft'] = "'soft' bir gecikme limitini dikkate almak için dakika cinsinden süre";
$l['all']['DashboardDelayHard'] = "'hard' bir gecikme limitini dikkate almak için dakika cinsinden süre";



$l['all']['SendWelcomeNotification'] = "Hoş Geldiniz Bildirimi Gönder";
$l['all']['SMTPServerAddress'] = "SMTP Sunucu Adresi";
$l['all']['SMTPServerPort'] = "SMTP Sunucu Bağlantı Noktası";
$l['all']['SMTPServerFromEmail'] = "E-posta Adresinden";

$l['all']['customAttributes'] = "Özel Nitelikler";

$l['all']['UserType'] = "Kullanıcı Tipi";

$l['all']['BatchName'] = "Toplu İş Adı";
$l['all']['BatchStatus'] = "Toplu İş Durumu";

$l['all']['Users'] = "Kullancılar";

$l['all']['Compare'] = "Karşılaştırma";
$l['all']['Never'] = "Asla";


$l['all']['Section'] = "Bölüm";
$l['all']['Item'] = "Öğe";

$l['all']['Megabytes'] = "Megabytes";
$l['all']['Gigabytes'] = "Gigabytes";

$l['all']['Daily'] = "Günlük";
$l['all']['Weekly'] = "Haftalık";
$l['all']['Monthly'] = "Aylık";
$l['all']['Yearly'] = "Yıllık";

$l['all']['Month'] = "Ay";

$l['all']['RemoveRadacctRecords'] = "Muhasebe Kayıtlarını Kaldır";

$l['all']['CleanupSessions'] = "Şundan daha eski temizleme oturumları";
$l['all']['DeleteSessions'] = "Şundan daha eski olan oturumları sil";

$l['all']['StartingDate'] = "Başlangıç Tarihi";
$l['all']['EndingDate'] = "Bitiş Tarihi";

$l['all']['Realm'] = "Realm";
$l['all']['RealmName'] = "Realm Name";
$l['all']['RealmSecret'] = "Realm Secret";
$l['all']['AuthHost'] = "Yetki Sunucusu";
$l['all']['AcctHost'] = "Muhasebe Sunucusu";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "ipuçları";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Proxy Name";
$l['all']['ProxySecret'] = "Proxy Secret";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Retry Delay";
$l['all']['RetryCount'] = "Retry Count";
$l['all']['DefaultFallback'] = "Default Fallback";


$l['all']['Firmware'] = "Firmware";
$l['all']['NASMAC'] = "NAS MAC";

$l['all']['WanIface'] = "Wan Iface";
$l['all']['WanMAC'] = "Wan MAC";
$l['all']['WanIP'] = "Wan IP";
$l['all']['WanGateway'] = "Wan Gateway";

$l['all']['LanIface'] = "Lan Iface";
$l['all']['LanMAC'] = "Lan MAC";
$l['all']['LanIP'] = "Lan IP";

$l['all']['WifiIface'] = "Wifi Iface";
$l['all']['WifiMAC'] = "Wifi MAC";
$l['all']['WifiIP'] = "Wifi IP";

$l['all']['WifiSSID'] = "Wifi SSID";
$l['all']['WifiKey'] = "Wifi Şifresi";
$l['all']['WifiChannel'] = "Wifi Kanalı";

$l['all']['CheckinTime'] = "Last Checked-In";

$l['all']['FramedIPAddress'] = "Framed-IP-Address";
$l['all']['SimultaneousUse'] = "Simultaneous-Use";
$l['all']['HgID'] = "HG ID";
$l['all']['Hg'] = "HG ";
$l['all']['HgIPHost'] = "HG IP/Host";
$l['all']['HgGroupName'] = "HG GroupName";
$l['all']['HgPortId'] = "HG Port Id";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "NAS Shortname";
$l['all']['NasType'] = "NAS Tipi";
$l['all']['NasPorts'] = "NAS Portları";
$l['all']['NasSecret'] = "NAS Gizli Anahtarı";
$l['all']['NasVirtualServer'] = "NAS Sanal Sunucu";
$l['all']['NasCommunity'] = "NAS Community";
$l['all']['NasDescription'] = "NAS Açıklama";
$l['all']['PacketType'] = "Paket Tipi";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['HotSpots'] = "HotSpots";
$l['all']['HotSpotName'] = "Hotspot Adı";
$l['all']['Name'] = "Ad";
$l['all']['Username'] = "Kullanıcı Adı";
$l['all']['Password'] = "Şifre";
$l['all']['PasswordType'] = "Şifre Tipi";
$l['all']['IPAddress'] = "IP Adres";
$l['all']['Profile'] = "Profil";
$l['all']['Group'] = "Grup";
$l['all']['Groupname'] = "Grup İsmi";
$l['all']['ProfilePriority'] = "Profil Önceliği";
$l['all']['GroupPriority'] = "Grup Önceliği";
$l['all']['CurrentGroupname'] = "Current Groupname";
$l['all']['NewGroupname'] = "Yeni grup Adı";
$l['all']['Priority'] = "Öncelik";
$l['all']['Attribute'] = "Özellik";
$l['all']['Operator'] = "Yönetici";
$l['all']['Value'] = "Değer";
$l['all']['NewValue'] = "Yeni Değer";
$l['all']['MaxTimeExpiration'] = "MMaksimum Süre / Sona Erme";
$l['all']['UsedTime'] = "Kullanılan Zaman";
$l['all']['Status'] = "Durum";
$l['all']['Usage'] = "Kullanım";
$l['all']['StartTime'] = "Başlangıç Saati";
$l['all']['StopTime'] = "Bitiş Saati";
$l['all']['TotalTime'] = "Toplam Zaman";
$l['all']['TotalTraffic'] = "Toplam Trafik";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Yükle";
$l['all']['Download'] = "İndir";
$l['all']['Rollback'] = "Geri Alma";
$l['all']['Termination'] = "Termination";
$l['all']['NASIPAddress'] = "NAS IP Adres";
$l['all']['NASShortName'] = "NAS Kısa Ad";
$l['all']['Action'] = "İşlem";
$l['all']['UniqueUsers'] = "Benzersiz Kullanıcılar";
$l['all']['TotalHits'] = "Toplam Hit";
$l['all']['AverageTime'] = "Ortalama Süre";
$l['all']['Records'] = "Kayıtlar";
$l['all']['Summary'] = "Özet";
$l['all']['Statistics'] = "İstatistikler";
$l['all']['Credit'] = "Kredi";
$l['all']['Used'] = "Kullanılmış";
$l['all']['LeftTime'] = "Time Remains";
$l['all']['LeftPercent'] = "% of Time Left";
$l['all']['TotalSessions'] = "Total Sessions";
$l['all']['LastLoginTime'] = "Son Giriş Zamanı";
$l['all']['TotalSessionTime'] = "Toplam Oturum Süresi";
$l['all']['RateName'] = "Maliyet Adı";
$l['all']['RateType'] = "Maliyet Tipi";
$l['all']['RateCost'] = "Maliyet Ücreti";
$l['all']['Billed'] = "Faturalandırılan";
$l['all']['TotalUsers'] = "Toplam Kullanıcılar";
$l['all']['ActiveUsers'] = "Aktif Kullanıcılar";
$l['all']['TotalBilled'] = "Toplam Faturalanan";
$l['all']['TotalPayed'] = "Toplam Ödenen";
$l['all']['Balance'] = "Bakiye";
$l['all']['CardBank'] = "Kart Bankası";
$l['all']['Type'] = "Tip";
$l['all']['CardBank'] = "Kart Bankası";
$l['all']['MACAddress'] = "MAC Adresi";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "PIN Code";
$l['all']['CreationDate'] = "Eklenme Tarihi";
$l['all']['CreationBy'] = "Ekleyen";
$l['all']['UpdateDate'] = "Güncelleme Tarihi";
$l['all']['UpdateBy'] = "Güncelleyen";

$l['all']['Discount'] = "İndirim";
$l['all']['BillAmount'] = "Faturalanan Tutar";
$l['all']['BillAction'] = "Faturalandırılmış İşlem";
$l['all']['BillPerformer'] = "Faturayı Kesen";
$l['all']['BillReason'] = "Faturalandırma Nedeni";
$l['all']['Lead'] = "Lead";
$l['all']['Coupon'] = "Kupon";
$l['all']['OrderTaker'] = "Order Taker";
$l['all']['BillStatus'] = "Fatura Durumu";
$l['all']['LastBill'] = "Önceki Fatura";
$l['all']['NextBill'] = "Sonraki Fatura";
$l['all']['BillDue'] = "Fatura Ödenmesi";
$l['all']['NextInvoiceDue'] = "Sonraki Fatura Vadesi";
$l['all']['PostalInvoice'] = "Faturayı Postala";
$l['all']['FaxInvoice'] = "Faturayı Faksla";
$l['all']['EmailInvoice'] = "Faturayı E-postala";

$l['all']['ClientName'] = "Müşteri Adı";
$l['all']['Date'] = "Tarih";

$l['all']['edit'] = "düzenle";
$l['all']['del'] = "sil";
$l['all']['groupslist'] = "groups-list";
$l['all']['TestUser'] = "Kullanıcıyı Test Et";
$l['all']['Accounting'] = "Muhasebe";
$l['all']['RADIUSReply'] = "RADIUS Reply";

$l['all']['Disconnect'] = "Disconnect";

$l['all']['Debug'] = "Debug";
$l['all']['Timeout'] = "Timeout";
$l['all']['Retries'] = "Retries";
$l['all']['Count'] = "Count";
$l['all']['Requests'] = "İstekler";

$l['all']['DatabaseHostname'] = "Veritabanı Adresi";
$l['all']['DatabasePort'] = "Veritabanı Port";
$l['all']['DatabaseUser'] = "Veritabanı Kullanıcı Adı";
$l['all']['DatabasePass'] = "Veritabanı Şifresi";
$l['all']['DatabaseName'] = "Veritabanı Adı";

$l['all']['PrimaryLanguage'] = "Birincil Dil";

$l['all']['PagesLogging'] = "Sayfaların Günlüğü (sayfa ziyaretleri)";
$l['all']['QueriesLogging'] = "Sorguların Günlüğü (raporlar ve grafikler)";
$l['all']['ActionsLogging'] = "İşlem Günlüğü (form gönderimleri)";
$l['all']['FilenameLogging'] = "Dosya adını günlüğe kaydetme (tam yol)";
$l['all']['LoggingDebugOnPages'] = "Sayfalardaki Hata Ayıklama bilgilerinin günlüğe kaydedilmesi";
$l['all']['LoggingDebugInfo'] = "Hata Ayıklama Bilgilerinin Günlüğü";

$l['all']['PasswordHidden'] = "Enable Password Hiding (asterisk will be shown)";
$l['all']['TablesListing'] = "Rows/Records per Tables Listing page";
$l['all']['TablesListingNum'] = "Enable Tables Listing Numbering";
$l['all']['AjaxAutoComplete'] = "Enable Ajax Auto-Complete";

$l['all']['RadiusServer'] = "Radius Server";
$l['all']['RadiusPort'] = "Radius Port";

$l['all']['UsernamePrefix'] = "Kullanıcı Adı Öneki";

$l['all']['batchName'] = "Toplu İş No/Ad";
$l['all']['batchDescription'] = "Toplu İş Açıklama Description";

$l['all']['NumberInstances'] = "Oluşturulacak örnek sayısı";
$l['all']['UsernameLength'] = "Kullanıcı adı dizisinin uzunluğu";
$l['all']['PasswordLength'] = "Parola dizesinin uzunluğu";

$l['all']['Expiration'] = "Sona Erme";
$l['all']['MaxAllSession'] = "Max-All-Session";
$l['all']['SessionTimeout'] = "Oturum Zaman Aşımı";
$l['all']['IdleTimeout'] = "Boşta Kalma Zaman Aşımı";

$l['all']['DBEngine'] = "DB Engine";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "usergroup";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "operators";
$l['all']['operators_acl'] = "operators_acl";
$l['all']['operators_acl_files'] = "operators_acl_files";
$l['all']['billingrates'] = "billing rates";
$l['all']['hotspots'] = "hotspots";
$l['all']['node'] = "node";
$l['all']['nas'] = "nas";
$l['all']['hunt'] = "radhuntgroup";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "radippool";
$l['all']['userinfo'] = "userinfo";
$l['all']['dictionary'] = "dictionary";
$l['all']['realms'] = "realms";
$l['all']['proxys'] = "proxys";
$l['all']['billingpaypal'] = "billing paypal";
$l['all']['billingmerchant'] = "billing merchant";
$l['all']['billingplans'] = "billing plans";
$l['all']['billinghistory'] = "billing history";
$l['all']['billinginfo'] = "billing user info";

$l['all']['CreateIncrementingUsers'] = "Artan Kullanıcılar Oluştur";
$l['all']['CreateRandomUsers'] = "Rastgele Kullanıcılar Oluştur";
$l['all']['StartingIndex'] = "Başlangıç Dizini";
$l['all']['EndingIndex'] = "Bitiş Dizini";
$l['all']['RandomChars'] = "İzin Verilen Rastgele Karakterler";
$l['all']['Memfree'] = "Memory Free";
$l['all']['Çalışma Süresi'] = "Çalışma Süresi";
$l['all']['BandwidthUp'] = "Bandwidth Up";
$l['all']['BandwidthDown'] = "Bandwidth Down";

$l['all']['BatchCost'] = "Toplu Maliyet";

$l['all']['PaymentDate'] = "Ödeme Tarihi";
$l['all']['PaymentStatus'] = "Ödeme Durumu";
$l['all']['FirstName'] = "Ad";
$l['all']['LastName'] = "Soyadı";
$l['all']['VendorType'] = "Satıcı";
$l['all']['PayerStatus'] = "Ödeyen Durumu";
$l['all']['PaymentAddressStatus'] = "Ödeme Adresi Durumu";
$l['all']['PayerEmail'] = "Ödeyen E-posta";
$l['all']['TxnId'] = "İşlem No";
$l['all']['PlanActive'] = "Plan Aktif";
$l['all']['PlanTimeType'] = "Zaman Türünü Planla";
$l['all']['PlanTimeBank'] = "Zaman Bankası Planla";
$l['all']['PlanTimeRefillCost'] = "Süre Yenileme Planla";
$l['all']['PlanTrafficRefillCost'] = "Trafik Yenileme Maliyeti";
$l['all']['PlanBandwidthUp'] = "Yükleme Bant Genişliğini Planla";
$l['all']['PlanBandwidthDown'] = "İndirme Bant Genişliğini Planla";
$l['all']['PlanTrafficTotal'] = "Trafik Toplamını Planla";
$l['all']['PlanTrafficDown'] = "Trafik Azaltmayı Planla";
$l['all']['PlanTrafficUp'] = "Trafik Arttırmayı Planla";
$l['all']['PlanRecurring'] = "Yinelenen Plan";
$l['all']['PlanRecurringPeriod'] = "Yinelenen Dönemi Planla";
$l['all']['planRecurringBillingSchedule'] = "Yinelenen Faturalama Planı Zamanla";
$l['all']['PlanCost'] = "Plan Maliyeti";
$l['all']['PlanSetupCost'] = "Plan Kurulum Maliyeti";
$l['all']['PlanTax'] = "Plan Vergisi";
$l['all']['PlanCurrency'] = "Plan Para Birimi";
$l['all']['PlanGroup'] = "Plan Profil (Grup)";
$l['all']['PlanType'] = "Plan Türü";
$l['all']['PlanName'] = "Plan Adı";
$l['all']['PlanId'] = "Plan Id";

$l['all']['UserId'] = "Kullanıcı Id";

$l['all']['Invoice'] = "Fatura";
$l['all']['InvoiceID'] = "Fatura No";
$l['all']['InvoiceItems'] = "Fatura Kalemleri";
$l['all']['InvoiceStatus'] = "Fatura Durumu";

$l['all']['InvoiceType'] = "Fatura Türü";
$l['all']['Amount'] = "Miktar";
$l['all']['Total'] = "Toplam";
$l['all']['TotalInvoices'] = "Toplam Fatura Sayısı";

$l['all']['PayTypeName'] = "Ödeme Türü Adı";
$l['all']['PayTypeNotes'] = "Ödeme Türü Açıklaması";
$l['all']['payment_type'] = "ödeme türleri";
$l['all']['payments'] = "ödemeler";
$l['all']['PaymentId'] = "Ödeme Kimliği";
$l['all']['PaymentInvoiceID'] = "Fatura No";
$l['all']['PaymentAmount'] = "Miktar";
$l['all']['PaymentDate'] = "Tarih";
$l['all']['PaymentType'] = "Ödeme Türü";
$l['all']['PaymentNotes'] = "Ödeme Notları";

$l['all']['Quantity'] = "Miktar";
$l['all']['ReceiverEmail'] = "Alıcı E-postası";
$l['all']['Business'] = "İş";
$l['all']['Tax'] = "Vergi";
$l['all']['MaCostliyet'] = "Maliyet";
$l['all']['TotalCost'] = "Toplam Maliyet";
$l['all']['TransactionFee'] = "İşlem Ücreti";
$l['all']['PaymentCurrency'] = "Ödeme Para Birimi";
$l['all']['AddressRecipient'] = "Adres Alıcısı";
$l['all']['Street'] = "Sokak";
$l['all']['Country'] = "Ülke";
$l['all']['CountryCode'] = "Ülke Kodu";
$l['all']['City'] = "Şehir";
$l['all']['State'] = "Devlet";
$l['all']['Zip'] = "Posta Kodu";

$l['all']['BusinessName'] = "İşletme Adı";
$l['all']['BusinessPhone'] = "İş Telefonu";
$l['all']['BusinessAddress'] = "İş Adresi";
$l['all']['BusinessWebsite'] = "İş Web Sitesi";
$l['all']['BusinessEmail'] = "İş E-postası";
$l['all']['BusinessContactPerson'] = "İş İlgili Kişisi";
$l['all']['DBPasswordEncryption'] = "DB Parola Şifreleme Türü";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "Bu toplu oluşturma için bir tanımlayıcı adı sağlayın";
$l['Tooltip']['batchDescriptionTooltip'] = "Bu toplu oluşturmayla ilgili genel açıklama sağlayın";

$l['Tooltip']['hotspotTooltip'] = "Bu toplu iş örneğinin ilişkilendirildiği etkin nokta adını seçin";

$l['Tooltip']['startingIndexTooltip'] = "Kullanıcının oluşturulacağı başlangıç dizinini sağlayın";
$l['Tooltip']['planTooltip'] = "Kullanıcıyı ilişkilendirmek için bir plan seçin";

$l['Tooltip']['InvoiceEdit'] = "Faturayı Düzenle";
$l['Tooltip']['invoiceTypeTooltip'] = "";
$l['Tooltip']['invoiceStatusTooltip'] = "";
$l['Tooltip']['invoiceID'] = "Fatura no yazın";
$l['Tooltip']['user_idTooltip'] = "Kullanıcı No";

$l['Tooltip']['amountTooltip'] = "";
$l['Tooltip']['taxTooltip'] = "";

$l['Tooltip']['PayTypeName'] = "Ödeme Türü adını yazın";
$l['Tooltip']['EditPayType'] = "Ödeme Türünü Düzenle";
$l['Tooltip']['RemovePayType'] = "Ödeme Türünü Kaldır";
$l['Tooltip']['paymentTypeTooltip'] = "Ödemenin amacını açıklayan ödeme türü kolay adı";
$l['Tooltip']['paymentTypeNotesTooltip'] = "Ödeme türünün işleyişini açıklamak için ödeme türü açıklaması";
$l['Tooltip']['EditPayment'] = "Ödemeyi Düzenle";
$l['Tooltip']['PaymentId'] = "Ödeme No";
$l['Tooltip']['RemovePayment'] = "Ödemeyi Kaldır";
$l['Tooltip']['paymentInvoiceTooltip'] = "Bu ödemeyle ilgili fatura";


$l['Tooltip']['Username'] = "Kullanıcı Adını Yazın";
$l['Tooltip']['BatchName'] = "Parti adını yazın";
$l['Tooltip']['UsernameWildcard'] = "İpucu: joker karakter belirtmek için * veya % karakterini kullanabilirsiniz";
$l['Tooltip']['HotspotName'] = "Hotspot adını yazın";
$l['Tooltip']['NasName'] = "NAS adını yazın";
$l['Tooltip']['GroupName'] = "Grup adını yazın";
$l['Tooltip']['AttributeName'] = "Özellik adını yazın";
$l['Tooltip']['VendorName'] = "Satıcı adını yazın";
$l['Tooltip']['PoolName'] = "Havuz adını yazın";
$l['Tooltip']['IPAddress'] = "IP adresini yazın";
$l['Tooltip']['Filter'] = "Bir filtre yazın, herhangi bir alfa sayısal dize olabilir. Herhangi bir şeyle eşleşmek için boş bırakın.";
$l['Tooltip']['Date'] = "Tarihi yazın <br/> örneği: 1982-06-04 (E-M-D)";
$l['Tooltip']['RateName'] = "Fiyat adını yazın";
$l['Tooltip']['OperatorName'] = "Operatör adını yazın";
$l['Tooltip']['BillingPlanName'] = "Faturalandırma Planı adını yazın";
$l['Tooltip']['PlanName'] = "Plan adını yazın";

$l['Tooltip']['EditRate'] = "Edit Rate";
$l['Tooltip']['RemoveRate'] = "Remove Rate";

$l['Tooltip']['rateNameTooltip'] = "Fiyatın amacını açıklamak için oran kısa ad";
$l['Tooltip']['rateTypeTooltip'] = "Fiyatın işleyişini açıklamak için oran tipi";
$l['Tooltip']['rateCostTooltip'] = "Ücret maliyeti tutarı";
$l['Tooltip']['planNameTooltip'] = "Planın adı. Bu, planın özelliklerini anlatan kolay bir addır";

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

$l['Tooltip']['EditIPPool'] = "IP-Pool Düzenle";
$l['Tooltip']['RemoveIPPool'] = "IP-Pool Sil";
$l['Tooltip']['EditIPAddress'] = "IP Adres Düzenle";
$l['Tooltip']['RemoveIPAddress'] = "IP Adres Sil";

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



$l['Tooltip']['AttributeEdit'] = "Edit Attribute";

$l['Tooltip']['BatchDetails'] = "Batch Details";

$l['Tooltip']['UserEdit'] = "Kullanıcıyı Düzenle";
$l['Tooltip']['HotspotEdit'] = "Hotspot Düzenle";
$l['Tooltip']['EditNAS'] = "NAS Düzenle";
$l['Tooltip']['RemoveNAS'] = "NAS Kaldır";
$l['Tooltip']['EditHG'] = "HuntGroup Düzenle";
$l['Tooltip']['RemoveHG'] = "HuntGroup Kaldır";
$l['Tooltip']['hgNasIpAddress'] = "Host/Ip adresini yazın";
$l['Tooltip']['hgGroupName'] = "NAS için Grup Adını yazın";
$l['Tooltip']['hgNasPortId'] = "Nas portunu yazın";
$l['Tooltip']['EditUserGroup'] = "Kullanıcı Grubunu Düzenle";
$l['Tooltip']['ListUserGroups'] = "Kullanıcı Gruplarını Listele";
$l['Tooltip']['DeleteUserGroup'] = "Kullanıcı Grubu İlişkisini Sil";

$l['Tooltip']['EditProfile'] = "Profili Düzenle";

$l['Tooltip']['EditRealm'] = "Alamı Düzenle";
$l['Tooltip']['EditProxy'] = "Proxy Düzenle";

$l['Tooltip']['EditGroup'] = "Grubu Düzenle";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(descriptive name)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!";


$l['Tooltip']['usernameTooltip'] = "The exact username as the user<br/>&nbsp;&nbsp;&nbsp;
                    will use to connect to the system";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "Passwords are case sensetive in<br/>&nbsp;&nbsp;&nbsp;
                    certain systems so take extra care";
$l['Tooltip']['groupTooltip'] = "The user will be added to this group.<br/>&nbsp;&nbsp;&nbsp;
                    By assigning a user to a particular group<br/>&nbsp;&nbsp;&nbsp;
                    the user is subject to the group's attributes";
$l['Tooltip']['macaddressTooltip'] = "Example: 00:AA:BB:CC:DD:EE<br/>&nbsp;&nbsp;&nbsp;
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

$l['Tooltip']['hotspotMacaddressTooltip'] = "Example: 00-aa-bb-cc-dd-ee<br/>&nbsp;&nbsp;&nbsp;
                    The MAC address of the NAS<br/>";

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

$l['button']['DashboardSettings'] = "Dashboard Settings";


$l['button']['GenerateReport'] = "Generate Report";

$l['button']['ListPayTypes'] = "Ödeme Türlerini Listele";
$l['button']['NewPayType'] = "Yeni Ödeme Türü";
$l['button']['EditPayType'] = "Ödeme Türünü Düzenle";
$l['button']['RemovePayType'] = "Ödeme Türünü Kaldır";
$l['button']['ListPayments'] = "Ödemeleri Listele";
$l['button']['NewPayment'] = "Yeni Ödeme";
$l['button']['EditPayment'] = "Ödemeyi Düzenle";
$l['button']['RemovePayment'] = "Ödemeyi Kaldır";

$l['button']['NewUsers'] = "New Users";

$l['button']['ClearSessions'] = "Oturumları Temizle";
$l['button']['Dashboard'] = "Kontrol Paneli";
$l['button']['MailSettings'] = "E-posta Ayarları";

$l['button']['Batch'] = "Batch";
$l['button']['BatchHistory'] = "Batch History";
$l['button']['BatchDetails'] = "Batch Details";

$l['button']['ListRates'] = "Ücret Listesi";
$l['button']['NewRate'] = "Yeni Ücret";
$l['button']['EditRate'] = "Ücreti Düzenle";
$l['button']['RemoveRate'] = "Ücreti Temizle";

$l['button']['ListPlans'] = "Liste Planları";
$l['button']['NewPlan'] = "Yeni Plan";
$l['button']['EditPlan'] = "Planı Düzenle";
$l['button']['RemovePlan'] = "Planı Kaldır";

$l['button']['ListInvoices'] = "Faturaları Listele";
$l['button']['NewInvoice'] = "Yeni Fatura";
$l['button']['EditInvoice'] = "Faturayı Düzenle";
$l['button']['RemoveInvoice'] = "Faturayı Kaldır";

$l['button']['ListRealms'] = "List Realms";
$l['button']['NewRealm'] = "New Realm";
$l['button']['EditRealm'] = "Edit Realm";
$l['button']['RemoveRealm'] = "Remove Realm";

$l['button']['ListProxys'] = "List Proxys";
$l['button']['NewProxy'] = "New Proxy";
$l['button']['EditProxy'] = "Edit Proxy";
$l['button']['RemoveProxy'] = "Remove Proxy";

$l['button']['ListAttributesforVendor'] = "List Attributes for Vendor:";
$l['button']['NewVendorAttribute'] = "New Vendor Attribute";
$l['button']['EditVendorAttribute'] = "Edit Vendor's Attribute";
$l['button']['SearchVendorAttribute'] = "Search Attribute";
$l['button']['RemoveVendorAttribute'] = "Remove Vendor's Attribute";
$l['button']['ImportVendorDictionary'] = "Import Vendor Dictionary";


$l['button']['BetweenDates'] = "Between Dates:";
$l['button']['Where'] = "Where";
$l['button']['AccountingFieldsinQuery'] = "Accounting Fields in Query:";
$l['button']['OrderBy'] = "Order By";
$l['button']['HotspotAccounting'] = "Hotspot Accounting";
$l['button']['HotspotsComparison'] = "Hotspots Comparison";

$l['button']['CleanupStaleSessions'] = "Cleanup Stale Sessions";
$l['button']['DeleteAccountingRecords'] = "Delete Accounting Records";

$l['button']['ListUsers'] = "Kullanıcı Listesi";
$l['button']['ListBatches'] = "List Batches";
$l['button']['RemoveBatch'] = "Remove Batch";
$l['button']['ImportUsers'] = "Import Users";
$l['button']['NewUser'] = "Yeni Kullanıcı";
$l['button']['NewUserQuick'] = "Yeni Kullanıcı - Hızlı Ekleme";
$l['button']['BatchAddUsers'] = "Batch Add Users";
$l['button']['EditUser'] = "Kullanıcı Düzenle";
$l['button']['SearchUsers'] = "Kullanıcı Ara";
$l['button']['RemoveUsers'] = "Kullanıcı Sil";
$l['button']['ListHotspots'] = "List Hotspots";
$l['button']['NewHotspot'] = "Yeni Hotspot";
$l['button']['EditHotspot'] = "Hotspot Düzenle";
$l['button']['RemoveHotspot'] = "Hotspot Sil";

$l['button']['ListIPPools'] = "List IP-Pools";
$l['button']['NewIPPool'] = "New IP-Pool";
$l['button']['EditIPPool'] = "Edit IP-Pool";
$l['button']['RemoveIPPool'] = "Remove IP-Pool";

$l['button']['ListNAS'] = "NAS Listesi";
$l['button']['NewNAS'] = "Yeni NAS";
$l['button']['EditNAS'] = "NAS Düzenle";
$l['button']['RemoveNAS'] = "NAS Sil";
$l['button']['ListHG'] = "List HuntGroup";
$l['button']['NewHG'] = "New HuntGroup";
$l['button']['EditHG'] = "Edit HuntGroup";
$l['button']['RemoveHG'] = "Remove HuntGroup";
$l['button']['ListUserGroup'] = "List User-Group Mappings";
$l['button']['ListUsersGroup'] = "List  User's Group Mappings";
$l['button']['NewUserGroup'] = "New User-Group Mappings";
$l['button']['EditUserGroup'] = "Edit User-Group Mappings";
$l['button']['RemoveUserGroup'] = "Remove User-Group Mappings";

$l['button']['ListProfiles'] = "Profilleri Listele";
$l['button']['NewProfile'] = "Yeni Profil";
$l['button']['EditProfile'] = "Profili Düzenle";
$l['button']['DuplicateProfile'] = "Yinelenen Profil";
$l['button']['RemoveProfile'] = "Profili Kaldır";

$l['button']['ListGroupReply'] = "Grup Yanıt Eşlemelerini Listele";
$l['button']['SearchGroupReply'] = "Grup Yanıtını Ara";
$l['button']['NewGroupReply'] = "Yeni Grup Yanıt Eşlemesi";
$l['button']['EditGroupReply'] = "Grup Yanıt Eşlemesini Düzenle";
$l['button']['RemoveGroupReply'] = "Grup Yanıt Eşlemesini Kaldır";

$l['button']['ListGroupCheck'] = "Grup Kontrol Eşlemelerini Listele";
$l['button']['SearchGroupCheck'] = "Arama Grubu Kontrolü";
$l['button']['NewGroupCheck'] = "Yeni Grup Kontrol Eşlemesi";
$l['button']['EditGroupCheck'] = "Grup Kontrol Eşlemesini Düzenle";
$l['button']['RemoveGroupCheck'] = "Grup Kontrolü Eşlemesini Kaldır";

$l['button']['UserAccounting'] = "User Accounting";
$l['button']['IPAccounting'] = "IP Accounting";
$l['button']['NASIPAccounting'] = "NAS IP Accounting";
$l['button']['NASIPAccountingOnlyActive'] = "Yalnızca aktifleri göster";
$l['button']['DateAccounting'] = "Date Accounting";
$l['button']['AllRecords'] = "Tüm Kayıtlar";
$l['button']['ActiveRecords'] = "Aktif Kayıtlar";

$l['button']['PlanUsage'] = "Plan Usage";

$l['button']['OnlineUsers'] = "Çevrimiçi Kullanıcılar";
$l['button']['LastConnectionAttempts'] = "Son Bağlantı Denemeleri";
$l['button']['TopUser'] = "En İyi Kullanıcı";
$l['button']['Geçmiş'] = "Geçmiş";

$l['button']['ServerStatus'] = "Sunucu Durumu";
$l['button']['ServicesStatus'] = "Servis Durumu";

$l['button']['daloRADIUSLog'] = "daloRADIUS Log";
$l['button']['RadiusLog'] = "Radius Log";
$l['button']['SystemLog'] = "System Log";
$l['button']['BootLog'] = "Boot Log";

$l['button']['UserLogins'] = "Kullanıcı Girişleri";
$l['button']['UserDownloads'] = "Kullanıcı İndirmeleri";
$l['button']['UserUploads'] = "Kullanıcı Yüklemeleri";
$l['button']['TotalLogins'] = "Toplam Giriş Sayısı";
$l['button']['TotalTraffic'] = "Toplam Trafik";
$l['button']['LoggedUsers'] = "Kayıtlı Kullanıcılar";

$l['button']['ViewMAP'] = "View MAP";
$l['button']['EditMAP'] = "Edit MAP";
$l['button']['RegisterGoogleMapsAPI'] = "RegisterGoogleMaps API";

$l['button']['UserSettings'] = "Kullanıcı Ayarları";
$l['button']['DatabaseSettings'] = "Veritabanı Ayarları";
$l['button']['LanguageSettings'] = "Dil Ayarları";
$l['button']['LoggingSettings'] = "Günlük Ayarları";
$l['button']['InterfaceSettings'] = "Arayüz Ayarları";

$l['button']['ReAssignPlanProfiles'] = "Re-Assign Plan Profiles";

$l['button']['TestUserConnectivity'] = "Kullanıcı Bağlantısını Test Et";
$l['button']['DisconnectUser'] = "Kullanıcı Bağlantısını Kes";

$l['button']['ManageBackups'] = "Yedeklemeleri Yönet";
$l['button']['CreateBackups'] = "Yedekleme Oluştur";

$l['button']['ListOperators'] = "Yöneticileri Listele";
$l['button']['NewOperator'] = "Yeni Yönetici";
$l['button']['EditOperator'] = "Yönetici Düzenle";
$l['button']['RemoveOperator'] = "Yönetici Kaldır";

$l['button']['ProcessQuery'] = "İşlem Sorgusu";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['ImportUsers'] = "Kullanıcıları İçe Aktar";


$l['title']['Dashboard'] = "Kontrol Paneli";
$l['title']['DashboardAlerts'] = "Alarmlar";

$l['title']['Fatura'] = "Fatura";
$l['title']['Faturalar'] = "Faturalar";
$l['title']['InvoiceRemoval'] = "Fatura Kaldırma";
$l['title']['Ödemeler'] = "Ödemeler";
$l['title']['Items'] = "Öğeler";

$l['title']['PayTypeInfo'] = "Ödeme Türü Bilgileri";
$l['title']['PaymentInfo'] = "Ödeme Bilgileri";


$l['title']['RateInfo'] = "Oran Bilgileri";
$l['title']['PlanInfo'] = "Ücret Bilgileri";
$l['title']['TimeSettings'] = "Zaman Ayarları";
$l['title']['BandwidthSettings'] = "Bant Genişliği Ayarları";
$l['title']['PlanRemoval'] = "Plan Kaldırma";

$l['title']['BatchRemoval'] = "Batch Removal";

$l['title']['Backups'] = "Yedekler";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS Tabloları";
$l['title']['daloRADIUSTables'] = "daloRADIUS Tabloları";

$l['title']['IPPoolInfo'] = "IP-Pool Info";

$l['title']['BusinessInfo'] = "Business Info";

$l['title']['CleanupRecordsByUsername'] = "By Username";
$l['title']['CleanupRecordsByDate'] = "By Date";
$l['title']['DeleteRecords'] = "Delete Records";

$l['title']['RealmInfo'] = "Realm Info";

$l['title']['ProxyInfo'] = "Proxy Info";

$l['title']['VendorAttribute'] = "Vendor Attribute";

$l['title']['AccountRemoval'] = "Account Removal";
$l['title']['AccountInfo'] = "Account Info";

$l['title']['Profiles'] = "Profiles";
$l['title']['ProfileInfo'] = "Profile Info";

$l['title']['GroupInfo'] = "Group Info";
$l['title']['GroupAttributes'] = "Group Attributes";

$l['title']['NASInfo'] = "NAS Info";
$l['title']['NASAdvanced'] = "NAS Advanced";
$l['title']['HGInfo'] = "HG Info";
$l['title']['UserInfo'] = "User Info";
$l['title']['BillingInfo'] = "Billing Info";

$l['title']['Attributes'] = "Attributes";
$l['title']['ProfileAttributes'] = "Profile Attributes";

$l['title']['HotspotInfo'] = "Hotspot Info";
$l['title']['HotspotRemoval'] = "Hotspot Removal";

$l['title']['ContactInfo'] = "Contact Info";

$l['title']['Plan'] = "Plan";

$l['title']['Profile'] = "Profil";
$l['title']['Gruplar'] = "Gruplar";
$l['title']['RADIUSCheck'] = "Özellikleri Kontrol Et";
$l['title']['RADIUSReply'] = "Yanıt Özellikleri";

$l['title']['Ayarlar'] = "Ayarlar";
$l['title']['DatabaseSettings'] = "Veritabanı Ayarları";
$l['title']['DatabaseTables'] = "Veritabanı Tabloları";
$l['title']['AdvancedSettings'] = "Gelişmiş Ayarlar";

$l['title']['Gelişmiş'] = "Gelişmiş";
$l['title']['Opsiyonel'] = "Opsiyonel";

/* ********************************************************************************** */

/* **********************************************************************************
 * Graphs
 * General graphing text
 ************************************************************************************/
$l['graphs']['Day'] = "Gün";
$l['graphs']['Month'] = "Ay";
$l['graphs']['Year'] = "Yıl";
$l['graphs']['Jan'] = "Ocak";
$l['graphs']['Feb'] = "Şubat";
$l['graphs']['Mar'] = "Mart";
$l['graphs']['Apr'] = "Nisan";
$l['graphs']['May'] = "Mayıs";
$l['graphs']['Jun'] = "Haziran";
$l['graphs']['Jul'] = "Temmuz";
$l['graphs']['Aug'] = "Ağustos";
$l['graphs']['Sep'] = "Eylül";
$l['graphs']['Oct'] = "Ekim";
$l['graphs']['Nov'] = "Kasım";
$l['graphs']['Dec'] = "Aralık";


/* ********************************************************************************** */

/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Giriş Gerekli";
$l['text']['LoginPlease'] = "Lütfen Giriş Yapın";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "Ad";
$l['ContactInfo']['LastName'] = "Soyadı";
$l['ContactInfo']['Email'] = "E-posta";
$l['ContactInfo']['Department'] = "Departman";
$l['ContactInfo']['WorkPhone'] = "İş Telefonu";
$l['ContactInfo']['HomePhone'] = "Ev Telefonu";
$l['ContactInfo']['Phone'] = "Telefon";
$l['ContactInfo']['MobilePhone'] = "Cep Telefonu";
$l['ContactInfo']['Notes'] = "Notlar";
$l['ContactInfo']['EnableUserUpdate'] = "Kullanıcı Güncellemesini Etkinleştir";
$l['ContactInfo']['EnablePortalLogin'] = "Kullanıcı Portalı Oturum Açmayı Etkinleştir";
$l['ContactInfo']['PortalLoginPassword'] = "Kullanıcı Portalı Giriş Parolası";

$l['ContactInfo']['OwnerName'] = "Sahip Adı";
$l['ContactInfo']['OwnerEmail'] = "Sahip E-postası";
$l['ContactInfo']['ManagerName'] = "Yönetici Adı";
$l['ContactInfo']['ManagerEmail'] = "Yönetici E-postası";
$l['ContactInfo']['Company'] = "Şirket";
$l['ContactInfo']['Address'] = "Adres";
$l['ContactInfo']['City'] = "İl";
$l['ContactInfo']['State'] = "İlçe";
$l['ContactInfo']['Country'] = "Ülke";
$l['ContactInfo']['Zip'] = "Posta Kodu";
$l['ContactInfo']['Phone1'] = "Telefon";
$l['ContactInfo']['Phone2'] = "Telefon";
$l['ContactInfo']['HotspotType'] = "Hotspot Tipi";
$l['ContactInfo']['CompanyWebsite'] = "Şirket Web Sitesi";
$l['ContactInfo']['CompanyPhone'] = "Şirket Telefonu";
$l['ContactInfo']['CompanyEmail'] = "Şirket E-postası";
$l['ContactInfo']['CompanyContact'] = "Şirket İletişim";

$l['ContactInfo']['PlanName'] = "Plan Adı";
$l['ContactInfo']['ContactPerson'] = "İrtibat Kişisi";
$l['ContactInfo']['PaymentMethod'] = "Ödeme Yöntemi";
$l['ContactInfo']['Cash'] = "Nakit";
$l['ContactInfo']['CreditCardNumber'] = "Kart Numarası";
$l['ContactInfo']['CreditCardName'] = "Kart Sahibi";
$l['ContactInfo']['CreditCardVerificationNumber'] = "CVC";
$l['ContactInfo']['CreditCardType'] = "Kredi Kartı Türü";
$l['ContactInfo']['CreditCardExpiration'] = "Son Kullanma";

/* ********************************************************************************** */

$l['Giriş']['configdashboard.php'] = "Kontrol Paneli Ayarları";



$l['Giriş']['paymenttypesmain.php'] = "Ödeme Türleri Sayfası";
$l['Intro']['paymenttypesdel.php'] = "Ödeme Türü Girişini Silin";
$l['Intro']['paymenttypesedit.php'] = "Ödeme Türü Ayrıntılarını Düzenle";
$l['Intro']['paymenttypeslist.php'] = "Ödeme Türleri Tablosu";
$l['Intro']['paymenttypesnew.php'] = "Yeni Ödeme Tipi";
$l['Intro']['paymenttypeslist.php'] = "Ödeme Türleri Tablosu";
$l['Giriş']['paymentslist.php'] = "Ödeme Tablosu";
$l['Giriş']['paymentsmain.php'] = "Ödeme Sayfası";
$l['Intro']['paymentsdel.php'] = "Ödeme Girişini Sil";
$l['Intro']['paymentsedit.php'] = "Ödeme Ayrıntılarını Düzenle";
$l['Intro']['paymentsnew.php'] = "Yeni Ödeme";

$l['Giriş']['billhistorymain.php'] = "Fatura Geçmişi";
$l['Giriş']['msgerrorpermissions.php'] = "Hata";

$l['Intro']['repnewusers.php'] = "Yeni Kullanıcı Listeleme";

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

$l['Intro']['mngradattributes.php'] = "Vendor's Attributes Management";
$l['Intro']['mngradattributeslist.php'] = "Vendor's Attributes List";
$l['Intro']['mngradattributesnew.php'] = "New Vendor Attributes";
$l['Intro']['mngradattributesedit.php'] = "Edit Vendor's Attributes";
$l['Intro']['mngradattributessearch.php'] = "Search Attributes";
$l['Intro']['mngradattributesdel.php'] = "Remove Vendor's Attributes";
$l['Intro']['mngradattributesimport.php'] = "Import Vendor Dictionary";
$l['Intro']['mngimportusers.php'] = "Import Users";


$l['Intro']['acctactive.php'] = "Active Records Accounting";
$l['Intro']['acctall.php'] = "All Users Accounting";
$l['Intro']['acctdate.php'] = "Date Sort Accounting";
$l['Intro']['accthotspot.php'] = "Hotspot Accounting";
$l['Intro']['acctipaddress.php'] = "IP Accounting";
$l['Intro']['accthotspotcompare.php'] = "Hotspot Comparison";
$l['Intro']['acctmain.php'] = "Accounting Page";
$l['Intro']['acctplans.php'] = "Plans Accounting Page";
$l['Intro']['acctnasipaddress.php'] = "NAS IP Accounting";
$l['Intro']['acctusername.php'] = "Users Accounting";
$l['Intro']['acctcustom.php'] = "Custom Accountings";
$l['Intro']['acctcustomquery.php'] = "Custom Query Accounting";
$l['Intro']['acctmaintenance.php'] = "Accounting Records Maintenance";
$l['Intro']['acctmaintenancecleanup.php'] = "Cleanup Stale-connections";
$l['Intro']['acctmaintenancedelete.php'] = "Delete Accounting Records";

$l['Intro']['billmain.php'] = "Billing Page";
$l['Intro']['ratesmain.php'] = "Rates Billing Page";
$l['Intro']['billratesdate.php'] = "Rates Prepaid Accounting";
$l['Intro']['billratesdel.php'] = "Delete Rate entry";
$l['Intro']['billratesedit.php'] = "Edit Rate Details";
$l['Intro']['billrateslist.php'] = "Rates Table";
$l['Intro']['billratesnew.php'] = "New Rate entry";

$l['Intro']['paypalmain.php'] = "PayPal Transactions Page";
$l['Intro']['billpaypaltransactions.php'] = "PayPal Transactions Page";

$l['Intro']['billhistoryquery.php'] = "Billing History";

$l['Giriş']['billinvoice.php'] = "Satış Faturaları";
$l['Intro']['billinvoicedel.php'] = "Fatura Sil";
$l['Intro']['billinvoiceedit.php'] = "Faturayı Düzenle";
$l['Giriş']['billinvoicelist.php'] = "Faturaları Listele";
$l['Giriş']['billinvoicereport.php'] = "Fatura Raporu";
$l['Giriş']['billinvoicenew.php'] = "Yeni Fatura";

$l['Giriş']['billplans.php'] = "Faturalandırma Planları Sayfası";
$l['Intro']['billplansdel.php'] = "Plan Sil";
$l['Intro']['billplansedit.php'] = "Plan Ayrıntılarını Düzenle";
$l['Giriş']['billplanslist.php'] = "Plan Tablosu";
$l['Giriş']['billplansnew.php'] = "Yeni Plan Girişi";

$l['Giriş']['billpos.php'] = "Faturalandırma Satış Noktası Sayfası";
$l['Giriş']['billposdel.php'] = "Kullanıcıyı Sil";
$l['Intro']['billposedit.php'] = "Kullanıcıyı Düzenle";
$l['Giriş']['billposlist.php'] = "Kullanıcıları Listele";
$l['Giriş']['billposnew.php'] = "Yeni Kullanıcı";

$l['Intro']['giseditmap.php'] = "Edit MAP Mode";
$l['Intro']['gismain.php'] = "GIS Mapping";
$l['Intro']['gisviewmap.php'] = "View MAP Mode";

$l['Giriş']['graphmain.php'] = "Kullanım Grafikleri";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Toplam Trafik Karşılaştırma Kullanımı";
$l['Intro']['graphsalltimelogins.php'] = "Toplam Oturum Açma Sayısı";
$l['Giriş']['graphslogggedusers.php'] = "Kayıtlı Kullanıcılar";
$l['Intro']['graphsoveralldownload.php'] = "Kullanıcı İndirmeleri";
$l['Intro']['graphsoveralllogins.php'] = "Kullanıcı Girişleri";
$l['Intro']['graphsoverallupload.php'] = "Kullanıcı Yüklemeleri";

$l['Intro']['rephistory.php'] = "Action History";
$l['Intro']['replastconnect.php'] = "Last Connection Attempts";
$l['Intro']['repstatradius.php'] = "Daemons Information";
$l['Intro']['repstatserver.php'] = "Server Status and Information";
$l['Intro']['reponline.php'] = "Listing Online Users";
$l['Intro']['replogssystem.php'] = "System Logfile";
$l['Intro']['replogsradius.php'] = "RADIUS Server Logfile";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS Logfile";
$l['Intro']['replogsboot.php'] = "Boot Logfile";
$l['Intro']['replogs.php'] = "Logs";
$l['Intro']['rephb.php'] = "Heartbeat";
$l['Intro']['rephbdashboard.php'] = "daloRADIUS NAS Dashboard";
$l['Intro']['repbatch.php'] = "Batch";
$l['Intro']['mngbatchlist.php'] = "Batch Sessions List";
$l['Intro']['repbatchlist.php'] = "Batch Users List";
$l['Intro']['repbatchdetails.php'] = "Batch Details";

$l['Intro']['rephsall.php'] = "Hotspots Listing";
$l['Giriş']['repmain.php'] = "Raporlar Sayfası";
$l['Giriş']['repstatus.php'] = "Durum Sayfası";
$l['Giriş']['reptopusers.php'] = "En İyi Kullanıcılar";
$l['Giriş']['repusername.php'] = "Kullanıcı Listesi";

$l['Intro']['mngbatch.php'] = "Create batch users";
$l['Intro']['mngbatchdel.php'] = "Delete batch sessions";

$l['Intro']['mngdel.php'] = "Kullanıcıyı Kaldır";
$l['Intro']['mngedit.php'] = "Kullanıcı Ayrıntılarını Düzenle";
$l['Intro']['mnglistall.php'] = "Kullanıcı Listesi";
$l['Intro']['mngmain.php'] = "Kullanıcılar ve Erişim Noktaları Yönetimi";
$l['Giriş']['mngbatch.php'] = "Toplu Kullanıcı Yönetimi";
$l['Giriş']['mngnew.php'] = "Yeni Kullanıcı";
$l['Giriş']['mngnewquick.php'] = "Hızlı Kullanıcı Ekleme";
$l['Giriş']['mngsearch.php'] = "Kullanıcı Arama";

$l['Intro']['mnghsdel.php'] = "Hotspot Sil";
$l['Intro']['mnghsedit.php'] = "Hotspots Düzenle";
$l['Intro']['mnghslist.php'] = "Hotspot Listesi";
$l['Intro']['mnghsnew.php'] = "Yeni Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Remove User-Group Mapping";
$l['Intro']['mngradusergroup.php'] = "User-Group Configuration";
$l['Intro']['mngradusergroupnew.php'] = "New User-Group Mapping";
$l['Intro']['mngradusergrouplist'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergrouplistuser'] = "User-Group Mapping in Database";
$l['Intro']['mngradusergroupedit'] = "Edit User-Group Mapping for User:";

$l['Intro']['mngradippool.php'] = "IP-Pool Configuration";
$l['Intro']['mngradippoolnew.php'] = "New IP-Pool";
$l['Intro']['mngradippoollist.php'] = "List IP-Pools";
$l['Intro']['mngradippooledit.php'] = "Edit IP-Pool";
$l['Intro']['mngradippooldel.php'] = "Remove IP-Pool";

$l['Intro']['mngradnas.php'] = "NAS Configuration";
$l['Intro']['mngradnasnew.php'] = "New NAS Record";
$l['Intro']['mngradnaslist.php'] = "NAS Listing in Database";
$l['Intro']['mngradnasedit.php'] = "Edit NAS Record";
$l['Intro']['mngradnasdel.php'] = "Remove NAS Record";

$l['Intro']['mngradhunt.php'] = "HuntGroup Configuration";
$l['Intro']['mngradhuntnew.php'] = "New HuntGroup Record";
$l['Intro']['mngradhuntlist.php'] = "HuntGroup Listing in Database";
$l['Intro']['mngradhuntedit.php'] = "Edit HuntGroup Record";
$l['Intro']['mngradhuntdel.php'] = "Remove HuntGroup Record";

$l['Intro']['mngradprofiles.php'] = "Profil Ayarları";
$l['Intro']['mngradprofilesedit.php'] = "Profilleri Düzenle";
$l['Intro']['mngradprofilesduplicate.php'] = "Benzer Profiller";
$l['Intro']['mngradprofilesdel.php'] = "Profilleri Sil";
$l['Intro']['mngradprofileslist.php'] = "Profilleri Listele";
$l['Giriş']['mngradprofilesnew.php'] = "Yeni Profil";

$l['Intro']['mngradgroups.php'] = "Grup Ayarları";

$l['Intro']['mngradgroupreplynew.php'] = "Yeni Grup Yanıt Eşlemesi";
$l['Intro']['mngradgroupreplylist.php'] = "Veritabanında Grup Yanıt Eşlemesi";
$l['Intro']['mngradgroupreplyedit.php'] = "Grup için Grup Yanıt Eşlemesini Düzenle:";
$l['Intro']['mngradgroupreplydel.php'] = "Grup Yanıt Eşlemesini Kaldır";
$l['Intro']['mngradgroupreplysearch.php'] = "Arama Grubu Yanıt Eşlemesi";

$l['Intro']['mngradgroupchecknew.php'] = "Yeni Grup Kontrol Eşlemesi";
$l['Intro']['mngradgroupchecklist.php'] = "Veritabanında Grup Kontrol Eşlemesi";
$l['Intro']['mngradgroupcheckedit.php'] = "Grup için Grup Kontrol Eşlemesini Düzenle:";
$l['Intro']['mngradgroupcheckdel.php'] = "Grup Kontrol Eşlemesini Kaldır";
$l['Intro']['mngradgroupchecksearch.php'] = "Arama Grubu Kontrol Eşlemesi";

$l['Giriş']['configuser.php'] = "Kullanıcı Ayarları";
$l['Giriş']['configmail.php'] = "E-posta Ayarları";

$l['Giriş']['configdb.php'] = "Veritabanı Ayarları";
$l['Giriş']['configlang.php'] = "Dil Ayarları";
$l['Intro']['configlogging.php'] = "Loglama Ayarları";
$l['Intro']['configinterface.php'] = "Arayüz Ayarları";
$l['Intro']['configmainttestuser.php'] = "Kullanıcı Bağlantısını Test Et";
$l['Giriş']['configmain.php'] = "Veritabanı Yapılandırması";
$l['Giriş']['configmaint.php'] = "Bakım";
$l['Intro']['configmaintdisconnectuser.php'] = "Kullanıcı Bağlantısını Kes";
$l['Giriş']['configbusiness.php'] = "İş Ayrıntıları";
$l['Giriş']['configbusinessinfo.php'] = "İş Bilgileri";
$l['Giriş']['configbackup.php'] = "Yedekleme";
$l['Intro']['configbackupcreatebackups.php'] = "Yedekleme Oluştur";
$l['Intro']['configbackupmanagebackups.php'] = "Yedeklemeleri Yönet";

$l['Giriş']['configoperators.php'] = "Operatör Ayarları";
$l['Intro']['configoperatorsdel.php'] = "Yönetici Kaldır";
$l['Intro']['configoperatorsedit.php'] = "Yönetici Düzenle";
$l['Giriş']['configoperatorsnew.php'] = "Yeni Yönetici";
$l['Intro']['configoperatorslist.php'] = "Yönetici Listesi";

$l['Giriş']['login.php'] = "Giriş";

$l['captions']['providebillratetodel'] = "Kaldırmak istediğiniz ücret giriş türünü belirtin";
$l['captions']['detailsofnewrate'] = "Yeni fiyat için aşağıdaki bilgileri doldurabilirsiniz";
$l['captions']['filldetailsofnewrate'] = "Yeni fiyat girişi için ayrıntıları aşağıya girin";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "Kontrol Paneli Ayarları";


$l['helpPage']['repnewusers'] = "The following table lists new users created each month.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "List all PayPal transactions";
$l['helpPage']['billhistoryquery'] = "List all billing history for a user(s)";

$l['helpPage']['billinvoicereport'] = "";

$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billinvoicenew'] = "";
$l['helpPage']['billinvoiceedit'] = "";
$l['helpPage']['billinvoicedel'] = "";

$l['helpPage']['paymenttypeslist'] = "";
$l['helpPage']['paymenttypesnew'] = "";
$l['helpPage']['paymenttypesedit'] = "";
$l['helpPage']['paymenttypesdel'] = "";
$l['helpPage']['paymenttypesdate'] = "";

$l['helpPage']['paymentslist'] = "";
$l['helpPage']['paymentsnew'] = "";
$l['helpPage']['paymentsedit'] = "";
$l['helpPage']['paymentsdel'] = "";
$l['helpPage']['paymentsdate'] = "";

$l['helpPage']['billplanslist'] = "";
$l['helpPage']['billplansnew'] = "";
$l['helpPage']['billplansedit'] = "";
$l['helpPage']['billplansdel'] = "";

$l['helpPage']['billposlist'] = "";
$l['helpPage']['billposnew'] = "";
$l['helpPage']['billposedit'] = "";
$l['helpPage']['billposdel'] = "";

$l['helpPage']['billrateslist'] = "";
$l['helpPage']['billratesnew'] = "";
$l['helpPage']['billratesedit'] = "";
$l['helpPage']['billratesdel'] = "";
$l['helpPage']['billratesdate'] = "";

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

$l['helpPage']['mngradattributes'] = "";
$l['helpPage']['mngradattributeslist'] = "";
$l['helpPage']['mngradattributesnew'] = "";
$l['helpPage']['mngradattributesedit'] = "";
$l['helpPage']['mngradattributessearch'] = "";
$l['helpPage']['mngradattributesdel'] = "";
$l['helpPage']['mngradattributesimport'] = "";
$l['helpPage']['mngimportusers'] = "";

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
$l['helpPage']['mngradprofilesduplicate'] = "
<h200><b>Duplicate Profile </b></h200> - Duplicate a Profile's set of attributes to a new one with a different profile name <br/>
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


$l['helpPage']['mngradippool'] = "
<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>
<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>
<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>
<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "To remove a nas ip/host entry from the database you must provide the ip/host of the account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

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
$l['helpPage']['configoperatorsdel'] = "Veritabanından bir operatörü kaldırmak için kullanıcı adını sağlamalısınız.";
$l['helpPage']['configoperatorsedit'] = "Operatör kullanıcı ayrıntılarını aşağıda düzenleyin";
$l['helpPage']['configoperatorsnew'] = "Veritabanına eklenen yeni operatör kullanıcısı için aşağıdaki bilgileri doldurabilirsiniz";
$l['helpPage']['configoperatorslist'] = "Veritabanındaki tüm Operatörleri listeleme";
$l['helpPage']['configoperators'] = "Operatör Yapılandırması";
$l['helpPage']['configbackup'] = "Yedekleme Yap";
$l['helpPage']['configbackupcreatebackups'] = "Yedekleme Oluştur";
$l['helpPage']['configbackupmanagebackups'] = "Yedeklemeleri Yönet";


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
$l['helpPage']['repbatchdetails'] = "Provides a list of active users of this batch instance";
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
