<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Username implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Username must:
        // Start with a letter.
        // Contain only letters, numbers or underscores.
        // Be between 3-15 characters in length.
        return preg_match('/^[a-z][a-z0-9_]{2}[a-z0-9_]{0,12}$/i', $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Username is invalid.';
    }
}
