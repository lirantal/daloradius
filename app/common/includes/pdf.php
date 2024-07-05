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

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/pdf.php') !== false) {
    http_response_code(404);
    exit;
}

include_once 'config_read.php';

// Include the dompdf class
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'dompdf', 'vendor', 'autoload.php' ]);


function create_pdf($html_content) {
    // instantiate and use the dompdf class
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html_content);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    return $dompdf->output();
}
