<?php

namespace Axcel\AxcelCore\Http;

use Axcel\AxcelCore\Http\Concerns\InteractsWithFileBag;
use Axcel\AxcelCore\Http\Concerns\InteractsWithInputBag;
use Axcel\AxcelCore\Validators\Validator;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    use InteractsWithFileBag, InteractsWithInputBag;

    protected $statusCode = 200;

    public static function capture(): self
    {
        $request = parent::createFromGlobals();
        return new static(
            $request->query->all(),
            $request->request->all(),
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    public function getMethod(): string
    {
        if (parent::getMethod() === 'POST' && $this->request->has('_method')) {
            return strtoupper($this->request->get('_method'));
        }

        return parent::getMethod();
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    public function validate(array $rules)
    {
        $validator = new Validator();
        return $validator->validate($this->all(), $rules);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function expectsJson(): bool
    {
        return $this->ajax() || $this->wantsJson();
    }

    public function ajax(): bool
    {
        return $this->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }

    public function wantsJson(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();
        return isset($acceptable[0]) && (stripos($acceptable[0], 'json') !== false);
    }



    public function pjax(): bool
    {
        return $this->headers->has('X-PJAX');
    }

    public function acceptsAnyContentType(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();
        return count($acceptable) === 0 || (isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*'));
    }
}
