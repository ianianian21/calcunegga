<?php
// PHP serves the calculator page through Apache; the interactive behavior stays in JavaScript.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleek Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- The calculator container holds the display and all input buttons. -->
    <div class="calculator">
        <div class="display">
            <!-- JavaScript updates these elements whenever the calculator state changes. -->
            <div class="history" id="history"></div>
            <div class="current" id="current">0</div>
            <!-- This stays hidden until the special 9 + 10 or 10 + 9 calculation. -->
            <video class="meme-video" id="memeVideo" src="meme.mp4" controls playsinline hidden></video>
        </div>
        <!-- Inline click handlers call the JavaScript functions for each button. -->
        <div class="buttons">
            <button class="btn action" onclick="clearDisplay()">AC</button>
            <button class="btn action" onclick="deleteLast()">DEL</button>
            <button class="btn action" onclick="appendOperator('%')">%</button>
            <button class="btn operator" onclick="appendOperator('/')">/</button>

            <button class="btn" onclick="appendNumber('7')">7</button>
            <button class="btn" onclick="appendNumber('8')">8</button>
            <button class="btn" onclick="appendNumber('9')">9</button>
            <button class="btn operator" onclick="appendOperator('*')">×</button>

            <button class="btn" onclick="appendNumber('4')">4</button>
            <button class="btn" onclick="appendNumber('5')">5</button>
            <button class="btn" onclick="appendNumber('6')">6</button>
            <button class="btn operator" onclick="appendOperator('-')">-</button>

            <button class="btn" onclick="appendNumber('1')">1</button>
            <button class="btn" onclick="appendNumber('2')">2</button>
            <button class="btn" onclick="appendNumber('3')">3</button>
            <button class="btn operator" onclick="appendOperator('+')">+</button>

            <button class="btn zero" onclick="appendNumber('0')">0</button>
            <button class="btn" onclick="appendNumber('.')">.</button>
            <button class="btn equals" onclick="calculate()">=</button>
        </div>
    </div>
    <!-- Load the calculator behavior after the page elements have been created. -->
    <script src="script.js"></script>
</body>
</html>
