<?php

declare(strict_types=1);

namespace OOPress\Core\Events;

use OOPress\Http\Request;

class ApplicationStarted
{
    public function __construct(public readonly Request $request)
    {
    }
}