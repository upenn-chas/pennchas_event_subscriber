<?php

namespace Drupal\event_feedback\Plugin\Form;

use Drupal\common_utils\Plugin\Option\DropdownOption;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class FilterForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'feedback_filter_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $request = \Drupal::request()->request;

        $form['#attributes']['class'] = 'views-exposed-form bef-exposed-form';

        $form['wrapper'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => 'd-flex flex-wrap'
            ]
        ];
        $form['wrapper']['gid'] = [
            '#type' => 'select',
            '#title' => $this->t('House'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getGroups(),
            '#default_value' => $request->get('gid', null),
            '#required' => FALSE,
        ];

        $form['wrapper']['type'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended Audience/Participant Group'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedAudience(),
            '#default_value' => $request->get('type', null),
            '#required' => FALSE,
        ];

        $form['wrapper']['outcome'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended Outcome(s)'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedOutcomes(),
            '#default_value' => $request->get('outcome', null),
            '#required' => FALSE,
        ];

        $form['wrapper']['participants'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended participant year(s)'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedParticipantYears(),
            '#default_value' => $request->get('participants', null),
            '#required' => FALSE,
        ];

        $form['wrapper']['goal_area'] = [
            '#type' => 'select',
            '#title' => $this->t('CHAS Priority/Goal Area'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventGoalAreas(),
            '#default_value' => $request->get('goal_area', null),
            '#required' => FALSE,
        ];

        $form['wrapper']['submit_from'] = [
            '#type' => 'date',
            '#title' => 'Submitted From'
        ];
        

        $form['wrapper']['submit_to'] = [
            '#type' => 'date',
            '#title' => 'Submitted To'
        ];

        $form['wrapper']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
            '#ajax' => [
                'callback' => '::filterAjaxCallback',
                'wrapper' => 'report-table-container',
            ],
        ];

        $form['wrapper']['reset'] = [
            '#type' => 'reset',
            '#value' => $this->t('Reset'),
        ];

        return $form;
    }

    public function filterAjaxCallback(array &$form, FormStateInterface $form_state)
    {
        $controller = \Drupal::service('event_feedback.event_feedback_controller');;
        $data = $controller->buildTable($form_state->getValues());
        $res = new AjaxResponse();
        $res->addCommand(new HtmlCommand('#report-table-container', $data));
        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {}
}
