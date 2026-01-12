<?php
session_start(); // Start or resume the session

// Access the stored email from session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    echo '<script>var userEmail = "' . $email . '";</script>';
} else {
    echo "<script>alert('Session error, please login to continue!');  window.location.href = 'index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantraz Game Center</title>
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <link rel="stylesheet" href="styles/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
    
          
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            z-index: 10000;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s ease;
          }
    
          .alert-popup {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transform: translateY(-100vh);
            transition: transform 0.5s ease;
          }
    
          .alert-popup-text {
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
          }
    
          .alert-popup-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
          }
    
          .alert-popup-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
          }
    
          .alert-popup-button:hover {
            background-color: #0056b3;
          }
    
          .alert-overlay.active {
            visibility: visible;
            opacity: 1;
          }
    
          .alert-popup.show {
            transform: translateY(0);
          }
    
          .alert-popup.hide {
            transform: translateY(100vh);
          }
      
      
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            background-image: url('images/bg.svg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .header {
            text-align: center;
            font-size: 50px;
            margin: 20px 0;
            color: #CBC493;
            padding-top: .5em;
            padding-bottom: .5em;
        }
        h1{
            color: #CBC493;
        }

        .main-section {
            display: flex;
            justify-content: space-around;
            padding: 20px;
        }

        .teams-section,
        .players-section {
            background-color: transparent;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%;
        }

        h2 {
            color: #fff;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        input[type="number"],
        input[type="text"],
        select {
            width: 50%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #CBC493;
            border-radius: 4px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.5);
        }

        input::placeholder{
            color: #fff;
        }

        button {
            padding: 10px 20px;
            background-color: #3399ff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;
        }

        button:hover {
            background-color: #1a73e8;
        }

        .team-container ul {
            border: 1px solid #ccc;
            padding: 10px;
            list-style-type: none;
            background-color: #f9f9f9;
            width: 50%;
        }

        .team-container li {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }

        .team-container li:last-child {
            border-bottom: none;
        }

        .team-container li.selected {
            background-color: #e0e0e0;
        }

        .delete-button {
            background-color: #dc3545;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        .button-container {
            display: flex;
            justify-content: flex-start;
            gap: 2em;
            margin-bottom: 1em;
        }

        .button-container-start {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1em;
            margin-right: 3em;
        }

        .start-btn {
            margin-left: 3.5em;
        }

        #team-select {
            width: 50%;
        }

        .header {
            text-align: center;
            font-size: 50px;
            margin: 20px 0;
            color: #3399ff;
            padding-top: .5em;
            padding-bottom: .5em;
            position: relative;
        }

        .gear-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            cursor: pointer;
            color: #CBC493;
        }

        .menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 10px;
            z-index: 1000;
            flex-wrap: wrap;
            gap: 10px;
            max-width: 170px;
        }

        .menu a {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            background-color: #3399ff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .menu a:hover {
            background-color: #1a73e8;
        }

        #sign-out-btn {
            background-color: red;
        }
        #other-bt {
            background-color: #3399ff;
        }
        .labels{
            color: #fff;
        }
        .team-container li{
            color: white;
        }
        .ul {
            color: white;
        }
        .slider-container {
            display: none; /* Hidden by default */
            margin-top: 20px;
            text-align: center;
        }
        
        .slider-container label {
            color: #fff;
            margin-bottom: 10px;
            display: block;
        }
        
        .slider-container input[type="range"] {
            width: 80%;
            margin: 10px auto;
        }

        
    </style>
</head>

