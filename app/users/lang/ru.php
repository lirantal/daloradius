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
 * Description:    Russian language file for user portal application
 *
 * Authors:        Evgeniy Kozhuhovskiy
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/ru.php') !== false) {
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

$l['all']['Amount'] = "Сумма";
$l['all']['Balance'] = "Баланс";
$l['all']['ClientName'] = "Имя клиента";
$l['all']['Date'] = "Дата";
$l['all']['Download'] = "Загрузка";
$l['all']['EndingDate'] = "Дата окончания";
$l['all']['HotSpot'] = "HotSpot";
$l['all']['ID'] = "ID";
$l['all']['Invoice'] = "Счет";
$l['all']['InvoiceStatus'] = "Статус счета";
$l['all']['InvoiceType'] = "Тип счета";
$l['all']['IPAddress'] = "IP-адрес";
$l['all']['Language'] = "Язык";
$l['all']['NASIPAddress'] = "IP-адрес NAS";
$l['all']['NewPassword'] = "Новый пароль";
$l['all']['Password'] = "Пароль";
$l['all']['PaymentDate'] = "Дата оплаты";
$l['all']['StartingDate'] = "Дата начала";
$l['all']['StartTime'] = "Время начала";
$l['all']['Statistics'] = "Статистика";
$l['all']['Status'] = "Статус";
$l['all']['StopTime'] = "Время окончания";
$l['all']['Termination'] = "Завершение";
$l['all']['TotalBilled'] = "Общая сумма счета";
$l['all']['TotalPaid'] = "Общая оплаченная сумма";
$l['all']['TotalTime'] = "Общее время";
$l['all']['Upload'] = "Выгрузка";
$l['all']['Username'] = "Имя пользователя";
$l['all']['CurrentPassword'] = "Текущий пароль";
$l['all']['VerifyPassword'] = "Подтвердите пароль";

$l['all']['Global'] = "Общий";
$l['all']['Daily'] = "Ежедневно";
$l['all']['Weekly'] = "Еженедельно";
$l['all']['Monthly'] = "Ежемесячно";
$l['all']['Yearly'] = "Ежегодно";

$l['button']['Accounting'] = "Учет";
$l['button']['ChangeAuthPassword'] = "Изменить пароль аутентификации";
$l['button']['ChangePortalPassword'] = "Изменить пароль портала";
$l['button']['DateAccounting'] = "Учет по дате";
$l['button']['EditUserInfo'] = "Редактировать контактную информацию";
$l['button']['GenerateReport'] = "Создать отчет";
$l['button']['Graphs'] = "Графики";
$l['button']['Preferences'] = "Настройки";
$l['button']['ShowInvoice'] = "Показать счет";

$l['button']['UserDownloads'] = "Трафик загрузки";
$l['button']['UserLogins'] = "История входов";
$l['button']['UserUploads'] = "Трафик выгрузки";

$l['ContactInfo']['Address'] = "Адрес";
$l['ContactInfo']['City'] = "Город";
$l['ContactInfo']['Company'] = "Организация";
$l['ContactInfo']['Country'] = "Страна";
$l['ContactInfo']['Department'] = "Операционная единица";
$l['ContactInfo']['Email'] = "Электронная почта";
$l['ContactInfo']['FirstName'] = "Имя";
$l['ContactInfo']['HomePhone'] = "Домашний телефон";
$l['ContactInfo']['LastName'] = "Фамилия";
$l['ContactInfo']['MobilePhone'] = "Мобильный телефон";
$l['ContactInfo']['Notes'] = "Заметки";
$l['ContactInfo']['State'] = "Штат/Регион";
$l['ContactInfo']['WorkPhone'] = "Рабочий телефон";
$l['ContactInfo']['Zip'] = "Почтовый индекс";


$l['helpPage']['acctdate'] = <<<EOF
<h2 class="fs-6">Учет за датой</h2>
<p>Предоставляет подробную информацию об учете для всех сеансов между двумя указанными датами для определенного пользователя.</p>
EOF;

$l['helpPage']['acctmain'] = '<h1 class="fs-5">Общий учет</h1>' . $l['helpPage']['acctdate'];
$l['helpPage']['billinvoicelist'] = "";
$l['helpPage']['billmain'] = "";

$l['helpPage']['graphsoveralldownload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserDownloads']) . <<<EOF
<p>Генерирует графическую диаграмму, отображающую объем данных, которые вы загрузили за определенный период времени.<br>
Диаграмма сопровождается списком в виде таблицы.</p>
EOF;

