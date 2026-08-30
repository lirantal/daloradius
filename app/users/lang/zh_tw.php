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
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/zh_tw.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$year = date('Y');
if ($year > 2023) {
    $year = "2023-$year";
}
$l['all']['copyright2'] = <<<EOF
<a target="_blank" href="https://github.com/filippolauria/daloradius">daloRADIUS</a><br>
Copyright &copy; 2007-2022 Liran Tal, Filippo Lauria $year.
EOF;

$l['all']['Amount'] = "金額";
$l['all']['Balance'] = "餘額";
$l['all']['ClientName'] = "客戶名稱";
$l['all']['Date'] = "日期";
$l['all']['Download'] = "下載";
$l['all']['EndingDate'] = "結束日期";
$l['all']['HotSpot'] = "熱點";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "發票";
$l['all']['InvoiceStatus'] = "發票狀態";
$l['all']['InvoiceType'] = "發票類型";
$l['all']['IPAddress'] = "IP 位址";
$l['all']['Language'] = "語言";
$l['all']['NASIPAddress'] = "NAS IP 位址";
$l['all']['NewPassword'] = "新密碼";
$l['all']['Password'] = "密碼";
$l['all']['PaymentDate'] = "日期";
$l['all']['StartingDate'] = "開始日期";
$l['all']['StartTime'] = "開始時間";
$l['all']['Statistics'] = "統計資料";
$l['all']['Status'] = "狀態";
$l['all']['StopTime'] = "結束時間";
$l['all']['Termination'] = "結束原因";
$l['all']['TotalBilled'] = "應付總額";
$l['all']['TotalPayed'] = "已付總額";
$l['all']['TotalTime'] = "總時數";
$l['all']['Upload'] = "上傳";
$l['all']['Username'] = "使用者名稱";
$l['all']['CurrentPassword'] = "目前的密碼";
$l['all']['VerifyPassword'] = "確認密碼";

$l['all']['Global'] = "全部";
$l['all']['Daily'] = "每日";
$l['all']['Weekly'] = "每週";
$l['all']['Monthly'] = "每月";
$l['all']['Yearly'] = "每年";

$l['button']['Accounting'] = "連線紀錄";
$l['button']['ChangeAuthPassword'] = "變更連線密碼";
$l['button']['ChangePortalPassword'] = "變更入口網站密碼";
$l['button']['DateAccounting'] = "依日期查詢連線紀錄";
$l['button']['EditUserInfo'] = "編輯聯絡資訊";
$l['button']['GenerateReport'] = "產生報表";
$l['button']['Graphs'] = "統計圖表";
$l['button']['Preferences'] = "偏好設定";
$l['button']['ShowInvoice'] = "顯示發票";

$l['button']['UserDownloads'] = "下載流量";
$l['button']['UserLogins'] = "登入紀錄";
$l['button']['UserUploads'] = "上傳流量";

$l['ContactInfo']['Address'] = "地址";
$l['ContactInfo']['City'] = "城市";
$l['ContactInfo']['Company'] = "組織";
$l['ContactInfo']['Country'] = "國家";
$l['ContactInfo']['Department'] = "所屬單位";
$l['ContactInfo']['Email'] = "電子郵件";
$l['ContactInfo']['FirstName'] = "名字";
$l['ContactInfo']['HomePhone'] = "住家電話";
$l['ContactInfo']['LastName'] = "姓氏";
$l['ContactInfo']['MobilePhone'] = "行動電話";
$l['ContactInfo']['Notes'] = "備註";
$l['ContactInfo']['State'] = "州／地區";
$l['ContactInfo']['WorkPhone'] = "公司電話";
$l['ContactInfo']['Zip'] = "郵遞區號";

