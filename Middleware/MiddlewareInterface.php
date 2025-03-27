<?php

namespace App\Core\Middleware;

use App\Core\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next);
}
