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
            '#default_value' => NULL,
            '#required' => FALSE,
        ];

        $form['wrapper']['type'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended Audience/Participant Group'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedAudience(),
            '#default_value' => NULL,
            '#required' => FALSE,
        ];

        $form['wrapper']['outcome'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended Outcome(s)'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedOutcomes(),
            '#default_value' => NULL,
            '#required' => FALSE,
        ];

        $form['wrapper']['participants'] = [
            '#type' => 'select',
            '#title' => $this->t('Intended participant year(s)'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventIntendedParticipantYears(),
            '#default_value' => NULL,
            '#required' => FALSE,
        ];

        $form['wrapper']['goal_area'] = [
            '#type' => 'select',
            '#title' => $this->t('CHAS Priority/Goal Area'),
            '#options' => ['_all' => $this->t('- Any -')] + DropdownOption::getEventGoalAreas(),
            '#default_value' => NULL,
            '#required' => FALSE,
        ];

        $form['wrapper']['submit_from'] = [
            '#type' => 'date',
            '#title' => 'Submitted From',
            '#default_value' => NULL,
        ];


        $form['wrapper']['submit_to'] = [
            '#type' => 'date',
            '#title' => 'Submitted To',
            '#default_value' => NULL,
        ];

        $form['wrapper']['submit'] = [
            '#type' => 'submit',
            '#id' => 'submit-btn',
            '#value' => $this->t('Apply'),
            '#ajax' => [
                'callback' => '::filterAjaxCallback',
                'wrapper' => 'report-table-container',
                'progress' => ['type' => 'fullscreen'],
            ],
        ];
        $form['wrapper']['reset'] = [
            '#type' => 'submit',
            '#id' => 'reset-btn',
            '#value' => $this->t('Reset'),
            '#ajax' => [
                'callback' => '::filterAjaxCallback',
                'wrapper' => 'report-table-container',
                'progress' => ['type' => 'fullscreen'],
            ],
            '#access' => false,
            '#attributes' => [
                'onclick' => 'this.form.reset();setTimeout(() => {}, 10);',
            ]
        ];

        return $form;
    }

    public function filterAjaxCallback(array &$form, FormStateInterface $form_state)
    {
        $trigger = $form_state->getUserInput()['_triggering_element_value'] ?? 'Submit';
        $controller = \Drupal::service('event_feedback.event_feedback_controller');
        if ($trigger === 'Submit') {
            $form['wrapper']['reset']['#access'] = true;
        } else {
            $this->resetForm($form, $form_state);
        }
        \Drupal::requestStack()->getSession()->set('participantsSurvey', $form_state->getValues());
        $data = $controller->reportData($form_state->getValues(), $form);
        $res = new AjaxResponse();
        // $res->addCommand(new HtmlCommand('#view-filters-container', $form));
        // $res->addCommand(new HtmlCommand('#report-table-container', $data));
        $res->addCommand(new HtmlCommand('#participations-survey-report', $data));
        return $res;
    }

    private function resetForm(array &$form, FormStateInterface &$form_state)
    {
        $form['wrapper']['reset']['#access'] = false;
        $form['wrapper']['gid']['#value'] = '_all';
        $form['wrapper']['type']['#value'] = '_all';
        $form['wrapper']['outcome']['#value'] = '_all';
        $form['wrapper']['participants']['#value'] = '_all';
        $form['wrapper']['goal_area']['#value'] = '_all';
        $form['wrapper']['submit_from']['#value'] = '_all';
        $form['wrapper']['submit_to']['#value'] = '_all';
        $form_state->setValues([]);
        $form_state->setRebuild(TRUE);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {}
}
