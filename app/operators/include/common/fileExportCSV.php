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
 * Description:    The purpose of this extension is to handle exports of different
 *                 kinds like CSV andother formats to the user's browser so that
 *                 they can download a local copy of the tables listing mostly
 * 
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../../library/checklogin.php');

    $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
              ? trim($_SESSION['PREV_LIST_PAGE'])
              : "../../index.php";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accounts = null;
        $filename_prefix = "users";
        $exportToken = $_POST['export_token'] ?? null;
        $exportLifetime = 300;
        $now = time();

        if (isset($_SESSION['generated_password_exports']) &&
            is_array($_SESSION['generated_password_exports'])) {
            foreach ($_SESSION['generated_password_exports'] as $token => $export) {
                if (!is_array($export) || !isset($export['created_at']) ||
                    intval($export['created_at']) <= ($now - $exportLifetime)) {
                    unset($_SESSION['generated_password_exports'][$token]);
                }
            }
        }

        // This session-bound random token is one-time export authorization and is independent of global CSRF rotation.
        if (is_string($exportToken) && preg_match('/^[a-f0-9]{64}$/', $exportToken) === 1 &&
            isset($_SESSION['generated_password_exports'][$exportToken])) {
            $export = $_SESSION['generated_password_exports'][$exportToken];
            unset($_SESSION['generated_password_exports'][$exportToken]);

            if (isset($export['accounts']) && is_array($export['accounts'])) {
                $accounts = $export['accounts'];
                $filename_prefix = "generated-passwords";
            }
        } else {
            $csrfToken = $_POST['csrf_token'] ?? null;
            $csrfValid = is_string($csrfToken) && dalo_check_csrf_token($csrfToken);

            if ($csrfValid && array_key_exists('accounts', $_POST) &&
                !empty($_POST['accounts']) && is_array($_POST['accounts'])) {
                $accounts = $_POST['accounts'];
                $filename_prefix = (array_key_exists('batch_name', $_POST) &&
                                    is_string($_POST['batch_name']) &&
                                    !empty(trim($_POST['batch_name'])) &&
                                    preg_match("/^[\w\-. ]+$/", trim($_POST['batch_name'])) === 1)
                                 ? trim($_POST['batch_name']) : "users";
            }
        }

        if (is_array($accounts)) {
            $stream = fopen('php://temp', 'w+');

            foreach ($accounts as $account) {
                if (!is_array($account)) {
                    continue;
                }

                $fields = array();
                foreach ($account as $value) {
                    if (!is_scalar($value)) {
                        continue 2;
                    }
                    $fields[] = (string)$value;
                }

                if (count($fields) < 2) {
                    continue;
                }

                fputcsv($stream, $fields, ',', '"', '');
            }

            rewind($stream);
            $content = str_replace("\n", "\r\n", stream_get_contents($stream));
            fclose($stream);

            if (!empty($content)) {
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");
                header("X-Content-Type-Options: nosniff");
                header("Content-type: text/csv; charset=utf-8");
                header(sprintf('Content-Disposition: attachment; filename="%s__%s.csv"',
                               $filename_prefix, date("Ymd")));
                header("Content-Length: " . strlen($content));
                print $content;
                exit;
            }
        }
    }
    
    header("Location: $redirect");
?>
