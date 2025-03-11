<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\group\Entity\GroupType;
use Drupal\user\Entity\Role;

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
        unset($form['field_event_schedule']['widget']['add_more']);

        if ($form['#form_id'] === 'node_chas_event_form') {
            $form['terms_condition'] = [
                '#type' => 'checkbox',
                '#title' => t('I have read and understand the <a href="/policies">policies</a> associated with reserving rooms in this College House.'),
                '#required' => TRUE,
                '#weight' => 100
            ];
        } else {
            foreach ($form['field_event_schedule']['widget'] as $key => $value) {
                if (is_numeric($key) && $key !== 0) {
                    unset($form['field_event_schedule']['widget'][$key]);
                }
            }
        }
        $index = array_search('group_relationship_entity_submit', $form['actions']['submit']['#submit']);
        if ($index !== FALSE) {
            unset($form['actions']['submit']['#submit'][$index]);
        }

        $userDetails = $this->getUserDetails();
        $form['form_caption'] = [
            '#markup' => "<div class='author-details-container mb-3'><span class='author-label'>Author:&nbsp;</span><span class='author-details'>{$userDetails}</span></div>",
            '#weight' => -100, // Ensures it appears at the top
        ];
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

    private function getUserDetails()
    {
        $user = \Drupal::currentUser();
        $userName = $user->getDisplayName();
        $roles = [];

        $groupMemberships = GroupMembership::loadByUser($user);
        if ($groupMemberships) {
            $groupRoles = $this->getAllGroupRoles();
            foreach ($groupMemberships as $groupMembership) {
                $userGroupRoles = $groupMembership->getRoles(FALSE);
                foreach ($userGroupRoles as $role) {
                    $roleId = $role->id();
                    $roles[$roleId] = $groupRoles[$roleId];
                }
            }
        } else {
            $allRoles = $this->getAllRoles();
            $userRoles = $user->getRoles(TRUE);
            foreach ($userRoles as $roleId) {
                $roles[$roleId] = $allRoles[$roleId];
            }

        }
        return "{$userName} ({$this->implodeWithAnd($roles)})";
    }

    private function getAllGroupRoles()
    {
        $roles = [];
        $groupType = GroupType::load('house1');
        $groupRoles = $groupType->getRoles(FALSE);
        foreach ($groupRoles as $role) {
            $roles[$role->id()] = $role->label();
        }
        return $roles;
    }

    private function getAllRoles()
    {
        $roles = [];
        $rolesData = Role::loadMultiple();
        foreach ($rolesData as $role) {
            $roles[$role->id()] = $role->label();
        }
        return $roles;
    }

    private function implodeWithAnd(array $array)
    {
        $last = array_pop($array);
        if ($array) {
            return implode(', ', $array) . ' and ' . $last;
        }
        return $last;
    }
}
