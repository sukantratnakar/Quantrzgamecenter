<?php
// Check if the form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $email = $_POST["email"];
    $password = $_POST["password"];

    require_once "control.php";
    $return = login($email, $password);
    if($return === "Login successful"){
        // Redirect to game-center.html on successful login
        session_destroy();
        session_start();
        session_unset();
        $_SESSION['email'] = $email;
        header("Location: menu.php");
        exit; // Stop script execution after the redirect
    }
    else{
        // Output JavaScript alert and redirect
        echo "<script>alert('{$return}. Please try again.'); window.location.href='index.php';</script>";
        exit; // Stop script execution after outputting JavaScript
    }

} else {
    // Handle non-POST requests (optional)
    // you shouldn't be here page
    echo "Invalid request method.";
}

?>
