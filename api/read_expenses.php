<?php
require 'db.php';$user_id=logged_in();
$stmt=$conn->prepare('SELECT id,title,category,amount,expense_date,notes FROM expenses WHERE user_id=? ORDER BY expense_date DESC,id DESC');$stmt->bind_param('i',$user_id);$stmt->execute();respond($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
