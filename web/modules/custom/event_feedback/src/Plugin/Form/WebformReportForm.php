<?php

namespace Drupal\event_feedback\Plugin\Form;

use Drupal\common_utils\Plugin\Option\DropdownOption;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class WebformReportForm extends FormBase
{
    /**
     * The webform ID.
     *
     * @var string
     */
    protected $webformId;

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'webform_report_filter_form';
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($webformId = NULL)
    {
        $this->webformId = $webformId;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['#method'] = 'get';
        $form['#attributes']['class'] = ['views-exposed-form', 'bef-exposed-form'];

        $form['wrapper'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => 'd-flex flex-wrap'
            ]
        ];
        
        $form['wrapper']['wid'] = [
            '#type' => 'select',
            '#title' => $this->t('Webform'),
            '#options' => ['_none' => $this->t('- Select Webform -')] + DropdownOption::getWebforms(),
            '#description' => $this->t('Please select the webform to load the data.'),
            '#default_value' => $this->webformId,
            '#required' => FALSE,
        ];

        $form['wrapper']['submit'] = [
            '#type' => 'submit',
            '#id' => 'submit-btn',
            '#value' => $this->t('Apply')
        ];


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {}
}
