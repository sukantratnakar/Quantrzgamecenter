<?php
session_start(); // Start or resume the session

// Check if email is set in session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Include control.php to access functions
    require_once 'control.php';

    // Check if user has admin privileges
    if (!isAdmin($email)) {
        // Redirect to index.php if user does not have admin privileges
        echo "<script>window.location.href = 'index.php';</script>";
        exit; // Stop further execution
    }

    // Fetch users and store in JavaScript variable
    echo '<script>';
    echo 'var userEmail = "' . $email . '";';
    echo 'var users = ' . listUsers() . ';'; // Assuming listUsers() returns an array of users
    echo '</script>';
} else {
    // Redirect to index.php if session email is not set
    echo "<script>window.location.href = 'index.php';</script>";
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            margin: 0 5px;
        }
        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }
        .btn-info {
            background-color: #17a2b8;
            color: #fff;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-danger:last-child {
            margin-left: 10px; /* Add some space between logout button and close panel button */
        }
        table {
            width: 100%;
            background-color: #fff;
            border: 1px solid #ddd;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Admin Panel</h1>

    <!-- Button Group -->
    <div class="btn-group d-flex justify-content-center">
        <button class="btn btn-primary mx-2" onclick="listUsers()">List Users</button>
        <button class="btn btn-info mx-2" onclick="listAllowedUsers()">List Allowed Users</button>
        <button class="btn btn-warning mx-2" onclick="listNotAllowedUsers()">List Not Allowed Users</button>
        <button class="btn btn-warning mx-2" onclick="refresh()">Refresh the data</button>
        <button class="btn btn-danger mx-2" onclick="closePanel()">Close Panel</button>
        <button class="btn btn-danger mx-2" onclick="logout()">Logout</button> <!-- Changed X to Logout -->
    </div>

    <!-- Table Placeholder -->
    <div id="table-container">
        <!-- Table will be loaded here dynamically -->
    </div>
</div>

<script>
    // Function to list all users
    function listUsers() {
        renderUsers(users);
    }

    // Function to list allowed users
    function listAllowedUsers() {
        var allowedUsers = users.filter(function(user) {
            return user.Allowed == 1;
        });
        renderUsers(allowedUsers);
    }

    // Function to list not allowed users
    function listNotAllowedUsers() {
        var notAllowedUsers = users.filter(function(user) {
            return user.Allowed == 0;
        });
        renderUsers(notAllowedUsers);
    }

    // Function to render users in the table
    function renderUsers(usersArray) {
        var tableHtml = '<table>';
        tableHtml += '<tr>';
        tableHtml += '<th onclick="sortUsers(\'ID\')">ID</th>';
        tableHtml += '<th onclick="sortUsers(\'Display Name\')">Display Name</th>';
        tableHtml += '<th onclick="sortUsers(\'Email\')">Email</th>';
        tableHtml += '<th onclick="sortUsers(\'Verified\')">Verified</th>';
        tableHtml += '<th onclick="sortUsers(\'Allowed\')">Allowed</th>';
        tableHtml += '<th onclick="sortUsers(\'Has Admin privileges\')">Has Admin Privileges</th>';
        tableHtml += '<th onclick="sortUsers(\'Location\')">Location</th>';
        tableHtml += '<th onclick="sortUsers(\'Member Since\')">Member Since</th>';
        tableHtml += '</tr>';
        
        usersArray.forEach(function(user) {
            tableHtml += '<tr>';
            tableHtml += '<td>' + user.ID + '</td>';
            tableHtml += '<td>' + user['Display Name'] + '</td>';
            tableHtml += '<td>' + user.Email + '</td>';
            tableHtml += '<td>' + (user.Verified === 1 ? 'Yes' : 'No') + '</td>';
            tableHtml += '<td>';
            if (user.Allowed === 1) {
                tableHtml += '<span style="cursor: pointer; color: green;" onclick="changeAllowUser(\'' + user.Email + '\', \'' + user['Display Name'] + '\', 0)">Yes</span>';
            } else {
                tableHtml += '<span style="cursor: pointer; color: red;" onclick="changeAllowUser(\'' + user.Email + '\', \'' + user['Display Name'] + '\', 1)">No</span>';
            }
            tableHtml += '</td>';
            tableHtml += '<td>';
            if (user['Has Admin privileges'] === 1) {
                tableHtml += '<span style="cursor: pointer; color: green;" onclick="changeAllowUserAdmin(\'' + user.Email + '\', \'' + user['Display Name'] + '\', 0)">Yes</span>';
            } else {
                tableHtml += '<span style="cursor: pointer; color: red;" onclick="changeAllowUserAdmin(\'' + user.Email + '\', \'' + user['Display Name'] + '\', 1)">No</span>';
            }
            tableHtml += '</td>';
            tableHtml += '<td>' + user.Location + '</td>';
            tableHtml += '<td>' + user['Member Since'] + '</td>';
            tableHtml += '</tr>';
        });

        tableHtml += '</table>';
        document.getElementById('table-container').innerHTML = tableHtml;
    }

    // Function to handle allowing/disallowing a user
    function changeAllowUser(email, displayName, value) {
        if (confirm('Do you want to allow user: ' + displayName + ' (' + email + ') ?')) {
            // Create a form dynamically
            var form = document.createElement('form');
            form.method = 'post';
            form.action = 'adminEXE.php'; // Replace with your PHP file path

            // Create input fields to send data
            var actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'Allowed'; // Action to allow user in adminEXE.php

            var emailField = document.createElement('input');
            emailField.type = 'hidden';
            emailField.name = 'email';
            emailField.value = email;

            var valueField = document.createElement('input');
            valueField.type = 'hidden';
            valueField.name = 'value';
            valueField.value = value;

            // Append inputs to the form
            form.appendChild(actionField);
            form.appendChild(emailField);
            form.appendChild(valueField);
            
            // Append form to the document body (necessary for older IE support)
            document.body.appendChild(form);

            // Submit the form
            form.submit();

            // Inform the user about the action (optional)
            //alert('Success! Changes will take effect shortly.');

            // Optional: Refresh table after 3 seconds
            // setTimeout(function() {
            //    listUsers();
            // }, 3000);
        }
    }


    function changeAllowUserAdmin(email, displayName, value) {
        if (confirm('Do you want to allow user: ' + displayName + ' (' + email + ') ?')) {
            // Create a form dynamically
            var form = document.createElement('form');
            form.method = 'post';
            form.action = 'adminEXE.php'; // Replace with your PHP file path

            // Create input fields to send data
            var actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'Admin'; // Action to allow user in adminEXE.php

            var emailField = document.createElement('input');
            emailField.type = 'hidden';
            emailField.name = 'email';
            emailField.value = email;

            var valueField = document.createElement('input');
            valueField.type = 'hidden';
            valueField.name = 'value';
            valueField.value = value;

            // Append inputs to the form
            form.appendChild(actionField);
            form.appendChild(emailField);
            form.appendChild(valueField);
            
            // Append form to the document body (necessary for older IE support)
            document.body.appendChild(form);

            // Submit the form
            form.submit();

            // Inform the user about the action (optional)
            //alert('Success! Changes will take effect shortly.');

            // Optional: Refresh table after 3 seconds
            // setTimeout(function() {
            //    listUsers();
            // }, 3000);
        }
    }


    // Function to sort users by column
    function sortUsers(column) {
        users.sort(function(a, b) {
            if (column === 'ID' || column === 'Verified' || column === 'Allowed' || column === 'Has Admin privileges') {
                return a[column] - b[column];
            } else if (column === 'Member Since') {
                return new Date(a[column]) - new Date(b[column]);
            } else {
                return a[column].localeCompare(b[column], undefined, {numeric: true, sensitivity: 'base'});
            }
        });

        renderUsers(users);
    }

    // Function to close panel (placeholder)
    function closePanel() {
        window.location.href = 'testmenu.php'; 
    }
    
    // Function to close panel (placeholder)
    function refresh() {
        window.location.href = 'adminPanel.php'; 
    }
    
    // Function to logout (placeholder)
    function logout() {
        window.location.href = 'logout.php'; 
    }
    
    listUsers();
</script>
</body>
</html>
