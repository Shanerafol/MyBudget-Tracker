<?php
session_start();
include "db.php";

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
  header("Location: register.html?error=empty");
  exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashed);

if ($stmt->execute()) {
  $_SESSION['user_id'] = $conn->insert_id;
  $_SESSION['user_name'] = $name;
  $_SESSION['user_email'] = $email;
  header("Location: index.php");
  exit;
}

header("Location: register.html?error=email_exists");
exit;
?>
