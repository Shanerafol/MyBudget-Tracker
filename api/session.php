<?php
require 'db.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
respond(['success'=>true,'authenticated'=>!empty($_SESSION['user_id']), 'user'=>empty($_SESSION['user_id']) ? null : ['id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],'email'=>$_SESSION['user_email']], 'csrf'=>$_SESSION['csrf']]);
