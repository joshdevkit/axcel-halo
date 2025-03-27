<?php

namespace Axcel\AxcelCore\Auth;

use Axcel\AxcelCore\Auth\Contracts\AuthenticatableContract;
use Axcel\AxcelCore\Forpart\Hash;
use App\Models\User;
use Axcel\AxcelCore\Session\SessionManager;

class AuthManager
{
    private static ?AuthManager $instance = null;
    private ?AuthenticatableContract $user = null;
    private SessionManager $session;
    private string $userModel = User::class;

    private function __construct()
    {
        $this->session = SessionManager::getInstance();
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->userModel::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->getAuthPassword())) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function login(AuthenticatableContract $user): void
    {
        $this->user = $user;
        $this->session->set('auth_id', $user->getAuthIdentifier());
        $this->session->regenerate();
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->invalidate();
        $this->session->regenerate();
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?AuthenticatableContract
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->session->has('auth_id')) {
            $id = $this->session->get('auth_id');
            $this->user = $this->userModel::find($id);
            return $this->user;
        }

        return null;
    }

    public function id(): ?int
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function setUserModel(string $model): void
    {
        $this->userModel = $model;
    }
}
