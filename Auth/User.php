<?php

namespace Axcel\AxcelCore\Auth;

use Axcel\AxcelCore\Eloquent\Foundations\Model;
use Axcel\AxcelCore\Auth\Contracts\AuthenticatableContract;
use Axcel\AxcelCore\Auth\Contracts\CanResetPasswordContract;
use Axcel\AxcelCore\Auth\Passwords\CanResetPassword;

class User extends Model implements
    AuthenticatableContract,
    CanResetPasswordContract
{
    use Authenticatable, MustVerifyEmail, CanResetPassword;
}
