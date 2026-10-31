<?php
require 'db.php'; csrf_check();
$_SESSION=[];session_destroy();respond(['success'=>true]);
