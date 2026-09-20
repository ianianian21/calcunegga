<?php
session_start();

require_once 'calculator.php';

// Initialize calculator if not in session
if (!isset($_SESSION['calculator_state'])) {
    $_SESSION['calculator_state'] = [
        'currentOperand' => '0',
        'previousOperand' => '',
        'operation' => null,
        'shouldResetDisplay' => false
    ];
}

$calc = new Calculator();
$calc->setState($_SESSION['calculator_state']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which action was pressed
    if (isset($_POST['clear'])) {
        $calc->clearDisplay();
    } elseif (isset($_POST['delete'])) {
        $calc->deleteLast();
    } elseif (isset($_POST['calculate'])) {
        $calc->calculate();
    } elseif (isset($_POST['num'])) {
        $calc->appendNumber($_POST['num']);
    } elseif (isset($_POST['op'])) {
        $calc->appendOperator($_POST['op']);
    }
    
    // Save state back to session
    $_SESSION['calculator_state'] = $calc->getState();
}

// Get display values
$currentDisplay = $calc->getCurrentDisplay();
$historyDisplay = $calc->getHistoryDisplay();
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
            <!-- Display shows current calculation history and result. -->
            <div class="history" id="history"><?php echo htmlspecialchars($historyDisplay); ?></div>
            <div class="current" id="current"><?php echo htmlspecialchars($currentDisplay); ?></div>
        </div>
        
        <!-- Form handles calculator inputs and submissions to PHP backend. -->
        <form method="POST" class="buttons">
            <button type="submit" name="clear" class="btn action">AC</button>
            <button type="submit" name="delete" class="btn action">DEL</button>
            <button type="submit" name="op" value="%" class="btn action">%</button>
            <button type="submit" name="op" value="/" class="btn operator">/</button>

            <button type="submit" name="num" value="7" class="btn">7</button>
            <button type="submit" name="num" value="8" class="btn">8</button>
            <button type="submit" name="num" value="9" class="btn">9</button>
            <button type="submit" name="op" value="*" class="btn operator">×</button>

            <button type="submit" name="num" value="4" class="btn">4</button>
            <button type="submit" name="num" value="5" class="btn">5</button>
            <button type="submit" name="num" value="6" class="btn">6</button>
            <button type="submit" name="op" value="-" class="btn operator">-</button>

            <button type="submit" name="num" value="1" class="btn">1</button>
            <button type="submit" name="num" value="2" class="btn">2</button>
            <button type="submit" name="num" value="3" class="btn">3</button>
            <button type="submit" name="op" value="+" class="btn operator">+</button>

            <button type="submit" name="num" value="0" class="btn zero">0</button>
            <button type="submit" name="num" value="." class="btn">.</button>
            <button type="submit" name="calculate" class="btn equals">=</button>
        </form>
    </div>

    <script>
        // Preload audio with multiple attributes
        const flashbangAudio = document.createElement('audio');
        flashbangAudio.src = 'flashbang.mp3';
        flashbangAudio.preload = 'auto';
        flashbangAudio.crossOrigin = 'anonymous';

        // Flashbang effect on every button click
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                triggerFlashbang();
                
                // Get the form
                const form = document.querySelector('form');
                const formData = new FormData();
                
                // Add the clicked button's data
                formData.append(this.name, this.value);
                
                // Submit via AJAX - no page reload!
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Parse the response to get updated display values
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update display elements
                    const newCurrent = doc.querySelector('.current').textContent;
                    const newHistory = doc.querySelector('.history').textContent;
                    
                    document.querySelector('.current').textContent = newCurrent;
                    document.querySelector('.history').textContent = newHistory;
                })
                .catch(err => console.error('Form submission error:', err));
            });
        });

        function triggerFlashbang() {
            // Create white flashbang effect
            const flash = document.createElement('div');
            flash.style.position = 'fixed';
            flash.style.top = '0';
            flash.style.left = '0';
            flash.style.width = '100%';
            flash.style.height = '100%';
            flash.style.backgroundColor = '#ffffff';
            flash.style.zIndex = '9999';
            flash.style.opacity = '1';
            flash.style.pointerEvents = 'none';
            document.body.appendChild(flash);

            // Animate opacity over 2 seconds
            let elapsed = 0;
            const duration = 2000;
            const startTime = Date.now();

            function animate() {
                elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                flash.style.opacity = (1 - progress).toString();

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    flash.remove();
                }
            }
            animate();

            // Play sound
            try {
                flashbangAudio.currentTime = 0;
                flashbangAudio.volume = 1;
                flashbangAudio.play();
            } catch (err) {
                console.error('Audio error:', err);
            }
        }
    </script>
</body>
</html>
