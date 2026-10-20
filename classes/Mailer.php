<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Sends plain-text email through the cinema's SMTP mailbox.
class Mailer
{
    // Returns false instead of throwing so a mail problem never breaks the page that asked for it.
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null): bool
    {
        $mail = (require __DIR__ . '/../config/config.php')['mail'];

        $phpMailer = new PHPMailer(true);

        try {
            $phpMailer->isSMTP();
            $phpMailer->Host = $mail['host'];
            $phpMailer->Port = $mail['port'];
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $mail['user'];
            $phpMailer->Password = $mail['password'];
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $phpMailer->CharSet = 'UTF-8';

            $phpMailer->setFrom($mail['from'], 'Untitled Cinema');
            $phpMailer->addAddress($to);
            if ($replyTo !== null) {
                $phpMailer->addReplyTo($replyTo);
            }

            $phpMailer->Subject = $subject;
            $phpMailer->Body = $body;

            $phpMailer->send();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
