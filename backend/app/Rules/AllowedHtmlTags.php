<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedHtmlTags implements ValidationRule
{
    private array $allowedTags = ['p', 'br', 'b', 'strong'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $stripped = $value;
        foreach ($this->allowedTags as $tag) {
            $stripped = preg_replace('/<\/?' . $tag . '(\s[^>]*)?' . '>/i', '', $stripped);
        }

        if (preg_match('/<[^>]+>/', $stripped)) {
            $allowed = implode(', ', array_map(fn($t) => "<$t>", $this->allowedTags));
            $fail("A descricao aceita apenas as tags HTML: $allowed.");
        }
    }
}
