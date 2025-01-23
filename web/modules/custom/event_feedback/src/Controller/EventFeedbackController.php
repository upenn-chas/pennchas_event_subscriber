<?php

namespace Drupal\event_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
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

        if (!$node->isPublished()) {
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
        foreach ($node->get('field_event_schedule')->getValue() as $schedule) {
            $eventDates[] = $schedule['value'];
        }
        array_unique($eventDates);

        if ($this->hasUserAlreadySubmitted($this->eventFeedbackWebformId, $node->id())) {
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
        $webformElements = $webform->getElementsOriginalDecoded();

        $tableHeaders = [
            [
                [
                    'title' => 'Event',
                    'cspan' => 0,
                ],
                [
                    'title' => 'Respondants',
                    'cspan' => 0
                ],
            ],
            [
                [
                    'title' => '',
                    'cspan' => 0,
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                ]
            ]
        ];
        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $tableHeaders[0][] = [
                    'title' => $ele['#title'],
                    'cspan' => count($ele['#options'])
                ];
                foreach ($ele['#options'] as $opt) {
                    $tableHeaders[1][] = [
                        'title' => $opt,
                        'cspan' => 0
                    ];
                }
            }
        }

        $connection = Database::getConnection();
        $query = $connection->select('webform_submission_data', 'wsd');
        $query->innerJoin('webform_submission', 'ws', 'wsd.sid = ws.sid');
        // $query->innerJoin('node_field_data', 'n', 'ws.entity_id = n.nid');
        $query->condition('wsd.webform_id', $this->eventFeedbackWebformId, '=');
        $query->fields('wsd', ['webform_id', 'name', 'value']);
        $query->fields('ws', ['entity_id']);
        $query->addExpression('COUNT(wsd.value)', 'count');
        $query->groupBy('ws.entity_id');
        $query->groupBy('wsd.webform_id');
        $query->groupBy('wsd.name',);
        $query->groupBy('wsd.value');

        $results = $query->execute()->fetchAll();

        // dd($results);

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
