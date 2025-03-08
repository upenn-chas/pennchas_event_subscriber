<?php

namespace Drupal\penchas_custom_token\Service;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

class PendingModerationNodeCount
{
    // public function nodeCountForModerator(string $type)
    // {
    //     $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');

    //     if(!count($groups)) {
    //         return NULL;
    //     }

    //     $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
    //     $query->fields('ms', ['content_entity_id']);
    //     $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
    //     $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
    //     $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
    //     $query->condition('nfg.bundle', 'chas_event', '=');
    //     $result = $query->distinct()->countQuery()->execute()->fetchCol();

    //     if($result[0] && $result[0] > 0) {
    //         return new TranslatableMarkup('<div class="info-fields"><p>You have @count events for moderation. Go to <a href="@goToUrl">My Events</a>.</p></div>', [
    //             '@count' => $result[0],
    //             '@goToUrl' => Url::fromRoute('view.my_events.page_1')->toString()
    //           ]);
    //     }
    //     return NULL;
    // }

    public function nodeCountForAuthor(string $type, string $suffix = '') {
        $current_user = \Drupal::currentUser();
        $uid = $current_user->id();
        
        $groups  = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');
        if (!count($groups)) {
            return '';
        }
       
        $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        $query->fields('ms', ['content_entity_id']);
        $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
        $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
        $query->condition('ms.uid', $uid, '=');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        $query->condition('nfg.bundle', $type, '=');
        $result = $query->distinct()->countQuery()->execute()->fetchCol();
        if(!empty($result)){
            if($result[0] && $result[0] > 0) {
                return "{$result[0]} {$suffix}";
            }else{
                return "No {$suffix}";
            }
        }
    }
}
