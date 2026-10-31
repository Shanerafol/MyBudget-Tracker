<?php
require 'db.php'; csrf_check();
$name=trim($_POST['name'] ?? '');$email=trim(strtolower($_POST['email'] ?? ''));$password=$_POST['password'] ?? '';
if ($name==='' || strlen($name)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>120 || strlen($password)<8) respond(['success'=>false,'message'=>'Use a name, valid email, and password of at least 8 characters.'],422);
$hash=password_hash($password,PASSWORD_DEFAULT);
try { $stmt=$conn->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');$stmt->bind_param('sss',$name,$email,$hash);$stmt->execute(); }
catch (mysqli_sql_exception $e) { if($e->getCode()==1062) respond(['success'=>false,'message'=>'Email is already registered.'],409); throw $e; }
session_regenerate_id(true);
$_SESSION['user_id']=$conn->insert_id;$_SESSION['user_name']=$name;$_SESSION['user_email']=$email;$_SESSION['csrf']=bin2hex(random_bytes(32));
respond(['success'=>true]);
