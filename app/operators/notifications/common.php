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
if (strpos($_SERVER['PHP_SELF'], '/notifications/common.php') !== false) {
    header("Location: ../index.php");
    exit;
}

//include the dompdf class
require_once("../../../common/library/dompdf/dompdf_config.inc.php");

//include the Pear Mail classes for sending out emails
@require_once('Mail.php');
@require_once('Mail/mime.php');

/**
 * createPDF()
 * creates a PDF document for a given html file
 * 
 * @param         string        the html file to convert to pdf
 * 
 * @return        string        returns the pdf in binary/string stream
 */
function createPDF($html, $base_path) {
    // this fix has been set in place according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    $html = str_replace("\n", "", $html);
    
    // instansiate the pdf document
    $dompdf = new DOMPDF();
    $dompdf->set_base_path($base_path);
    $dompdf->load_html($html);
    $dompdf->render();
    return $dompdf->output();
}


/**
 * send_notification_via_email()
 * creates an email message with the pdf and sends it
 * 
 * @param        string        pdf binary/string stream
 * @param        array         smtp server information
 * @param        string        destination email address
 * @param        string        from email address of the sender identity
 * @param        string        email subject
 * @param        string        email body
 * @param        string        pdf attachment name
 * 
 */
function send_notification_via_email($pdfDocument, $smtpInfo, $sendTo, $from, $subject, $body, $attachmentName) {
    if (filter_var($sendTo, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    }
    
    $headers = array(
                       "From"     => $from, 
                       "Subject"  => $subject,
                       "Reply-To" => $from
                    );

    $mime = new Mail_mime();
    $mime->setTXTBody($body); 
    $mime->addAttachment($pdfDocument, "application/pdf", $attachmentName, false, 'base64');
    $body = $mime->get();
    $headers = $mime->headers($headers);
    $mail =& Mail::factory("smtp", $smtpInfo);
    $mail->send($sendTo, $headers, $body);

    return true;
}
?>
