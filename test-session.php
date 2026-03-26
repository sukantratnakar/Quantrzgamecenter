<?php
// Test session - sets email and redirects to menu
session_start();
$_SESSION['email'] = 'sukantratnakar@gmail.com';
header('Location: menu.php');
exit;
?>
