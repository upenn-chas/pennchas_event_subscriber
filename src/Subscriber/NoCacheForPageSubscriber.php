<?php

namespace Drupal\pennchas_event_subscriber\Subscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NoCacheForPageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['kernel.response' => 'onRespond'];
    }

    public function onRespond(ResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getRequestUri() === '/dashboard') {
            $response = $event->getResponse();
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
    }
}
