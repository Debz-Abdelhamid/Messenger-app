<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $g_response = Http::asForm()->post("https://www.google.com/recaptcha/api/siteverify",[
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip()
    ]);

        if ( $g_response->successful() && $g_response->json('success') && $g_response->json('score') > config('services.recaptcha.min_score')) {

            return;
        }else
        {
            $fail("The {$attribute} is invalid.");

        }

    }
}
