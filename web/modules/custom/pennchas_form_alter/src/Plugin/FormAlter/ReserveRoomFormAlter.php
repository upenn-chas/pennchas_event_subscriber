<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\node\Entity\Node;
use Drupal\pennchas_form_alter\Util\Constant;

class ReserveRoomFormAlter
{
    /**
     * Alters the reservation form.
     *
     * @param array $form
     *   The form array.
     * @param \Drupal\Core\Form\FormStateInterface $formState
     *   The form state.
     */
    public function alter(array $form, FormStateInterface $formState)
    {
        unset($form['field_event_schedule']['widget']['add_more']);
        $currentUser = \Drupal::currentUser();
        $form['moderation_state']['#access'] = FALSE;
        if ($form['#form_id'] === 'node_reserve_room_form') {
            $this->alterAddForm($form, $currentUser);
        } else {
            $this->alterEditForm($form, $formState, $currentUser);
        }

        $index = array_search('group_relationship_entity_submit', $form['actions']['submit']['#submit']);
        if ($index !== FALSE) {
            unset($form['actions']['submit']['#submit'][$index]);
        }

        $form['actions']['submit']['#value'] = t('Send Request');
        $form['#attached']['library'][] = 'pennchas_form_alter/custom_smart_date';
        return $form;
    }

    /**
     * Handles modifications for the reserve_room add form.
     *
     * @param array $form
     *   The form array.
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user account.
     */
    private function alterAddForm(array &$form, AccountInterface $user)
    {
        $group = \Drupal::routeMatch()->getParameter('group');
        if ($group && $group instanceof Group) {
            $this->alterRoomField($group, $form['field_room']['widget'], $user);
        } else {
            $form['field_room']['widget']['#options']['_none'] = t('Select Your Room');
        }

        $form['terms_condition'] = [
            '#type' => 'checkbox',
            '#title' => t('I have read and understand the <a href="/policies">policies associated</a> with reserving rooms in this College House.'),
            '#required' => TRUE,
            '#weight' => 100
        ];
    }


    /**
     * Handles modifications of reserve_room edit form.
     *
     * @param array $form
     *   The form array.
     * @param \Drupal\Core\Form\FormStateInterface $formState
     *   The form state.
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user account.
     */
    private function alterEditForm(array &$form, FormStateInterface $formState, AccountInterface $user)
    {
        $node  = $formState->getFormObject()->getEntity();
        $groupId = (int) $node->get('field_group')->getString();

        // Load the group entity and update the room options.
        $group = Group::load($groupId);
        if ($group) {
            $this->alterRoomField($group, $form['field_room']['widget'], $user);
        }

        $eventEndsOn = (int) $node->get('field_event_ends_on')->getString();
        if ($eventEndsOn < time()) {
            $form['field_event_schedule']['widget'][0]['#disabled'] = TRUE;
            $form['field_room']['widget']['#disabled'] = TRUE;
        }
        // Remove all but the first event schedule widget.
        foreach ($form['field_event_schedule']['widget'] as $key => $value) {
            if (is_numeric($key) && $key !== 0) {
                unset($form['field_event_schedule']['widget'][$key]);
            }
        }

         if($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED && \Drupal::currentUser()->hasPermission('use editorial transition unpublished')) {
                $form['moderation_state']['#access'] = TRUE;
            }
    }


    /**
     * Alters the room field for a given group.
     *
     * @param \Drupal\group\Entity\Group $group
     *   The group entity whose rooms and label are used to alter the field.
     * @param array &$field
     *   The field array to be altered. This is passed by reference.
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user account.
     */
    private function alterRoomField(Group $group, array &$field, AccountInterface $user)
    {
        $field['#options'] = $this->_getGroupRooms($user, $group);
        $field['#title'] .= " at {$group->label()}";
        $roomPageUrl = $group->get('field_rooms_page')->getString();
        if ($roomPageUrl) {
            $roomPageUrl = Url::fromUri($roomPageUrl, ['absolute' => true])->toString();
            $descriptionText = $field['#description']->__toString();
            $field['#description'] = [
                '#markup' => '<a href="' . $roomPageUrl . '" target="_blank"><span class="house-room-link">' . $descriptionText . '</span></a>'
            ];
        }
    }

    /**
     * Retrieves a list of available rooms for a given group.
     *
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user account.
     * @param \Drupal\group\Entity\Group $group
     *   The group.
     *
     * @return array
     *   An associative array of room options, formatted as [node_id => title].
     */

    private function _getGroupRooms(AccountInterface $user, Group $group)
    {
        $roomRelationships = $group->getRelationships('group_node:room');
        $rooms = [];
        $umbrellaRoles = $this->getUmbrellaRoles($user, $group);

        foreach ($roomRelationships as $relationship) {
            $entity = $relationship->getEntity();
            if ($entity->isPublished() && $this->isRoomAllowToBook($entity, $user, $umbrellaRoles)) {
                $rooms[$entity->id()] = $entity->getTitle();
            }
        }
        asort($rooms);
        return ['' => t('Select Your Room')] + $rooms;
    }

    private function isRoomAllowToBook(Node $room, AccountInterface $user, array $umbrellaRoles)
    {   
        $roomAvailableTo = $room->get('field_available_to')->getString();
        return $user->hasRole('administrator') || isset($umbrellaRoles[$roomAvailableTo]);
    }

    private function getUmbrellaRoles(AccountInterface $user, Group $group)
    {
        $state = \Drupal::state();
        $roles = $user->getRoles() ?? [];
        // Fetch user roles in the group, if applicable.
        if ($group && ($membership = $group->getMember($user))) {
            $groupRoles = $membership->getRoles();
            $roles = array_merge($roles, array_keys($groupRoles));
        }

        $umbrellaRoles = [];

        // Check if any of the user's roles have permission for the selected room.
        foreach ($roles as $role) {
            $roleConfig = $state->get('custom_group_role_config_' . $role);
            if ($roleConfig) {
                $umbrellaRoles = array_merge($umbrellaRoles, $roleConfig);
            }
        }
        return $umbrellaRoles;
    }
}
