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

session_start([
    'cookie_samesite' => 'Lax',
    'cookie_httponly' => true,
    // Only over HTTPS, so local http://localhost can still log in.
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'use_strict_mode' => true,
]);
require __DIR__ . '/../config/autoload.php';

// One secret per visitor session. Every POST form carries it back (see csrfField()); another
// site can't read it, so it can't forge a valid form submission.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Hidden input that templates place inside every <form method="post">.
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) . '">';
}

// The logged-in user's id, or null if nobody is logged in. Reads as: "is there a user_id in the
// session? if yes use it as a number, if no use null". The routes below pass it to the pages that need it.
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

// The submitted form with every field as plain text: anything that isn't text (e.g. title[]=x)
// becomes '', so trim() and similar can't crash in the admin controllers that receive it.
$textPost = array_map(fn($value) => is_string($value) ? $value : '', $_POST);

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
    'GET /book'         => fn() => (new BookingController())->showSeats((int) ($_GET['showing'] ?? 0), $userId),
    'POST /book'        => fn() => (new BookingController())->book(
        (int) ($_POST['showing_id'] ?? 0),
        $userId,
        // The seat picker sends a list of seat labels. (array) makes sure it IS a list, and
        // array_filter(..., 'is_string') throws out anything in it that isn't plain text.
        array_filter((array) ($_POST['seats'] ?? []), 'is_string')
    ),
    'GET /checkout'     => fn() => (new BookingController())->checkout($userId),
    'POST /checkout'    => fn() => (new BookingController())->confirm($userId),
    'GET /checkout/success' => fn() => (new BookingController())->complete($userId),
    'GET /booking/success' => fn() => (new BookingController())->success((int) ($_GET['booking'] ?? 0), $userId),

    'GET /login'        => fn() => (new AccountController())->showLoginForm((string) ($_GET['reason'] ?? '')),
    'POST /login'       => fn() => (new AccountController())->login(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['password'] ?? '')),
    'GET /register'     => fn() => (new AccountController())->showRegisterForm(),
    'POST /register'    => fn() => (new AccountController())->register(
        trim((string) ($_POST['name'] ?? '')),
        trim((string) ($_POST['email'] ?? '')),
        (string) ($_POST['password'] ?? ''),
        (string) ($_POST['password_confirm'] ?? '')
    ),
    'GET /profile'      => fn() => (new AccountController())->profile($userId, $_GET),
    'POST /logout'      => fn() => (new AccountController())->logout(),
    'POST /profile/cancel' => fn() => (new AccountController())->cancelBooking($userId, (int) ($_POST['booking_id'] ?? 0)),

    // Admin pages. The whole $_GET / $_POST array is passed where a page has many optional
    // parameters (filters, edit ids, snackbar messages); the controller picks out what it needs.
    // $_FILES holds uploaded files (the news editor's image upload).
    'GET /admin'           => fn() => (new AdminDashboardController())->index(),
    'GET /admin/bookings'  => fn() => (new AdminBookingController())->index($_GET),
    'GET /admin/showing-seats' => fn() => (new AdminBookingController())->showingSeats((int) ($_GET['showing'] ?? 0), $_GET),
    'GET /admin/users'     => fn() => (new AdminUserController())->index(),
    'POST /admin/bookings/cancel' => fn() => (new AdminBookingController())->cancel((int) ($_POST['booking_id'] ?? 0), (int) ($_POST['showing_id'] ?? 0)),
    'GET /admin/movies'    => fn() => (new AdminMovieController())->index($_GET),
    'POST /admin/movies'   => fn() => (new AdminMovieController())->handlePost($textPost, $_GET),
    'GET /admin/news'      => fn() => (new AdminNewsController())->index($_GET),
    'POST /admin/news'     => fn() => (new AdminNewsController())->handlePost($textPost, $_FILES, $_GET),
    'GET /admin/schedule'  => fn() => (new AdminScheduleController())->index((string) ($_GET['date'] ?? date('Y-m-d')), $_GET),
    'POST /admin/schedule' => fn() => (new AdminScheduleController())->handlePost((string) ($_GET['date'] ?? date('Y-m-d')), $textPost, $_GET),
    'GET /admin/settings'  => fn() => (new AdminSettingsController())->showForm(),
    'POST /admin/settings' => fn() => (new AdminSettingsController())->save($textPost),
    // The prices form sends a list (prices[screen id]), so it gets the raw $_POST.
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

    // Second layer: the submission must also carry this session's secret token. (string) guards against array input.
    $tokenValid = hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''));

    // No origin at all, an origin that isn't us, or a missing/wrong token: refuse with 403 ("forbidden").
    if ($sourceHost === '' || $sourceHost !== strtolower($_SERVER['HTTP_HOST'] ?? '') || !$tokenValid) {
        http_response_code(403);
        $title = 'Request blocked';
        $message = 'This form was not submitted from our own site, or the page was open for too long. Go back to the form, reload that page and try again.';
        require __DIR__ . '/../templates/error.php';
        exit;
    }
}

// Pull just the path out of the full address ("/movie?id=5" -> "/movie"), remove a
// trailing slash, and treat an empty result as the front page "/".
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// The role is re-read from the database on every request, so changing someone's role there
// takes effect at once instead of at their next login. `?? ''` covers an account that no longer exists.
if ($userId !== null) {
    $_SESSION['role'] = User::find($userId)['role'] ?? '';
}

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
