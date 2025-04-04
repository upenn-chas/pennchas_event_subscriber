<?php

namespace Drupal\penchas_group_role_permissions\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;

class ImportGroupUserService {

  public function customImport($entity) {
    $values = []; 
    $options = [];
    $group_name = $entity->field_group->value;
    
    $group_roles = $entity->field_group_roles->value;
    $roles_array = explode(" | ", $group_roles);
    dump($roles_array);

    $groupType = "house1";
    $groupsId =  \Drupal::entityQuery('group')
        ->condition('type', $groupType)
        ->condition('status', 1)->accessCheck(true)->execute();
    $groups = Group::loadMultiple($groupsId);
    foreach ($groups as $group) {
      $options[] = $group->field_short_name->value;
      dd($options);
    }

    if(!empty($roles_array)){
      $group_type_entity = GroupType::load('house1');
      $roles = $group_type_entity->getRoles();

      foreach ($roles as $role_key => $role) {
        $role_name = $role->label();
        if (in_array($role_name, $roles_array)) {
          $values[] = $role_key;
        }
      }
      dd($values);
    }
  }

}