<?php

namespace Drupal\penchas_block_group_role_condition;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Drupal\Core\Render\Markup;
use Drupal\Core\Database\Database;

/**
 * Defines a custom Twig extension.
 */
class TwigExtension extends AbstractExtension {

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        // Define a new Twig function.
        return [
        new TwigFunction('verify_role_access', [$this, 'verifyRoleAccess']),
        ];
    }


    public function verifyRoleAccess($field_values) {
        
        if (!isset($field_values['#items'])) {
            // If there are no field values, stop further processing.
            return;
        }
        // dump($field_values);
        $group_roles_arr = [];
        foreach ($field_values['#items']->referencedEntities() as $value) {
            $group_roles_arr[] = $value->id();
        }

        $current_user = \Drupal::currentUser();
        $user_roles = $current_user->getRoles();  // Get an array of the current user's roles

        // Skip processing if the current user is an admin
        if (in_array('administrator', $user_roles)) {
            return TRUE; // If the user is an admin, prevent role-based access
        }

        $group_role = TRUE;
        $user_entity = \Drupal\user\Entity\User::load($current_user->id());
        $connection = Database::getConnection();
        $query = $connection->select('group_relationship_field_data', 'gr')
        ->fields('gr', ['id'])
        ->condition('entity_id', $current_user->id(), '=')
        ->condition('plugin_id', 'group_membership', '=');
        
        // Execute the query and fetch all results as an associative array.
        $results = $query->execute()->fetchAssoc('id');
        if(!empty($results['id'])){
            $current_user_role_id = $results['id'];
            $query = $connection->select('group_relationship__group_roles', 'grr')
            ->fields('grr', ['group_roles_target_id'])
            ->condition('entity_id', $current_user_role_id, '=');
            $group_roles_results = $query->execute()->fetchAssoc('group_roles_target_id');
            if(!empty($group_roles_results)){
            $group_role = $group_roles_results['group_roles_target_id'];
            }
        }
        if(!empty($group_role)){
            $key = array_search($group_role, $group_roles_arr);
            if ($key !== false) {
                $group_role = TRUE;
            } else {
                $group_role = FALSE;
            }
        }
        return $group_role;
    }

  
}
