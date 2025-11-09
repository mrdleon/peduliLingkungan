<?php
require_once 'config.php';

// Hancurkan semua data session
$_SESSION = array();
session_destroy();

// Redirect ke halaman utama
header("location: index.php");
exit;
?>