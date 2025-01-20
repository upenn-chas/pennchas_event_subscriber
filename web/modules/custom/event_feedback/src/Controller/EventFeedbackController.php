<?php

namespace Drupal\event_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
// use Symfony\Component\HttpFoundation\Request;

class EventFeedbackController extends ControllerBase
{
    protected $eventFeedbackWebformId = 'event_feedback';

    public function feedback(Node $node)
    {
        if ($node->getType() !== 'chas_event') {
            return [
                '#markup' => $this->t('Content type not supported.'),
            ];
        }

        if(!$node->isPublished()) {
            return [
                '#markup' => $this->t('The event is not published.'),
            ];
        }

        $webform = Webform::load($this->eventFeedbackWebformId);

        if (!$webform) {
            return [
                '#markup' => $this->t('Webform not found.')
            ];
        }

        $eventDates = [];
        foreach($node->get('field_event_schedule')->getValue() as $schedule) {
            $eventDates[] = $schedule['value'];
        }
        array_unique($eventDates);

        if($this->hasUserAlreadySubmitted($this->eventFeedbackWebformId, $node->id())) {
            return [
                '#theme' => 'event_feedback_page',
                '#node' => $node,
                '#message' => 'You have already submitted your feedback. Thank You!',
                '#eventDates' => $eventDates
            ];
        }

        $eventIntendedElements = $webform->getElementsDecoded();
        $eventIntendedElements['event_intended_to']['#title'] = $eventIntendedElements['event_intended_to']['#title'] . ': ' . str_replace(', ', ' | ', $node->get('field_intended_outcomes')->getString());

        $webform->setElements($eventIntendedElements);

        return [
            '#theme' => 'event_feedback_page',
            '#node' => $node,
            '#eventDates' => $eventDates,
            '#webform' => [
                '#type' => 'webform',
                '#webform' => $webform->id(),
            ],
        ];
    }

    public function report()
    {
        $webform = Webform::load($this->eventFeedbackWebformId);
        dd($webform->getElementsOriginalDecoded());

        $tableHeaders = [
            'Header 1',
            'Header 2',
            'Header 3',
            'Header 4',
            'Header 5'
        ];

        return [
            '#theme' => 'report_page',
            '#title' => $this->t('Participations Survey Report'),
            '#header' => $tableHeaders,
        ];
    }

    private function hasUserAlreadySubmitted($webformId, $nodeId)
    {
        return (bool) \Drupal::entityQuery('webform_submission')
        ->accessCheck(false)
        ->condition('entity_id', $nodeId)
        ->condition('webform_id', $webformId)
        ->condition('uid', \Drupal::currentUser()->id())
        ->count()->execute();
    }
}
