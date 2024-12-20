<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class EventFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
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
}
