<?php

namespace Axcel\AxcelCore\Middleware;

use Axcel\AxcelCore\Http\Request;
use Axcel\AxcelCore\Http\Response;

class EnsureCsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if ($request->isMethod("POST")) {
            $token = $request->request->get("_csrf");
            $sessionToken = session()->get("_csrf");

            if (!$token || !hash_equals($sessionToken, $token)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Invalid CSRF token.',
                    'errors' => ['_csrf' => 'Invalid CSRF token.']
                ], 403);
            }
        }

        return $next($request);
    }
}
