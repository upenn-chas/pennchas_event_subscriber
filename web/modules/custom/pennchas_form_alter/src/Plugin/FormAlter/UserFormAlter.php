<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class UserFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $options = $this->getUmbrellaRoles();
        if($form['form_id']['#value'] == 'user_role_form'){
            $roleType = $form['id']['#default_value'];
        }else if($form['form_id']['#value'] == 'group_role_edit_form'){
            $roleType = $form['id']['#value'];
        }


        $form['umbera_roles'] = [
            '#type' => 'checkboxes',
            '#title' => t('Umbrella Roles'),
            '#description' => t('Choose an option from the dropdown list.'),
            '#options' => $options,
            '#default_value' => \Drupal::state()->get('custom_group_role_config_' . $roleType) ?: [],
            '#required' => TRUE,
        ];
        $form['actions']['submit']['#submit'][] = [$this, 'roleSubmit'];
        return $form;
    }

    public function roleSubmit(array $form, FormStateInterface $formState)
    {
        $selectedOptions = array_filter($formState->getValue('umbera_roles'));
        $roleType = $form['id']['#value'];
        \Drupal::state()->set('custom_group_role_config_' . $roleType, $selectedOptions);
    }

    protected function getUmbrellaRoles()
    {
        return \Drupal::service('pennchas_common.field_values_label')->getNodeFieldAllowedValues('room', 'field_available_to');
    }
}
