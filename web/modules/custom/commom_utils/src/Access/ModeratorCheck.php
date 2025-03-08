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
        $field = NULL;
        $nodeType = $node->getType();
        if ($nodeType === 'reserve_room') {
            $field = 'field_group';
        } else if ($nodeType === 'chas_event') {
            $field = 'field_location';
        }

        if (!$field) {
            return FALSE;
        }
        if ($this->user->hasPermission($this->moderatorPermission)) {
            return TRUE;
        }
        $groupId = (int) $node->get($field)->getString();
        $group = Group::load($groupId);

        return $group->hasPermission($this->moderatorPermission, $this->user);
    }
}
