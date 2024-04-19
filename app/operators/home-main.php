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
 * Description:    The script is a dashboard that retrieves and displays statistics related to
 *                 RADIUS users, NAS devices, and hotspots. It generates cards for each statistic,
 *                 fetches recent connection attempts and online users
 *                 and presents the data in formatted tables.
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    // setting table-related parameters first
    $tableSetting = [
        'postauth' => [
            'user' => ($configValues['FREERADIUS_VERSION'] == '1') ? 'user' : 'username',
            'date' => ($configValues['FREERADIUS_VERSION'] == '1') ? 'date' : 'authdate'
        ]
    ];

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logQuery = "performed query for all usernames on page: ";
    $logDebugSQL = "";

    // print HTML prologue
    $title = t('button', 'Dashboard');

    print_html_prologue($title, $langCode);

    // Consolidated SQL queries
    $total_users = count_users($dbSocket);
    $total_hotspots = count_hotspots($dbSocket);
    $total_nas = count_nas($dbSocket);


    function print_title($title, $href, $icon) {
        echo <<<HTML
        <span class="d-flex align-items-center justify-content-start mb-2">
            <h1 class="fs-4 m-0">{$title}</h1>
            <a class="ms-2 text-decoration-none" href="{$href}"><i class="bi $icon fs-6"></i></a>
        </span>
    HTML;
    }

    function print_dashboard_table_head($headers) {
        echo '<table class="table table-hover table-striped"><tr>';
        
        foreach ($headers as $header) {
            printf('<th>%s</th>', $header);
        }

        echo '</tr>';
    }

    function print_dashboard_table_row($items) {
        echo '<tr>';
        foreach ($items as $item) {
            printf('<td>%s</td>', $item);
        }
        echo '</tr>';
    }

    function print_dashboard_info_message($message) {
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo <<<EOF
        <div class="col-12 m-0">
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>{$message}</div>
          </div>
        </div>
        EOF;
    }

    function generateCard($title, $total, $linkText, $linkURL, $bgColor, $icon) {
        return <<<HTML
<div class="col-md-4 m-0 p-0">
    <div class="card m-1 rounded-0">
        <div class="row g-0">
            <div class="d-none d-md-flex col-md-2 text-bg-{$bgColor} align-items-center justify-content-center" style="--bs-bg-opacity: .9;">
                <i class="bi bi-{$icon} fs-2"></i>
            </div>
            <div class="col-md-10 p-1 d-flex align-items-center justify-content-center flex-column text-bg-{$bgColor}">
                <h5 class="card-title">{$title}</h5>
                <p class="card-text">{$total}</p>
                <a href="{$linkURL}" class="btn btn-light btn-sm">{$linkText}</a>
            </div>
        </div>
    </div>
</div>
   
HTML;
     
    }
    
    // Define card parameters

    $card_params = [
        [
            "title" => t('submenu', 'Users'),
            "total" => sprintf("%s: <strong>%d</strong>", t('all', 'Total'), $total_users),
            "linkText" => "Go to users list",
            "linkURL" => "mng-list-all.php",
            "bgColor" => "success",
            "icon" => "people-fill"
        ],
        [
            "title" => t('submenu', 'Nas'),
            "total" => sprintf("%s: <strong>%d</strong>", t('all', 'Total'), $total_nas),
            "linkText" => "Go to NAS list",
            "linkURL" => "mng-rad-nas-list.php",
            "bgColor" => "danger",
            "icon" => "router-fill"
        ],
        [
            "title" => t('submenu', 'Hotspots'),
            "total" => sprintf("%s: <strong>%d</strong>", t('all', 'Total'), $total_hotspots),
            "linkText" => "Go to hotspots list",
            "linkURL" => "mng-hs-list.php",
            "bgColor" => "primary",
            "icon" => "wifi"
        ]
    ];

    $version = t('all', 'daloRADIUS');
    $copyright = strip_tags(t('all', 'copyright2'));
    
    echo <<<HTML
<span class="d-flex align-items-center justify-content-start mb-2">
    <h1 class="fs-4 m-0">daloRADIUS</h1>
    <a tabindex="0" class="ms-2 text-decoration-none btn btn-light" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-title="{$version}" data-bs-content="{$copyright}">
        <i class="fs-6 bi bi-c-circle"></i>
    </a>
</span>

