<?php

namespace Drupal\penchas_block_group_role_condition\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'ReservationCount' block.
 *
 * @Block(
 *   id = "moderator_room_reservation_count_block",
 *   admin_label = @Translation("Moderator Room Reservation Count Block"),
 *   category = @Translation("Custom")
 * )
 */
class ReservationCount extends BlockBase {
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


        $view = \Drupal\views\Views::getView('my_reservations');
        $display_id = 'page_1'; 
        $view->setDisplay($display_id);

        $view->execute();
        $total_results = $view->total_rows;

        if (!empty($total_results)) {
          return [
              '#type' => 'inline_template',
              '#template' => '<div class="info-fields"><p>They are {{ count }} room reservations awaiting moderation. Go to <a href="{{ goToUrl }}">Pending Room Reservations</a>.</p></div>',
              '#context' => [
                  'count' => $total_results,
                  'goToUrl' => Url::fromRoute('view.my_reservations.page_1')->toString(),
              ],
              '#cache' => [
                  'contexts' => ['user', 'session','user.roles'],
              ],
          ];
        }
 
    }
    return [];
}

    
}
