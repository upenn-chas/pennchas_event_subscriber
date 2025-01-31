<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

class UserFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $options = [
            'senior_staff' => t('SeniorStaff'),
            'ra' => t('RA'),
            'student_worker' => t('Student Worker'),
            'any_resident' => t('Any Resident'),
            'anyone_with_pennkey' => t('Anyone with a Pennkey'),
        ];
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

    function role_form_submit_handler(&$form, FormStateInterface $formState)
    {
        $umberaRoles = $formState->getValue('umbera_roles');
        $umberaRoles = array_filter($umberaRoles);
        if ($umberaRoles) {
            $role = $formState->getFormObject()->getEntity();
            $role->setThirdPartySetting('pennchas_form_alter', 'umbera_roles', $umberaRoles);
            $role->save();
        }
    }
}
