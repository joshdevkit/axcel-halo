<?php

namespace App\Core\Validators\Rules;

class StringRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return is_string($value) ? null : ucfirst($field) . " must be a string.";
    }
}
