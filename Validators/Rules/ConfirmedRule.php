<?php

namespace Axcel\AxcelCore\Validators\Rules;

class ConfirmedRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        $confirmationField = $field . '_confirmation';

        return isset($data[$confirmationField]) && $data[$confirmationField] === $value
            ? null
            : ucfirst($field) . " does not match confirmation.";
    }
}
