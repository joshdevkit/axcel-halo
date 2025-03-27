<?php

namespace Axcel\AxcelCore\Http;


class Redirect extends Response
{
    public function __construct(?string $url = null)
    {
        parent::__construct('', 302, ['Location' => $url ?? '/']);
    }

    public function to(string $url): self
    {
        $this->headers->set('Location', $url);
        return $this;
    }

    public function back(): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->to($referer);
    }

    public function with(string $key, string|array $data): self
    {
        session()->set($key, $data);
        return $this;
    }

    public function withErrors(array $errors): self
    {
        if (request()->expectsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['errors' => $errors]);
            exit;
        }

        session()->flash('errors', $errors);
        return $this;
    }


    public function withInput(?array $input = null): self
    {
        $input = $input ?? $_POST;
        return $this->with('old', $input);
    }
}
