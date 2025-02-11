<?php
namespace Drupal\common_utils\Option;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

/**
 * Get list of groups for current user.
 */
class GroupOption {

    /**
     * Get the list of groups.
     * 
     * @param string  $groupType
     *  Type of group to return,
     * 
     * @param bool $allGroupsForNonMember
     *  Return all groups for non member user.
     * 
     * @return array []
     *  Key value pair of group id and group label
     */
    public function options(String $groupType = 'house1', bool $allGroupsForNonMember = true )
    {
        $options = [];

        $currentUser = \Drupal::currentUser();
        if(!$currentUser->isAuthenticated()) {
            return $options;
        }
        
        $groupMemberships = GroupMembership::loadByUser($currentUser);
        if ($groupMemberships) {
            foreach ($groupMemberships as $groupMembership) {
                $gid = $groupMembership->get('gid')->getString();
                $group = Group::load($gid);
                $options[$group->id()] = $group->label();
            }
        } else if($allGroupsForNonMember) {
            $groupsId =  \Drupal::entityQuery('group')
                ->condition('type', $groupType)
                ->condition('status', 1)->accessCheck(true)->execute();
            $groups = Group::loadMultiple($groupsId);

            foreach ($groups as $group) {
                $options[$group->id()] = $group->label();
            }
        }
        return $options;
    }

}