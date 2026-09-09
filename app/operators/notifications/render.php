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
 * Description:    HTML template helpers shared by the operator PDF notifications.
 *
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/notifications/render.php') !== false) {
    http_response_code(404);
    exit;
}

/**
 * Load a notification HTML template.
 *
 * @param string $path Absolute path to the template file.
 *
 * @return string|false The template contents, or false when it is missing/unreadable.
 */
function notification_load_template($path) {
    if (!is_string($path) || $path === "" || !is_file($path) || !is_readable($path)) {
        return false;
    }

    return file_get_contents($path);
}

/**
 * Replace a map of literal tokens inside an HTML string.
 *
 * Keys are matched verbatim, so callers pass the exact token as it appears in the
 * template (e.g. "####__CUSTOMER_NAME__####" or "[InvoiceNumber]").
 *
 * @param string $html
 * @param array  $replacements token => value
 *
 * @return string
 */
function notification_fill($html, array $replacements) {
    if (empty($replacements)) {
        return $html;
    }

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}

/**
 * Escape a value for safe inclusion in notification HTML.
 *
 * @param mixed $value
 *
 * @return string
 */
function notification_escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * The shared stylesheet for every notification document.
 *
 * Templates carry only structure and drop this in through the
 * "####__STYLE__####" token, so the whole look lives in one place.
 *
 * @return string a full <style> element
 */
function notification_stylesheet() {
    return <<<'CSS'
<style>
    @page { margin: 22mm 18mm; }
    body { font-family: "DejaVu Sans", "Helvetica", sans-serif; font-size: 11px; line-height: 1.5; color: #2b2b2b; margin: 0; }

    table.doc-header { width: 100%; border-collapse: collapse; border-bottom: 3px solid #6aa121; }
    table.doc-header td { vertical-align: bottom; padding-bottom: 9px; }
    table.doc-header td.title { font-size: 22px; font-weight: bold; color: #4a4a4a; letter-spacing: .5px; }
    table.doc-header td.logo { text-align: right; }
    table.doc-header img { max-height: 50px; }

    .meta { color: #8a8a8a; font-size: 9.5px; margin: 6px 0 18px; }

    h1.section { font-size: 12px; color: #6aa121; text-transform: uppercase; letter-spacing: .6px;
                 margin: 22px 0 8px; padding-bottom: 3px; border-bottom: 1px solid #e2e2e2; }

    p { margin: 0 0 9px; }
    p.lede { font-size: 12px; }

    .panel { background: #f6f8f2; border: 1px solid #e6ebdd; border-radius: 4px; padding: 9px 12px; }

    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 3px 0; vertical-align: top; }
    table.kv td.k { color: #8a8a8a; width: 120px; }

    table.grid { width: 100%; border-collapse: collapse; margin: 6px 0 14px; }
    table.grid th, table.grid td { padding: 6px 9px; text-align: left; font-size: 10px; border-bottom: 1px solid #ececec; }
    table.grid thead th { background: #6aa121; color: #ffffff; border-bottom: 0; }
    table.grid tbody tr:nth-child(even) td { background: #f7f7f4; }
    table.grid td.num, table.grid th.num { text-align: right; white-space: nowrap; }

    .totals { width: 46%; margin-left: 54%; border-collapse: collapse; }
    .totals td { padding: 3px 9px; text-align: right; }
    .totals td.k { color: #8a8a8a; }
    .totals tr.grand td { border-top: 2px solid #6aa121; font-size: 13px; font-weight: bold; color: #2b2b2b; }

    .footer { margin-top: 26px; padding-top: 6px; border-top: 1px solid #ececec;
              font-size: 8.5px; color: #9a9a9a; text-align: center; }
</style>
CSS;
}
