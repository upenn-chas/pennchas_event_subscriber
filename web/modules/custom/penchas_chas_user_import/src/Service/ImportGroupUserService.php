<?php

namespace Drupal\penchas_chas_user_import\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\user\Entity\User;
use Drupal\group\Entity\GroupMembership;
use Drupal\group\Entity\GroupRole;
use Drupal\group\GroupManager;

class ImportGroupUserService {

  public function customImport($entity) {
    $values = []; 
    $user_id = $entity->id();

    $group_roles = $entity->field_group_roles->value;
    $roles_array = explode("|", $group_roles);
    $group_name = $entity->field_group->value;
    $group_array = explode("|", $group_name);
    $groupType = "house1";

    if(!empty($group_array)){
  
      $group_type_entity = GroupType::load('house1');
      $roles = $group_type_entity->getRoles();
      $values = ['group_roles' => []];  
      foreach ($roles as $role_key => $role) {
        $role_name = $role->label();
        if (in_array($role_name, $roles_array)) {
          $values['group_roles'][] = $role_key;
        }
      }
      $groupsId = \Drupal::entityQuery('group')
      ->condition('type', $groupType)
      ->condition('status', 1)
      ->accessCheck(true)
      ->execute();
      $groups = Group::loadMultiple($groupsId);
      
      foreach ($groups as $group_id => $group) {
        $group_short_name = $group->field_short_name->value;
        // Check if the group short name matches any of the selected groups
        if (in_array($group_short_name, $group_array)) {
          $gid = $group_id;
          $groupData = Group::load($gid);
          // dd($gid);
          // Check if user is already a member of the group
          if (!$groupData->getMember($entity)) {
            // Add user to group with assigned roles
            // dd($values);
            $groupData->addMember($entity, $values);
          } else {
            // Optionally update the roles if the user is already a member
            // dd('dgds');
            $groupData->removeMember($entity); 
            $groupData->addMember($entity, $values);
          }
        }
      }

      $entity->set('field_group_roles', NULL);
      $entity->set('field_group', NULL);
      $entity->save();
    }

  }

}