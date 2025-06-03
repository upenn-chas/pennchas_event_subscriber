<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\group\Entity\GroupType;
use Drupal\user\Entity\Role;

class EventFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $currentUser = \Drupal::currentUser();
        $options = $this->getOptions($currentUser);
        $form['field_multi_text']['widget']['add_more']['#value'] = t('Add Another Collaborator');
        $form['field_college_houses']['widget']['#options'] = $options['houses'];
        $form['field_program_communities']['widget']['#options'] = $options['communities'];
        $form['field_location']['widget']['#options'] = $options['houses'];
        $houseCount = count($options['houses']);
        // if ($houseCount > 1) {
        // $form['#attached']['library'][] = 'pennchas_form_alter/eventListeners';
        // $form['field_college_houses']['widget']['#options'] = ['_none' => t('All college houses')] + $options['houses'];
        // }
        if ($houseCount > 1) {
            $form['#attached']['library'][] = 'pennchas_form_alter/eventListeners';
        }

        $form['field_flag']['#access'] = FALSE;
        $form['field_location']['widget']['#required'] = TRUE;
        if (\Drupal::service('pennchas_common.access_check')->checkForNonGroupMember('chas central event')) {
            $form['field_flag']['#access'] = TRUE;
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
            $entity = $formState->getFormObject()->getEntity();
            $currentUser = $entity->getOwner();
            $eventEndsOn = (int) $entity->get('field_event_ends_on')->getString();

            if ($eventEndsOn < time()) {
                $form['field_event_schedule']['widget'][0]['#disabled'] = TRUE;
            }
        }
        $index = array_search('group_relationship_entity_submit', $form['actions']['submit']['#submit']);
        if ($index !== FALSE) {
            unset($form['actions']['submit']['#submit'][$index]);
        }

        $userDetails = $this->getUserDetails($currentUser);
        $form['form_caption'] = [
            '#markup' => "<div class='author-details-container mb-3'><span class='author-label'>Author:&nbsp;</span><span class='author-details'>{$userDetails}</span></div>",
            '#weight' => -100, // Ensures it appears at the top
        ];

        $form['#attached']['library'][] = 'pennchas_form_alter/custom_smart_date';
        $form['#validate'][] = [$this, 'customValidator'];
        return $form;
    }

    public function customValidator(array $form, FormStateInterface $formState)
    {
        $values = $formState->getValues();
        if (empty($values['field_location'][0]['target_id']) && $values['field_flag']['value'] === 0) {
            $formState->setErrorByName('field_location', t('%field is required.', [
                '%field' => $form['field_location']['widget']['#title']
            ]));
        }
    }

    protected function getOptions(AccountInterface $currentUser)
    {
        $houseOptions = [];
        $communityOptions = [];

        $groupService = \Drupal::service('pennchas_common.option_group');
        $houseOptions = $groupService->options('house1');

        if ($houseOptions) {
            $groups = Group::loadMultiple(array_keys($houseOptions));
            foreach ($groups as $group) {
                $this->_getCommunities($communityOptions, $group);
            }
        }
        asort($communityOptions, SORT_STRING);

        return [
            'houses' => $houseOptions,
            'communities' => $communityOptions
        ];
    }

    protected function _getCommunities(&$options, $group)
    {
        $relationships = $group->getRelationships('group_node:program_community');
        foreach ($relationships as $rel) {
            // $options[$rel->getEntityId()] = "{$group->label()} - {$rel->label()}";
            $options[$rel->id()] = "{$group->label()} - {$rel->label()}";;
        }
    }

    private function getUserDetails(AccountInterface $user)
    {
        $userName = $user->getDisplayName();
        $roles = [];

        $allRoles = $this->getAllRoles();
        $userRoles = $user->getRoles(TRUE);
        foreach ($userRoles as $roleId) {
            if ($roleId !== 'content_editor') {
                $roles[$roleId] = $allRoles[$roleId];
            }
        }

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