HTML;
    
    // Print cards using parameters
    echo '<div class="row mb-4">';
    foreach ($card_params as $params) {
        echo generateCard($params["title"], $params["total"], $params["linkText"], $params["linkURL"], $params["bgColor"], $params["icon"]);
    }
    echo '</div>';

    ////////////////////////////////////////

    echo '<div class="row mb-4">';

    $sql = sprintf("SELECT %s AS `username`, reply, %s AS `datetime` FROM %s ORDER BY `datetime` DESC LIMIT 10",
                   $tableSetting['postauth']['user'], $tableSetting['postauth']['date'],
                   $configValues['CONFIG_DB_TBL_RADPOSTAUTH']);
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    echo '<div class="col-sm-12 col-md-6 m-0 px-3">';
    $title = t('button', 'LastConnectionAttempts');
    print_title($title, "rep-lastconnect.php", "bi-box-arrow-up-right");


    if ($numrows > 0) {
        $headers = array(t('all', 'Username'), t('all', 'RADIUSReply'), t('all', 'Date'));
        print_dashboard_table_head($headers);
        
        while ($row = $res->fetchRow()) {
            // Apply htmlspecialchars to each element of the row
            list($user, $reply, $datetime) = array_map(function($value) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }, $row);

            $reply = sprintf('<span class="text-%s">%s</span>',
                            (($reply == "Access-Reject") ? "danger" : "success"), $reply);

            print_dashboard_table_row(array($user, $reply, $datetime));
        }

        echo '</table>';
    
    } else {
        print_dashboard_info_message('no data to show');
    }

    echo '</div>';
    $sql = sprintf("SELECT `username`, `acctstarttime` FROM %s
                     WHERE `acctstoptime` IS NULL OR `acctstoptime`='0000-00-00 00:00:00'
                     ORDER BY `acctstarttime` DESC LIMIT 10",
                   $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    echo '<div class="col-sm-12 col-md-6 m-0 px-3">';
    print_title('Currently online', "rep-online.php?orderBy=acctstarttime&orderType=desc", "bi-box-arrow-up-right");

    if ($numrows > 0) {
        print_dashboard_table_head(array(t('all', 'Username'), 'Online since'));
        
        while ($row = $res->fetchRow()) {
            // Apply htmlspecialchars to each element of the row
            $row = array_map(function($value) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }, $row);
            
            print_dashboard_table_row($row);
        }

        echo '</table>';
    
    } else {
        print_dashboard_info_message('no data to show');
    }

    echo <<<HTML
        </div>
    </div>
    <div class="row">
HTML;

    $sql = sprintf("SELECT DISTINCT(ra.username) AS `username`, 
                    SUM(ra.AcctSessionTime) AS `session_time`,
                    SUM(ra.AcctInputOctets) AS `uploaded_bytes`, 
                    SUM(ra.AcctOutputOctets) AS `downloaded_bytes`
                    FROM %s AS ra 
                    WHERE ra.acctstarttime >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                    GROUP BY `username` 
                    ORDER BY `session_time` DESC 
                    LIMIT 10", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    
    // Today's date
    $today = date("Y-m-d");
    // Date one month ago
    $one_month_ago = date("Y-m-d", strtotime("-1 month"));
    $href = sprintf('rep-topusers.php?startdate=%s&enddate=%s&orderBy=Time&orderType=desc', $one_month_ago, $today);
    $title = "Last month top users";

    echo '<div class="col-12 m-0 px-3">';
    print_title($title, $href, 'bi-box-arrow-up-right');

    if ($numrows > 0) {
        print_dashboard_table_head(array(t('all', 'Username'), t('all', 'TotalSessionTime'), t('all', 'Upload'), t('all', 'Download')));

        while ($row = $res->fetchRow()) {
            list($username, $session_time, $uploaded_bytes, $downloaded_bytes) = array_map(function($value) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }, $row);

            $session_time = time2str($session_time);
            $uploaded_bytes = toxbyte($uploaded_bytes);
            $downloaded_bytes = toxbyte($downloaded_bytes);

            print_dashboard_table_row(array($username, $session_time, $uploaded_bytes, $downloaded_bytes));
        }

        echo '</table>';
    
    } else {
        print_dashboard_info_message('no data to show');
    }

    echo '</div>';

    echo '</div>';

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);

    $inline_extra_js = <<<JS
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
JS;

    print_footer_and_html_epilogue($inline_extra_js);
