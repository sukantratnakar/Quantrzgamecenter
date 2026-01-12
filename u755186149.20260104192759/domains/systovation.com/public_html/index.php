<?php
session_start(); // Start or resume the session

// Access the stored email from session
if (isset($_SESSION['email'])) {
    echo "<script> window.location.href = 'testmenu.php';</script>";
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quantraz Game Center</title>
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
</head>
<style>
    body {
    background-image: url('images/bg.svg');
    background-size: cover; /* Ensures the background image covers the entire body */
    background-repeat: no-repeat; /* Prevents the background image from repeating */
    background-attachment: fixed; /* Fixes the background image so it doesn't scroll with the content */
    }
</style>
<body>
    <header>
        <h1 style="color: #CBC493;">Quantraz Game Center</h1>
    </header>

    <section class="form-container">
        <form class="signup-form" action="" method="post" onsubmit="clear()">
            <div class="form-choice">
                <button type="button" onclick="showSignUpForm()">Sign Up</button>
                <button type="button" onclick="showLoginForm()">Login</button>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="" required>
                <label for="email">Email</label>
            </div>
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="" required>
                <label for="username">Display name</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="" required>
                <label for="password">Password</label>
            </div>
            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="" required>
                <label for="confirm_password">Confirm Password</label>
            </div>
            <div class="terms-group">
                <div>
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="color: white;">I agree to the terms of service</label>
                </div>
                <button class="submit-btn" type="submit">Send</button>
            </div>
        </form>
        
        <form class="login-form" action="login.php" method="post" onsubmit="clear()">
            <div class="form-choice">
                <button type="button" onclick="showSignUpForm()">Sign Up</button>
                <button type="button" onclick="showLoginForm()">Login</button>
            </div>
            <div class="form-group">
                <input type="text" id="email" name="email" placeholder="" required>
                <label for="email">Email</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="" required>
                <label for="password">Password</label>
            </div>
            <div class="bottom-form" style="display: flex; justify-content: space-between;">
                <a href="/forgot-password" style="color: white;">Forgot Password?</a>
                <button class="login-btn" type="submit">Send</button>
            </div>
        </form>
    </section>

    <footer>
        <p>Copyright Quantraz &copy; 2024</p>
    </footer>

    <script>
        function showSignUpForm() {
            document.querySelector('.signup-form').style.display = 'block';
            document.querySelector('.login-form').style.display = 'none';
        }

        function showLoginForm() {
            document.querySelector('.signup-form').style.display = 'none';
            document.querySelector('.login-form').style.display = 'block';
        }
        
        function clear() {
            // Get all input elements in both forms
            var inputElements = document.querySelectorAll('.signup-form input, .login-form input');
        
            // Loop through each input element and reset its value
            inputElements.forEach(function(input) {
                input.value = '';
            });
        
            // Optionally reset checkboxes and other form elements if needed
            document.getElementById('signup-terms').checked = false;
        }
    </script>
</body>
</html>
