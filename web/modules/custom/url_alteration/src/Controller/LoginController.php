<?php

namespace Drupal\url_alteration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends ControllerBase
{
    /**
     * Returns a simple page with a login form.
     *
     * @return array
     *   A render array containing the login form.
     */
    public function userLogin()
    {
        if (!\Drupal::currentUser()->isAuthenticated()) {

            $request = \Drupal::request();
            $destination = $request->headers->get('referer');
        
            if ($destination) {
                $destination = preg_replace('/^.+?[^\/:](?=[?\/]|$)/', '', $destination);
            }
            if( !$destination || $destination === '/') {
                // If the destination is empty or the front page, redirect to the front page.
                $destination = \Drupal::config('system.site')->get('page.front');
            }
            $login_url = \Drupal\Core\Url::fromRoute('simplesamlphp_auth.saml_login', [
                'destination' => $destination,
            ])->toString();
            return new RedirectResponse($login_url);
        }
    }
}
