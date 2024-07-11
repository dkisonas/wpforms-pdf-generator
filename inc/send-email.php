<?php


function send_email_with_attachment($attachment_path): void
{
    log_message("trying to send email");
    $COMPANY_NAME = "Stiklopaketai24.lt";

    $to = "domkisonas@gmail.com";
    $subject = 'Naujai pateikta užklausa ' . $COMPANY_NAME;
    $body = '';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($attachment_path);

    $response = wp_mail($to, $subject, $body, $headers, $attachments);

    if ($response) {
        log_message("email sent successfully");
    } else {
        log_message("email sending failed");
    }

}