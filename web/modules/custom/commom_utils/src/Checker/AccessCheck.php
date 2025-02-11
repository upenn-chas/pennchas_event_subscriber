<?php

namespace Drupal\common_utils\Checker;

use Drupal\Core\Session\AccountInterface;

/**
 * Check the permission for the logged in user.
 */
class AccessCheck
{

    /**
     * Logged in user.
     * 
     * @var Drupal\Core\Session\AccountInterface
     */
    protected $user;

    public function __construct(AccountInterface $user) {
        $this->user = $user;
    }

    /**
     * Checks whether the current user have given permission or not.
     *
     * @param string $permission
     *   The permission that need to check.
     *
     * @return boolean
     *  
     */
    public function check(string $permission)
    {
        if ($this->user->hasPermission($permission)) {
            return TRUE;
        }

        // Check permision at group level, if the user is member of any group.
        $groupMemberships = \Drupal::service('group.membership_loader')->loadByUser($this->user);
        if ($groupMemberships) {
            foreach ($groupMemberships as $membership) {
                $group = $membership->getGroup();
                if ($group && $group->hasPermission($permission, $this->user)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }
}
