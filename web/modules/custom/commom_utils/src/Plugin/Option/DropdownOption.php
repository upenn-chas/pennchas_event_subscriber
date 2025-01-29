<?php
namespace Drupal\common_utils\Plugin\Option;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

class DropdownOption
{
    public static function getGroups()
    {
        $options = [];

        $currentUser = \Drupal::currentUser();
        $groupMemberships = GroupMembership::loadByUser($currentUser);
        if ($groupMemberships) {
            foreach ($groupMemberships as $groupMembership) {
                $gid = $groupMembership->get('gid')->getString();
                $group = Group::load($gid);
                $options[$group->id()] = $group->label();
            }
        } else {
            $groupsId =  \Drupal::entityQuery('group')
                ->condition('type', 'house1')
                ->condition('status', 1)->accessCheck(true)->execute();
            $groups = Group::loadMultiple($groupsId);

            foreach ($groups as $group) {
                $options[$group->id()] = $group->label();
            }
        }
        return $options;
    }

    public static function getEventIntendedAudience()
    {
        return self::getFieldAllowedValues('node', 'chas_event', 'field_intended_audience');
    }

    public static function getEventIntendedParticipantYears()
    {
        return self::getFieldAllowedValues('node', 'chas_event', 'field_participants');
    }

    public static function getEventIntendedOutcomes()
    {
        return self::getFieldAllowedValues('node', 'chas_event', 'field_intended_outcomes');
    }

    public static function getEventGoalAreas()
    {
        return self::getTerms('chas_priority');
    }

    protected static function getFieldAllowedValues($entityType, $bundle, $fieldName)
    {
        $options = [];
        $fieldDefinitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entityType, $bundle);
        if (isset($fieldDefinitions[$fieldName])) {
            $settings = $fieldDefinitions[$fieldName]->getSettings();
            $options = $settings['allowed_values'] ?? [];
        }
        return $options;
    }

    protected static function getTerms($vocabulary)
    {
        $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties(['vid' => $vocabulary]);

        $options = [];
        foreach ($terms as $term) {
            /** @var \Drupal\taxonomy\Entity\Term $term */
            $options[$term->id()] = $term->getName();
        }

        return $options;
    }
}