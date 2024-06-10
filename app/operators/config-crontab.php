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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";

    $is_smtp_enabled = strtolower($configValues['CONFIG_MAIL_ENABLED']) === 'yes';
    $dalo_crontab_file = implode(DIRECTORY_SEPARATOR, [ $configValues['CONTRIB_SCRIPTS'], 'dalo-crontab' ]);
    $crontab_bin = str_replace("\n", "", shell_exec("which crontab || command -v crontab"));
    $storeConfig = true;
    $noticeMsg = '<small class="mt-5"><strong>Please note</strong>: <a href="config-mail-settings.php">SMTP server configuration</a> is required beforehand.</small>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
            // Validate and set default values
            $configValues['CONFIG_FIX_STALE_INTERVAL'] = max(1, intval($_POST['CONFIG_FIX_STALE_INTERVAL'] ?? 60));
            $configValues['CONFIG_FIX_STALE_GRACE'] =
                max(0, intval($_POST['CONFIG_FIX_STALE_GRACE'] ?? intdiv($configValues['CONFIG_FIX_STALE_INTERVAL'], 2)));
            $configValues['CONFIG_FIX_STALE_ENABLED'] = in_array(strtolower(trim($_POST['CONFIG_FIX_STALE_ENABLED'] ?? 'no')), ["yes", "no"])
                                                       ? strtolower(trim($_POST['CONFIG_FIX_STALE_ENABLED'])) : "no";
            $configValues['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] = max(1, intval($_POST['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] ?? 15));
            $configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] = max(1, intval($_POST['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] ?? 1073741824));
            $configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] =
                max(1, intval($_POST['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] ?? intdiv($configValues['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'], 2)));

            // Validate and set enabled/disabled states
            if ($is_smtp_enabled) {
                $configValues['CONFIG_NODE_STATUS_MONITOR_ENABLED'] =
                    in_array(strtolower(trim($_POST['CONFIG_NODE_STATUS_MONITOR_ENABLED'] ?? 'no')), ["yes", "no"])
                        ? strtolower(trim($_POST['CONFIG_NODE_STATUS_MONITOR_ENABLED'])) : "no";
                $configValues['CONFIG_USER_TRAFFIC_MONITOR_ENABLED'] =
                    in_array(strtolower(trim($_POST['CONFIG_USER_TRAFFIC_MONITOR_ENABLED'] ?? 'no')), ["yes", "no"])
                        ? strtolower(trim($_POST['CONFIG_USER_TRAFFIC_MONITOR_ENABLED'])) : "no";

                $configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'] =
                    filter_var(trim($_POST['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'] ?? ''), FILTER_VALIDATE_EMAIL)
                        ? trim($_POST['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO']) : '';
                $configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'] =
                    filter_var(trim($_POST['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'] ?? ''), FILTER_VALIDATE_EMAIL)
                        ? trim($_POST['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO']) : '';
            }

            // Generate crontab contents if at least one recurring job is enabled
            $enabled = [$configValues['CONFIG_FIX_STALE_ENABLED'],
                        $configValues['CONFIG_NODE_STATUS_MONITOR_ENABLED'],
                        $configValues['CONFIG_USER_TRAFFIC_MONITOR_ENABLED']];
            if (in_array("yes", $enabled)) {
                $contents = <<<EOF
# daloRADIUS - RADIUS Web Platform
# Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
#
# Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
#

CONTRIB_SCRIPTS="{$configValues['CONTRIB_SCRIPTS']}"

EOF;

                $jobs = [
                    'CONFIG_FIX_STALE_ENABLED' => ['file' => 'maintenance/fix-stale-sessions.php', 'interval' => '*/1'],
                    'CONFIG_NODE_STATUS_MONITOR_ENABLED' => ['file' => 'monitor/node-status-monitor.php', 'interval' => '*/5'],
                    'CONFIG_USER_TRAFFIC_MONITOR_ENABLED' => ['file' => 'monitor/user-traffic-monitor.php', 'interval' => '*/20']
                ];

                foreach ($jobs as $key => $job) {
                    if ($configValues[$key] === 'yes') {
                        $contents .= <<<EOF

# This job {$job['file']} runs every {$job['interval']} minutes.
{$job['interval']} * * * * /usr/bin/php "\$CONTRIB_SCRIPTS/{$job['file']}" >/dev/null 2>&1

EOF;
                    }
                }

                if (is_writable($dalo_crontab_file)) {
                    
                    $result = file_put_contents($dalo_crontab_file, $contents);
                    if ($result > 0) {
                        $exec1 = sprintf("%s -r", $crontab_bin);

                        $result1 = exec($exec1, $output1, $result_code1);
                        if ($result1 !== false) {
                            $exec2 = sprintf("%s %s", $crontab_bin, $dalo_crontab_file);
                            $result2 = exec($exec2, $output2, $result_code2);

                            if ($result2 === false) {
                                $storeConfig = false;
                                $failureMsg = "Error while loading new recurring tasks";
                            }

                        } else {
                            $storeConfig = false;
                            $failureMsg = "Error while removing old recurring tasks";
                        }

                    } else {
                        $storeConfig = false;
                        $failureMsg = sprintf("Error: failure while writing <strong>%s</strong>", $dalo_crontab_file);
                    }

                } else {
                    $storeConfig = false;
                    $failureMsg = sprintf("Error: <strong>%s</strong> is not writable", $dalo_crontab_file);
                }

            } else {
                $exec1 = sprintf("%s -r", $crontab_bin);

                $result1 = exec($exec1, $output1, $result_code1);
                if ($result1 === false) {
                    $storeConfig = false;
                    $failureMsg = "Error while removing old recurring tasks";
                }

            }

            if ($storeConfig) {
                include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'config_write.php' ]);
            }

        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }

    
    // print HTML prologue
    $title = t('Intro','configcrontab.php');
    $help = t('helpPage','configcrontab');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    // set navbar stuff
    $navkeys = array(
                      array( 'stale-session', "Stale sessions" ),
                      array( 'node-monitor', "Node Monitor" ),
                      array( 'user-monitor', "User Monitor" ),
                      array( 'crontab-output', "Crontab output" ),
                    );

    // print navbar controls
    print_tab_header($navkeys);

    open_form();

    // open tab wrapper
    open_tab_wrapper();

    // tab 0
    open_tab($navkeys, 0, true);

    $fieldset0_descriptor = array( "title" => $navkeys[0][1] );

    open_fieldset($fieldset0_descriptor);

    $input_descriptors0 = [];

    $input_descriptors0[] = array(
        "type" => "select",
        "options" => ["yes", "no"],
        "caption" => sprintf("Enable '%s' check", strtolower($navkeys[0][1])),
        "name" => 'CONFIG_FIX_STALE_ENABLED',
        "selected_value" => $configValues['CONFIG_FIX_STALE_ENABLED'] ?? "no",
    );
 
    $interval = max(1, intval($configValues['CONFIG_FIX_STALE_INTERVAL'] ?? 60));
    $input_descriptors0[] = array(
                                    "id" => "CONFIG_FIX_STALE_INTERVAL",
                                    "name" => "CONFIG_FIX_STALE_INTERVAL",
                                    "caption" => 'Stale Interval (in seconds)',
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => $interval,
                                 );

    $input_descriptors0[] = array(
                                    "id" => "CONFIG_FIX_STALE_GRACE",
                                    "name" => "CONFIG_FIX_STALE_GRACE",
                                    "caption" => 'Stale Grace (in seconds)',
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => max(0, intval($configValues['CONFIG_FIX_STALE_GRACE'] ?? intdiv($interval, 2))),
                                 );

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_fieldset();

    close_tab();

    // tab 1
    open_tab($navkeys, 1);

    $fieldset1_descriptor = array( "title" => $navkeys[1][1], "disabled" => !$is_smtp_enabled );

    open_fieldset($fieldset1_descriptor);

    $input_descriptors2 = [];

    $input_descriptors1[] = array(
        "type" => "select",
        "options" => ["yes", "no"],
        "caption" => sprintf("Enable '%s' check", strtolower($navkeys[1][1])),
        "name" => 'CONFIG_NODE_STATUS_MONITOR_ENABLED',
        "selected_value" => $configValues['CONFIG_NODE_STATUS_MONITOR_ENABLED'] ?? "no",
    );

    $input_descriptors1[] = array(
                                    "type" => "email",
                                    "caption" => "Send email to",
                                    "name" => 'CONFIG_NODE_STATUS_MONITOR_EMAIL_TO',
                                    "value" => $configValues['CONFIG_NODE_STATUS_MONITOR_EMAIL_TO'],
                                    "tooltipText" => "The email address where alert email will be sent",
                                  );

    $input_descriptors1[] = array(
                                    "id" => "CONFIG_NODE_STATUS_MONITOR_HARD_DELAY",
                                    "name" => "CONFIG_NODE_STATUS_MONITOR_HARD_DELAY",
                                    "caption" => 'Hard delay (in seconds)',
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => max(1, intval($_POST['CONFIG_NODE_STATUS_MONITOR_HARD_DELAY'] ?? 15)),
                                );
 
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    if (!$is_smtp_enabled) {
        echo $noticeMsg;
    }

    close_fieldset();

    close_tab();

    // tab 2
    open_tab($navkeys, 2);

    $fieldset2_descriptor = array( "title" => $navkeys[2][1], "disabled" => !$is_smtp_enabled );

    open_fieldset($fieldset2_descriptor);

    $input_descriptors2 = [];

    $input_descriptors2[] = array(
                                    "type" => "select",
                                    "options" => ["yes", "no"],
                                    "caption" => sprintf("Enable '%s' check", strtolower($navkeys[2][1])),
                                    "name" => 'CONFIG_USER_TRAFFIC_MONITOR_ENABLED',
                                    "selected_value" => $configValues['CONFIG_USER_TRAFFIC_MONITOR_ENABLED'] ?? "no",
                                 );
    
    $input_descriptors2[] = array(
                                    "type" => "email",
                                    "caption" => "Send email to",
                                    "name" => 'CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO',
                                    "value" => $configValues['CONFIG_USER_TRAFFIC_MONITOR_EMAIL_TO'],
                                    "tooltipText" => "The email address where alert email will be sent",
                                  );

    $hardlimit = max(1, intval($_POST['CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT'] ?? 1073741824));

    $input_descriptors2[] = array(
                                    "id" => "CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT",
                                    "name" => "CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT",
                                    "caption" => 'Soft limit (in bytes)',
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => max(1, intval($configValues['CONFIG_USER_TRAFFIC_MONITOR_SOFTLIMIT'] ?? intdiv($hardlimit, 2))),
                                );

     $input_descriptors2[] = array(
                                    "id" => "CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT",
                                    "name" => "CONFIG_USER_TRAFFIC_MONITOR_HARDLIMIT",
                                    "caption" => 'Hard limit (in bytes)',
                                    "type" => "number",
                                    "min" => "1",
                                    "value" => $hardlimit,
                                );
 
    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    if (!$is_smtp_enabled) {
        echo $noticeMsg;
    }

    close_fieldset();

    close_tab();

    open_tab($navkeys, 3);
    printf('<h2 class="fs-5">%s</h2>', $navkeys[3][1]);
    echo '<pre class="p-2">';
    $exec = sprintf("%s -l 2>&1", $crontab_bin);
    exec($exec, $output, $return_status);
    
    foreach($output as $text) {
        printf("%s\n", htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
    echo '</pre>';
    close_tab();

    close_tab_wrapper();

    // end
    $input_descriptors3 = array();
    $input_descriptors3[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );
    $input_descriptors3[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );

    foreach ($input_descriptors3 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();
