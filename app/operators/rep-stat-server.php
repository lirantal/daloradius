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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    $log = "visited page: ";

    // print HTML prologue
    $title = t('Intro', 'repstatserver.php');
    $help = t('helpPage', 'repstatserver');

    print_html_prologue($title, $langCode);

    $collectedAt = htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');
    $collectedLabel = htmlspecialchars(t('serverStatus', 'collectedAt'), ENT_QUOTES, 'UTF-8');
    $refreshLabel = htmlspecialchars(t('serverStatus', 'refresh'), ENT_QUOTES, 'UTF-8');

    echo '<div class="server-status-header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">';
    echo '<div>';
    print_title_and_help($title, $help);
    echo '</div>';
    echo <<<HTML
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted small">
            <i class="bi bi-clock me-1" aria-hidden="true"></i>{$collectedLabel}: {$collectedAt}
        </span>
        <a href="rep-stat-server.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-clockwise me-1" aria-hidden="true"></i>{$refreshLabel}
        </a>
    </div>
</div>

HTML;

    include('library/extensions/server_info.php');

    include('include/config/logging.php');

    $inline_extra_js = <<<'JS'
(function () {
    var toggle = document.getElementById('toggleVirtualInterfaces');
    if (!toggle) {
        return;
    }
    toggle.addEventListener('change', function () {
        document.querySelectorAll('tr[data-virtual-iface="1"]').forEach(function (row) {
            row.classList.toggle('d-none', !toggle.checked);
        });
    });
}());
JS;

    print_footer_and_html_epilogue($inline_extra_js);
