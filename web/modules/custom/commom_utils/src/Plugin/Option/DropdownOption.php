<?php
namespace Drupal\common_utils\Plugin\Option;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

class DropdownOption
{
    public static function getGroups()
    {
        return  \Drupal::service('pennchas_common.option_group')->options('house1');
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

    public static function getBooleanOptions()
    {
        return [
            1 => t('Yes'),
            0 => t('No')
        ];
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