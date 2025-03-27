<?php

namespace App\Core\Validators\Rules;

class EmailRule implements ValidationRuleInterface
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ucfirst($field) . " must be a valid email.";
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/', $value)) {
            return ucfirst($field) . " must have a valid format.";
        }


        return null;
    }
}
