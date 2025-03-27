<?php

namespace Axcel\AxcelCore\Auth;

use Axcel\Core\Eloquent\Foundations\Model;
use Axcel\Core\Auth\Contracts\AuthenticatableContract;
use Axcel\Core\Auth\Contracts\CanResetPasswordContract;
use Axcel\Core\Auth\Passwords\CanResetPassword;

class User extends Model implements
    AuthenticatableContract,
    CanResetPasswordContract
{
    use Authenticatable, MustVerifyEmail, CanResetPassword;
}
