<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\group_test_plugin\Plugin\Group\Relation\GroupRelation;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeInsertHook
{
    use EntityHookTrait;

    function handle(NodeInterface $node)
    {
        // dd($node->get('layout_builder__layout')->getValue());
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_EVENT && (bool) $node->get('field_flag')->getString()) {
            $this->handleChasCentralEvent($node);
        } else if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        } else if ($nodeType === Constant::NODE_NOTICES) {
            $this->handleNotice($node);
        } else if ($nodeType === Constant::NODE_ROOM) {
            $this->handleRoom($node);
        } else if ($nodeType === Constant::NODE_HOUSE_PAGE) {
            $this->handleHousePage($node);
        } else if ($nodeType === Constant::NODE_ARTICLE) {
            $this->handleArticle($node);
        }
    }

    public function handleEvent(Node $node): void
    {
        $eventState = $node->get('moderation_state')->getString();
        $eventLocationHouseId = (int) $node->get('field_location')->getString();
        $roomReservationUrl =  Url::fromRoute('entity.group_relationship.create_form', ['group' => $eventLocationHouseId, 'plugin_id' => 'group_node:reserve_room'])->toString();
        $groupIds = [$eventLocationHouseId];

        if ($eventState === Constant::MOD_STATUS_PUBLISHED) {
            $eventHousesId = $node->get('field_groups')->getValue();
            $groupIds = array_column($eventHousesId, 'target_id');
        }
        $eventHouses = Group::loadMultiple($groupIds);
        foreach ($eventHouses as $house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
        $roomReservationMessage = "Do you need a room reservation? <a href='{$roomReservationUrl}'>click here</a>";
        $message = t('Your event has been accepted and published.' . ' ' . $roomReservationMessage);
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        if ($eventState === Constant::MOD_STATUS_PUBLISHED) {
            $mailService->notifyAuthor($node, Constant::EVENT_EMAIL_MODERATOR_CREATED, null);
        } else {
            $group = array_shift($eventHouses);
            $moderationWaitingDays =  $this->getHouseMaxModerationWaitingPeriod($group);
            $message = t('Your event has been submited and there is a possible ' . $moderationWaitingDays . ' day(s) wait time for approval.' . ' ' . $roomReservationMessage);
            $mailService->notifyAuthor($node, Constant::EVENT_EMAIL_CREATED, $group);
            $mailService->notifyModerators($node, Constant::EVENT_EMAIL_MODERATOR_ALERT, $group);
        }
        \Drupal::messenger()->addStatus($message);
    }

    public function handleChasCentralEvent(Node $node): void
    {
        $eventState = $node->get('moderation_state')->getString();
        
        $eventHousesId = $node->get('field_groups')->getValue();
        $groupIds = array_column($eventHousesId, 'target_id');

        $eventHouses = Group::loadMultiple($groupIds);
        foreach ($eventHouses as $house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }

        $message = t('Your event has been accepted and published.');
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        if ($eventState === Constant::MOD_STATUS_PUBLISHED) {
            $mailService->notifyAuthor($node, Constant::EVENT_EMAIL_MODERATOR_CREATED, null);
        } else {
            $moderationWaitingDays = \Drupal::service('config_pages.loader')->getValue('chas_moderator', 'field_waiting_period', [], 'value');
            $moderationWaitingDays = $moderationWaitingDays[0];
            $message = t('Your event has been submited and there is a possible ' . $moderationWaitingDays . ' day(s) wait time for approval.');
            $mailService->notifyAuthor($node, Constant::EVENT_EMAIL_CREATED, null, $moderationWaitingDays);
            $mailService->notifyModerators($node, Constant::EVENT_EMAIL_MODERATOR_ALERT, null);
        }
        \Drupal::messenger()->addStatus($message);
    }

    protected function handleNotice(Node $node)
    {
        $eventHousesId = $node->get('field_groups')->getValue();
        $groupIds = array_column($eventHousesId, 'target_id');
        $eventHouses = Group::loadMultiple($groupIds);
        foreach ($eventHouses as $house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        $mailService->notifyAuthor($node, Constant::NOTICE_EMAIL_CREATED, null);
    }

    protected function handleReserveRoom(Node $node)
    {
        $groupId = (int) $node->get('field_group')->getString();
        $group = Group::load($groupId);
        if ($group) {
            $existingRelationship = $group->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $group->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        $message = t('Your request has been accepted.');
        if ($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED) {
            $mailService->notifyAuthor($node, Constant::RESERVER_ROOM_EMAIL_MODERATOR_CREATED, null);
        } else {
            $moderationWaitingDays = $this->getHouseMaxModerationWaitingPeriod($group);
            $message = t('Your request has been submitted and there is a possible ' . $moderationWaitingDays . ' day(s) wait time for approval.');
            $mailService->notifyAuthor($node, Constant::RESERVER_ROOM_EMAIL_CREATED, $group);
            $mailService->notifyModerators($node, Constant::RESERVER_ROOM_EMAIL_MODERATOR_ALERT, $group);
        }
        \Drupal::messenger()->addStatus($message);
    }

    protected function handleRoom(Node $node)
    {
        $node->set('field_qr_code', $node->toUrl('canonical', ['absolute' => true])->toString());
        $node->setNewRevision(false);
        $node->save();
    }

    protected function handleHousePage(Node $node)
    {
        $houseId = (int) $node->get('field_select_house')->getString();
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
        $groupId = (int) $node->get('field_location')->getString();
        if ($groupId) {
            $group = Group::load($groupId);
            $existingRelationship = $group->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $group->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
    }
}
