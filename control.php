<?php


require 'vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "database.php";


$db = new Database();
$conn = $db->connect();


function signup($username, $email, $password, $confirm_password){
    global $conn;
    $result = validateUserData($username, $email, $password, $confirm_password);
    if($result === "Valid"){
        return insertUser($username, $email, $password);
    }
    else{
        return $result; 
    }
}

function insertUser($username, $email, $password) {
    global $conn;
    // Get the user's IP address    
    $userIP = $_SERVER['REMOTE_ADDR'];

    // Get user's location
    $location = getUserLocation($userIP);

    // Check if location retrieval was successful
    if ($location == "failed") {
        return "Error retrieving user location";
    }

    // Generate vkey
    $vkey = hash('sha256', random_bytes(32)); // Generate 32 random bytes and convert them to hexadecimal

    // Send confirmation email WILL NEED TO BE IMPLEMENTED ACTUALLY
    sendConfirmationEmail($email, $username, $vkey);

    // Hash password using password_hash() function
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Get current date for memberSince and lastLogin
    $currentDate = date("Y-m-d");
    

    // Prepare SQL statement
    $sql = "INSERT INTO users (username, email, password, vkey, verified, allowed, memberSince, lastLogin, location, LastSubscription, isAdmin, accountType)
            VALUES (?, ?, ?, ?, 0, 0, ?, ?, ?, NULL, 0, 1)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $email, $hashed_password, $vkey, $currentDate, $currentDate, $location);


    // Execute query
    if ($stmt->execute()) { 
        return "User inserted successfully";
    } else {
        return "Error inserting user: " . $stmt->error;
    }
}

function validateUserData($username, $email, $password, $confirm_password) {
    global $conn;
    // Email validation using regex
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }

    // Check for whitespace or new lines in password
    if (preg_match('/\s/', $password)) {
        return "Password cannot contain spaces";
    }

    // Password match validation
    if ($password !== $confirm_password) {
        return "Passwords do not match";
    }

    // Check if email is unique
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "We found an account with this email. Try login instead";
    }
    // If everything is valid
    return "Valid";
}

function login($email, $password) {
    global $conn;

    // Prepare SQL statement to fetch user data by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user with provided email exists
    if ($result->num_rows === 0) {
        return "Incorrect email or password";
    }

    // Fetch user data
    $user = $result->fetch_assoc();
  
    // Check if user is verified
    if ($user['verified'] == 0) {
        return "Email not verified";
    }
    
    if($user['allowed'] == 0){
        return "We are reviewing your account at the moment, please come back later on";
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        return "Incorrect email or password";
    }
    
    updateLastLoginDate($email);
    // Successful login
    return "Login successful";
}

function updateLastLoginDate($email){
    global $conn;
    
    $currentDate = date("Y-m-d"); // Use current date and time

    // Prepare SQL statement
    $sql = "UPDATE users SET lastLogin = ? WHERE email = ?";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $currentDate, $email);
    
    $stmt->execute();
}


function sendConfirmationEmail($email, $username, $vkey){
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'community@systovation.com';
        $mail->Password   = 'Systovation#2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('community@systovation.com', 'Systovation');
        $mail->addAddress($email, $username);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body    = "
            <h1>Email Verification</h1>
            <p>Hi $username,</p>
            <p>Thank you for registering on our website. Please click the link below to verify your email address:</p>
            <p><a href='http://systovation.com/verify.php?vkey=$vkey'>Verify Email</a></p>
            <p>If you did not register on our website, please ignore this email.</p>
        ";

        $mail->send();
        echo 'The email message was sent.';
        sendEmailSukant($email, $username);
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}


function sendEmailSukant($email, $username){
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'community@systovation.com';
        $mail->Password   = 'Systovation#2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('community@systovation.com', 'Systovation');
        $mail->addAddress('hello@quantraz.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New User on systovation';
        $mail->Body    = "
            <h1>Hi Sukant, there are new users on systovation.com requiring allowance</h1>
            <p>User '{$username}' ('{$email}') is waiting for your approval to join the website</p>
            <p><a href='http://systovation.com/adminPanel.php'>Access the admin panel</a></p>
        ";

        $mail->send();
        echo 'The email message was sent.';
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}


function getUsernameByEmail($email){
    global $conn; // Assuming $conn is your database connection

    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is a row returned
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['username']; // Return the username
    } else {
        return null; // Return null if no username found for the email
    }
}


