<?php
// logout.php - User logout
require_once 'config.php';
session_destroy();
redirect('login.php');
?>