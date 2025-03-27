<?php

namespace App\Core\Auth;

use App\Core\Eloquent\Foundations\Model;
use App\Core\Auth\Contracts\AuthenticatableContract;
use App\Core\Auth\Contracts\CanResetPasswordContract;
use App\Core\Auth\Passwords\CanResetPassword;

class User extends Model implements
    AuthenticatableContract,
    CanResetPasswordContract
{
    use Authenticatable, MustVerifyEmail, CanResetPassword;
}
