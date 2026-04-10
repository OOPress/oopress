<?php

declare(strict_types=1);

namespace OOPress\Http;

interface MiddlewareInterface
{
    public function process(Request $request, callable $next): Response;
}