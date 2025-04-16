<?php
require_once '../includes/config.php';

// Session zerstören
$_SESSION = array();
session_destroy();

// Zurück zum Login
header('Location: login.php');
exit;
?>