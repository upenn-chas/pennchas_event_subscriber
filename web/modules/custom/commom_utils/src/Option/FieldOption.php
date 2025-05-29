<?php

namespace Drupal\common_utils\Option;

use Drupal\field\Entity\FieldConfig;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\node\NodeInterface;

/**
 * Get list of values label for given field.
 */
class FieldOption
{

    /**
     * Get the list of value lables for select fields.
     * 
     * @param \Drupal\node\NodeInterface $node
     *  The noed from which to get value.
     * 
     * @param string $field
     *  The field for which value to get.
     * 
     * @return array []
     *  List of values label.
     */
    public function values(NodeInterface $node, string $field)
    {
        $fieldDefinition = $node->get($field);
        $selectedValues = $fieldDefinition->getValue();
        $fieldSettings = $fieldDefinition->getDataDefinition()->getSettings();
        $allowedValues = $fieldSettings['allowed_values'];

        $data = [];

        if($allowedValues) {
            foreach ($selectedValues as $value) {
                $data[] = $allowedValues[$value['value']];
            }
        }

        return $data;
    }


    /**
     * Retrieves the allowed values for a specific field in a given node bundle.
     *
     * @param string $bundle
     *   The machine name of the content type (node bundle).
     * @param string $field
     *   The machine name of the field.
     *
     * @return array
     *   An array of allowed values for the specified field, or an empty array if none are found.
     */
    public function getNodeFieldAllowedValues(string $bundle, string $field)
    {
        $config = FieldConfig::loadByName('node', $bundle, $field);
        if (!$config) {
            return [];
        }
        return $config->getSetting('allowed_values');
    }
}
