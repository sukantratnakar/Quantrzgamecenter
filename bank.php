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
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <style>
        body {
            position: relative;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
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
            width: 80%;
            margin: auto;
        }
        .team-container {
            margin-bottom: 20px;
        }
        .team-container input[type="range"] {
            width: 70%;
            margin-right: 10px;
        }
        .team-container input[type="number"] {
            width: 20%;
        }
        .message {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .proceed-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            font-size: 18px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .header {
            text-align: center;
            font-size: 50px;
        }
    </style>
</head>
<body>
    <header><h1 class="header">Quantraz Bank</h1></header>
    
    <div class="container">
        <div class="message">Adjust initial settings for each team:</div>
        <div id="teams-container"></div>
        <div class="note">
            Note: The loan must be repaid to Quantraz Bank by the end of the game.
        </div>
        
    </div>
    <button class="proceed-button" onclick="proceedToExchange()">Proceed</button>
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
                <label for="${team.teamName}-coins-slider">Bank Loan:</label>
                <input type="range" id="${team.teamName}-coins-slider" min="20" max="200" value="100">
                <input type="number" id="${team.teamName}-coins-field" min="20" max="200" value="100">
                <br>
                <label for="${team.teamName}-player-cost-slider">Player Cost:</label>
                <input type="range" id="${team.teamName}-player-cost-slider" min=1" max="${countPlayersInTeam(team.teamName) * 10}" value="0">
                <input type="number" id="${team.teamName}-player-cost-field" min="1" max="${countPlayersInTeam(team.teamName) * 10}" value="0">
            `;
            teamsContainer.appendChild(teamDiv);

            const coinSlider = document.getElementById(`${team.teamName}-coins-slider`);
            const coinField = document.getElementById(`${team.teamName}-coins-field`);
            const playerCostSlider = document.getElementById(`${team.teamName}-player-cost-slider`);
            const playerCostField = document.getElementById(`${team.teamName}-player-cost-field`);

            coinSlider.addEventListener('input', function() {
                coinField.value = this.value;
                updateMaxPlayerCost(team);
            });

            coinField.addEventListener('input', function() {
                if (parseInt(this.value) > 200) {
                    this.value = 200;
                } else if (parseInt(this.value) < 20) {
                    this.value = 20;
                }
                coinSlider.value = this.value;
                updateMaxPlayerCost(team);
            });

            playerCostSlider.addEventListener('input', function() {
                playerCostField.value = this.value;
            });

            playerCostField.addEventListener('input', function() {
                if (parseInt(this.value) > parseInt(coinField.value)) {
                    this.value = coinField.value;
                } else if (parseInt(this.value) < 0) {
                    this.value = 0;
                }
                playerCostSlider.value = this.value;
            });

            function updateMaxPlayerCost(team) {
                playerCostSlider.max = coinSlider.value;
                playerCostField.max = coinField.value;
                if (parseInt(playerCostField.value) > parseInt(coinField.value)) {
                    playerCostField.value = coinField.value;
                    playerCostSlider.value = coinSlider.value;
                }
            }
        });
        
        
        
        function countPlayersInTeam(teamName) {
            
            // Find the team by name
            let team = teams.find(t => t.teamName === teamName);
            
            // If team is found, return the number of players
            if (team) {
                return team.players.length;
            } else {
                console.error(`Team ${teamName} not found.`);
                return 0;
            }
        }

        function proceedToExchange() {
            teams.forEach(function(team) {
                const coinSlider = document.getElementById(`${team.teamName}-coins-slider`);
                const coinField = document.getElementById(`${team.teamName}-coins-field`);
                const playerCostSlider = document.getElementById(`${team.teamName}-player-cost-slider`);
                const playerCostField = document.getElementById(`${team.teamName}-player-cost-field`);
                
                team.coins = parseInt(coinSlider.value);
                team.startingCoins = team.coins;
                team.playerCost = parseInt(playerCostSlider.value);
                team.coins = team.coins - team.playerCost;
            });

            localStorage.setItem(userEmail + "teams" + type, JSON.stringify(teams));
            
            window.location.href = `exchange.php?type=${encodeURIComponent(type)}`;    
        }
    </script>
</body>
</html>
