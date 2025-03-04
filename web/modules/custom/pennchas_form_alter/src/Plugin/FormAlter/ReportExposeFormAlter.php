<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class ReportExposeFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        if (isset($form['moderation_state'])) {
            unset($form['moderation_state']['#options']['All']);
        }

        if (isset($form['exposed_from_date'])) {
            $form['exposed_from_date']['#title'] = t('From');
        }

        if (isset($form['exposed_to_date'])) {
            $form['exposed_to_date']['#title'] = t('To');
        }

        if (isset($form['type'])) {
            unset($form['type']['#options']['All']);
        }

        $form['#validate'][] = [$this, 'validate'];
        
        return $form;
    }

    public function validate(array $form, FormStateInterface &$formState)
    {
        $exposedFromDate = $formState->getValue('exposed_from_date');
        $exposedToDate = $formState->getValue('exposed_to_date');

        if (!$exposedFromDate || !$exposedToDate) {
            return;
        }
        if ($exposedFromDate > $exposedToDate) {
            $formState->setErrorByName('exposed_from_date', t('From date must be less than or equal to To date.'));
        }
    }
}
