<?php
namespace OOPress\Plugins\HelloWorld;

use OOPress\Core\Events\BeforeRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class HelloWorldPlugin implements EventSubscriberInterface
{
    /**
     * Register events this plugin subscribes to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRequestEvent::class => 'onBeforeRequest',
        ];
    }

    /**
     * Event callback for BeforeRequestEvent
     */
    public function onBeforeRequest(BeforeRequestEvent $event): void
    {
        $request = $event->request;

        // Example: add a header if the path is /hello-plugin
        if ($request->getPathInfo() === '/hello-plugin') {
            $response = new Response('<h1>Hello from HelloWorld Plugin!</h1>');
            $response->send();

            // Stop further handling
            exit;
        }
    }
}
