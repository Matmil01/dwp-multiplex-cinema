<?php

class AccountController
{
    private const PASSWORD_MIN_LENGTH = 8;

    public function showRegisterForm(): void
    {
        $name = '';
        $email = '';
        $error = null;

        require __DIR__ . '/../views/account/register.php';
    }

    public function register(string $name, string $email, string $password, string $passwordConfirm): void
    {
        $error = null;

        if ($name === '' || strlen($name) > 100) {
            $error = 'Please enter your name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . self::PASSWORD_MIN_LENGTH . ' characters.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Passwords do not match.';
        } elseif (User::emailExists($email)) {
            $error = 'An account with that email already exists.';
        } else {
            User::create($name, $email, $password);

            // The account exists either way, so a failed welcome email is ignored.
            $siteUrl = (require __DIR__ . '/../config/config.php')['site_url'];
            $html = Mailer::render('welcome', ['name' => $name, 'email' => $email, 'siteUrl' => $siteUrl]);
            Mailer::send($email, 'Welcome to Untitled Cinema', $html, null, true);

            header('Location: /login?reason=registered');
            exit;
        }

        require __DIR__ . '/../views/account/register.php';
    }

    // Pages that need a login redirect here with ?reason=, so the form can say why you were sent to it.
    private const LOGIN_REASONS = [
        'booking' => 'Log in to book seats.',
        'profile' => 'Log in to see your profile.',
        'admin' => 'Log in with an admin account to open the admin area.',
        'registered' => 'Account created. Log in to continue.',
    ];

    public function showLoginForm(string $reason): void
    {
        $email = '';
        $error = null;
        $notice = self::LOGIN_REASONS[$reason] ?? null;

        require __DIR__ . '/../views/account/login.php';
    }

    public function login(string $email, string $password): void
    {
        $user = User::findByEmail($email);

        // One generic message for both cases, so the form doesn't reveal which emails are registered.
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['role'] = $user['role'];

            header('Location: ' . ($user['role'] === 'admin' ? '/admin' : '/profile'));
            exit;
        }

        $error = 'Incorrect email or password.';
        $notice = null;

        require __DIR__ . '/../views/account/login.php';
    }

    public function profile(?int $userId, array $query): void
    {
        $user = $userId !== null ? User::find($userId) : null;

        if (!$user) {
            header('Location: /login?reason=profile');
            exit;
        }

        $bookings = Booking::forUser($user['id']);

        if (isset($query['cancelled'])) {
            $snackbarType = 'success';
            $snackbarMessage = 'Booking cancelled.';
        } elseif (isset($query['cancel_failed'])) {
            $snackbarType = 'error';
            $snackbarMessage = 'That booking could not be cancelled. It may have already started.';
        }

        require __DIR__ . '/../views/account/profile.php';
    }

    public function cancelBooking(?int $userId, int $bookingId): void
    {
        if ($userId === null) {
            header('Location: /login?reason=profile');
            exit;
        }

        $cancelled = Booking::cancel($bookingId, $userId);

        header('Location: /profile?' . ($cancelled ? 'cancelled=1' : 'cancel_failed=1'));
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header('Location: /');
        exit;
    }
}
