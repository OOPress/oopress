<?php

declare(strict_types=1);

namespace OOPress\Http\Middleware;

use OOPress\Http\Request;
use OOPress\Http\Response;
use OOPress\Http\MiddlewareInterface;

class AdminMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return Response::redirect('/login');
        }
        
        // Check if user is admin (you'll need to fetch from DB or store role in session)
        // For now, let's just check a simple condition
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return new Response('Access denied. Admin privileges required.', 403);
        }
        
        return $next($request);
    }
}