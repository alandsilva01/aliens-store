<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SalePriceRule implements ValidationRule
{
    public function __construct(private readonly mixed $costPrice) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cost    = (float) $this->costPrice;
        $sale    = (float) $value;
        $minimum = round($cost * 1.10, 2);

        if ($sale < $minimum) {
            $fail('O preco de venda deve ser no minimo 10% acima do custo (minimo: R$ ' . number_format($minimum, 2, ',', '.') . ').');
        }
    }
}
