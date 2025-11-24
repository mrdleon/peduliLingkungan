<?php
require_once 'config.php';

// 1. Hapus Session
$_SESSION = array();
session_destroy();

// 2. Hapus Cookie (Caranya: set waktu kadaluarsa ke masa lalu)
setcookie('id', '', time() - 3600, "/");
setcookie('key', '', time() - 3600, "/");

// Redirect ke halaman utama
header("location: index.php");
exit;
?>