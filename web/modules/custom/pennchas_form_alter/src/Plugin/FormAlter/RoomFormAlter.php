<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class RoomFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $form['field_duration']['widget'][0]['duration']['#h']['#max'] = 23;
        $form['field_duration']['widget'][0]['duration']['#h']['#step'] = 1;
        $form['field_duration']['widget'][0]['duration']['#i']['#max'] = 30;
        $form['field_duration']['widget'][0]['duration']['#i']['#step'] = 30;
        return $form;
    }
}
