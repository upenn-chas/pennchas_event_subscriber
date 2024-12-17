<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodePreInsertHook
{
    use EntityHookTrait;

    public function handle(NodeInterface $node)
    {
        $nodeType = $node->bundle();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_ROOM) {
            $this->handleRoom($node);
        } else if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        $eventFeedbackWebformId = 'event_feedback';
        $node->set('field_feedback_form', [
            'target_id' => $eventFeedbackWebformId
        ]);
        $eventHouseId = (int) $node->get('field_location')->getString();
        $eventHouse = Group::load($eventHouseId);
        $housesId = [$eventHouseId];
        if ($this->canByPassModeration($eventHouse, Constant::PERMISSION_MODERATION)) {
            $node->setPublished(true);
            $node->set('moderation_state', Constant::MOD_STATUS_PUBLISHED);
            $housesId = $this->getHouses($node);
        } else {
            $node->setPublished(false);
            $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
        }
        $node->set('field_groups', $housesId);
        $this->updateEventEndsOn($node);
    }

    protected function handleReserveRoom(Node $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        if ($node->isNew() || !$node->original->get('field_group')->getString()) {
            if(isset($group)){
                $node->set('field_group', $group->id());
            }
        }
        if ($node->isNew()) {
            if ($this->canByPassModeration($group, Constant::PERMISSION_MODERATION)) {
                $node->setPublished(true);
                $node->set('moderation_state', Constant::MOD_STATUS_PUBLISHED);
            } else {
                $node->setPublished(false);
                $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
            }
        }
    }

    protected function handleRoom(Node $node)
    {
        $urlAlias = $node->hasField('path') ? $node->get('path')->alias : "";
        $nid = $node->id();
        $urlAlias = $urlAlias ?: ('/' . $this->slugify($node->getTitle()));
        $groupId = $this->getGroupIdsByEntity($nid);
        $group = null;
        if ($groupId) {
            $group = Group::load($groupId);
        } else {
            $group = \Drupal::routeMatch()->getParameter('group');
        }
        if ($group) {
            if ($group->hasField('field_house_machine_name')) {
                $groupMachineName = $group->get('field_house_machine_name')->value;
                if (!empty($groupMachineName) && !str_contains($urlAlias, $groupMachineName)) {
                    $urlAlias = $groupMachineName . $urlAlias;
                }
            }
            if ($node->isNew() || !$node->original->get('field_group')->getString()) {
                $node->set('field_group', $group->id());
            }
        }

        // $node->set('field_group_ref', $urlAlias);
    }



    private function getGroupIdsByEntity($nid)
    {
        $query = \Drupal::database()->select('group_relationship_field_data', 'gr');
        $query->innerjoin('groups_field_data', 'gfd', 'gr.gid = gfd.id');
        $query->condition('gr.entity_id', $nid);

        // Don't include group user memberships in the query.
        $query->condition('gr.type', 'group-group_membership', '!=');

        $query->fields('gr', ['gid']);
        $result = $query->execute();
        $groupIds = [];
        foreach ($result as $record) {
            $groupIds = $record->gid;
        }
        return $groupIds;
    }

    private function slugify($text, string $divider = '-')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
