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
        if ($this->user->hasPermission($this->moderatorPermission)) {
            return TRUE;
        }
        if (!$node->hasField('field_location')) {
            return FALSE;
        }
        $groupId = (int) $node->get('field_location')->getString();
        $group = Group::load($groupId);

        return $group->hasPermission($this->moderatorPermission, $this->user);
    }
}
