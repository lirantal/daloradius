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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/pdf.php') !== false) {
    http_response_code(404);
    exit;
}

include_once 'config_read.php';

// Include the dompdf class
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'dompdf', 'vendor', 'autoload.php' ]);


/**
 * Render an HTML string to a PDF document (binary string).
 *
 * @param string      $html_content HTML markup to render.
 * @param string|null $base_path    Directory used to resolve relative asset
 *                                  references (e.g. <img src="logo.jpg">) in the
 *                                  markup. It is also added to dompdf's chroot so
 *                                  those local files are allowed to load.
 * @param string      $orientation  'portrait' (default) or 'landscape'.
 *
 * @return string
 */
function create_pdf($html_content, $base_path = null, $orientation = 'portrait') {
    // instantiate and use the dompdf class
    $dompdf = new Dompdf\Dompdf();
    $options = $dompdf->getOptions();

    // keep the runtime font cache out of the bundled dompdf directory
    $font_cache = implode(DIRECTORY_SEPARATOR, [ sys_get_temp_dir(), 'daloradius-dompdf-fonts' ]);
    if (@is_dir($font_cache) || @mkdir($font_cache, 0770, true)) {
        $options->setFontCache($font_cache);
    }

    if (is_string($base_path) && ($base_path = realpath($base_path)) !== false && is_dir($base_path)) {
        $options->setChroot(array_merge($options->getChroot(), array($base_path)));
        $dompdf->setBasePath($base_path . DIRECTORY_SEPARATOR);
    }

    $dompdf->setOptions($options);

    $dompdf->loadHtml($html_content);

    // Setup the paper size and orientation
    $dompdf->setPaper('A4', ($orientation === 'landscape') ? 'landscape' : 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    return $dompdf->output();
}
