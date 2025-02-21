<?php

namespace Drupal\penchas_custom_token\Service;


class PendingModerationNodeCount
{
    public function nodeCountForModerator(string $type)
    {
        $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');

        if(!$groups) {
            return 0;
        }

        $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        $query->fields('ms', ['content_entity_id']);
        $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
        $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        $query->condition('nfg.bundle', 'chas_event', '=');
        $result = $query->distinct()->countQuery()->execute()->fetchCol();
        return (int) $result[0] ?? 0;
    }

    public function nodeCountForAuthor(string $type)
    {
        $userId = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('content_moderation_state_field_data', 'ms');
        $query->fields('ms', ['content_entity_id']);
        $query->leftJoin('node_field_data', 'fs', 'fs.nid = ms.content_entity_id');
        $query->condition('ms.uid', $userId, '=');
        $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
        $query->condition('fs.type', $type);
        $result = $query->distinct()->countQuery()->execute()->fetchCol();
        return (int) $result[0] ?? 0;
    }
}
