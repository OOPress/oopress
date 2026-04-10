<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Http\Request;
use OOPress\Http\Response;

class PostController
{
    public function home(Request $request): Response
    {
        return new Response('<h1>Welcome to OOPress</h1><p>Home page is working!</p>');
    }
    
    public function show(Request $request): Response
    {
        $slug = $request->attribute('slug');
        return new Response("<h1>Post: {$slug}</h1><p>This post page is working.</p>");
    }
}