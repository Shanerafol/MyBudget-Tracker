<?php
include "db.php";

$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY expense_date DESC, id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$expenses = [];
while ($row = $result->fetch_assoc()) {
  $expenses[] = $row;
}

echo json_encode($expenses);
?>
