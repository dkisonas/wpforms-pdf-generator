<?php


function send_email_with_attachment($attachment_path): void
{
    $COMPANY_NAME = "Stiklopaketai24.lt";

    $to = get_option('admin_email');
    $subject = 'Naujai pateikta užklausa ' . $COMPANY_NAME;
    $body = '';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($attachment_path);

    wp_mail($to, $subject, $body, $headers, $attachments);
}