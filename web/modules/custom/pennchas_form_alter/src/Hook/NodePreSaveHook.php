<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodePreSaveHook
{
    use EntityHookTrait;

    public function handle(NodeInterface $node)
    {
        $nodeType = $node->bundle();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_ROOM) {
            $this->handleRoom($node);
        } else if ($nodeType === Constant::NODE_NOTICES) {
            $this->handleNotice($node);
        } else if ($nodeType === Constant::NODE_PROGRAM_COMMUNITY) {
            $this->handleProgramCommunity($node);
        } else if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        // dump('presave call here');
        $eventFeedbackWebformId = 'event_feedback';
        $node->set('field_feedback_form', [
            'target_id' => $eventFeedbackWebformId
        ]);
        $eventHouseId = (int) $node->get('field_location')->getString();
        $eventHouse = Group::load($eventHouseId);
        $housesId = $this->getHouses($node);
        if ($this->canByPassModeration($eventHouse, Constant::PERMISSION_MODERATION)) {
            $node->setPublished(true);
            $node->set('moderation_state', Constant::MOD_STATUS_PUBLISHED);
            $node->set('field_moderation_finished_at', time());
        } else {
            $node->setPublished(false);
            $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
        }
        $node->field_groups =  $housesId;
        $houseCount = count($housesId);
        if ($houseCount === 1) {
            $eventHouse_data = Group::load($housesId[0]);
            $group_machine_name = $eventHouse_data->get('field_short_name')->value;
            $node->set('field_group_ref', $group_machine_name);
        }
        $node->set('field_is_campus_wide', $houseCount >= 14);
        $this->updateEventEndsOn($node);
    }

    protected function handleNotice(Node $node)
    {
        $affectedHouses = $node->get('field_groups')->getValue();
        $groupIds = array_column($affectedHouses, 'target_id');
        if (count($groupIds) === 1) {
            $group = Group::load($groupIds[0]);
            $groupMachineName = $group->get('field_short_name')->value;
            if ($groupMachineName) {
                $node->set('field_group_ref', $groupMachineName);
            }
        }
    }

    protected function handleReserveRoom(Node $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        if (!$group) {
            $roomId = (int) $node->get('field_room')->getString();
            $room = Node::load($roomId);
            $groupRelationships = GroupRelationship::loadByEntity($room);
            if (count($groupRelationships) >= 1) {
                $groupRelationship = array_shift($groupRelationships);
                $group = $groupRelationship->getGroup();
            }
        }
        $node->set('field_group', $group->id());
        $eventHouse_data = Group::load($group->id());
        $group_machine_name = $eventHouse_data->get('field_short_name')->value;
        $node->set('field_group_ref', $group_machine_name);

        if ($this->canByPassModeration($group, Constant::PERMISSION_MODERATION)) {
            $node->setPublished(true);
            $node->set('moderation_state', Constant::MOD_STATUS_PUBLISHED);
        } else {
            $node->setPublished(false);
            $node->set('moderation_state', Constant::MOD_STATUS_DRAFT);
        }

        $this->updateEventEndsOn($node);
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
            $groupMachineName = $group->get('field_short_name')->value;
            if ($group->hasField('field_short_name')) {
                if (!empty($groupMachineName) && !str_contains($urlAlias, $groupMachineName)) {
                    $urlAlias = $groupMachineName . $urlAlias;
                }
            }
            if ($node->isNew() || !$node->original->get('field_group')->getString()) {
                $node->set('field_group', $group->id());
            }
            $node->set('field_group_ref', $groupMachineName);
            // dd('asdasd: '.$groupMachineName);
        }

        // $node->set('field_group_ref', $urlAlias);
    }

    protected function handleProgramCommunity(Node $node)
    {
        $group_id = (int) $node->get('field_group')->getString();
        $group = null;
        if ($group_id) {
            $group = Group::load($group_id);
        } else {
            $group = \Drupal::routeMatch()->getParameter('group');
            $node->set('field_group', $group->id());
        }
        $groupMachineName = $group->get('field_short_name')->value;

        if ($node->hasField('field_group')) {
            if (!empty($node->get('field_group'))) {
                $node->set('field_group_ref', $groupMachineName);
            }
        }
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
