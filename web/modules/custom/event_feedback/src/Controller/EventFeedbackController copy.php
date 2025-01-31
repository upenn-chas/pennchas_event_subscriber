<?php

namespace Drupal\event_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Pager\PagerManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\event_feedback\Plugin\Form\FilterForm;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventFeedbackControllerr extends ControllerBase
{
    /**
     * The event feedback webform machine name.
     *
     * @var string
     */
    protected $eventFeedbackWebformId = 'event_feedback';

    /**
     * The renderer service.
     *
     * @var \Drupal\Core\Render\RendererInterface
     */
    protected RendererInterface $renderer;

    /**
     * Page length.
     *
     * @var int
     */
    protected $pageLength = 10;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('renderer')
        );
    }

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
        $request = \Drupal::request()->request->all();
        $page = \Drupal::request()->query->get('page', 0);
        $page = $page < 0 ? 0 : $page;

        [$header, $rows, $total] = \Drupal::service('event_feedback.report_service')->buildReport($this->eventFeedbackWebformId,$request, $page, $this->pageLength);

        $rows = $this->buildTable($request, $page, true);
        $total = $rows['total'];
        unset($rows['total']);
        $rows = $this->renderer->render($rows);
        $pager = \Drupal::service('pager.manager')->createPager($total, $this->pageLength);
        return [
            '#theme' => 'report_page',
            '#exposed' => \Drupal::formBuilder()->getForm(new FilterForm()),
            '#title' => $this->t('Participations Survey Report'),
            '#rows' => $rows,
            '#header' => [
                '#markup' => '<a target="_blank" class="views-display-link" href="' . Url::fromRoute('event_feedback.report_export')->toString() . '">' . $this->t('Export') . '</a>',
            ],
            '#pager' => ['#type' => 'pager']
        ];
    }

    public function buildTable($filters, $page = 0, $includeTotal = false)
    {
        $webform = Webform::load($this->eventFeedbackWebformId);
        $tableHeaderData = $this->buildHeaderForTable($webform);
        $result = $this->getParticipantsData($filters, $page);
        $total = $result['total'];
        $rows = $this->processDataForTable($result, $tableHeaderData['indexes'], $tableHeaderData['default']);

        $tableData = [
            '#theme' => 'report_table',
            '#table_header' => $tableHeaderData['header'],
            '#ccount' => count($tableHeaderData['default']),
            '#table_body' => $rows
        ];
        if ($includeTotal) {
            $tableData['total'] = $total;
        }

        return $tableData;
    }

    public function reportExport()
    {
        $request = \Drupal::request()->request->all();
        $webform = Webform::load($this->eventFeedbackWebformId);
        $tableHeaderData = $this->buildHeaderForCSV($webform);
        return $this->buildCSVData($request, $tableHeaderData);
    }

    private function buildHeaderForTable(Webform $webform)
    {
        $tableHeaders = [
            [
                [
                    'title' => 'House(s)',
                    'cspan' => 0,
                ],
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
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                ]
            ]
        ];
        $keyIndexes = [
            'houses' => 0,
            'event' => 1,
            'repondants' => 2
        ];
        $defaultRowValues = [
            '',
            '',
            0
        ];
        $colIndex = count($defaultRowValues);

        $webformElements = $webform->getElementsOriginalDecoded();
        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $tableHeaders[0][] = [
                    'title' => $ele['#title'],
                    'cspan' => count($ele['#options'])
                ];
                foreach ($ele['#options'] as $optKey => $opt) {
                    $keyIndexes["{$key}:{$optKey}"] = $colIndex;
                    $defaultRowValues[$colIndex] = 0;
                    $tableHeaders[1][$colIndex++] = [
                        'title' => $opt,
                        'cspan' => 0
                    ];
                }
            }
        }

        return [
            'header' => $tableHeaders,
            'indexes' => $keyIndexes,
            'default' => $defaultRowValues
        ];
    }

    private function processDataForTable($result, $keyColumnMap, $defaultRow)
    {
        $submissionRows = $this->processSubmissionDataForTable($result['submissions'], $keyColumnMap, $defaultRow);

        $rows = [];
        foreach ($result['events'] as $event) {
            $row = $submissionRows[$event['nid']];
            $row[0] = $event['houses'];
            $row[1] = $event['title'];
            $row[2] = $event['respondant'];
            $rows[] = $row;
        }

        return $rows;
    }

    private function processSubmissionDataForTable($submissionData, $keyColumnMap, $defaultRow)
    {
        $submissionRows = [];
        foreach ($submissionData as $submission) {
            $nid = $submission['entity_id'];
            $rowIndex = $keyColumnMap["{$submission['name']}:{$submission['value']}"];
            if (!isset($submissionRows[$nid])) {
                $submissionRows[$nid] = $defaultRow;
            }
            $submissionRows[$nid][$rowIndex] += $submission['count'];
        }
        return $submissionRows;
    }

    private function buildHeaderForCSV(Webform $webform)
    {
        $tableHeaders = [
            [
                'House(s)',
                'Event',
                'Respondants'
            ],
            [
                '',
                '',
                ''
            ]
        ];
        $keyIndexes = [
            'houses' => 0,
            'event' => 1,
            'repondants' => 2
        ];
        $defaultRowValues = [
            '',
            '',
            0
        ];
        $colIndex = count($defaultRowValues);

        $webformElements = $webform->getElementsOriginalDecoded();
        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $firstRowTitle = true;
                foreach ($ele['#options'] as $optKey => $opt) {
                    if($firstRowTitle) {
                        $firstRowTitle = false;
                        $tableHeaders[0][$colIndex] = $ele['#title'];
                    } else {
                        $tableHeaders[0][$colIndex] = '';
                    }
                    $keyIndexes["{$key}:{$optKey}"] = $colIndex;
                    $defaultRowValues[$colIndex] = 0;
                    $tableHeaders[1][$colIndex++] = $opt;
                }
            }
        }

        return [
            'header' => $tableHeaders,
            'indexes' => $keyIndexes,
            'default' => $defaultRowValues
        ];
    }

    private function buildCSVData($filters, $tableHeaderData)
    {
        $result = $this->getParticipantsData($filters, -1);
        $summaryData = $this->processSubmissionDataAndFooter($result['submissions'], $tableHeaderData['indexes'], $tableHeaderData['default']);

        $csvFile = new StreamedResponse();
        $csvFile->setCallback(function () use ($tableHeaderData, $result, $summaryData) {
            $handler = fopen('php://output', 'w');

            foreach ($tableHeaderData['header'] as $header) {
                fputcsv($handler, $header);
            }

            foreach ($result['events'] as $event) {
                $row = $summaryData[0][$event['nid']];
                $row[0] = $event['houses'];
                $row[1] = $event['title'];
                $row[2] = $event['respondant'];
                $summaryData[1][2] += $row[2];
                fputcsv($handler, $row);
            }

            $summaryData[1][1] = 'Total';
            fputcsv($handler, $summaryData[1]);

            fclose($handler);
        });
        $csvFile->headers->set('Content-Type', 'text/csv');
        $csvFile->headers->set('Content-Disposition', 'attachment; filename="participant_survey.csv"');

        return $csvFile;
    }

    private function processSubmissionDataAndFooter($submissionData, $keyColumnMap, $defaultRow)
    {
        $submissionRows = [];
        $footer = $defaultRow;
        foreach ($submissionData as $submission) {
            $nid = $submission['entity_id'];
            $rowIndex = $keyColumnMap["{$submission['name']}:{$submission['value']}"];
            if (!isset($submissionRows[$nid])) {
                $submissionRows[$nid] = $defaultRow;
            }
            $submissionRows[$nid][$rowIndex] += $submission['count'];
            $footer[$rowIndex] += $submission['count'];
        }
        return [
            0 => $submissionRows,
            1 => $footer
        ];
    }

    private function getParticipantsData($filters, $page)
    {
        $connection = Database::getConnection();

        $eventQuery = $connection->select('webform_submission', 'ws');
        $eventQuery->innerJoin('node_field_data', 'n', 'ws.entity_id = n.nid');
        $eventQuery->leftJoin('group_relationship_field_data', 'grfd', 'grfd.entity_id=n.nid');
        $eventQuery->leftJoin('groups_field_data', 'gfd', 'gfd.id=grfd.gid');
        $eventQuery->leftJoin('node__field_intended_audience', 'nfia', 'nfia.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_intended_outcomes', 'nfio', 'nfio.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_event_priority', 'nfep', 'nfep.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_participants', 'nfp', 'nfp.entity_id = n.nid');
        $eventQuery->fields('n', ['title', 'nid']);
        $eventQuery->addExpression('GROUP_CONCAT(DISTINCT gfd.label)', 'houses');
        $eventQuery->addExpression('COUNT(DISTINCT ws.sid)', 'respondant');
        $eventQuery->condition('ws.webform_id', $this->eventFeedbackWebformId, '=');
        $eventQuery->groupBy('ws.entity_id');
        $eventQuery->orderBy('ws.created', 'DESC');


        if (isset($filters['gid']) && $filters['gid'] !== '_all') {
            $eventQuery->condition('grfd.gid', $filters['gid']);
        }

        if (isset($filters['type']) && $filters['type'] !== '_all') {
            $eventQuery->condition('nfia.field_intended_audience_value', $filters['type']);
        }

        if (isset($filters['participants']) && $filters['participants'] !== '_all') {
            $eventQuery->condition('nfp.field_participants_value', $filters['participants']);
        }

        if (isset($filters['outcome']) && $filters['outcome'] !== '_all') {
            $eventQuery->condition('nfio.field_intended_outcomes_value', $filters['outcome']);
        }

        if (isset($filters['goal_area']) && $filters['goal_area'] !== '_all') {
            $eventQuery->condition('nfep.field_event_priority_target_id', $filters['goal_area']);
        }

        if (isset($filters['submit_from']) && $filters['submit_from']) {
            $startFrom = strtotime($filters['submit_from'] . ' 00:00:00');
            $eventQuery->condition('ws.created', $startFrom, '>=');
        }

        if (isset($filters['submit_to']) && $filters['submit_to']) {
            $startTo = strtotime($filters['submit_to'] . ' 23:59:59');
            $eventQuery->condition('ws.created', $startTo, '<=');
        }
        $eventsCountQuery = $eventQuery->countQuery();
        $eventsCount = $eventsCountQuery->execute()->fetchCol();

        if (count($eventsCount) > 0 && !$eventsCount[0]) {
            return [
                'events' => [],
                'submissions' => [],
                'total' => 0
            ];
        }
        $eventsCount = $eventsCount[0];

        if ($page > -1) {
            $eventQuery->range($this->pageLength * $page, $this->pageLength);
        }

        $events = $eventQuery->execute()->fetchAll(PDO::FETCH_ASSOC);
        $eventsIds = array_column($events, 'nid');

        $query = $connection->select('webform_submission_data', 'wsd');
        $query->innerJoin('webform_submission', 'ws', 'wsd.sid = ws.sid');
        $query->fields('ws', ['entity_id']);
        $query->fields('wsd', ['name', 'value']);
        $query->addExpression('COUNT(DISTINCT wsd.sid)', 'count');
        $query->condition('ws.webform_id', $this->eventFeedbackWebformId, '=');
        $query->condition('ws.entity_id', $eventsIds, 'IN');
        $query->groupBy('ws.entity_id');
        $query->groupBy('wsd.name');
        $query->groupBy('wsd.value');

        $submissionData = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        return [
            'events' => $events,
            'submissions' => $submissionData,
            'total' => $eventsCount
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