function sendEmailAllowed($email){
    $mail = new PHPMailer(true);
    $username = getUsernameByEmail($email);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'community@systovation.com';
        $mail->Password   = 'Systovation#2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('community@systovation.com', 'Systovation');
        $mail->addAddress($email, $username);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Quantraz Game Center';
        $mail->Body = "
            <html>
            <body>
                <div style='font-family: Arial, sans-serif; width: 60%; margin: 0 auto; background-color: #f2f2f2; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);'>
                    <h1 style='color: #3399ff;'>Welcome to Quantraz Game Center, $username!</h1>
                    <p style='color: #333; line-height: 1.6;'>You have been granted access on systovation.com.</p>
                    <p style='color: #333; line-height: 1.6;'>Click the button below to log in and get started:</p>
                    <p><a href='http://systovation.com' style='display: inline-block; padding: 10px 20px; background-color: #3399ff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s;'>Login to Quantraz Game Center</a></p>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        echo 'The email message was sent.';
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}




function getUserLocation($userIP) {
    // Get geolocation details from ipinfo.io
    $details = json_decode(file_get_contents("http://ipinfo.io/{$userIP}/json"));

    // Check if geolocation details are available
    if (!empty($details->city) && !empty($details->postal) && !empty($details->region) && !empty($details->country)) {
        // Extract specific information
        $city = $details->city;
        $zip = $details->postal;
        $province = $details->region;
        $country = $details->country;

        // Format the information into a single string separated by commas
        $locationString = "$city, $zip, $province, $country";

        // Return the location string
        return $locationString;
    } else {
        // If any required information is missing, return "failed"
        return "failed";
    }
}


function isAdmin($email){
    global $conn;
    
    // Prepare SQL statement
    $sql = "SELECT isAdmin FROM users WHERE email = ? LIMIT 1";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    
    // Execute query
    if ($stmt->execute()) {
        // Bind result variables
        $stmt->bind_result($isAdmin);
        
        // Fetch result
        if ($stmt->fetch()) {
            // Check if isAdmin is 1 or 0 (assuming isAdmin is an integer field)
            if ($isAdmin == 1) {
                return true; // User is admin
            } else {
                return false; // User is not admin
            }
        } else {
            return false; // No user found or fetch failed
        }
    } else {
        // Error executing query
        return false;
    }
}




// Function to list users from the database
function listUsers() {
    global $conn; // Assuming $conn is your database connection object

    // Prepare SQL statement to select users
    $sql = "SELECT userID as 'ID', username as 'Display Name', email as 'Email', verified as 'Verified', allowed as 'Allowed', memberSince as 'Member Since', location as 'Location', isAdmin as 'Has Admin privileges' FROM users";
    
    // Initialize an empty array to store users
    $users = array();
    
    // Execute SQL query
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        // Bind variables to prepared statement
        $stmt->bind_result($userID, $username, $email, $verified, $allowed, $memberSince, $location, $isAdmin);
        
        // Fetch data and populate $users array
        while ($stmt->fetch()) {
            $users[] = array(
                'ID' => $userID,
                'Display Name' => $username,
                'Email' => $email,
                'Verified' => $verified,
                'Allowed' => $allowed,
                'Member Since' => $memberSince,
                'Location' => $location,
                'Has Admin privileges' => $isAdmin
            );
        }
        
        // Close statement
        $stmt->close();
    } else {
        // Error executing query
        return false;
    }
    
    // Return JSON encoded array of users
    return json_encode($users);
}


function changePrivledge($email, $privledge, $value) {
    global $conn;
    
    
    try {
        $send = false;
        // Check which privilege is being updated
        switch ($privledge) {
            case 'Allowed':
                // Update Allowed privilege for the user
                $sql = "UPDATE users SET Allowed = ? WHERE Email = ?";
                if($value == 1){
                    $send = true;
                }
                break;
            case 'Admin':
                // Update Admin privilege for the user
                $sql = "UPDATE users SET isAdmin = ? WHERE Email = ?";
                break;
            default:
                // Handle case where $privledge is not recognized
                return false;
        }

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param('is', $value, $email); // 'is' indicates integer and string types

        // Execute the statement
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            if($send){
                sendEmailAllowed($email);
            }
            return true; // Return true if update was successful
        } else {
            return false; // Return false if update failed or no rows were affected
        }
    } catch (Exception $e) {
        // Log or handle the exception appropriately
        // error_log('MySQLi Exception: ' . $e->getMessage());
        return false; // Return false on error
    } finally {
        // Close statement and connection
        if ($stmt) {
            $stmt->close();
        }
        $conn->close();
    }
}


?>
