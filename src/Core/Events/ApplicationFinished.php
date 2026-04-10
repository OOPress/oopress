<?php

declare(strict_types=1);

namespace OOPress\Core\Events;

use OOPress\Http\Response;

class ApplicationFinished
{
    public function __construct(public readonly Response $response)
    {
    }
}