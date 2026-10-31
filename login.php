<?php
session_start();
include "db.php";

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
  header("Location: login.html?error=empty");
  exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    header("Location: index.php");
    exit;
  }

  header("Location: login.html?error=wrong_password");
  exit;
}

/*
  If the email is not yet registered, save it in the users table after successful
  form submission. This makes sure the user is added to the database users table.
*/
$name = explode('@', $email)[0];
$hashed = password_hash($password, PASSWORD_DEFAULT);

$insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$insert->bind_param("sss", $name, $email, $hashed);

if ($insert->execute()) {
  $_SESSION['user_id'] = $conn->insert_id;
  $_SESSION['user_name'] = $name;
  $_SESSION['user_email'] = $email;
  header("Location: index.php");
  exit;
}

header("Location: login.html?error=login_failed");
exit;
?>