<body>
    <header>
        <h1 class="header" style="color:#CBC493">Quantraz Game Center</h1>
        <i class="fas fa-cog gear-icon" onclick="toggleMenu()"></i>
        <div class="menu" id="menu">
            <a id="sign-out-btn" onclick="logout()">Sign out</a>
            <a id="other-bt" onclick="adminPanel()">Admin panel</a>
            <a id="other-bt" onclick="clearGames()">Clear games</a>
            <a id="other-bt" onclick="backToMenu()">Main menu</a>
        </div>
    </header>
    
    <!--Popups-->
    
    <div class="alert-overlay" id="alert-overlay-information-def">
        <div class="alert-popup" id="alert-popup-information-def">
            <p class="alert-popup-text" id="alert-popup-text-information-def"></p>
            <div class="alert-popup-buttons">
                <button class="alert-popup-button" id="alert-button1-information-def" onclick="loadPage()">Continue</button>
                <button class="alert-popup-button" id="alert-button2-information-def" onclick="closeAlertPopupInformationDef()">Cancel</button>
            </div>
        </div>
    </div>
    
    <!--- <button id="showAlertPopup">Show Alert Popup</button> -->
    
    
    <section class="main-section">
        <div class="teams-section">
            <label class="labels" for="num-teams">Number of Teams (minimum 2):</label>
            <input type="number" id="num-teams" name="num-teams" min="2">
            <div class="button-container">
                <button onclick="createTeams()">Create Teams</button>
            </div>
            <div id="team-inputs"></div>
        </div>
        <div class="players-section">
            <h2>Add Players</h2>
            <div class="add-player-container">
                <input type="text" id="player-name" placeholder="Enter Player Name">
            </div>
            <select id="team-select">
                <!-- Teams will be dynamically added here -->
            </select>
            <div class="button-container">
                <button onclick="addPlayer()">Add Player</button>
                <button class="delete-button" onclick="deletePlayers()">Delete Selected</button>
            </div>
        </div>
    </section>
    <div class="button-container-start">
        <button id="exists" onclick="loadPage()" class="start-btn">Continue existing game</button>
        <button onclick="startGame()" class="start-btn">Start</button>
    </div>
    <br>
    <br>
    <br>
    <br>
    
    <div class="slider-container" id="slider-container">
        <label for="number-of-items">Questions:</label>
        <input type="range" id="number-of-items" name="number-of-items" min="2" max="50" value="20">
        <div id="slider-value" style="color: white;">20</div>
    </div>


    <script>
    
    
    
        
        // POPPUP FOR INFORMATION DEF
        
        function showAlertPopupInformationDef(information) {
          const info = document.getElementById("alert-popup-text-information-def");
          info.innerHTML = information;
          const overlay = document.getElementById("alert-overlay-information-def");
          const popup = document.getElementById("alert-popup-information-def");
        
          // Add classes to show the overlay and popup with animations
          overlay.classList.add("active");
          popup.classList.add("show");
        
          // Add click listeners to the buttons to close the popup
          document.querySelectorAll("#alert-popup .alert-popup-button-information-def").forEach((button) => {
            button.addEventListener("click", closeAlertPopup);
          });
        }
        
        function closeAlertPopupInformationDef() {
          const overlay = document.getElementById("alert-overlay-information-def");
          const popup = document.getElementById("alert-popup-information-def");
        
          // Add class to hide the popup with animation
          popup.classList.add("hide");
        
          // After the hide animation ends, remove the classes and reset the state
          popup.addEventListener(
            "transitionend",
            () => {
              overlay.classList.remove("active");
              popup.classList.remove("show", "hide");
            },
            { once: true }
          );
          
        }
         // END REGION POPPUP FOR INFORMATION DEF
        
        

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



        var colors = [
            'rgba(255, 99, 71, 0.3)',    // Red
            'rgba(0, 128, 0, 0.3)',      // Green
            'rgba(0, 0, 255, 0.3)',      // Blue
            'rgba(255, 255, 0, 0.3)',    // Yellow
            'rgba(128, 0, 128, 0.3)',    // Purple
            'rgba(255, 165, 0, 0.3)',    // Orange
            'rgba(128, 0, 0, 0.3)'       // Bordo (Maroon)
            // Add more colors as needed
        ];
        

        function backToMenu(){
            window.location.href = 'menu.php'; 
        }

        function logout(){
            window.location.href = 'logout.php'; 
        }    
        
        function adminPanel(){
              window.location.href = 'adminPanel.php'; 
        }
            
        // Function to check for existing game on page load
        function checkForGame() {
            var existingGame = localStorage.getItem(userEmail + "teams" + type);
            if (existingGame) {
                showAlertPopupInformationDef("We found an existing game. Do you want to continue?");
            }
            else{
                document.getElementById("exists").style.display = 'none';
            }
        }

        // Run checkForGame() when the page loads
        window.onload = function() {
            checkForGame();
        };
    
    
        function loadPage(){
            // Check if localStorage for teams exists
            var teamsJSON = localStorage.getItem(userEmail + "teams" + type);
            
            // Parse teams data
            var teams = JSON.parse(teamsJSON);
                // Check coins and tokens for teams[0]
           
            var firstTeam = teams[0];
            let url = "menu.php";
            if ('coins' in firstTeam && 'timeTokens' in firstTeam && 'energyTokens' in firstTeam) {
                // If first team has coins and both tokens, redirect to wheel.php or handle as needed
                if(type === ''){
                   url = 'wheel.php';
                }   
                else if(type === 'standard') {
                    url = 'standard_game.php';
                }
                   
            } 
            else if('coins' in firstTeam){
                if(type === 'standard'){
                    url = "exchange.php?type=standard";
                }
                else if (type === ''){
                    url = "exchange.php?type=";
                }
                // Redirect to the constructed URL
                //window.location.href = `exchange.php?type=${encodeURIComponent(type)}`;
            }
            else {
                if(type === 'standard'){
                    url = "bank.php?type=standard";
                }
                else if (type === ''){
                    url = "bank.php?type=";
                }
                // If first team does not have coins or tokens, redirect to exchange.php or handle as needed
                //window.location.href = 'bank.php?type=${encodeURIComponent(type)}';
            }
            
            window.location.href = url;
        }



        if (type === 'standard') {
            document.getElementById('slider-container').style.display = 'block';
        } else {
            document.getElementById('slider-container').style.display = 'none';
        }
        
        // Update slider value display
        const slider = document.getElementById('number-of-items');
        const sliderValue = document.getElementById('slider-value');
        
        slider.addEventListener('input', function () {
            sliderValue.textContent = slider.value;
        });        
        
        
        function toggleMenu() {
            var menu = document.getElementById("menu");
            if (menu.style.display === "flex") {
                menu.style.display = "none";
            } else {
                menu.style.display = "flex";
            }
        }
        
        // Function to create teams dynamically
        function createTeams() {
            var existingGame = localStorage.getItem(userEmail + "teams" + type);
            if (existingGame) {
                var check = confirm("Proceeding will delete the existing game files. Are you sure?");
                if (!check) {
                    return;
                }
            }
            
            document.getElementById("exists").style.display = 'none';
            localStorage.removeItem(userEmail + "teams" + type);
            document.getElementById("team-select").innerHTML = '';
            var numTeams = document.getElementById("num-teams").value;
            var numTeams = Math.max(2, numTeams);
            var teamInputs = document.getElementById("team-inputs");
            teamInputs.innerHTML = ""; // Clear previous inputs

            for (var i = 0; i < numTeams; i++) {
                var teamName = prompt("What will this team's name be?");
                if (teamName !== null) {
                    var teamContainer = document.createElement("div");
                    teamContainer.className = "team-container";

                    var heading = document.createElement("h2");
                    if (teamName === "") {
                        teamName = 'blank';
                        // TODO ADD RANDOM TEAM NAMES IN HERE
                    }
                    
                    teamName = teamName.trim();
                    
                    heading.textContent = teamName;

                    var ul = document.createElement("ul");
                    ul.id = "team-" + (i + 1);
                    ul.className = "team-list";
                    ul.style.backgroundColor = colors[i % colors.length]; // Assigning color from the array

                    teamContainer.appendChild(heading);
                    teamContainer.appendChild(ul);

                    teamInputs.appendChild(teamContainer);

                    // Add team as option in dropdown
                    var option = document.createElement("option");
                    option.value = teamName;
                    option.textContent = teamName;
                    document.getElementById("team-select").appendChild(option);
                }
            }
        }
        
        function clearGames(){
            localStorage.removeItem(userEmail + "teams" + type);
            localStorage.removeItem(userEmail + "order" + type);
            localStorage.removeItem(userEmail + "numbers" + type);
            document.getElementById("exists").style.display = 'none';
            toggleMenu();
            //showAlertPopupInformationDef("Game data cleared sucessfully!");
            alert("Game data cleared sucessfully!");
            //TODO ADD POPUP FOR THIS AS WELL
        }

        // Function to add player to selected team
        function addPlayer() {
            var playerName = document.getElementById("player-name").value;
            if (playerName === "") {
                return;
            }
            if (/\s/.test(playerName)) {
                //showAlertPopupInformationDef("Player name cannot contain whitespace!");
                alert("Player name cannot contain whitespace!");
                return;
            }

            var teamSelect = document.getElementById("team-select");
            var selectedTeam = teamSelect.options[teamSelect.selectedIndex].text;

            var teamLists = document.querySelectorAll(".team-container ul");
            for (var i = 0; i < teamLists.length; i++) {
                if (teamLists[i].previousElementSibling.textContent === selectedTeam) {
                    var listItem = document.createElement("li");
                    listItem.textContent = playerName;
                    listItem.onclick = function() {
                        this.classList.toggle("selected");
                    };

                    teamLists[i].appendChild(listItem);
                    break;
                }
            }
            // Clear player name input
            document.getElementById("player-name").value = "";
        }

        // Function to start the game
        function startGame() {
            var teams = [];
            var teamContainers = document.querySelectorAll(".team-container");
            teamContainers.forEach(function(teamContainer) {
                var teamName = teamContainer.querySelector("h2").textContent;
                var players = [];
                var playerListItems = teamContainer.querySelectorAll("li");
                var teamColor = teamContainer.querySelector("ul").style.backgroundColor;
                playerListItems.forEach(function(playerListItem) {
                    players.push(playerListItem.textContent);
                });
                teams.push({ teamName: teamName, blocked: false ,players: players, teamColor: teamColor });
            });

            // Convert teams array to JSON string
            var teamsJSON = JSON.stringify(teams);

            // Store teamsJSON in localStorage
            localStorage.setItem(userEmail + "teams" + type, teamsJSON);

            createOrder();
            // Redirect to wheel.php
            order += 0;
            localStorage.setItem(userEmail + "order" + type, order);
            let numbers = [];
            
            if(type === ''){
                for (let i = 1; i <= 38; i++) {
                    numbers.push(i);
                }    
            }
            else if (type === 'standard'){
                const slider = document.getElementById('number-of-items');
                const numberNumbers = parseInt(slider.value, 10);      
                for (let i = 1; i <= numberNumbers; i++) {
                    numbers.push(i);
                }
            }
            
            localStorage.setItem(userEmail + "numbers" + type, numbers);
            
            
            if(type === 'standard'){
                window.location.href = "bank.php?type=standard";
            }
            else if(type === ''){
                window.location.href = "bank.php?type=";
            }
            else{
                window.location.href = "menu.php";
            }
            
            
        }

        // Function to delete selected players
        function deletePlayers() {
            var selectedPlayers = document.querySelectorAll(".selected");
            selectedPlayers.forEach(function(player) {
                player.remove();
            });
        }
        
      var teams = [];
