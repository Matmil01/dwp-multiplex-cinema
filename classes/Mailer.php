<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    public static function render(string $template, array $vars): string
    {
        extract($vars);

        ob_start();
        require __DIR__ . "/../views/emails/$template.php";

        return ob_get_clean();
    }
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
                // Made from the HTML: add a line break after each paragraph/row/heading close tag,
                // then strip_tags() removes all HTML tags, leaving readable plain text.
                $phpMailer->AltBody = trim(strip_tags(preg_replace('/<\/(p|tr|h\d)>/i', "$0\n", $body)));
            }

            $phpMailer->send();

            return true;
        } catch (Exception $e) {
            // Written to the server log for debugging.
            error_log('Mailer: ' . $phpMailer->ErrorInfo);

            return false;
        }
    }
}
