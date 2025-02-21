<?php

namespace Drupal\penchas_custom_token\Service;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

class PendingModerationNodeCount
{
    public function nodeCountForModerator(string $type)
    {
        $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');

        if(!count($groups)) {
            return NULL;
        }

        $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        $query->fields('ms', ['content_entity_id']);
        $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
        $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        $query->condition('nfg.bundle', 'chas_event', '=');
        $result = $query->distinct()->countQuery()->execute()->fetchCol();

        if($result[0] && $result[0] > 0) {
            return new TranslatableMarkup('<div class="info-fields"><p>You have @count events for moderation. Go to <a href="@goToUrl">My Events</a>.</p></div>', [
                '@count' => $result[0],
                '@goToUrl' => Url::fromRoute('view.my_events.page_1')->toString()
              ]);
        }
        return NULL;
    }

    public function nodeCountForAuthor(string $type, string $suffix = '')
    {
        $userId = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        $query->fields('ms', ['content_entity_id']);
        $query->leftJoin('node_field_data', 'fs', 'fs.nid = ms.content_entity_id');
        $query->condition('ms.uid', $userId, '=');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        $query->condition('fs.type', $type);
        $result = $query->distinct()->countQuery()->execute()->fetchCol();
        if($result[0] && $result[0] > 0) {
            return "{$result[0]} {trim($suffix)}";
        }
    }
}
