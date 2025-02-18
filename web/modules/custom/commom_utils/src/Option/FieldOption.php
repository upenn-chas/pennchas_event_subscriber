<?php

namespace Drupal\common_utils\Option;

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
        $fieldDefinition = $node->get('field_intended_outcomes');
        $selectedValues = $fieldDefinition->getValue();
        $fieldSettings = $fieldDefinition->getDataDefinition()->getSettings();
        $allowedValues = $fieldSettings['allowed_values'];

        $data = [];

        foreach ($selectedValues as $value) {
            $data[] = $allowedValues[$value['value']];
        }

        return $data;
    }
}
