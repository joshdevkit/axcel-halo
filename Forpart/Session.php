<?php

namespace App\Core\Forpart;

class Session extends Forpart
{
    protected static function getForpartAccessor()
    {
        return 'session';
    }
}
