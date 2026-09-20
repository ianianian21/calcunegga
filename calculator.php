<?php
/**
 * Calculator Logic - PHP Functions
 * Contains all calculation functions for the calculator
 */

class Calculator {
    private $currentOperand = '0';
    private $previousOperand = '';
    private $operation = null;
    private $shouldResetDisplay = false;

    /**
     * Append a number to the current operand
     */
    public function appendNumber($number) {
        // Do not allow more than one decimal point in the current number
        if ($number === '.' && strpos($this->currentOperand, '.') !== false) {
            return;
        }

        // Start a new number after an operation/result
        if ($this->shouldResetDisplay) {
            if ($number === '.') {
                $this->currentOperand = '0.';
            } else {
                $this->currentOperand = $number;
            }
            $this->shouldResetDisplay = false;
        } 
        // Replace the initial zero
        elseif ($this->currentOperand === '0' && $number !== '.') {
            $this->currentOperand = $number;
        } 
        // Otherwise append the number
        else {
            $this->currentOperand .= $number;
        }
    }

    /**
     * Set the operator for calculation
     */
    public function appendOperator($op) {
        if ($this->currentOperand === '') {
            return;
        }

        // If an operator already exists, calculate that part before accepting the new one
        if ($this->previousOperand !== '') {
            $this->calculateWithoutUpdating();
        }

        $this->operation = $op;
        $this->previousOperand = $this->currentOperand;
        $this->currentOperand = '';
        $this->shouldResetDisplay = true;
    }

    /**
     * Perform the calculation
     */
    public function calculate() {
        $computation = null;

        if ($this->shouldResetDisplay) {
            return;
        }

        $prev = (float)$this->previousOperand;
        $current = (float)$this->currentOperand;

        if (is_nan($prev) || is_nan($current)) {
            return;
        }

        switch ($this->operation) {
            case '+':
                $computation = $prev + $current;
                break;
            case '-':
                $computation = $prev - $current;
                break;
            case '*':
                $computation = $prev * $current;
                break;
            case '/':
                $computation = $current === 0 ? 'Error' : $prev / $current;
                break;
            case '%':
                $computation = $prev % $current;
                break;
            default:
                return;
        }

        $this->currentOperand = (string)$computation;
        $this->operation = null;
        $this->previousOperand = '';
        $this->shouldResetDisplay = true;
    }

    /**
     * Helper function to calculate without updating display (for chained operations)
     */
    public function calculateWithoutUpdating() {
        $computation = null;
        $prev = (float)$this->previousOperand;
        $current = (float)$this->currentOperand;

        if (is_nan($prev) || is_nan($current)) {
            return;
        }

        // This helper calculates a chained operation without changing the history display
        switch ($this->operation) {
            case '+':
                $computation = $prev + $current;
                break;
            case '-':
                $computation = $prev - $current;
                break;
            case '*':
                $computation = $prev * $current;
                break;
            case '/':
                $computation = $current === 0 ? 'Error' : $prev / $current;
                break;
            case '%':
                $computation = $prev % $current;
                break;
            default:
                return;
        }

        $this->currentOperand = (string)$computation;
    }

    /**
     * Clear the display and reset calculator state
     */
    public function clearDisplay() {
        $this->currentOperand = '0';
        $this->previousOperand = '';
        $this->operation = null;
        $this->shouldResetDisplay = false;
    }

    /**
     * Delete the last character from current operand
     */
    public function deleteLast() {
        if ($this->shouldResetDisplay) {
            return;
        }

        if (strlen($this->currentOperand) === 1) {
            $this->currentOperand = '0';
        } else {
            $this->currentOperand = substr($this->currentOperand, 0, -1);
        }
    }

    /**
     * Convert operator characters into symbols
     */
    public static function getOpSymbol($op) {
        if ($op === '*') return '×';
        if ($op === '/') return '÷';
        return $op;
    }

    /**
     * Get the current display value
     */
    public function getCurrentDisplay() {
        return $this->currentOperand;
    }

    /**
     * Get the history display value
     */
    public function getHistoryDisplay() {
        if ($this->operation !== null && $this->shouldResetDisplay) {
            return "{$this->previousOperand} " . self::getOpSymbol($this->operation);
        }
        return '';
    }

    /**
     * Get all state values for session storage
     */
    public function getState() {
        return [
            'currentOperand' => $this->currentOperand,
            'previousOperand' => $this->previousOperand,
            'operation' => $this->operation,
            'shouldResetDisplay' => $this->shouldResetDisplay
        ];
    }

    /**
     * Set state from session data
     */
    public function setState($state) {
        if (isset($state['currentOperand'])) $this->currentOperand = $state['currentOperand'];
        if (isset($state['previousOperand'])) $this->previousOperand = $state['previousOperand'];
        if (isset($state['operation'])) $this->operation = $state['operation'];
        if (isset($state['shouldResetDisplay'])) $this->shouldResetDisplay = $state['shouldResetDisplay'];
    }
}
