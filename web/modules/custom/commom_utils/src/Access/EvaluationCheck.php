<?php

namespace Drupal\common_utils\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Checks whether the event is ready for evaluation or not. 
 */
class EvaluationCheck
{

    /**
     * Logged in user.
     * 
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $user;

    /**
     * Evaluation permission
     * 
     * @var string
     */
    protected $evaluationPermission = 'event own evaluation';

    public function __construct(AccountInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Checks whether the chas event can evaluate or not.
     *
     * @param \Drupal\node\NodeInterface $node
     *   The node to check for.
     *
     * @return boolean
     *  
     */
    public function checkForEntity(NodeInterface $node)
    {
        if ($node->bundle() !== 'chas_event' || !$node->isPublished()) {
            return false;
        }

        if ((int) $node->get('field_event_ends_on')->getString() >= time()) {
            return false;
        }

        if (!\Drupal::service('pennchas_common.access_check')->check($this->evaluationPermission)) {
            return false;
        }


        return $node->getOwnerId() === $this->user->id() || $this->user->hasRole('administrator');
    }
}
