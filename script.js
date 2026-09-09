// These references connect JavaScript to the visible calculator elements in the HTML.
const currentDisplay = document.getElementById('current');
const historyDisplay = document.getElementById('history');
const memeVideo = document.getElementById('memeVideo');

// These variables store the calculator's current state while the user enters an expression.
let currentOperand = '0';
let previousOperand = '';
let operation = undefined;
let shouldResetDisplay = false;

// The audio file is used by the normal visual effect, not by the 9 + 10 meme calculation.
const flashbangAudio = new Audio('flashbang.mp3');
flashbangAudio.preload = 'auto';

function appendNumber(number) {
    // Do not allow more than one decimal point in the current number.
    if (number === '.' && currentOperand.includes('.')) return;

    // Replace the initial zero, or start a new number after an operation/result.
    if (currentOperand === '0' && number !== '.') {
        currentOperand = number;
    } else if (shouldResetDisplay) {
        currentOperand = number;
        shouldResetDisplay = false;
    } else {
        currentOperand += number;
    }
    updateDisplay();
}

function appendOperator(op) {
    if (currentOperand === '') return;

    // If an operator already exists, calculate that part before accepting the new one.
    if (previousOperand !== '') {
        calculateWithoutUpdating();
    }
    operation = op;
    previousOperand = currentOperand;
    shouldResetDisplay = true;
    updateDisplay();
}

function calculate() {
    let computation;

    // Convert the text shown in the display into numbers for arithmetic.
    const prev = parseFloat(previousOperand);
    const current = parseFloat(currentOperand);
    if (isNaN(prev) || isNaN(current)) return;

    // The meme result is intentionally limited to both orders of 9 + 10.
    const isMemeCalculation = operation === '+' &&
        ((prev === 9 && current === 10) || (prev === 10 && current === 9));

    // The 9 + 10 meme uses the video instead of the flash prank.
    if (isMemeCalculation) {
        showMemeVideo();
    } else {
        hideMemeVideo();
        triggerExplosion();
    }

    // Choose the arithmetic operation. The special meme case changes 9 + 10 to 21.
    switch (operation) {
        case '+': computation = isMemeCalculation ? 21 : prev + current; break;
        case '-': computation = prev - current; break;
        case '*': computation = prev * current; break;
        case '/': computation = current === 0 ? 'Error' : prev / current; break;
        case '%': computation = prev % current; break;
        default: return;
    }

    // Save the complete expression in the history area, then show the result.
    historyDisplay.innerText = `${previousOperand} ${getOpSymbol(operation)} ${currentOperand}`;
    currentOperand = computation.toString();
    operation = undefined;
    previousOperand = '';
    shouldResetDisplay = true;
    updateDisplay();
}

function showMemeVideo() {
    // Unhide the video, rewind it, and start playback when 9 + 10 is calculated.
    memeVideo.hidden = false;
    memeVideo.currentTime = 0;
    memeVideo.play().catch(() => {});
}

function hideMemeVideo() {
    // Stop and hide the video so it does not continue playing during another calculation.
    memeVideo.pause();
    memeVideo.currentTime = 0;
    memeVideo.hidden = true;
}

function triggerExplosion() {
    const calculator = document.querySelector('.calculator');
    const flashCount = 10;
    const flashInterval = 1000; // 1 second between flashes
    const totalDuration = flashCount * flashInterval + 4500; // Total duration including last flash animation

    // Create multiple flashes at 1-second intervals for a funnier effect
    for (let i = 0; i < flashCount; i++) {
        window.setTimeout(() => {
            const flash = document.createElement('div');
            flash.className = 'explosion-flash';
            document.body.appendChild(flash);
            playFlashbangSound();

            // Remove flash after its animation completes
            window.setTimeout(() => {
                flash.remove();
            }, 4500);
        }, i * flashInterval);
    }

    // Force a browser reflow so the shake animation can restart on repeated clicks.
    calculator.classList.remove('explode');
    void calculator.offsetWidth;
    calculator.classList.add('explode');

    // Remove the shake animation after the total effect duration finishes.
    window.setTimeout(() => {
        calculator.classList.remove('explode');
    }, totalDuration);
}

function playFlashbangSound() {
    // Restart the audio from the beginning each time for the repeating effect.
    // This gets called multiple times per effect, creating the looping audio.
    flashbangAudio.currentTime = 0;
    flashbangAudio.play().catch(() => playSynthesizedFlashbangSound());
}

function playSynthesizedFlashbangSound() {
    // Use Web Audio as a fallback when the MP3 cannot be played by the browser.
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;

    // These values control the length and pitch of the generated sound.
    const blastDuration = 0.22;
    const ringDuration = 3;
    const audioContext = new AudioContext();
    const gain = audioContext.createGain();
    const oscillator = audioContext.createOscillator();
    const noiseBuffer = audioContext.createBuffer(
        1,
        audioContext.sampleRate * blastDuration,
        audioContext.sampleRate
    );
    const noiseData = noiseBuffer.getChannelData(0);
    const noise = audioContext.createBufferSource();

    // Generate random samples to imitate a short burst of noise.
    for (let index = 0; index < noiseData.length; index += 1) {
        noiseData[index] = Math.random() * 2 - 1;
    }

    noise.buffer = noiseBuffer;
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(1800, audioContext.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(420, audioContext.currentTime + blastDuration);
    oscillator.frequency.exponentialRampToValueAtTime(760, audioContext.currentTime + ringDuration);
    gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.45, audioContext.currentTime + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + ringDuration);

    // Connect the noise and tone to the volume control and then to the speakers.
    noise.connect(gain);
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    noise.start();
    oscillator.start();
    noise.stop(audioContext.currentTime + blastDuration);
    oscillator.stop(audioContext.currentTime + ringDuration);
    oscillator.addEventListener('ended', () => audioContext.close(), { once: true });
}

function calculateWithoutUpdating() {
    let computation;
    const prev = parseFloat(previousOperand);
    const current = parseFloat(currentOperand);
    if (isNaN(prev) || isNaN(current)) return;

    // This helper calculates a chained operation without changing the history display.
    switch (operation) {
        case '+': computation = prev + current; break;
        case '-': computation = prev - current; break;
        case '*': computation = prev * current; break;
        case '/': computation = current === 0 ? 'Error' : prev / current; break;
        case '%': computation = prev % current; break;
        default: return;
    }
    currentOperand = computation.toString();
}

function clearDisplay() {
    // Reset every stored value and return the calculator to its starting state.
    hideMemeVideo();
    currentOperand = '0';
    previousOperand = '';
    operation = undefined;
    historyDisplay.innerText = '';
    updateDisplay();
}

function deleteLast() {
    // Remove the last typed character unless the display is already showing a result.
    if (shouldResetDisplay) return;
    if (currentOperand.length === 1) {
        currentOperand = '0';
    } else {
        currentOperand = currentOperand.slice(0, -1);
    }
    updateDisplay();
}

function getOpSymbol(op) {
    // Convert JavaScript operator characters into symbols that look better in the history.
    if (op === '*') return '×';
    if (op === '/') return '÷';
    return op;
}

function updateDisplay() {
    // Copy the current state into the HTML elements visible to the user.
    currentDisplay.innerText = currentOperand;
    if (operation != null && shouldResetDisplay) {
        historyDisplay.innerText = `${previousOperand} ${getOpSymbol(operation)}`;
    }
}
