
<?php
require 'db.php';

csrf_check();
$user_id = logged_in();

$month = $_POST['month'] ?? '';
$amount = $_POST['amount'] ?? '';

if (
    !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $month) ||
    !is_numeric($amount) ||
    !is_finite((float)$amount) ||
    (float)$amount < 0.01 ||
    (float)$amount > 99999999.99
) {
    respond([
        'success' => false,
        'message' => 'Please enter a valid month and positive budget amount.'
    ], 422);
}

$date = $month . '-01';
$amount = round((float)$amount, 2);

$stmt = $conn->prepare(
    'INSERT INTO monthly_budgets
     (user_id, budget_month, amount)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE amount=VALUES(amount)'
);

$stmt->bind_param('isd', $user_id, $date, $amount);
$stmt->execute();

respond(['success' => true]);
