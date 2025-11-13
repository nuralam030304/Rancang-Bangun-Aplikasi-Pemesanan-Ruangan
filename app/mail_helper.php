<?php
// app/mail_helper.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendResetEmail($toEmail, $token, $userId) {
  $config = require __DIR__ . '/config.php';
  $mail = new PHPMailer(true);
  try {
    // Server settings (sesuaikan dengan SMTP Anda)
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'smtp_user@example.com';
    $mail->Password = 'smtp_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('no-reply@example.com', 'Booking Ruangan');
    $mail->addAddress($toEmail);

    $resetLink = sprintf(
      '%s/?p=reset&uid=%s&token=%s',
      rtrim($config['base_url'], '/'),
      urlencode($userId),
      urlencode($token)
    );

    $mail->isHTML(true);
    $mail->Subject = 'Reset Password — Booking Ruangan';
    $mail->Body = "
      <p>Anda meminta reset password. Klik tombol di bawah untuk mengganti password (berlaku 1 jam):</p>
      <p><a href=\"{$resetLink}\" style=\"display:inline-block;padding:10px 14px;background:#0d6efd;color:#fff;border-radius:6px;text-decoration:none;\">Reset Password</a></p>
      <p>Jika tidak meminta, abaikan email ini.</p>
    ";

    $mail->send();
    return true;
  } catch (Exception $e) {
    // log error jika perlu
    return false;
  }
}
