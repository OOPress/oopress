<?php
namespace OOPress\Core\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class BeforeRequestEvent extends Event
{
    public function __construct(public Request $request) {}
}
