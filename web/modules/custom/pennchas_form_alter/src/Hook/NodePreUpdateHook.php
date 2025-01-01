<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodePreUpdateHook
{
    use EntityHookTrait;

    public function handle(NodeInterface $node)
    {
        $nodeType = $node->bundle();
        if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        $eventExistingHouse = $node->original->get('field_groups')->getValue();
        $eventExistingHousesId = array_column($eventExistingHouse, 'target_id');

        $eventHouseId = (int) $node->get('field_location')->getString();
        $housesId = [$eventHouseId];
        $moderationState = $node->get('moderation_state')->getString();
        if ($moderationState === Constant::MOD_STATUS_PUBLISHED) {
            if ($this->canByPassModerationInAnyHouse($eventExistingHousesId, Constant::PERMISSION_MODERATION)) {
                $housesId = $this->getHouses($node);
            } else {
                $node->setPublished(false);
                $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
            }
        }
        $node->field_groups =  $housesId;
        $this->updateEventEndsOn($node);
        $node->set('field_is_campus_wide', count($node->get('field_group')->getValue()) >= 14);
    }
}
