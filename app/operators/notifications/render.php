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
