<?php

namespace Axcel\AxcelCore\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class SessionManager
{
    private static ?SessionManager $instance = null;
    private Session $session;
    private RequestStack $requestStack;

    private function __construct()
    {
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag(), new FlashBag());

        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $request = Request::createFromGlobals();
        $request->setSession($this->session);

        $this->requestStack = new RequestStack();
        $this->requestStack->push($request);
    }

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    public function getCsrfToken(): string
    {
        if (!$this->has('_csrf')) {
            $token = bin2hex(random_bytes(16));
            $this->set('_csrf', $token);
        }

        return $this->get('_csrf');
    }


    public function getSession(): Session
    {
        return $this->session;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->session->get($key, $default);

        if ($key === 'errors') {
            $this->session->remove('errors');
        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $this->session->set($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function remove(string $key): void
    {
        $this->session->remove($key);
    }

    public function flash(string $type, $message): void
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }

        $this->session->getFlashBag()->add($type, $message);
    }


    public function getFlashes(?string $type = null)
    {
        if ($type) {
            return $this->session->getFlashBag()->get($type, []);
        }

        return $this->session->getFlashBag()->all();
    }

    public function regenerate(): bool
    {
        return $this->session->migrate(true);
    }

    public function invalidate(): bool
    {
        return $this->session->invalidate();
    }
}
