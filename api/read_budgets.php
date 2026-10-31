
<?php
require 'db.php';

$user_id = logged_in();

$stmt = $conn->prepare(
    'SELECT
        DATE_FORMAT(budget_month, "%Y-%m") AS month,
        amount
     FROM monthly_budgets
     WHERE user_id=?
     ORDER BY budget_month DESC'
);

$stmt->bind_param('i', $user_id);
$stmt->execute();

respond([
    'success' => true,
    'budgets' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
]);
