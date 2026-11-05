<?php

// Front door. Every request goes through this file first.

date_default_timezone_set('Europe/Copenhagen');

// Errors go to Apache's log, never to the page, so visitors can't see file paths or SQL.
ini_set('display_errors', '0');
error_reporting(E_ALL);
set_exception_handler(function (Throwable $e): void {
    error_log($e);
    http_response_code(500);
    $title = 'Something went wrong';
    $message = 'Please try again in a moment.';
    require __DIR__ . '/../templates/error.php';
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
    // trim() strips spaces from both ends of the text, so " din@mor.dk " becomes "din@mor.dk".
    'POST /contact'     => fn() => (new ContactController())->send(
        trim((string) ($_POST['name'] ?? '')),
        trim((string) ($_POST['email'] ?? '')),
        trim((string) ($_POST['subject'] ?? '')),
        trim((string) ($_POST['message'] ?? ''))
    ),
    // Booking pages receive the logged-in user's id, or null if nobody is logged in.
    // `isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null` reads as:
    // "is there a user_id in the session? if yes use it as a number, if no use null".
    'GET /book'         => fn() => (new BookingController())->showSeats(
        (int) ($_GET['showing'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
    ),
    'POST /book'        => fn() => (new BookingController())->book(
        (int) ($_POST['showing_id'] ?? 0),
        isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        // The seat picker sends a list of seat labels. (array) makes sure it IS a list, and
        // array_filter(..., 'is_string') throws out anything in it that isn't plain text.
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

    // Admin pages. The whole $_GET / $_POST array is passed where a page has many optional
    // parameters (filters, edit ids, snackbar messages); the controller picks out what it needs.
    // $_FILES holds uploaded files (the news editor's image upload).
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
    'POST /admin/settings/prices' => fn() => (new AdminSettingsController())->savePrices($_POST),
];

// $_SERVER holds facts about the current request that the web server fills in.
// REQUEST_METHOD is "GET" or "POST".
$method = $_SERVER['REQUEST_METHOD'];

// Blocks cross-site form posts: browsers send Origin (or Referer) on every POST, and it must name this site's own host.
// Why: without this, a malicious page elsewhere could contain a hidden form that submits to
// e.g. our /profile/cancel while you're logged in, and your browser would send your session
// cookie along. (This attack is called CSRF.) Checking that the submission came from our own
// address shuts that down. parse_url() splits an address into parts; we keep only host + port.
if ($method === 'POST') {
    $source = parse_url($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '') ?: [];
    $sourceHost = strtolower(($source['host'] ?? '') . (isset($source['port']) ? ':' . $source['port'] : ''));

    // No origin at all, or an origin that isn't us: refuse with 403 ("forbidden").
    if ($sourceHost === '' || $sourceHost !== strtolower($_SERVER['HTTP_HOST'] ?? '')) {
        http_response_code(403);
        $title = 'Request blocked';
        $message = 'This form was not submitted from our own site.';
        require __DIR__ . '/../templates/error.php';
        exit;
    }
}

// Pull just the path out of the full address ("/movie?id=5" -> "/movie"), remove a
// trailing slash, and treat an empty result as the front page "/".
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// Gatekeeper for the admin area: any address starting with /admin needs role = admin in the
// session. Anyone else is sent to the login page. One check here covers every admin page,
// so no individual admin controller can forget it.
if (($path === '/admin' || str_starts_with($path, '/admin/')) && ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login?reason=admin');
    exit;
}

// Look up e.g. "GET /movies" in the route table. null if there is no such entry.
$handler = $routes["$method $path"] ?? null;

if ($handler === null) {
    http_response_code(404);
    $title = 'Page not found';
    $message = 'Nothing to see here.';
    require __DIR__ . '/../templates/error.php';
    exit;
}

// Finally run the matching route's function. This is the moment the controller is created,
// the model is asked for data, and the view prints the page.
$handler();
