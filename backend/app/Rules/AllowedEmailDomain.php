<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedEmailDomain implements Rule
{
    protected array $allowedDomains = [
        'gmail.com',
        'yahoo.com',
        'outlook.com',
        'hotmail.com',
        'icloud.com',
        'live.com',
        'aol.com',
        'protonmail.com',
    ];

    public function passes($attribute, $value)
    {
        $domain = strtolower(trim(substr((string) $value, strrpos((string) $value, '@') + 1)));

        return $domain !== '' && in_array($domain, $this->allowedDomains, true);
    }

    public function message()
    {
        return 'The :attribute must use a recognized email provider (e.g. Gmail, Yahoo, Outlook).';
    }
}
