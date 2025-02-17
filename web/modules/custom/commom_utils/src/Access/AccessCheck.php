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
     * Checks whether the current user has the given permission.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @return bool
     *   TRUE if the user has the given permission, FALSE otherwise.
     */
    public function check(string $permission)
    {
        return !empty($permission) && ($this->checkForNonGroupMember($permission) || $this->checkForGroupMember($permission));
    }

    /**
     * Checks if the logged-in user has the given permission.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @return bool
     *   TRUE if the user has the specified permission, FALSE otherwise.
     */
    public function checkForNonGroupMember(string $permission)
    {
        return $this->user->hasPermission($permission);
    }

    /**
     * Checks if the logged-in user has the specified permission in their membered groups.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @return bool
     *   TRUE if the user has the given permission, FALSE otherwise.
     */
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
