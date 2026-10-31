<?php
require 'db.php'; csrf_check();
$email = trim(strtolower($_POST['email'] ?? '')); $password=$_POST['password'] ?? '';
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') respond(['success'=>false,'message'=>'Enter a valid email and password.'],422);
$stmt=$conn->prepare('SELECT id,name,email,password FROM users WHERE email=? LIMIT 1');$stmt->bind_param('s',$email);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
if (!$user || !password_verify($password,$user['password'])) respond(['success'=>false,'message'=>'Incorrect email or password. Register first if you do not have an account.'],401);
session_regenerate_id(true);
$_SESSION['user_id']=(int)$user['id'];$_SESSION['user_name']=$user['name'];$_SESSION['user_email']=$user['email'];$_SESSION['csrf']=bin2hex(random_bytes(32));
respond(['success'=>true,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']]]);
