<?php

namespace App\Services;

class ShippingCalculator
{
    public function calculate(float $subtotal): float
    {
        if ($subtotal >= 200) {
            return 0; // Frete grátis
        }
        
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }
        
        return 20;
    }
}
