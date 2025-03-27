<?php

namespace Axcel\AxcelCore\Middleware;

use Axcel\AxcelCore\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next);
}
