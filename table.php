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
    <title>Quantraz Game Center</title>
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Dynamic Table Loader</title>
    <style>
        body {
            display: block;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        /* Centered header */
        header {
            text-align: center;
            margin-top: 2%;
        }

        /* Adjust layout */
        .main {
            display: flex;
            height: 90vh;
            margin: 0;
        }

        /* Left sidebar for buttons */
        .sidebar {
            width: 20%;
            background-color: #f4f4f4;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .sidebar button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .sidebar button:hover {
            background-color: #0056b3;
        }

        /* Main content area for tables */
        .content {
            width: 100%;
            
        }

        /* Make the table smaller and centered */
        #tableContainer {
            max-width: 1400px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    
    <header><h1>Quantraz Game Center</h1></header>
    <br>
    <br>
    <br>
    <div class="main">
        <div class="sidebar">
            <button onclick="loadTable(1)">Contest Menu</button>
            <button onclick="loadTable(2)">Game Data</button>
            <button onclick="loadTable(3)">Data Analysis</button>
            <button onclick="loadTable(4)">Parameters for Winner</button>
            <button onclick="loadTable(5)">Key Definitions</button>
            <button onclick="loadTable(6)">Key Ratio Formulas</button>
            <button onclick="loadTable(7)">Winning Teams in Category</button>
            <button onclick="loadTable(9)">Weightage per Category</button>
            <button onclick="loadTable(8)">Final Winner</button>
        </div>
    
        <div class="content" id="tableContainer">
            <!-- The table will be dynamically loaded here -->
            <p>Click a button to load a table.</p>
        </div>
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


    
        function loadTable(preset) {
            const tableContainer = document.getElementById('tableContainer');
            
            // Define table presets
            const tables = {
                1: `
                   <table>
                        <tr>
                            <th>Table Number</th>
                            <th>Table Name</th>
                            <th>Definition</th>
                        </tr>
                        <tr>
                            <td>Table 1</td>
                            <td>Game Data</td>
                            <td>Contains essential game data like loan amount, player cost, coins earned, and tokens used.</td>
                        </tr>
                        <tr>
                            <td>Table 2</td>
                            <td>Data Analysis</td>
                            <td>Shows performance metrics derived from the game data, such as EPP and LPR.</td>
                        </tr>
                        <tr>
                            <td>Table 3</td>
                            <td>Parameters for Winner Declaration</td>
                            <td>Outlines the criteria by which teams are judged, explaining the importance of each parameter.</td>
                        </tr>
                        <tr>
                            <td>Table 4</td>
                            <td>Key Definitions</td>
                            <td>Provides definitions for terms like Coins Earned (CE), Cost of Players (CP), and Token Efficiency (TE).</td>
                        </tr>
                        <tr>
                            <td>Table 5</td>
                            <td>Key Ratio Formulas</td>
                            <td>Lists the formulas for ratios like EPP, LPR, and PPM, helping users understand the metrics.</td>
                        </tr>
                        <tr>
                            <td>Table 6</td>
                            <td>Winning Teams in Each Category</td>
                            <td>Highlights the best-performing team in each key metric, identifying the strongest teams.</td>
                        </tr>
                        <tr>
                            <td>Table 7</td>
                            <td>Weightage of Each Parameter and Final Winner</td>
                            <td>Lists the weightage for each parameter and calculates the final winner based on weighted scores.</td>
                        </tr>
                        <tr>
                            <td>Table 8</td>
                            <td>Final Winner Based on Weighted Scores</td>
                            <td>Declares the overall final winner based on weighted scores from all categories. Final Weighted Score: The total score out of 100 points distributed between teams based on their performance across weighted game parameters, with the highest score determining the overall winner.</td>
                        </tr>
                    </table>

                `,
                2: loadGameData(),
                3: loadAnalysis(),
                4: `
                    <table>
                        <tr><th>Parameter</th><th>Importance</th></tr>
                        <tr><td>Earning to Player Purchase Ratio (EPP)</td><td>Shows how efficiently player purchases turned into earnings.</td></tr>
                        <tr><td>Coins in Hand (CIH)</td><td>Indicates financial standing at the end of the game.</td></tr>
                        <tr><td>Loan Productivity Ratio (LPR)</td><td>Evaluates resource optimization from loan usage.</td></tr>
                        <tr><td>Token Value in Hand (TVIH)</td><td>Indicates how effectively tokens were utilized.</td></tr>
                    </table>

                `,
                5: `
                    <table>
                        <tr><th>Term</th><th>Definition</th></tr>
                        <tr><td>Coins Earned (CE)</td><td>Total coins earned from challenges, wild cards, and trades.</td></tr>
                        <tr><td>Cost of Players (CP)</td><td>Coins spent on players during the auction.</td></tr>
                        <tr><td>Earning to Player Purchase Ratio (EPP)</td><td>The ratio of Coins Earned to Cost of Players (CE/CP).</td></tr>
                        <tr><td>Coins in Hand (CIH)</td><td>Total coins left at the end of the game, including initial balance and earnings.</td></tr>
                        <tr><td>Loan Productivity Ratio (LPR)</td><td>Measures how effectively the loan amount was used to generate earnings (CE/Loan Amount).</td></tr>
                        <tr><td>Token Value in Hand (TVIH)</td><td>Indicates how effectively tokens were utilized.</td></tr>
                    </table>

                `,
                6: `
                    <table>
                        <tr><th>Ratio</th><th>Formula</th></tr>
                        <tr><td>Earning to Player Purchase Ratio (EPP)</td><td>EPP = CE / CP</td></tr>
                        <tr><td>Coins in Hand (CIH)</td><td>CIH = Coins Balance + CE</td></tr>
                        <tr><td>Loan Productivity Ratio (LPR)</td><td>LPR = CE / Loan Amount</td></tr>
                        <tr><td>Token Value in Hand (TVIH)</td><td>Time Tokens + Energy Tokens.</td></tr>
                    </table>

                `,
                7: loadWinnerByCategory(),
                8: loadFinalResults(),
                9: 
                `
                    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Weightage (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Earning to Player Purchase Ratio (EPP)</td>
                <td>30</td>
            </tr>
            <tr>
                <td>Coins in Hand (CIH)</td>
                <td>20</td>
            </tr>
            <tr>
                <td>Loan Productivity Ratio (LPR)</td>
                <td>20</td>
            </tr>
            <tr>
                <td>Player Productivity Ratio (PPR)</td>
                <td>20</td>
            </tr>
            <tr>
                <td>Token Value in Hand (TVIH)</td>
                <td>10</td>
            </tr>
          </tbody>
    </table>
                `
            };
            
            // Load the selected table preset
            tableContainer.innerHTML = tables[preset];
        }
 let winners = {}; // Define winners in a global scope
const teamScores = {};
let sortedTeams = [];

document.addEventListener('DOMContentLoaded', function() {
    // Define the categories with weights
    const categories = [
        { name: 'Earning to Player Purchase Ratio (EPP)', formula: (team) => (team.playerCost ? team.profit / team.playerCost : 0), weight: 30 },
        { name: 'Coins in Hand (CIH)', formula: (team) => (team.coins != null && team.startingCoins != null ? team.coins + team.startingCoins : 0), weight: 20 },
        { name: 'Loan Productivity Ratio (LPR)', formula: (team) => (team.loanAmount ? team.profit / team.loanAmount : 0), weight: 20 },
        { name: 'Player Productivity Ratio (PPR)', formula: (team) => (team.playerCost ? team.profit / team.playerCost : 0), weight: 20 },
        { name: 'Token Value in Hand (TVIH)', formula: (team) => (team.timeTokens != null && team.energyTokens != null ? team.timeTokens + team.energyTokens : 0), weight: 10 }
    ];

    // Calculate and find the winner for each category
    categories.forEach(category => {
        let bestTeam = null;
        let bestValue = -Infinity;

        teams.forEach(team => {
            const value = category.formula(team);

            if (value > bestValue) {
                bestValue = value;
                bestTeam = team.teamName;
            }
        });

        winners[category.name] = bestTeam;
    });

    // Calculate final scores for each team based on the weightage
    teams.forEach(team => {
        let totalScore = 0;

        categories.forEach(category => {
            const value = category.formula(team);
            const weightedScore = (value * category.weight) / 100;
            totalScore += weightedScore;
        });

        teamScores[team.teamName] = totalScore;
    });

    // Sort teams by final score in descending order
    sortedTeams = Object.entries(teamScores).sort((a, b) => b[1] - a[1]);

    // Display the final results
    const resultsContainer = document.getElementById('resultsContainer');
    if (resultsContainer) {
        resultsContainer.innerHTML = loadFinalResults();
    }

    // Display the winner by category
    const winnersContainer = document.getElementById('winnersContainer');
    if (winnersContainer) {
        winnersContainer.innerHTML = loadWinnerByCategory();
    }
});

function loadFinalResults() {
    // Create the results table structure
    let ret = `
        <table>
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>`;

    // Populate the table with sorted results
    sortedTeams.forEach(([teamName, score]) => {
        ret += `
            <tr>
                <td>${teamName}</td>
                <td>${score.toFixed(2)}</td>
            </tr>`;
    });

    ret += '</tbody></table>';
    
    return ret;
}

function loadWinnerByCategory() {
    // Create the table structure
    let ret = `
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Winning Team</th>
                </tr>
            </thead>
            <tbody>`;

    // Populate the table with results
    Object.entries(winners).forEach(([category, team]) => {
        ret += `
            <tr>
                <td>${category}</td>
                <td>${team || 'N/A'}</td> <!-- Handle cases where team might be null or undefined -->
            </tr>`;
    });
    
    ret += '</tbody></table>';

    return ret;
}

        
        
                // Function to load game data from localStorage (for case 2)
        function loadGameData() {
            let ret = "";
            
            if (teams.length === 0) {
                ret = '<p>No data available.</p>';
                return ret;
            }

            // Create table structure
            ret = `
                <table>
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Loan Amount (Coins)</th>
                            <th>Cost of Player (CP)</th>
                            <th>Coins Earned (CE)</th>
                            <th>Coins in Hand (CIH)</th>
                            <th>Energy Tokens in Hand</th>
                            <th>Time Tokens in Hand</th>
                        </tr>
                    </thead>
                    <tbody>`;

            // Dynamically generate rows for each team
            teams.forEach(function(team) {
                ret += `
                    <tr>
                        <td>${team.teamName}</td>
                        <td>${team.startingCoins}</td>
                        <td>${team.playerCost}</td>
                        <td>${team.profit}</td>
                        <td>${team.coins}</td>
                        <td>${team.timeTokens}</td>
                        <td>${team.energyTokens}</td>
                    </tr>`;
            });

            ret += '</tbody></table>';
            
           return ret;
        }
        
        function loadAnalysis() {
            let ret = "";
            
            if (teams.length === 0) {
                ret = '<p>No data available.</p>';
                return ret;
            }

            // Create table structure
            ret = `
                <table>
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Earn to Player Purchase Ration (EPP)</th>
                            <th>Loan Productivity Ration (LPR)</th>
                        </tr>
                    </thead>
                    <tbody>`;

            // Dynamically generate rows for each team
            teams.forEach(function(team) {
                let EPP = (team.profit / team.playerCost).toFixed(2);
                
                let LPR = (team.startingCoins / team.profit).toFixed(2);
                
                console.log(" BEFORE EPP: " + EPP + " LPR " + LPR );
                
                LPR = (isNaN(LPR) || !isFinite(LPR)) ? 0 : LPR;
                EPP = (isNaN(EPP) || !isFinite(EPP)) ? 0 : EPP;
                
                
                console.log("EPP: " + EPP + " LPR " + LPR );
                ret += `
                    <tr>
                        <td>${team.teamName}</td>
                        <td>${EPP}</td>
                        <td>${LPR}</td>
                    </tr>`;
            });

            ret += '</tbody></table>';
            
           return ret;
        }
        
    </script>

</body>
</html>
