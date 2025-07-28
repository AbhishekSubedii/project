<?php
require_once '../includes/AdminAuth.php';
require_once '../includes/functions.php';

$adminAuth->logout();
redirect('login.php');
?>
