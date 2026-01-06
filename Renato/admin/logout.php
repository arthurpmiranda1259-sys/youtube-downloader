<?php
session_start();
require_once __DIR__ . '/utils.php';

session_unset();
session_destroy();

redirect('login.php');
?>
