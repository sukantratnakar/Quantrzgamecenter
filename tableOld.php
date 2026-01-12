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
    <title>Quantraz Game Center</title>
    <link rel="stylesheet" href="styles/wheel.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <link rel="stylesheet" href="styles/header.css">
    <style>
        .container {
            width: 80%;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #65b0ff;
            color: #333;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .buttons-container {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        .button {
            padding: 10px 15px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .exit-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 15px;
            font-size: 16px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .exit-button:hover {
            background-color: #c82333;
        }
        .winner-announcement {
            text-align: center;
            font-size: 20px;
            margin-top: 20px;
        }
        .header {
            text-align: center;
            font-size: 36px;
            margin: 20px 0;
        }
        .details-button {
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            padding: 10px 15px;
            font-size: 16px;
            margin-left: 10px;
        }
        .details-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body style="display: block;">
    <header><h1 class="header">Quantraz Game Center</h1></header>
    <br>
    <br>
    <br>
    <div class="container">
        <table id="teams-table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Coins Earned</th>
                    <th>Bank Loan</th>
                    <th>Player Cost</th>
                    <th>Coins in Hand</th>
                    <th>Tokens owned</th>
                    <th>Earning to Player Purchase Ratio (EP)</th>
                    <th>Net Profit</th>
                    <th>Loan Productivity Ratio (LPR)</th>
                    <th>Bank selling value of a token</th>
                    <th>Bank buying value of a token</th>
                    <th>Overall Score</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody id="teams-body"></tbody>
        </table>
        <div class="buttons-container">
            <button class="button" id="calcuateButton" onclick="calculatePayback()">Calculate</button>
        </div>
        <h2 id="winner-announcement" class="winner-announcement"></h2>
    </div>
    
    <button class="exit-button" onclick="exitGame()">Exit</button>

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

        const teamsBody = document.getElementById("teams-body");

        teams.forEach(function(team) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${team.teamName}</td>
                <td>${team.profit}</td>
                <td>${team.startingCoins - team.playerCost}</td>
                <td>${team.playerCost}</td>
                <td>${team.coins}</td>
                <td>${team.energyTokens + team.timeTokens}</td>
                <td id="${team.teamName}-ep">Calculating...</td>
                <td id="${team.teamName}-profit">Calculating...</td>
                <td id="${team.teamName}-pr">Calculating...</td>
                <td>2</td>
                <td>1</td>
                <td id="${team.teamName}-score">N/A</td>
                <td><button class="details-button" onclick="showDetails('${team.teamName}')">View Details</button></td>
            `;
            teamsBody.appendChild(row);
        });

        function calculatePayback() {
            teams.forEach(function(team) {
                const coinsEarned = team.profit;
                const costOfPlayers = team.playerCost;
                const loanAmount = team.startingCoins;
                const energyTokens = team.energyTokens || 0;
                const timeTokens = team.timeTokens || 0;

                const coinsReceivedFromTokens = (energyTokens + timeTokens) * 0.5; // 50% of 2 coins per token
                const totalCoinsEarned = team.coins + coinsReceivedFromTokens;
                const earningToPlayerPurchaseRatio = coinsEarned / costOfPlayers;
                const profit = totalCoinsEarned - loanAmount;
                const productivityRatio = team.profit / loanAmount;


                document.getElementById(`${team.teamName}-ep`).textContent = earningToPlayerPurchaseRatio.toFixed(2);
                document.getElementById(`${team.teamName}-profit`).textContent = profit.toFixed(2);
                document.getElementById(`${team.teamName}-pr`).textContent = productivityRatio.toFixed(2);
                


                team.earningToPlayerPurchaseRatio = earningToPlayerPurchaseRatio;
                team.profit = profit;
                team.productivityRatio = productivityRatio;

                team.overallScore = (0.15 * coinsEarned) +
                                    (0.25 * profit) +
                                    (0.30 * earningToPlayerPurchaseRatio) +
                                    (0.30 * productivityRatio);
                                    
                document.getElementById(`${team.teamName}-score`).textContent = team.overallScore.toFixed(2);
            });

            teams.sort((a, b) => b.overallScore - a.overallScore);

            const winnerAnnouncement = document.getElementById("winner-announcement");
            winnerAnnouncement.textContent = `Winning Team: ${teams[0].teamName} with an overall score of ${teams[0].overallScore.toFixed(2)}`;
            
            document.getElementById("calcuateButton").style.display = "none";
        }

        function clearGame() {
            localStorage.removeItem(userEmail + "teams" + type);
            localStorage.removeItem(userEmail + "order" + type);
            localStorage.removeItem(userEmail + "numbers" + type);
        }

        function showDetails(teamName) {
            const team = teams.find(t => t.teamName === teamName);
            if (!team) return;

            alert(`
                Team: ${team.teamName}
                Loan Amount: $${team.startingCoins}
                Coins Earned: $${team.coins}
                Cost of Players: $${team.playerCost}
                Tokens Returned: ${team.energyTokens + team.timeTokens || 0} tokens
                Coins Received from Tokens: ${((team.energyTokens + team.timeTokens) * 0.5).toFixed(2)}
                Earning to Player Purchase Ratio (EP): ${team.earningToPlayerPurchaseRatio.toFixed(2)}
                Profit: $${team.profit.toFixed(2)}
                Productivity Ratio (PR): ${team.productivityRatio.toFixed(2)}
                Overall Score: ${team.overallScore.toFixed(2)}
            `);
        }
        

        function exitGame() {
            clearGame();
            window.location.href = "menu.php"; // Replace with the actual exit page URL
        }
    </script>
</body>
</html>
