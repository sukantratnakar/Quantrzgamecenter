<?php
session_start(); // Start or resume the session

// Access the stored email from session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    echo '<script>var userEmail = "' . $email . '";</script>';
} else {
    echo "<script> window.location.href = 'index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/wheel.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Classic Game</title>
    
    <style>
        .action-buttons-hidden{
            align-items:center;
            flex-direction:column;
        }
        .action-button{
            width:100%;
        }
        
        
        @media (max-width: 1468px) {
        .left-panel {
            margin-left: 5%; 
            
        }
    
        .right-panel {
            margin-right: 5%; 
           
            }
        }
        .game-over-popup {
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70%;
        max-width: 500px; /* Optional: to limit the maximum width */
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        text-align: center;
        padding: 20px;
        z-index: 1000;
        border-radius: 8px;
    }
    
    </style>

    
</head>
<body>
    
   <!--Popups-->
    
    <div class="alert-overlay" id="alert-overlay">
        <div class="alert-popup" id="alert-popup">
            <p class="alert-popup-text">Round is over, do you want to conitnue to play?</p>
            <div class="alert-popup-buttons">
                <button class="alert-popup-button" id="alert-button1" onclick="closeAlertPopupGameContinues()">Continue</button>
                <button class="alert-popup-button" id="alert-button2" onclick="endGame()">End Game</button>
            </div>
        </div>
    </div>
    
     <div class="alert-overlay" id="alert-overlay-information">
        <div class="alert-popup" id="alert-popup-information">
            <p class="alert-popup-text" id="alert-popup-text-information"></p>
            <div class="alert-popup-buttons">
                <button class="alert-popup-button" id="alert-button1-information" onclick="closeAlertPopupInformationGameEnded()">Continue</button>
            </div>
        </div>
    </div>
    
    <div class="alert-overlay" id="alert-overlay-information-def">
        <div class="alert-popup" id="alert-popup-information-def">
            <p class="alert-popup-text" id="alert-popup-text-information-def"></p>
            <div class="alert-popup-buttons">
                <button class="alert-popup-button" id="alert-button1-information-def" onclick="closeAlertPopupInformationDef()">Continue</button>
            </div>
        </div>
    </div>
    <!--- <button id="showAlertPopup">Show Alert Popup</button> -->
    
    <div class="left-panel">
        <h3>Teams Information</h3>
        <div id="teams-info"></div>
    </div>

    <div class="wheel-container">
        <header><h1>Classic Game</h1></header>
        
        <div class="info">
            <button class="left-button" onclick="trade()">Trade</button>
        </div>
        
        <div class="wheel-container">
            <canvas id="wheel" class="wheel"></canvas>
        </div>
        <button class="spin-button" id="spinButton" onclick="spinWheel()">Spin</button>
        <button class="spin-button" style="background-color: red; display: none;" id="spinSkipButton" onclick="skipWheel()">Skip Turn</button>
        
        <div id="popup" class="popup">
            <div class="popup-content">
                <h2 id="current-player-popup"></h2>
                <div class="popup-buttons">
                    <h2 id="winning-card-number" style="margin-top: -10%;"></h2>
                    <button class="popup-button" onclick="playChallenge()">Play ( -1 Energy Token, -1 Time Token)</button>
                    <button class="popup-button" onclick="skipChallengeEnergy()">Skip ( -1 Time Token)</button>
                </div>
                <div class="action-buttons-hidden">
                    <p>Is the answer correct?</p>
                    <button class="action-button" onclick="Answer(true)">Yes</button>
                    <button class="action-button" onclick="Answer(false)">No</button>
                    <button class="action-button" id="cuddleButton" onclick="cuddle()">HUDDLE</button>
                    <button class="action-button" id="extraTimeButton" onclick="extraTime()">EXTRA TIME</button>
                    <button class="popup-button" onclick="skipChallengeToken()">Skip ( +1 Energy Token)</button>
                </div>
                <div class ="wild-buttons">
                    <button class="popup-button" onclick="playWildCard()">Play Wild Card</button>
                    <button class="popup-button" onclick="skipChallenge()">Skip Wild Card</button>
                </div>
                <div class="slider-input">
                    <p id="winLabel1">How would you rate this answer: </p><p id="winLabel">2</p>
                    <input type="range" min='0' max='4' id="reward-slider">
                    <button class="popup-button" id="wildAnswerButton1" onclick="extraTime()">Extra Time</button>
                    
                    <button class="doneWildButton" id="wildRewardButton" onclick="wildReward()">Done</button>
                </div>
                <div class="before-wc">
                    <button class="popup-button" onclick="proceedToWildCard()" style="width: 100%;">Proceed to wild card</button>
                </div>
            </div>
        </div>


        <div id="popupTrade" class="popupTrade">
            <div class="top">
                <div class="topUp">
                    <span>TRADE</span>
                    <img src="images/x.png" alt="Close" onclick="closeTrade()" class="close-icon">
                </div>
                <div class="topBottom">
                    <span>trade:</span>
                    <select>
                    </select>
                    <select>
                    </select>
                </div>
            </div>




            <div class="middle">
                <div class="section" id="sectionBuyer"></div>
                
                <div class="section" id="sectionBuyerArea">
                    <textarea disabled id="textarea1" style="width: 100%; height: 100%; resize: none;"></textarea>
                </div>
            
                <div class="arrows">
                    <img src="images/leftArr.png" alt="arrowLeft" class="arrow-icon">
                    <img src="images/rightArr.png" alt="arrowRight" class="arrow-icon">
                </div>
            
                <div class="section" id="sectionSellerArea">
                    <textarea disabled id="textarea2" style="width: 100%; height: 100%; resize: none;"></textarea>
                </div>
                <div class="section" id="sectionSeller"></div>
            </div>
            
            

            <div class="bottom">
                <span id="premiumText">You are paying x premium on this trade</span>
                <button class="confirm-button" onclick="finish_trade()">CONFIRM</button>
            </div>

        </div>
        
    
        <div class="progress-bar-container hidden" id="progress-bar-container">
            <div class="progress-bar" id="progress-bar"></div>
        </div>
    </div>

    <div class="right-panel">
        
        <i class="fas fa-cog gear-icon"  onclick="toggleMenu()"></i>
        <div class="menuGear" id="gear_menu">
            <a id="gear_sign-out-btn" onclick="logout()">Sign Out</a>
            <a onclick="endGame()">End Game</a>
        </div>
        
        <br>
        <div id="player_info" style="text-align: left;">
            <!-- Dynamic content will be inserted here -->
        </div>
        
        
        <br>
        <br>
        <br>
        <p> <b> Player order </b> </p>
        
        <table id="upcoming-players">
            <thead>
                <tr style="text-align: left;">
                    <th style="text-align: left; font-weight: normal;">SN</th>
                    <th style="text-align: left; font-weight: normal;">Player</th>
                    <th style="text-align: left; font-weight: normal;">Team</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be inserted here dynamically -->
            </tbody>
        </table>
        
       
        
        <button class="exit-button" onclick="exitGame()">Exit</button>
    

    </div>
    
   
    
    <script>
        const wheelCanvas = document.getElementById('wheel');
        const wheelContext = wheelCanvas.getContext('2d');
        const wheelSize = Math.min(window.innerWidth, window.innerHeight) * 0.6;
        wheelCanvas.width = wheelSize;
        wheelCanvas.height = wheelSize;
        let numbers = localStorage.getItem(userEmail + 'numbersstandard').split(','); //   ['0', '00', ...Array.from({ length: 36 }, (_, i) => (i + 1).toString())];
        const segmentAngle = 360 / numbers.length;
        let currentAngle = 0;
        let team;
        let winningNumber;
        let cards =[];
        let canSkip = true;
        let round = localStorage.getItem('round') ? parseInt(localStorage.getItem('round')) : 1;


        // Add event listener to the button to show the alert popup
        // document.getElementById("showAlertPopup").addEventListener("click", showAlertPopup);
        
        
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
            button.addEventListener("click", closeAlertPopupInformationDef);
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
        
        
        
        
        // POPPUP FOR GAME END - NO MORE CARDS
        function showAlertPopupInformation(information) {
          const info = document.getElementById("alert-popup-text-information");
          info.innerHTML = information;
          const overlay = document.getElementById("alert-overlay-information");
          const popup = document.getElementById("alert-popup-information");
        
          // Add classes to show the overlay and popup with animations
          overlay.classList.add("active");
          popup.classList.add("show");
        
          // Add click listeners to the buttons to close the popup
          document.querySelectorAll("#alert-popup .alert-popup-button-information").forEach((button) => {
            button.addEventListener("click", closeAlertPopupInformationGameEnded);
          });
        }
        
        function closeAlertPopupInformationGameEnded() {
          const overlay = document.getElementById("alert-overlay-information");
          const popup = document.getElementById("alert-popup-information");
        
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
          
             window.location.href = "table.php?type=standard";
        }
        // END REGION - POPPUP FOR GAME END - NO MORE CARDS
        
        // POPUP FOR ROUND END
        function showAlertPopup() {
          const overlay = document.getElementById("alert-overlay");
          const popup = document.getElementById("alert-popup");
        
          // Add classes to show the overlay and popup with animations
          overlay.classList.add("active");
          popup.classList.add("show");
        
          // Add click listeners to the buttons to close the popup
          document.querySelectorAll("#alert-popup .alert-popup-button").forEach((button) => {
            button.addEventListener("click", closeAlertPopupAlways);
          });
        }

        function closeAlertPopupGameContinues() {
          round += 1;
          localStorage.setItem('round', round);
          //displayCurrentPlayer();
          
          const overlay = document.getElementById("alert-overlay");
          const popup = document.getElementById("alert-popup");
        
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
        
        function closeAlertPopupAlways() {
          const overlay = document.getElementById("alert-overlay");
          const popup = document.getElementById("alert-popup");
        
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
        
        
        // END REGION POPAP FOR ROUND END\
        
        function exitGame() {
            window.location.href = "menu.php"; // Replace with the actual exit page URL
        }
        
        function logout(){
            window.location.href = 'logout.php';
        }   
        
        function endGame(){
            window.location.href = 'table.php?type=standard';
        }
        
        
        function toggleMenu() {
            var menu = document.getElementById("gear_menu");
            if (menu.style.display === "flex") {
                menu.style.display = "none";
            } else {
                menu.style.display = "flex";
            }
        }
        
        
        function drawWheel() {
            wheelContext.clearRect(0, 0, wheelSize, wheelSize);
            //FIX popup should come up after the last question is answered, not as soon as it comes up 
            if(numbers.length < 1){
                showAlertPopupInformation("Game over! No more cards. Proceeding to results!");
                //alert("Game is over! Proceeding to results!");
                //window.location.href = "table.php?type=standard";
            }
            
            //console.log("Drawing wheel with: " + numbers);
            numbers.forEach((number, i) => {
                const startAngle = (i * (360 / numbers.length)) * Math.PI / 180;
                const endAngle = ((i + 1) * (360 / numbers.length)) * Math.PI / 180;
                wheelContext.beginPath();
                wheelContext.moveTo(wheelSize / 2, wheelSize / 2);
                wheelContext.arc(wheelSize / 2, wheelSize / 2, wheelSize / 2, startAngle, endAngle);
                wheelContext.closePath();

                if (number === '37' || number === '38') {
                    wheelContext.fillStyle = '#D7D98A';
                } else {
                    wheelContext.fillStyle = i % 2 === 0 ? '#ff0000' : '#000000';
                }

                wheelContext.fill();
                wheelContext.strokeStyle = '#ffffff';
                wheelContext.lineWidth = 2;
                wheelContext.stroke();

                wheelContext.save();
                wheelContext.translate(wheelSize / 2, wheelSize / 2);
                wheelContext.rotate((startAngle + endAngle) / 2);
                wheelContext.textAlign = 'right';
                wheelContext.fillStyle = '#ffffff';
                wheelContext.font = 'bold 16px Arial';
                wheelContext.fillText(number, wheelSize / 2 - 10, 5);
                wheelContext.restore();
            });
        }



        const teamsJSON = localStorage.getItem(userEmail + 'teamsstandard');

        const teams = JSON.parse(teamsJSON);
              
        const order = localStorage.getItem(userEmail + 'orderstandard');
        let playOrder = order.split(", ");
        
        let currentPlayerIndex = parseInt(playOrder[playOrder.length - 1]);
        let currentPlayer;


        let first = true;
        function displayCurrentPlayer() {
            displayTeamInfo();
            if(currentPlayerIndex == playOrder.length - 1){  // because last item in playerOrder array is the index of the player who plays next
                currentPlayerIndex = 0;
                
                //var continueGame = confirm("Round is over. Do you want to continue?");
                
                showAlertPopup();
                
                //if (!continueGame) 
                //     window.location.href = "table.php?type=standard";
                //else{
                //    round+=1;                
                //}
            }
            
            currentPlayer = playOrder[currentPlayerIndex];
            
            //const text = document.getElementById('current-player').innerText = `Current player: ${currentPlayer}`;
            //text.style.display = 'none';
            
            
            
            
            let data = currentPlayer.split(" ");
            let teamNameName = '';
            for(let i = 1; i<data.length; i++){
                teamNameName += data[i] + " ";
            }
            teamNameName = teamNameName.substring(0, teamNameName.length - 1);
            team = teams.find(team => team.teamName === teamNameName);
            
            
            //console.log("Getting data");
            //console.log("Data" + data);
            
            
            //console.log("Team: " + team);
            
            const playerDATA = document.getElementById('player_info');
    
           playerDATA.innerHTML = `
                <h2><b>Status </b></h3>
                <h3>Round:  ${round}</h3>
                <h3>Player:   ${data[0]}</h3>
                <h3>Team:   ${teamNameName}</h3>
            `;
            
            
            checkBlocked();
            changeSpin();
            
            playOrder[playOrder.length - 1] = currentPlayerIndex;
            saveOrder();
            
            currentPlayerIndex += 1;
            
        }
        
        function saveOrder(){
            var SaveOrder = "";
            for(var i=0; i < playOrder.length - 1; i++){
                SaveOrder += playOrder[i] + ", ";
            }
            console.log("order: " + SaveOrder);
            SaveOrder += playOrder[playOrder.length - 1];
            localStorage.removeItem(userEmail + "orderstandard");
            localStorage.setItem(userEmail + "orderstandard", SaveOrder);
        }
        
        function skipWheel(){
            displayCurrentPlayer();
        }

        function displayUpcomingPlayers() {
            const upcomingPlayersTable = document.getElementById('upcoming-players').getElementsByTagName('tbody')[0];
            let upcomingPlayers = [];
            console.log(playOrder);
            try {
                 for (let i = 0; i < playOrder.length - 1; i++) {
                    const player = playOrder[i];
                    if (typeof player !== 'string') {
                        console.error('Unexpected player type:', typeof player);
                        continue; // Skip invalid types or handle as needed
                    }
                    const parts = player.split(' ');
                    if (parts.length < 2) {
                        console.error('Invalid player format:', player);
                        continue; // Skip invalid format or handle as needed
                    }
                    console.log(parts);
                    upcomingPlayers.push(`
                        <tr>
                            <td style="text-align: left;">${i+1}</td>
                            <td style="text-align: left;">${parts[0]}</td>
                            <td style="text-align: left;">${parts.slice(1).join(' ')}</td>
                        </tr>`
                    );
                }
                
                upcomingPlayersTable.innerHTML = upcomingPlayers.join('');;
            
                
            } catch(error) {
                console.error('Error in displayUpcomingPlayers:', error);
            }
            
        }
        
        function skipChallengeToken(){
            team.energyTokens += 1;
            displayTeamInfo();
            closePopup();
        }
            
        function updateTextArea(buyer, input, type) {
                let value = parseInt(input.value) || 0;
                let text = "";
                switch (type) {
                    case 'coins':
                    text = "Coins: ";
                    if(value>input.max){
                        value = input.max;
                        input.value = value;
                    }
                    break;
                case 'energy':
                    text = "Energy Tokens: ";
                    if(value>input.max){
                        value = input.max;
                        input.value = value;
                    }
                    break;
                case 'time':
                    text = "Time Tokens: ";
                    if(value>input.max){
                        value = input.max;
                        input.value = value;
                    }
                    break;
                }

            let element;
            let textarea;
            if (buyer) {
                element = document.getElementById('textarea2');
                textarea = textarea2; // Storing the element for later use
            } else {
                element = document.getElementById('textarea1');
                textarea = textarea1; // Storing the element for later use
            }

            let elementText = element.textContent;
            let newValueText = `${text}${value}`;

            // Check if the text already exists in the textarea content
            if (elementText.includes(text)) {
                // Replace existing value with new value
                let regex = new RegExp(`${text}\\s*\\d+`, 'gi'); // Regular expression to find the text and value
                elementText = elementText.replace(regex, newValueText);
            } else {
                // Append new value
                elementText += `\n${newValueText}`;
            }

            // Update the textarea with modified text
            element.textContent = elementText;

            calculatePremium();

        }

        function calculatePremium() {
            const textarea1Value = document.getElementById('textarea1').textContent.trim();
            const textarea2Value = document.getElementById('textarea2').textContent.trim();

            // Extract values from textarea1
            let energyTokens1 = 0, timeTokens1 = 0, coins1 = 0;
            const matches1 = textarea1Value.match(/(?:Energy Tokens: (\d+))|(?:Time Tokens: (\d+))|(?:Coins: (\d+))/g);
            if (matches1) {
                matches1.forEach(match => {
                    if (match.includes('Energy Tokens')) {
                        energyTokens1 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Time Tokens')) {
                        timeTokens1 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Coins')) {
                        coins1 = parseInt(match.split(':')[1].trim()) || 0;
                    }
                });
            }

            // Extract values from textarea2
            let energyTokens2 = 0, timeTokens2 = 0, coins2 = 0;
            const matches2 = textarea2Value.match(/(?:Energy Tokens: (\d+))|(?:Time Tokens: (\d+))|(?:Coins: (\d+))/g);
            if (matches2) {
                matches2.forEach(match => {
                    if (match.includes('Energy Tokens')) {
                        energyTokens2 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Time Tokens')) {
                        timeTokens2 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Coins')) {
                        coins2 = parseInt(match.split(':')[1].trim()) || 0;
                    }
                });
            }

            // Calculate total values
            const value1 = energyTokens1 * 2 + timeTokens1 * 2 + coins1;
            const value2 = energyTokens2 * 2 + timeTokens2 * 2 + coins2;

            const val1 = coins2;
            const val2 = 2*(energyTokens1 + timeTokens1);
            
            // Determine premium
            // let premium = value2 - value1;
            let premium = Math.min(((val1 * 100)/val2) - 100, 100);
            
            let premiumText = "";

            if (premium > 0) {
                premiumText = `You are paying ${premium.toFixed(2)}% premium on this trade`;
            } else if (premium < 0) {
                premiumText = `You are getting ${Math.abs(premium.toFixed(2))}% discount on this trade`;
            } else {
                premiumText = `You are not paying any premium on this trade`;
            }

            // Update premiumText span
            const premiumSpan = document.getElementById('premiumText');
            premiumSpan.textContent = premiumText;
        }

        function finish_trade(){
            const textarea1Value = document.getElementById('textarea1').textContent.trim();
            const textarea2Value = document.getElementById('textarea2').textContent.trim();

            let energyTokens1 = 0, timeTokens1 = 0, coins1 = 0;
            const matches1 = textarea1Value.match(/(?:Energy Tokens: (\d+))|(?:Time Tokens: (\d+))|(?:Coins: (\d+))/g);
            if (matches1) {
                matches1.forEach(match => {
                    if (match.includes('Energy Tokens')) {
                        energyTokens1 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Time Tokens')) {
                        timeTokens1 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Coins')) {
                        coins1 = parseInt(match.split(':')[1].trim()) || 0;
                    }
                });
            }

            // Extract values from textarea2
            let energyTokens2 = 0, timeTokens2 = 0, coins2 = 0;
            const matches2 = textarea2Value.match(/(?:Energy Tokens: (\d+))|(?:Time Tokens: (\d+))|(?:Coins: (\d+))/g);
            if (matches2) {
                matches2.forEach(match => {
                    if (match.includes('Energy Tokens')) {
                        energyTokens2 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Time Tokens')) {
                        timeTokens2 = parseInt(match.split(':')[1].trim()) || 0;
                    } else if (match.includes('Coins')) {
                        coins2 = parseInt(match.split(':')[1].trim()) || 0;
                    }
                });
            }

            const dropdown1 = document.querySelector('.topBottom select:nth-child(2)');
            const dropdown2 = document.querySelector('.topBottom select:nth-child(3)');

            const team11 = dropdown1.value;
            const team22 = dropdown2.value;

            const team1 = teams.find(team => team.teamName === team11);
            const team2 = teams.find(team => team.teamName === team22);

            // Update team1 and team2 data
            if (team1 && team2) {
                team1.energyTokens += energyTokens1;
                team1.timeTokens += timeTokens1;
                team1.coins += coins1;

                team1.energyTokens -= energyTokens2;
                team1.timeTokens -= timeTokens2;
                team1.coins -= coins2;

                // team 2 info

                team2.energyTokens += energyTokens2;
                team2.timeTokens += timeTokens2;
                team2.coins += coins2;

                team2.energyTokens -= energyTokens1;
                team2.timeTokens -= timeTokens1;
                team2.coins -= coins1;


                // Optionally, you might want to update the UI or perform other actions here
                console.log("Trade completed successfully. Teams updated:", team1, team2);
                displayTeamInfo();
                closeTrade();
                const textarea1Value = document.getElementById('textarea1').textContent = "";
                const textarea2Value = document.getElementById('textarea2').textContent = "";
                document.getElementById('premiumText').innerHTML = "You are paying x premium on this trade";

            } else {
                console.error("Team not found in teams array.");
            }
            
            
            if(team1.energyTokens > 0 && team1.timeTokens > 0){
                team1.blocked = false;
            }
            else{
                team1.blocked = true;
            }
            
            if(team2.energyTokens > 0 && team2.timeTokens > 0){
                team2.blocked = false;
            }
            else{
                team2.blocked = true;
            }
            changeSpin();
        }

        function proceedToWildCard(){
            switchPopupWindow(4);
            //setWildCard(true);
        }
        
        function changeSpin() {
            if(team.blocked){
                document.getElementById('spinSkipButton').style.display = 'block';
                document.getElementById('spinButton').style.display = 'none';
            }
            else {
                document.getElementById('spinSkipButton').style.display = 'none';
                document.getElementById('spinButton').style.display = 'block';
            }
        }

        // new
        document.addEventListener('DOMContentLoaded', function() {
        const teamNames = teams.map(team => team.teamName);

        const dropdown1 = document.querySelector('.topBottom select:nth-child(2)');
        const dropdown2 = document.querySelector('.topBottom select:nth-child(3)');

        function populateDropdown(dropdown, teamNames, selectedTeam = null) {
            dropdown.innerHTML = '';
            teamNames.forEach(team => {
                if (team !== selectedTeam) {
                    const option = document.createElement('option');
                    option.value = team;
                    option.textContent = team;
                    dropdown.appendChild(option);
                }
            });
        }

        

        function updateTeamInfo(teamName, section, buyer) {
            const team = teams.find(team => team.teamName === teamName);
            if (team) {
                if (buyer) {
                    section.innerHTML = `
                        <h3>Buyer: ${team.teamName}</h3>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <p>Coins: ${team.coins}</p>
                            <input type="number" min="0" value="0" max="${team.coins}" onchange="updateTextArea(true, this, 'coins')">
                        </div>
                    `;
                    
                    document.getElementById("sectionBuyer").style.backgroundColor = team.teamColor;
                    document.getElementById("sectionSellerArea").style.backgroundColor = team.teamColor;

                } else {
                    section.innerHTML = `
                        <h3>Seller: ${team.teamName}</h3>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <p>Time Tokens: ${team.timeTokens}</p>
                            <input type="number" min="0" value="0" max="${team.timeTokens}" onchange="updateTextArea(false, this, 'time')">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <p>Energy Tokens: ${team.energyTokens}</p>
                            <input type="number" min="0" value="0" max="${team.energyTokens}" onchange="updateTextArea(false, this, 'energy')">
                        </div>
                    `;
                    
                    document.getElementById("sectionSeller").style.backgroundColor = team.teamColor;
                    document.getElementById("sectionBuyerArea").style.backgroundColor = team.teamColor;
                    

                }
            } else {
                section.innerHTML = '';
            }
        }

        

        function updateDropdowns() {

            const textarea1Value = document.getElementById('textarea1').textContent = "";
            const textarea2Value = document.getElementById('textarea2').textContent = "";
            document.getElementById('premiumText').innerHTML = "You are paying x premium on this trade";


            const selectedTeam1 = dropdown1.value;
            const selectedTeam2 = dropdown2.value;

            dropdown1.value = selectedTeam1;
            dropdown2.value = selectedTeam2;

            const section1 = document.querySelector('.middle .section:nth-child(1)');
            const section4 = document.querySelector('.middle .section:nth-child(5)');

            updateTeamInfo(selectedTeam1, section1, true);
            updateTeamInfo(selectedTeam2, section4, false);
        }

        // Initial population of dropdowns
        populateDropdown(dropdown1, teamNames);
        populateDropdown(dropdown2, teamNames);

        dropdown1.addEventListener('change', updateDropdowns);
        dropdown2.addEventListener('change', updateDropdowns);

        // Initial load of team info
        updateDropdowns();

    });
    
    
        let winningSegment;
        function spinWheel() {
            if (numbers.length === 0) {
                window.location.href = "table.php";
                return;
            }
            if(!canSpin){
                return;
            }
            canSpin = false;
            const spinAngle = Math.floor(Math.random() * 360) + 3600;
            currentAngle += spinAngle;
            wheelCanvas.style.transform = `rotate(${currentAngle}deg)`;

            winningSegment = Math.floor((currentAngle % 360) / (360 / numbers.length));
            winningNumber = numbers[winningSegment];
            document.getElementById('winning-card-number').innerText = "Winning number: " + winningNumber;
            
            setTimeout(() => {
                //document.getElementById('winning-number').innerText = `The winning number is: ${winningNumber}`;
                
                
                if(numbers.length === 0){
                    document.getElementById('spinButton').innerHTML = "End game";
                }
                
                if (winningNumber == '0'){
                    winningNumber = 37;
                }
                else if (winningNumber == '00'){
                    winningNumber = 38;
                }
                
                
                showPopup(winningNumber);

            
            }, 4000);


        }
        
        
       async function loadCosts() {
            try {
                const response = await fetch('data.json'); // Fetch JSON data from data.json
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json(); // Parse JSON response
                const cards = data.map(item => ({
                    energyCost: parseInt(item.energyCost, 10),
                    timeCost: parseInt(item.timeCost, 10),
                    playTime: parseInt(item.playTime, 10),
                    additionalTimeCost: parseInt(item.additionalTimeCost, 10),
                    huddleFee: parseInt(item.huddleFee, 10),
                    winGain: parseInt(item.winGain, 10),
                    correctAnswer: item.correctAnswer,
                    wildPlayTime: parseInt(item.wildPlayTime, 10),
                    wildAdditionalTimeCost: parseInt(item.wildAdditionalTimeCost, 10),
                    wildMaxGain: parseInt(item.wildMaxGain, 10)
                }));
                return cards; // Return the populated cards array
            } catch (error) {
                console.error('Error loading card data:', error);
                throw error;
            }
        }
        
        // Usage example
        async function processCards() {
            try {
                cards = await loadCosts(); // Wait for loadCosts to complete
                console.log("All cards:", cards);
            } catch (error) {
                // Handle error
                console.error('Failed to process cards:', error);
            }
        }
        
        processCards(); // Start fetching and processing cards asynchronously

        
        function playChallenge() {
            console.log(winningNumber);
            if(team.timeTokens - cards[winningNumber].timeCost >= 0 && team.energyTokens - cards[winningNumber].energyCost >= 0){
                team.timeTokens -= cards[winningNumber].timeCost;
                team.energyTokens -= cards[winningNumber].energyCost;   
                console.log("card " + winningNumber +  " played");
                console.log("segment: " + winningSegment);
                numbers.splice(winningSegment, 1);
                localStorage.setItem(userEmail + 'numbersstandard', numbers); //   ['0', '00', ...Array.from({ length: 36 }, (_, i) => (i + 1).toString())];
                drawWheel();
            }
            else{
                showAlertPopupInformationDef("Your team doesn't have enough tokens to play! Try trading with other teams.");
                //alert();
                return;
            }
            
            displayTeamInfo();
            switchPopupWindow(1);
            startProgressBar(cards[winningNumber].playTime, true);
            console.log("Played challenge with: " + cards[winningNumber].playTime);
        }     

        


        function skipChallenge() {
            closePopup();
        }
        
        function checkBlocked(){
            console.log("checking for team: " + team);
            if(team.energyTokens > 0 && team.timeTokens > 0){
                team.blocked = false;
            }
            else{
                team.blocked = true;
            }
            
        }
        
        function skipChallengeEnergy(){
            console.log("to je to " + team.timeTokens);
            team.timeTokens = team.timeTokens - 1;   
            closePopup();
            displayTeamInfo();
            console.log("to je to posle " + team.timeTokens);
        }

        function Answer(isTrue){
            if(isTrue){
                team.coins += cards[winningNumber].winGain;
                team.profit += cards[winningNumber].winGain;
                displayTeamInfo();
            }
            
            switchPopupWindow(3);
            stopProgressBar();
        }

        function wildReward(){
            try{
                value = document.getElementById('reward-slider').value;
                team.coins += parseInt(value);
                team.profit += parseInt(value);
                displayTeamInfo();
            }
            catch (ex){
                console.log(ex);
            }
            
            
            //document.getElementById('wildRewardButton').style.display = 'none';
            //document.getElementById('winLabel').style.display = 'none';
            //document.getElementById('winLabel1').style.display = 'none';
            //document.getElementById('reward-slider').style.display = 'none';
            
            skipChallenge();
        }

        function playWildCard(){
            const slider = document.getElementById('reward-slider');
            slider.max = cards[winningNumber].wildMaxGain;
            //slider.value = 0;
            //slider.style.display = "flex";
            
            startProgressBar(cards[winningNumber].wildPlayTime, false);
            console.log("played wild with time: " + cards[winningNumber].wildPlayTime);
            switchPopupWindow(4);

        }

        function cuddle() {
            if(team.energyTokens - cards[winningNumber].huddleFee >= 0){
                team.energyTokens -= cards[winningNumber].huddleFee;
                document.getElementById('cuddleButton').style.display = 'none';
                displayTeamInfo();
            }
            else{
                showAlertPopupInformationDef("Your team doesn't have enough energy tokens to huddle! Try trading with other teams.");
                //alert("You dont have energy token to huddle!");
            }
        }

       

        function showPopup(winningNumber) {
            setCard(true);
            popup.style.display = 'flex';
            switchPopupWindow(0);
        }
        
        function switchPopupWindow(to){
            const popupButtons = document.querySelector('.popup-buttons');
            const actionButtons = document.querySelector('.action-buttons-hidden');
            const wildButtons = document.querySelector('.wild-buttons');
            const sliderInput = document.querySelector('.slider-input');
            const playWildButtons = document.querySelector('.before-wc');
            switch (to){
                case 0: 
                    popupButtons.style.display = "flex";
                    sliderInput.style.display = "none";
                    actionButtons.style.display = "none";
                    wildButtons.style.display = "none";
                    playWildButtons.style.display = "none";
                    break;
                case 1:
                    popupButtons.style.display = "none";
                    sliderInput.style.display = "none";
                    actionButtons.style.display = "flex";
                    document.getElementById('cuddleButton').style.display = 'block';
                    document.getElementById('extraTimeButton').style.display = 'block';
                    wildButtons.style.display = "none";
                    playWildButtons.style.display = "none";
                    break;
                case 2:
                    popupButtons.style.display = "none";
                    sliderInput.style.display = "none";
                    actionButtons.style.display = "none";
                    wildButtons.style.display = "none";
                    playWildButtons.style.display = "flex";
                    break;
                case 3:
                    popupButtons.style.display = "none";
                    sliderInput.style.display = "none";
                    actionButtons.style.display = "none";
                    wildButtons.style.display = "flex";
                    playWildButtons.style.display = "none";
                    break;
                case 4:
                    popupButtons.style.display = "none";
                    sliderInput.style.display = "flex";
                    actionButtons.style.display = "none";
                    wildButtons.style.display = "none";
                    playWildButtons.style.display = "none";
                    break;
            }
        }

        function closePopup() {
            displayTeamInfo();
            const popup = document.getElementById('popup');
            popup.style.display = 'none';
            //document.getElementById('winning-number').innerText = '';
            stopProgressBar();
            displayCurrentPlayer();
            canSpin = true;
        }

        function trade() {
            document.getElementById("popupTrade").style.display = "flex";
        }

        function closeTrade() {
            document.getElementById("popupTrade").style.display = "none";
        }
        
        // Progress bar for time limit
        let progressBarWidth = 100; // Initial width in percentage
        let progressBarInterval;


        function extraTime() {
            
            if(team.timeTokens - cards[winningNumber].additionalTimeCost >= 0){

                team.timeTokens -= cards[winningNumber].additionalTimeCost;

                const progressBar = document.getElementById('progress-bar');
                
                let widthData = progressBar.style.width;
                let width = parseInt(widthData.slice(0, -1));
                console.log("starting width: " + width);
                //150 je minut ali ide na % pa sam ga morao /100
                width += 1.5 * 25

                progressBarWidth = Math.min(width, 100);
                console.log("end width: " + progressBar.style.width);
                document.getElementById('extraTimeButton').style.display = 'none';
                document.getElementById('wildAnswerButton1').style.display = "none";
                console.log(timeLeft + " " + maxTime);
                setTime(Math.min(timeLeft + 60, maxTime));
                
                displayTeamInfo();
            }
            else{
                showAlertPopupInformationDef("Time Token Exhausted: You don’t have enough time tokens to buy extra time!");
                //alert();
            }
        }
        
       
       
       
       
        let timerInterval;
        let timeLeft = 0;
        let maxTime = 0

        /**
         * Updates the display of the countdown timer in minutes:seconds format.
         */
        function updateDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('current-player-popup').innerHTML = `Time: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
        
        /**
         * Starts a countdown timer from a given number of seconds.
         * @param {number} startSeconds - The number of seconds to start the countdown from.
         */
        function startCountdown(startSeconds, close) {
            maxTime = startSeconds;
            timeLeft = startSeconds;
            updateDisplay(); // Initial display

            if (timerInterval) {
                clearInterval(timerInterval); // Clear any existing timer
            }

            timerInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(timerInterval);
                    document.getElementById('current-player-popup').innerHTML = 'Time is up!'; // Optionally display when time is up
                    timeUp(close);
                } else {
                    updateDisplay();
                }
            }, 1000);
        }
        
        function stopTimer(){
            document.getElementById('current-player-popup').innerHTML = "";
            timeLeft = -1;
            maxTime = 0;
            if (timerInterval) {
                clearInterval(timerInterval); // Clear the interval to stop the countdown
                timerInterval = null; // Optional: Clear the reference to the timer
            }
        }
        
        /**
         * Adds a specified number of seconds to the current countdown timer.
         * @param {number} additionalSeconds - The number of seconds to add.
         */
        function setTime(time) {
            timeLeft = time;
            updateDisplay();
        }
       
        function startProgressBar(time, close) {
            let interval = 450;
            switch(time){
                case 1:
                    startCountdown(60, close);
                    interval = 150;
                    break;
                case 2:
                    startCountdown(120, close);
                    interval = 300;
                    break;
                case 3:
                    startCountdown(180, close);
                    interval = 450;
                    break;
                case 4:
                    startCountdown(240, close);
                    interval = 600;
                    break;
                case 5:
                    startCountdown(300, close);
                    interval = 750;
                    break;
            }
    
            /*
            progressBarWidth = 100;
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = `${progressBarWidth}%`;
            const progressBarContainer = document.getElementById('progress-bar-container');
            progressBarContainer.classList.remove('hidden');
            progressBarInterval = setInterval(() => {
                progressBarWidth -= 0.25; // Adjust as needed for speed of countdown
                progressBar.style.width = `${progressBarWidth}%`;
                if (progressBarWidth <= 0) {
                        // to have correct buttons being displayed
                    stopProgressBar();
                    timeUp();
                }
            }, interval); // Adjust interval for tick/second
            */
        }

        function timeUp(close){
            showAlertPopupInformationDef("Time is up! Keep in mind to use extra time option if it's needed!");
            //alert("Time is up! Keep in mind to use extra time if it's needed!");
            if(close)
                closePopup();
        }

        function stopProgressBar() {
            stopTimer();
            clearInterval(progressBarInterval);
            const progressBarContainer = document.getElementById('progress-bar-container');
            progressBarContainer.classList.add('hidden');
        }
        

        // Function to display team information
        function displayTeamInfo() {
            const teamsInfoDiv = document.getElementById('teams-info');
            teamsInfoDiv.innerHTML = teams.map(team => `
                <h3>${team.teamName}</h3>
                <p>Loan: ${team.startingCoins}</p>
                <p>Player cost: ${team.playerCost}</p>
                <p>Coins in hand: ${team.coins}</p>
                <p>Time Tokens: ${team.timeTokens}</p>
                <p>Energy Tokens: ${team.energyTokens}</p>
                <hr>
            `).join('');
            localStorage.setItem(userEmail + "teamsstandard", JSON.stringify(teams));   //coment this line for TESTING
        }

        function setWildCard(){
            const popup = document.getElementById('popup');
            document.getElementById('wildRewardButton').style.display = 'none';
            document.getElementById('wildAnswerButton').style.display = "flex";
            document.getElementById('wildAnswerButton1').style.display = "flex";
            
            document.getElementById('winLabel').style.display = 'none';
            document.getElementById('winLabel1').style.display = 'none';
            document.getElementById('reward-slider').style.display = 'block';
            

        }

        function wildAnswer(){
            setWildCard();
            document.getElementById('reward-slider').style.display = 'block';
            document.getElementById('winLabel').style.display = 'block';
            document.getElementById('winLabel1').style.display = 'block';
            document.getElementById('wildRewardButton').style.display = 'block'
            document.getElementById('wildAnswerButton').style.display = "none";
            document.getElementById('wildAnswerButton1').style.display = "none";
            
            stopProgressBar();
        }

        function setCard(front){
            const popup = document.getElementById('popup');
            stopProgressBar();
        }

        const vSlider = document.getElementById('reward-slider');
        const label = document.getElementById('winLabel');
        vSlider.addEventListener('input', function() {  
            label.innerHTML = vSlider.value;
        });

        // Initial setup
        drawWheel();
        closePopup();
        displayTeamInfo();
        displayUpcomingPlayers();
        closeTrade();


    </script>
</body>
</html>
