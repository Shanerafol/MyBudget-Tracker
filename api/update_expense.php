
<?php
require 'db.php';

csrf_check();
$user_id = logged_in();

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    respond([
        'success' => false,
        'message' => 'Invalid expense ID'
    ], 422);
}

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$amount = $_POST['amount'] ?? '';
$date = $_POST['expense_date'] ?? '';
$notes = trim($_POST['notes'] ?? '');

$allowed = [
    'Foods', 'Drinks', 'School', 'Personal',
    'Needs', 'Wants', 'Groceries',
    'Electronics', 'Bills', 'Other'
];

$d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

if (
    $title === '' ||
    mb_strlen($title) > 100 ||
    !in_array($category, $allowed, true) ||
    !is_numeric($amount) ||
    !is_finite((float)$amount) ||
    (float)$amount <= 0 ||
    (float)$amount > 99999999 ||
    !$d ||
    $d->format('Y-m-d') !== $date
) {
    respond([
        'success' => false,
        'message' => 'Invalid expense information'
    ], 422);
}

$amount = (float)$amount;

// Require a budget for the expense month.
$month = substr($date, 0, 7) . '-01';

$budgetCheck = $conn->prepare(
    'SELECT 1 FROM monthly_budgets
     WHERE user_id=? AND budget_month=?
     LIMIT 1'
);

$budgetCheck->bind_param('is', $user_id, $month);
$budgetCheck->execute();

if (!$budgetCheck->get_result()->fetch_row()) {
    respond([
        'success' => false,
        'message' => 'Set a budget for this expense month before saving expenses.'
    ], 422);
}

// Update only the logged-in user's expense.
$stmt = $conn->prepare(
    'UPDATE expenses
     SET title=?, category=?, amount=?,
         expense_date=?, notes=?
     WHERE id=? AND user_id=?'
);

$stmt->bind_param(
    'ssdssii',
    $title,
    $category,
    $amount,
    $date,
    $notes,
    $id,
    $user_id
);

$stmt->execute();

respond(['success' => true]);
