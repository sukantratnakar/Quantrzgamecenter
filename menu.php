<?php
session_start(); // Start or resume the session

// Access the stored email from session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    echo '<script>var userEmail = "' . $email . '";</script>';
}  else {
    echo "<script> window.location.href = 'index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <title>Quantraz Game Center</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-image: url('images/bg.svg');
            background-size: cover; /* Ensures the background image covers the entire body */
            background-repeat: no-repeat; /* Prevents the background image from repeating */
            background-attachment: fixed; /* Fixes the background image so it doesn't scroll with the content */
        }
        h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 40px;
        }
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .button {
            padding: 20px 40px;
            font-size: 24px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            width: 200px;
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .button:hover {
            background-color: #0056b3;
        }
        .top{
            font-family: Arial, sans-serif;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            
        }
        .top img{
            width: 600px;
            height: 250px;
            margin-left: 20px;
            margin-bottom: 0px;
        }
    </style>
</head>
<body>
    <br>
    <br>
    <br>
    <div class="top">
        <img src="images/Game_Center_White_Logo.png" alt="Placeholder">
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <div class="button-container">
        <button class="button" onclick="move(1)">Understanding Tinergy</button>
        <button class="button" onclick="move(2)">Classic Game</button>
    </div>
</body>
<script>
    function move(where){
        console.log("moved");
        switch (where) {
            case 1:
                 window.location.href = 'testmenu.php?type=';
                break;
            case 2:
                 window.location.href = 'testmenu.php?type=standard';
                break;
        }
    }
</script>
</html>
