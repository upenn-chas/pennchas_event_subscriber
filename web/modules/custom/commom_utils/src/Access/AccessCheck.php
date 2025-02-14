<?php

namespace Drupal\common_utils\Access;

use Drupal\Core\Session\AccountInterface;

/**
 * Check the permission for the logged in user.
 */
class AccessCheck
{

    /**
     * Logged in user.
     * 
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $user;

    public function __construct(AccountInterface $user)
    {
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
        if(!$permission) {
            return FALSE;
        }
        if ($this->checkForNonGroupMember($permission)) {
            return TRUE;
        }

        // Check permision at group level, if the user is member of any group.
        return $this->checkForGroupMember($permission);
    }

    public function checkForNonGroupMember(string $permission)
    {
        return $this->user->hasPermission($permission);
    }

    public function checkForGroupMember(string $permission)
    {
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