var teamPlayers = [];
var order = "";

function createOrder() {
    var teamContainers = document.querySelectorAll(".team-container");

    // Step 1: Form an array of all team names and their players
    teamContainers.forEach(function(teamContainer) {
        var teamName = teamContainer.querySelector("h2").textContent;
        var players = [];
        players.push({ id: 1 }); // Initial index tracker
        var playerListItems = teamContainer.querySelectorAll("li");
        playerListItems.forEach(function(playerListItem) {
            players.push(playerListItem.textContent);
        });
        teams.push(teamName);
        teamPlayers.push({ teamName: teamName, players: players });
    });

    // Step 2: Form the order of play while respecting the rules
    var numTeams = teams.length;

    // Shuffle teams and players within each team
    shuffleArray(teams);
    
    teamPlayers.forEach(function(teamPlayer) {
            shuffleArrayPlayers(teamPlayer.players);
    });

    // Generate order while keeping players in their correct teams
    while (checkEnd()) {
        for (let i = 0; i < numTeams; i++) {
            var currentIndex = teamPlayers[i].players[0].id;

            if (currentIndex != -1) {
                var data = "";
                data += teamPlayers[i].players[currentIndex] + " " + teamPlayers[i].teamName;
                order += (data + ", ");
                teamPlayers[i].players[0].id += 1;

                // If the player index exceeds the number of players, mark the team as finished
                if (teamPlayers[i].players[0].id > teamPlayers[i].players.length - 1) {
                    teamPlayers[i].players[0].id = -1;
                }
            }
        }
    }
    
    console.log(order);
}

function checkEnd() {
    for (let i = 0; i < teamPlayers.length; i++) {
        if (teamPlayers[i].players[0].id != -1) return true;
    }
    return false;
}

// Function to shuffle an array using Fisher-Yates algorithm
function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
}

// Function to shuffle an array using Fisher-Yates algorithm, excluding index 0
function shuffleArrayPlayers(array) {
    for (var i = array.length - 1; i > 1; i--) { // Start at 1 to leave index 0 intact
        var j = Math.floor(Math.random() * (i - 1)) + 1; // Ensure j is within range [1, i]
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
}



    </script>
</body>
</html>
