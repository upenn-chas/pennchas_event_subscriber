<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationshipInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeUpdateHook
{
    use EntityHookTrait;

    function handle(NodeInterface $node)
    {
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        } else if ($nodeType === Constant::NODE_NOTICES) {
            $this->handleNotices($node);
        } else if ($nodeType === Constant::NODE_HOUSE_PAGE) {
            $this->handleHousePage($node);
        } else if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_ARTICLE) {
            $this->handleArticle($node);
        }
    }

    protected function handleEvent(Node $node)
    {
        $state = $node->get('moderation_state')->getString();
        if ($state === Constant::MOD_STATUS_DELETE) {
            return;
        }

        $this->updateHouseRelations($node);

        if ($this->isMovedForModeration($node)) {
            $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
            $isChasCentralEvent = (bool) $node->get('field_flag')->getString();
            $group = null;
            $moderationWaitingDays = 0;
            if ($isChasCentralEvent) {
                $moderationWaitingDays = \Drupal::service('config_pages.loader')->getValue('chas_moderator', 'field_waiting_period', [], 'value');
                $moderationWaitingDays = $moderationWaitingDays[0];
            } else {
                $groupId = (int) $node->get('field_location')->getString();
                $group = Group::load($groupId);
            }
            $mailService->notifyAuthor($node, Constant::EVENT_MOVED_TO_DRAFT, $group, $moderationWaitingDays);
            $mailService->notifyModerators($node, Constant::EVENT_EMAIL_MODERATOR_ALERT, $group);
        }
    }

    protected function handleNotices(Node $node)
    {
        $state = $node->get('moderation_state')->getString();
        if ($state === Constant::MOD_STATUS_DELETE) {
            return;
        }
        $this->updateHouseRelations($node);
    }

    protected function handleReserveRoom(Node $node)
    {
        if ($this->isMovedForModeration($node)) {
            $groupId = (int) $node->get('field_group')->getString();
            if ($groupId) {
                $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
                $group = Group::load($groupId);
                $mailService->notifyAuthor($node, Constant::RESERVE_ROOM_MOVED_TO_DRAFT, $group);
                $mailService->notifyModerators($node, Constant::RESERVER_ROOM_EMAIL_MODERATOR_ALERT, $group);
            }
        }
    }

    protected function handleHousePage(Node $node)
    {
        $oldHouseId = (int) $node->original->get('field_select_house')->getString();
        $houseId = (int) $node->get('field_select_house')->getString();
        if ($oldHouseId !== $houseId) {
            $oldHouse = Group::load($oldHouseId);
            $oldRelationships = $oldHouse->getRelationshipsByEntity($node);
            if ($oldRelationships) {
                array_walk($oldRelationships, function ($rel) {
                    $rel->delete();
                });
            }
        }
        $house = Group::load($houseId);
        if ($house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
    }

    protected function handleArticle(Node $node)
    {
        $existingGroupId = (int) $node->original->get('field_location')->getString();
        $groupId = (int) $node->get('field_location')->getString();
        if ($existingGroupId === $groupId) {
            return;
        }
        if ($existingGroupId) {
            $existingGroup = Group::load($existingGroupId);
            $oldRelationships = $existingGroup->getRelationshipsByEntity($node);
            array_walk($oldRelationships, function ($rel) {
                $rel->delete();
            });
        }
        if ($groupId) {
            $group = Group::load($groupId);
            $existingRelationship = $group->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $group->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
    }

    protected function updateHouseRelations(Node $node)
    {
        $result = $this->processHouseChangeData($node);
        $new = $result['new'];
        $remove = $result['remove'];

        if ($new) {
            $houses = Group::loadMultiple($new);
            foreach ($houses as $house) {
                $existingRelationship = $house->getRelationshipsByEntity($node);
                if (empty($existingRelationship)) {
                    $house->addRelationship($node, 'group_node:' . $node->getType());
                }
            }
        }
        if ($remove) {
            $houses = Group::loadMultiple($remove);
            foreach ($houses as $house) {
                $relationships = $house->getRelationshipsByEntity($node);
                if (!empty($relationships)) {
                    array_walk($relationships, function (GroupRelationshipInterface $rel) {
                        $rel->delete();
                    });
                }
            }
        }
    }

    protected function processHouseChangeData(Node $node)
    {
        $existingHouses = array_column($node->original->get('field_groups')->getValue(), 'target_id');
        $newHouses = [];
        $isChasCentralEvent = (bool) $node->get('field_flag')->getString();
        if ($node->getType() === Constant::NODE_EVENT && !$isChasCentralEvent) {
            $newHouses = [(int) $node->get('field_location')->getString()];
            if ($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED) {
                $newHouses = array_column($node->get('field_groups')->getValue(), 'target_id');
            }
        } else if ($node->getType() === Constant::NODE_EVENT || $node->getType() === Constant::NODE_NOTICES) {
            $newHouses = array_column($node->get('field_groups')->getValue(), 'target_id');
        }


        return $this->getHousesDifference($existingHouses, $newHouses);
    }

    private function getHousesDifference(array $existingHouses, array $newHouses)
    {
        if (!$newHouses) {
            return [
                'new' => [],
                'remove' => []
            ];
        }

        $existingHouses = array_flip($existingHouses);

        $newHousesRel = [];
        $houseRelDel = [];

        foreach ($newHouses as $houseId) {
            if (isset($existingHouses[$houseId])) {
                unset($existingHouses[$houseId]);
            } else {
                $newHousesRel[] = $houseId;
            }
        }

        if ($existingHouses) {
            $houseRelDel = array_flip($existingHouses);
        }

        return [
            'new' => $newHousesRel,
            'remove' => $houseRelDel
        ];
    }
}
