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
if (strpos($_SERVER['PHP_SELF'], '/common/includes/mail.php') !== false) {
    http_response_code(404);
    exit;
}

include_once 'config_read.php';

// Include PHPMailer classes
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'phpmailer', 'Exception.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'phpmailer', 'SMTP.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'phpmailer', 'PHPMailer.php' ]);


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
function send_email($config_values, $recipient_email_address, $recipient_name, $subject, $body, $attachment=array()) {
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

        if (is_array($attachment) && array_key_exists('content', $attachment) && array_key_exists('filename', $attachment) ) {
            $mail->addStringAttachment($attachment['content'], $attachment['filename'],
                                       PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64, 'application/pdf', 'attachment');
        }

        // Send the email
        $mail->send();

        return [true, "Email sent successfully"];
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }

    return [false, "Cannot send the email"];
}
