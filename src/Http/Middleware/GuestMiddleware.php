<?php

declare(strict_types=1);

namespace OOPress\Http\Middleware;

use OOPress\Http\Request;
use OOPress\Http\Response;
use OOPress\Http\MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            return Response::redirect('/dashboard');
        }
        
        return $next($request);
    }
}