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
<html>
<head>
    <link rel="stylesheet" href="styles/wheel.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <link rel="stylesheet" href="styles/header.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            color: #333;
            line-height: 1.6;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("images/bank_backgorund.png");
            background-size: cover;
            opacity: 0.4; /* Adjust this value for more or less transparency */
            z-index: -1;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #4682b4;
            margin-bottom: 30px;
        }
        .team-container {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #e6f3ff;
            border-radius: 5px;
        }
        .team-container h2 {
            color: #4682b4;
            margin-top: 0;
        }
        .input-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .input-row label {
            flex: 0 0 120px;
        }
        .input-row input[type="range"] {
            flex: 1;
            margin: 0 10px;
        }
        .input-row input[type="number"] {
            width: 60px;
            padding: 5px;
            border: 1px solid #4682b4;
            border-radius: 3px;
        }
        .proceed-button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 18px;
            background-color: #4682b4;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .proceed-button:hover {
            background-color: #3a6d8c;
        }
    </style>
</head>
<body>
    
    
    <div class="container">
        <h1 class="header">Quantraz Exchange</h1>
        <div id="teams-container"></div>
        <button class="proceed-button" onclick="proceedToNextStage()">Proceed</button>
    </div>

    <script>

        
        // Function to get URL parameters
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Get the 'type' parameter from the URL
        let type = getQueryParam('type');

        // React based on the 'type' value
        if (type === null) {
            // Redirect to menu.php
            window.location.href = 'menu.php';
        }



        const teamsJSON = localStorage.getItem(userEmail + "teams" + type);
        const teams = JSON.parse(teamsJSON);

        const teamsContainer = document.getElementById("teams-container");

        teams.forEach(function(team) {
            const teamDiv = document.createElement("div");
            teamDiv.className = "team-container";
            teamDiv.innerHTML = `
                <h2>Team: ${team.teamName}</h2>
                <label id="${team.teamName}-coins-label">Coins: ${team.coins}</label>
                <div class="input-row">
                    <label for="${team.teamName}-time-slider">Time Tokens:</label>
                    <input type="range" id="${team.teamName}-time-slider" min="0" max="${Math.floor(team.coins / 2)}" value="0">
                    <input type="number" id="${team.teamName}-time-field" min="0" max="${Math.floor(team.coins / 2)}" value="0">
                </div>
                <div class="input-row">
                    <label for="${team.teamName}-energy-slider">Energy Tokens:</label>
                    <input type="range" id="${team.teamName}-energy-slider" min="0" max="${Math.floor(team.coins / 2)}" value="0">
                    <input type="number" id="${team.teamName}-energy-field" min="0" max="${Math.floor(team.coins / 2)}" value="0">
                </div>
            `;
            teamsContainer.appendChild(teamDiv);

            const timeSlider = document.getElementById(`${team.teamName}-time-slider`);
            const timeField = document.getElementById(`${team.teamName}-time-field`);
            const energySlider = document.getElementById(`${team.teamName}-energy-slider`);
            const energyField = document.getElementById(`${team.teamName}-energy-field`);
            const coinsLabel = document.getElementById(`${team.teamName}-coins-label`);

            timeSlider.addEventListener('input', function() {
                timeField.value = this.value;
                validateSliders(team.coins, timeSlider, timeField, energySlider, energyField, coinsLabel);
            });

            timeField.addEventListener('input', function() {
                if (parseInt(this.value) > Math.floor(team.coins / 2)) {
                    this.value = Math.floor(team.coins / 2);
                } else if (parseInt(this.value) < 0) {
                    this.value = 0;
                }
                timeSlider.value = this.value;
                validateSliders(team.coins, timeSlider, timeField, energySlider, energyField, coinsLabel);
            });

            energySlider.addEventListener('input', function() {
                energyField.value = this.value;
                validateSliders(team.coins, timeSlider, timeField, energySlider, energyField, coinsLabel);
            });

            energyField.addEventListener('input', function() {
                if (parseInt(this.value) > Math.floor(team.coins / 2)) {
                    this.value = Math.floor(team.coins / 2);
                } else if (parseInt(this.value) < 0) {
                    this.value = 0;
                }
                energySlider.value = this.value;
                validateSliders(team.coins, timeSlider, timeField, energySlider, energyField, coinsLabel);
            });
        });
        
        

        function validateSliders(maxCoins, timeSlider, timeField, energySlider, energyField, coinsLabel) {
            const total = parseInt(timeSlider.value) + parseInt(energySlider.value);
            maxCoins = Math.floor(maxCoins / 2);
            if (total > maxCoins) {
                if (timeSlider === document.activeElement) {
                    energySlider.value = maxCoins - parseInt(timeSlider.value);
                    energyField.value = energySlider.value;
                } else {
                    timeSlider.value = maxCoins - parseInt(energySlider.value);
                    timeField.value = timeSlider.value;
                }
            }
            let coinsLeft = maxCoins * 2 - total * 2;
            coinsLeft = Math.max(coinsLeft, 0);
            coinsLabel.textContent = `Coins: ${coinsLeft}`;
        }

        function proceedToNextStage() {
            teams.forEach(function(team) {
                const timeSlider = document.getElementById(`${team.teamName}-time-slider`);
                const energySlider = document.getElementById(`${team.teamName}-energy-slider`);
                const coinLabel = document.getElementById(`${team.teamName}-coins-label`);
                let data = coinLabel.textContent.split(" ");
                team.timeTokens = parseInt(timeSlider.value);
                team.energyTokens = parseInt(energySlider.value);
                team.coins = parseInt(data[1]);
                team.profit = 0;
            });

            localStorage.setItem(userEmail + "teams" + type, JSON.stringify(teams));
            if(type === "")
                window.location.href = "wheel.php"; // Replace with the actual next page URL
            else if(type = "standard")
                window.location.href = "standard_game.php";
            
        }
    </script>
</body>
</html>
