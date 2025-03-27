<?php

namespace Axcel\AxcelCore\Validators\Rules;

class MinRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return ucfirst($field) . " must be a string or number.";
        }

        if (strlen($value) < (int)$ruleValue) {
            return ucfirst($field) . " must be at least $ruleValue characters long.";
        }

        return null;
    }
}
