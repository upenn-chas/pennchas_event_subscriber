<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Hook\Trait\HousePageHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodePreUpdateHook
{
    use EntityHookTrait, HousePageHookTrait;

    public function handle(NodeInterface $node)
    {
        $nodeType = $node->bundle();
        if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        } else if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_HOUSE_PAGE) {
            $this->handleHousePage($node);
        }

        if ($node->hasField('layout_builder__layout') && $node->hasField('field_blocks_ref')) {
            $this->handleLayoutBuilder($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        $originalNode = $node->original;
        $existingState = $originalNode->get('moderation_state')->getString();
        $newState = $node->get('moderation_state')->getString();

        if ($newState === Constant::MOD_STATUS_DELETE) {
            return;
        }

        $eventExistingHouse = $originalNode->get('field_groups')->getValue();
        $eventExistingHousesId = array_column($eventExistingHouse, 'target_id');

        $requestUrl = \Drupal::request()->getRequestUri();
        $nodeId = $node->id();

        if (strpos($requestUrl, "/node/$nodeId/edit") !== FALSE) {
            $eventEndsOn = (int) $node->get('field_event_ends_on')->getString();
            if ($eventEndsOn > time()) {
                if (
                    $existingState === Constant::MOD_STATUS_PUBLISHED
                    && !$this->canByPassModerationInAnyHouse($eventExistingHousesId, Constant::PERMISSION_MODERATION)
                ) {
                    $node->setPublished(FALSE);
                    $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
                    $node->setNewRevision(TRUE);
                    $node->setRevisionLogMessage('The event has moved back to draft for moderation, as the author has edited the event.');
                }
                $this->updateEventEndsOn($node);
            }
            $node->field_groups =  $this->getHouses($node);
            $node->set('field_is_campus_wide', count($node->get('field_groups')->getValue()) >= 14);
        }
        $node->isDefaultRevision(TRUE);
    }

    protected function handleReserveRoom(Node $node)
    {
        $originalNode = $node->original;
        $eventExistingHousesId = (int) $originalNode->get('field_group')->getString();

        $newState = $node->get('moderation_state')->getString();

        if ($newState === Constant::MOD_STATUS_DELETE) {
            return;
        }
        $existingState = $originalNode->get('moderation_state')->getString();

        if (
            $existingState === Constant::MOD_STATUS_PUBLISHED
            && !$this->canByPassModerationInAnyHouse([$eventExistingHousesId], Constant::PERMISSION_MODERATION)
        ) {
            $node->setPublished(false);
            $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
            $node->setNewRevision(TRUE);
            $node->setRevisionLogMessage('Moved to draft.');
            $node->isDefaultRevision(TRUE);
        }
        $node->isDefaultRevision(TRUE);
        $this->updateEventEndsOn($node);
    }


    protected function handleLayoutBuilder(Node $node)
    {
        $layoutBuilder = $node->get('layout_builder__layout')->getValue();
        $blocksIds = $this->getVideoBlocks($this->extractBlocks($layoutBuilder));
        $node->set('field_blocks_ref', $blocksIds ?? null);
    }

    protected function extractBlocks(array $layout)
    {
        $blocks = [];
        foreach ($layout as $ele) {
            $section = $ele['section'];
            $components = $section->getComponents();
            foreach ($components as $comp) {
                $pluginId = $comp->getPluginId();
                $pluginId = explode(':', $pluginId);
                if (count($pluginId) === 2 && $pluginId[0] === 'block_content') {
                    $blocks[$pluginId[1]] = 1;
                }
            }
        }
        return array_keys($blocks);
    }

    protected function getVideoBlocks(array $blocksUuids)
    {
        if (!count($blocksUuids)) {
            return [];
        }
        $blocksIds = [];
        $blocks = \Drupal::entityTypeManager()
            ->getStorage('block_content')
            ->loadByProperties(['uuid' => $blocksUuids]);
        foreach ($blocks as $block) {
            if ($block->bundle() === 'video_block') {
                $blocksIds[] = $block->id();
            }
        }
        return $blocksIds;
    }
}
