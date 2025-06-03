<?php

namespace Drupal\common_utils\User;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\user\Entity\Role;

class User
{

    /**
     * The group type machine name.
     * 
     * @var string $groupType
     */
    protected $groupType = 'house1';

    /**
     * Retrieves a list of user IDs that have the specified permission.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @param array $groupIds
     *   An array of group IDs.
     *
     * @return array
     *   An array of user IDs that have the specified permission.
     */
    public function usersWithPermission(string $permission, array $groupIds = [])
    {
        $nonGroupMembers = $this->getNonGroupMemberUsers($permission);
        $groupMembers = $this->getGroupMemberUsers($permission, $groupIds);
        return array_unique(array_merge($nonGroupMembers, $groupMembers));
    }

    /**
     * Retrieves a list of user IDs, that have the specified permission.
     * The returned users are not members of any group/house.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @return array
     *   An array of user IDs that have the specified permission.
     */
    protected function getNonGroupMemberUsers(string $permission)
    {
        $roles = $this->getNonGroupRolesWithPermission($permission);

        if ($roles) {
            $users = \Drupal::entityQuery('user')
                ->accessCheck(false) // Bypass access check
                ->condition('status', 1) // Active users
                ->condition('mail', '', '!=') // Exclude users with empty email
                ->condition('roles', $roles, 'IN') // Users with the specified roles
                ->execute();
            return $users ? array_keys($users) : [];
        }
        return [];
    }

    /**
     * Retrieves a list of user IDs, that have the specified permission.
     * The returned users are member of one or more group/house.
     *
     * @param string $permission
     *   The permission to check.
     * 
     * @param array $groupIds
     *  An array of group IDs to check for members.
     *
     * @return array
     *   An array of user IDs that have the specified permission.
     */
    protected function getGroupMemberUsers(string $permission, array $groupIds)
    {
        if (!$groupIds) {
            return [];
        }

        $groupRoles = $this->getGroupRolesWithPermission($permission);

        $groupUserIds = [];

        $groups = Group::loadMultiple($groupIds);
        foreach ($groups as $group) {
            $members = $group->getMembers($groupRoles);
            foreach ($members as $member) {
                $user = $member->getUser(); // Get the user object
                if ($user->getEmail()) { // Exclude users with empty email
                    $groupUserIds[] = (int) $user->id();
                }
            }
        }
        return $groupUserIds;
    }


    /**
     * Retrieves a list of drupal roles that have the specified permission.
     * 
     * @param string $permission
     *   The permission to check.
     *
     * @return array
     *   An array of role IDs that have the specified permission.
     */
    protected function getNonGroupRolesWithPermission(string $permission)
    {
        $roles = Role::loadMultiple();

        $moderatorRoles = [];
        foreach ($roles as $key => $role) {
            if ($role->hasPermission($permission)) {
                $moderatorRoles[] = $key;
            }
        }
        return $moderatorRoles;
    }

    /**
     * Retrieves a list of group roles that have the specified permission.
     *
     * @param string $permission
     *   The permission to check.
     *
     * @return array
     *   An array of group role IDs that have the specified permission.
     */
    protected function getGroupRolesWithPermission(string $permission)
    {
        $groupType = GroupType::load($this->groupType);
        $groupRoles = $groupType->getRoles(false);
        $roles = [];

        foreach ($groupRoles as $roleKey => $groupRole) {
            if ($roleKey !== $this->groupType . '-admin' && $groupRole->hasPermission($permission)) {
                $roles[] = $roleKey;
            }
        }
        return $roles;
    }
}
