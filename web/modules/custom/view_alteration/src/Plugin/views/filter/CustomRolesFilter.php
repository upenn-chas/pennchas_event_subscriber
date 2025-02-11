<?php

namespace Drupal\view_alteration\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Roles custom filter
 * 
 * @ingroup views_filter_handlers
 * 
 * @ViewsFilter("custom_roles_filter")
 */
class CustomRolesFilter extends FilterPluginBase
{
    /**
     * {@inheritDoc}
     */
    public function buildExposedForm(&$form, FormStateInterface $form_state)
    {
        parent::buildExposedForm($form, $form_state);

        // $form[$this->options['expose']['identifier']]['#type'] = 'select';
        // $form[$this->options['expose']['identifier']]['#multiple'] = TRUE;
        // $form[$this->options['expose']['identifier']]['#options'] = $this->getOptions();
        // $form[$this->options['expose']['identifier']]['#size'] = 5;
        // $form[$this->options['expose']['identifier']]['#description'] = $this->t('Select one or more options to filter results.');
    }

    /**
     * {@inheritdoc}
     */
    public function query()
    {
        // Add custom filtering logic here.
        $value = $this->value;

        if (!empty($value)) {
            // $this->query->addWhereExpression(0, 'field_name LIKE :value', [':value' => '%' . $this->escapeLike($value) . '%']);
        }
    }

    /**
     * {@inheritdoc}
     */
    // protected function defineOptions()
    // {
    //     $options = parent::defineOptions();
    //     $options['filter_roles'] = [];

    //     return $options;
    // }

    /**
     * Gets the options for the multi-select filter.
     *
     * @return array
     *   An associative array of options.
     */
    protected function getOptions()
    {
        // Define the options for the multi-select filter.
        // Replace with dynamic data if needed.
        $options = []; 
        $roles = Role::loadMultiple();
        foreach($roles as $role) {
            $options[$role->id()] = $role->label();
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    // public function buildOptionsForm(&$form, FormStateInterface $form_state)
    // {
        
    //     // dd($form);
    //     parent::buildOptionsForm($form, $form_state);
    //     // Add custom filter options.
    //     // $form['filter_roles'] = [
    //     //     '#type' => 'checkboxes',
    //     //     '#title' => $this->t('Roles'),
    //     //     '#options' => [
    //     //         'option1' => 'Option 1',
    //     //         'option2' => 'Option 2',
    //     //         'option3' => 'Option 3',
    //     //     ],
    //     //     '#default_value' => [],
    //     //     '#description' => $this->t('An example option for this filter.'),
    //     // ];
    //     // return $form;
        
    // }
}
