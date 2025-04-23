<?php

namespace Drupal\penchas_custom_token\Service;


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
    //         return new \Drupal\Core\StringTranslation\TranslatableMarkup('<div class="info-fields"><p>You have @count events for moderation. Go to <a href="@goToUrl">My Events</a>.</p></div>', [
    //             '@count' => $result[0],
    //             '@goToUrl' => \Drupal\Core\Url::fromRoute('view.my_events.page_1')->toString()
    //           ]);
    //     }
    //     return NULL;
    // }

    public function nodeCountForAuthor(string $type, string $suffix = '') {
        $current_user = \Drupal::currentUser();
        $uid = $current_user->id();
        
        // $groups  = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');
        // if (!count($groups)) {
        //     return "No {$suffix}";
        // }

        $query = \Drupal::database()->select('node_field_data', 'nfd');
        $query->join('content_moderation_state_field_data', 'ms', 'nfd.vid = ms.content_entity_revision_id');
        $query->fields('ms', ['content_entity_id']);
        $query->join('node__field_event_schedule', 'nfes', 'nfd.nid = nfes.entity_id');
        $query->condition('nfes.field_event_schedule_value', time(), '>');
        $query->condition('nfd.type', $type, '=');
        $query->condition('nfd.uid', $uid, '=');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
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

