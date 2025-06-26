<?php

namespace Drupal\penchas_block_group_role_condition\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class LoadEventsSubscriber implements EventSubscriberInterface {

    /**
     * {@inheritdoc}
     *
     * @return array
     *   The event names to listen for, and the methods that should be executed.
     */
    public static function getSubscribedEvents() {
        // $events[KernelEvents::REQUEST][] = array('checkForRedirection');
        $events[KernelEvents::RESPONSE][] = ['onResponse',28];
        return $events;
    }

    public function onResponse(ResponseEvent  $event) {
    
        $current_path = \Drupal::service('path.current')->getPath();
        $route_match = \Drupal::routeMatch();
        if (!\Drupal::currentUser()->isAuthenticated()) {
            if("system.403" == $route_match->getRouteName() || preg_match('/^\/user\/\d+$/', \Drupal::request()->getPathInfo())) {
                $login_url = '/saml_login?destination=' . urlencode($current_path);
                $response = new RedirectResponse($login_url);
                $event->setResponse($response);
            }
        }

    }

    // public function checkForRedirection(RequestEvent $event) {
    //     // dump('checkForRedirection',$event);
    // }

}