$l['helpPage']['graphsoverallupload'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserUploads']) . <<<EOF
<p>Генерирует графическую диаграмму, отображающую объем данных, которые вы загрузили за определенный период времени.<br>
Диаграмма сопровождается списком в виде таблицы.</p>
EOF;

$l['helpPage']['graphsoveralllogins'] = sprintf('<h2 class="fs-6">%s</h2>', $l['button']['UserLogins']) . <<<EOF
<p>Генерирует графическую диаграмму, отображающую вашу активность входа в систему за определенный период времени.<br>
Диаграмма отображает количество входов (или "попаданий" в NAS) и сопровождается списком в виде таблицы.</p>
EOF;

$l['helpPage']['graphmain'] = '<h1 class="fs-5">Диаграммы</h1>'
                            . $l['helpPage']['graphsoveralllogins'] . $l['helpPage']['graphsoveralldownload']
                            . $l['helpPage']['graphsoverallupload'];

$l['helpPage']['loginUsersPortal'] = <<<EOF
<p>Уважаемый пользователь,</p>
<p>Добро пожаловать в портал пользователей FiloRADIUS. Мы рады, что вы присоединились к нам!</p>

<p>Авторизуясь с помощью имени пользователя и пароля вашей учетной записи, вы сможете воспользоваться широким спектром функций. Например, вы сможете легко редактировать настройки контактов, обновлять личную информацию и просматривать данные о вашей истории с помощью визуальных диаграмм.</p>

<p>Мы серьезно относимся к вашей конфиденциальности и безопасности, поэтому будьте уверены, что все ваши данные хранятся в защищенной базе данных и доступны только вам и нашему авторизованному персоналу.</p>

<p>Если у вас возникнут вопросы или понадобится помощь, не стесняйтесь обращаться в нашу службу поддержки. Мы всегда рады помочь!</p>

<p>С уважением,<br/>
Команда FiloRADIUS.</p>
EOF;

$l['messages']['loginerror'] = <<<EOF
<h1 class="fs-5">Не удается войти</h1>
<p>Если у вас возникли проблемы с входом в вашу учетную запись, вероятно, вы ввели неправильное имя пользователя и/или пароль. Пожалуйста, убедитесь, что вы правильно ввели свои учетные данные и попробуйте снова.</p>
<p>Если после проверки ваших данных вы все равно не можете войти в систему, не стесняйтесь обращаться в нашу службу поддержки за помощью. Мы всегда готовы помочь вам восстановить доступ к вашей учетной записи и вернуться к использованию наших услуг как можно скорее.</p>
EOF;


$l['helpPage']['prefmain'] = "В этом разделе вы можете управлять <strong>контактной информацией</strong>, а также паролями для входа в веб-портал и наши сервисы.";
$l['helpPage']['prefpasswordedit'] = "Используйте форму ниже, чтобы изменить пароль. Из соображений безопасности вам будет предложено ввести старый пароль и дважды ввести новый пароль, чтобы избежать ошибок.";
$l['helpPage']['prefuserinfoedit'] = "Используйте форму ниже, чтобы обновить свои контактные данные. Вы можете изменить свое имя, фамилию, адрес электронной почты, номера телефонов и другие данные по необходимости. Пожалуйста, проверьте изменения перед сохранением, чтобы обеспечить точность обновленной информации.";

$l['Intro']['acctdate.php'] = "Учет даты";
$l['Intro']['acctmain.php'] = "Страница учета";
$l['Intro']['billinvoiceedit.php'] = "Показать счет";
$l['Intro']['billinvoicereport.php'] = "Отчет о счете";
$l['Intro']['billmain.php'] = "Страница выставления счетов";
$l['Intro']['graphmain.php'] = "Графики использования";
$l['Intro']['graphsoveralldownload.php'] = "Загрузки пользователей";
$l['Intro']['graphsoveralllogins.php'] = "Входы пользователей";
$l['Intro']['graphsoverallupload.php'] = "Загрузки пользователей";
$l['Intro']['prefmain.php'] = "Страница настроек";
$l['Intro']['prefpasswordedit.php'] = "Изменить пароль";
$l['Intro']['prefuserinfoedit.php'] = "Изменить контактную информацию";
$l['menu']['Accounting'] = "Учет";
$l['menu']['Billing'] = "Выставление счетов";
$l['menu']['Graphs'] = "Графики";
$l['menu']['Home'] = "Главная";
$l['menu']['Preferences'] = "Настройки";
$l['menu']['Help'] = "Help";

$l['text']['LoginPlease'] = "Вход, пожалуйста";
$l['text']['LoginRequired'] = "Требуется вход";
$l['title']['ContactInfo'] = "Контактная информация";
$l['title']['BusinessInfo'] = "Информация об организации";
$l['title']['Invoice'] = "Счет";
$l['title']['Items'] = "Пункты";
$l['Tooltip']['invoiceID'] = "Введите идентификатор счета";
