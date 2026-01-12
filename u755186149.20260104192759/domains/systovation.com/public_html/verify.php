<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Quantraz Game Center</title>
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #4CAF50;
        }
        .error {
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quantraz Game Center</h1>
        <?php
        // Include database.php
        require_once "database.php";

        if (isset($_GET["vkey"])) {
            // Get the vkey from GET parameter
            $vkey = $_GET["vkey"];

            // Create a new Database object
            $db = new Database();

            // Connect to the database
            $conn = $db->connect();

            // Prepare SQL statement to fetch user data by vkey
            $stmt = $conn->prepare("SELECT * FROM users WHERE vkey = ?");
            $stmt->bind_param("s", $vkey);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user with provided vkey exists
            if ($result->num_rows === 0) {
                echo '<div class="error">Invalid verification key. Please contact us for additional information.</div>';
            } else {
                // Update user's verification status
                $update_stmt = $conn->prepare("UPDATE users SET verified = 1 WHERE vkey = ?");
                $update_stmt->bind_param("s", $vkey);
                
                if ($update_stmt->execute()) {
                    echo '<div class="success">Email verified successfully</div>';
                } else {
                    echo '<div class="error">Error updating verification status. Please try again.</div>';
                }
            }
        } else {
            echo '<div class="error">Invalid request</div>';
        }
        ?>
    </div>
</body>
</html>
