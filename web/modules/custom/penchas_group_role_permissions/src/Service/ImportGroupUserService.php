<?php

namespace Drupal\penchas_group_role_permissions\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;

class ImportGroupUserService {

  public function customImport($entity) {
    $values = []; 
    $gid = [];
    $user_id = $entity->id();

    $group_roles = $entity->field_group_roles->value;
    $roles_array = explode(" | ", $group_roles);
    
    $group_name = $entity->field_group->value;
    $group_array = explode(" | ", $group_name);
    $groupType = "house1";

    if(!empty($roles_array)){
      $group_type_entity = GroupType::load('house1');
      $roles = $group_type_entity->getRoles();

      foreach ($roles as $role_key => $role) {
        $role_name = $role->label();
        if (in_array($role_name, $roles_array)) {
          $values[] = $role_key;
        }
      }

      $groupsId =  \Drupal::entityQuery('group')
        ->condition('type', $groupType)
        ->condition('status', 1)->accessCheck(true)->execute();
      $groups = Group::loadMultiple($groupsId);
      foreach ($groups as $group_id => $group) {
        $group_short_name = $group->field_short_name->value;
        if(in_array($group_short_name, $group_array)){
          $gid[] = $group_id;
        }
        $groupData = Group::load($gid);
        dd($groupData);
        if (!$groupData->getMember($user_id)) {
          $groupData->addMember($user, $values);
        }
      }


    }
  }

}