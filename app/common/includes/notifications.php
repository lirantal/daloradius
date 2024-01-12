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
if (strpos($_SERVER['PHP_SELF'], '/common/includes/notifications.php') !== false) {
    http_response_code(404);
    exit;
}

// Include PHPMailer classes
include __DIR__ . '/../library/phpmailer/Exception.php';
include __DIR__ . '/../library/phpmailer/SMTP.php';
include __DIR__ . '/../library/phpmailer/PHPMailer.php';

// Include the dompdf class
require_once __DIR__ . '/../library/dompdf/dompdf_config.inc.php';


/**
 * createPDF()
 * Creates a PDF document for a given HTML file.
 * 
 * @param string $html The HTML file to convert to PDF.
 * @param string $base_path The base path for resolving relative URLs in the HTML.
 * 
 * @return string Returns the PDF in binary/string stream.
 */
function createPDF($html, $base_path) {
    // This fix has been implemented according to this thread:
    // https://stackoverflow.com/questions/37521775/dompdf-error-no-block-level-parent-found-not-good
    $html = str_replace("\n", "", $html);
    
    // Instantiate the PDF document
    $dompdf = new DOMPDF();
    $dompdf->set_base_path($base_path);
    $dompdf->load_html($html);
    $dompdf->render();
    
    return $dompdf->output();
}


/**
 * Function for sending an email using PHPMailer.
 *
 * @param array  $config_values           Configuration values.
 * @param string $recipient_email_address Recipient's email address.
 * @param string $recipient_name          Recipient's name.
 * @param string $subject                 Email subject.
 * @param string $body                    Email body.
 *
 * @return array [bool, string] An array indicating success or failure and a message.
 */
function send_email($config_values, $recipient_email_address, $recipient_name, $subject, $body) {
    // Create a PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configure SMTP settings
        $mail->isSMTP();
        $mail->Host = $config_values['CONFIG_MAIL_SMTPADDR'];
        $mail->Port = $config_values['CONFIG_MAIL_SMTPPORT'];
        $mail->SMTPSecure = $config_values['CONFIG_MAIL_SMTP_SECURITY'];

        // Check if the username and password are not empty before enabling authentication
        if (!empty($config_values['CONFIG_MAIL_SMTP_USERNAME']) && !empty($config_values['CONFIG_MAIL_SMTP_PASSWORD'])) {
            $mail->SMTPAuth = true;
            $mail->Username = $config_values['CONFIG_MAIL_SMTP_USERNAME'];
            $mail->Password = $config_values['CONFIG_MAIL_SMTP_PASSWORD'];
        }

        // Set sender and recipient
        $mail->setFrom($config_values['CONFIG_MAIL_SMTPFROM'], $config_values['CONFIG_MAIL_SMTP_SENDER_NAME']);
        $mail->addAddress(trim($recipient_email_address), trim($recipient_name));

        // Set email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        $mail->send();

        return [true, "Email sent successfully"];
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }

    return [false, "Cannot send the email"];
}
