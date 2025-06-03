<?php

namespace Drupal\common_utils\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\node\NodeInterface;

/**
 * Check if the logged in user is modarator.
 */
class ModeratorCheck
{
    /**
     * Logged in user.
     * 
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $user;

    /**
     * Moderation permission
     * 
     * @var string
     */
    protected $moderatorPermission = 'use editorial transition publish';

    public function __construct(AccountInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Checks whether the current user can modarate or not.
     *
     * @param Drupal\node\NodeInterface $node
     *   The node to check for.
     *
     * @return boolean
     *  
     */
    public function checkForEntity(NodeInterface $node)
    {
        $nodeType = $node->getType();
        // Check if the node type is either 'reserve_room' or 'chas_event'
        if ($nodeType !== 'reserve_room' && $nodeType !== 'chas_event') {
            return FALSE;
        }

        // Check if the user has the moderator permission for 'chas_event' node type
        // and the event type is 'chas_centered_event'
        if ($nodeType === 'chas_event' && (bool) $node->get('field_flag')->getString()) {
            return $this->user->hasPermission($this->moderatorPermission);
        }

        $field = $nodeType === 'reserve_room' ? 'field_group' : 'field_location';

        if ($this->user->hasPermission($this->moderatorPermission)) {
            return TRUE;
        }
        $groupId = (int) $node->get($field)->getString();
        $group = Group::load($groupId);
        if ($group) {
            return $group->hasPermission($this->moderatorPermission, $this->user);
        }

        return FALSE;
    }
}
