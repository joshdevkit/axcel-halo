<?php

namespace App\Core\Validators\Rules;

class RequiredRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return empty($value) ? ucfirst($field) . " is required." : null;
    }
}
