<?php

namespace App\Core\Validators;

use Axcel\Core\Validators\Rules\ConfirmedRule;
use Axcel\Core\Validators\Rules\CsrfRule;
use Axcel\Core\Validators\Rules\DateRule;
use Axcel\Core\Validators\Rules\EmailRule;
use Axcel\Core\Validators\Rules\ExistsRule;
use Axcel\Core\Validators\Rules\FileRule;
use Axcel\Core\Validators\Rules\ImageRule;
use Axcel\Core\Validators\Rules\IntegerRule;
use Axcel\Core\Validators\Rules\MaxRule;
use Axcel\Core\Validators\Rules\MinRule;
use Axcel\Core\Validators\Rules\NumericRule;
use Axcel\Core\Validators\Rules\StringRule;
use Axcel\Core\Validators\Rules\UniqueRule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;


class Validator
{
    protected array $errors = [];
    protected SymfonyRequest $request;
    protected array $rulesMap = [];

    public function __construct()
    {
        $this->request = SymfonyRequest::createFromGlobals();
        $_GET = $this->sanitizeInput($_GET);
        $_POST = $this->sanitizeInput($_POST);


        $this->rulesMap = [
            'string' => StringRule::class,
            'min' => MinRule::class,
            'max' => MaxRule::class,
            'integer' => IntegerRule::class,
            'numeric' => NumericRule::class,
            'date' => DateRule::class,
            'unique' => UniqueRule::class,
            'exists' => ExistsRule::class,
            'confirmed' => ConfirmedRule::class,
            'file' => FileRule::class,
            'image' => ImageRule::class,
            'email' => EmailRule::class,
            '_csrf' => CsrfRule::class,
        ];
    }

    public function validate(array $data, array $rules)
    {
        $sanitizedData = $this->sanitizeInput($data);

        foreach ($rules as $field => $rule) {

            if (!$this->validateCsrf($sanitizedData['_csrf'] ?? null)) {
                return ['errors' => ['_csrf' => "Invalid CSRF token."]];
            }


            $value = $sanitizedData[$field] ?? null;
            $rulesArray = explode('|', $rule);

            if (in_array('required', $rulesArray) && empty($value)) {
                $this->errors[$field] = ucfirst($field) . " is required.";
                continue;
            }

            foreach ($rulesArray as $ruleItem) {
                [$ruleName, $ruleValue] = strpos($ruleItem, ':') !== false
                    ? explode(':', $ruleItem)
                    : [$ruleItem, null];

                $this->applyRule($field, $value, $ruleName, $ruleValue, $sanitizedData);
            }
        }

        return empty($this->errors) ? $sanitizedData : ['errors' => $this->errors];
    }

    protected function applyRule($field, $value, $rule, $ruleValue, $data)
    {
        if (!isset($this->rulesMap[$rule])) {
            return;
        }

        $ruleInstance = new $this->rulesMap[$rule]();
        $errorMessage = $ruleInstance->validate($field, $value, $ruleValue, $data);

        if ($errorMessage) {
            $this->errors[$field] = $errorMessage;
        }
    }

    protected function sanitizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) continue;

            if (is_array($value)) {
                $data[$key] = $this->sanitizeInput($value);
            } elseif (is_string($value)) {
                $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    protected function validateCsrf(?string $token): bool
    {
        $headerToken = request()->headers->get('X-CSRF-TOKEN');
        $sessionToken = session()->get('_csrf');

        if ($token && hash_equals($sessionToken, $token)) {
            return true;
        }

        if ($headerToken && hash_equals($sessionToken, $headerToken)) {
            return true;
        }

        return false;
    }



    public function passes(): bool
    {
        return empty($this->errors);
    }
}
