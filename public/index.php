<?php

// Showing times are stored as local wall-clock times, so "now" has to be local too.
date_default_timezone_set('Europe/Copenhagen');

session_start(['cookie_samesite' => 'Lax', 'cookie_httponly' => true]);
require __DIR__ . '/../config/autoload.php';

$routes = [
    'GET /'             => fn() => (new HomeController())->index(),
    'GET /movies'       => fn() => (new MovieController())->index((string) ($_GET['status'] ?? '')),
    'GET /movie'        => fn() => (new MovieController())->show((int) ($_GET['id'] ?? 0)),
    'GET /news'         => fn() => (new NewsController())->index(),
    'GET /news/article' => fn() => (new NewsController())->show((int) ($_GET['id'] ?? 0)),
    'GET /screens'      => fn() => (new ScreenController())->index(),
    'GET /book'         => fn() => (new BookingController())->showSeats(
        (int) ($_GET['showing'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
    ),
    'POST /book'        => fn() => (new BookingController())->book(
        (int) ($_POST['showing_id'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        array_filter((array) ($_POST['seats'] ?? []), 'is_string')
    ),

    'GET /login'        => fn() => (new AccountController())->showLoginForm(),
    'POST /login'       => fn() => (new AccountController())->login(trim($_POST['email'] ?? ''), $_POST['password'] ?? ''),
    'GET /register'     => fn() => (new AccountController())->showRegisterForm(),
    'POST /register'    => fn() => (new AccountController())->register(
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        $_POST['password_confirm'] ?? ''
    ),
    'GET /profile'      => fn() => (new AccountController())->profile(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null),
    'GET /logout'       => fn() => (new AccountController())->logout(),
    'POST /profile/cancel' => fn() => (new AccountController())->cancelBooking(
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        (int) ($_POST['booking_id'] ?? 0)
    ),

    'GET /admin'           => fn() => (new AdminDashboardController())->index(),
    'GET /admin/bookings'  => fn() => (new AdminBookingController())->index(),
    'GET /admin/bookings/invoice' => fn() => (new AdminBookingController())->invoice((int) ($_GET['id'] ?? 0)),
    'POST /admin/bookings/cancel' => fn() => (new AdminBookingController())->cancel((int) ($_POST['booking_id'] ?? 0)),
    'GET /admin/movies'    => fn() => (new AdminMovieController())->index($_GET),
    'POST /admin/movies'   => fn() => (new AdminMovieController())->handlePost($_POST, $_GET),
    'GET /admin/news'      => fn() => (new AdminNewsController())->index($_GET),
    'POST /admin/news'     => fn() => (new AdminNewsController())->handlePost($_POST, $_FILES, $_GET),
    'GET /admin/schedule'  => fn() => (new AdminScheduleController())->index($_GET['date'] ?? date('Y-m-d'), $_GET),
    'POST /admin/schedule' => fn() => (new AdminScheduleController())->handlePost($_GET['date'] ?? date('Y-m-d'), $_POST, $_GET),
    'GET /admin/settings'  => fn() => (new AdminSettingsController())->showForm(),
    'POST /admin/settings' => fn() => (new AdminSettingsController())->save($_POST),
];

$method = $_SERVER['REQUEST_METHOD'];

// Blocks cross-site form posts: browsers send Origin (or Referer) on every POST, and it must name this site's own host.
if ($method === 'POST') {
    $source = parse_url($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '') ?: [];
    $sourceHost = strtolower(($source['host'] ?? '') . (isset($source['port']) ? ':' . $source['port'] : ''));

    if ($sourceHost === '' || $sourceHost !== strtolower($_SERVER['HTTP_HOST'] ?? '')) {
        http_response_code(403);
        echo 'Request blocked.';
        exit;
    }
}

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if (($path === '/admin' || str_starts_with($path, '/admin/')) && ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

$handler = $routes["$method $path"] ?? null;

if ($handler === null) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

$handler();
