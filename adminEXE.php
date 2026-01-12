<?php
session_start(); // Start or resume the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Include control.php to access functions
   

        // Ensure email and value parameters are set
        if (isset($_POST['email']) && isset($_POST['value'])) {
            $email = $_POST['email'];
            $value = $_POST['value'];

            // Call function to change user privilege
            require_once 'control.php';
            changePrivledge($email, $_POST['action'], $value);
            
            header("Location: adminPanel.php");
            
        } 
    } 
}
?>
