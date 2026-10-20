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
            header('Location: /login');
            exit;
        }

        require __DIR__ . '/../views/account/register.php';
    }

    public function showLoginForm(): void
    {
        $email = '';
        $error = null;

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

        require __DIR__ . '/../views/account/login.php';
    }

    public function profile(?int $userId): void
    {
        $user = $userId !== null ? User::find($userId) : null;

        if (!$user) {
            header('Location: /login');
            exit;
        }

        $bookings = Booking::forUser($user['id']);

        require __DIR__ . '/../views/account/profile.php';
    }

    public function cancelBooking(?int $userId, int $bookingId): void
    {
        if ($userId === null) {
            header('Location: /login');
            exit;
        }

        Booking::cancel($bookingId, $userId);

        header('Location: /profile');
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
