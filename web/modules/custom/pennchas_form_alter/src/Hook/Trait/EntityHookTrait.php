<?php

namespace Drupal\pennchas_form_alter\Hook\Trait;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

trait EntityHookTrait
{
    protected function canByPassModeration(Group|null $group, $permission): bool
    {
        $currentUser = \Drupal::currentUser();
        return ($group) ? $group->hasPermission($permission, $currentUser) : $currentUser->hasPermission($permission);
    }

    protected function canByPassModerationInAnyHouse(array $gids, $permission): bool
    {
        $result = false;
        if (!$gids) {
            return $result;
        }
        $groups = Group::loadMultiple($gids);
        foreach ($groups as $group) {
            $result = $this->canByPassModeration($group, $permission);
            if ($result) {
                break;
            }
        }
        return $result;
    }

    protected function updateEventEndsOn(Node $node)
    {
        $eventSchedules = $node->get('field_event_schedule')->getValue();
        $count = count($eventSchedules);
        $eventLastDay = $eventSchedules[$count - 1];
        $node->set('field_event_ends_on', $eventLastDay['end_value']);
    }

    protected function getHouses(Node $node)
    {
        $eventHouseId = (int) $node->get('field_location')->getString();
        $eventType = $node->get('field_intended_audience')->getString();
        $housesId[$eventHouseId] = $eventHouseId;

        if ($eventType === Constant::EVT_COMMUNITY_EVENT) {
            $programsGroupRelationshipsId = array_column($node->get('field_program_communities')->getValue(), 'target_id');
            $programsGroupRelationship = \Drupal::entityTypeManager()
                ->getStorage('group_relationship')
                ->loadMultiple($programsGroupRelationshipsId);
            foreach ($programsGroupRelationship as $gr) {
                $gid = (int) $gr->get('gid')->getString();
                $housesId[$gid] = $gid;
            }
        } else if ($eventType === Constant::EVT_HOUSE_EVENT) {
            $participantsHouses = $node->get('field_college_houses')->getValue();
            foreach ($participantsHouses as $grp) {
                $gid = (int) $grp['target_id'];
                $housesId[$gid] = $gid;
            }
        }

        return array_values($housesId);
    }


    protected function getHouseMaxModerationWaitingPeriod(Group|null $group)
    {
        $houseMaxModerationWaitingPeriod = 3;
        if ($group && $group->hasField('field_waiting_period')) {
            $houseMaxModerationWaitingPeriod = $group->get('field_waiting_period')->getString();
            $houseMaxModerationWaitingPeriod = $houseMaxModerationWaitingPeriod ? (int) $houseMaxModerationWaitingPeriod : 3;
        }
        return $houseMaxModerationWaitingPeriod;
    }

    protected function isMovedForModeration(NodeInterface $node)
    {
        $previousState = $node->original->get('moderation_state')->getString();
        $currentState = $node->get('moderation_state')->getString();

        return $previousState === Constant::MOD_STATUS_PUBLISHED && $currentState === Constant::MOD_STATUS_DRAFT;
    }
}
