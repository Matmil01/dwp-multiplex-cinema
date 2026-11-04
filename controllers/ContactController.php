<?php

class ContactController
{
    public function showForm(): void
    {
        $name = '';
        $email = '';
        $subject = '';
        $message = '';
        $error = null;
        $snackbarType = null;
        $snackbarMessage = null;

        require __DIR__ . '/../views/contact.php';
    }

    // The four values were trimmed and forced to text by public/index.php.
    public function send(string $name, string $email, string $subject, string $message): void
    {
        $error = null;
        $snackbarType = null;
        $snackbarMessage = null;

        if ($name === '' || strlen($name) > 100) {
            $error = 'Please enter your name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif ($subject === '' || strlen($subject) > 150) {
            $error = 'Please enter a subject.';
        } elseif ($message === '' || strlen($message) > 5000) {
            $error = 'Please enter a message.';
        } else {
            $owner = (require __DIR__ . '/../config/config.php')['mail']['owner'];
            $html = Mailer::render('contact-message', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
            ]);

            if (Mailer::send($owner, "Contact form: $subject", $html, $email, true)) {
                $snackbarType = 'success';
                $snackbarMessage = 'Your message has been sent.';
                $name = $email = $subject = $message = '';
            } else {
                $snackbarType = 'error';
                $snackbarMessage = 'Your message could not be sent. Please try again later.';
            }
        }

        require __DIR__ . '/../views/contact.php';
    }
}
