<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    require_once "control.php";
    // Call the signup function with the provided parameters
    $return = signup($username, $email, $password, $confirm_password);

    if ($return === "User inserted successfully") {
        echo "<script>alert('Signup successful! Please check your inbox for a verification email.'); window.location.href='index.php';</script>";
        exit; // Stop script execution after outputting JavaScript
    } else {
        echo "<script>alert('{$return}. Please try again.'); window.location.href='index.php';</script>";
        exit; // Stop script execution after outputting JavaScript
    }
} else {
    echo "You shouldn't be here!";
    // Redirect to index.php or display a message
    header("Location: index.php");
    exit;
}
?>
