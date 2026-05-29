<?php
include "db.php";

$user_id = $_SESSION['user_id'] ?? null;
$title = $_POST["title"] ?? "";
$category = $_POST["category"] ?? "";
$amount = $_POST["amount"] ?? "";
$expense_date = $_POST["expense_date"] ?? "";
$notes = $_POST["notes"] ?? "";

if ($title === "" || $category === "" || $amount === "" || $expense_date === "") {
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO expenses (user_id, title, category, amount, expense_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issdss", $user_id, $title, $category, $amount, $expense_date, $notes);

echo json_encode(["success" => $stmt->execute()]);
?>
