<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class EventFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $form['field_long_text']['#states'] = [
            'visible' => [':input[name="field_intended_audience"]' => ['value' => 'floor_event']]
        ];
        $form['field_long_text']['widget'][0]['value']['#required'] = [
            ':input[name="field_intended_audience"]' => ['value' => 'floor_event']
        ];

        $form['field_program_communities']['#states'] = [
            'visible' => [':input[name="field_intended_audience"]' => ['value' => 'community_event']]
        ];
        $form['field_program_communities']['widget']['#required'] = [
            ':input[name="field_intended_audience"]' => ['value' => 'community_event']
        ];

        $form['field_college_houses']['#states'] = [
            'visible' => [':input[name="field_intended_audience"]' => ['value' => 'house_event']]
        ];
        $form['field_college_houses']['widget']['#required'] = [
            ':input[name="field_intended_audience"]' => ['value' => 'house_event']
        ];

        $outcomeRemarksCondition = [
            [':input[name="field_intended_outcomes[learn_a_skill]"]' => ['checked' => true]],
            [':input[name="field_intended_outcomes[gain_information]"]' => ['checked' => true]],
            [':input[name="field_intended_outcomes[connect_with_peers]"]' => ['checked' => true]],
            [':input[name="field_intended_outcomes[connect_with_resource]"]' => ['checked' => true]],
            [':input[name="field_intended_outcomes[connect_with_faculty]"]' => ['checked' => true]],
            [':input[name="field_intended_outcomes[engage_in_an_activity]"]' => ['checked' => true]]
        ];
        $form['field_long_text_2']['#states'] = ['visible' => $outcomeRemarksCondition];
        $form['field_long_text_2']['widget'][0]['value']['#required'] = $outcomeRemarksCondition;

        $marketingFieldConditions = [
            [':input[name="field_intended_audience"]' => [
                ['value' => 'house_event'],
                'OR',
                ['value' => 'floor_event']
            ]]
        ];

        $form['field_banner']['#states'] = ['visible' => $marketingFieldConditions];
        $form['field_file']['#states'] = ['visible' => $marketingFieldConditions];
        $form['field_marketing_blurb']['#states'] = ['visible' => $marketingFieldConditions];
        $form['field_link_1']['#states'] = ['visible' => $marketingFieldConditions];

        // $form['#attached']['library'][] = 'node_event_form_ext/event_listeners';
        // if ($formId === 'node_chas_event_form') {
        //     $form['#attached']['library'][] = 'node_event_form_ext/default_select';
        // }

        return $form;
    }
}
