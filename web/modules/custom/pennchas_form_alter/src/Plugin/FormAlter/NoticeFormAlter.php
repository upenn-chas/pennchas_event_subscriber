<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

class NoticeFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $form['field_groups']['widget']['#options'] = $this->getOptions();
        $houseCount = count($form['field_groups']['widget']['#options']);
        if ($houseCount > 1) {
            $form['#attached']['library'][] = 'pennchas_form_alter/notice_select_all';
            $form['field_groups']['widget']['#options'] = ['select_all' => t('All college houses')] + $form['field_groups']['widget']['#options'];
        }
        unset($form['field_event_schedule']['widget']['add_more']);
        if ($form['#form_id'] === 'node_notices_edit_form') {
            foreach ($form['field_event_schedule']['widget'] as $key => $value) {
                if (is_numeric($key) && $key !== 0) {
                    unset($form['field_event_schedule']['widget'][$key]);
                }
            }
        }
        $index = array_search('group_relationship_entity_submit', $form['actions']['submit']['#submit']);
        if($index !== FALSE) {
            unset( $form['actions']['submit']['#submit'][$index]);
        }
        $form['#attached']['library'][] = 'pennchas_form_alter/custom_smart_date';
        return $form;
    }

    protected function getOptions()
    {
        return  \Drupal::service('pennchas_common.option_group')->options('house1');
    }
}
