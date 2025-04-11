<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;

class HouseFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $form['field_select_house']['widget']['#default_value'] = $this->getHouseId();
        $index = array_search('group_relationship_entity_submit', $form['actions']['submit']['#submit']);
        if ($index !== FALSE) {
            unset($form['actions']['submit']['#submit'][$index]);
        }
        return $form;
    }

    private function getHouseId()
    {
        $group = \Drupal::routeMatch()->getParameter('group');
        if ($group && $group instanceof Group) {
            return $group->id();
        }
        return null;
    }
}
