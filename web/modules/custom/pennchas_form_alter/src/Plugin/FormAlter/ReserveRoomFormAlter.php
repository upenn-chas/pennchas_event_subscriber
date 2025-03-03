<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\node\Entity\Node;

class ReserveRoomFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {

        unset($form['field_event_schedule']['widget']['add_more']);

        if ($form['#form_id'] === 'node_reserve_room_form') {

            $group = \Drupal::routeMatch()->getParameter('group');
            if (isset($group)) {
                $form['field_room']['widget']['#options'] = $this->_getGroupRooms($group->id());
                $form['field_room']['widget']['#title'] .= " at {$group->label()}";
            }

            $form['terms_condition'] = [
                '#type' => 'checkbox',
                '#title' => t('I have read and understand the <a href="/policies">policies associated</a> with reserving rooms in this College House.'),
                '#required' => TRUE,
                '#weight' => 100
            ];
            
        } else {
            $node  = $formState->getFormObject()->getEntity();
            $groupId = (int) $node->get('field_group')->getString();
            $group = Group::load($groupId);
            if($group) {
                $form['field_room']['widget']['#options'] = $this->_getGroupRooms($groupId);
                $form['field_room']['widget']['#title'] .= " at {$group->label()}";
            }

            foreach ($form['field_event_schedule']['widget'] as $key => $value) {
                if (is_numeric($key) && $key !== 0) {
                    unset($form['field_event_schedule']['widget'][$key]);
                }
            }
        }
        $form['actions']['submit']['#value'] = t('Send Request');
        return $form;
    }

    private function _getGroupRooms(int $groupId)
    {
        $roomsIds = \Drupal::entityQuery('node')
            ->accessCheck(TRUE)
            ->condition('type', 'room')
            ->condition('status', 1)
            ->sort('title', 'ASC') 
            ->condition('field_group', $groupId)
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
