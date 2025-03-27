<?php

namespace Axcel\AxcelCore\Validators\Rules;

use Axcel\AxcelCore\Http\Request;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfRule
{
    public function validate(string $field, $value, $ruleValue = null, array $data = [])
    {
        $request = Request::capture();

        if ($request->ajax() || $request->pjax() || $request->expectsJson()) {
            return null;
        }

        if (!$value) {
            return "CSRF token is missing.";
        }

        $csrfManager = app('csrf');
        $csrfToken = new CsrfToken('_csrf', $value);

        if (!$csrfManager->isTokenValid($csrfToken)) {
            return "Invalid CSRF token.";
        }

        return null;
    }
}
