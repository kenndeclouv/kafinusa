<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Session;

class Calculator extends Component
{
    #[Session]
    public $display = '0';
    #[Session]
    public $equation = '';
    #[Session]
    public $previousValue = null;
    #[Session]
    public $operator = null;
    #[Session]
    public $waitingForNewValue = false;
    
    #[Session]
    public $mode = 'standard'; // standard, volume, length, mass
    #[Session]
    public $fromUnit = '';
    #[Session]
    public $toUnit = '';

    public function setMode($mode)
    {
        $this->mode = $mode;
        $this->clear();
        
        if ($mode === 'volume') {
            $this->fromUnit = 'teaspoons';
            $this->toUnit = 'milliliters';
        } elseif ($mode === 'length') {
            $this->fromUnit = 'inches';
            $this->toUnit = 'centimeters';
        } elseif ($mode === 'mass') {
            $this->fromUnit = 'pounds';
            $this->toUnit = 'kilograms';
        }
    }

    public function getAvailableUnitsProperty()
    {
        if ($this->mode === 'volume') {
            return [
                'milliliters' => 'Millilitres',
                'liters' => 'Litres',
                'teaspoons' => 'Teaspoons (US)',
                'tablespoons' => 'Tablespoons (US)',
                'fluid_ounces' => 'Fluid ounces (US)',
                'cups' => 'Cups (US)',
            ];
        } elseif ($this->mode === 'length') {
            return [
                'millimeters' => 'Millimetres',
                'centimeters' => 'Centimetres',
                'meters' => 'Metres',
                'kilometers' => 'Kilometres',
                'inches' => 'Inches',
                'feet' => 'Feet',
                'yards' => 'Yards',
                'miles' => 'Miles',
            ];
        } elseif ($this->mode === 'mass') {
            return [
                'milligrams' => 'Milligrams',
                'grams' => 'Grams',
                'kilograms' => 'Kilograms',
                'ounces' => 'Ounces',
                'pounds' => 'Pounds',
            ];
        }
        return [];
    }

    public function getConvertedValueProperty()
    {
        if ($this->mode === 'standard' || $this->display === '') return '0';
        
        $val = (float) $this->display;
        if ($val == 0) return '0';
        
        $rates = [
            'volume' => [
                'milliliters' => 1,
                'liters' => 1000,
                'teaspoons' => 4.92892,
                'tablespoons' => 14.7868,
                'fluid_ounces' => 29.5735,
                'cups' => 236.588,
            ],
            'length' => [
                'millimeters' => 1,
                'centimeters' => 10,
                'meters' => 1000,
                'kilometers' => 1000000,
                'inches' => 25.4,
                'feet' => 304.8,
                'yards' => 914.4,
                'miles' => 1609344,
            ],
            'mass' => [
                'milligrams' => 1,
                'grams' => 1000,
                'kilograms' => 1000000,
                'ounces' => 28349.5231,
                'pounds' => 453592.37,
            ]
        ];
        
        $baseVal = $val * ($rates[$this->mode][$this->fromUnit] ?? 1);
        $converted = $baseVal / ($rates[$this->mode][$this->toUnit] ?? 1);
        
        // Remove trailing zeros for a clean display
        $formatted = rtrim(rtrim(sprintf('%.6f', $converted), '0'), '.');
        return $formatted ?: '0';
    }

    public function input($value)
    {
        if ($this->waitingForNewValue) {
            $this->display = (string) $value;
            $this->waitingForNewValue = false;
        } else {
            if ($this->display === '0' && $value !== '.') {
                $this->display = (string) $value;
            } else {
                if ($value === '.' && str_contains($this->display, '.')) {
                    return;
                }
                // limit length
                if (strlen($this->display) < 16) {
                    $this->display .= (string) $value;
                }
            }
        }
    }

    public function operate($operator)
    {
        if ($this->mode !== 'standard') return;

        if ($this->operator && !$this->waitingForNewValue) {
            $this->calculate();
        }

        $this->previousValue = $this->display;
        $this->operator = $operator;
        $this->waitingForNewValue = true;
        
        $opSymbol = $operator;
        if ($operator === '*') $opSymbol = '×';
        if ($operator === '/') $opSymbol = '÷';
        
        $this->equation = $this->previousValue . ' ' . $opSymbol;
    }

    public function calculate()
    {
        if ($this->mode !== 'standard') return;
        if (!$this->operator || $this->previousValue === null) return;

        $prev = (float) $this->previousValue;
        $current = (float) $this->display;
        $result = 0;

        switch ($this->operator) {
            case '+': $result = $prev + $current; break;
            case '-': $result = $prev - $current; break;
            case '*': $result = $prev * $current; break;
            case '/': $result = $current != 0 ? $prev / $current : 0; break;
        }

        $this->display = (string) $result;
        $this->equation = '';
        $this->operator = null;
        $this->previousValue = null;
        $this->waitingForNewValue = true;
    }

    public function clear()
    {
        $this->display = '0';
        $this->equation = '';
        $this->previousValue = null;
        $this->operator = null;
        $this->waitingForNewValue = false;
    }

    public function clearEntry()
    {
        $this->display = '0';
    }

    public function backspace()
    {
        if ($this->waitingForNewValue) return;

        $this->display = substr($this->display, 0, -1);
        if ($this->display === '' || $this->display === '-') {
            $this->display = '0';
        }
    }

    public function negate()
    {
        if ($this->mode !== 'standard') return;
        if ($this->display !== '0') {
            if (str_starts_with($this->display, '-')) {
                $this->display = substr($this->display, 1);
            } else {
                $this->display = '-' . $this->display;
            }
        }
    }

    public function percent()
    {
        if ($this->mode !== 'standard') return;
        $this->display = (string) ((float) $this->display / 100);
        $this->waitingForNewValue = true;
    }
    
    public function inverse() {
        if ($this->mode !== 'standard') return;
        $val = (float) $this->display;
        $this->display = $val != 0 ? (string)(1 / $val) : '0';
        $this->waitingForNewValue = true;
    }
    
    public function square() {
        if ($this->mode !== 'standard') return;
        $val = (float) $this->display;
        $this->display = (string)($val * $val);
        $this->waitingForNewValue = true;
    }
    
    public function sqrt() {
        if ($this->mode !== 'standard') return;
        $val = (float) $this->display;
        $this->display = $val >= 0 ? (string)sqrt($val) : '0';
        $this->waitingForNewValue = true;
    }

    public function render()
    {
        return view('components.calculator');
    }
}
