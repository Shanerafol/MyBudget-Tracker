<?php
include "db.php";

$user_id = $_SESSION['user_id'] ?? 0;
$id = $_POST["id"] ?? "";

if ($id === "") {
  echo json_encode(["success" => false, "message" => "Missing ID"]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM expenses WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);

echo json_encode(["success" => $stmt->execute()]);
?>
