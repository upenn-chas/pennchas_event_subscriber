<?php

namespace Drupal\pennchas_event_subscriber\Subscriber;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DeletedNodeAccessSubscriber.
 * 
 */
class DeletedNodeAccessSubscriber implements EventSubscriberInterface
{

    /**
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $currentUser;

    /**
     * The allowed routes.
     *
     * @var array
     */
    protected $allowedRoutes = [
        'entity.node.version_history',
        'entity.node.canonical'
    ];

    /**
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountInterface $currentUser
     *  The current user.
     */
    function __construct(AccountInterface $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['checkNodeAccess']
        ];
    }

    /**
     * Check if the node is deleted and the user has access to view it.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *   The event to process.
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *   Thrown when the node is deleted and the user does not have access to view it.
     * 
     */
    public function checkNodeAccess(RequestEvent $event)
    {
        $route = \Drupal::routeMatch();
        $routeName = $route->getRouteName();
        $node = $route->getParameter('node');
        // Check if the node is an instance of NodeInterface
        if (!$node || !$node instanceof \Drupal\node\NodeInterface) {
            return;
        }

        // Check if the node is deleted
        if ($node->hasField('moderation_state') && $node->get('moderation_state')->getString() === 'delete') {
            // Check if the user has access to view the node
            if (!(in_array($routeName, $this->allowedRoutes) && $this->currentUser->hasPermission('bypass node access'))) {
                // Return 404 response
                throw new NotFoundHttpException(t('The requested page was not found.'));
            }
        }
    }
}
