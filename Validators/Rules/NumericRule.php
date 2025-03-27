<?php

namespace Axcel\AxcelCore\Validators\Rules;

class NumericRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return is_numeric($value) ? null
            : ucfirst($field) . " must be a numeric value.";
    }
}
