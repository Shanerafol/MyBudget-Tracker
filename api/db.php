
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }

    if (
        !isset($_SESSION['csrf']) ||
        !hash_equals(
            $_SESSION['csrf'],
            $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''
        )
    ) {
        respond([
            'success' => false,
            'message' => 'Invalid session token. Refresh the page.'
        ], 403);
    }
}

function logged_in() {
    if (empty($_SESSION['user_id'])) {
        respond([
            'success' => false,
            'message' => 'Please log in'
        ], 401);
    }

    return (int) $_SESSION['user_id'];
}

try {
    $conn = new mysqli(
        getenv('BUDGET_DB_HOST') ?: 'localhost',
        getenv('BUDGET_DB_USER') ?: 'root',
        getenv('BUDGET_DB_PASS') === false
            ? ''
            : getenv('BUDGET_DB_PASS'),
        getenv('BUDGET_DB_NAME') ?: 'shane_budgettracker_db'
    );

    $conn->set_charset('utf8mb4');

} catch (Throwable $e) {
    error_log($e->getMessage());

    respond([
        'success' => false,
        'message' => 'Database unavailable. Check XAMPP MySQL.'
    ], 503);
}
