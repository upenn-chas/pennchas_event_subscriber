<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeInsertHook
{
    function handle(NodeInterface $node)
    {
        // dd($node->get('layout_builder__layout')->getValue());
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        } else if ($nodeType === Constant::NODE_NOTICES) {
            $this->handleNotice($node);
        } else if ($nodeType === Constant::NODE_ROOM) {
            $this->handleRoom($node);
        } else if ($nodeType === Constant::NODE_HOUSE_PAGE) {
            $this->handleHousePage($node);
        }
    }

    public function handleEvent(Node $node): void
    {
        \Drupal::messenger()->deleteAll();

        $eventHousesId = $node->get('field_groups')->getValue();
        $eventHouses = Group::loadMultiple(array_column($eventHousesId, 'target_id'));
        foreach ($eventHouses as $house) {
            // $node_url = $house->get('field_house_machine_name')->value.'/'.$node->getTitle();
            // \Drupal::service('path.alias_storage')->save([
            //     'alias' => '/' . $node_url,
            //     'source' => '/events/' . $node->id()
            //   ]);
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
        $eventLocationHouseId = (int) $node->get('field_location')->getString();
        $roomReservationMessage = 'Do you need a room reservation? <a href="'. Url::fromRoute('entity.group_relationship.create_form', ['group' => $eventLocationHouseId, 'plugin_id' => 'group_node:reserve_room'])->toString() .'">click here</a>';
        $message = t('Your event has been submited and there is a possible three day wait time for approval.' . ' ' . $roomReservationMessage);
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        if ($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED) {
            $message = t('Your event has been accepted and published.' . ' ' . $roomReservationMessage);
            $mailService->notifyAuthor(Constant::EVENT_EMAIL_MODERATOR_CREATED, $node);
        } else {
            $mailService->notifyAuthor(Constant::EVENT_EMAIL_CREATED, $node);
            $mailService->notifyModerators(Constant::EVENT_EMAIL_MODERATOR_ALERT, $node, $eventHouses[0]);
        }
        \Drupal::messenger()->addStatus($message);
    }

    protected function handleNotice(Node $node)
    {
        $eventHousesId = $node->get('field_groups')->getValue();
        $eventHouses = Group::loadMultiple(array_column($eventHousesId, 'target_id'));
        foreach ($eventHouses as $house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        $mailService->notifyAuthor(Constant::NOTICE_EMAIL_CREATED, $node);
    }

    protected function handleReserveRoom(Node $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        $mailService = \Drupal::service('pennchas_form_alter.moderation_entity_email_service');
        $message = t('Your request has been submitted and there is a possible three day wait time for approval.');
        if ($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED) {
            $message = t('Your request has been accepted.');
            $mailService->notifyAuthor(Constant::RESERVER_ROOM_EMAIL_MODERATOR_CREATED, $node);
        } else {
            $mailService->notifyAuthor(Constant::RESERVER_ROOM_EMAIL_CREATED, $node);
            $mailService->notifyModerators(Constant::RESERVER_ROOM_EMAIL_MODERATOR_ALERT, $node, $group);
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
        if($house) {
            $existingRelationship = $house->getRelationshipsByEntity($node);
            if (empty($existingRelationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }
    }
}
