<?php

// Showing times are stored as local wall-clock times, so "now" has to be local too.
date_default_timezone_set('Europe/Copenhagen');

// Errors go to Apache's log, never to the page, so visitors can't see file paths or SQL.
ini_set('display_errors', '0');
error_reporting(E_ALL);
set_exception_handler(function (Throwable $e): void {
    error_log($e);
    http_response_code(500);
    $title = 'Something went wrong';
    $message = 'Please try again in a moment.';
    require __DIR__ . '/../views/error.php';
});

session_start(['cookie_samesite' => 'Lax', 'cookie_httponly' => true]);
require __DIR__ . '/../config/autoload.php';

$routes = [
    'GET /'             => fn() => (new HomeController())->index(),
    'GET /movies'       => fn() => (new MovieController())->index((string) ($_GET['status'] ?? '')),
    'GET /movie'        => fn() => (new MovieController())->show((int) ($_GET['id'] ?? 0)),
    'GET /news'         => fn() => (new NewsController())->index(),
    'GET /news/article' => fn() => (new NewsController())->show((int) ($_GET['id'] ?? 0)),
    'GET /screens'      => fn() => (new ScreenController())->index(),
    'GET /contact'      => fn() => (new ContactController())->showForm(),
    // (string) casts guard against array input like ?email[]=x, which would otherwise crash trim()/typed parameters.
    'POST /contact'     => fn() => (new ContactController())->send(
        trim((string) ($_POST['name'] ?? '')),
        trim((string) ($_POST['email'] ?? '')),
        trim((string) ($_POST['subject'] ?? '')),
        trim((string) ($_POST['message'] ?? ''))
    ),
    'GET /book'         => fn() => (new BookingController())->showSeats(
        (int) ($_GET['showing'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
    ),
    'POST /book'        => fn() => (new BookingController())->book(
        (int) ($_POST['showing_id'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        array_filter((array) ($_POST['seats'] ?? []), 'is_string')
    ),
    'GET /checkout'     => fn() => (new BookingController())->checkout(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null),
    'POST /checkout'    => fn() => (new BookingController())->confirm(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null),
    'GET /checkout/success' => fn() => (new BookingController())->complete(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null),
    'GET /booking/success' => fn() => (new BookingController())->success(
        (int) ($_GET['booking'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
    ),

    'GET /login'        => fn() => (new AccountController())->showLoginForm((string) ($_GET['reason'] ?? '')),
    'POST /login'       => fn() => (new AccountController())->login(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['password'] ?? '')),
    'GET /register'     => fn() => (new AccountController())->showRegisterForm(),
    'POST /register'    => fn() => (new AccountController())->register(
        trim((string) ($_POST['name'] ?? '')),
        trim((string) ($_POST['email'] ?? '')),
        (string) ($_POST['password'] ?? ''),
        (string) ($_POST['password_confirm'] ?? '')
    ),
    'GET /profile'      => fn() => (new AccountController())->profile(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null, $_GET),
    'POST /logout'      => fn() => (new AccountController())->logout(),
    'POST /profile/cancel' => fn() => (new AccountController())->cancelBooking(
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        (int) ($_POST['booking_id'] ?? 0)
    ),

    'GET /admin'           => fn() => (new AdminDashboardController())->index(),
    'GET /admin/bookings'  => fn() => (new AdminBookingController())->index($_GET),
    'GET /admin/showing-seats' => fn() => (new AdminBookingController())->showingSeats((int) ($_GET['showing'] ?? 0), $_GET),
    'GET /admin/users'     => fn() => (new AdminUserController())->index(),
    'POST /admin/bookings/cancel' => fn() => (new AdminBookingController())->cancel((int) ($_POST['booking_id'] ?? 0), (int) ($_POST['showing_id'] ?? 0)),
    'GET /admin/movies'    => fn() => (new AdminMovieController())->index($_GET),
    'POST /admin/movies'   => fn() => (new AdminMovieController())->handlePost($_POST, $_GET),
    'GET /admin/news'      => fn() => (new AdminNewsController())->index($_GET),
    'POST /admin/news'     => fn() => (new AdminNewsController())->handlePost($_POST, $_FILES, $_GET),
    'GET /admin/schedule'  => fn() => (new AdminScheduleController())->index((string) ($_GET['date'] ?? date('Y-m-d')), $_GET),
    'POST /admin/schedule' => fn() => (new AdminScheduleController())->handlePost((string) ($_GET['date'] ?? date('Y-m-d')), $_POST, $_GET),
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
        $title = 'Request blocked';
        $message = 'This form was not submitted from our own site.';
        require __DIR__ . '/../views/error.php';
        exit;
    }
}

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if (($path === '/admin' || str_starts_with($path, '/admin/')) && ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login?reason=admin');
    exit;
}

$handler = $routes["$method $path"] ?? null;

if ($handler === null) {
    http_response_code(404);
    $title = 'Page not found';
    $message = 'Nothing to see here.';
    require __DIR__ . '/../views/error.php';
    exit;
}

$handler();
