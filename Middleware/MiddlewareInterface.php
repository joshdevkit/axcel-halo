<?php

namespace App\Core\Middleware;

use Axcel\Core\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next);
}
