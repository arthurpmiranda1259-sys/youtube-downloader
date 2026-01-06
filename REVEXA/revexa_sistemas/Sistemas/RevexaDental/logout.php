<?php
require_once 'config/config.php';

if (is_logged_in()) {
    log_audit('Logout realizado');
    session_destroy();
}

redirect('index.php');
