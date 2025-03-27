<?php

namespace Axcel\AxcelCore\Validators\Rules;

class DateRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return strtotime($value) !== false ? null
            : ucfirst($field) . " must be a valid date.";
    }
}
