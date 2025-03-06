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
    $current_user = \Drupal::currentUser();
    // $current_user_id = $current_user->id();
    // $groupMemberships = \Drupal::service('group.membership_loader')->loadByUser($current_user);
    // dd($groupMemberships);
    $user_roles = $current_user->getRoles();
    $allowed_roles = ['administrator', 'chas_director'];

    if (array_intersect($user_roles, $allowed_roles)) {

      $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');
  
      if (!count($groups)) {
          return [];
      }
  
  
      $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
      $query->fields('ms', ['content_entity_id']);
      $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
      $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
      $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
      $query->condition('nfg.bundle', 'chas_event', '=');
      $result = $query->distinct()->countQuery()->execute()->fetchCol();
  
      if (!empty($result[0])) {
          return [
              '#type' => 'inline_template',
              '#template' => '<div class="info-fields"><p>You have {{ count }} events for moderation. Go to <a href="{{ goToUrl }}">My Events</a>.</p></div>',
              '#context' => [
                  'count' => $result[0],
                  'goToUrl' => Url::fromRoute('view.my_events.page_1')->toString(),
              ],
              '#cache' => [
                'contexts' => ['user','session'],
              ],
          ];
        }
  
      return [];
    }
  }
    
}
