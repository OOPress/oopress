<?php
namespace OOPress\Core\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AfterRequestEvent extends Event
{
    public function __construct(public Request $request, public Response $response) {}
}
