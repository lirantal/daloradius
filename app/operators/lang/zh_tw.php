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
 * Description:    繁體中文（台灣）語言檔案
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 三多 <10644331064@qq.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *                 rongzedong <rongzedong@qq.com> 2023-11-30
 *
 * Note:           Traditional Chinese (Taiwan) pack, derived from the Simplified
 *                 Chinese pack (zh.php) with Taiwan-localised terminology.
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/zh_tw.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("版本 %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS 管理、報表、計費與帳務 <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . <<<EOF
 <span class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Follow @filippolauria on GitHub">
  <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>
</span>  and <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a> Chinese language pack produced by SanDuo, rongzedong, robertkwok2..
EOF;

$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "IP位址名稱";
$l['all']['CalledStationId'] = "被叫號碼";
$l['all']['CallingStationID'] = "主叫號碼";
$l['all']['ExpiryTime'] = "到期時間";
$l['all']['PoolKey'] = "池金鑰";

/********************************************************************************/
/* 裝置屬性相關的翻譯                                     */
/********************************************************************************/
$l['all']['Dictionary'] = "字典";
$l['all']['VendorID'] = "裝置程式碼";
$l['all']['VendorName'] = "裝置名稱";
$l['all']['VendorAttribute'] = "所屬裝置";
$l['all']['RecommendedOP'] = "推薦人";
$l['all']['RecommendedTable'] = "推薦表";
$l['all']['RecommendedTooltip'] = "推薦工具提示";
$l['all']['RecommendedHelper'] = "推薦助手";
/***********************************************************************************/

$l['all']['CSVData'] = "CSV格式資料";

$l['all']['CPU'] = "CPU";

/* ****************************** radius的相關文字 ******************************* */
$l['all']['RADIUSDictionaryPath'] = "RADIUS字典路徑";


$l['all']['DashboardSecretKey'] = "儀表板金鑰";
$l['all']['DashboardDebug'] = "除錯";
$l['all']['DashboardDelaySoft'] = "在幾分鐘的時間來考慮一個‘軟’延遲限制";
$l['all']['DashboardDelayHard'] = "在幾分鐘的時間來考慮一個‘硬’延遲限制";



$l['all']['SendWelcomeNotification'] = "歡迎傳送通知";
$l['all']['SMTPServerAddress'] = "SMTP伺服器位址";
$l['all']['SMTPServerPort'] = "SMTP伺服器連接埠";
$l['all']['SMTPServerFromEmail'] = "發件人郵件地址";

$l['all']['customAttributes'] = "使用者屬性";

$l['all']['UserType'] = "使用者類型";

$l['all']['BatchName'] = "批次名稱";
$l['all']['BatchStatus'] = "批次狀態";

$l['all']['Users'] = "使用者";

$l['all']['Compare'] = "比較";
$l['all']['Never'] = "從不";


$l['all']['Section'] = "部門";
$l['all']['Item'] = "項目";

$l['all']['Megabytes'] = "MB";
$l['all']['Gigabytes'] = "GB";

$l['all']['Daily'] = "每日";
$l['all']['Weekly'] = "每週";
$l['all']['Monthly'] = "每月";
$l['all']['Yearly'] = "每年";

$l['all']['Month'] = "月";

$l['all']['RemoveRadacctRecords'] = "刪除帳單記錄";

$l['all']['CleanupSessions'] = "清理工作階段年齡比";
$l['all']['DeleteSessions'] = "刪除工作階段年齡比";

$l['all']['StartingDate'] = "開始日期";
$l['all']['EndingDate'] = "結束日期";

$l['all']['Realm'] = "域";
$l['all']['RealmName'] = "域名";
$l['all']['RealmSecret'] = "網域共用金鑰";
$l['all']['AuthHost'] = "認證主機";
$l['all']['AcctHost'] = "統計主機";
$l['all']['Ldflag'] = "ld標識";
$l['all']['Nostrip'] = "分佈IP";
$l['all']['Notrealm'] = "非域";
$l['all']['Hints'] = "提示";

$l['all']['Proxy'] = "代理";
$l['all']['ProxyName'] = "代理名稱";
$l['all']['ProxySecret'] = "代理共用金鑰";
$l['all']['DeadTime'] = "停滯時間";
$l['all']['RetryDelay'] = "延遲重試";
$l['all']['RetryCount'] = "重試次數";
$l['all']['DefaultFallback'] = "預設後退";


$l['all']['Firmware'] = "韌體";
$l['all']['NASMAC'] = "NAS MAC";

$l['all']['WanIface'] = "廣域網網路介面";
$l['all']['WanMAC'] = "廣域網MAC位址";
$l['all']['WanIP'] = "廣域網IP位址";
$l['all']['WanGateway'] = "廣域網閘道器";

$l['all']['LanIface'] = "區域網路網路介面";
$l['all']['LanMAC'] = "區域網路MAC位址";
$l['all']['LanIP'] = "區域網路IP位址";

$l['all']['WifiIface'] = "無線網網路介面";
$l['all']['WifiMAC'] = "無線網MAC位址";
$l['all']['WifiIP'] = "無線網IP位址";

$l['all']['WifiSSID'] = "無線網網路名稱";
$l['all']['WifiKey'] = "無線網金鑰";
$l['all']['WifiChannel'] = "無線網頻道";

$l['all']['CheckinTime'] = "最後登入";

$l['all']['FramedIPAddress'] = "使用者IP位址";
$l['all']['SimultaneousUse'] = "同時使用";
$l['all']['HgID'] = "尋線群ID";
$l['all']['Hg'] = "尋線群";
$l['all']['HgIPHost'] = "尋線群IP/主機";
$l['all']['HgGroupName'] = "尋線群組名稱";
$l['all']['HgPortId'] = "尋線群連接埠名稱";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/主機";
$l['all']['NasShortname'] = "NAS 簡稱";
$l['all']['NasType'] = "NAS類型";
$l['all']['NasPorts'] = "NAS連接埠";
$l['all']['NasSecret'] = "NAS 共用金鑰";
$l['all']['NasCommunity'] = "NAS 社群字串";
$l['all']['NasDescription'] = "NAS描述";
$l['all']['PacketType'] = "封包類型";
$l['all']['HotSpot'] = "熱點";
$l['all']['HotSpots'] = "熱點";
$l['all']['HotSpotName'] = "熱點名稱";
$l['all']['Name'] = "名稱";
$l['all']['Username'] = "使用者名稱";
$l['all']['Password'] = "密碼";
$l['all']['PasswordType'] = "密碼類型";
$l['all']['IPAddress'] = "IP位址";
$l['all']['Profile'] = "使用者設定檔";
$l['all']['Group'] = "群組";
$l['all']['Groupname'] = "群組名稱";
$l['all']['ProfilePriority'] = "設定檔優先順序";
$l['all']['GroupPriority'] = "群組優先順序";
$l['all']['CurrentGroupname'] = "目前的群組名稱";
$l['all']['NewGroupname'] = "新增群組名稱";
$l['all']['Priority'] = "優先";
$l['all']['Attribute'] = "屬性";
$l['all']['Operator'] = "操作員";
$l['all']['Value'] = "值";
$l['all']['NewValue'] = "新建值";
$l['all']['MaxTimeExpiration'] = "最大時間/有效期";
$l['all']['UsedTime'] = "使用時間";
$l['all']['Status'] = "狀態";
$l['all']['Usage'] = "使用";
$l['all']['StartTime'] = "登入時間";
$l['all']['StopTime'] = "停止時間";
$l['all']['TotalTime'] = "總時間";
$l['all']['TotalTraffic'] = "總流量";
$l['all']['Bytes'] = "位元組";
$l['all']['Upload'] = "上傳";
$l['all']['Download'] = "下載";
$l['all']['Rollback'] = "回滾";
$l['all']['Termination'] = "終止";
$l['all']['NASIPAddress'] = "NAS IP位址";
$l['all']['NASShortName'] = "NAS簡稱";
$l['all']['Action'] = "活動";
$l['all']['UniqueUsers'] = "獨立使用者";
$l['all']['TotalHits'] = "總點選數";
$l['all']['AverageTime'] = "平均時間";
$l['all']['Records'] = "記錄";
$l['all']['Summary'] = "明細";
$l['all']['Statistics'] = "統計";
$l['all']['Credit'] = "信用";
$l['all']['Used'] = "已使用";
$l['all']['LeftTime'] = "剩餘時間";
$l['all']['LeftPercent'] = "%剩餘時間";
$l['all']['TotalSessions'] = "總工作階段";
$l['all']['LastLoginTime'] = "最後登入時間";
$l['all']['TotalSessionTime'] = "總工作階段時間";
$l['all']['RateName'] = "價格名稱";
$l['all']['RateType'] = "價格類型";
$l['all']['RateCost'] = "成本率";//這個詞語有待改進
$l['all']['Billed'] = "記帳";
$l['all']['TotalUsers'] = "總使用者";
$l['all']['ActiveUsers'] = "活動使用者";
$l['all']['TotalBilled'] = "總記帳";
$l['all']['TotalPayed'] = "總支付";
$l['all']['Balance'] = "餘額";
$l['all']['Type'] = "類型";
$l['all']['CardBank'] = "銀行卡";
$l['all']['MACAddress'] = "MAC位址";
$l['all']['Geocode'] = "地址編碼";
$l['all']['PINCode'] = "PIN碼";
$l['all']['CreationDate'] = "建立日期";
$l['all']['CreationBy'] = "建立人";
$l['all']['UpdateDate'] = "更新日期";
$l['all']['UpdateBy'] = "更新人";

$l['all']['Discount'] = "折扣";
$l['all']['BillAmount'] = "記帳總額";
$l['all']['BillAction'] = "記帳功能";
$l['all']['BillPerformer'] = "記帳執行者";
$l['all']['BillReason'] = "記帳原因";
$l['all']['Lead'] = "廣告";
$l['all']['Coupon'] = "優惠券";
$l['all']['OrderTaker'] = "訂單員";
$l['all']['BillStatus'] = "記帳狀態";
$l['all']['LastBill'] = "最後記帳";
$l['all']['NextBill'] = "下次記帳";
$l['all']['BillDue'] = "記帳到期";
$l['all']['NextInvoiceDue'] = "下次應付款帳單";
$l['all']['PostalInvoice'] = "郵寄帳單";
$l['all']['FaxInvoice'] = "傳真帳單";
$l['all']['EmailInvoice'] = "Email帳單";

$l['all']['ClientName'] = "客戶名稱";
$l['all']['Date'] = "日期";

$l['all']['edit'] = "編輯";
$l['all']['del'] = "刪除";
$l['all']['groupslist'] = "群組清單";
$l['all']['TestUser'] = "測試使用者";
$l['all']['Accounting'] = "帳單";
$l['all']['RADIUSReply'] = "使用者狀態";/**RADIUS回覆狀態Access-Accept  Access-Request**/

$l['all']['Disconnect'] = "斷開";

$l['all']['Debug'] = "除錯";
$l['all']['Timeout'] = "逾時";
$l['all']['Retries'] = "重試";
$l['all']['Count'] = "計數";
$l['all']['Requests'] = "請求";

$l['all']['DatabaseHostname'] = "資料庫主機名稱";
$l['all']['DatabasePort'] = "資料庫連接埠號";
$l['all']['DatabaseUser'] = "資料庫使用者名稱";
$l['all']['DatabasePass'] = "資料庫密碼";
$l['all']['DatabaseName'] = "資料名稱";

$l['all']['PrimaryLanguage'] = "主要語言";

$l['all']['PagesLogging'] = "頁面日誌（存取頁面）";
$l['all']['QueriesLogging'] = "查詢日誌（報表和圖表）";
$l['all']['ActionsLogging'] = "活動日誌（表單提交）";
$l['all']['FilenameLogging'] = "檔名日誌（完整路徑）";
$l['all']['LoggingDebugOnPages'] = "頁面除錯資訊日誌";
$l['all']['LoggingDebugInfo'] = "除錯資訊日誌";

$l['all']['PasswordHidden'] = "啟用密碼隱藏（將用星號顯示）";
$l['all']['TablesListing'] = "行/記錄每表格清單頁面";
$l['all']['TablesListingNum'] = "啟用表清單編號";
$l['all']['AjaxAutoComplete'] = "啟用Ajax自動完成";

$l['all']['RadiusServer'] = "Radius伺服器";
$l['all']['RadiusPort'] = "Radius連接埠";

$l['all']['UsernamePrefix'] = "使用者字首";

$l['all']['batchName'] = "批次Id/名稱";
$l['all']['batchDescription'] = "批次描述";

$l['all']['NumberInstances'] = "建立數量";
$l['all']['UsernameLength'] = "使用者名稱字元數";
$l['all']['PasswordLength'] = "密碼字元數";

$l['all']['Expiration'] = "效期時間";
$l['all']['MaxAllSession'] = "最大總工作階段";
$l['all']['SessionTimeout'] = "工作階段逾時";
$l['all']['IdleTimeout'] = "閒置逾時";

$l['all']['DBEngine'] = "伺服器引擎";
$l['all']['radcheck'] = "radius檢查";
$l['all']['radreply'] = "radius回覆";
$l['all']['radgroupcheck'] = "radius群組檢查";
$l['all']['radgroupreply'] = "radius群組回覆";
$l['all']['usergroup'] = "使用者群組";
$l['all']['radacct'] = "radius帳單";
$l['all']['operators'] = "操作人";
$l['all']['operators_acl'] = "操作人存取控制清單";
$l['all']['operators_acl_files'] = "操作人存取控制清單檔案";
$l['all']['billingrates'] = "記帳費用";
$l['all']['hotspots'] = "熱點";
$l['all']['node'] = "節點";
$l['all']['nas'] = "nas";
$l['all']['hunt'] = "radius尋線群";
$l['all']['radpostauth'] = "radius提交認證";
$l['all']['radippool'] = "radiusIP位址池";
$l['all']['userinfo'] = "使用者資訊";
$l['all']['dictionary'] = "字典";
$l['all']['realms'] = "域";
$l['all']['proxys'] = "代理";
$l['all']['billingpaypal'] = "PayPal記帳";
$l['all']['billingmerchant'] = "供貨方記帳";
$l['all']['billingplans'] = "記帳方案";
$l['all']['billinghistory'] = "記帳歷史";
$l['all']['billinginfo'] = "記帳資訊";


$l['all']['CreateIncrementingUsers'] = "建立增量使用者";
$l['all']['CreateRandomUsers'] = "建立隨機使用者";
$l['all']['StartingIndex'] = "開始索引";
$l['all']['EndingIndex'] = "結束索引";
$l['all']['RandomChars'] = "允許隨機字元";
$l['all']['Memfree'] = "閒置記憶體";
$l['all']['Uptime'] = "正常執行時間";
$l['all']['BandwidthUp'] = "上傳頻寬";
$l['all']['BandwidthDown'] = "下載頻寬";

$l['all']['BatchCost'] = "批次花費";

$l['all']['PaymentStatus'] = "付款狀態";
$l['all']['FirstName'] = "名";
$l['all']['LastName'] = "姓";
$l['all']['VendorType'] = "裝置類型";
$l['all']['PayerStatus'] = "付款人狀態";
$l['all']['PaymentAddressStatus'] = "付款地址狀態";
$l['all']['PayerEmail'] = "付款日Email";
$l['all']['TxnId'] = "交易ID";
$l['all']['PlanActive'] = "活動方案";
$l['all']['PlanTimeType'] = "方案時間類型";
$l['all']['PlanTimeBank'] = "方案時間銀行";
$l['all']['PlanTimeRefillCost'] = "方案補充花費";
$l['all']['PlanTrafficRefillCost'] = "方案補充花費";
$l['all']['PlanBandwidthUp'] = "方案上傳頻寬";
$l['all']['PlanBandwidthDown'] = "方案下載頻寬";
$l['all']['PlanTrafficTotal'] = "方案總流量";
$l['all']['PlanTrafficDown'] = "方案下載流量";
$l['all']['PlanTrafficUp'] = "方案上傳流量";
$l['all']['PlanRecurring'] = "方案迴圈";
$l['all']['PlanRecurringPeriod'] = "方案迴圈週期";
$l['all']['planRecurringBillingSchedule'] = "方案重複記帳安排";
$l['all']['PlanCost'] = "方案花費";
$l['all']['PlanSetupCost'] = "方案安裝花費";
$l['all']['PlanTax'] = "方案稅額";
$l['all']['PlanCurrency'] = "方案貨幣";
$l['all']['PlanGroup'] = "方案設定檔（群組）";
$l['all']['PlanType'] = "方案類型";
$l['all']['PlanName'] = "方案名稱";
$l['all']['PlanId'] = "方案ID";

$l['all']['UserId'] = "使用者Id";

$l['all']['Invoice'] = "帳單";
$l['all']['InvoiceID'] = "帳單ID";
$l['all']['InvoiceItems'] = "帳單項目";
$l['all']['InvoiceStatus'] = "帳單狀態";

$l['all']['InvoiceType'] = "帳單類型";
$l['all']['Amount'] = "總額";
$l['all']['Total'] = "總計";
$l['all']['TotalInvoices'] = "總帳單";

$l['all']['PayTypeName'] = "付款類型名稱";
$l['all']['PayTypeNotes'] = "付款類型描述";
$l['all']['payment_type'] = "付款類型";
$l['all']['payments'] = "付款";
$l['all']['PaymentId'] = "付款ID";
$l['all']['PaymentInvoiceID'] = "帳單ID";
$l['all']['PaymentAmount'] = "支付金額";
$l['all']['PaymentDate'] = "日期";
$l['all']['PaymentType'] = "付款類型";
$l['all']['PaymentNotes'] = "付款備註";




$l['all']['Quantity'] = "總量";
$l['all']['ReceiverEmail'] = "接受電子郵件";
$l['all']['Business'] = "公司";
$l['all']['Tax'] = "稅額";
$l['all']['Cost'] = "花費";
$l['all']['TotalCost'] = "總花費";
$l['all']['TransactionFee'] = "交易費";
$l['all']['PaymentCurrency'] = "支付貨幣";
$l['all']['AddressRecipient'] = "地址接收人";
$l['all']['Street'] = "街道";
$l['all']['Country'] = "國家";
$l['all']['CountryCode'] = "國家程式碼";
$l['all']['City'] = "城市";
$l['all']['State'] = "省份";
$l['all']['Zip'] = "郵編";

$l['all']['BusinessName'] = "公司名字";
$l['all']['BusinessPhone'] = "公司電話";
$l['all']['BusinessAddress'] = "公司地址";
$l['all']['BusinessWebsite'] = "公司網址";
$l['all']['BusinessEmail'] = "公司Email";
$l['all']['BusinessContactPerson'] = "公司聯絡人";
$l['all']['DBPasswordEncryption'] = "資料庫密碼加密類型";


/***********************************************************************************
    工具提示
    輔助資訊輔助資訊,如為滑鼠懸停提示文字事件和彈出提示
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "為本批建立提供一個識別符號名稱";
$l['Tooltip']['batchDescriptionTooltip'] = "為本批建立提供一個一般描述";

$l['Tooltip']['hotspotTooltip'] = "選擇與這批例項相關聯的熱點名字";

$l['Tooltip']['startingIndexTooltip'] = "提供起始索引的建立使用者";
$l['Tooltip']['planTooltip'] = "選一個方案來關聯使用者";

$l['Tooltip']['InvoiceEdit'] = "編輯帳單";
$l['Tooltip']['invoiceTypeTooltip'] = "帳單類型工具提示";
$l['Tooltip']['invoiceStatusTooltip'] = "帳單狀態工具提示";
$l['Tooltip']['invoiceID'] = "帳單ID類型";

$l['Tooltip']['amountTooltip'] = "金額工具提示";
$l['Tooltip']['taxTooltip'] = "稅額工具提示";

$l['Tooltip']['PayTypeName'] = "支付類型名稱";
$l['Tooltip']['EditPayType'] = "編輯支付類型";
$l['Tooltip']['RemovePayType'] = "移除支付類型";
$l['Tooltip']['paymentTypeTooltip'] = "付款類型友好的名稱,<br/>
                                        來描述付款的目的";
$l['Tooltip']['paymentTypeNotesTooltip'] = "描述付款類型的描述<br/>
                                        付款類型的操作";
$l['Tooltip']['EditPayment'] = "編輯付款";
$l['Tooltip']['PaymentId'] = "付款Id";
$l['Tooltip']['RemovePayment'] = "移除付款";
$l['Tooltip']['paymentInvoiceTooltip'] = "此次付款相關的帳單";



$l['Tooltip']['Username'] = "使用者名稱類型";
$l['Tooltip']['BatchName'] = "批次名稱類型";
$l['Tooltip']['UsernameWildcard'] = "提示: 你可以用字元 * 或 % 來制定一個萬用字元";
$l['Tooltip']['HotspotName'] = "熱點名稱類型";
$l['Tooltip']['NasName'] = "NAS名稱類型";
$l['Tooltip']['GroupName'] = "群組名稱類型";
$l['Tooltip']['AttributeName'] = "屬性名稱類型";
$l['Tooltip']['VendorName'] = "裝置名稱類型";
$l['Tooltip']['PoolName'] = "IP位址池名稱類型";
$l['Tooltip']['IPAddress'] = "IP位址池類型";
$l['Tooltip']['Filter'] = "過濾器的類型，可以是任何字元的字串。用留空配對其它。";
$l['Tooltip']['Date'] = "日期類型 <br/> 範例: 2024-06-05 (Y-M-D)";
$l['Tooltip']['RateName'] = "價格名稱類型";
$l['Tooltip']['OperatorName'] = "操作人名稱類型";
$l['Tooltip']['BillingPlanName'] = "記帳方案名稱類型";
$l['Tooltip']['PlanName'] = "方案名稱類型";

$l['Tooltip']['EditRate'] = "編輯價格";
$l['Tooltip']['RemoveRate'] = "移除價格";

$l['Tooltip']['rateNameTooltip'] = "價格的名稱，<br/>
                    來描述價格的用途";
$l['Tooltip']['rateTypeTooltip'] = "價格類型，來描述<br/>
                    價格的操作";
$l['Tooltip']['rateCostTooltip'] = "價格花費金額";

$l['Tooltip']['planNameTooltip'] = "方案的名字。這是<br/>
                    一個友好的描述方案的特性。";
$l['Tooltip']['planIdTooltip'] = "方案ID提示工具";
$l['Tooltip']['planTimeTypeTooltip'] = "方案時間類型提示工具";
$l['Tooltip']['planTimeBankTooltip'] = "方案時間銀行提示工具";
$l['Tooltip']['planTimeRefillCostTooltip'] = "方案時間補充話費提示工具";
$l['Tooltip']['planTrafficRefillCostTooltip'] = "方案流量補充提示工具";
$l['Tooltip']['planBandwidthUpTooltip'] = "方案上傳頻寬提示工具";
$l['Tooltip']['planBandwidthDownTooltip'] = "方案下載頻寬提示工具";
$l['Tooltip']['planTrafficTotalTooltip'] = "方案總流量提示工具";
$l['Tooltip']['planTrafficDownTooltip'] = "方案下載流量提示工具";
$l['Tooltip']['planTrafficUpTooltip'] = "方案流量上傳提示工具";

$l['Tooltip']['planRecurringTooltip'] = "方案迴圈提示工具";
$l['Tooltip']['planRecurringBillingScheduleTooltip'] = "方案迴圈記帳安排提示工具";
$l['Tooltip']['planRecurringPeriodTooltip'] = "方案迴圈週期提示工具";
$l['Tooltip']['planCostTooltip'] = "方案花費提示工具";
$l['Tooltip']['planSetupCostTooltip'] = "方案安裝話費提示工具";
$l['Tooltip']['planTaxTooltip'] = "方案稅額提示工具";
$l['Tooltip']['planCurrencyTooltip'] = "方案貨幣提示工具";
$l['Tooltip']['planGroupTooltip'] = "方案群組提示工具";

$l['Tooltip']['EditIPPool'] = "編輯IP位址池";
$l['Tooltip']['RemoveIPPool'] = "移除IP位址池";
$l['Tooltip']['EditIPAddress'] = "編輯IP位址";
$l['Tooltip']['RemoveIPAddress'] = "移除IP位址";

$l['Tooltip']['BusinessNameTooltip'] = "公司名稱提示工具";
$l['Tooltip']['BusinessPhoneTooltip'] = "公司電話提示工具";
$l['Tooltip']['BusinessAddressTooltip'] = "公司地址提示工具";
$l['Tooltip']['BusinessWebsiteTooltip'] = "公司網站提示工具";
$l['Tooltip']['BusinessEmailTooltip'] = "公司Email提示工具";
$l['Tooltip']['BusinessContactPersonTooltip'] = "公司聯絡人提示工具";

$l['Tooltip']['proxyNameTooltip'] = "代理名稱";
$l['Tooltip']['proxyRetryDelayTooltip'] = "等待的時間(在短時間內)<br/>
                    來自代理的響應, <br/>
                    在重發代理請求之前";
$l['Tooltip']['proxyRetryCountTooltip'] = "傳送重試次數 <br/>
                    在放棄之前,併傳送拒絕 <br/>
                    訊息給NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "如果主機不響應 <br/>
                    給任意一個多重嘗試，<br/>
                    然後FreeRADIUS將停止傳送給它。<br/>
                    代理請求，然後標記它‘廢棄’。";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "如果所有完全匹配的域 <br/>
                        不響應，我們可以嘗試 <br/>
                        ";
$l['Tooltip']['realmNameTooltip'] = "域名";
$l['Tooltip']['realmTypeTooltip'] = "設定預設radius";
$l['Tooltip']['realmSecretTooltip'] = "網域 RADIUS 共用金鑰";
$l['Tooltip']['realmAuthhostTooltip'] = "域認證主機";
$l['Tooltip']['realmAccthostTooltip'] = "域帳單主機";
$l['Tooltip']['realmLdflagTooltip'] = "允許負載平衡<br/>
                    允許值為‘失效轉移’ <br/>
                    和‘輪叫排程’。";
$l['Tooltip']['realmNostripTooltip'] = "不論是否去除 <br/>
                    域字尾";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "範例：cisco<br/>&nbsp;&nbsp;&nbsp;
                                        廠商名稱<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "範例：string<br/>&nbsp;&nbsp;&nbsp;
                                        屬性變數類型<br/>&nbsp;&nbsp;&nbsp;
                    (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "範例：Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;
                                        準確的屬性名稱<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "範例：:=<br/>&nbsp;&nbsp;&nbsp;
                                        推薦的屬性的運算子<br/>&nbsp;&nbsp;&nbsp;
                    (one of: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "範例：check<br/>&nbsp;&nbsp;&nbsp;
                                        推薦的目標表<br/>&nbsp;&nbsp;&nbsp;
                    (either check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "範例：使用者的ip位址<br/>&nbsp;&nbsp;&nbsp;
                                        推薦的工具提示<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['RecommendedHelperTooltip'] = "新增屬性為<br/>&nbsp;&nbsp;&nbsp;
                                        可使用的說明函式<br/>&nbsp;&nbsp;&nbsp;";



$l['Tooltip']['AttributeEdit'] = "編輯屬性";

$l['Tooltip']['BatchDetails'] = "批次詳情";

$l['Tooltip']['UserEdit'] = "編輯使用者";
$l['Tooltip']['HotspotEdit'] = "編輯熱點";
$l['Tooltip']['EditNAS'] = "編輯NAS";
$l['Tooltip']['RemoveNAS'] = "移除NAS";
$l['Tooltip']['EditHG'] = "編輯尋線群";
$l['Tooltip']['RemoveHG'] = "移除尋線群";
$l['Tooltip']['hgNasIpAddress'] = "輸入主機/IP位址";
$l['Tooltip']['hgGroupName'] = "輸入NAS組名稱";
$l['Tooltip']['hgNasPortId'] = "輸入NAS連接埠Id";
$l['Tooltip']['EditUserGroup'] = "編輯使用者群組";
$l['Tooltip']['ListUserGroups'] = "使用者群組清單";
$l['Tooltip']['DeleteUserGroup'] = "刪除關聯使用者群組";

$l['Tooltip']['EditProfile'] = "編輯設定檔";

$l['Tooltip']['EditRealm'] = "編輯域";
$l['Tooltip']['EditProxy'] = "編輯代理";

$l['Tooltip']['EditGroup'] = "編輯組";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "如果指定的值，然後只有單一的記錄都同組名稱和指定值匹配，指定值將被移除。如果省略了值，然後所有那些特定組名稱的記錄將被移除！";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "如果指定的值，然後只有單一的記錄都同組名稱和指定值匹配，指定值將被移除。如果省略了值，然後所有那些特定組名稱的記錄將被移除！";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "（描述名稱）";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "如果指定的組，然後只有單一的記錄都同使用者名稱和組匹配，指定的將被移除。如果省略了組，然後所有那些特定的使用者名稱稱記錄將被移除！";


$l['Tooltip']['usernameTooltip'] = "準確的使用者名稱，使用者將<br/>&nbsp;&nbsp;&nbsp;
                    用來連線系統";
$l['Tooltip']['passwordTypeTooltip'] = "進行Radius使用者認證的密碼類型";
$l['Tooltip']['passwordTooltip'] = "密碼例項包含在系統裡<br/>&nbsp;&nbsp;&nbsp;
                    所以要格外小心";
$l['Tooltip']['groupTooltip'] = "使用者將被新增到這個組<br/>&nbsp;&nbsp;&nbsp;
                    透過分配一個使用者特定組<br/>&nbsp;&nbsp;&nbsp;
                    使用者必須受制於組的屬性";
$l['Tooltip']['macaddressTooltip'] = "範例：00:AA:BB:CC:DD:EE<br/>&nbsp;&nbsp;&nbsp;
                    MAC位址格式應該是相同的<br/>&nbsp;&nbsp;&nbsp;
                    隨著NAS傳送它，通常這<br/>&nbsp;&nbsp;&nbsp;
                    沒有字元";
$l['Tooltip']['pincodeTooltip'] = "範例：khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    這是準確的pin碼將作為使用者進入它<br/>&nbsp;&nbsp;&nbsp;
                    你可以使用alpha數字字元";
$l['Tooltip']['usernamePrefixTooltip'] = "範例：TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    這個使用者名稱字首會增加<br/>&nbsp;&nbsp;&nbsp;
                    產生的使用者名稱最終。";
$l['Tooltip']['instancesToCreateTooltip'] = "範例：100<br/>&nbsp;&nbsp;&nbsp;
                    使用者建立隨機的數量<br/>&nbsp;&nbsp;&nbsp;
                    用指定的個人設定檔案";
$l['Tooltip']['lengthOfUsernameTooltip'] = "範例：8<br/>&nbsp;&nbsp;&nbsp;
                    使用者名稱的字元長度<br/>&nbsp;&nbsp;&nbsp;
                    被建立。建議8-12個字元。";
$l['Tooltip']['lengthOfPasswordTooltip'] = "範例：8<br/>&nbsp;&nbsp;&nbsp;
                    密碼的字元長度<br/>&nbsp;&nbsp;&nbsp;
                    被建立。建議8-12個字元。";


$l['Tooltip']['hotspotNameTooltip'] = "範例：酒店的電吉他<br/>&nbsp;&nbsp;&nbsp;
                    一個友好的熱點名稱<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "範例：00-aa-bb-cc-dd-ee<br/>&nbsp;&nbsp;&nbsp;
                    NAS的MAC位址<br/>";

$l['Tooltip']['geocodeTooltip'] = "範例：-1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    GooleMaps位置程式碼<br/>&nbsp;&nbsp;&nbsp;
                    來PIN熱點/NAS在上（看GIS）";

$l['Tooltip']['reassignplanprofiles'] = "如果開啟,當應用使用者資訊 <br/>
                    這個個人設定檔案中顯示的個人設定檔案索引標籤將被忽略和<br/>
                    個人設定檔案將被重新分配根據方案個人設定檔案關聯";

/* ********************************************************************************** */




/* **********************************************************************************
連結和按鈕
 ************************************************************************************/

$l['button']['DashboardSettings'] = "儀表板設定";


$l['button']['GenerateReport'] = "產生報表";

$l['button']['ListPayTypes'] = "顯示付款類型";
$l['button']['NewPayType'] = "新建付款類型";
$l['button']['EditPayType'] = "編輯付款類型";
$l['button']['RemovePayType'] = "移除付款類型";
$l['button']['ListPayments'] = "顯示支付";
$l['button']['NewPayment'] = "新建支付";
$l['button']['EditPayment'] = "編輯支付";
$l['button']['RemovePayment'] = "移除支付";

$l['button']['NewUsers'] = "新建使用者";

$l['button']['ClearSessions'] = "清除工作階段";
$l['button']['Dashboard'] = "儀表板";
$l['button']['MailSettings'] = "郵件設定";

$l['button']['Batch'] = "批次";
$l['button']['BatchHistory'] = "批次歷史";
$l['button']['BatchDetails'] = "批次明細";

$l['button']['ListRates'] = "顯示率列";
$l['button']['NewRate'] = "新建率列";
$l['button']['EditRate'] = "編輯率列";
$l['button']['RemoveRate'] = "移除率列";

$l['button']['ListPlans'] = "顯示方案";
$l['button']['NewPlan'] = "新建方案";
$l['button']['EditPlan'] = "編輯方案";
$l['button']['RemovePlan'] = "移除方案";

$l['button']['ListInvoices'] = "顯示帳單";
$l['button']['NewInvoice'] = "新建帳單";
$l['button']['EditInvoice'] = "編輯帳單";
$l['button']['RemoveInvoice'] = "移除帳單";

$l['button']['ListRealms'] = "顯示域";
$l['button']['NewRealm'] = "新建域";
$l['button']['EditRealm'] = "編輯域";
$l['button']['RemoveRealm'] = "移除域";

$l['button']['ListProxys'] = "顯示代理";
$l['button']['NewProxy'] = "新建代理";
$l['button']['EditProxy'] = "編輯代理";
$l['button']['RemoveProxy'] = "移除代理";

$l['button']['ListAttributesforVendor'] = "顯示屬性";
$l['button']['NewVendorAttribute'] = "新建屬性";
$l['button']['EditVendorAttribute'] = "編輯屬性";
$l['button']['SearchVendorAttribute'] = "搜尋屬性";
$l['button']['RemoveVendorAttribute'] = "移除屬性";
$l['button']['ImportVendorDictionary'] = "匯入字典/屬性";


$l['button']['BetweenDates'] = "始末日期";
$l['button']['Where'] = "條件";
$l['button']['AccountingFieldsinQuery'] = "查詢帳單域";
$l['button']['OrderBy'] = "排序";
$l['button']['HotspotAccounting'] = "熱點帳單";
$l['button']['HotspotsComparison'] = "熱點比較";

$l['button']['CleanupStaleSessions'] = "清理過期帳單";
$l['button']['DeleteAccountingRecords'] = "刪除帳單記錄";

$l['button']['ListUsers'] = "使用者清單";
$l['button']['ListBatches'] = "顯示批次";
$l['button']['RemoveBatch'] = "移除批次";
$l['button']['ImportUsers'] = "匯入使用者";
$l['button']['NewUser'] = "新建使用者";
$l['button']['NewUserQuick'] = "新增使用者";
$l['button']['BatchAddUsers'] = "批次新增使用者";
$l['button']['EditUser'] = "編輯使用者";
$l['button']['SearchUsers'] = "搜尋使用者";
$l['button']['RemoveUsers'] = "移除使用者";
$l['button']['ListHotspots'] = "顯示熱點";
$l['button']['NewHotspot'] = "新建熱點";
$l['button']['EditHotspot'] = "編輯熱點";
$l['button']['RemoveHotspot'] = "移除熱點";

$l['button']['ListIPPools'] = "顯示IP位址池";
$l['button']['NewIPPool'] = "新建IP位址池";
$l['button']['EditIPPool'] = "編輯IP位址池";
$l['button']['RemoveIPPool'] = "移除IP位址池";

$l['button']['ListNAS'] = "顯示NAS";
$l['button']['NewNAS'] = "新建NAS";
$l['button']['EditNAS'] = "編輯NAS";
$l['button']['RemoveNAS'] = "移除NAS";
$l['button']['ListHG'] = "顯示尋線群";
$l['button']['NewHG'] = "新建尋線群";
$l['button']['EditHG'] = "編輯尋線群";
$l['button']['RemoveHG'] = "移除尋線群";
$l['button']['ListUserGroup'] = "顯示使用者群組";
$l['button']['ListUsersGroup'] = "顯示使用者群組";
$l['button']['NewUserGroup'] = "新建使用者群組";
$l['button']['EditUserGroup'] = "編輯使用者群組";
$l['button']['RemoveUserGroup'] = "移除使用者群組";

$l['button']['ListProfiles'] = "設定檔清單";
$l['button']['NewProfile'] = "新增設定檔";
$l['button']['EditProfile'] = "編輯設定檔";
$l['button']['DuplicateProfile'] = "複製設定檔";
$l['button']['RemoveProfile'] = "刪除設定檔";

$l['button']['ListGroupReply'] = "顯示群組回覆";
$l['button']['SearchGroupReply'] = "搜尋群組回覆";
$l['button']['NewGroupReply'] = "新建群組回覆";
$l['button']['EditGroupReply'] = "編輯群組回覆";
$l['button']['RemoveGroupReply'] = "移除群組回覆";

$l['button']['ListGroupCheck'] = "顯示群組檢查";
$l['button']['SearchGroupCheck'] = "搜尋群組檢查";
$l['button']['NewGroupCheck'] = "新建群組檢查";
$l['button']['EditGroupCheck'] = "編輯群組檢查";
$l['button']['RemoveGroupCheck'] = "移除群組檢查";

$l['button']['UserAccounting'] = "使用者帳單";
$l['button']['IPAccounting'] = "IP帳單";
$l['button']['NASIPAccounting'] = "NAS IP帳單";
$l['button']['NASIPAccountingOnlyActive'] = "只顯示活動";
$l['button']['DateAccounting'] = "日期帳單";
$l['button']['AllRecords'] = "所有記錄";
$l['button']['ActiveRecords'] = "活動記錄";

$l['button']['PlanUsage'] = "方案使用";

$l['button']['OnlineUsers'] = "線上使用者";
$l['button']['LastConnectionAttempts'] = "連線記錄";
$l['button']['TopUser'] = "使用者排行";
$l['button']['History'] = "歷史";

$l['button']['ServerStatus'] = "伺服器狀態";
$l['button']['ServicesStatus'] = "服務狀態";

$l['button']['daloRADIUSLog'] = "daloRADIUS日誌";
$l['button']['RadiusLog'] = "Radius日誌";
$l['button']['SystemLog'] = "系統日誌";
$l['button']['BootLog'] = "引導日誌";

$l['button']['UserLogins'] = "使用者登入";
$l['button']['UserDownloads'] = "使用者下載";
$l['button']['UserUploads'] = "使用者上傳";
$l['button']['TotalLogins'] = "總登入";
$l['button']['TotalTraffic'] = "總流量";
$l['button']['LoggedUsers'] = "使用者日誌";

$l['button']['ViewMAP'] = "顯示地圖";
$l['button']['EditMAP'] = "編輯地圖";
$l['button']['RegisterGoogleMapsAPI'] = "註冊谷歌地圖API";

$l['button']['UserSettings'] = "使用者設定";
$l['button']['DatabaseSettings'] = "資料庫設定";
$l['button']['LanguageSettings'] = "語言設定";
$l['button']['LoggingSettings'] = "日誌設定";
$l['button']['InterfaceSettings'] = "介面設定";

$l['button']['ReAssignPlanProfiles'] = "重新指派方案設定檔";

$l['button']['TestUserConnectivity'] = "測試使用者連通性";
$l['button']['DisconnectUser'] = "斷開使用者";

$l['button']['ManageBackups'] = "管理備份";
$l['button']['CreateBackups'] = "建立備份";

$l['button']['ListOperators'] = "顯示操作人";
$l['button']['NewOperator'] = "新建操作人";
$l['button']['EditOperator'] = "編輯操作人";
$l['button']['RemoveOperator'] = "移除操作人";

$l['button']['ProcessQuery'] = "查詢程序";



/*********************************************************************************** */


/***********************************************************************************
標題
在題注中文字相關的所有標題，表和指定佈局文字
************************************************************************************/

$l['title']['ImportUsers'] = "匯入使用者";


/*$l['title']['Dashboard'] = "儀表板";*/

$l['title']['Dashboard'] = "控制面板";
$l['title']['DashboardAlerts'] = "警告";

$l['title']['Invoice'] = "帳單";
$l['title']['Invoices'] = "帳單";
$l['title']['InvoiceRemoval'] = "帳單移除";
$l['title']['Payments'] = "支付";
$l['title']['Items'] = "項目";

$l['title']['PayTypeInfo'] = "支付類型資訊";
$l['title']['PaymentInfo'] = "支付資訊";


$l['title']['RateInfo'] = "價格資訊";
$l['title']['PlanInfo'] = "方案資訊";
$l['title']['TimeSettings'] = "時間設定";
$l['title']['BandwidthSettings'] = "頻寬設定";
$l['title']['PlanRemoval'] = "方案移除";

$l['title']['BatchRemoval'] = "批次移除";

$l['title']['Backups'] = "備份";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS表";
$l['title']['daloRADIUSTables'] = "daloRADIUS表";

$l['title']['IPPoolInfo'] = "IP位址池資訊";

$l['title']['BusinessInfo'] = "公司資訊";

$l['title']['CleanupRecords'] = "清除記錄";
$l['title']['DeleteRecords'] = "刪除記錄";

$l['title']['RealmInfo'] = "域資訊";

$l['title']['ProxyInfo'] = "代理資訊";

$l['title']['VendorAttribute'] = "裝置屬性";

$l['title']['AccountRemoval'] = "帳單移除";
$l['title']['AccountInfo'] = "帳單資訊";

$l['title']['Profiles'] = "設定檔";
$l['title']['ProfileInfo'] = "設定檔資訊";

$l['title']['GroupInfo'] = "組資訊";
$l['title']['GroupAttributes'] = "組屬性";

$l['title']['NASInfo'] = "NAS資訊";
$l['title']['NASAdvanced'] = "NAS高階";
$l['title']['HGInfo'] = "尋線群資訊";
$l['title']['UserInfo'] = "使用者資訊";
$l['title']['BillingInfo'] = "記帳資訊";

$l['title']['Attributes'] = "屬性";
$l['title']['ProfileAttributes'] = "設定檔屬性";

$l['title']['HotspotInfo'] = "熱點資訊";
$l['title']['HotspotRemoval'] = "熱點移除";

$l['title']['ContactInfo'] = "聯絡資訊";

$l['title']['Plan'] = "方案";

$l['title']['Profile'] = "設定檔";
$l['title']['Groups'] = "群組";
$l['title']['RADIUSCheck'] = "檢查屬性";
$l['title']['RADIUSReply'] = "回覆屬性";

$l['title']['Settings'] = "設定";
$l['title']['DatabaseSettings'] = "資料庫設定";
$l['title']['DatabaseTables'] = "資料庫表";
$l['title']['AdvancedSettings'] = "高階設定";

$l['title']['Advanced'] = "高階";
$l['title']['Optional'] = "可選";

/* ********************************************************************************** */

/* **********************************************************************************
圖表
一般圖表文字
 ************************************************************************************/
$l['graphs']['Day'] = "日";
$l['graphs']['Month'] = "月";
$l['graphs']['Year'] = "年";
$l['graphs']['Jan'] = "一月";
$l['graphs']['Feb'] = "二月";
$l['graphs']['Mar'] = "三月";
$l['graphs']['Apr'] = "四月";
$l['graphs']['May'] = "五月";
$l['graphs']['Jun'] = "六月";
$l['graphs']['Jul'] = "七月";
$l['graphs']['Aug'] = "八月";
$l['graphs']['Sep'] = "九月";
$l['graphs']['Oct'] = "十月";
$l['graphs']['Nov'] = "十一月";
$l['graphs']['Dec'] = "十二月";


/* ********************************************************************************** */

/* **********************************************************************************
文字
會在頁面使用的一般的文字資訊
 ************************************************************************************/

$l['text']['LoginRequired'] = "需要登入";
$l['text']['LoginPlease'] = "請先登入";

/* ********************************************************************************** */



/* **********************************************************************************
聯絡資訊
相關的所有聯絡資訊文字、使用者資訊、熱點所有者聯絡資訊等
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "名";
$l['ContactInfo']['LastName'] = "姓";
$l['ContactInfo']['Email'] = "電子郵件";
$l['ContactInfo']['Department'] = "部門";
$l['ContactInfo']['WorkPhone'] = "工作電話";
$l['ContactInfo']['HomePhone'] = "家庭電話";
$l['ContactInfo']['Phone'] = "電話";
$l['ContactInfo']['MobilePhone'] = "手機";
$l['ContactInfo']['Notes'] = "備註";
$l['ContactInfo']['EnableUserUpdate'] = "允許使用者更新";
$l['ContactInfo']['EnablePortalLogin'] = "允許使用者登入門戶";
$l['ContactInfo']['PortalLoginPassword'] = "設定登入密碼";

$l['ContactInfo']['OwnerName'] = "所有者姓名";
$l['ContactInfo']['OwnerEmail'] = "所有者電子郵件";
$l['ContactInfo']['ManagerName'] = "管理員姓名";
$l['ContactInfo']['ManagerEmail'] = "管理員電子郵件";
$l['ContactInfo']['Company'] = "公司";
$l['ContactInfo']['Address'] = "地址";
$l['ContactInfo']['City'] = "城市";
$l['ContactInfo']['State'] = "省份";
$l['ContactInfo']['Country'] = "國家";
$l['ContactInfo']['Zip'] = "郵編";
$l['ContactInfo']['Phone1'] = "電話1";
$l['ContactInfo']['Phone2'] = "電話2";
$l['ContactInfo']['HotspotType'] = "熱點類型";
$l['ContactInfo']['CompanyWebsite'] = "公司網站";
$l['ContactInfo']['CompanyPhone'] = "公司電話";
$l['ContactInfo']['CompanyEmail'] = "公司電子郵件";
$l['ContactInfo']['CompanyContact'] = "聯絡公司";

$l['ContactInfo']['PlanName'] = "方案名稱";
$l['ContactInfo']['ContactPerson'] = "聯絡人";
$l['ContactInfo']['PaymentMethod'] = "支付方式";
$l['ContactInfo']['Cash'] = "現金";
$l['ContactInfo']['CreditCardNumber'] = "信用卡卡號";
$l['ContactInfo']['CreditCardName'] = "信用卡名稱";
$l['ContactInfo']['CreditCardVerificationNumber'] = "信用卡驗證碼";
$l['ContactInfo']['CreditCardType'] = "信用卡類型";
$l['ContactInfo']['CreditCardExpiration'] = "信用卡有效期";

/* ********************************************************************************** */

$l['Intro']['configdashboard.php'] = "儀表板設定";



$l['Intro']['paymenttypesmain.php'] = "支付類型頁面";
$l['Intro']['paymenttypesdel.php'] = "刪除支付類型條目";
$l['Intro']['paymenttypesedit.php'] = "編輯支付類型明細";
$l['Intro']['paymenttypesnew.php'] = "新建支付類型條目";
$l['Intro']['paymenttypeslist.php'] = "支付類型表格";
$l['Intro']['paymentslist.php'] = "支付表格";
$l['Intro']['paymentsmain.php'] = "支付頁面";
$l['Intro']['paymentsdel.php'] = "刪除支付條目";
$l['Intro']['paymentsedit.php'] = "編輯支付明細";
$l['Intro']['paymentsnew.php'] = "新建支付條目";

$l['Intro']['billhistorymain.php'] = "記帳歷史";
$l['Intro']['msgerrorpermissions.php'] = "錯誤";

$l['Intro']['repnewusers.php'] = "顯示新使用者";

$l['Intro']['mngradproxys.php'] = "管理代理";
$l['Intro']['mngradproxysnew.php'] = "新建代理";
$l['Intro']['mngradproxyslist.php'] = "顯示代理";
$l['Intro']['mngradproxysedit.php'] = "編輯代理";
$l['Intro']['mngradproxysdel.php'] = "移除代理";

$l['Intro']['mngradrealms.php'] = "管理域";
$l['Intro']['mngradrealmsnew.php'] = "新建域";
$l['Intro']['mngradrealmslist.php'] = "顯示域";
$l['Intro']['mngradrealmsedit.php'] = "編輯域";
$l['Intro']['mngradrealmsdel.php'] = "移除域";

$l['Intro']['mngradattributes.php'] = "裝置屬性管理";
$l['Intro']['mngradattributeslist.php'] = "裝置的屬性清單";
$l['Intro']['mngradattributesnew.php'] = "新建裝置屬性";
$l['Intro']['mngradattributesedit.php'] = "編輯裝置屬性";
$l['Intro']['mngradattributessearch.php'] = "搜尋屬性";
$l['Intro']['mngradattributesdel.php'] = "移除裝置屬性";
$l['Intro']['mngradattributesimport.php'] = "匯入裝置字典";
$l['Intro']['mngimportusers.php'] = "匯入使用者";


$l['Intro']['acctactive.php'] = "活動記錄帳單";
$l['Intro']['acctall.php'] = "所有使用者帳單";
$l['Intro']['acctdate.php'] = "日期方式帳單";
$l['Intro']['accthotspot.php'] = "熱點帳單";
$l['Intro']['acctipaddress.php'] = "IP帳單";
$l['Intro']['accthotspotcompare.php'] = "熱點比較";
$l['Intro']['acctmain.php'] = "帳單頁面";
$l['Intro']['acctplans.php'] = "方案帳單頁面";
$l['Intro']['acctnasipaddress.php'] = "NAS IP帳單";
$l['Intro']['acctusername.php'] = "使用者帳單";
$l['Intro']['acctcustom.php'] = "客戶帳單";
$l['Intro']['acctcustomquery.php'] = "客戶查詢帳單";
$l['Intro']['acctmaintenance.php'] = "帳單記錄維護";
$l['Intro']['acctmaintenancecleanup.php'] = "刪除過期帳單";
$l['Intro']['acctmaintenancedelete.php'] = "刪除帳單記錄";

$l['Intro']['billmain.php'] = "記帳頁面";
$l['Intro']['ratesmain.php'] = "價格記帳頁面";
$l['Intro']['billratesdate.php'] = "價格預付帳單";
$l['Intro']['billratesdel.php'] = "移除利率條目";
$l['Intro']['billratesedit.php'] = "編輯利率資訊";
$l['Intro']['billrateslist.php'] = "帳單利率表";
$l['Intro']['billratesnew.php'] = "新建利率清單";

$l['Intro']['paypalmain.php'] = "PayPal交易頁面";
$l['Intro']['billpaypaltransactions.php'] = "PayPal交易頁面";

$l['Intro']['billhistoryquery.php'] = "記帳歷史";

$l['Intro']['billinvoice.php'] = "會計帳單";
$l['Intro']['billinvoicedel.php'] = "刪除帳單條目";
$l['Intro']['billinvoiceedit.php'] = "編輯帳單";
$l['Intro']['billinvoicelist.php'] = "顯示帳單";
$l['Intro']['billinvoicereport.php'] = "發票報表";
$l['Intro']['billinvoicenew.php'] = "新建帳單";

$l['Intro']['billplans.php'] = "記帳方案頁面";
$l['Intro']['billplansdel.php'] = "刪除方案條目";
$l['Intro']['billplansedit.php'] = "編輯方案明細";
$l['Intro']['billplanslist.php'] = "方案表";
$l['Intro']['billplansnew.php'] = "新建方案條目";

$l['Intro']['billpos.php'] = "銷售頁面的記帳點";
$l['Intro']['billposdel.php'] = "刪除使用者";
$l['Intro']['billposedit.php'] = "編輯使用者";
$l['Intro']['billposlist.php'] = "顯示使用者";
$l['Intro']['billposnew.php'] = "新建使用者";

$l['Intro']['giseditmap.php'] = "編輯地圖模式";
$l['Intro']['gismain.php'] = "GIS 地圖";
$l['Intro']['gisviewmap.php'] = "V檢視地圖模式";

$l['Intro']['graphmain.php'] = "使用圖表";
$l['Intro']['graphsalltimetrafficcompare.php'] = "總流量使用比較";
$l['Intro']['graphsalltimelogins.php'] = "總登入";
$l['Intro']['graphsloggedusers.php'] = "已登入使用者";
$l['Intro']['graphsoveralldownload.php'] = "使用者下載";
$l['Intro']['graphsoveralllogins.php'] = "使用者登入";
$l['Intro']['graphsoverallupload.php'] = "使用者上傳";

$l['Intro']['rephistory.php'] = "活動歷史";
$l['Intro']['replastconnect.php'] = "最後嘗試連線";
$l['Intro']['repstatradius.php'] = "常駐程式資訊";
$l['Intro']['repstatserver.php'] = "伺服器狀態和資訊";
$l['Intro']['reponline.php'] = "顯示線上使用者";
$l['Intro']['replogssystem.php'] = "系統日誌檔案";
$l['Intro']['replogsradius.php'] = "RADIUS伺服器日誌檔案";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS日誌檔案";
$l['Intro']['replogsboot.php'] = "Boot日誌檔案";
$l['Intro']['replogs.php'] = "日誌";
$l['Intro']['rephb.php'] = "心跳";
$l['Intro']['rephbdashboard.php'] = "daloRADIUS NAS儀表板";
$l['Intro']['repbatch.php'] = "批次";
$l['Intro']['mngbatchlist.php'] = "批次工作階段清單";
$l['Intro']['repbatchlist.php'] = "批次使用者清單";
$l['Intro']['repbatchdetails.php'] = "批次明細";

$l['Intro']['rephsall.php'] = "熱點清單";
$l['Intro']['repmain.php'] = "報表頁面";
$l['Intro']['repstatus.php'] = "狀態頁面";
$l['Intro']['reptopusers.php'] = "使用者使用詳情";
$l['Intro']['repusername.php'] = "使用者清單";

$l['Intro']['mngbatchdel.php'] = "刪除批次工作階段";

$l['Intro']['mngdel.php'] = "移除使用者";
$l['Intro']['mngedit.php'] = "編輯使用者明細";
$l['Intro']['mnglistall.php'] = "使用者清單";
$l['Intro']['mngmain.php'] = "使用者和熱點管理";
$l['Intro']['mngbatch.php'] = "批次使用者管理";
$l['Intro']['mngnew.php'] = "新建使用者";
$l['Intro']['mngnewquick.php'] = "快速新增使用者";
$l['Intro']['mngsearch.php'] = "搜尋使用者";

$l['Intro']['mnghsdel.php'] = "移除熱點";
$l['Intro']['mnghsedit.php'] = "編輯熱點明細";
$l['Intro']['mnghslist.php'] = "顯示熱點";
$l['Intro']['mnghsnew.php'] = "新建熱點";

$l['Intro']['mngradusergroupdel.php'] = "移除使用者群組對應";
$l['Intro']['mngradusergroup.php'] = "使用者群組設定";
$l['Intro']['mngradusergroupnew.php'] = "新建使用者群組對應";
$l['Intro']['mngradusergrouplist'] = "資料庫使用者群組對應";
$l['Intro']['mngradusergrouplistuser'] = "資料庫使用者群組對應";
$l['Intro']['mngradusergroupedit'] = "編輯使用者群組對應";

$l['Intro']['mngradippool.php'] = "IP位址池設定";
$l['Intro']['mngradippoolnew.php'] = "新建IP位址池";
$l['Intro']['mngradippoollist.php'] = "顯示IP位址池";
$l['Intro']['mngradippooledit.php'] = "編輯IP位址池";
$l['Intro']['mngradippooldel.php'] = "移除IP位址池";

$l['Intro']['mngradnas.php'] = "NAS設定";
$l['Intro']['mngradnasnew.php'] = "新建NAS記錄";
$l['Intro']['mngradnaslist.php'] = "NAS資料庫清單";
$l['Intro']['mngradnasedit.php'] = "編輯NAS記錄";
$l['Intro']['mngradnasdel.php'] = "移除NAS記錄";

$l['Intro']['mngradhunt.php'] = "尋線群設定";
$l['Intro']['mngradhuntnew.php'] = "新建尋線群記錄";
$l['Intro']['mngradhuntlist.php'] = "資料庫尋線群清單";
$l['Intro']['mngradhuntedit.php'] = "編輯尋線群記錄";
$l['Intro']['mngradhuntdel.php'] = "移除尋線群記錄";

$l['Intro']['mngradprofiles.php'] = "設定檔清單";
$l['Intro']['mngradprofilesedit.php'] = "編輯設定檔";
$l['Intro']['mngradprofilesduplicate.php'] = "複製設定檔";
$l['Intro']['mngradprofilesdel.php'] = "刪除設定檔";
$l['Intro']['mngradprofileslist.php'] = "設定檔清單";
$l['Intro']['mngradprofilesnew.php'] = "新增設定檔";

$l['Intro']['mngradgroups.php'] = "設定組";

$l['Intro']['mngradgroupreplynew.php'] = "新建群組回覆對應";
$l['Intro']['mngradgroupreplylist.php'] = "資料庫群組回覆對應";
$l['Intro']['mngradgroupreplyedit.php'] = "編輯群組回覆對應";
$l['Intro']['mngradgroupreplydel.php'] = "移除群組回覆對應";
$l['Intro']['mngradgroupreplysearch.php'] = "搜尋群組回覆對應";

$l['Intro']['mngradgroupchecknew.php'] = "新建群組檢查對應";
$l['Intro']['mngradgroupchecklist.php'] = "資料庫群組檢查對應";
$l['Intro']['mngradgroupcheckedit.php'] = "編輯群組檢查對應";
$l['Intro']['mngradgroupcheckdel.php'] = "移除群組檢查對應";
$l['Intro']['mngradgroupchecksearch.php'] = "搜尋群組檢查對應";

$l['Intro']['configuser.php'] = "設定使用者";
$l['Intro']['configmail.php'] = "設定郵件";

$l['Intro']['configdb.php'] = "設定資料庫";
$l['Intro']['configlang.php'] = "設定語言";
$l['Intro']['configlogging.php'] = "設定日誌";
$l['Intro']['configinterface.php'] = "設定Web介面";
$l['Intro']['configmainttestuser.php'] = "測試使用者連通性";
$l['Intro']['configmain.php'] = "設定資料庫";
$l['Intro']['configmaint.php'] = "維護";
$l['Intro']['configmaintdisconnectuser.php'] = "斷開使用者";
$l['Intro']['configbusiness.php'] = "公司明細";
$l['Intro']['configbusinessinfo.php'] = "公司資訊";
$l['Intro']['configbackup.php'] = "備份";
$l['Intro']['configbackupcreatebackups.php'] = "建立備份";
$l['Intro']['configbackupmanagebackups.php'] = "管理備份";

$l['Intro']['configoperators.php'] = "設定操作人";
$l['Intro']['configoperatorsdel.php'] = "移除操作人";
$l['Intro']['configoperatorsedit.php'] = "編輯操作人設定";
$l['Intro']['configoperatorsnew.php'] = "新建操作人";
$l['Intro']['configoperatorslist.php'] = "操作人清單";

$l['Intro']['login.php'] = "登入";

$l['captions']['providebillratetodel'] = "提供你想去除的價格類型條目";
$l['captions']['detailsofnewrate'] = "可以填充下面新建價格的明細";
$l['captions']['filldetailsofnewrate'] = "填充下面新建價格條目的明細";

/* **********************************************************************************
 * 說明頁面資訊
 *每個頁面都有一個標題是前奏類的標題，當點選
 *它會顯示/隱藏helpPage格的內容是具體的描述
 *頁，基本上你的擴充套件工具提示。
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "控制檯設定";


$l['helpPage']['repnewusers'] = "下拉表顯示了每個月建立的新使用者.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "顯示所有支付寶交易";
$l['helpPage']['billhistoryquery'] = "顯示所有使用者計費歷史(年代)";

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

$l['helpPage']['msgerrorpermissions'] = "你沒有權限存取該頁面。<br/>
請諮詢您的系統管理員。 <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "為了從資料庫中刪除使用者條目，您必須提供帳戶的使用者名稱";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Profiles Management</b> - 管理使用者設定檔透過組合一群組回覆並檢查屬性 <br/>
設定檔案可以被認為是組織構成的答覆和檢查組的組成。<br/>
<h200><b>設定檔清單 </b></h200> - List Profiles <br/>
<h200><b>新增設定檔 </b></h200> - Add a Profile <br/>
<h200><b>編輯設定檔 </b></h200> - Edit a Profile <br/>
<h200><b>刪除設定檔 </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>編輯個人資料</b></h200> - 編輯個人資料 <br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>刪除組設定 </b></h200> - 刪除設定檔資料<br/>
";
$l['helpPage']['mngradprofilesduplicate'] = "
<h200><b>複製檔案 </b></h200> - 複製一個概要檔案的屬性設定為一個新建不同的設定檔名稱 <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>設定檔清單 </b></h200> - 設定檔清單 <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>新增設定檔</b></h200> - 新增一個設定檔案 <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>組織管理</b> - 管理組織回覆和組織檢查(radgroupreply/radgroupcheck tables).<br/>
<h200><b>回覆/檢視清單組 </b></h200> - 回覆/檢視錶組<br/>
<h200><b>搜尋群組回覆/檢視 </b></h200> - 搜尋一群組回覆/檢視(你可以使用萬用字元) <br/>
<h200><b>新群組回覆/檢視 </b></h200> - 新增一群組回覆/檢查 <br/>
<h200><b>編輯群組回覆/檢視 </b></h200> - 編輯一群組回覆/檢視地圖<br/>
<h200><b>刪除群組回覆/檢視 </b></h200> - 刪除一個回覆/檢視地圖 <br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>新群組檢查 </b></h200> - 新增一個檢查組 <br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>刪除群組檢查 </b></h200> - 刪除一群組檢查 <br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>組織檢查清單 </b></h200> - 組清單檢查 <br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>編輯群組檢查 </b></h200> - 編輯檢查組 <br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>搜尋群組檢查 </b></h200> - 搜尋一群組檢查 <br/>
使用萬用字元，你既可以鍵入 ‘％’ 字元是在熟悉SQL，或者您可以使用更常見‘*’
為方便起見，並daloRADIUS將它翻譯成‘％’
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>新群組回覆 </b></h200> - 新增一組回答的 <br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>刪除群組回覆</b></h200> - 刪除一組回答的 <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>清單群組回覆</b></h200> - 群組回覆清單<br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>編輯組回答 </b></h200> - 編輯回答一組 <br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>搜尋組的回覆</b></h200> - 搜尋群群組回覆對應 <br/>
使用萬用字元，你既可以鍵入 ‘％’ 字元是在熟悉SQL，或者您可以使用更常見‘*’
為方便起見，並daloRADIUS將它翻譯成‘％’
";


$l['helpPage']['mngradippool'] = "
<h200><b>IP位址池清單</b></h200> - 清單設定IP位址池及其分配IP位址 <br/>
<h200><b>新建IP位址池</b></h200> - 新增一個新建IP位址設定IP位址池 <br/>
<h200><b>編輯IP位址池</b></h200> - 編輯一個IP位址設定IP位址池 <br/>
<h200><b>刪除IP位址池</b></h200> - 刪除一個IP位址從一個設定IP位址池 <br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>IP位址池清單</b></h200> - 清單設定IP位址池及其分配IP位址 <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>新建IP位址池</b></h200> - 新增一個新建IP位址設定IP位址池 <br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>編輯IP位址池</b></h200> - 編輯一個IP位址設定IP位址池 <br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>刪除IP位址池</b></h200> - 刪除一個IP位址從一個設定IP位址池 <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "刪除一個nas ip /從資料庫主機條目必須提供的ip /主機帳戶";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

$l['helpPage']['mngradhunt'] = "HuntGroup開始工作之前,請閱讀 <a href='http://wiki.freeradius.org/SQL_Huntgroup_HOWTO' target='_blank'>http://wiki.freeradius.org/SQL_Huntgroup_HOWTO</a>.
<br/>
特別是:
...
<i>找到你的radiusd.conf或網站功能/ defaut設定檔案中的授權部分和編輯它。在預處理模組後，授權部分的頂部插入這些行：</i>
<br/>
<pre>
update request {
    Huntgroup-Name := \"%{sql:select groupname from radhuntgroup where nasipaddress=\\\"%{NAS-IP-Address}\\\"}\"
}
</pre>
<i> 這是使用IP位址作為回報huntgroup名字中的一個重要radhuntgroup表中執行查詢。然後新增一個屬性/值對該請求的屬性名稱是huntgroup的名字和它的值就是從SQL查詢返回的。如果查詢沒有發現任何值是空字串。 </i>";


$l['helpPage']['mngradhuntdel'] = "從資料庫中刪除組條目必須提供的ip /主機和連接埠id";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

$l['helpPage']['mnghsdel'] = "從資料庫中刪除一個熱點必須提供熱點的名稱<br/>";
$l['helpPage']['mnghsedit'] = "您可以編輯以下細節熱點<br/>";
$l['helpPage']['mnghsnew'] = "您可以填寫以下細節的新熱點除了資料庫";
$l['helpPage']['mnghslist'] = "資料庫中的所有熱點的清單。您可以使用快速連結來編輯或刪除資料庫中的一個熱點。";

$l['helpPage']['configdb'] = "
<b>資料庫設定</b> - 設定資料庫引擎，連線設定，表名，如果
預設沒有被使用，並在資料庫中的密碼加密類型.<br/>
<h200><b>全域性設定</b></h200> - 資料庫儲存引擎<br/>
<h200><b>表設定</b></h200> - 如果不使用預設FreeRADIUS模式你可以改變名字
表的名稱<br/>
<h200><b>高階設定</b></h200> - 你想在資料庫中儲存使用者的密碼不在是
純文字,而是讓它以某種方式你可以選擇一個MD5或加密<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>語言設定</b></h200> - 設定介面語言<br/>
";
$l['helpPage']['configuser'] = "
<h200><b>使用者設定</b></h200> - 設定使用者管理行為。<br/>
";
$l['helpPage']['configmail'] = "
<h200><b>使用者設定</b></h200> - 設定郵件設定。<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>日誌設定</b></h200> - 設定日誌規則和設施 <br/>
請確保您指定的檔名寫權限的網頁伺服器<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>介面設定</b></h200> - 設定介面佈局設定和behvaiour <br/>
";
$l['helpPage']['configmain'] = "
<b>全域性設定</b><br/>
<h200><b>資料庫設定</b></h200> - 設定資料庫引擎，連線設定，表名，如果
預設沒有被使用，並在資料庫中的密碼加密的類型。<br/>
<h200><b>語言設定</b></h200> - 設定介面語言。<br/>
<h200><b>語言設定</b></h200> - 設定日誌記錄的規則和設施 <br/>
<h200><b>介面設定</b></h200> - 設定介面佈局設定和behvaiour <br/>

<b>子類設定</b>
<h200><b>維護</b></h200> - 維護選項用於測試使用者連線或終止工作階段 <br/>
<h200><b>裝置</b></h200> - 裝置設定存取控制清單(ACL) <br/>
";
$l['helpPage']['configbusiness'] = "
<b>業務資訊</b><br/>
<h200><b>業務聯絡</b></h200> - 設定業務聯絡人資訊(所有者、標題、地址、電話等)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b>維護</b><br/>
<h200><b>測試使用者連線</b></h200> - 傳送一個存取請求的RADIUS伺服器檢查使用者憑證是有效的<br/>
<h200><b>斷開連線的使用者</b></h200> - 發出一個POD（包斷開連線）或CoA（改變權限）的封包NAS伺服器
要斷開使用者並在一個特定的NAS終止他/她工作階段。<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>測試使用者連線</b></h200> - RADIUS伺服器的存取請求傳送給檢查使用者憑證是否有效。<br/>
ddaloradius使用RADIUS用戶端二進位實用程式來執行測試並返回命令結果完成後。 <br/>
daloRADIUS計數的RADIUS用戶端的二進位檔案在\$ PATH環境變數可用，如果不是，請
更正庫/extensions/maintenance_radclient.php 檔案<br/><br/>

請注意，它可能需要一段時間的測試完成（幾秒[ 10-20秒左右]）由於故障和
radclient將重發的封包。

在“高階”索引標籤可以調整測試選項：<br/>
逾時等待逾時秒後重試（可能是一個浮點數）<br/>
如果逾時重試，重試傳送該封包的重試的次數。<br/>
計數傳送每個封包的數倍<br/>
從並行檔案請求傳送的封包數<br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>斷開使用者</b></h200> - 發出一個POD（包斷開連線）或CoA（改變權限）的封包NAS伺服器
要斷開使用者並在一個特定的NAS終止他/她工作階段。<br/>
終止使用者工作階段，要求在NAS支援POD或AOC包類型，請諮詢您的NAS裝置或
文件這一點。此外，它需要知道在NAS連接埠POD或AOC封包，而較新建NAS的使用連接埠3799
而其他的被設定成接收在連接埠1700的封包。

ddaloradius使用RADIUS用戶端二進位實用程式來執行測試並返回命令結果完成後。 <br/>
daloRADIUS計數的RADIUS用戶端的二進位檔案在\$ PATH環境變數可用，如果不是，請
更正庫/extensions/maintenance_radclient.php 檔案<br/><br/

請注意，它可能需要一段時間的測試完成（幾秒[ 10-20秒左右]）由於故障和
radclient將重發的封包。

在“高階”索引標籤可以調整測試選項：<br/>
逾時等待逾時秒後重試（可能是一個浮點數）<br/>
如果逾時重試，重試傳送該封包的重試的次數。<br/>
計數傳送每個封包的數倍<br/>
從並行檔案請求傳送的封包數<br/>


";
$l['helpPage']['configoperatorsdel'] = "從資料庫中刪除的操作員必須提供使用者名稱。";
$l['helpPage']['configoperatorsedit'] = "下面編輯裝置使用者詳細資訊";
$l['helpPage']['configoperatorsnew'] = "你可以填寫下面的一個新建裝置的使用者除了資料庫的詳細資訊";
$l['helpPage']['configoperatorslist'] = "顯示所有裝置的資料庫";
$l['helpPage']['configoperators'] = "裝置的設定";
$l['helpPage']['configbackup'] = "執行備份";
$l['helpPage']['configbackupcreatebackups'] = "建立備份";
$l['helpPage']['configbackupmanagebackups'] = "管理備份";


$l['helpPage']['graphmain'] = "
<b>圖表</b><br/>
<h200><b>總體登入/點選</b></h200> - 繪製的每一段時間內的特定使用者的使用情況圖表。
所有登入 （或 '點選' 到 NAS） 是透過圖形方式顯示以及表格清單。<br/>
<h200><b>總下載統計</b></h200> - 繪製的每一段時間內的特定使用者的使用情況圖表
由用戶端下載的資料量是正在被計算的值。該圖伴隨下載量即時顯示<br/>
<h200><b>總體上傳統計</b></h200> - 繪製的每一段時間內的特定使用者的使用情況圖表。
由用戶端上傳的資料量是正在被計算的值。該圖伴隨上傳量即時顯示<br/>
<br/>
<h200><b>所有時間登入/點選</b></h200> - 繪出登入到伺服器上的給定時間週期的圖形圖表。<br/>
<h200><b>所有流量對比</b></h200> - 繪製圖表的下載和上傳 統計資料。<br/>
<h200><b>登入使用者</b></h200> - 繪製指定期間中的登入的使用者的圖表
按天、 月、 年僅按月份和年份圖每小時圖或篩選器篩選 （選擇 \"---\"天） 圖的最小和最大登入的使用者在所選的一個月.
";
$l['helpPage']['graphsalltimelogins'] = "登入到伺服器的歷史統計資料基於分佈在一段時間內";
$l['helpPage']['graphsalltimetrafficcompare'] = "透過伺服器基於分佈在一段時間內流量資料統計。";
$l['helpPage']['graphsloggedusers'] = "繪製已登入的總的圖表";
$l['helpPage']['graphsoveralldownload'] = "繪製圖表伺服器的已下載位元組數";
$l['helpPage']['graphsoverallupload'] = "繪製圖表的上傳到伺服器的位元組";
$l['helpPage']['graphsoveralllogins'] = "繪製圖表對伺服器的登入嘗試";



$l['helpPage']['rephistory'] = "顯示所有活動執行管理項目和提供資訊<br/>
建立日期,建立和更新日期和更新歷史領域";
$l['helpPage']['replastconnect'] = "顯示所有RADIUS伺服器的登入嘗試,成功和失敗的登入";
$l['helpPage']['replogsboot'] = "監控作業系統啟動日誌——相當於執行dmesg命令。";
$l['helpPage']['replogsdaloradius'] = "監控daloRADIUS的日誌檔案";
$l['helpPage']['replogsradius'] = "監控FreeRADIUS的日誌檔案。";
$l['helpPage']['replogssystem'] = "監控作業系統日誌檔案。";
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "提供了一個活躍使用者的這批例項的清單";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS日誌</b></h200> - 監控daloRADIUS的日誌檔案。<br/>
<h200><b>RADIUS日誌</b></h200> - 監控FreeRADIUS的日誌檔案,在 /var/log/freeradius/radius.log 或 /usr/local/var/log/radius/radius.log.
日誌檔案可能在其他可能的地方,如果是這樣的話請相應地調整設定.<br/>
<h200><b>系統日誌</b></h200> - 監控作業系統日誌檔案,在 /var/log/syslog or /var/log/訊息在大多數平臺上。
日誌檔案可能在其他可能的地方,如果是這樣的話請相應地調整設定。<br/>
<h200><b>Boot Log</b></h200> - 監控作業系統啟動日誌——相當於執行dmesg命令。
";
$l['helpPage']['repmain'] = "
<b>一般報表</b><br/>
<h200><b>線上使用者</b></h200> - 提供了一個清單的所有使用者
發現線上透過會計表在資料庫中。為使用者正在執行的檢查
沒有結束時間(AcctStopTime)。重要的是要注意,這些使用者也會過期的工作階段
這當NASs由於某種原因未能傳送accounting-stop包。.<br/>
<h200><b>Last Connection Attempts</b></h200> - 提供所有Access-Accept的清單和Access-Reject(接受和失敗)登入為使用者。 <br/>
這些從資料庫的postauth表需要定義FreeRADIUS設定檔案的實際記錄這些.<br/>
<h200><b>使用者使用詳情</b></h200> - 提供了一個清單的前N使用者頻寬消耗和工作階段時間使用。<br/><br/>
<b>子分類報表</b><br/>
<h200><b>Logs</b></h200> - 提供daloRADIUS日誌檔案、FreeRADIUSs日誌檔案系統的日誌檔案和啟動日誌檔案<br/>
<h200><b>Status</b></h200> - 提供伺服器狀態資訊和RADIUS元件狀態";
$l['helpPage']['repstatradius'] = "提供關於伺服器本身的一般資訊:CPU使用率,流程,正常執行時間、記憶體使用情況,等等";
$l['helpPage']['repstatserver'] = "提供關於FreeRADIUS常駐程式的一般資訊和MySQL資料庫伺服器";
$l['helpPage']['repstatus'] = "<b>狀態</b><br/>
<h200><b>伺服器狀態</b></h200> - 提供關於伺服器本身的一般資訊:CPU使用率,流程,正常執行時間、記憶體使用情況,等等。<br/>
<h200><b>RADIUS 狀態</b></h200> - 提供關於FreeRADIUS常駐程式的一般資訊和MySQL資料庫伺服器";
$l['helpPage']['reptopusers'] = "下面顯示記錄為高階使用者,那些獲得了最高消費的工作階段
時間和頻寬使用情況。清單的使用者類別: ";
$l['helpPage']['repusername'] = "記錄發現的使用者:";
$l['helpPage']['reponline'] = "
下表顯示了目前連線使用者
系統。非常有可能,有陳舊的連線,
這意味著使用者斷線但NAS沒有傳送或不是
能夠傳送停止會計包RADIUS伺服器。";

$l['helpPage']['mnglistall'] = "清單中的使用者資料庫";
$l['helpPage']['mngsearch'] = "搜尋使用者： ";
$l['helpPage']['mngnew'] = "您可以填寫以下資訊新使用者除了資料庫<br/>";
$l['helpPage']['mngedit'] = "編輯下面的使用者詳細資訊<br/>";
$l['helpPage']['mngdel'] = "為了從資料庫中刪除使用者條目，你必須提供帳戶的使用者名稱<br/>";
$l['helpPage']['mngbatch'] = "您可以填寫以下資訊新使用者除了資料庫。<br/>
請注意，這些設定將適用於所有你所建立的使用者。<br/>";
$l['helpPage']['mngnewquick'] = "下面的使用者/卡是預付費類型。<br/>
在時間信用證規定的時間內將被用作 Session-Timeout（工作階段逾時） 和 Max-All-Session（最大-所有-工作階段） RADIUS屬性";

// 帳單部分
$l['helpPage']['acctactive'] = "
    規定，將被證明是用於跟蹤活動或過期的資料庫中的使用者有用的資訊
其中有一個到期屬性或馬克斯 - 所有工作階段屬性的使用者而言。
<br/>
";
$l['helpPage']['acctall'] = "
    為資料庫中的所有工作階段的完整的會計資訊。
<br/>
";
$l['helpPage']['acctdate'] = "
    為給定的2日期為特定使用者之間的所有工作階段完整的會計資訊。
<br/>
";
$l['helpPage']['acctipaddress'] = "
    為起源與特定IP位址的所有工作階段的完整的會計資訊。
<br/>
";

$l['helpPage']['acctplans'] = "";
$l['helpPage']['acctmain'] = "
<b>General Accounting</b><br/>
<h200><b>User Accounting</b></h200> -
    為資料庫中的一個特定使用者的所有工作階段的完整的會計資訊。
<br/>
<h200><b>IP Accounting</b></h200> -
    為起源與特定IP位址的所有工作階段的完整的會計資訊。
<br/>
<h200><b>NAS Accounting</b></h200> -
    為所有的特定NAS的IP位址已辦理了全面的工作階段計費資訊。
<br/>
<h200><b>Date Accounting</b></h200> -
    Provides對於給定的2日期為特定使用者之間的所有工作階段完整的會計資訊。
<br/>
<h200><b>All Accounting Records</b></h200> -
    為資料庫中的所有工作階段的完整的會計資訊。
<br/>
<h200><b>Active Records Accounting</b></h200> -
    規定，將被證明是用於跟蹤活動或過期的資料庫中的使用者有用的資訊
其中有一個到期屬性或 Max-All-Session（最大-所有-工作階段）屬性的使用者而言。
<br/>

<br/>
<b>Sub-Category Accounting</b><br/>
<h200><b>Custom</b></h200> -
    提供了最靈活的自訂查詢到資料庫上執行。
<br/>
<h200><b>Hotspots</b></h200> -
    提供不同的管理熱點資訊、比較,和其他有用的資訊。
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    提供完整的會計資訊的所有工作階段的具體處理NAS IP位址。
<br/>
";
$l['helpPage']['acctusername'] = "
    提供完整的會計資訊對特定使用者的資料庫中的所有工作階段。
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    提供完整的會計資訊的所有工作階段起源於這個特定的熱點。
這個清單是計算清單隻有那些與CalledStationId radacct表中的記錄
欄位匹配一個熱點中的熱點的MAC位址條目的管理資料庫。
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    提供了基本的會計資訊比較資料庫中找到的所有活躍的熱點。
       會計提供的資訊:<br/> <br/>
    熱點名稱——熱點的名稱<br/>
    獨特的使用者-使用者已登入,只有透過這個熱點<br/>
    總點選——總登入,進行從這個熱點(獨特的和非獨特的)<br/>
    平均時間——平均時間使用者花在這個熱點<br/>
    總時間——所有使用者的accumolated花時間在這個熱點<br/>

<br/>
    提供了一個圖塊不同的比較了<br/>
    圖:<br/> <br/>
    每個熱點分佈的獨特使用者<br/>
    分配每個熱點的點選<br/>
    每個熱點分佈的時間使用 <br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    提供完整的會計資訊的所有工作階段起源於這個特定的熱點。
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    提供了基本的會計資訊比較資料庫中找到的所有活躍的熱點。
提供了一個圖塊不同的比較。
<br/>
";
// 會計自訂查詢部分
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    提供最靈活的自訂查詢資料庫上執行。<br/>
你可以調整查詢的max透過修改設定在左側欄。<br/>
<br/>
    <b> 日期</b> -設定開始和結束日期.
<br/>
    <b> </b>——設定資料庫中的欄位(像一個鍵)你想匹配,選擇如果值
比賽應該等於(=)或它包含你搜尋的一部分價值(如一個正規表示式)。如果你
選擇使用包含運算子你不應該新增任何常見的萬用字元“*”而是
您輸入的值將自動搜尋這種形式:* *價值(或mysql風格:%值%)。
<br/>
    <b> </b>查詢會計領域,你可以選擇你想要的欄位出現在結果中
清單。
<br/>
<b> </b>訂單——選擇你想訂場的結果和它的類型(提升
或降序)
<br/>
";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200><b>清理過期工作階段</b></h200> -
    ‘過期工作階段’可能經常存在因為會影響NAS無法提供計費停止紀錄<<br/>
    如不不清理長時間的過期使用者工作階段，會導致假的使用者登入記錄的存在
    記錄 (false positive).
<br/>
<h200><b>刪除會計記錄</b></h200> -
    刪除資料庫中的會計記錄。要執行該操作，或者要允許其他使用者。
    除了管理員存取這個頁面。
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
    編輯地圖模式，在這種模式下你可以簡單地透過點選新增或刪除熱點
在地圖上的位置或透過點選一個熱點（分別）<br/><br/>
    <b> 新增熱點 </b> - 只需點選一個清晰的地圖上的位置,你將提供
熱點的名稱和它的MAC位址。這些關鍵細節後用於識別這個熱點
在會計表中。務必提供正確的MAC位址！
<br/><br/>
    <b> 刪除熱點 </b> - 只需點選一個熱點的圖示，你確定它刪除從
資料庫。
<br/>
";
$l['helpPage']['gisviewmap'] = "
檢視地圖模式-在此模式下你可以瀏覽他們的熱點進行佈局
在利用GoogleMaps服務提供的地圖圖示。<br/><br/>

    <b> 點選一個熱點 </b> -將提供您更深入的細節上的熱點。
    如聯絡資訊的熱點，統計資訊。
<br/>
";
$l['helpPage']['gismain'] = "
<b> 一般資訊 </b>
GIS熱點位置的提供了視覺化世界各地的地圖使用Google Maps API。<br/>
在管理頁面你可以向資料庫新增新建熱點條目,那裡也是一個欄位
稱為地理位置,這是Google Maps API使用以有定位的準確數值
位置在地圖上的熱點。<br/><br/>

<h200><b>2 提供的操作模式:</b></h200>
一個是<b>檢視地圖</b>模式使“網上衝浪”透過世界地圖
檢視目前位置的熱點在資料庫和另一個<b>編輯地圖</b> -該模式
一個可以使用以建立熱點的直觀簡單的左點選地圖或刪除
現有的熱點條目，左鍵點選現有熱點的標誌。.<br/><br/>

另一個重要的問題是,網路上的每臺計算機需要一個獨特的註冊碼,你
從Google Maps API頁面可以獲得透過提供完整的web託管目錄的地址嗎
daloRADIUS伺服器上的應用程式。一旦你從谷歌獲得程式碼,只需貼上的
註冊框,然後點選“註冊碼”按鈕來寫它。
然後你可以使用谷歌地圖服務。 <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "這個使用者沒有檢查相關聯的屬性";
$l['messages']['noReplyAttributesForUser'] = "這個使用者沒有回複相關聯的屬性";

$l['messages']['noCheckAttributesForGroup'] = "這個組沒有檢查相關聯的屬性";
$l['messages']['noReplyAttributesForGroup'] = "這個組沒有回複相關聯的屬性";

$l['messages']['nogroupdefinedforuser'] = "這個使用者沒有相關聯的組";
$l['messages']['wouldyouliketocreategroup'] = "你想建立一個？";


$l['messages']['missingratetype'] = "錯誤：缺失價格類型";
$l['messages']['missingtype'] = "錯誤：丟失類型";
$l['messages']['missingcardbank'] = "錯誤：丟失銀行卡";
$l['messages']['missingrate'] = "錯誤：丟失價格";
$l['messages']['success'] = "成功";
$l['messages']['gisedit1'] = "歡迎,你目前在編輯模式";
$l['messages']['gisedit2'] = "從地圖和資料庫刪除目前標記?";
$l['messages']['gisedit3'] = "請輸入熱點的名稱";
$l['messages']['gisedit4'] = "新增目前標記到資料庫嗎?";
$l['messages']['gisedit5'] = "請輸入熱點的名稱";
$l['messages']['gisedit6'] = "請輸入MAC熱點的地址";

$l['messages']['gismain1'] = "成功更新谷歌地圖API註冊碼";
$l['messages']['gismain2'] = "錯誤:無法開啟檔案寫入";
$l['messages']['gismain3'] = "檢查檔案的權限。這個檔案應該是網頁伺服器的使用者/組可寫的。";
$l['messages']['gisviewwelcome'] = "歡迎來到Enginx視覺地圖";

$l['messages']['loginerror'] = "<br/><br/>下面之一：<br/>
1. 錯誤的使用者名稱/密碼<br/>
2. 管理員已經登入的（只允許一個例項）<br/>
3. 似乎有不止一個的管理員的使用者在資料庫中<br/>
";

$l['buttons']['savesettings'] = "儲存設定";
$l['buttons']['apply'] = "應用";

$l['menu']['Home'] = "首頁";
$l['menu']['Managment'] = "管理";
$l['menu']['Reports'] = "報表";
$l['menu']['Accounting'] = "連線紀錄";
$l['menu']['Billing'] = "帳務";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "圖表";
$l['menu']['Config'] = "設定";
$l['menu']['Help'] = "說明";

// sidebar menu titles, section headings, link labels and form captions
// (see app/operators/include/menu/sidebar/)
$l['sidebar']['Accounting'] = "連線紀錄";
$l['sidebar']['AttributesManagement'] = "屬性管理";
$l['sidebar']['BackupSettings'] = "備份設定";
$l['sidebar']['BatchManagement'] = "批次管理";
$l['sidebar']['BatchUsers'] = "批次使用者";
$l['sidebar']['Billing'] = "帳務";
$l['sidebar']['CRONStatus'] = "CRON 狀態";
$l['sidebar']['Charts'] = "圖表";
$l['sidebar']['Configuration'] = "設定";
$l['sidebar']['CustomQuery'] = "自訂查詢";
$l['sidebar']['ExtendedCapabilities'] = "進階功能";
$l['sidebar']['ExtendedPeripherals'] = "延伸周邊設備";
$l['sidebar']['Filter'] = "篩選";
$l['sidebar']['FilterRADIUSReply'] = "以所選的 RADIUS Reply 篩選紀錄";
$l['sidebar']['GIS'] = "GIS";
$l['sidebar']['GISMapping'] = "GIS 地圖";
$l['sidebar']['GlobalSettings'] = "全域設定";
$l['sidebar']['GroupCheckManagement'] = "群組 Check 屬性管理";
$l['sidebar']['GroupReplyManagement'] = "群組 Reply 屬性管理";
$l['sidebar']['Heartbeat'] = "心跳監控";
$l['sidebar']['Help'] = "說明";
$l['sidebar']['Home'] = "首頁";
$l['sidebar']['HotspotsAccounting'] = "熱點連線紀錄";
$l['sidebar']['HotspotsManagement'] = "熱點管理";
$l['sidebar']['Huntgroup'] = "尋線組";
$l['sidebar']['HuntgroupsManagement'] = "尋線組管理";
$l['sidebar']['IPPoolsManagement'] = "IP 位址池管理";
$l['sidebar']['InvoiceManagement'] = "發票管理";
$l['sidebar']['InvoiceReport'] = "發票報表";
$l['sidebar']['LinesCount'] = "行數";
$l['sidebar']['List'] = "清單";
$l['sidebar']['LogFiles'] = "記錄檔";
$l['sidebar']['Logs'] = "日誌";
$l['sidebar']['Mail'] = "郵件";
$l['sidebar']['Maintenance'] = "維護";
$l['sidebar']['Management'] = "管理";
$l['sidebar']['MessageSettings'] = "訊息設定";
$l['sidebar']['NASManagement'] = "NAS 管理";
$l['sidebar']['OperatorsManagement'] = "操作員管理";
$l['sidebar']['OrderResultsBy'] = "可依下列欄位排序：%s";
$l['sidebar']['OrderType'] = "排序方式";
$l['sidebar']['OtherReports'] = "其他報表";
$l['sidebar']['PaymentsManagement'] = "付款管理";
$l['sidebar']['PaymentsTypesManagement'] = "付款方式管理";
$l['sidebar']['PlanAccounting'] = "方案用量紀錄";
$l['sidebar']['PlansManagement'] = "方案管理";
$l['sidebar']['PleaseInsertAValid'] = "請輸入有效的%s";
$l['sidebar']['PleaseSelectA'] = "請選擇%s";
$l['sidebar']['PleaseSelectOneOrMultiple'] = "請選擇一個或多個%s";
$l['sidebar']['PointOfSalesManagement'] = "銷售點管理";
$l['sidebar']['ProfilesManagement'] = "設定檔管理";
$l['sidebar']['ProxiesManagement'] = "代理管理";
$l['sidebar']['RAIDStatus'] = "RAID 狀態";
$l['sidebar']['RatesManagement'] = "費率管理";
$l['sidebar']['ReadMore'] = "了解更多";
$l['sidebar']['RealmsManagement'] = "網域管理";
$l['sidebar']['RecurringTasksSettings'] = "排程工作設定";
$l['sidebar']['ReportingSettings'] = "報表設定";
$l['sidebar']['Reports'] = "報表";
$l['sidebar']['ShowOnlySelectedLines'] = "只顯示所選的行數";
$l['sidebar']['Status'] = "狀態";
$l['sidebar']['Support'] = "支援資訊";
$l['sidebar']['TestEmail'] = "測試郵件";
$l['sidebar']['TrackBillingHistory'] = "帳務紀錄查詢";
$l['sidebar']['TrackMerchantTransactions'] = "交易紀錄查詢";
$l['sidebar']['TrackRates'] = "費率紀錄查詢";
$l['sidebar']['TwoFactorAuthentication'] = "兩步驟驗證";
$l['sidebar']['UPSStatus'] = "UPS 狀態";
$l['sidebar']['UserCharts'] = "使用者圖表";
$l['sidebar']['UserGroupManagement'] = "使用者群組管理";
$l['sidebar']['UserReports'] = "使用者報表";
$l['sidebar']['UsersAccounting'] = "使用者連線紀錄";
$l['sidebar']['UsersManagement'] = "使用者管理";

// operator dashboard strings (see app/operators/home-main.php)
$l['dashboard']['CurrentlyOnline'] = "目前線上";
$l['dashboard']['GoToHotspotsList'] = "前往熱點清單";
$l['dashboard']['GoToNASList'] = "前往 NAS 清單";
$l['dashboard']['GoToUsersList'] = "前往使用者清單";
$l['dashboard']['LastMonthTopUsers'] = "上個月使用量排行";
$l['dashboard']['OnlineSince'] = "上線時間";
$l['messages']['noDataToShow'] = "沒有資料可顯示";

$l['submenu']['General'] = "通用";
$l['submenu']['Reporting'] = "報表";
$l['submenu']['Maintenance'] = "維護";
$l['submenu']['Operators'] = "操作員";
$l['submenu']['Backup'] = "備份";
$l['submenu']['Logs'] = "日誌";
$l['submenu']['Status'] = "狀態";
$l['submenu']['Batch Users'] = "批次使用者處理";
$l['submenu']['Dashboard'] = "控制檯";
$l['submenu']['Users'] = "使用者";
$l['submenu']['Hotspots'] = "熱點";
$l['submenu']['Nas'] = "Nas";
$l['submenu']['User-Groups'] = "使用者群組";
$l['submenu']['Profiles'] = "設定檔";
$l['submenu']['HuntGroups'] = "尋線組";
$l['submenu']['Attributes'] = "屬性";
$l['submenu']['Realm/Proxy'] = "域/代理";
$l['submenu']['IP-Pool'] = "IP位址池";
$l['submenu']['POS'] = "銷售點";
$l['submenu']['Plans'] = "方案";
$l['submenu']['Rates'] = "費率";
$l['submenu']['Merchant-Transactions'] = "交易管理";
$l['submenu']['Billing-History'] = "帳單記錄";
$l['submenu']['Invoices'] = "發票";
$l['submenu']['Payments'] = "支付管理";
$l['submenu']['Custom'] = "自訂查詢";
$l['submenu']['Hotspot'] = "熱點";

//
// Entries below exist in en.php but were missing from the Simplified Chinese
// pack (zh.php) at the time this file was created.
//

$l['all']['GeneratePassword'] = "自動產生密碼";
$l['all']['GeneratedPasswords'] = "已產生的密碼";
$l['all']['Yes'] = "是";
$l['all']['No'] = "否";
$l['all']['NasVirtualServer'] = "NAS 虛擬伺服器";
$l['all']['Calling Station ID'] = "主叫識別碼";
$l['all']['Framed IP Address'] = "配發 IP 位址";

$l['Tooltip']['user_idTooltip'] = "使用者 ID";
$l['Tooltip']['generatePasswordTooltip'] = "設為「是」時，若 CSV 資料的密碼欄位留空，系統會自動產生 8 個字元的隨機密碼。";
$l['Tooltip']['CSVDataGeneratePasswordHint'] = "將密碼欄位留空，即可在「自動產生密碼」設為「是」時自動產生密碼。";

$l['title']['CleanupRecordsByUsername'] = "依使用者名稱";
$l['title']['CleanupRecordsByDate'] = "依日期";

$l['Intro']['configcrontab.php'] = "排程工作設定";

$l['messages']['generatedPasswordsExportNotice'] = "請立即下載產生的帳號密碼。此 CSV 只能下載一次，會在 %d 分鐘後失效，且僅包含本次匯入時自動產生的密碼。";

$l['buttons']['downloadGeneratedPasswordsCSV'] = "下載自動產生的密碼 CSV";

$l['submenu']['Mail'] = "郵件";

$l['helpPage']['mngimportusers'] = <<<EOF
<h1 class="fs-5">匯入使用者</h1>
<p>本頁可透過 CSV 格式的資料一次建立多個 RADIUS 使用者。您可以選擇認證類型、將匯入的使用者指派到群組，並選擇性地與某個計費方案關聯。</p>

<h2 class="fs-6">以使用者名稱與密碼匯入</h2>
<p>選擇<strong>依使用者名稱與密碼</strong>時，CSV 的每一列至少必須包含下列五個欄位：</p>
<pre><code>username,password,email,firstname,lastname</code></pre>
<p>在前五個欄位之後，還可以依「CSV 資料」欄位的說明附加其他選填欄位。</p>

<h2 class="fs-6">自動產生密碼</h2>
<p>將<strong>自動產生密碼</strong>設為<strong>是</strong>，當某一列 CSV 的密碼欄位留空時，系統會產生 8 個字元的隨機密碼；若該列已填寫密碼，則保留原值。此選項設為<strong>否</strong>時，密碼欄位留空的資料列會被拒絕。</p>
<p>若要讓系統自動產生密碼，請將 CSV 的第二個欄位留空：</p>
<pre><code>user001,,user001@example.com,John,Doe</code></pre>
<p>匯入成功後，請使用<strong>下載自動產生的密碼 CSV</strong>取得該次匯入所產生的帳號密碼。原始 CSV 中自行填寫的密碼不會包含在內。此檔案只能下載一次，並於五分鐘後失效。</p>
<p>產生的密碼會依所選的「密碼類型」儲存。若使用單向雜湊的密碼類型，日後將無法從儲存的 RADIUS 屬性還原原始密碼。停用入口網站登入時，原始密碼不會複製到使用者資訊記錄；啟用入口網站登入時，則會一併儲存為入口網站的登入密碼。</p>
EOF;

$l['helpPage']['configdb_short'] = <<<EOF
<h2 class="fs-6">資料庫設定</h2>
<p>設定資料庫引擎、連線參數，以及未使用預設名稱時的資料表名稱。</p>
EOF;

$l['helpPage']['configcrontab'] = <<<EOF
<p>本區可設定系統中與工作階段及流量相關的各種監控與警示功能。<br>
您可以在此調整殘留工作階段的偵測間隔、節點監控設定、使用者流量監控門檻值，以及電子郵件警示等參數。本區以索引標籤分頁呈現，每個分頁各自對應系統監控與警示的一個面向。您可以啟用或停用各項檢查、設定門檻值，並指定接收警示的電子郵件收件者。此外還有一個分頁可檢視系統 crontab 設定的輸出內容，藉此了解與監控及維護相關的排程工作。</p>

<h3 class="fs-6">殘留工作階段</h3>
<p>「間隔」與「寬限時間」會用來計算時間門檻值。請務必依照 <strong>Acct-Interim-Interval</strong> 來設定這個門檻值，尤其要確保它大於 Acct-Interim-Interval，以免工作階段被提前中斷。</p>
EOF;

$l['helpPage']['acctcustom_short'] = <<<EOF
<h1 class="fs-5">自訂查詢</h1>
<p>提供在資料庫上執行查詢時最具彈性的方式。您可以透過左側邊欄調整查詢設定，以符合實際需求。</p>
EOF;

?>
