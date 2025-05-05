<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class ProgramCommunityFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $formId = $form['#form_id'];
        if($formId === 'node_program_community_form') {
            $group = \Drupal::routeMatch()->getParameter('group');
            if($group) {
                $form['field_group']['widget']['#default_value'] = [$group->id()];
            }
        }
        return $form;
    }
}
