<?php
// app/mail_helper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendResetEmail($toEmail, $token, $userId) {
    $config = require __DIR__ . '/config.php';
    
    $resetLink = sprintf(
        '%s/?p=reset&uid=%s&token=%s',
        rtrim($config['base_url'], '/'),
        urlencode($userId),
        urlencode($token)
    );

    $subject = 'Reset Password — Booking Ruangan';
    
    // Template Email HTML
    $message = "
        <html>
        <head>
            <title>Reset Password</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .footer { margin-top: 20px; font-size: 0.8em; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Permintaan Reset Password</h2>
                <p>Halo,</p>
                <p>Kami menerima permintaan untuk mereset password akun Anda. Silakan klik tombol di bawah ini untuk melanjutkan:</p>
                <p><a href=\"$resetLink\" class=\"btn\">Reset Password</a></p>
                <p>Tautan ini hanya berlaku selama 1 jam.</p>
                <p>Jika Anda tidak meminta reset password, silakan abaikan email ini.</p>
                <div class='footer'>
                    <p>Terima kasih,<br>Tim Booking Ruangan</p>
                </div>
            </div>
        </body>
        </html>
    ";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['mail']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['mail']['username'];
        $mail->Password   = $config['mail']['password'];
        $mail->SMTPSecure = $config['mail']['encryption'];
        $mail->Port       = $config['mail']['port'];

        // Recipients
        $mail->setFrom($config['mail']['from_address'], $config['mail']['from_name']);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = "Salin tautan berikut untuk reset password: $resetLink";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}