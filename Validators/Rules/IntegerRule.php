<?php

namespace App\Core\Validators\Rules;

class IntegerRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? null
            : ucfirst($field) . " must be an integer.";
    }
}
