<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

class EventFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $options = $this->getOptions();
        $form['field_college_houses']['widget']['#options'] = $options['houses'];
        $form['field_program_communities']['widget']['#options'] = $options['communities'];
        $form['field_location']['widget']['#options'] = $options['houses'];
        $houseCount = count($form['field_college_houses']['widget']['#options']);
        if ($houseCount > 1) {
            $form['#attached']['library'][] = 'pennchas_form_alter/eventListeners';
        }

        if ($form['#form_id'] === 'node_chas_event_form') {
            $form['terms_condition'] = [
                '#type' => 'checkbox',
                '#title' => [
                    '#markup' => t('I have read and understand the <a href="#">policies</a> associated with reserving rooms in this College House.')
                ],
                '#required' => TRUE,
                '#weight' => 100
            ];
        }
        return $form;
    }

    protected function getOptions()
    {
        $houseOptions = [];
        $communityOptions = [];

        $currentUser = \Drupal::currentUser();
        $groupMemberships = GroupMembership::loadByUser($currentUser);
        if ($groupMemberships) {
            foreach ($groupMemberships as $groupMembership) {
                $gid = $groupMembership->get('gid')->getString();
                $group = Group::load($gid);

                $this->_getHouses($houseOptions, $group);
                $this->_getCommunities($communityOptions, $group);
            }
        } else {
            $groupsId =  \Drupal::entityQuery('group')
                ->condition('type', 'house1')
                ->condition('status', 1)->accessCheck(true)->execute();
                
            $groups = Group::loadMultiple($groupsId);

            foreach ($groups as $group) {
                $this->_getHouses($houseOptions, $group);
                $this->_getCommunities($communityOptions, $group);
            }
        }
        return [
            'houses' => $houseOptions,
            'communities' => $communityOptions
        ];
    }

    protected function _getHouses(&$options, $group)
    {
        $options[$group->id()] = $group->label();
    }

    protected function _getCommunities(&$options, $group)
    {
        $relationships = $group->getRelationships('group_node:program_community');
        foreach ($relationships as $rel) {
            $options[$rel->id()] = "{$group->label()} - {$rel->label()}";
        }
    }
}
