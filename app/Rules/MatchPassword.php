<?php

namespace App\Rules;

use App\Models\Nvoq\NvoqUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MatchPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
//        dd($attribute, $value, auth()->user());
        if ($value !== auth()->user()->password) {
//            $fail(':attribute is incorrect.');
            $fail('Current password is incorrect.');
        }
    }
}
