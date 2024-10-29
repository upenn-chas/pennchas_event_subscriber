<?php

namespace Drupal\node_event_form_ext\Plugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationshipInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\webform\Entity\Webform;

class EventCreateAndUpdate
{
    public function __construct() {}

    public function handlePreSaveEvent(NodeInterface $node)
    {
        $eventFeedbackWebformId = 'event_feedback';
        $node->set('field_feedback_form', [
            'target_id' => $eventFeedbackWebformId
        ]);
        $houses = $node->get('field_college_houses')->getValue();
        if ($this->canByPassModeration(array_column($houses, 'target_id'))) {
            $node->setPublished(true);
            $node->set('moderation_state', 'published');
        } else {
            $node->setPublished(false);
            $node->set('moderation_state', 'draft');
        }
        $this->updateEventEndsOn($node);
    }

    public function handlePreUpdateEvent(NodeInterface $node)
    {
        $this->updateEventEndsOn($node);
    }

    public function handleEventInsert(Node $node): void
    {
        \Drupal::messenger()->deleteAll();

        $houses = $node->get('field_college_houses')->getValue();
        foreach ($houses as $house_details) {
            $house = Group::load($house_details['target_id']);
            $existing_relationship = $house->getRelationshipsByEntity($node);
            if (empty($existing_relationship)) {
                $house->addRelationship($node, 'group_node:' . $node->getType());
            }
        }

        $message = t('Your event has been accepted and published.');
        if (!$this->canByPassModeration(array_column($houses, 'target_id'))) {
            $message = t('Your event has been submitted and there is a possible three day wait time for approval.');
            $mailService = \Drupal::service('node_event_form_ext.mail_service');
            $mailService->notify($node);
        }

        \Drupal::messenger()->addStatus($message);
    }

    public function handleEventUpdate(Node $node): void
    {
        $this->handleEventHousesUpdate($node);
        $this->handleModerationStateChange($node);
    }

    function handleEventHousesUpdate(Node $node): void
    {
        $result = $this->processHouseChangeData($node);

        if ($result['new']) {
            foreach ($result['new'] as $house_id) {
                $house = Group::load($house_id);
                $existing_relationship = $house->getRelationshipsByEntity($node);
                if (empty($existing_relationship)) {
                    $house->addRelationship($node, 'group_node:' . $node->getType());
                }
            }
        }
        if ($result['remove']) {
            foreach ($result['remove'] as $house_id) {
                $house = Group::load($house_id);
                $relationships = $house->getRelationshipsByEntity($node);
                if (!empty($relationships)) {
                    array_walk($relationships, function (GroupRelationshipInterface $rel) {
                        $rel->delete();
                    });
                }
            }
        }
    }

    function processHouseChangeData(Node $node)
    {
        $existing_houses = $node->original->get('field_college_houses')->getValue();
        $new_houses = $node->get('field_college_houses')->getValue();

        $existing_houses_id = array_flip(array_column($existing_houses, 'target_id'));
        $new_houses_id = array_column($new_houses, 'target_id');

        $new_houses_rel = [];
        $house_rel_del = [];

        foreach ($new_houses_id as $house_id) {
            if (isset($existing_houses_id[$house_id])) {
                unset($existing_houses_id[$house_id]);
            } else {
                $new_houses_rel[] = $house_id;
            }
        }

        if ($existing_houses) {
            $house_rel_del = array_flip($existing_houses_id);
        }

        return [
            'new' => $new_houses_rel,
            'remove' => $house_rel_del
        ];
    }

    function updateEventEndsOn(NodeInterface $node)
    {
        $eventSchedules = $node->get('field_event_schedule')->getValue();
        $count = count($eventSchedules);
        $eventLastDay = $eventSchedules[$count - 1];
        $node->set('field_event_ends_on', $eventLastDay['end_value']);
    }

    function handleModerationStateChange(Node $node): void
    {
        $oldModerationState = $node->original->get('moderation_state')->getString();
        $newModerationState = $node->get('moderation_state')->getString();

        if ($oldModerationState !== $newModerationState) {
            $mailService = \Drupal::service('node_event_form_ext.mail_service');
            $mailService->notifyOwnerAboutModeration($node, $newModerationState);
        }
    }

    function canByPassModeration($groupIds): bool
    {
        $currentUser = \Drupal::currentUser();
        $group_memberships = \Drupal::service('group.membership_loader')->loadByUser($currentUser);
        if (count($group_memberships) > 0) {
            foreach ($groupIds as $groupId) {
                $group = Group::load($groupId);
                if ($group->hasPermission('use editorial transition publish', $currentUser)) {
                    return true;
                }
            }
            return false;
        }

        return $currentUser->hasPermission('use editorial transition publish');
    }
}
