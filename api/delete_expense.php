<?php
require 'db.php';csrf_check();$user_id=logged_in();$id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT);if(!$id || $id<1) respond(['success'=>false,'message'=>'Invalid expense ID'],422);
$stmt=$conn->prepare('DELETE FROM expenses WHERE id=? AND user_id=?');$stmt->bind_param('ii',$id,$user_id);$stmt->execute();respond(['success'=>true,'deleted'=>$stmt->affected_rows]);
