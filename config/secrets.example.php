<?php
// ============================================================
//  Arandia College eLMS — Private settings TEMPLATE
//  File: config/secrets.example.php
//
//  1. Copy this file to config/secrets.php (same folder).
//  2. Fill in your own values. Never commit or share secrets.php.
//
//  mail_*        Gmail account used for password-reset and admin OTP e-mails.
//                Use a Gmail "App Password" (Google Account → Security →
//                2-Step Verification → App passwords), not your normal password.
//  groq_api_key  Free key from https://console.groq.com/keys for EduBot and the
//                AI helpers. Leave '' to turn the AI features off.
//
//  mail_host / mail_port / mail_secure are optional. Leave them out for Gmail
//  (smtp.gmail.com, port 465, ssl). If port 465 is blocked, port 587 is tried.
//
//  Environment variables with the same names in UPPER CASE (MAIL_USERNAME,
//  MAIL_PASSWORD, GROQ_API_KEY ...) override these values.
// ============================================================
return [
    'mail_username'  => '',
    'mail_password'  => '',
    'mail_from'      => '',
    'mail_from_name' => 'Arandia College eLMS',
    'groq_api_key'   => '',
    // Optional. Public address used in e-mailed links (password reset). Leave '' on localhost.
    // Set it once the site is online, e.g. 'https://elms.school.edu/capstone1'.
    'app_url'        => '',
];
