<?php

namespace Axcel\AxcelCore\Validators\Rules;

class PasswordRule implements ValidationRuleInterface
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        return preg_match($pattern, $value) ? null :
            ucfirst($field) . " must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }
}
