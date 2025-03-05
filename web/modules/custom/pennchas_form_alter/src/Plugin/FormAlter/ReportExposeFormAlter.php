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
        $form['#attached']['library'][] = 'pennchas_form_alter/toast';


        return $form;
    }

    public function validate(array $form, FormStateInterface $formState)
    {
        $triggeringElement = $formState->getTriggeringElement();
        if ($triggeringElement['#value'] === 'Reset') {
            return;
        }
        $exposedFromDate = $formState->getValue('exposed_from_date');
        $exposedToDate = $formState->getValue('exposed_to_date');

        if (!$exposedFromDate || !$exposedToDate) {
            return;
        }
        if ($exposedToDate < $exposedFromDate) {
            $formState->setErrorByName('exposed_to_date', t('To Date must be greater than or equal to From Date.'));
        }
    }
}
