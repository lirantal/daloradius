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
 * Description:    中文语言文件
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 三多 <10644331064@qq.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/zh.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("版本 %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'RADIUS 管理、报告、会计和账单 <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.<br>'
                        . 'Chinese language pack produced by SanDuo';

$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "IP地址名称";
$l['all']['CalledStationId'] = "被叫号码";
$l['all']['CallingStationID'] = "被叫号码";
$l['all']['ExpiryTime'] = "到期时间";
$l['all']['PoolKey'] = "池秘钥";

/********************************************************************************/
/* 设备属性相关的翻译                                     */
/********************************************************************************/
$l['all']['Dictionary'] = "字典";
$l['all']['VendorID'] = "设备代码";
$l['all']['VendorName'] = "设备名称";
$l['all']['VendorAttribute'] = "所属设备";
$l['all']['RecommendedOP'] = "推荐人";
$l['all']['RecommendedTable'] = "推荐表";
$l['all']['RecommendedTooltip'] = "推荐工具提示";
$l['all']['RecommendedHelper'] = "推荐助手";
/***********************************************************************************/

$l['all']['CSVData'] = "CSV格式数据";

$l['all']['CPU'] = "CPU";

/* ****************************** radius的相关文本 ******************************* */
$l['all']['RADIUSDictionaryPath'] = "RADIUS字典路径";


$l['all']['DashboardSecretKey'] = "仪表盘密钥";
$l['all']['DashboardDebug'] = "调试";
$l['all']['DashboardDelaySoft'] = "在几分钟的时间来考虑一个‘软’延迟限制";
$l['all']['DashboardDelayHard'] = "在几分钟的时间来考虑一个‘硬’延迟限制";



$l['all']['SendWelcomeNotification'] = "欢迎发送通知";
$l['all']['SMTPServerAddress'] = "SMTP服务器地址";
$l['all']['SMTPServerPort'] = "SMTP服务器端口";
$l['all']['SMTPServerFromEmail'] = "发件人邮件地址";

$l['all']['customAttributes'] = "用户属性";

$l['all']['UserType'] = "用户类型";

$l['all']['BatchName'] = "批量名称";
$l['all']['BatchStatus'] = "批量状态";

$l['all']['Users'] = "用户";

$l['all']['Compare'] = "比较";
$l['all']['Never'] = "从不";


$l['all']['Section'] = "部门";
$l['all']['Item'] = "项目";

$l['all']['Megabytes'] = "MB";
$l['all']['Gigabytes'] = "GB";

$l['all']['Daily'] = "每日";
$l['all']['Weekly'] = "每周";
$l['all']['Monthly'] = "每月";
$l['all']['Yearly'] = "每年";

$l['all']['Month'] = "月";

$l['all']['RemoveRadacctRecords'] = "删除账单记录";

$l['all']['CleanupSessions'] = "清理会话年龄比";
$l['all']['DeleteSessions'] = "删除会话年龄比";

$l['all']['StartingDate'] = "开始日期";
$l['all']['EndingDate'] = "结束日期";

$l['all']['Realm'] = "域";
$l['all']['RealmName'] = "域名";
$l['all']['RealmSecret'] = "域安全";
$l['all']['AuthHost'] = "认证主机";
$l['all']['AcctHost'] = "统计主机";
$l['all']['Ldflag'] = "ld标识";
$l['all']['Nostrip'] = "分布IP";
$l['all']['Notrealm'] = "非域";
$l['all']['Hints'] = "提示";

$l['all']['Proxy'] = "代理";
$l['all']['ProxyName'] = "代理名称";
$l['all']['ProxySecret'] = "代理安全";
$l['all']['DeadTime'] = "停滞时间";
$l['all']['RetryDelay'] = "延迟重试";
$l['all']['RetryCount'] = "重试次数";
$l['all']['DefaultFallback'] = "默认后退";


$l['all']['Firmware'] = "固件";
$l['all']['NASMAC'] = "NAS MAC";

$l['all']['WanIface'] = "广域网网络接口";
$l['all']['WanMAC'] = "广域网MAC地址";
$l['all']['WanIP'] = "广域网IP地址";
$l['all']['WanGateway'] = "广域网网关";

$l['all']['LanIface'] = "局域网网络接口";
$l['all']['LanMAC'] = "局域网MAC地址";
$l['all']['LanIP'] = "局域网IP地址";

$l['all']['WifiIface'] = "无线网网络接口";
$l['all']['WifiMAC'] = "无线网MAC地址";
$l['all']['WifiIP'] = "无线网IP地址";

$l['all']['WifiSSID'] = "无线网网络名称";
$l['all']['WifiKey'] = "无线网密钥";
$l['all']['WifiChannel'] = "无线网频道";

$l['all']['CheckinTime'] = "最后登录";

$l['all']['FramedIPAddress'] = "用户IP地址";
$l['all']['SimultaneousUse'] = "同时使用";
$l['all']['HgID'] = "寻线群ID";
$l['all']['Hg'] = "寻线群";
$l['all']['HgIPHost'] = "寻线群IP/主机";
$l['all']['HgGroupName'] = "寻线群组名称";
$l['all']['HgPortId'] = "寻线群端口名称";
$l['all']['NasID'] = "NAS ID";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/主机";
$l['all']['NasShortname'] = "NAS 简称";
$l['all']['NasType'] = "NAS类型";
$l['all']['NasPorts'] = "NAS端口";
$l['all']['NasSecret'] = "NAS安全";
$l['all']['NasCommunity'] = "NAS组";
$l['all']['NasDescription'] = "NAS描述";
$l['all']['PacketType'] = "数据包类型";
$l['all']['HotSpot'] = "热点";
$l['all']['HotSpots'] = "热点";
$l['all']['HotSpotName'] = "热点名称";
$l['all']['Name'] = "名称";
$l['all']['Username'] = "用户名";
$l['all']['Password'] = "密码";
$l['all']['PasswordType'] = "密码类型";
$l['all']['IPAddress'] = "IP地址";
$l['all']['Profile'] = "用户配置文件";
$l['all']['Group'] = "组";
$l['all']['Groupname'] = "组名称";
$l['all']['ProfilePriority'] = "优先的配置文件";
$l['all']['GroupPriority'] = "优先的组";
$l['all']['CurrentGroupname'] = "通用组名称";
$l['all']['NewGroupname'] = "新建组名称";
$l['all']['Priority'] = "优先";
$l['all']['Attribute'] = "属性";
$l['all']['Operator'] = "操作员";
$l['all']['Value'] = "值";
$l['all']['NewValue'] = "新建值";
$l['all']['MaxTimeExpiration'] = "最大时间/有效期";
$l['all']['UsedTime'] = "使用时间";
$l['all']['Status'] = "状态";
$l['all']['Usage'] = "使用";
$l['all']['StartTime'] = "登陆时间";
$l['all']['StopTime'] = "停止时间";
$l['all']['TotalTime'] = "总时间";
$l['all']['TotalTraffic'] = "总流量";
$l['all']['Bytes'] = "字节";
$l['all']['Upload'] = "上传";
$l['all']['Download'] = "下载";
$l['all']['Rollback'] = "回滚";
$l['all']['Termination'] = "终止";
$l['all']['NASIPAddress'] = "NAS IP地址";
$l['all']['NASShortName'] = "NAS简称";
$l['all']['Action'] = "活动";
$l['all']['UniqueUsers'] = "独立用户";
$l['all']['TotalHits'] = "总点击数";
$l['all']['AverageTime'] = "平均时间";
$l['all']['Records'] = "记录";
$l['all']['Summary'] = "明细";
$l['all']['Statistics'] = "统计";
$l['all']['Credit'] = "信用";
$l['all']['Used'] = "已使用";
$l['all']['LeftTime'] = "剩余时间";
$l['all']['LeftPercent'] = "%剩余时间";
$l['all']['TotalSessions'] = "总会话";
$l['all']['LastLoginTime'] = "最后登录时间";
$l['all']['TotalSessionTime'] = "总会话时间";
$l['all']['RateName'] = "价格名称";
$l['all']['RateType'] = "价格类型";
$l['all']['RateCost'] = "成本率";//这个词语有待改进
$l['all']['Billed'] = "记账";
$l['all']['TotalUsers'] = "总用户";
$l['all']['ActiveUsers'] = "活动用户";
$l['all']['TotalBilled'] = "总记账";
$l['all']['TotalPayed'] = "总支付";
$l['all']['Balance'] = "余额";
$l['all']['CardBank'] = "银行卡";
$l['all']['Type'] = "类型";
$l['all']['CardBank'] = "银行卡";
$l['all']['MACAddress'] = "MAC地址";
$l['all']['Geocode'] = "地址编码";
$l['all']['PINCode'] = "PIN码";
$l['all']['CreationDate'] = "创建日期";
$l['all']['CreationBy'] = "创建人";
$l['all']['UpdateDate'] = "更新日期";
$l['all']['UpdateBy'] = "更新人";

$l['all']['Discount'] = "折扣";
$l['all']['BillAmount'] = "记账总额";
$l['all']['BillAction'] = "记账功能";
$l['all']['BillPerformer'] = "记账执行者";
$l['all']['BillReason'] = "记账原因";
$l['all']['Lead'] = "广告";
$l['all']['Coupon'] = "优惠券";
$l['all']['OrderTaker'] = "订单员";
$l['all']['BillStatus'] = "记账状态";
$l['all']['LastBill'] = "最后记账";
$l['all']['NextBill'] = "下次记账";
$l['all']['BillDue'] = "记账到期";
$l['all']['NextInvoiceDue'] = "下次应付款账单";
$l['all']['PostalInvoice'] = "邮寄账单";
$l['all']['FaxInvoice'] = "传真账单";
$l['all']['EmailInvoice'] = "Email账单";

$l['all']['ClientName'] = "客户名称";
$l['all']['Date'] = "日期";

$l['all']['edit'] = "编辑";
$l['all']['del'] = "删除";
$l['all']['groupslist'] = "群组列表";
$l['all']['TestUser'] = "测试用户";
$l['all']['Accounting'] = "账单";
$l['all']['RADIUSReply'] = "用户状态";/**RADIUS回复状态Access-Accept  Access-Request**/

$l['all']['Disconnect'] = "断开";

$l['all']['Debug'] = "调试";
$l['all']['Timeout'] = "超时";
$l['all']['Retries'] = "重试";
$l['all']['Count'] = "计数";
$l['all']['Requests'] = "请求";

$l['all']['DatabaseHostname'] = "数据库主机名称";
$l['all']['DatabasePort'] = "数据库端口号";
$l['all']['DatabaseUser'] = "数据库用户名";
$l['all']['DatabasePass'] = "数据库密码";
$l['all']['DatabaseName'] = "数据名称";

$l['all']['PrimaryLanguage'] = "主要语言";

$l['all']['PagesLogging'] = "页面日志（访问页面）";
$l['all']['QueriesLogging'] = "查询日志（报表和图表）";
$l['all']['ActionsLogging'] = "活动日志（表单提交）";
$l['all']['FilenameLogging'] = "文件名日志（完整路径）";
$l['all']['LoggingDebugOnPages'] = "页面调试信息日志";
$l['all']['LoggingDebugInfo'] = "调试信息日志";

$l['all']['PasswordHidden'] = "启用密码隐藏（将用星号显示）";
$l['all']['TablesListing'] = "行/记录每表格清单页面";
$l['all']['TablesListingNum'] = "启用表清单编号";
$l['all']['AjaxAutoComplete'] = "启用Ajax自动完成";

$l['all']['RadiusServer'] = "Radius服务器";
$l['all']['RadiusPort'] = "Radius端口";

$l['all']['UsernamePrefix'] = "用户前缀";

$l['all']['batchName'] = "批量Id/名称";
$l['all']['batchDescription'] = "批量描述";

$l['all']['NumberInstances'] = "创建数量";
$l['all']['UsernameLength'] = "用户名字符数";
$l['all']['PasswordLength'] = "密码字符数";

$l['all']['Expiration'] = "效期时间";
$l['all']['MaxAllSession'] = "最大总会话";
$l['all']['SessionTimeout'] = "会话超时";
$l['all']['IdleTimeout'] = "空闲超时";

$l['all']['DBEngine'] = "服务器引擎";
$l['all']['radcheck'] = "radius检查";
$l['all']['radreply'] = "radius回复";
$l['all']['radgroupcheck'] = "radius组检查";
$l['all']['radgroupreply'] = "radius组回复";
$l['all']['usergroup'] = "用户组";
$l['all']['radacct'] = "radius账单";
$l['all']['operators'] = "操作人";
$l['all']['operators_acl'] = "操作人访问控制列表";
$l['all']['operators_acl_files'] = "操作人访问控制列表文件";
$l['all']['billingrates'] = "记账费用";
$l['all']['hotspots'] = "热点";
$l['all']['node'] = "节点";
$l['all']['nas'] = "nas";
$l['all']['hunt'] = "radius寻线群";
$l['all']['radpostauth'] = "radius提交认证";
$l['all']['radippool'] = "radiusIP地址池";
$l['all']['userinfo'] = "用户信息";
$l['all']['dictionary'] = "字典";
$l['all']['realms'] = "域";
$l['all']['proxys'] = "代理";
$l['all']['billingpaypal'] = "PayPal记账";
$l['all']['billingmerchant'] = "供货方记账";
$l['all']['billingplans'] = "记账计划";
$l['all']['billinghistory'] = "记账历史";
$l['all']['billinginfo'] = "记账信息";


$l['all']['CreateIncrementingUsers'] = "创建增量用户";
$l['all']['CreateRandomUsers'] = "创建随机用户";
$l['all']['StartingIndex'] = "开始索引";
$l['all']['EndingIndex'] = "结束索引";
$l['all']['RandomChars'] = "允许随机字符";
$l['all']['Memfree'] = "空闲内存";
$l['all']['Uptime'] = "正常运行时间";
$l['all']['BandwidthUp'] = "上传带宽";
$l['all']['BandwidthDown'] = "下载带宽";

$l['all']['BatchCost'] = "批量花费";

$l['all']['PaymentDate'] = "付款日";
$l['all']['PaymentStatus'] = "付款状态";
$l['all']['FirstName'] = "名";
$l['all']['LastName'] = "姓";
$l['all']['VendorType'] = "设备类型";
$l['all']['PayerStatus'] = "付款人状态";
$l['all']['PaymentAddressStatus'] = "付款地址状态";
$l['all']['PayerEmail'] = "付款日Email";
$l['all']['TxnId'] = "交易ID";
$l['all']['PlanActive'] = "活动计划";
$l['all']['PlanTimeType'] = "计划时间类型";
$l['all']['PlanTimeBank'] = "计划时间银行";
$l['all']['PlanTimeRefillCost'] = "计划补充花费";
$l['all']['PlanTrafficRefillCost'] = "计划补充花费";
$l['all']['PlanBandwidthUp'] = "计划上传带宽";
$l['all']['PlanBandwidthDown'] = "计划下载带宽";
$l['all']['PlanTrafficTotal'] = "计划总流量";
$l['all']['PlanTrafficDown'] = "计划下载流量";
$l['all']['PlanTrafficUp'] = "计划上传流量";
$l['all']['PlanRecurring'] = "计划循环";
$l['all']['PlanRecurringPeriod'] = "计划循环周期";
$l['all']['planRecurringBillingSchedule'] = "计划重复记账安排";
$l['all']['PlanCost'] = "计划花费";
$l['all']['PlanSetupCost'] = "计划安装花费";
$l['all']['PlanTax'] = "计划税额";
$l['all']['PlanCurrency'] = "计划货币";
$l['all']['PlanGroup'] = "计划个人用户配置（组）";
$l['all']['PlanType'] = "计划类型";
$l['all']['PlanName'] = "计划名称";
$l['all']['PlanId'] = "计划ID";

$l['all']['UserId'] = "用户Id";

$l['all']['Invoice'] = "账单";
$l['all']['InvoiceID'] = "账单ID";
$l['all']['InvoiceItems'] = "账单项目";
$l['all']['InvoiceStatus'] = "账单状态";

$l['all']['InvoiceType'] = "账单类型";
$l['all']['Amount'] = "总额";
$l['all']['Total'] = "总计";
$l['all']['TotalInvoices'] = "总账单";

$l['all']['PayTypeName'] = "付款类型名称";
$l['all']['PayTypeNotes'] = "付款类型描述";
$l['all']['payment_type'] = "付款类型";
$l['all']['payments'] = "付款";
$l['all']['PaymentId'] = "付款ID";
$l['all']['PaymentInvoiceID'] = "账单ID";
$l['all']['PaymentAmount'] = "支付金额";
$l['all']['PaymentDate'] = "日期";
$l['all']['PaymentType'] = "付款类型";
$l['all']['PaymentNotes'] = "付款备注";




$l['all']['Quantity'] = "总量";
$l['all']['ReceiverEmail'] = "接受电子邮件";
$l['all']['Business'] = "公司";
$l['all']['Tax'] = "税额";
$l['all']['Cost'] = "花费";
$l['all']['TotalCost'] = "总花费";
$l['all']['TransactionFee'] = "交易费";
$l['all']['PaymentCurrency'] = "支付货币";
$l['all']['AddressRecipient'] = "地址接收人";
$l['all']['Street'] = "街道";
$l['all']['Country'] = "国家";
$l['all']['CountryCode'] = "国家代码";
$l['all']['City'] = "城市";
$l['all']['State'] = "省份";
$l['all']['Zip'] = "邮编";

$l['all']['BusinessName'] = "公司名字";
$l['all']['BusinessPhone'] = "公司电话";
$l['all']['BusinessAddress'] = "公司地址";
$l['all']['BusinessWebsite'] = "公司网址";
$l['all']['BusinessEmail'] = "公司Email";
$l['all']['BusinessContactPerson'] = "公司联系人";
$l['all']['DBPasswordEncryption'] = "数据库密码加密类型";


/***********************************************************************************
    工具提示
    辅助信息辅助信息,如为鼠标悬停提示文本事件和弹出提示
 ************************************************************************************/

$l['Tooltip']['batchNameTooltip'] = "为本批创建提供一个标识符名称";
$l['Tooltip']['batchDescriptionTooltip'] = "为本批创建提供一个一般描述";

$l['Tooltip']['hotspotTooltip'] = "选择与这批实例相关联的热点名字";

$l['Tooltip']['startingIndexTooltip'] = "提供起始索引的创建用户";
$l['Tooltip']['planTooltip'] = "选一个计划来关联用户";

$l['Tooltip']['InvoiceEdit'] = "编辑账单";
$l['Tooltip']['invoiceTypeTooltip'] = "账单类型工具提示";
$l['Tooltip']['invoiceStatusTooltip'] = "账单状态工具提示";
$l['Tooltip']['invoiceID'] = "账单ID类型";

$l['Tooltip']['amountTooltip'] = "金额工具提示";
$l['Tooltip']['taxTooltip'] = "税额工具提示";

$l['Tooltip']['PayTypeName'] = "支付类型名称";
$l['Tooltip']['EditPayType'] = "编辑支付类型";
$l['Tooltip']['RemovePayType'] = "移除支付类型";
$l['Tooltip']['paymentTypeTooltip'] = "付款类型友好的名称,<br/>
                                        来描述付款的目的";
$l['Tooltip']['paymentTypeNotesTooltip'] = "描述付款类型的描述<br/>
                                        付款类型的操作";
$l['Tooltip']['EditPayment'] = "编辑付款";
$l['Tooltip']['PaymentId'] = "付款Id";
$l['Tooltip']['RemovePayment'] = "移除付款";
$l['Tooltip']['paymentInvoiceTooltip'] = "此次付款相关的账单";



$l['Tooltip']['Username'] = "用户名类型";
$l['Tooltip']['BatchName'] = "批量名称类型";
$l['Tooltip']['UsernameWildcard'] = "提示: 你可以用字符 * 或 % 来制定一个通配符";
$l['Tooltip']['HotspotName'] = "热点名称类型";
$l['Tooltip']['NasName'] = "NAS名称类型";
$l['Tooltip']['GroupName'] = "群组名称类型";
$l['Tooltip']['AttributeName'] = "属性名称类型";
$l['Tooltip']['VendorName'] = "设备名称类型";
$l['Tooltip']['PoolName'] = "IP地址池名称类型";
$l['Tooltip']['IPAddress'] = "IP地址池类型";
$l['Tooltip']['Filter'] = "过滤器的类型，可以是任何字符的字符串。用留空配对其它。";
$l['Tooltip']['Date'] = "日期类型 <br/> 示例: 1982-06-04 (Y-M-D)";
$l['Tooltip']['RateName'] = "价格名称类型";
$l['Tooltip']['OperatorName'] = "操作人名称类型";
$l['Tooltip']['BillingPlanName'] = "记账计划名称类型";
$l['Tooltip']['PlanName'] = "计划名称类型";

$l['Tooltip']['EditRate'] = "编辑价格";
$l['Tooltip']['RemoveRate'] = "移除价格";

$l['Tooltip']['rateNameTooltip'] = "价格的名称，<br/>
                    来描述价格的用途";
$l['Tooltip']['rateTypeTooltip'] = "价格类型，来描述<br/>
                    价格的操作";
$l['Tooltip']['rateCostTooltip'] = "价格花费金额";

$l['Tooltip']['planNameTooltip'] = "计划的名字。这是<br/>
                    一个友好的描述计划的特性。";
$l['Tooltip']['planIdTooltip'] = "计划ID提示工具";
$l['Tooltip']['planTimeTypeTooltip'] = "计划时间类型提示工具";
$l['Tooltip']['planTimeBankTooltip'] = "计划时间银行提示工具";
$l['Tooltip']['planTimeRefillCostTooltip'] = "计划时间补充话费提示工具";
$l['Tooltip']['planTrafficRefillCostTooltip'] = "计划流量补充提示工具";
$l['Tooltip']['planBandwidthUpTooltip'] = "计划上传带宽提示工具";
$l['Tooltip']['planBandwidthDownTooltip'] = "计划下载带宽提示工具";
$l['Tooltip']['planTrafficTotalTooltip'] = "计划总流量提示工具";
$l['Tooltip']['planTrafficDownTooltip'] = "计划下载流量提示工具";
$l['Tooltip']['planTrafficUpTooltip'] = "计划流量上传提示工具";

$l['Tooltip']['planRecurringTooltip'] = "计划循环提示工具";
$l['Tooltip']['planRecurringBillingScheduleTooltip'] = "计划循环记账安排提示工具";
$l['Tooltip']['planRecurringPeriodTooltip'] = "计划循环周期提示工具";
$l['Tooltip']['planCostTooltip'] = "计划花费提示工具";
$l['Tooltip']['planSetupCostTooltip'] = "计划安装话费提示工具";
$l['Tooltip']['planTaxTooltip'] = "计划税额提示工具";
$l['Tooltip']['planCurrencyTooltip'] = "计划货币提示工具";
$l['Tooltip']['planGroupTooltip'] = "计划群组提示工具";

$l['Tooltip']['EditIPPool'] = "编辑IP地址池";
$l['Tooltip']['RemoveIPPool'] = "移除IP地址池";
$l['Tooltip']['EditIPAddress'] = "编辑IP地址";
$l['Tooltip']['RemoveIPAddress'] = "移除IP地址";

$l['Tooltip']['BusinessNameTooltip'] = "公司名称提示工具";
$l['Tooltip']['BusinessPhoneTooltip'] = "公司电话提示工具";
$l['Tooltip']['BusinessAddressTooltip'] = "公司地址提示工具";
$l['Tooltip']['BusinessWebsiteTooltip'] = "公司网站提示工具";
$l['Tooltip']['BusinessEmailTooltip'] = "公司Email提示工具";
$l['Tooltip']['BusinessContactPersonTooltip'] = "公司联系人提示工具";

$l['Tooltip']['proxyNameTooltip'] = "代理名称";
$l['Tooltip']['proxyRetryDelayTooltip'] = "等待的时间(在短时间内)<br/>
                    来自代理的响应, <br/>
                    在重发代理请求之前";
$l['Tooltip']['proxyRetryCountTooltip'] = "发送重试次数 <br/>
                    在放弃之前,并发送拒绝 <br/>
                    消息给NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "如果主机不响应 <br/>
                    给任意一个多重尝试，<br/>
                    然后FreeRADIUS将停止发送给它。<br/>
                    代理请求，然后标记它‘废弃’。";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "如果所有完全匹配的域 <br/>
                        不响应，我们可以尝试 <br/>
                        ";
$l['Tooltip']['realmNameTooltip'] = "域名";
$l['Tooltip']['realmTypeTooltip'] = "设置默认radius";
$l['Tooltip']['realmSecretTooltip'] = "域RADIUS共享秘钥安全";
$l['Tooltip']['realmAuthhostTooltip'] = "域认证主机";
$l['Tooltip']['realmAccthostTooltip'] = "域账单主机";
$l['Tooltip']['realmLdflagTooltip'] = "允许负载平衡<br/>
                    允许值为‘失效转移’ <br/>
                    和‘轮叫调度’。";
$l['Tooltip']['realmNostripTooltip'] = "不论是否去除 <br/>
                    域后缀";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "示例：cisco<br/>&nbsp;&nbsp;&nbsp;
                                        设备商名称<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "示例：string<br/>&nbsp;&nbsp;&nbsp;
                                        属性变量类型<br/>&nbsp;&nbsp;&nbsp;
                    (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "示例：Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;
                                        准确的属性名称<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "示例：:=<br/>&nbsp;&nbsp;&nbsp;
                                        推荐的属性的操作符<br/>&nbsp;&nbsp;&nbsp;
                    (one of: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "示例：check<br/>&nbsp;&nbsp;&nbsp;
                                        推荐的目标表<br/>&nbsp;&nbsp;&nbsp;
                    (either check or reply).";
$l['Tooltip']['RecommendedTooltipTooltip'] = "示例：用户的ip地址<br/>&nbsp;&nbsp;&nbsp;
                                        推荐的工具提示<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['RecommendedHelperTooltip'] = "添加属性为<br/>&nbsp;&nbsp;&nbsp;
                                        可使用的帮助函数<br/>&nbsp;&nbsp;&nbsp;";



$l['Tooltip']['AttributeEdit'] = "编辑属性";

$l['Tooltip']['BatchDetails'] = "批量详情";

$l['Tooltip']['UserEdit'] = "编辑用户";
$l['Tooltip']['HotspotEdit'] = "编辑热点";
$l['Tooltip']['EditNAS'] = "编辑NAS";
$l['Tooltip']['RemoveNAS'] = "移除NAS";
$l['Tooltip']['EditHG'] = "编辑寻线群";
$l['Tooltip']['RemoveHG'] = "移除寻线群";
$l['Tooltip']['hgNasIpAddress'] = "输入主机/IP地址";
$l['Tooltip']['hgGroupName'] = "输入NAS组名称";
$l['Tooltip']['hgNasPortId'] = "输入NAS端口Id";
$l['Tooltip']['EditUserGroup'] = "编辑用户组";
$l['Tooltip']['ListUserGroups'] = "用户组列表";
$l['Tooltip']['DeleteUserGroup'] = "删除关联用户组";

$l['Tooltip']['EditProfile'] = "编辑个人配置文件";

$l['Tooltip']['EditRealm'] = "编辑域";
$l['Tooltip']['EditProxy'] = "编辑代理";

$l['Tooltip']['EditGroup'] = "编辑组";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "如果指定的值，然后只有单一的记录都同组名称和指定值匹配，指定值将被移除。如果省略了值，然后所有那些特定组名称的记录将被移除！";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "如果指定的值，然后只有单一的记录都同组名称和指定值匹配，指定值将被移除。如果省略了值，然后所有那些特定组名称的记录将被移除！";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "（描述名称）";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "如果指定的组，然后只有单一的记录都同用户名和组匹配，指定的将被移除。如果省略了组，然后所有那些特定的用户名称记录将被移除！";


$l['Tooltip']['usernameTooltip'] = "准确的用户名，用户将<br/>&nbsp;&nbsp;&nbsp;
                    用来连接系统";
$l['Tooltip']['passwordTypeTooltip'] = "The password type used to authenticate the user in Radius.";
$l['Tooltip']['passwordTooltip'] = "密码实例包含在系统里<br/>&nbsp;&nbsp;&nbsp;
                    所以要格外小心";
$l['Tooltip']['groupTooltip'] = "用户将被添加到这个组<br/>&nbsp;&nbsp;&nbsp;
                    通过分配一个用户特定组<br/>&nbsp;&nbsp;&nbsp;
                    用户必须受制于组的属性";
$l['Tooltip']['macaddressTooltip'] = "示例：00:AA:BB:CC:DD:EE<br/>&nbsp;&nbsp;&nbsp;
                    MAC地址格式应该是相同的<br/>&nbsp;&nbsp;&nbsp;
                    随着NAS发送它，通常这<br/>&nbsp;&nbsp;&nbsp;
                    没有字符";
$l['Tooltip']['pincodeTooltip'] = "示例：khrivnxufi101<br/>&nbsp;&nbsp;&nbsp;
                    这是准确的pin码将作为用户进入它<br/>&nbsp;&nbsp;&nbsp;
                    你可以使用alpha数字字符";
$l['Tooltip']['usernamePrefixTooltip'] = "示例：TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp;
                    这个用户名前缀会增加<br/>&nbsp;&nbsp;&nbsp;
                    生成的用户名最终。";
$l['Tooltip']['instancesToCreateTooltip'] = "示例：100<br/>&nbsp;&nbsp;&nbsp;
                    用户创建随机的数量<br/>&nbsp;&nbsp;&nbsp;
                    用指定的个人配置文件";
$l['Tooltip']['lengthOfUsernameTooltip'] = "示例：8<br/>&nbsp;&nbsp;&nbsp;
                    用户名的字符长度<br/>&nbsp;&nbsp;&nbsp;
                    被创建。建议8-12个字符。";
$l['Tooltip']['lengthOfPasswordTooltip'] = "示例：8<br/>&nbsp;&nbsp;&nbsp;
                    密码的字符长度<br/>&nbsp;&nbsp;&nbsp;
                    被创建。建议8-12个字符。";


$l['Tooltip']['hotspotNameTooltip'] = "Example：酒店的电吉他<br/>&nbsp;&nbsp;&nbsp;
                    一个友好的热点名称<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "示例：00-aa-bb-cc-dd-ee<br/>&nbsp;&nbsp;&nbsp;
                    NAS的MAC地址<br/>";

$l['Tooltip']['geocodeTooltip'] = "示例：-1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;
                    GooleMaps位置代码<br/>&nbsp;&nbsp;&nbsp;
                    来PIN热点/NAS在上（看GIS）";

$l['Tooltip']['reassignplanprofiles'] = "如果开启,当应用用户信息 <br/>
                    这个个人配置文件中显示的个人配置文件选项卡将被忽略和<br/>
                    个人配置文件将被重新分配根据计划个人配置文件关联";

/* ********************************************************************************** */




/* **********************************************************************************
链接和按钮
 ************************************************************************************/

$l['button']['DashboardSettings'] = "仪表盘设置";


$l['button']['GenerateReport'] = "生成报告";

$l['button']['ListPayTypes'] = "显示付款类型";
$l['button']['NewPayType'] = "新建付款类型";
$l['button']['EditPayType'] = "编辑付款类型";
$l['button']['RemovePayType'] = "移除付款类型";
$l['button']['ListPayments'] = "显示支付";
$l['button']['NewPayment'] = "新建支付";
$l['button']['EditPayment'] = "编辑支付";
$l['button']['RemovePayment'] = "移除支付";

$l['button']['NewUsers'] = "新建用户";

$l['button']['ClearSessions'] = "清除会话";
$l['button']['Dashboard'] = "仪表盘";
$l['button']['MailSettings'] = "邮件设置";

$l['button']['Batch'] = "批量";
$l['button']['BatchHistory'] = "批量历史";
$l['button']['BatchDetails'] = "批量明细";

$l['button']['ListRates'] = "显示率列";
$l['button']['NewRate'] = "新建率列";
$l['button']['EditRate'] = "编辑率列";
$l['button']['RemoveRate'] = "移除率列";

$l['button']['ListPlans'] = "显示计划";
$l['button']['NewPlan'] = "新建计划";
$l['button']['EditPlan'] = "编辑计划";
$l['button']['RemovePlan'] = "移除计划";

$l['button']['ListInvoices'] = "显示账单";
$l['button']['NewInvoice'] = "新建账单";
$l['button']['EditInvoice'] = "编辑账单";
$l['button']['RemoveInvoice'] = "移除账单";

$l['button']['ListRealms'] = "显示域";
$l['button']['NewRealm'] = "新建域";
$l['button']['EditRealm'] = "编辑域";
$l['button']['RemoveRealm'] = "移除域";

$l['button']['ListProxys'] = "显示代理";
$l['button']['NewProxy'] = "新建代理";
$l['button']['EditProxy'] = "编辑代理";
$l['button']['RemoveProxy'] = "移除代理";

$l['button']['ListAttributesforVendor'] = "显示属性";
$l['button']['NewVendorAttribute'] = "新建属性";
$l['button']['EditVendorAttribute'] = "编辑属性";
$l['button']['SearchVendorAttribute'] = "搜索属性";
$l['button']['RemoveVendorAttribute'] = "移除属性";
$l['button']['ImportVendorDictionary'] = "导入字典/属性";


$l['button']['BetweenDates'] = "始末日期";
$l['button']['Where'] = "条件";
$l['button']['AccountingFieldsinQuery'] = "查询账单域";
$l['button']['OrderBy'] = "排序";
$l['button']['HotspotAccounting'] = "热点账单";
$l['button']['HotspotsComparison'] = "热点比较";

$l['button']['CleanupStaleSessions'] = "清理过期账单";
$l['button']['DeleteAccountingRecords'] = "删除账单记录";

$l['button']['ListUsers'] = "用户列表";
$l['button']['ListBatches'] = "显示批量";
$l['button']['RemoveBatch'] = "移除批量";
$l['button']['ImportUsers'] = "导入用户";
$l['button']['NewUser'] = "新建用户";
$l['button']['NewUserQuick'] = "添加用户";
$l['button']['BatchAddUsers'] = "批量添加用户";
$l['button']['EditUser'] = "编辑用户";
$l['button']['SearchUsers'] = "搜索用户";
$l['button']['RemoveUsers'] = "移除用户";
$l['button']['ListHotspots'] = "显示热点";
$l['button']['NewHotspot'] = "新建热点";
$l['button']['EditHotspot'] = "编辑热点";
$l['button']['RemoveHotspot'] = "移除热点";

$l['button']['ListIPPools'] = "显示IP地址池";
$l['button']['NewIPPool'] = "新建IP地址池";
$l['button']['EditIPPool'] = "编辑IP地址池";
$l['button']['RemoveIPPool'] = "移除IP地址池";

$l['button']['ListNAS'] = "显示NAS";
$l['button']['NewNAS'] = "新建NAS";
$l['button']['EditNAS'] = "编辑NAS";
$l['button']['RemoveNAS'] = "移除NAS";
$l['button']['ListHG'] = "显示寻线群";
$l['button']['NewHG'] = "新建寻线群";
$l['button']['EditHG'] = "编辑寻线群";
$l['button']['RemoveHG'] = "移除寻线群";
$l['button']['ListUserGroup'] = "显示用户组";
$l['button']['ListUsersGroup'] = "显示用户组";
$l['button']['NewUserGroup'] = "新建用户组";
$l['button']['EditUserGroup'] = "编辑用户组";
$l['button']['RemoveUserGroup'] = "移除用户组";

$l['button']['ListProfiles'] = "配置文件列表";
$l['button']['NewProfile'] = "新建配置文件";
$l['button']['EditProfile'] = "编辑配置文件";
$l['button']['DuplicateProfile'] = "复制配置文件";
$l['button']['RemoveProfile'] = "删除配置文件";

$l['button']['ListGroupReply'] = "显示组回复";
$l['button']['SearchGroupReply'] = "搜索组回复";
$l['button']['NewGroupReply'] = "新建组回复";
$l['button']['EditGroupReply'] = "编辑组回复";
$l['button']['RemoveGroupReply'] = "移除组回复";

$l['button']['ListGroupCheck'] = "显示组检查";
$l['button']['SearchGroupCheck'] = "搜索组检查";
$l['button']['NewGroupCheck'] = "新建组检查";
$l['button']['EditGroupCheck'] = "编辑组检查";
$l['button']['RemoveGroupCheck'] = "移除组检查";

$l['button']['UserAccounting'] = "用户账单";
$l['button']['IPAccounting'] = "IP账单";
$l['button']['NASIPAccounting'] = "NAS IP账单";
$l['button']['NASIPAccountingOnlyActive'] = "只显示活动";
$l['button']['DateAccounting'] = "日期账单";
$l['button']['AllRecords'] = "所有记录";
$l['button']['ActiveRecords'] = "活动记录";

$l['button']['PlanUsage'] = "计划使用";

$l['button']['OnlineUsers'] = "在线用户";
$l['button']['LastConnectionAttempts'] = "连接记录";
$l['button']['TopUser'] = "用户排行";
$l['button']['History'] = "历史";

$l['button']['ServerStatus'] = "服务器状态";
$l['button']['ServicesStatus'] = "服务状态";

$l['button']['daloRADIUSLog'] = "daloRADIUS日志";
$l['button']['RadiusLog'] = "Radius日志";
$l['button']['SystemLog'] = "系统日志";
$l['button']['BootLog'] = "引导日志";

$l['button']['UserLogins'] = "用户登录";
$l['button']['UserDownloads'] = "用户下载";
$l['button']['UserUploads'] = "用户上传";
$l['button']['TotalLogins'] = "总登录";
$l['button']['TotalTraffic'] = "总流量";
$l['button']['LoggedUsers'] = "用户日志";

$l['button']['ViewMAP'] = "显示地图";
$l['button']['EditMAP'] = "编辑地图";
$l['button']['RegisterGoogleMapsAPI'] = "注册谷歌地图API";

$l['button']['UserSettings'] = "用户设置";
$l['button']['DatabaseSettings'] = "数据库设置";
$l['button']['LanguageSettings'] = "语言设置";
$l['button']['LoggingSettings'] = "日志设置";
$l['button']['InterfaceSettings'] = "接口设置";

$l['button']['ReAssignPlanProfiles'] = "重新分配计划个人配置文件";

$l['button']['TestUserConnectivity'] = "测试用户连通性";
$l['button']['DisconnectUser'] = "断开用户";

$l['button']['ManageBackups'] = "管理备份";
$l['button']['CreateBackups'] = "创建备份";

$l['button']['ListOperators'] = "显示操作人";
$l['button']['NewOperator'] = "新建操作人";
$l['button']['EditOperator'] = "编辑操作人";
$l['button']['RemoveOperator'] = "移除操作人";

$l['button']['ProcessQuery'] = "查询进程";



/*********************************************************************************** */


/***********************************************************************************
标题
在题注中文本相关的所有标题，表和指定布局文本
************************************************************************************/

$l['title']['ImportUsers'] = "导入用户";


/*$l['title']['Dashboard'] = "仪表盘";*/

$l['title']['Dashboard'] = "控制面板";
$l['title']['DashboardAlerts'] = "警告";

$l['title']['Invoice'] = "账单";
$l['title']['Invoices'] = "账单";
$l['title']['InvoiceRemoval'] = "账单移除";
$l['title']['Payments'] = "支付";
$l['title']['Items'] = "项目";

$l['title']['PayTypeInfo'] = "支付类型信息";
$l['title']['PaymentInfo'] = "支付信息";


$l['title']['RateInfo'] = "价格信息";
$l['title']['PlanInfo'] = "计划信息";
$l['title']['TimeSettings'] = "时间设置";
$l['title']['BandwidthSettings'] = "带宽设置";
$l['title']['PlanRemoval'] = "计划移除";

$l['title']['BatchRemoval'] = "批量移除";

$l['title']['Backups'] = "备份";
$l['title']['FreeRADIUSTables'] = "FreeRADIUS表";
$l['title']['daloRADIUSTables'] = "daloRADIUS表";

$l['title']['IPPoolInfo'] = "IP地址池信息";

$l['title']['BusinessInfo'] = "公司信息";

$l['title']['CleanupRecords'] = "清除记录";
$l['title']['DeleteRecords'] = "删除记录";

$l['title']['RealmInfo'] = "域信息";

$l['title']['ProxyInfo'] = "代理信息";

$l['title']['VendorAttribute'] = "设备属性";

$l['title']['AccountRemoval'] = "账单移除";
$l['title']['AccountInfo'] = "账单信息";

$l['title']['Profiles'] = "个人配置";
$l['title']['ProfileInfo'] = "个人配置信息";

$l['title']['GroupInfo'] = "组信息";
$l['title']['GroupAttributes'] = "组属性";

$l['title']['NASInfo'] = "NAS信息";
$l['title']['NASAdvanced'] = "NAS高级";
$l['title']['HGInfo'] = "寻线群信息";
$l['title']['UserInfo'] = "用户信息";
$l['title']['BillingInfo'] = "记账信息";

$l['title']['Attributes'] = "属性";
$l['title']['ProfileAttributes'] = "个人配置属性";

$l['title']['HotspotInfo'] = "热点信息";
$l['title']['HotspotRemoval'] = "热点移除";

$l['title']['ContactInfo'] = "联系信息";

$l['title']['Plan'] = "计划";

$l['title']['Profile'] = "个人配置";
$l['title']['Groups'] = "组";
$l['title']['RADIUSCheck'] = "检查属性";
$l['title']['RADIUSReply'] = "回复属性";

$l['title']['Settings'] = "设置";
$l['title']['DatabaseSettings'] = "数据库设置";
$l['title']['DatabaseTables'] = "数据库表";
$l['title']['AdvancedSettings'] = "高级设置";

$l['title']['Advanced'] = "高级";
$l['title']['Optional'] = "可选";

/* ********************************************************************************** */

/* **********************************************************************************
图表
一般图表文本
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
文本
会在页面使用的一般的文本信息
 ************************************************************************************/

$l['text']['LoginRequired'] = "需要登录";
$l['text']['LoginPlease'] = "请先登录";

/* ********************************************************************************** */



/* **********************************************************************************
联系信息
相关的所有联系信息文本、用户信息、热点所有者联系信息等
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "名";
$l['ContactInfo']['LastName'] = "姓";
$l['ContactInfo']['Email'] = "电子邮件";
$l['ContactInfo']['Department'] = "部门";
$l['ContactInfo']['WorkPhone'] = "工作电话";
$l['ContactInfo']['HomePhone'] = "家庭电话";
$l['ContactInfo']['Phone'] = "电话";
$l['ContactInfo']['MobilePhone'] = "手机";
$l['ContactInfo']['Notes'] = "备注";
$l['ContactInfo']['EnableUserUpdate'] = "允许用户更新";
$l['ContactInfo']['EnablePortalLogin'] = "允许用户登录门户";
$l['ContactInfo']['PortalLoginPassword'] = "设置登录密码";

$l['ContactInfo']['OwnerName'] = "所有者姓名";
$l['ContactInfo']['OwnerEmail'] = "所有者电子邮件";
$l['ContactInfo']['ManagerName'] = "管理员姓名";
$l['ContactInfo']['ManagerEmail'] = "管理员电子邮件";
$l['ContactInfo']['Company'] = "公司";
$l['ContactInfo']['Address'] = "地址";
$l['ContactInfo']['City'] = "城市";
$l['ContactInfo']['State'] = "省份";
$l['ContactInfo']['Country'] = "国家";
$l['ContactInfo']['Zip'] = "邮编";
$l['ContactInfo']['Phone1'] = "电话1";
$l['ContactInfo']['Phone2'] = "电话2";
$l['ContactInfo']['HotspotType'] = "热点类型";
$l['ContactInfo']['CompanyWebsite'] = "公司网站";
$l['ContactInfo']['CompanyPhone'] = "公司电话";
$l['ContactInfo']['CompanyEmail'] = "公司电子邮件";
$l['ContactInfo']['CompanyContact'] = "联系公司";

$l['ContactInfo']['PlanName'] = "计划名称";
$l['ContactInfo']['ContactPerson'] = "联系人";
$l['ContactInfo']['PaymentMethod'] = "支付方式";
$l['ContactInfo']['Cash'] = "现金";
$l['ContactInfo']['CreditCardNumber'] = "信用卡卡号";
$l['ContactInfo']['CreditCardName'] = "信用卡名称";
$l['ContactInfo']['CreditCardVerificationNumber'] = "信用卡验证码";
$l['ContactInfo']['CreditCardType'] = "信用卡类型";
$l['ContactInfo']['CreditCardExpiration'] = "信用卡有效期";

/* ********************************************************************************** */

$l['Intro']['configdashboard.php'] = "仪表盘设置";



$l['Intro']['paymenttypesmain.php'] = "支付类型页面";
$l['Intro']['paymenttypesdel.php'] = "删除支付类型条目";
$l['Intro']['paymenttypesedit.php'] = "编辑支付类型明细";
$l['Intro']['paymenttypeslist.php'] = "支付类型表格";
$l['Intro']['paymenttypesnew.php'] = "新建支付类型条目";
$l['Intro']['paymenttypeslist.php'] = "支付类型表格";
$l['Intro']['paymentslist.php'] = "支付表格";
$l['Intro']['paymentsmain.php'] = "支付页面";
$l['Intro']['paymentsdel.php'] = "删除支付条目";
$l['Intro']['paymentsedit.php'] = "编辑支付明细";
$l['Intro']['paymentsnew.php'] = "新建支付条目";

$l['Intro']['billhistorymain.php'] = "记账历史";
$l['Intro']['msgerrorpermissions.php'] = "错误";

$l['Intro']['repnewusers.php'] = "显示新用户";

$l['Intro']['mngradproxys.php'] = "管理代理";
$l['Intro']['mngradproxysnew.php'] = "新建代理";
$l['Intro']['mngradproxyslist.php'] = "显示代理";
$l['Intro']['mngradproxysedit.php'] = "编辑代理";
$l['Intro']['mngradproxysdel.php'] = "移除代理";

$l['Intro']['mngradrealms.php'] = "管理域";
$l['Intro']['mngradrealmsnew.php'] = "新建域";
$l['Intro']['mngradrealmslist.php'] = "显示域";
$l['Intro']['mngradrealmsedit.php'] = "编辑域";
$l['Intro']['mngradrealmsdel.php'] = "移除域";

$l['Intro']['mngradattributes.php'] = "设备属性管理";
$l['Intro']['mngradattributeslist.php'] = "设备的属性列表";
$l['Intro']['mngradattributesnew.php'] = "新建设备属性";
$l['Intro']['mngradattributesedit.php'] = "编辑设备属性";
$l['Intro']['mngradattributessearch.php'] = "搜索属性";
$l['Intro']['mngradattributesdel.php'] = "移除设备属性";
$l['Intro']['mngradattributesimport.php'] = "导入设备字典";
$l['Intro']['mngimportusers.php'] = "导入用户";


$l['Intro']['acctactive.php'] = "活动记录账单";
$l['Intro']['acctall.php'] = "所有用户账单";
$l['Intro']['acctdate.php'] = "日期方式账单";
$l['Intro']['accthotspot.php'] = "热点账单";
$l['Intro']['acctipaddress.php'] = "IP账单";
$l['Intro']['accthotspotcompare.php'] = "热点比较";
$l['Intro']['acctmain.php'] = "账单页面";
$l['Intro']['acctplans.php'] = "计划账单页面";
$l['Intro']['acctnasipaddress.php'] = "NAS IP账单";
$l['Intro']['acctusername.php'] = "用户账单";
$l['Intro']['acctcustom.php'] = "客户账单";
$l['Intro']['acctcustomquery.php'] = "客户查询账单";
$l['Intro']['acctmaintenance.php'] = "账单记录维护";
$l['Intro']['acctmaintenancecleanup.php'] = "删除过期账单";
$l['Intro']['acctmaintenancedelete.php'] = "删除账单记录";

$l['Intro']['billmain.php'] = "记账页面";
$l['Intro']['ratesmain.php'] = "价格记账页面";
$l['Intro']['billratesdate.php'] = "价格预付账单";
$l['Intro']['billratesdel.php'] = "移除利率条目";
$l['Intro']['billratesedit.php'] = "编辑利率信息";
$l['Intro']['billrateslist.php'] = "账单利率表";
$l['Intro']['billratesnew.php'] = "新建利率列表";

$l['Intro']['paypalmain.php'] = "PayPal交易页面";
$l['Intro']['billpaypaltransactions.php'] = "PayPal交易页面";

$l['Intro']['billhistoryquery.php'] = "记账历史";

$l['Intro']['billinvoice.php'] = "会计账单";
$l['Intro']['billinvoicedel.php'] = "删除账单条目";
$l['Intro']['billinvoiceedit.php'] = "编辑账单";
$l['Intro']['billinvoicelist.php'] = "显示账单";
$l['Intro']['billinvoicereport.php'] = "账单报告";
$l['Intro']['billinvoicenew.php'] = "新建账单";

$l['Intro']['billplans.php'] = "记账计划页面";
$l['Intro']['billplansdel.php'] = "删除计划条目";
$l['Intro']['billplansedit.php'] = "编辑计划明细";
$l['Intro']['billplanslist.php'] = "计划表";
$l['Intro']['billplansnew.php'] = "新建计划条目";

$l['Intro']['billpos.php'] = "销售页面的记账点";
$l['Intro']['billposdel.php'] = "删除用户";
$l['Intro']['billposedit.php'] = "编辑用户";
$l['Intro']['billposlist.php'] = "显示用户";
$l['Intro']['billposnew.php'] = "新建用户";

$l['Intro']['giseditmap.php'] = "编辑地图模式";
$l['Intro']['gismain.php'] = "GIS绘图";
$l['Intro']['gisviewmap.php'] = "V查看地图模式";

$l['Intro']['graphmain.php'] = "使用图表";
$l['Intro']['graphsalltimetrafficcompare.php'] = "总流量使用比较";
$l['Intro']['graphsalltimelogins.php'] = "总登录";
$l['Intro']['graphsloggedusers.php'] = "已登录用户";
$l['Intro']['graphsoveralldownload.php'] = "用户下载";
$l['Intro']['graphsoveralllogins.php'] = "用户登录";
$l['Intro']['graphsoverallupload.php'] = "用户上传";

$l['Intro']['rephistory.php'] = "活动历史";
$l['Intro']['replastconnect.php'] = "最后尝试连接";
$l['Intro']['repstatradius.php'] = "守护进程信息";
$l['Intro']['repstatserver.php'] = "服务器状态和信息";
$l['Intro']['reponline.php'] = "显示在线用户";
$l['Intro']['replogssystem.php'] = "系统日志文件";
$l['Intro']['replogsradius.php'] = "RADIUS服务器日志文件";
$l['Intro']['replogsdaloradius.php'] = "daloRADIUS日志文件";
$l['Intro']['replogsboot.php'] = "Boot日志文件";
$l['Intro']['replogs.php'] = "日志";
$l['Intro']['rephb.php'] = "心跳";
$l['Intro']['rephbdashboard.php'] = "daloRADIUS NAS仪表盘";
$l['Intro']['repbatch.php'] = "批量";
$l['Intro']['mngbatchlist.php'] = "批量会话列表";
$l['Intro']['repbatchlist.php'] = "批量用户列表";
$l['Intro']['repbatchdetails.php'] = "批量明细";

$l['Intro']['rephsall.php'] = "热点列表";
$l['Intro']['repmain.php'] = "报告页面";
$l['Intro']['repstatus.php'] = "状态页面";
$l['Intro']['reptopusers.php'] = "用户使用详情";
$l['Intro']['repusername.php'] = "用户列表";

$l['Intro']['mngbatch.php'] = "创建批量用户";
$l['Intro']['mngbatchdel.php'] = "删除批量会话";

$l['Intro']['mngdel.php'] = "移除用户";
$l['Intro']['mngedit.php'] = "编辑用户明细";
$l['Intro']['mnglistall.php'] = "用户列表";
$l['Intro']['mngmain.php'] = "用户和热点管理";
$l['Intro']['mngbatch.php'] = "批量用户管理";
$l['Intro']['mngnew.php'] = "新建用户";
$l['Intro']['mngnewquick.php'] = "快速添加用户";
$l['Intro']['mngsearch.php'] = "搜索用户";

$l['Intro']['mnghsdel.php'] = "移除热点";
$l['Intro']['mnghsedit.php'] = "编辑热点明细";
$l['Intro']['mnghslist.php'] = "显示热点";
$l['Intro']['mnghsnew.php'] = "新建热点";

$l['Intro']['mngradusergroupdel.php'] = "移除用户组绘图";
$l['Intro']['mngradusergroup.php'] = "用户组配置";
$l['Intro']['mngradusergroupnew.php'] = "新建用户组绘图";
$l['Intro']['mngradusergrouplist'] = "数据库用户组绘图";
$l['Intro']['mngradusergrouplistuser'] = "数据库用户组绘图";
$l['Intro']['mngradusergroupedit'] = "编辑用户组绘图";

$l['Intro']['mngradippool.php'] = "IP地址池配置";
$l['Intro']['mngradippoolnew.php'] = "新建IP地址池";
$l['Intro']['mngradippoollist.php'] = "显示IP地址池";
$l['Intro']['mngradippooledit.php'] = "编辑IP地址池";
$l['Intro']['mngradippooldel.php'] = "移除IP地址池";

$l['Intro']['mngradnas.php'] = "NAS配置";
$l['Intro']['mngradnasnew.php'] = "新建NAS记录";
$l['Intro']['mngradnaslist.php'] = "NAS数据库列表";
$l['Intro']['mngradnasedit.php'] = "编辑NAS记录";
$l['Intro']['mngradnasdel.php'] = "移除NAS记录";

$l['Intro']['mngradhunt.php'] = "寻线群配置";
$l['Intro']['mngradhuntnew.php'] = "新建寻线群记录";
$l['Intro']['mngradhuntlist.php'] = "数据库寻线群列表";
$l['Intro']['mngradhuntedit.php'] = "编辑寻线群记录";
$l['Intro']['mngradhuntdel.php'] = "移除寻线群记录";

$l['Intro']['mngradprofiles.php'] = "配置文件列表";
$l['Intro']['mngradprofilesedit.php'] = "编辑组配置";
$l['Intro']['mngradprofilesduplicate.php'] = "复制组配置";
$l['Intro']['mngradprofilesdel.php'] = "删除组配置";
$l['Intro']['mngradprofileslist.php'] = "显示组配置";
$l['Intro']['mngradprofilesnew.php'] = "新建组配置";

$l['Intro']['mngradgroups.php'] = "配置组";

$l['Intro']['mngradgroupreplynew.php'] = "新建组回复绘图";
$l['Intro']['mngradgroupreplylist.php'] = "数据库组回复绘图";
$l['Intro']['mngradgroupreplyedit.php'] = "编辑组回复绘图";
$l['Intro']['mngradgroupreplydel.php'] = "移除组回复绘图";
$l['Intro']['mngradgroupreplysearch.php'] = "搜索组回复绘图";

$l['Intro']['mngradgroupchecknew.php'] = "新建组检查绘图";
$l['Intro']['mngradgroupchecklist.php'] = "数据库组检查绘图";
$l['Intro']['mngradgroupcheckedit.php'] = "编辑组检查绘图";
$l['Intro']['mngradgroupcheckdel.php'] = "移除组检查绘图";
$l['Intro']['mngradgroupchecksearch.php'] = "搜索组检查绘图";

$l['Intro']['configuser.php'] = "配置用户";
$l['Intro']['configmail.php'] = "配置邮件";

$l['Intro']['configdb.php'] = "配置数据库";
$l['Intro']['configlang.php'] = "配置语言";
$l['Intro']['configlogging.php'] = "配置日志";
$l['Intro']['configinterface.php'] = "配置Web接口";
$l['Intro']['configmainttestuser.php'] = "测试用户连通性";
$l['Intro']['configmain.php'] = "配置数据库";
$l['Intro']['configmaint.php'] = "维护";
$l['Intro']['configmaintdisconnectuser.php'] = "断开用户";
$l['Intro']['configbusiness.php'] = "公司明细";
$l['Intro']['configbusinessinfo.php'] = "公司信息";
$l['Intro']['configbackup.php'] = "备份";
$l['Intro']['configbackupcreatebackups.php'] = "创建备份";
$l['Intro']['configbackupmanagebackups.php'] = "管理备份";

$l['Intro']['configoperators.php'] = "配置操作人";
$l['Intro']['configoperatorsdel.php'] = "移除操作人";
$l['Intro']['configoperatorsedit.php'] = "编辑操作人设置";
$l['Intro']['configoperatorsnew.php'] = "新建操作人";
$l['Intro']['configoperatorslist.php'] = "操作人列表";

$l['Intro']['login.php'] = "登录";

$l['captions']['providebillratetodel'] = "提供你想去除的价格类型条目";
$l['captions']['detailsofnewrate'] = "可以填充下面新建价格的明细";
$l['captions']['filldetailsofnewrate'] = "填充下面新建价格条目的明细";

/* **********************************************************************************
 * 帮助页面信息
 *每个页面都有一个标题是前奏类的标题，当点击
 *它会显示/隐藏helpPage格的内容是具体的描述
 *页，基本上你的扩展工具提示。
 ************************************************************************************/

$l['helpPage']['configdashboard'] = "控制台设置";


$l['helpPage']['repnewusers'] = "下拉表显示了每个月创建的新用户.";

$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "显示所有支付宝交易";
$l['helpPage']['billhistoryquery'] = "显示所有用户计费历史(年代)";

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

$l['helpPage']['msgerrorpermissions'] = "你没有权限访问该页面。<br/>
请咨询您的系统管理员。 <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "为了从数据库中删除用户条目，您必须提供帐户的用户名";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Profiles Management</b> - 管理用户配置文件通过组合一组应答并检查属性 <br/>
配置文件可以被认为是组织构成的答复和检查组的组成。<br/>
<h200><b>配置文件列表 </b></h200> - List Profiles <br/>
<h200><b>新建配置文件 </b></h200> - Add a Profile <br/>
<h200><b>编辑配置文件 </b></h200> - Edit a Profile <br/>
<h200><b>删除配置文件 </b></h200> - Delete a Profile <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>编辑个人资料</b></h200> - 编辑个人资料 <br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>删除组配置 </b></h200> - 删除配置文件资料<br/>
";
$l['helpPage']['mngradprofilesduplicate'] = "
<h200><b>复制档案 </b></h200> - 复制一个概要文件的属性设置为一个新建不同的配置文件名称 <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>配置文件列表 </b></h200> - 配置文件列表 <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>新建配置文件</b></h200> - 添加一个配置文件 <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>组织管理</b> - 管理组织回复和组织检查(radgroupreply/radgroupcheck tables).<br/>
<h200><b>回复/查看列表组 </b></h200> - 回复/查看表组<br/>
<h200><b>搜索组回复/查看 </b></h200> - 搜索一组回复/查看(你可以使用通配符) <br/>
<h200><b>新组回复/查看 </b></h200> - 添加一组回复/检查 <br/>
<h200><b>编辑组回复/查看 </b></h200> - 编辑一组回复/查看地图<br/>
<h200><b>删除组回复/查看 </b></h200> - 删除一个回复/查看地图 <br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>新组检查 </b></h200> - 添加一个检查组 <br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>删除组检查 </b></h200> - 删除一组检查 <br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>组织检查列表 </b></h200> - 组列表检查 <br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>编辑组检查 </b></h200> - 编辑检查组 <br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>搜索组检查 </b></h200> - 搜索一组检查 <br/>
使用通配符，你既可以键入 ‘％’ 字符是在熟悉SQL，或者您可以使用更常见‘*’
为方便起见，并daloRADIUS将它翻译成‘％’
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>新组回复 </b></h200> - 添加一组回答的 <br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>删除组回复</b></h200> - 删除一组回答的 <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>列表组回复</b></h200> - 组回复列表<br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>编辑组回答 </b></h200> - 编辑回答一组 <br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>搜索组的回复</b></h200> - 搜索组应答</ 绘图 <br/>
使用通配符，你既可以键入 ‘％’ 字符是在熟悉SQL，或者您可以使用更常见‘*’
为方便起见，并daloRADIUS将它翻译成‘％’
";


$l['helpPage']['mngradippool'] = "
<h200><b>IP地址池列表</b></h200> - 列表配置IP地址池及其分配IP地址 <br/>
<h200><b>新建IP地址池/b></h200> - 添加一个新建IP地址配置IP地址池 <br/>
<h200><b>编辑IP地址池</b></h200> - 编辑一个IP地址配置IP地址池 <br/>
<h200><b>删除IP地址池</b></h200> - 删除一个IP地址从一个配置IP地址池 <br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>IP地址池列表</b></h200> - 列表配置IP地址池及其分配IP地址 <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>新建IP地址池</b></h200> - 添加一个新建IP地址配置IP地址池 <br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>编辑IP地址池</b></h200> - 编辑一个IP地址配置IP地址池 <br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>删除IP地址池</b></h200> - 删除一个IP地址从一个配置IP地址池 <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "删除一个nas ip /从数据库主机条目必须提供的ip /主机帐户";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";

$l['helpPage']['mngradhunt'] = "HuntGroup开始工作之前,请阅读 <a href='http://wiki.freeradius.org/SQL_Huntgroup_HOWTO' target='_blank'>http://wiki.freeradius.org/SQL_Huntgroup_HOWTO</a>.
<br/>
特别是:
...
<i>找到你的radiusd.conf或网站功能/ defaut配置文件中的授权部分和编辑它。在预处理模块后，授权部分的顶部插入这些行：</i>
<br/>
<pre>
update request {
    Huntgroup-Name := \"%{sql:select groupname from radhuntgroup where nasipaddress=\\\"%{NAS-IP-Address}\\\"}\"
}
</pre>
<i> 这是使用IP地址作为回报huntgroup名字中的一个重要radhuntgroup表中执行查找。然后添加一个属性/值对该请求的属性名称是huntgroup的名字和它的值就是从SQL查询返回的。如果查询没有发现任何值是空字符串。 </i>";


$l['helpPage']['mngradhuntdel'] = "从数据库中删除组条目必须提供的ip /主机和端口id";
$l['helpPage']['mngradhuntnew'] = "";
$l['helpPage']['mngradhuntlist'] = "";
$l['helpPage']['mngradhuntedit'] = "";

$l['helpPage']['mnghsdel'] = "从数据库中删除一个热点必须提供热点的名称<br/>";
$l['helpPage']['mnghsedit'] = "您可以编辑以下细节热点<br/>";
$l['helpPage']['mnghsnew'] = "您可以填写以下细节的新热点除了数据库";
$l['helpPage']['mnghslist'] = "数据库中的所有热点的列表。您可以使用快速链接来编辑或删除数据库中的一个热点。";

$l['helpPage']['configdb'] = "
<b>数据库设置</b> - 配置数据库引擎，连接设置，表名，如果
默认没有被使用，并在数据库中的口令加密类型.<br/>
<h200><b>全局设置</b></h200> - 数据库存储引擎<br/>
<h200><b>表设置</b></h200> - 如果不使用默认FreeRADIUS模式你可以改变名字
表的名称<br/>
<h200><b>高级设置</b></h200> - 你想在数据库中存储用户的密码不在是
纯文本,而是让它以某种方式你可以选择一个MD5或加密<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>语言设置</b></h200> - 配置界面语言<br/>
";
$l['helpPage']['configuser'] = "
<h200><b>用户设置</b></h200> - 配置用户管理行为。<br/>
";
$l['helpPage']['configmail'] = "
<h200><b>用户设置</b></h200> - 配置邮件设置。<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>日志设置</b></h200> - 配置日志规则和设施 <br/>
请确保您指定的文件名写权限的网络服务器<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>界面设置</b></h200> - 配置界面布局设置和behvaiour <br/>
";
$l['helpPage']['configmain'] = "
<b>全局设置</b><br/>
<h200><b>数据库设置</b></h200> - 配置数据库引擎，连接设置，表名，如果
默认没有被使用，并在数据库中的口令加密的类型。<br/>
<h200><b>语言设置</b></h200> - 配置界面语言。<br/>
<h200><b>语言设置</b></h200> - 配置日志记录的规则和设施 <br/>
<h200><b>接口设置</b></h200> - 配置界面布局设置和behvaiour <br/>

<b>子类配置</b>
<h200><b>维护</b></h200> - 维护选项用于测试用户连接或终止会话 <br/>
<h200><b>设备/b></h200> - 设备配置访问控制列表(ACL) <br/>
";
$l['helpPage']['configbusiness'] = "
<b>业务信息</b><br/>
<h200><b>业务联系</b></h200> - 设置业务联系人信息(所有者、标题、地址、电话等)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b>维护</b><br/>
<h200><b>测试用户连接</b></h200> - 发送一个访问请求的RADIUS服务器检查用户凭证是有效的<br/>
<h200><b>断开连接的用户</b></h200> - 发出一个POD（包断开连接）或CoA（改变权限）的数据包NAS服务器
要断开用户并在一个特定的NAS终止他/她会话。<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>测试用户连接</b></h200> - RADIUS服务器的访问请求发送给检查用户凭证是否有效。<br/>
ddaloradius使用RADIUS客户端二进制实用程序来执行测试并返回命令结果完成后。 <br/>
daloRADIUS计数的RADIUS客户端的二进制文件在\$ PATH环境变量可用，如果不是，请
更正库/extensions/maintenance_radclient.php 文件<br/><br/>

请注意，它可能需要一段时间的测试完成（几秒[ 10-20秒左右]）由于故障和
radclient将重发的数据包。

在“高级”选项卡可以调整测试选项：<br/>
超时等待超时秒后重试（可能是一个浮点数）<br/>
如果超时重试，重试发送该数据包的重试的次数。<br/>
计数发送每个数据包的数倍<br/>
从并行文件请求发送的数据包数<br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>断开用户</b></h200> - 发出一个POD（包断开连接）或CoA（改变权限）的数据包NAS服务器
要断开用户并在一个特定的NAS终止他/她会话。<br/>
终止用户会话，要求在NAS支持POD或AOC包类型，请咨询您的NAS设备或
文档这一点。此外，它需要知道在NAS端口POD或AOC数据包，而较新建NAS的使用端口3799
而其他的被配置成接收在端口1700的数据包。

ddaloradius使用RADIUS客户端二进制实用程序来执行测试并返回命令结果完成后。 <br/>
daloRADIUS计数的RADIUS客户端的二进制文件在\$ PATH环境变量可用，如果不是，请
更正库/extensions/maintenance_radclient.php 文件<br/><br/

请注意，它可能需要一段时间的测试完成（几秒[ 10-20秒左右]）由于故障和
radclient将重发的数据包。

在“高级”选项卡可以调整测试选项：<br/>
超时等待超时秒后重试（可能是一个浮点数）<br/>
如果超时重试，重试发送该数据包的重试的次数。<br/>
计数发送每个数据包的数倍<br/>
从并行文件请求发送的数据包数<br/>


";
$l['helpPage']['configoperatorsdel'] = "从数据库中删除的操作员必须提供用户名。";
$l['helpPage']['configoperatorsedit'] = "下面编辑设备用户详细信息";
$l['helpPage']['configoperatorsnew'] = "你可以填写下面的一个新建设备的用户除了数据库的详细信息";
$l['helpPage']['configoperatorslist'] = "显示所有设备的数据库";
$l['helpPage']['configoperators'] = "设备的配置";
$l['helpPage']['configbackup'] = "执行备份";
$l['helpPage']['configbackupcreatebackups'] = "创建备份";
$l['helpPage']['configbackupmanagebackups'] = "管理备份";


$l['helpPage']['graphmain'] = "
<b>图表</b><br/>
<h200><b>总体登录/点击</b></h200> - 绘制的每一段时间内的特定用户的使用情况图表。
所有登录 （或 '点击' 到 NAS） 是通过图形方式显示以及表格列表。<br/>
<h200><b>总下载统计</b></h200> - 绘制的每一段时间内的特定用户的使用情况图表
由客户端下载的数据量是正在被计算的值。该图伴随下载量实时显示<br/>
<h200><b>总体上传统计</b></h200> - 绘制的每一段时间内的特定用户的使用情况图表。
由客户端上传的数据量是正在被计算的值。该图伴随上传量实时显示<br/>
<br/>
<h200><b>所有时间登录/点击</b></h200> - 绘出登录到服务器上的给定时间周期的图形图表。<br/>
<h200><b>所有流量对比</b></h200> - 绘制图表的下载和上传 statisticse.</br>
<h200><b>登录用户</b></h200> - 绘制指定期间中的登录的用户的图表
按天、 月、 年仅按月份和年份图每小时图或筛选器筛选 （选择 \"---\"天） 图的最小和最大登录的用户在所选的一个月.
";
$l['helpPage']['graphsalltimelogins'] = "登录到服务器的历史统计数据基于分布在一段时间内";
$l['helpPage']['graphsalltimetrafficcompare'] = "通过服务器基于分布在一段时间内流量数据统计。";
$l['helpPage']['graphsloggedusers'] = "绘制已登录的总的图表";
$l['helpPage']['graphsoveralldownload'] = "绘制图表服务器的已下载字节数";
$l['helpPage']['graphsoverallupload'] = "绘制图表的上传到服务器的字节";
$l['helpPage']['graphsoveralllogins'] = "绘制图表对服务器的登录尝试";



$l['helpPage']['rephistory'] = "显示所有活动执行管理项目和提供信息<br/>
创建日期,创建和更新日期和更新历史领域";
$l['helpPage']['replastconnect'] = "显示所有RADIUS服务器的登录尝试,成功和失败的登录";
$l['helpPage']['replogsboot'] = "监控操作系统启动日志——相当于运行dmesg命令。";
$l['helpPage']['replogsdaloradius'] = "监控daloRADIUS的日志文件";
$l['helpPage']['replogsradius'] = "监控FreeRADIUS的日志文件。";
$l['helpPage']['replogssystem'] = "监控操作系统日志文件。";
$l['helpPage']['rephb'] = "";
$l['helpPage']['rephbdashboard'] = "";
$l['helpPage']['repbatch'] = "";
$l['helpPage']['repbatchlist'] = "";
$l['helpPage']['mngbatchlist'] = "";
$l['helpPage']['mngbatchdel'] = "";
$l['helpPage']['repbatchdetails'] = "提供了一个活跃用户的这批实例的列表";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS日志</b></h200> - 监控daloRADIUS的日志文件。<br/>
<h200><b>RADIUS日志</b></h200> - 监控FreeRADIUS的日志文件,在 /var/log/freeradius/radius.log 或 /usr/local/var/log/radius/radius.log.
日志文件可能在其他可能的地方,如果是这样的话请相应地调整配置.<br/>
<h200><b>系统日志</b></h200> - 监控操作系统日志文件,在 /var/log/syslog or /var/log/消息在大多数平台上。
日志文件可能在其他可能的地方,如果是这样的话请相应地调整配置。<br/>
<h200><b>Boot Log</b></h200> - 监控操作系统启动日志——相当于运行dmesg命令。
";
$l['helpPage']['repmain'] = "
<b>普通的报告</b><br/>
<h200><b>在线用户</b></h200> - 提供了一个清单的所有用户
发现在线通过会计表在数据库中。为用户正在执行的检查
没有结束时间(AcctStopTime)。重要的是要注意,这些用户也会过期的会话
这当NASs由于某种原因未能发送accounting-stop包。.<br/>
<h200><b>Last Connection Attempts</b></h200> - 提供所有Access-Accept的清单和Access-Reject(接受和失败)登录为用户。 <br/>
这些从数据库的postauth表需要定义FreeRADIUS配置文件的实际记录这些.<br/>
<h200><b>用户使用详情</b></h200> - 提供了一个清单的前N用户带宽消耗和会话时间使用br/><br/>
<b>Sub-范畴的报告</b><br/>
<h200><b>Logs</b></h200> - 提供daloRADIUS日志文件、FreeRADIUSs日志文件系统的日志文件和启动日志文件<br/>
<h200><b>Status</b></h200> - 提供服务器状态信息和RADIUS组件状态";
$l['helpPage']['repstatradius'] = "提供关于服务器本身的一般信息:CPU使用率,流程,正常运行时间、内存使用情况,等等";
$l['helpPage']['repstatserver'] = "提供关于FreeRADIUS守护进程的一般信息和MySQL数据库服务器";
$l['helpPage']['repstatus'] = "<b>状态</b><br/>
<h200><b>服务器状态</b></h200> - 提供关于服务器本身的一般信息:CPU使用率,流程,正常运行时间、内存使用情况,等等。<br/>
<h200><b>RADIUS 状态</b></h200> - 提供关于FreeRADIUS守护进程的一般信息和MySQL数据库服务器";
$l['helpPage']['reptopusers'] = "下面显示记录为高级用户,那些获得了最高消费的会话
时间和带宽使用情况。清单的用户类别: ";
$l['helpPage']['repusername'] = "记录发现的用户:";
$l['helpPage']['reponline'] = "
下表显示了当前连接用户
系统。非常有可能,有陈旧的连接,
这意味着用户掉线但NAS没有发送或不是
能够发送停止会计包RADIUS服务器。";

$l['helpPage']['mnglistall'] = "清单中的用户数据库";
$l['helpPage']['mngsearch'] = "搜索用户： ";
$l['helpPage']['mngnew'] = "您可以填写以下信息新用户除了数据库<br/>";
$l['helpPage']['mngedit'] = "编辑下面的用户详细信息<br/>";
$l['helpPage']['mngdel'] = "为了从数据库中删除用户条目，你必须提供帐户的用户名<br/>";
$l['helpPage']['mngbatch'] = "您可以填写以下信息新用户除了数据库。<br/>
请注意，这些设置将适用于所有你所创建的用户。<br/>";
$l['helpPage']['mngnewquick'] = "下面的用户/卡是预付费类型。<br/>
在时间信用证规定的时间内将被用作 Session-Timeout（会话超时） 和 Max-All-Session（最大-所有-会话） RADIUS属性";

// 账单部分
$l['helpPage']['acctactive'] = "
    规定，将被证明是用于跟踪活动或过期的数据库中的用户有用的信息
其中有一个到期属性或马克斯 - 所有会话属性的用户而言。
<br/>
";
$l['helpPage']['acctall'] = "
    为数据库中的所有会话的完整的会计信息。
<br/>
";
$l['helpPage']['acctdate'] = "
    为给定的2日期为特定用户之间的所有会话完整的会计信息。
<br/>
";
$l['helpPage']['acctipaddress'] = "
    为起源与特定IP地址的所有会话的完整的会计信息。
<br/>
";

$l['helpPage']['acctplans'] = "";
$l['helpPage']['acctmain'] = "
<b>General Accounting</b><br/>
<h200><b>User Accounting</b></h200> -
    为数据库中的一个特定用户的所有会话的完整的会计信息。
<br/>
<h200><b>IP Accounting</b></h200> -
    为起源与特定IP地址的所有会话的完整的会计信息。
<br/>
<h200><b>NAS Accounting</b></h200> -
    为所有的特定NAS的IP地址已办理了全面的会话计费信息。
<br/>
<h200><b>Date Accounting</b></h200> -
    Provides对于给定的2日期为特定用户之间的所有会话完整的会计信息。
<br/>
<h200><b>All Accounting Records</b></h200> -
    为数据库中的所有会话的完整的会计信息。
<br/>
<h200><b>Active Records Accounting</b></h200> -
    规定，将被证明是用于跟踪活动或过期的数据库中的用户有用的信息
其中有一个到期属性或 Max-All-Session（最大-所有-会话）属性的用户而言。
<br/>

<br/>
<b>Sub-Category Accounting</b><br/>
<h200><b>Custom</b></h200> -
    提供了最灵活的自定义查询到数据库上运行。
<br/>
<h200><b>Hotspots</b></h200> -
    提供不同的管理热点信息、比较,和其他有用的信息。
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    提供完整的会计信息的所有会话的具体处理NAS IP地址。
<br/>
";
$l['helpPage']['acctusername'] = "
    提供完整的会计信息对特定用户的数据库中的所有会话。
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    提供完整的会计信息的所有会话起源于这个特定的热点。
这个列表是计算清单只有那些与CalledStationId radacct表中的记录
字段匹配一个热点中的热点的MAC地址条目的管理数据库。
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    提供了基本的会计信息比较数据库中找到的所有活跃的热点。
       会计提供的信息:< br / > < br / >
    热点名称——热点的名称< br / >
    独特的用户-用户已登陆,只有通过这个热点< br / >
    总点击——总登录,进行从这个热点(独特的和非独特的)< br / >
    平均时间——平均时间用户花在这个热点< br / >
    总时间——所有用户的accumolated花时间在这个热点<br/>

<br/>
    提供了一个图块不同的比较了< br / >
    图:< br / > < br / >
    每个热点分布的独特用户< br / >
    分配每个热点的点击< br / >
    每个热点分布的时间使用 <br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    提供完整的会计信息的所有会话起源于这个特定的热点。
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    提供了基本的会计信息比较数据库中找到的所有活跃的热点。
提供了一个图块不同的比较。
<br/>
";
// 会计自定义查询部分
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    提供最灵活的自定义查询数据库上运行。< br / >
你可以调整查询的max通过修改设置在左侧栏。< br / >
<br/>
    <b> 日期< / b > -设置开始和结束日期.
<br/>
    <b> < / b >——设置数据库中的字段(像一个键)你想匹配,选择如果值
比赛应该等于(=)或它包含你搜索的一部分价值(如一个正则表达式)。如果你
选择使用包含操作符你不应该添加任何常见的通配符“*”而是
您输入的值将自动搜索这种形式:* *价值(或mysql风格:%值%)。
<br/>
    < b > < / b >查询会计领域,你可以选择你想要的字段出现在结果中
列表。
< br / >
< b > < / b >订单——选择你想订场的结果和它的类型(提升
或降序)
< br / >
";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200><b>清理过期会话</b></h200> -
    ‘过期会话’可能经常存在因为会影响NAS无法提供计费停止纪录<<br/>
    如不不清理长时间的过期用户会话，会导致假的用户登录记录的存在
    记录 (false positive).
<br/>
<h200><b>删除会计记录</b></h200> -
    删除数据库中的会计记录。要执行该操作，或者要允许其他用户。
    除了管理员访问这个页面。
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
    编辑地图模式，在这种模式下你可以简单地通过点击添加或删除热点
在地图上的位置或通过点击一个热点（分别）<br/><br/>
    <b> 添加热点 </b> - 只需点击一个清晰的地图上的位置,你将提供
热点的名称和它的MAC地址。这些关键细节后用于识别这个热点
在会计表中。务必提供正确的MAC地址！
<br/><br/>
    <b> 删除热点 </b> - 只需点击一个热点的图标，你确定它删除从
数据库。
<br/>
";
$l['helpPage']['gisviewmap'] = "
查看地图模式-在此模式下你可以浏览他们的热点进行布局
在利用GoogleMaps服务提供的地图图标。<br/><br/>

    <b> 点击一个热点 </b> -将提供您更深入的细节上的热点。
    如联系信息的热点，统计信息。
<br/>
";
$l['helpPage']['gismain'] = "
<b> 一般信息 </b>
GIS热点位置的提供了可视化世界各地的地图使用Google Maps API。< br / >
在管理页面你可以向数据库添加新建热点条目,那里也是一个字段
称为地理位置,这是Google Maps API使用以有定位的准确数值
位置在地图上的热点。<br/><br/>

<h200><b>2 提供的操作模式:</b></h200>
一个是<b>查看地图</b>模式使“网上冲浪”通过世界地图
查看当前位置的热点在数据库和另一个<b>编辑地图</b> -该模式
一个可以使用以创建热点的直观简单的左点击地图或删除
现有的热点条目，左键单击现有热点的旗帜。.<br/><br/>

另一个重要的问题是,网络上的每台计算机需要一个独特的注册码,你
从Google Maps API页面可以获得通过提供完整的web托管目录的地址吗
daloRADIUS服务器上的应用程序。一旦你从谷歌获得代码,只需粘贴的
注册框,然后单击“注册码”按钮来写它。
然后你可以使用谷歌地图服务。 <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "这个用户没有检查相关联的属性";
$l['messages']['noReplyAttributesForUser'] = "这个用户没有回复相关联的属性";

$l['messages']['noCheckAttributesForGroup'] = "这个组没有检查相关联的属性";
$l['messages']['noReplyAttributesForGroup'] = "这个组没有回复相关联的属性";

$l['messages']['nogroupdefinedforuser'] = "这个用户没有相关联的组";
$l['messages']['wouldyouliketocreategroup'] = "你想创建一个？";


$l['messages']['missingratetype'] = "错误：缺失价格类型";
$l['messages']['missingtype'] = "错误：丢失类型";
$l['messages']['missingcardbank'] = "错误：丢失银行卡";
$l['messages']['missingrate'] = "错误：丢失价格";
$l['messages']['success'] = "成功";
$l['messages']['gisedit1'] = "欢迎,你目前在编辑模式";
$l['messages']['gisedit2'] = "从地图和数据库删除当前标记?";
$l['messages']['gisedit3'] = "请输入热点的名称";
$l['messages']['gisedit4'] = "添加当前标记到数据库吗?";
$l['messages']['gisedit5'] = "请输入热点的名称";
$l['messages']['gisedit6'] = "请输入MAC热点的地址";

$l['messages']['gismain1'] = "成功更新谷歌地图API注册码";
$l['messages']['gismain2'] = "错误:无法打开文件写入";
$l['messages']['gismain3'] = "检查文件的权限。这个文件应该是网络服务器的用户/组可写的。";
$l['messages']['gisviewwelcome'] = "欢迎来到Enginx视觉地图";

$l['messages']['loginerror'] = "<br/><br/>下面之一：<br/>
1. 错误的用户名/密码<br/>
2. 管理员已经登录的（只允许一个实例）<br/>
3. 似乎有不止一个的管理员的用户在数据库中<br/>
";

$l['buttons']['savesettings'] = "保存设置";
$l['buttons']['apply'] = "应用";

$l['menu']['Home'] = "主页";
$l['menu']['Managment'] = "管理";
$l['menu']['Reports'] = "报告";
$l['menu']['Accounting'] = "账单";
$l['menu']['Billing'] = "记账";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "图表";
$l['menu']['Config'] = "配置";
$l['menu']['Help'] = "帮助";

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

