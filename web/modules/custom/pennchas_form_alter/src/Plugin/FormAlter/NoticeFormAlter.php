<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class NoticeFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $houseCount = count($form['field_groups']['widget']['#options']);
        if ($houseCount > 1) {
            $form['#attached']['library'][] = 'pennchas_form_alter/notice_select_all';
            $form['field_groups']['widget']['#options'] = ['select_all' => t('All college houses')] + $form['field_groups']['widget']['#options'];
        }
        return $form;
    }
}
