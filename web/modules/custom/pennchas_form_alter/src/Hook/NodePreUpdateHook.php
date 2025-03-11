<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityRepository;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
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

        if ($node->hasField('layout_builder__layout') && $node->hasField('field_blocks_ref')) {
            $this->handleLayoutBuilder($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        $eventExistingHouse = $node->original->get('field_groups')->getValue();
        $eventExistingHousesId = array_column($eventExistingHouse, 'target_id');

        $housesId = $this->getHouses($node);
        $moderationState = $node->get('moderation_state')->getString();
        if ($moderationState === Constant::MOD_STATUS_PUBLISHED) {
            if (!$this->canByPassModerationInAnyHouse($eventExistingHousesId, Constant::PERMISSION_MODERATION)) {
                $node->setPublished(false);
                $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
                $node->isDefaultRevision(TRUE);
            }
        }
        $node->field_groups =  $housesId;
        $this->updateEventEndsOn($node);
        $node->set('field_is_campus_wide', count($node->get('field_groups')->getValue()) >= 14);
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
