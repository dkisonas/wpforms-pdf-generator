<?php


function send_email_with_attachment($to, $attachment_path): void
{
    log_message("trying to send email to: " . $to);
    $company_name = get_company_name();


    $subject = 'Naujai pateikta užklausa ' . $company_name;
    $body = 'Naujai pateikta užklausa ' . $company_name;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($attachment_path);

    $response = wp_mail($to, $subject, $body, $headers, $attachments);

    if ($response) {
        log_message("email sent successfully");
    } else {
        log_message("email sending failed");
    }

}