$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">依日期查詢連線紀錄</h2>
<p>提供特定使用者在兩個指定日期之間所有連線工作階段的詳細紀錄。</p>
EOF;
$l['helpPage']['acctmain'] = '<h1 class="fs-5">連線紀錄總覽</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>產生圖表，顯示您在指定期間內的下載流量。<br>
圖表下方會一併列出對應的資料表。</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>產生圖表，顯示您在指定期間內的上傳流量。<br>
圖表下方會一併列出對應的資料表。</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>產生圖表，顯示您在指定期間內的登入活動。<br>
圖表會顯示登入次數（亦即對 NAS 的連線次數），並一併列出對應的資料表。</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">統計圖表</h1>'
                            . $l['helpPage']['graphsoveralllogins'] . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>親愛的使用者您好：</p>
<p>歡迎使用使用者入口網站，很高興您加入我們！</p>

<p>以您的帳號名稱與密碼登入後，即可使用各項功能。例如：修改聯絡方式設定、更新個人資料，以及透過視覺化圖表檢視歷史使用紀錄。</p>

<p>我們非常重視您的隱私與資料安全，請放心，您的所有資料都安全地儲存在我們的資料庫中，且僅有您本人與經授權的工作人員能夠存取。</p>

<p>若您需要任何協助或有任何疑問，歡迎隨時與我們的支援團隊聯絡，我們很樂意為您服務！</p>

<p>敬祝順心，<br/>
FiloRADIUS 團隊敬上</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">無法登入</h1>
<p>若您無法登入帳號，最可能的原因是輸入了錯誤的帳號名稱或密碼。請確認登入資訊是否正確，然後再試一次。</p>
<p>如果確認資訊無誤後仍無法登入，請不要猶豫，直接與我們的支援團隊聯絡。我們隨時樂意協助您重新取得帳號存取權限，盡快恢復使用我們的服務。</p>
EOF;

$l['helpPage']['prefmain'] = "在本區中，您可以管理自己的<strong>聯絡資訊</strong>，以及入口網站與各項服務的登入密碼。";
$l['helpPage']['prefpasswordedit'] = "請使用下方的表單變更密碼。基於安全考量，系統會要求您輸入舊密碼，並將新密碼輸入兩次以避免誤植。";
$l['helpPage']['prefuserinfoedit'] = "請使用下方的表單更新您的聯絡資訊。您可以視需要修改名字、姓氏、電子郵件地址、電話號碼及其他資料。儲存前請再次確認修改內容，以確保資料正確無誤。";

$l['Intro']['acctdate.php'] = "依日期排序的連線紀錄";
$l['Intro']['acctmain.php'] = "連線紀錄頁面";
$l['Intro']['billinvoiceedit.php'] = "檢視發票";
$l['Intro']['billinvoicereport.php'] = "發票報表";
$l['Intro']['billmain.php'] = "帳務頁面";
$l['Intro']['graphmain.php'] = "使用量圖表";
$l['Intro']['graphsoveralldownload.php'] = "使用者下載量";
$l['Intro']['graphsoveralllogins.php'] = "使用者登入紀錄";
$l['Intro']['graphsoverallupload.php'] = "使用者上傳量";
$l['Intro']['prefmain.php'] = "偏好設定頁面";
$l['Intro']['prefpasswordedit.php'] = "變更密碼";
$l['Intro']['prefuserinfoedit.php'] = "變更使用者資訊";
$l['menu']['Accounting'] = "連線紀錄";
$l['menu']['Billing'] = "帳務";
$l['menu']['Graphs'] = "統計圖表";
$l['menu']['Home'] = "首頁";
$l['menu']['Preferences'] = "偏好設定";
$l['menu']['Help'] = "說明";


$l['text']['LoginPlease'] = "請登入";
$l['text']['LoginRequired'] = "需要登入";
$l['title']['ContactInfo'] = "聯絡資訊";
$l['title']['BusinessInfo'] = "營運資訊";
$l['title']['Invoice'] = "發票";
$l['title']['Items'] = "項目";
$l['Tooltip']['invoiceID'] = "請輸入發票 ID";
