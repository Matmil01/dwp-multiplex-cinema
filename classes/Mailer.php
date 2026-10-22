<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Sends email through the cinema's SMTP mailbox: plain text by default, or HTML with a text fallback.
class Mailer
{
    // Runs a template from views/emails/ and returns its HTML as a string instead of printing it.
    public static function render(string $template, array $vars): string
    {
        // Turns ['booking' => ...] into a local $booking, so the template reads like the page views do.
        extract($vars);

        ob_start();
        require __DIR__ . "/../views/emails/$template.php";

        return ob_get_clean();
    }

    // Returns false instead of throwing so a mail problem never breaks the page that asked for it.
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null, bool $html = false): bool
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

            if ($html) {
                $phpMailer->isHTML(true);
                // Shown by mail clients that don't render HTML.
                $phpMailer->AltBody = trim(strip_tags(preg_replace('/<\/(p|tr|h\d)>/i', "$0\n", $body)));
            }

            $phpMailer->send();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
