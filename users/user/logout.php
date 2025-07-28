<?php
require_once '../includes/auth.php';

$auth->logout();
redirect('../index.php');