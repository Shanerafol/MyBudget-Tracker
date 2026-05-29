<?php
include "db.php";

$user_id = $_SESSION['user_id'] ?? 0;
$id = $_POST["id"] ?? "";
$title = $_POST["title"] ?? "";
$category = $_POST["category"] ?? "";
$amount = $_POST["amount"] ?? "";
$expense_date = $_POST["expense_date"] ?? "";
$notes = $_POST["notes"] ?? "";

if ($id === "" || $title === "" || $category === "" || $amount === "" || $expense_date === "") {
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

$stmt = $conn->prepare("UPDATE expenses SET title=?, category=?, amount=?, expense_date=?, notes=? WHERE id=? AND user_id=?");
$stmt->bind_param("ssdssii", $title, $category, $amount, $expense_date, $notes, $id, $user_id);

echo json_encode(["success" => $stmt->execute()]);
?>
