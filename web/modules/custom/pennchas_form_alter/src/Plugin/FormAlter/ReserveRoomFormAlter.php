<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\node\Entity\Node;

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
        if ($form['#form_id'] === 'node_reserve_room_form') {
            $this->alterAddForm($form);
        } else {
            $this->alterEditForm($form, $formState);
        }
        $form['actions']['submit']['#value'] = t('Send Request');
        $form['#attached']['library'][] = 'pennchas_form_alter/customSamrtDate';
        return $form;
    }

    /**
     * Handles modifications for the reserve_room add form.
     *
     * @param array $form
     *   The form array.
     */
    private function alterAddForm(array &$form)
    {
        $group = \Drupal::routeMatch()->getParameter('group');
        if ($group && $group instanceof Group) {
            $this->alterRoomField($group, $form['field_room']['widget']);
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
     */
    private function alterEditForm(array &$form, FormStateInterface $formState)
    {
        $node  = $formState->getFormObject()->getEntity();
        $groupId = (int) $node->get('field_group')->getString();

        // Load the group entity and update the room options.
        $group = Group::load($groupId);
        if ($group) {
            $this->alterRoomField($group, $form['field_room']['widget']);
        }

        // Remove all but the first event schedule widget.
        foreach ($form['field_event_schedule']['widget'] as $key => $value) {
            if (is_numeric($key) && $key !== 0) {
                unset($form['field_event_schedule']['widget'][$key]);
            }
        }
    }


    /**
     * Alters the room field for a given group.
     *
     * @param \Drupal\group\Entity\Group $group
     *   The group entity whose rooms and label are used to alter the field.
     * @param array &$field
     *   The field array to be altered. This is passed by reference.
     */
    private function alterRoomField(Group $group, array &$field)
    {
        $field['#options'] = $this->_getGroupRooms($group->id());
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
     * @param int $groupId
     *   The group ID.
     *
     * @return array
     *   An associative array of room options, formatted as [node_id => title].
     */
    private function _getGroupRooms(int $groupId)
    {
        $roomsIds = \Drupal::entityQuery('node')
            ->accessCheck(TRUE)
            ->condition('type', 'room')
            ->condition('status', 1)
            ->condition('field_group', $groupId)
            ->sort('title', 'ASC')
            ->execute();

        $rooms = Node::loadMultiple($roomsIds);

        $roomOptions = [
            '' => t('Select Your Room')
        ];
        foreach ($rooms as $room) {
            $roomOptions[$room->id()] = $room->getTitle();
        }
        return $roomOptions;
    }
}
