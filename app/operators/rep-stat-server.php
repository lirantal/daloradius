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

    $inline_extra_css = <<<'CSS'
.server-status-header h3 {
    margin-bottom: 0;
}

.server-kpi-icon {
    width: 2.75rem;
    height: 2.75rem;
    flex-shrink: 0;
    line-height: 1;
}

.server-kpi-icon .bi {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.server-kpi-icon .bi::before {
    vertical-align: 0;
}

.server-kv-table th {
    width: 38%;
    color: var(--bs-secondary-color, #6c757d);
    font-weight: 500;
    white-space: nowrap;
}

.server-kv-table td {
    white-space: normal;
    overflow-wrap: anywhere;
}

.server-legend-dot {
    display: inline-block;
    width: 0.625rem;
    height: 0.625rem;
    border-radius: 50%;
    flex-shrink: 0;
}

.server-usage-cell {
    min-width: 8.5rem;
}

.server-net-table td.server-net-wrap {
    white-space: normal;
    overflow-wrap: anywhere;
}
CSS;

    print_html_prologue($title, $langCode, array(), array(), $inline_extra_css);

    $refreshLabel = htmlspecialchars(t('serverStatus', 'refresh'), ENT_QUOTES, 'UTF-8');

    echo '<div class="server-status-header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">';
    echo '<div>';
    print_title_and_help($title, $help);
    echo '</div>';
    echo <<<HTML
    <div>
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
