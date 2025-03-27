<?php

namespace Axcel\AxcelCore\Validators\Rules;

interface ValidationRuleInterface
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string;
}
