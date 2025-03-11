<?php

namespace Drupal\penchas_block_group_role_condition\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'BlockCountForModerator' block.
 *
 * @Block(
 *   id = "moderator_node_count_block",
 *   admin_label = @Translation("Moderator Node Count Block"),
 *   category = @Translation("Custom")
 * )
 */
class BlockCountForModerator extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $group_role = FALSE;
    
    $current_user = \Drupal::currentUser();
    $groupMemberships = \Drupal::service('group.membership_loader')->loadByUser($current_user);
    $required_roles = ['house1-house_coordinator', 'house1-house_director', 'house1-admin_in'];
    
    foreach ($groupMemberships as $membership) {
      $roles = $membership->getRoles();
      foreach ($roles as $role) {
        if (in_array($role->id(), $required_roles)) {
          $group_role = TRUE; 
        }
      }
    }

    $user_roles = $current_user->getRoles();
    $allowed_roles = ['administrator', 'chas_director'];

    if (array_intersect($user_roles, $allowed_roles) || $group_role == TRUE) {
        // $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');


        $view = \Drupal\views\Views::getView('my_events');
        $display_id = 'page_2'; 
        $view->setDisplay($display_id);

        $view->execute();
        $total_results = $view->total_rows;

        if (!empty($total_results)) {
          return [
              '#type' => 'inline_template',
              '#template' => '<div class="info-fields"><p>You have {{ count }} events for moderation. Go to <a href="{{ goToUrl }}">Pending Events</a>.</p></div>',
              '#context' => [
                  'count' => $total_results,
                  'goToUrl' => Url::fromRoute('view.my_events.page_2')->toString(),
              ],
              '#cache' => [
                  'contexts' => ['user', 'session','user.roles'],
              ],
          ];
        }
        // If no groups found, return an empty array to prevent passing null to the cacheable metadata
        // if (!count($groups)) {
        //     return [];
        // }

        // $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        // $query->fields('ms', ['content_entity_id']);
        // $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
        // $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
        // $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        // $query->condition('nfg.bundle', 'chas_event', '=');
        // $result = $query->distinct()->countQuery()->execute()->fetchCol();

        // // Check if we have content to render
        // if (!empty($result[0])) {
        //     return [
        //         '#type' => 'inline_template',
        //         '#template' => '<div class="info-fields"><p>You have {{ count }} events for moderation. Go to <a href="{{ goToUrl }}">My Events</a>.</p></div>',
        //         '#context' => [
        //             'count' => $result[0],
        //             'goToUrl' => Url::fromRoute('view.my_events.page_1')->toString(),
        //         ],
        //         '#cache' => [
        //             'contexts' => ['user', 'session'],
        //         ],
        //     ];
        // }
    }

    // Ensure that you return an empty array if none of the conditions are met
    return [];
}

    
}
