<?php

function send_email_to_user($to, $attachment_path): void {
    $company_name = get_company_name();
    $subject = $company_name . ' sąskaita-faktūra';
    $body = $company_name . ' sąskaita-faktūra';
    send_email_with_attachment($to, $subject, $body, $attachment_path);
}

function send_email_to_admin($attachment_path, $address): void {
    $company_name = get_company_name();
    $subject = $company_name . ' Išankstinė sąskaita faktūra ' . $address;
    $body = $company_name . ' Išankstinė sąskaita faktūra ' . $address;
    $to = get_company_email();
    send_email_with_attachment($to, $subject, $body, $attachment_path);
}

function send_email_with_attachment($to, $subject, $body, $attachment_path): void
{
    log_message("trying to send email to: " . $to);

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($attachment_path);

    $response = wp_mail($to, $subject, $body, $headers, $attachments);

    if ($response) {
        log_message("email sent successfully");
    } else {
        log_message("email sending failed");
    }

}